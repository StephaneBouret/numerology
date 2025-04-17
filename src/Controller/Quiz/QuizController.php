<?php

namespace App\Controller\Quiz;

use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\SectionsRepository;
use App\Service\QuizResultService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class QuizController extends AbstractController
{
    #[Route('/quiz/submit', name: 'quiz_submit', methods: ['POST'])]
    public function submitQuiz(
        Request $request,
        QuestionRepository $questionRepository,
        SectionsRepository $sectionsRepository,
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                return new JsonResponse(['error' => 'Donnée JSON invalide'], 400);
            }

            $sectionId = $data['sectionId'] ?? null;
            $questionId = $data['questionId'] ?? null;
            $answerId = $data['answerId'] ?? null; // Peut être un entier ou un tableau
            $attemptId = $data['attemptId'] ?? 0;

            if (!$sectionId || !$questionId || $answerId === null) {
                return new JsonResponse(['error' => 'Données manquantes'], 400);
            }

            $section = $sectionsRepository->find($sectionId);
            if (!$section) {
                return new JsonResponse(['error' => 'Section non trouvée'], 404);
            }

            $question = $questionRepository->find($questionId);
            if (!$question) {
                return new JsonResponse(['error' => 'Question non trouvée'], 404);
            }

            $isMultiple = $question->isMultiple();

            if ($isMultiple) {
                // Réponses multiples : $answerId est un tableau
                if (!is_array($answerId)) {
                    return new JsonResponse(['error' => 'Format de réponse incorrect pour question multiple'], 400);
                }

                $correctAnswers = $question->getAnswers()
                    ->filter(fn ($a) => $a->isCorrect())
                    ->map(fn ($a) => $a->getId())
                    ->toArray();

                $userAnswers = array_map('intval', $answerId);
                sort($userAnswers);
                sort($correctAnswers);

                $isCorrect = $userAnswers === $correctAnswers;

                return new JsonResponse([
                    'correct' => $isCorrect,
                    'correctAnswers' => $correctAnswers,
                    'explanation' => $question->getExplanation(),
                    'attemptId' => $attemptId,
                ]);
            } else {
                // Réponse unique
                $correctAnswer = $question->getCorrectAnswer();
                $isCorrect = ($answerId == $correctAnswer?->getId());

                return new JsonResponse([
                    'correct' => $isCorrect,
                    'correctAnswers' => [$correctAnswer?->getId()],
                    'explanation' => $question->getExplanation(),
                    'attemptId' => $attemptId,
                ]);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/quiz/finalize', name: 'quiz_finalize', methods: ['GET', 'POST'])]
    public function finalizeQuiz(
        Request $request,
        AnswerRepository $answerRepository,
        SectionsRepository $sectionsRepository,
        QuizResultService $quizResultService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if ($data === null || empty($data['answers']) || empty($data['sectionId'])) {
            return new JsonResponse(['error' => 'Données non valides, pas de réponse ou section disponible'], 400);
        }

        $user = $this->getUser();

        $section = $sectionsRepository->find($data['sectionId']);
        if (!$section) {
            return new JsonResponse(['error' => 'Section non trouvée'], 404);
        }

        $startedAtString = $data['startedAt'] ?? null;
        $startedAt = $startedAtString ? new \DateTimeImmutable($startedAtString) : null;

        // Calcul du score délégué au service
        $score = $quizResultService->calculateScore($data['answers'], $answerRepository);

        // Vérifier s'il existe une tentative incomplète (score 0)
        $existingAttempt = $quizResultService->getLastAttemptId($user, $section->getSlug());

        $attemptId = $quizResultService->handleQuizAttempt($existingAttempt, $user, $section, $score, $startedAt);

        // Génération de l'URL de redirection vers la page de résultats
        $course = $section->getCourses()->last();
        $programSlug = $section->getProgram()->getSlug();
        $sectionSlug = $section->getSlug();
        $courseSlug = $course ? $course->getSlug() : null;

        $redirectUrl = $this->generateUrl('courses_quiz_attempt', [
            'program_slug' => $programSlug,
            'section_slug' => $sectionSlug,
            'slug' => $courseSlug,
            'attemptId' => $attemptId,
        ]);

        return new JsonResponse([
            'attemptId' => $attemptId,
            'score' => $score,
            'totalQuestions' => count($data['answers']),
            'redirectUrl' => $redirectUrl,
        ]);
    }
}