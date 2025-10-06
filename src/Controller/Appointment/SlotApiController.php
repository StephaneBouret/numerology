<?php

namespace App\Controller\Appointment;

use App\Repository\AppointmentTypeRepository;
use App\Service\SlotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class SlotApiController extends AbstractController
{
    #[Route('/slots', name: 'api_slots', methods: ['GET'])]
    public function slots(
        Request $request,
        SlotService $slots,
        AppointmentTypeRepository $appointmentTypeRepo
    ): JsonResponse {
        // 1) Inputs
        $typeId = (int) ($request->query->get('type') ?? 0);
        $date = (string) $request->query->get('date'); // attendu: YYYY-MM-DD

        if ($typeId <= 0 || $date === '') {
            return $this->json(['error' => 'Paramètres requis: type (int>0) et date (YYYY-MM-DD)'], 422);
        }

        $type = $appointmentTypeRepo->find($typeId);
        if (!$type) {
            return $this->json(['error' => 'Type introuvable'], 404);
        }

        // 2) Date stricte Europe/Paris
        $tz  = new \DateTimeZone('Europe/Paris');
        $day = \DateTimeImmutable::createFromFormat('Y-m-d', $date, $tz);
        $errors = \DateTimeImmutable::getLastErrors();
        if (!$day || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
            return $this->json(['error' => 'Date invalide (format attendu: YYYY-MM-DD)'], 422);
        }

        // 3) Slots
        $available = $slots->getAvailableSlots($type, $day);
        // FullCAlendar attend un tableau d'events {start, end}
        $events = array_map(static fn(array $s) => [
            'start' => $s['start']->format(\DateTimeInterface::RFC3339),
            'end'   => $s['end']->format(\DateTimeInterface::RFC3339),
        ], $available);

        $response = $this->json($events);
        $response->headers->set('Cache-Control', 'no-store');
        return $response;
    }

    #[Route('/fixed-slots', name: 'api_fixed_slots', methods: ['GET'])]
    public function fixedSlots(
        Request $request,
        SlotService $slots,
        AppointmentTypeRepository $appointmentTypeRepo
    ): JsonResponse {
        // Alias: même logique que /slots
        return $this->slots($request, $slots, $appointmentTypeRepo);
    }
}
