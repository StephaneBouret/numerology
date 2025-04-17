<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\QuizResult;
use App\Entity\Sections;
use App\Repository\AnswerRepository;
use App\Repository\QuizResultRepository;
use App\Repository\SectionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuizResultService
{
    public function __construct(protected QuizResultRepository $quizResultRepository, protected EntityManagerInterface $em, protected SectionsRepository $sectionsRepository)
    {
    }

    public function getQuizAttemptResults(User $user, int $attemptId): array
    {
        $quizResult = $this->quizResultRepository->find($attemptId);

        if (!$quizResult) {
            throw new NotFoundHttpException("La tentative de quiz n'existe pas.");
        }

        $section = $quizResult->getSection();

        // Récupération des résultats pour la section spécifique
        $quizResults = $this->quizResultRepository->findByUserAndSection($user, $section);

        $totalQuestions = count($quizResult->getSection()->getQuestions());
        $programSlug = $section->getProgram()->getSlug();
        $sectionSlug = $section->getSlug();
        $course = $section->getCourses()->last();
        $courseSlug = $course ? $course->getSlug() : null;

        // $duration = null;
        // if ($quizResult->getStartedAt() && $quizResult->getCompletedAt()) {
        //     $interval = $quizResult->getStartedAt()->diff($quizResult->getCompletedAt());
        //     $duration = $interval->format('%h heures %i minutes %s secondes');
        // }

        return [
            'quizResults' => $quizResults,
            'totalQuestions' => $totalQuestions,
            'program_slug' => $programSlug,
            'section_slug' => $sectionSlug,
            'slug' => $courseSlug,
            // 'duration' => $duration,
        ];
    }

    public function createNewAttempt(User $user, $sectionSlug, bool $forceNewAttempt = false): int
    {
        // Récupérer la section
        $section = $this->sectionsRepository->findOneBy(['slug' => $sectionSlug]);

        if (!$forceNewAttempt) {
            // Vérifier s'il existe une tentative incomplète (score 0)
            $existingAttempt = $this->quizResultRepository->findOneBy([
                'user' => $user,
                'section' => $section,
                'score' => 0
            ]);

            if ($existingAttempt) {
                return $existingAttempt->getId();
            }

            // Chercher la dernière tentative complétée (score > 0)
            $lastCompletedAttempt = $this->quizResultRepository->findOneBy([
                'user' => $user,
                'section' => $section,
            ], ['completedAt' => 'DESC']);

            if ($lastCompletedAttempt && $lastCompletedAttempt->getScore() > 0) {
                return $lastCompletedAttempt->getId();
            }
        }

        // Crée une nouvelle tentative avec score initial à 0
        $now = new DateTimeImmutable();
        $newAttempt = new QuizResult();
        $newAttempt->setUser($user)
            ->setCompletedAt($now)
            ->setScore(0)
            ->setSection($section);

        $this->em->persist($newAttempt);
        $this->em->flush();

        return $newAttempt->getId();
    }

    public function getLastAttemptId(?User $user, string $sectionSlug): ?array
    {
        $section = $this->sectionsRepository->findOneBy(['slug' => $sectionSlug]);

        if (!$section) {
            return null; // Gérer le cas où la section n'existe pas
        }

        // Récupérer la dernière tentative de l'utilisateur pour cette section, sans filtrer par score
        $lastAttempt = $this->em->getRepository(QuizResult::class)
            ->createQueryBuilder('qr')
            ->where('qr.user = :user')
            ->andWhere('qr.section = :section')
            ->setParameter('user', $user)
            ->setParameter('section', $section)
            ->orderBy('qr.completedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $lastAttempt ? ['id' => $lastAttempt->getId(), 'score' => $lastAttempt->getScore()] : null;
    }

    public function updateAttemptScore(int $attemptId, int $score): void
    {
        $attempt = $this->quizResultRepository->find($attemptId);

        if (!$attempt) {
            throw new NotFoundHttpException("La tentative de quiz n'existe pas.");
        }

        $attempt->setScore($score);
        $this->em->flush();
    }

    public function handleQuizAttempt(?array $existingAttempt, User $user, Sections $section, int $score): int
    {
        $now = new \DateTimeImmutable();

        if ($existingAttempt && $existingAttempt['score'] === 0) {
            $this->updateAttemptScore($existingAttempt['id'], $score);
            return $existingAttempt['id'];
        }

        $quizAttempt = new QuizResult();
        $quizAttempt->setUser($user)
            ->setCompletedAt($now)
            ->setScore($score)
            ->setSection($section);

        $this->em->persist($quizAttempt);
        $this->em->flush();

        return $quizAttempt->getId();
    }

    public function calculateScore(array $answers, AnswerRepository $answerRepository): int
    {
        $score = 0;

        foreach ($answers as $answerData) {
            $questionId = $answerData['questionId'] ?? null;
            $userAnswers = $answerData['answerId'] ?? null;

            if (!$questionId || $userAnswers === null) {
                continue;
            }

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
}