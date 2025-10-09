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
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, $tz);

        $errors = \DateTimeImmutable::getLastErrors() ?: ['warning_count' => 0, 'error_count' => 0];
        if (!$day || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
            return $this->json(['error' => 'Date invalide (format attendu: YYYY-MM-DD)'], 422);
        }

        // 3) Slots
        try {
            $available = $slots->getAvailableSlots($type, $day);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
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

    #[Route('/fixed-slots-range', name: 'api_fixed_slots_range', methods: ['GET'])]
    public function fixedSlotsRange(
        Request $request,
        SlotService $slots,
        AppointmentTypeRepository $types
    ): JsonResponse {
        $typeId = (int) $request->query->get('type');
        $start  = (string) $request->query->get('start'); // YYYY-MM-DD
        $end    = (string) $request->query->get('end');   // YYYY-MM-DD (exclu)
        if ($typeId <= 0 || $start === '' || $end === '') {
            return $this->json(['error' => 'Paramètres requis: type, start, end (YYYY-MM-DD)'], 422);
        }

        $type = $types->find($typeId);
        if (!$type) return $this->json(['error' => 'Type introuvable'], 404);

        $tz = new \DateTimeZone('Europe/Paris');
        $from = \DateTimeImmutable::createFromFormat('!Y-m-d', $start, $tz);
        $to   = \DateTimeImmutable::createFromFormat('!Y-m-d', $end, $tz);
        if (!$from || !$to || $to <= $from) {
            return $this->json(['error' => 'Période invalide'], 422);
        }

        $events = [];
        for ($d = $from; $d < $to; $d = $d->modify('+1 day')) {
            foreach ($slots->getAvailableSlots($type, $d) as $s) {
                if ($s['end'] <= new \DateTimeImmutable('now', $tz)) continue; // coupe le passé côté serveur
                $events[] = [
                    'start' => $s['start']->format(\DateTimeInterface::RFC3339),
                    'end'   => $s['end']->format(\DateTimeInterface::RFC3339),
                ];
            }
        }

        $response = $this->json($events);
        $response->headers->set('Cache-Control', 'public, max-age=30');
        return $response;
    }
}
