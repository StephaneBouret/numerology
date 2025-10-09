<?php

namespace App\Service;

use App\Entity\AppointmentType;
use App\Enum\AppointmentStatus;
use App\Repository\AppointmentRepository;
use App\Repository\ScheduleSettingRepository;
use App\Repository\UnavailabilityRepository;
use App\Repository\UnavailableDayRepository;

/**
 * Génère les créneaux disponibles pour un type de RDV et une date donnée.
 * - Respecte les jours fermés (open_days), les plages horaires matin/après-midi,
 * - Exclut les jours off (admin) et les indispos horaires,
 * - Applique le mode "créneaux fixes" (cadence = durée + buffer) ou un pas libre,
 * - Exclut les RDV CONFIRMED existants en leur appliquant un buffer (avant/après).
 */
final class SlotService
{
    public function __construct(
        private AppointmentRepository $appointmentRepo,
        private ScheduleSettingRepository $settingRepo,
        private UnavailableDayRepository $unavailableDayRepo,
        private UnavailabilityRepository $unavailabilityRepo,
    ) {}

    /**
     * Retourne les créneaux libres (start/end en \DateTimeImmutable) pour un type et un jour donné
     *
     * @param AppointmentType $type
     * @param \DateTimeInterface $date
     * @return array<int, array{start:\DateTimeImmutable,end:\DateTimeImmutable}>
     */
    public function getAvailableSlots(AppointmentType $type, \DateTimeInterface $date): array
    {
        // 1) Jour bloqué par l'admin ? => aucun slot
        if ($this->unavailableDayRepo->isUnavailable($date)) {
            return [];
        }

        // 2) Jour bloqué via indispo "all day" ?
        if ($this->unavailabilityRepo->hasAllDay($date)) {
            return [];
        }

        // 3) Filtrage par jours ouverts (open_days: ex "1,2,3,4,5"; 1 = lundi)
        $openDays  = array_map('intval', explode(',', (string) ($this->settingRepo->get('open_days', '1,2,3,4,5') ?: '1,2,3,4,5')));
        $dayOfWeek = (int) $date->format('N'); // 1=lundi, ...7=dimanche
        if (!in_array($dayOfWeek, $openDays, true)) {
            return []; // jour fermé
        }

        // 4) Créneaux d'ouverture
        $morningStart    = (string) ($this->settingRepo->get('morning_start', '09:00') ?: '09:00');
        $morningEnd      = (string) ($this->settingRepo->get('morning_end', '12:00')   ?: '12:00');
        $afternoonStart  = (string) ($this->settingRepo->get('afternoon_start', '14:00') ?: '14:00');
        $afternoonEnd    = (string) ($this->settingRepo->get('afternoon_end', '18:00')   ?: '18:00');

        // 5) Données du jour (RDV confirmés + indispos horaires)
        $appointments = $this->appointmentRepo->findByDate($date); // idéalement: CONFIRMED only
        // Par sureté, on filtre ici si le repo renvoie d'autres statuts :
        $appointments = array_filter($appointments, function ($a) {
            if (!method_exists($a, 'getStatus')) return true;
            $st = $a->getStatus();
            return in_array($st, [AppointmentStatus::PENDING, AppointmentStatus::CONFIRMED], true);
        });
        $unavails     = $this->unavailabilityRepo->findForDate($date);

        // 6) Génération des slots (matin + après-midi)
        $slots = [];
        // Créneaux matin
        $slots = array_merge($slots, $this->generateSlots($type, $date, $morningStart, $morningEnd, $appointments, $unavails));
        // Créneaux après-midi
        $slots = array_merge($slots, $this->generateSlots($type, $date, $afternoonStart, $afternoonEnd, $appointments, $unavails));

        return $slots;
    }

    /**
     * Génère les créneaux pour une plage (ex: 09:00-12:00) en évitant les chevauchements
     *
     * @param AppointmentType $type
     * @param \DateTimeInterface $date (seule la date est utilisée)
     * @param string $start "HH:MM"
     * @param string $end "HH:MM"
     * @param array<int,object>  $appointments objets avec getStartAt()/getEndAt()
     * @param array<int,object>  $unavails     objets avec getStartAt()/getEndAt() (même jour)
     * @return array<int, array{start:\DateTimeImmutable,end:\DateTimeImmutable}>
     */
    private function generateSlots(
        AppointmentType $type,
        \DateTimeInterface $date,
        string $start,
        string $end,
        array $appointments,
        array $unavails
    ): array {
        $duration = (int) $type->getDuration(); // minutes
        if ($duration <= 0) {
            return [];
        }

        // Lecture des réglages
        $buffer = max(0, $this->settingRepo->getInt('slot_buffer_minutes', 0));
        $fixed  = $this->settingRepo->getBool('fixed_slots', true); // ON par défaut
        $step   = $fixed ? ($duration + $buffer) : max(5, $this->settingRepo->getInt('slot_step_minutes', 15));

        // Construction avec TZ explicite (Europe/Paris)
        [$hStart, $mStart] = explode(':', $start);
        [$hEnd,   $mEnd]   = explode(':', $end);
        $tz = new \DateTimeZone('Europe/Paris');
        $windowStart = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . " $hStart:$mStart", $tz);
        $windowEnd     = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . " $hEnd:$mEnd", $tz);

        if (!$windowStart || !$windowEnd || $windowEnd <= $windowStart) {
            return [];
        }

        $slots = [];
        $stepInterval = new \DateInterval('PT' . $step . 'M');

        for ($cursor = $windowStart; ($cursor->getTimestamp() + $duration * 60) <= $windowEnd->getTimestamp(); $cursor = $cursor->add($stepInterval)) {
            $slotEnd = $cursor->modify("+{$duration} minutes");

            // 1) Indispos horaires (sans buffer)
            if ($this->overlapAny($cursor, $slotEnd, $unavails, 0)) {
                continue;
            }

            // 2) RDV confirmés (élargis du buffer avant/après)
            if ($this->overlapAny($cursor, $slotEnd, $appointments, $buffer)) {
                continue;
            }

            $slots[] = ['start' => $cursor, 'end' => $slotEnd];
        }

        return $slots;
    }

    private function overlapAny(\DateTimeImmutable $start, \DateTimeImmutable $end, iterable $periods, int $buffer): bool
    {
        foreach ($periods as $p) {
            $pStart = $p->getStartAt();
            $pEnd   = $p->getEndAt();
            if (!$pStart || !$pEnd) {
                continue;
            }

            if ($buffer > 0) {
                $pStart = $pStart->modify("-{$buffer} minutes");
                $pEnd   = $pEnd->modify("+{$buffer} minutes");
            }

            if ($pStart < $end && $pEnd > $start) {
                return true;
            }
        }
        return false;
    }
}
