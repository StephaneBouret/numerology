<?php

namespace App\Service;

use App\Entity\AppointmentType;
use App\Repository\AppointmentRepository;
use App\Repository\ScheduleSettingRepository;
use App\Repository\UnavailabilityRepository;
use App\Repository\UnavailableDayRepository;

class SlotService
{
    public function __construct(private AppointmentRepository $appointmentRepo, private ScheduleSettingRepository $settingRepo, private UnavailableDayRepository $unavailableDayRepo, private UnavailabilityRepository $unavailabilityRepo) {}

    /**
     * Retourne les créneaux libres (start/end en \DateTimeImmutable) pour un type et un jour donné
     *
     * @param AppointmentType $type
     * @param \DateTimeInterface $date
     * @return array
     */
    public function getAvailableSlots(AppointmentType $type, \DateTimeInterface $date): array
    {
        // 1) Jour bloqué par l'admin ? => aucun slot
        if ($this->unavailableDayRepo->isUnavailable($date)) {
            return [];
        }

        // 2) Jour bloqué via Unavailability allDay ?
        if ($this->unavailabilityRepo->hasAllDay($date)) {
            return [];
        }

        // 3) Paramètres hebdo
        $settings = $this->settingRepo->findAllKeyValue();
        $openDays = array_map('intval', explode(',', $settings['open_days'] ?? '1,2,3,4,5'));
        $dayOfWeek = (int)$date->format('N'); // 1=lundi, ...7=dimanche
        if (!in_array($dayOfWeek, $openDays, true)) {
            return []; // jour fermé
        }

        $slots = [];
        $morningStart = $settings['morning_start'] ?? '09:00';
        $morningEnd = $settings['morning_end'] ?? '12:00';
        $afternoonStart = $settings['afternoon_start'] ?? '14:00';
        $afternoonEnd = $settings['afternoon_end'] ?? '18:00';

        // Récupère les RDVs du jour + indispos horaires du jour
        $rdvs = $this->appointmentRepo->findByDate($date);
        $unavails = $this->unavailabilityRepo->findForDate($date);

        // Créneaux matin
        $slots = array_merge(
            $slots,
            $this->generateSlots($type, $date, $morningStart, $morningEnd, $rdvs, $unavails)
        );

        // Créneaux après-midi
        $slots = array_merge(
            $slots,
            $this->generateSlots($type, $date, $afternoonStart, $afternoonEnd, $rdvs, $unavails)
        );

        return $slots;
    }

    /**
     * Génère les créneaux pour une plage (ex: 09:00-12:00) en évitant les chevauchements
     *
     * @param AppointmentType $type
     * @param \DateTimeInterface $date
     * @param string $start
     * @param string $end
     * @param array $rdvs
     * @param array $unavails
     * @return array
     */
    private function generateSlots(AppointmentType $type, \DateTimeInterface $date, string $start, string $end, array $rdvs, array $unavails): array
    {
        $duration = (int) $type->getDuration(); // minutes
        $slots = [];

        // [$hStart, $mStart] = explode(':', $start);
        list($hStart, $mStart) = explode(':', $start);
        list($hEnd, $mEnd) = explode(':', $end);

        $startAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . " $hStart:$mStart");
        $endAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . " $hEnd:$mEnd");

        while (($startAt->getTimeStamp() + $duration * 60) <= $endAt->getTimeStamp()) {
            $slotEnd = (clone $startAt)->modify("+{$duration} minutes");
            $overlap = false;

            // 1) chevauche RDV existant ?
            foreach ($rdvs as $rdv) {
                if ($startAt < $rdv->getEndAt() && $slotEnd > $rdv->getStartAt()) {
                    $overlap = true;
                    break;
                }
            }

            // 2) Chevauche une indispo horaire ?
            if (!$overlap) {
                foreach ($unavails as $u) {
                    if ($startAt < $u->getEndAt() && $slotEnd > $u->getStartAt()) {
                        $overlap = true;
                        break;
                    }
                }
            }

            if (!$overlap) {
                $slots[] = [
                    'start' => (clone $startAt),
                    'end' => (clone $slotEnd),
                ];
            }

            // pas à pas (ex: 15 minutes entre départs)
            $startAt = $startAt->modify('+15 minutes'); // intervalle entre créneaux
        }

        return $slots;
    }
}
