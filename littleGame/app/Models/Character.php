<?php

namespace App\Models;

class Character
{
    public function __construct(
        public string $name,
        public int $health,
        public int $strength,
        public int $defence,
        public int $speed,
        public float $luck
    ) {}

    public function isAlive(): bool
    {
        return $this->health > 0;
    }

    public function takeDamage(int $amount): void
    {
        $this->health = max(0, $this->health - $amount);
    }
}
