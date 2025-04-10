<?php

namespace App\Controller\Quiz;

use App\Repository\AnswerRepository;
use App\Repository\QuestionRepository;
use App\Repository\SectionsRepository;
use App\Service\QuizResultService;
use Doctrine\ORM\EntityManagerInterface;
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
    ): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if ($data === null) {
                return new JsonResponse(['error' => 'Donnée JSON invalide'], 400);
            }

            // Récupérer les données du payload
            $sectionId = $data['sectionId'] ?? null;
            $questionId = $data['questionId'] ?? null;
            $answerId = $data['answerId'] ?? null;
            $attemptId = $data['attemptId'] ?? 0;

            if (!$sectionId || !$questionId || !$answerId) {
                return new JsonResponse(['error' => 'Données manquantes'], 400);
            }

            // Récupérer la section
            $section = $sectionsRepository->find($sectionId);
            if (!$section) {
                return new JsonResponse(['error' => 'Section non trouvée'], 404);
            }

            // Récupérer la question depuis la base de données
            $question = $questionRepository->find($questionId);
            if (!$question) {
                return new JsonResponse(['error' => 'Question non trouvée'], 404);
            }

            // Vérifier si la réponse de l'utilisateur est correcte
            $correctAnswer = $question->getCorrectAnswer();
            $isCorrect = ($answerId == $correctAnswer->getId());

            // Retourner la réponse avec le résultat de la validation
            return new JsonResponse([
                'correct' => $isCorrect,
                'explanation' => $question->getExplanation(),
                'attemptId' => $attemptId, // Retourner l'ID de la tentative
            ]);
        } catch (\Exception $e) {
            // Retourner une réponse JSON avec l'erreur
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/quiz/finalize', name: 'quiz_finalize', methods: ['GET', 'POST'])]
    public function finalizeQuiz(Request $request, EntityManagerInterface $em, AnswerRepository $answerRepository, SectionsRepository $sectionsRepository, QuizResultService $quizResultService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null || empty($data['answers']) || empty($data['sectionId'])) {
            return new JsonResponse(['error' => 'Données non valides, pas de réponse ou section disponible'], 400);
        }

        $user = $this->getUser();
        $score = 0;

        // Récupérer la section du quiz
        $section = $sectionsRepository->find($data['sectionId']);
        if (!$section) {
            return new JsonResponse(['error' => 'Section non trouvée'], 404);
        }

        // Récupérer les slugs pour l'URL
        $course = $section->getCourses()->last();
        $programSlug = $section->getProgram()->getSlug();
        $sectionSlug = $section->getSlug();
        $courseSlug = $course ? $course->getSlug() : null;

        // Vérifier s'il existe une tentative incomplète (score 0)
        $existingAttempt = $quizResultService->getLastAttemptId($user, $sectionSlug);

        // Calcul du score
        foreach ($data['answers'] as $answerData) {
            if (empty($answerData['answerId'])) {
                continue; // Si answerId est null ou vide, ignorer cette réponse
            }

            $selectedAnswer = $answerRepository->find($answerData['answerId']);
            if ($selectedAnswer && $selectedAnswer->getIsCorrect()) {
                $score++;
            }
        }

        $attemptId = $quizResultService->handleQuizAttempt($existingAttempt, $user, $section, $score);

        // Génération de l'URL de redirection vers la page de résultats
        $redirectUrl = $this->generateUrl('courses_quiz_attempt', [
            'program_slug' => $programSlug,
            'section_slug' => $sectionSlug,
            'slug' => $courseSlug,
            'attemptId' => $attemptId
        ]);

        return new JsonResponse([
            'attemptId' => $attemptId,
            'score' => $score,
            'totalQuestions' => count($data['answers']),
            'redirectUrl' => $redirectUrl,
        ]);
    }
}