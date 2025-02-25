<?php

namespace App\Controller\Course;

use App\Entity\User;
use App\Entity\Lesson;
use App\Form\ButtonFormType;
use App\Repository\LessonRepository;
use App\Repository\CoursesRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CourseConfirmationController extends AbstractController
{
    public function __construct(protected CoursesRepository $coursesRepository, protected LessonRepository $lessonRepository, protected EntityManagerInterface $em)
    {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/course/confirmation/{id}', name: 'app_course_confirmation')]
    public function index($id, Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();

        $course = $this->coursesRepository->findOneBy([
            'id' => $id
        ]);

        /** @var Lesson */
        $lesson = $this->lessonRepository->getLessonByUserByCourse($user, $course);

        $form = $this->createForm(ButtonFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $now = new DateTimeImmutable();
            $verif = $form->get('status')->getData();
            if (!$lesson && $verif === 'todo') {
                $newLesson = new Lesson();
                $newLesson->setName($course->getName())
                    ->setCourses($course)
                    ->setStatus(Lesson::STATUS_DONE)
                    ->setUser($user);
                $this->em->persist($newLesson);
                $this->em->flush();
            } elseif ($lesson && $verif === 'done') {
                $lesson->setStatus(Lesson::STATUS_STUDY)
                ->setStudiedAt($now);
                $this->em->flush();
            } else {
                $lesson->setStatus(Lesson::STATUS_DONE)
                ->setStudiedAt($now);
                $this->em->flush();
            }
        }

        return $this->redirectToRoute('courses_show', [
            'program_slug' => $course->getProgram()->getSlug(),
            'section_slug' => $course->getSection()->getSlug(),
            'slug' => $course->getSlug(),
        ]);
    }
}
