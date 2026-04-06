<?php

namespace App\Services\Skills;

use App\Models\Hero;

class MagicArmour extends Skill
{
    protected static float $chance = 0.15;

    public static function shouldTrigger(Hero $hero): bool
    {
        return static::triggers();
    }

    public static function reduceDamage(int $damage): int
    {
        return (int) ceil($damage / 2);
    }
}
