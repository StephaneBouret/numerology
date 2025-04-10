<?php

namespace App\Service;

use App\Entity\Lesson;
use App\Entity\User;
use App\Repository\CoursesRepository;
use App\Repository\LessonRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class LessonService
{
    public function __construct(
        protected LessonRepository $lessonRepository,
        protected CoursesRepository $coursesRepository,
        protected EntityManagerInterface $em
    ) {}

    public function handleLessonUpdate($courseSlug, User $user)
    {
        $course = $this->coursesRepository->findOneBy([
            'slug' => $courseSlug
        ]);
        $lesson = $this->lessonRepository->getLessonByUserByCourse($user, $course);
        $now = new DateTimeImmutable();

        if (!$lesson) {
            $newLesson = new Lesson;
            $newLesson->setName($course->getName())
                ->setCourses($course)
                ->setStatus(Lesson::STATUS_DONE)
                ->setUser($user);
            $this->em->persist($newLesson);
            $this->em->flush();
        } else {
            $lesson->setStatus(Lesson::STATUS_DONE)
                ->setStudiedAt($now);
            $this->em->flush();
        }
    }
}