<?php

namespace App\Services\Skills;

abstract class Skill
{
    protected static float $chance = 0.0;

    protected static function triggers(): bool
    {
        return mt_rand(0, 10000) / 10000 <= static::$chance;
    }
}
