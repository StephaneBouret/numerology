<?php

namespace App\Service;

use App\Entity\Program;

class ProgramDurationService
{
    public function calculateDuration(Program $program): int
    {
        $totalSeconds = 0;

        foreach ($program->getCourses() as $course) {
            $totalSeconds += $course->getDuration(); // La durée est en secondes
        }

        return ceil($totalSeconds / 3600); // Conversion en heures, arrondi vers le haut
    }
}