<?php

namespace App\EventListener;

use App\Exception\TooManySessionsException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ExceptionEvent::class, method: 'onKernelException')]
class ExceptionListener
{
    public function __construct(protected UrlGeneratorInterface $urlGenerator)
    {}

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof TooManySessionsException) {
            $response = new RedirectResponse($this->urlGenerator->generate('error_too_many_sessions'));
            $event->setResponse($response);
        }
    }
}