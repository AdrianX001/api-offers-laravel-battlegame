<?php

namespace App\Services\Skills;

use App\Models\Hero;

class RapidFire extends Skill
{
    protected static float $chance = 0.15;

    public static function shouldTrigger(Hero $hero): bool
    {
        return static::triggers();
    }
}
