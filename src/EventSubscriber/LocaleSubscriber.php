<?php

namespace App\EventSubscriber;

use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TranslatableListener $translatableListener,
        private string $defaultLocale = 'en'
    )
    {}

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        // On vérifie si la langue est passée en paramètre de l'URL
        // if ($locale = $request->query->get('_locale')) {
        //     $request->setLocale($locale);
        // } else {
        //     $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        // }
        $locale = $request->query->get('_locale') ?? $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($locale);

        $this->translatableListener->setTranslatableLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
