<?php

namespace App\Enums;

enum BagianEnum: string
{
    case PBJ1 = 'PBJ1';
    case PBJ2 = 'PBJ2';
    case PJDP = 'PJDP';
    case VM = 'VM';
    case VME = 'VME';

    public function label(): string
    {
        return match($this) {
            self::PBJ1 => 'PBJ1',
            self::PBJ2 => 'PBJ2',
            self::VM => 'VM',
            self::PJDP => 'PJDP',
            self::VME => 'VME',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PBJ1 => '#f63b3bff', // Blue
            self::PBJ2 => '#6366f1', // Indigo
            self::PJDP => '#22c55e', // Green
            self::VM => '#f97316',   // Orange
            self::VME => '#06b6d4',  // Cyan
        };
    }
}
