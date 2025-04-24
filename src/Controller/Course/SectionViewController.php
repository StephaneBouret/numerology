<?php

namespace App\Controller\Course;

use App\Entity\User;
use App\Repository\LessonRepository;
use App\Repository\CoursesRepository;
use App\Repository\ProgramRepository;
use App\Repository\SectionsRepository;
use App\Service\SectionDurationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SectionViewController extends AbstractController
{
    public function __construct(protected CoursesRepository $coursesRepository, protected SectionsRepository $sectionsRepository, protected EntityManagerInterface $em, protected LessonRepository $lessonRepository, protected SectionDurationService $sectionDurationService, protected ProgramRepository $programRepository)
    {
    }

    #[Route('/courses/{program_slug}/{slug}', name: 'courses_section', priority: -1)]
    public function section($slug, $program_slug): Response
    {
        /** @var User */
        $user = $this->getUser();
        $program = $this->programRepository->findOneBy([
            'slug' => $program_slug
        ]);
        $sections = $this->sectionsRepository->findBy([
            'program' => $program
        ]);
        $section = $this->sectionsRepository->findOneBy([
            'slug' => $slug
        ]);

        $this->denyAccessUnlessGranted('SECTION_VIEW', $section, "Vous n'avez pas accès à cette section");

        $sectionsTotalDuration = $this->sectionDurationService->calculateTotalDuration($sections);

        $count = $this->coursesRepository->countNumberCoursesBySection($section);
        $nbrCourses = $this->coursesRepository->countByProgram($program);
        $lessonsDoneForProgram = $user ? $this->lessonRepository->countLessonsDoneByUserAndProgram($user, $program) : 0;

        if (!$section) {
            throw $this->createNotFoundException("La section demandée n'existe pas");
        }

        return $this->render('courses/section.html.twig', [
            'section' => $section,
            'sections' => $sections,
            'count' => $count,
            'nbrCourses' => $nbrCourses,
            'nbrLessonsDone' => $lessonsDoneForProgram,
            // 'lessons' => $this->lessonRepository->findBy(['user' => $user->getId()]),
            'lessons' => $user ? $this->lessonRepository->findBy(['user' => $user->getId()]) : [],
            'sectionsTotalDuration' => $sectionsTotalDuration,
        ]);
    }
}
