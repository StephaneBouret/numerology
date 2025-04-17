<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\QuizResult;
use App\Entity\Sections;
use App\Repository\AnswerRepository;
use App\Repository\QuizResultRepository;
use App\Repository\SectionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuizResultService
{
    public function __construct(
        private QuizResultRepository $quizResultRepository,
        private EntityManagerInterface $em,
        private SectionsRepository $sectionsRepository
    ) {}

    public function getQuizAttemptResults(User $user, int $attemptId): array
    {
        $quizResult = $this->quizResultRepository->find($attemptId);
        if (!$quizResult) {
            throw new NotFoundHttpException("La tentative de quiz n'existe pas.");
        }

        $section = $quizResult->getSection();
        $quizResults = $this->quizResultRepository->findByUserAndSection($user, $section);
        $course = $section->getCourses()->last();

        return [
            'quizResults' => $quizResults,
            'totalQuestions' => count($section->getQuestions()),
            'program_slug' => $section->getProgram()->getSlug(),
            'section_slug' => $section->getSlug(),
            'slug' => $course?->getSlug(),
        ];
    }

    public function handleQuizAttempt(?array $existingAttempt, User $user, Sections $section, int $score, ?\DateTimeImmutable $startedAt = null): int
    {
        $this->removeIncompleteAttempts($user, $section, $existingAttempt['id'] ?? null);

        if ($existingAttempt && $existingAttempt['score'] === 0) {
            $this->updateAttemptScore($existingAttempt['id'], $score);
            return $existingAttempt['id'];
        }

        $now = new \DateTimeImmutable();
        $quizAttempt = (new QuizResult())
            ->setUser($user)
            ->setSection($section)
            ->setStartedAt($startedAt ?? $now)
            ->setCompletedAt($now)
            ->setScore($score);

        $this->em->persist($quizAttempt);
        $this->em->flush();

        return $quizAttempt->getId();
    }

    public function updateAttemptScore(int $attemptId, int $score): void
    {
        $attempt = $this->quizResultRepository->find($attemptId);
        if (!$attempt) {
            throw new NotFoundHttpException("La tentative de quiz n'existe pas.");
        }

        $now = new \DateTimeImmutable();
        if (!$attempt->getStartedAt()) {
            $attempt->setStartedAt($now);
        }

        $attempt
            ->setCompletedAt($now)
            ->setScore($score);

        $this->removeIncompleteAttempts($attempt->getUser(), $attempt->getSection(), $attemptId);
        $this->em->flush();
    }

    public function calculateScore(array $answers, AnswerRepository $answerRepository): int
    {
        $score = 0;

        foreach ($answers as $answerData) {
            $questionId = $answerData['questionId'] ?? null;
            $userAnswers = $answerData['answerId'] ?? null;

            if (!$questionId || $userAnswers === null) continue;

            $userAnswers = (array) $userAnswers;
            $correctAnswers = $answerRepository->createQueryBuilder('a')
                ->select('a.id')
                ->where('a.question = :question')
                ->andWhere('a.isCorrect = true')
                ->setParameter('question', $questionId)
                ->getQuery()
                ->getSingleColumnResult();

            sort($userAnswers);
            sort($correctAnswers);

            if ($userAnswers === $correctAnswers) {
                $score++;
            }
        }

        return $score;
    }

    public function getLastAttemptId(?User $user, string $sectionSlug): ?array
    {
        $section = $this->sectionsRepository->findOneBy(['slug' => $sectionSlug]);
        if (!$section) return null;

        $lastAttempt = $this->quizResultRepository->createQueryBuilder('qr')
            ->where('qr.user = :user')
            ->andWhere('qr.section = :section')
            ->orderBy('qr.completedAt', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('section', $section)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $lastAttempt ? ['id' => $lastAttempt->getId(), 'score' => $lastAttempt->getScore()] : null;
    }

    public function removeIncompleteAttempts(User $user, Sections $section, ?int $excludeAttemptId = null): void
    {
        $qb = $this->quizResultRepository->createQueryBuilder('qr')
            ->where('qr.user = :user')
            ->andWhere('qr.section = :section')
            ->andWhere('qr.score = 0')
            ->andWhere('qr.completedAt IS NULL')
            ->setParameter('user', $user)
            ->setParameter('section', $section);

        if ($excludeAttemptId) {
            $qb->andWhere('qr.id != :excludeId')
                ->setParameter('excludeId', $excludeAttemptId);
        }

        foreach ($qb->getQuery()->getResult() as $attempt) {
            $this->em->remove($attempt);
        }

        $this->em->flush();
    }
}