<?php

namespace App\Models;

class Monster extends Character
{
    public static function random(): self
    {
        $health = rand(50, 80);
        $strength = rand(55, 80);
        $defence = rand(50, 70);
        $speed = rand(40, 60);
        $luck = rand(30, 45) / 100;

        return new self(
            name: 'Monster',
            health: $health,
            strength: $strength,
            defence: $defence,
            speed: $speed,
            luck: $luck
        );
    }
}
