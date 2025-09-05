<?php

namespace App\Service;

use App\Entity\AppointmentType;
use App\Repository\AppointmentRepository;
use App\Repository\ScheduleSettingRepository;

class SlotService
{
    public function __construct(private AppointmentRepository $appointmentRepo, private ScheduleSettingRepository $settingRepo) {}

    public function getAvailableSlots(AppointmentType $type, \DateTimeInterface $date): array
    {
        // Paramètres admin :
        $settings = $this->settingRepo->findAllKeyValue();

        // 1=lundi, ...7=dimanche
        $dayOfWeek = (int)$date->format('N');
        $openDays = array_map('intval', explode(',', $settings['open_days'] ?? '1,2,3,4,5'));

        if (!in_array($dayOfWeek, $openDays, true)) {
            return []; // jour fermé
        }

        $slots = [];

        // Créneaux matin
        $morningStart = $settings['morning_start'] ?? '09:00';
        $morningEnd = $settings['morning_end'] ?? '12:00';

        $slots = array_merge(
            $slots,
            $this->generateSlots($type, $date, $morningStart, $morningEnd)
        );

        // Créneaux après-midi
        $afternoonStart = $settings['afternoon_start'] ?? '14:00';
        $afternoonEnd = $settings['afternoon_end'] ?? '18:00';

        $slots = array_merge(
            $slots,
            $this->generateSlots($type, $date, $afternoonStart, $afternoonEnd)
        );

        return $slots;
    }

    private function generateSlots(AppointmentType $type, \DateTimeInterface $date, string $start, string $end): array
    {
        $duration = $type->getDuration();
        $slots = [];

        list($hStart, $mStart) = explode(':', $start);
        list($hEnd, $mEnd) = explode(':', $end);

        $startAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . " $hStart:$mStart");
        $endAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . " $hEnd:$mEnd");

        $rdvs = $this->appointmentRepo->findByDate($date);

        while (($startAt->getTimeStamp() + $duration * 60) <= $endAt->getTimeStamp()) {
            $slotEnd = (clone $startAt)->modify("+{$duration} minutes");
            $overlap = false;

            foreach ($rdvs as $rdv) {
                if ($startAt < $rdv->getEndAt() && $slotEnd > $rdv->getStartAt()) {
                    $overlap = true;
                    break;
                }
            }

            if (!$overlap) {
                $slots[] = [
                    'start' => (clone $startAt),
                    'end' => (clone $slotEnd),
                ];
            }
            $startAt = $startAt->modify('+15 minutes'); // intervalle entre créneaux
        }

        return $slots;
    }
}
