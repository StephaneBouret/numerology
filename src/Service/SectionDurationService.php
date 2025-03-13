<?php

namespace App\Service;

use App\Entity\Courses;

class SectionDurationService
{
    public function calculateTotalDuration(array $sections): array
    {
        $sectionsTotalDuration = [];

        foreach ($sections as $section) {
            $totalDuration = 0;

            foreach ($section->getCourses() as $course) {
                if ($course->getContentType() === Courses::TYPE_VIDEO && $course->getDuration() !== null) {
                    $totalDuration += $course->getDuration();
                }
            }

            $sectionsTotalDuration[$section->getId()] = $totalDuration;
        }

        return $sectionsTotalDuration;
    }
}