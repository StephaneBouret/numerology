<?php

namespace App\Entity;

use App\Repository\ScheduleSettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleSettingRepository::class)]
class ScheduleSetting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $setting_key = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSettingKey(): ?string
    {
        return $this->setting_key;
    }

    public function setSettingKey(string $setting_key): static
    {
        $this->setting_key = $setting_key;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
