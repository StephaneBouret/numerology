<?php

namespace App\EventListener;

use App\Repository\SettingRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;

#[AsEventListener(event: RequestEvent::class)]
class MaintenanceListener
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly CacheInterface $cache,
        private readonly Security $security,
        private readonly Environment $twig,
        private readonly RouterInterface $router
    ) {
    }

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if ($request->isXmlHttpRequest()) {
            return;
        }

        // Autorise les routes du profiler
        if (str_starts_with($request->getPathInfo(), '/_wdt') || str_starts_with($request->getPathInfo(), '/_profiler')) {
            return;
        }

        $maintenance = $this->cache->get('maintenance_mode', function (ItemInterface $item) {
            $item->expiresAfter(300);
            return $this->settingRepository->findOneBy(['settingKey' => 'maintenance'])?->getValue() ?? false;
        });

        $adminRoutes = ['/admin', '/login', '/2fa', '/2fa_check', '/contact'];

        foreach ($adminRoutes as $route) {
            if (str_starts_with($request->getPathInfo(), $route)) {
                return;
            }
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        if ($maintenance) {
            $response = new Response(
                $this->twig->render('maintenance/index.html.twig'),
                Response::HTTP_SERVICE_UNAVAILABLE
            );

            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}
