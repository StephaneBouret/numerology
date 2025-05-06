<?php

namespace App\Service;

use App\Entity\Program;
use App\Entity\User;

class CompletionService
{
    public function isProgramCompleted(User $user, Program $program): bool
    {
        if (count($program->getCourses()) === 0) {
            return false; // Aucun cours dans le programme
        }

        foreach ($program->getCourses() as $course) {
            if (!$course->isCompletedByUser($user)) {
                return false; // Si un cours n'est pas complété, le programme n'est pas complété
            }
        }

        return true;
    }
}