<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\UserDevice;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\TooManySessionsException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use UAParser\Parser;

class CheckDevicesSubscriber implements EventSubscriberInterface
{
    private const MAX_TOTAL_DEVICES = 2; // Limite totale de connexions
    private const MAX_PER_TYPE = 1; // Limite par type (1 desktop, 1 mobile, 1 tablette)

    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        protected RouterInterface $router,
        protected Security $security,
    ) {
    }

    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        $deviceType = $this->getDeviceType($request);
        $userAgent = $request->headers->get('User-Agent');

        $parser = Parser::create();
        $ua = $parser->parse($userAgent);
        $browser = $ua->ua->family;   // Exemple: Chrome, Firefox
        $platform = $ua->os->family;  // Exemple: Windows, macOS, Android

        $deviceRepository = $this->em->getRepository(UserDevice::class);
        $existingDevice = $deviceRepository->findOneBy(['user' => $user, 'deviceFingerprint' => $deviceFingerprint]);

        if ($existingDevice) {
            $existingDevice->updateLastUsed();
        } else {
            $existingDevices = $deviceRepository->findBy(['user' => $user]);

            // Vérifier la limite totale (max 2 appareils)
            if (count($existingDevices) >= self::MAX_TOTAL_DEVICES) {
                throw new TooManySessionsException();
            }

            // Vérifier la limite par type d'appareil
            $countDesktop = count(array_filter($existingDevices, fn ($d) => $d->getDeviceType() === 'desktop'));
            $countMobile = count(array_filter($existingDevices, fn ($d) => $d->getDeviceType() === 'mobile'));
            $countTablet = count(array_filter($existingDevices, fn ($d) => $d->getDeviceType() === 'tablet'));

            if (
                ($deviceType === 'desktop' && $countDesktop >= self::MAX_PER_TYPE) || 
                ($deviceType === 'mobile' && $countMobile >= self::MAX_PER_TYPE) || 
                ($deviceType === 'tablet' && $countTablet >= self::MAX_PER_TYPE)
            ) {
                throw new TooManySessionsException();
            }

            // Enregistrer le nouvel appareil
            $newDevice = new UserDevice($user, $deviceFingerprint, $deviceType, $browser, $platform);
            $this->em->persist($newDevice);
        }

        $this->em->flush();
    }

    public function onKernelRequestEvent(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return; // L'utilisateur n'est pas connecté
        }

        $request = $event->getRequest();
        $currentRoute = $request->attributes->get('_route');
        if ($currentRoute === 'error_too_many_sessions' || $currentRoute === '2fa_login' || $currentRoute === '2fa_login_check') {
            return; // Empêche une boucle infinie
        }

        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        $deviceRepository = $this->em->getRepository(UserDevice::class);
        $existingDevices = $deviceRepository->findBy(['user' => $user]);

        // Vérifier si l'appareil actuel est autorisé
        $isDeviceAllowed = false;
        foreach ($existingDevices as $device) {
            if ($device->getDeviceFingerprint() === $deviceFingerprint) {
                $isDeviceAllowed = true;
                break;
            }
        }

        if (!$isDeviceAllowed) {
            $event->setResponse(new RedirectResponse($this->router->generate('error_too_many_sessions')));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccessEvent',
            RequestEvent::class => 'onKernelRequestEvent'
        ];
    }

    private function generateDeviceFingerprint(Request $request): string
    {
        return hash(
            'sha256',
            $request->headers->get('User-Agent') . 
            $request->headers->get('Sec-Ch-Ua-Platform') // Plateforme (Windows, macOs, Android)
        );
    }

    private function getDeviceType(Request $request): string
    {
        $userAgent = $request->headers->get('User-Agent');
        if (preg_match('/ipad|android(?!.*mobile)|tablet|kindle|silk/i', $userAgent)) {
            return 'tablet';
        }
        return preg_match('/mobile|iphone|android/i', $userAgent) ? 'mobile' : 'desktop';
    }
}
