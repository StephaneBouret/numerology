<?php

namespace App\Controller\Course;

use App\Entity\Comments;
use App\Entity\User;
use App\Form\ButtonFormType;
use App\Form\CommentsFormType;
use App\Repository\CommentsRepository;
use App\Repository\CoursesRepository;
use App\Repository\LessonRepository;
use App\Repository\NavigationRepository;
use App\Repository\ProgramRepository;
use App\Repository\SectionsRepository;
use App\Service\CourseFileService;
use App\Service\QuizResultService;
use App\Service\QuizService;
use App\Service\SectionDurationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

final class CoursesController extends AbstractController
{
    public function __construct(protected CoursesRepository $coursesRepository, protected SectionsRepository $sectionsRepository, protected EntityManagerInterface $em, protected LessonRepository $lessonRepository, protected SectionDurationService $sectionDurationService, protected ProgramRepository $programRepository)
    {
    }

    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas le droit d\'accéder à cette page')]
    #[Route('/courses/{program_slug}/{section_slug}/{slug}', name: 'courses_show', priority: -1)]
    public function show($slug, $section_slug, $program_slug, Request $request, NavigationRepository $navigationRepository, CourseFileService $courseFileService, CommentsRepository $commentsRepository, QuizService $quizService, QuizResultService $quizResultService): Response
    {
        $currentUrl = $request->getUri();
        $response = new Response();
        $response->headers->setCookie(new Cookie('url_visited_' . $program_slug, $currentUrl, strtotime('+1 month')));

        /** @var User */
        $user = $this->getUser();

        $program = $this->programRepository->findOneBy([
            'slug' => $program_slug
        ]);

        $course = $this->coursesRepository->findOneBy([
            'slug' => $slug
        ]);
        // 🔒 Vérification des permissions via le Voter
        // $this->denyAccessUnlessGranted('VIEW_COURSE', $course, 'Vous n\'avez pas accès à ce cours.');

        $navigation = $navigationRepository->findByProgram($program);

        $content = $courseFileService->getFileContent($course);

        // Total des cours en BDD
        $nbrCourses = $this->coursesRepository->countByProgram($program);
        // Nombre de leçons effectuées par l'utilisateur connecté
        $lessonsDoneForProgram = $user ? $this->lessonRepository->countLessonsDoneByUserAndProgram($user, $program) : 0;

        // $sections = $this->sectionsRepository->findAll();
        $sections = $this->sectionsRepository->findBy([
            'program' => $program
        ]);
        $sectionsTotalDuration = $this->sectionDurationService->calculateTotalDuration($sections);

        if (!$course) {
            throw $this->createNotFoundException("Le cours demandé n'existe pas");
        }

        // On veut récupérer la leçon en-cours par l'utilisateur connecté
        $lesson = $this->lessonRepository->getLessonByUserByCourse($user, $course);

        // Partie Quiz
        // Récupération des questions du quiz pour la section en cours
        $section = $this->sectionsRepository->findOneBy(['slug' => $section_slug]);
        if (!$section) {
            throw $this->createNotFoundException("La section demandée n'existe pas");
        }
        $quizData = $quizService->getQuizData($section);
        // On récupère la dernière tentative de quiz de l'utilisateur pour cette section
        $lastAttempt = $quizResultService->getLastAttemptId($user, $section_slug);
        $lastAttemptId = $lastAttempt ? $lastAttempt['id'] : null;
        // Initialisation de $attemptId à une valeur par défaut si aucune tentative n'est trouvée
        $attemptId = $request->get('attemptId') ?? $lastAttemptId ?? 0; // Par défaut à 0

        // Condition pour afficher les résultats
        $lastAttemptScore = $lastAttempt ? $lastAttempt['score'] : 0;
        $displayResults = (!$attemptId && $lastAttemptScore > 0) || ($attemptId && $attemptId == $lastAttemptId && $lastAttemptScore > 0);

        // Si on doit afficher les résultats, on récupère les résultats de la dernière tentative
        $quizAttemptResults = $displayResults
            ? $quizResultService->getQuizAttemptResults($user, $lastAttemptId)
            : ['quizResults' => [], 'totalQuestions' => count($section->getQuestions())];

        $form = $this->createForm(ButtonFormType::class);

        // Partie commentaires
        $countComments = $commentsRepository->countComments($course);
        $comment = new Comments();
        $commentForm = $this->createForm(CommentsFormType::class, $comment);
        $commentForm->handleRequest($request);

        $routeParameters = $request->attributes->get('_route_params');

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setCourse($course)
                ->setUser($user);

            // On récupère le contenu du champ parent
            $parentid = $commentForm->get("parent")->getData();
            // On va chercher le commentaire correspondant
            if ($parentid != null) {
                $parent = $commentsRepository->find($parentid);
            }
            // On définit le parent
            $comment->setParent($parent ?? null);

            $this->em->persist($comment);
            $this->em->flush();

            $this->addFlash('success', 'Votre commentaire a bien été envoyé');
            return $this->redirectToRoute('courses_show', ['program_slug' => $routeParameters['program_slug'], 'section_slug' => $routeParameters['section_slug'], 'slug' => $course->getSlug()]);
        }

        return $this->render('courses/show.html.twig', [
            'course' => $course,
            'sections' => $sections,
            'lesson' => $lesson,
            'form' => $form,
            'nbrCourses' => $nbrCourses,
            'nbrLessonsDone' => $lessonsDoneForProgram,
            // 'lessons' => $this->lessonRepository->findBy(['user' => $user->getId()]),
            'lessons' => $user ? $this->lessonRepository->findBy(['user' => $user->getId()]) : [],
            'fileContent' => $content,
            'commentForm' => $commentForm,
            'countComments' => $countComments,
            'navigation' => $navigation,
            'sectionsTotalDuration' => $sectionsTotalDuration,
            'questions' => $quizData['questions'],
            'count' => $quizData['count'],
            'csrf_token' => $quizData['csrf_token'],
            'section' => $section,
            'quizResults' => $quizAttemptResults['quizResults'],
            'totalQuestions' => $quizAttemptResults['totalQuestions'],
            'attemptId' => $attemptId,
        ], $response);
    }
}
