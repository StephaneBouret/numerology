<?php

namespace App\Controller\Course;

use App\Entity\User;
use App\Repository\CoursesRepository;
use App\Repository\LessonRepository;
use App\Repository\ProgramRepository;
use App\Repository\SectionsRepository;
use App\Service\SectionDurationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SectionsController extends AbstractController
{
    public function __construct(protected CoursesRepository $coursesRepository, protected ProgramRepository $programRepository, protected SectionsRepository $sectionsRepository, protected LessonRepository $lessonRepository, protected SectionDurationService $sectionDurationService)
    {}

    // #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas le droit d\'accéder à cette page')]
    #[Route('/courses/{slug}', name: 'app_sections', priority: -1)]
    public function index($slug): Response
    {
        /** @var User */
        $user = $this->getUser();

        $program = $this->programRepository->findOneBy([
            'slug' => $slug
        ]);
        if (!$program) {
            throw $this->createNotFoundException("Le programme demandé n'existe pas");
        }

        $this->denyAccessUnlessGranted('PROGRAM_VIEW', $program, "Vous n'avez pas accès à ce programme");

        $sections = $this->sectionsRepository->findAll();
        $sectionsTotalDuration = $this->sectionDurationService->calculateTotalDuration($sections);
        $nbrCourses = $this->coursesRepository->countAll();
        $coursesBySection = $this->coursesRepository->countCoursesBySections();
        $nbrLessonsDone = $user ? $this->lessonRepository->countLessonsDoneByUser($user) : 0;

        return $this->render('sections/section.html.twig', [
            'program' => $program,
            'sections' => $sections,
            'coursesBySection' => $coursesBySection,
            'nbrCourses' => $nbrCourses,
            'nbrLessonsDone' => $nbrLessonsDone,
            // 'lessons' => $this->lessonRepository->findBy(['user' => $user->getId()]),
            'lessons' => $user ? $this->lessonRepository->findBy(['user' => $user->getId()]) : [],
            'sectionsTotalDuration' => $sectionsTotalDuration,
        ]);
    }
}
