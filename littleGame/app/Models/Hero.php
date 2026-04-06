<?php

namespace App\Models;

class Hero extends Character
{
    public static function random(): self
    {
        $health = rand(65, 100);
        $strength = rand(75, 90);
        $defence = rand(40, 50);
        $speed = rand(40, 50);
        $luck = rand(10, 20) / 100;

        return new self(
            name: 'Kratos',
            health: $health,
            strength: $strength,
            defence: $defence,
            speed: $speed,
            luck: $luck
        );
    }
}
