<?php

namespace App\Controller\Course;

use App\Repository\CoursesRepository;
use App\Repository\ProgramRepository;
use App\Repository\SectionsRepository;
use App\Service\SectionDurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FreeCourseController extends AbstractController
{
    public function __construct(protected CoursesRepository $coursesRepository, protected ProgramRepository $programRepository, protected SectionsRepository $sectionsRepository, protected SectionDurationService $sectionDurationService)
    {}

    #[Route('/summary/free/{slug}', name: 'app_free_course', priority: -1)]
    public function index($slug): Response
    {
        $program = $this->programRepository->findOneBy([
            'slug' => $slug
        ]);
        if (!$program) {
            throw $this->createNotFoundException("Le programme demandé n'existe pas");
        }

        $sections = $this->sectionsRepository->findBy([
            'program' => $program
        ]);

        $sectionsTotalDuration = $this->sectionDurationService->calculateTotalDuration($sections);

        $coursesBySection = $this->coursesRepository->countCoursesBySections();

        return $this->render('free/index.html.twig', [
            'program' => $program,
            'sections' => $sections,
            'coursesBySection' => $coursesBySection,
            'sectionsTotalDuration' => $sectionsTotalDuration,
        ]);
    }
}
