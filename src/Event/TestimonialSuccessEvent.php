<?php

namespace App\Event;

use App\Entity\Testimonial;
use Symfony\Contracts\EventDispatcher\Event;

class TestimonialSuccessEvent extends Event
{
    public function __construct(protected Testimonial $testimonial)
    {}

    public function getTestimonial(): Testimonial
    {
        return $this->testimonial;
    }
}