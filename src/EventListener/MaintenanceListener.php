<?php

namespace App\EventListener;

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
    public function __construct(protected bool $maintenance, protected Security $security, protected Environment $twig, protected RouterInterface $router, protected CacheInterface $cache)
    {}

    public function __invoke(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->maintenance) {
            return;
        }

        if ($request->isXmlHttpRequest()) {
            return;
        }

        $maintenance = $this->cache->get('maintenance_mode', function (ItemInterface $item) {
            $item->expiresAfter(300);
            return true;
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
            // $response->headers->set('Symfony-Debug-Toolbar-Replace', '1');
            $event->setResponse($response);
            $event->stopPropagation();
        }
    }
}