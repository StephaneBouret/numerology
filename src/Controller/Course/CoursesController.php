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
use App\Repository\SectionsRepository;
use App\Service\CourseFileService;
use App\Service\SectionDurationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

final class CoursesController extends AbstractController
{
    public function __construct(protected CoursesRepository $coursesRepository, protected SectionsRepository $sectionsRepository, protected EntityManagerInterface $em, protected LessonRepository $lessonRepository, protected SectionDurationService $sectionDurationService)
    {
    }

    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas le droit d\'accéder à cette page')]
    #[Route('/courses/{program_slug}/{slug}', name: 'courses_section', priority: -1)]
    public function section($slug): Response
    {
        /** @var User */
        $user = $this->getUser();
        $sections = $this->sectionsRepository->findAll();
        $section = $this->sectionsRepository->findOneBy([
            'slug' => $slug
        ]);

        $this->denyAccessUnlessGranted('SECTION_VIEW', $section, "Vous n'avez pas accès à cette section");

        $sectionsTotalDuration = $this->sectionDurationService->calculateTotalDuration($sections);

        $count = $this->coursesRepository->countNumberCoursesBySection($section);
        $nbrCourses = $this->coursesRepository->countAll();
        $nbrLessonsDone = $user ? $this->lessonRepository->countLessonsDoneByUser($user) : 0;

        if (!$section) {
            throw $this->createNotFoundException("La section demandée n'existe pas");
        }

        return $this->render('courses/section.html.twig', [
            'section' => $section,
            'sections' => $sections,
            'count' => $count,
            'nbrCourses' => $nbrCourses,
            'nbrLessonsDone' => $nbrLessonsDone,
            // 'lessons' => $this->lessonRepository->findBy(['user' => $user->getId()]),
            'lessons' => $user ? $this->lessonRepository->findBy(['user' => $user->getId()]) : [],
            'sectionsTotalDuration' => $sectionsTotalDuration,
        ]);
    }

    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas le droit d\'accéder à cette page')]
    #[Route('/courses/{program_slug}/{section_slug}/{slug}', name: 'courses_show', priority: -1)]
    public function show($slug, Request $request, NavigationRepository $navigationRepository, CourseFileService $courseFileService, CommentsRepository $commentsRepository): Response
    {
        $currentUrl = $request->getUri();
        $response = new Response();
        $response->headers->setCookie(new Cookie('url_visited', $currentUrl, strtotime('+1 month')));

        /** @var User */
        $user = $this->getUser();

        $course = $this->coursesRepository->findOneBy([
            'slug' => $slug
        ]);
        // 🔒 Vérification des permissions via le Voter
        // $this->denyAccessUnlessGranted('VIEW_COURSE', $course, 'Vous n\'avez pas accès à ce cours.');

        $navigation = $navigationRepository->findAll();

        $content = $courseFileService->getFileContent($course);

        // Total des cours en BDD
        $nbrCourses = $this->coursesRepository->countAll();
        // Nombre de leçons effectuées par l'utilisateur connecté
        $nbrLessonsDone = $user ? $this->lessonRepository->countLessonsDoneByUser($user) : 0;

        $sections = $this->sectionsRepository->findAll();
        $sectionsTotalDuration = $this->sectionDurationService->calculateTotalDuration($sections);

        if (!$course) {
            throw $this->createNotFoundException("Le cours demandé n'existe pas");
        }

        // On veut récupérer la leçon en-cours par l'utilisateur connecté
        $lesson = $this->lessonRepository->getLessonByUserByCourse($user, $course);

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
            'nbrLessonsDone' => $nbrLessonsDone,
            // 'lessons' => $this->lessonRepository->findBy(['user' => $user->getId()]),
            'lessons' => $user ? $this->lessonRepository->findBy(['user' => $user->getId()]) : [],
            'fileContent' => $content,
            'commentForm' => $commentForm,
            'countComments' => $countComments,
            'navigation' => $navigation,
            'sectionsTotalDuration' => $sectionsTotalDuration,
        ], $response);
    }
}
