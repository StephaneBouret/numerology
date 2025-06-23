<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\UserDevice;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\TooManySessionsException;
use App\Service\DeviceService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CheckDevicesSubscriber implements EventSubscriberInterface
{
    private const MAX_TOTAL_DEVICES = 2; // Limite totale de connexions
    private const MAX_PER_TYPE = 1; // Limite par type (1 desktop, 1 mobile, 1 tablette)

    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        protected RouterInterface $router,
        protected Security $security,
        private DeviceService $deviceService,
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

        $deviceFingerprint = $this->deviceService->generateDeviceFingerprint($request);
        $deviceType = $this->deviceService->getDeviceType($request);
        $browserInfo = $this->deviceService->getBrowserAndPlatform($request);

        $browser = $browserInfo['browser'];
        $platform = $browserInfo['platform'];

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
        if (in_array($currentRoute, [
            'error_too_many_sessions',
            '2fa_login',
            '2fa_login_check',
            'app_remove_device',
        ])) {
            return; // Empêche une boucle infinie
        }

        $deviceFingerprint = $this->deviceService->generateDeviceFingerprint($request);
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
}
