<?php

namespace App\Data;

class BattleRoundResult
{
    public function __construct(
        public int $round,
        public string $attacker,
        public string $defender,
        public ?string $skillUsed,
        public int $damage,
        public int $defenderHealth,
        public array $notes = [],
    ) {}

    public function toArray(): array
    {
        return [
            'round' => $this->round,
            'attacker' => $this->attacker,
            'defender' => $this->defender,
            'skillUsed' => $this->skillUsed,
            'damage' => $this->damage,
            'defenderHealth' => $this->defenderHealth,
            'notes' => $this->notes,
        ];
    }
}
