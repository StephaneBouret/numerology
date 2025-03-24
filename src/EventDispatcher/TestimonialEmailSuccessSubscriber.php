<?php

namespace App\EventDispatcher;

use App\Event\TestimonialSuccessEvent;
use App\Service\SendMailService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestimonialEmailSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(protected SendMailService $sendMailService, protected string $defaultFrom)
    {}

    public static function getSubscribedEvents(): array
    {
        return [
            'testimonial.success' => 'sendTestimonialEmail'
        ];
    }

    public function sendTestimonialEmail(TestimonialSuccessEvent $testimonialSuccessEvent)
    {
        $testimonial = $testimonialSuccessEvent->getTestimonial();

        $this->sendMailService->sendMail(
            null,
            'Demande de modération',
            $this->defaultFrom,
            'Demande de modération',
            'testimonial',
            [
                'testimonial' => $testimonial
            ]
        );
    }
}