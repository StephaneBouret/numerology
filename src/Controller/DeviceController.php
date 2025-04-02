<?php

namespace App\Controller;

use App\Repository\UserDeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DeviceController extends AbstractController
{
    public function __construct(protected UserDeviceRepository $userDevice, protected EntityManagerInterface $em)
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
    public function removeDevice(int $id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $device = $this->userDevice->find($id);
        if (!$device || $device->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $this->em->remove($device);
        $this->em->flush();

        $this->addFlash('success', 'Appareil supprimé avec succès.');
        return $this->redirectToRoute('app_device');
    }
}
