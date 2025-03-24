<?php

namespace App\Service;

use App\Entity\Testimonial;

class RatingService
{
    /**
     * Calcule les pourcentages des notes des témoignages.
     *
     * @param Testimonial[] $testimonials
     * @return array
     */
    public function calculateRatingPercentages(array $testimonials): array
    {
        // Initialisation des compteurs pour chaque note
        $counts = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0
        ];

        // Boucle à travers chaque témoignage
        foreach ($testimonials as $testimonial) {
            $rating = $testimonial->getRating();
            if (array_key_exists($rating, $counts)) {
                $counts[$rating]++;
            }
        }

        $totalTestimonials = array_sum($counts);
        $percentages = [];

        if ($totalTestimonials > 0) {
            foreach ($counts as $rating => $count) {
                $percentages[$rating] = ($count / $totalTestimonials) * 100;
            }
        } else {
            foreach ($counts as $rating => $count) {
                $percentages[$rating] = 0;
            }
        }
        
        return $percentages;
    }
}