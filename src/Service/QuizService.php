<?php

namespace App\Service;

use App\Entity\Sections;
use App\Repository\QuestionRepository;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class QuizService
{
    public function __construct(protected QuestionRepository $questionRepository, protected CsrfTokenManagerInterface $csrfTokenManager)
    {}

    public function getQuizData(Sections $section): array
    {
        // Récupération des questions du quiz liées à la section
        $questions = $this->questionRepository->findBy(['section' => $section]);

        // Comptage du nombre de questions
        $count = $this->questionRepository->countAllBySection($section);

        // Génération du CSRF token
        $csrfToken = $this->csrfTokenManager->getToken('quiz_submission');

        return [
            'questions' => $questions,
            'count' => $count,
            'csrf_token' => $csrfToken,
        ];
    }
}