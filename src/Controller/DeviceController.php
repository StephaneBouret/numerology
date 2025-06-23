<?php

namespace App\Controller;

use App\Repository\UserDeviceRepository;
use App\Service\DeviceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DeviceController extends AbstractController
{
    public function __construct(protected UserDeviceRepository $userDevice, protected EntityManagerInterface $em, protected DeviceService $deviceService, private TokenStorageInterface $tokenStorage)
    {}

    #[Route('/account/devices', name: 'app_device')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function index(): Response
    {
        $user = $this->getUser();
        $devices = $this->userDevice->findBy(['user' => $user]);

        return $this->render('device/index.html.twig', [
            'devices' => $devices,
        ]);
    }

    #[Route('/account/remove/{id}', name: 'app_remove_device')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page')]
    public function removeDevice(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $device = $this->userDevice->find($id);
        if (!$device || $device->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // Empreinte de l'appareil courant
        $currentFingerPrint = $this->deviceService->generateDeviceFingerprint($request);
        // Vérification : est-ce que l'appareil supprimé est l'actuel ?
        $isCurrentDevice = $device->getDeviceFingerprint() === $currentFingerPrint;

        $this->em->remove($device);
        $this->em->flush();

        if ($isCurrentDevice) {
            // Déconnexion de l'utilisateur
            $this->tokenStorage->setToken(null);
            $request->getSession()->invalidate();
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('success', 'Appareil supprimé avec succès.');
        return $this->redirectToRoute('app_device');
    }
}
