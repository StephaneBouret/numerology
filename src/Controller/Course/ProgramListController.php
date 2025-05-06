<?php

namespace App\Controller\Course;

use App\Entity\User;
use App\Repository\CoursesRepository;
use App\Repository\LessonRepository;
use App\Repository\ProgramRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProgramListController extends AbstractController
{
    #[Route('/courses', name: 'app_courses_list')]
    public function list(ProgramRepository $programRepository, CoursesRepository $coursesRepository, LessonRepository $lessonRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('home_index');
        }

        $programs = $programRepository->findAllWithSectionsAndCourses();

        // Total des cours par programme en BDD
        $nbrCoursesByProgram = [];
        foreach ($programs as $program) {
            $nbrCoursesByProgram[$program->getId()] = $coursesRepository->countByProgram($program);
        }
        // Nombre de leçons effectuées par l'utilisateur connecté
        $nbrLessonsDone = $lessonRepository->countLessonsDoneByUserGroupedByProgram($user);

        return $this->render('courses/list.html.twig', [
            'programs' => $programs,
            'nbrCoursesByProgram' => $nbrCoursesByProgram,
            'nbrLessonsDone' => $nbrLessonsDone
        ]);
    }
}
