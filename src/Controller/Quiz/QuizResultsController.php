<?php
 
 namespace App\Controller\Quiz;

use App\Repository\SectionsRepository;
use App\Service\LessonService;
 use App\Service\QuizResultService;
 use Symfony\Component\HttpFoundation\Response;
 use Symfony\Component\Routing\Attribute\Route;
 use Symfony\Component\Security\Http\Attribute\IsGranted;
 use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 
 class QuizResultsController extends AbstractController
 {
     public function __construct(protected QuizResultService $quizResultService, protected LessonService $lessonService, protected SectionsRepository $sectionsRepository)
     {}
 
     #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas le droit d\'accéder à cette page')]
     #[Route('/courses/{program_slug}/{section_slug}/{slug}/attempts/{attemptId}', name: 'courses_quiz_attempt')]
     public function showQuizAttemptResults($attemptId, $slug): Response
     {
         $user = $this->getUser();
         $quizData = $this->quizResultService->getQuizAttemptResults($user, $attemptId);
         $this->lessonService->handleLessonUpdate($slug, $user);
 
         return $this->redirectToRoute('courses_show', [
             'quizResults' => $quizData['quizResults'],
             'program_slug' => $quizData['program_slug'],
             'section_slug' => $quizData['section_slug'],
             'slug' => $quizData['slug'],
             'attemptId' => $attemptId,
         ]);
     }
 
     #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas le droit d\'accéder à cette page')]
     #[Route('/courses/{program_slug}/{section_slug}/{slug}/retry', name: 'courses_quiz_retry')]
     public function retryQuiz($program_slug, $section_slug, $slug): Response
     {
         $user = $this->getUser();

         $section = $this->sectionsRepository->findOneBy(['slug' => $section_slug]);
 
         // Création d'une nouvelle tentative de quiz
        $newAttemptId = $this->quizResultService->handleQuizAttempt(null, $user, $section, 0, new \DateTimeImmutable());
 
         // Redirige vers la page de quiz avec le nouvel ID de tentative
         return $this->redirectToRoute('courses_show', [
             'program_slug' => $program_slug,
             'section_slug' => $section_slug,
             'slug' => $slug,
             'attemptId' => $newAttemptId,
         ]);
     }
 }