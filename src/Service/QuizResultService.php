<?php

namespace App\Service;

use App\Entity\QuizResult;
use App\Entity\Sections;
use App\Entity\User;
use App\Repository\QuizResultRepository;
use App\Repository\SectionsRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuizResultService
{
    public function __construct(protected QuizResultRepository $quizResultRepository, protected EntityManagerInterface $em, protected SectionsRepository $sectionsRepository)
    {}

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

        return [
            'quizResults' => $quizResults,
            'totalQuestions' => $totalQuestions,
            'program_slug' => $programSlug,
            'section_slug' => $sectionSlug,
            'slug' => $courseSlug,
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

    public function getLastAttemptId(User $user, string $sectionSlug): ?int
    {
        $section = $this->sectionsRepository->findOneBy(['slug' => $sectionSlug]);
        if (!$section) {
            return null; // Gérer le cas où la section n'existe pas
        }

        // récupérer la dernière tentative de quiz de l'utilisateur pour la section donnée
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
}