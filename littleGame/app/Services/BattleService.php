<?php

namespace App\Services;

use App\Data\BattleRoundResult;
use App\Models\Hero;
use App\Models\Monster;
use App\Services\Skills\RapidFire;
use App\Services\Skills\MagicArmour;

class BattleService
{
    public function simulate(): array
    {
        $hero = Hero::random();
        $monster = Monster::random();

        [$attacker, $defender] = $this->decideFirstAttacker($hero, $monster);

        $rounds = [];
        $maxRounds = 15;

        for ($round = 1; $round <= $maxRounds; $round++) {
            if (!$attacker->isAlive() || !$defender->isAlive()) {
                break;
            }

            $notes = [];
            $skillUsed = null;
            $totalDamage = 0;

            $attackTimes = 1;
            if ($attacker instanceof Hero && RapidFire::shouldTrigger($attacker)) {
                $attackTimes = 2;
                $skillUsed = 'Rapid Fire';
                $notes[] = "{$attacker->name} uses Rapid Fire and attacks twice.";
            }

            for ($i = 0; $i < $attackTimes; $i++) {

                if (!$defender->isAlive()) {
                    break;
                }

                if ($this->chance($defender->luck)) {
                    $notes[] = "{$defender->name} got lucky and dodged the hit.";
                    continue;
                }

                $damage = max(1, $attacker->strength - $defender->defence);

                if ($defender instanceof Hero && $damage > 0 && MagicArmour::shouldTrigger($defender)) {
                    $damage = MagicArmour::reduceDamage($damage);
                    $skillUsed = $skillUsed
                        ? $skillUsed . ' + Magic Armour'
                        : 'Magic Armour';

                    $notes[] = "{$defender->name} uses Magic Armour and takes half damage.";
                }

                if ($damage <= 0) {
                    $notes[] = 'No damage dealt.';
                    continue;
                }

                $defender->takeDamage($damage);
                $totalDamage += $damage;

                if (!$defender->isAlive()) {
                    $notes[] = "{$defender->name} has fallen.";
                    break;
                }
            }

            $rounds[] = new BattleRoundResult(
                round: $round,
                attacker: $attacker->name,
                defender: $defender->name,
                skillUsed: $skillUsed,
                damage: $totalDamage,
                defenderHealth: $defender->health,
                notes: $notes,
            );

            if (!$defender->isAlive()) {
                break;
            }

            [$attacker, $defender] = [$defender, $attacker];
        }

        $winner = null;
        if ($hero->isAlive() && !$monster->isAlive()) {
            $winner = 'hero';
        } elseif ($monster->isAlive() && !$hero->isAlive()) {
            $winner = 'monster';
        }

        return [
            'hero' => $this->characterToArray($hero),
            'monster' => $this->characterToArray($monster),
            'rounds' => array_map(
                fn (BattleRoundResult $r) => $r->toArray(),
                $rounds
            ),
            'winner' => $winner,
        ];
    }

    private function decideFirstAttacker(Hero $hero, Monster $monster): array
    {
        if ($hero->speed > $monster->speed) {
            return [$hero, $monster];
        }

        if ($monster->speed > $hero->speed) {
            return [$monster, $hero];
        }

        if ($hero->luck >= $monster->luck) {
            return [$hero, $monster];
        }

        return [$monster, $hero];
    }

    private function characterToArray($c): array
    {
        return [
            'name' => $c->name,
            'health' => $c->health,
            'strength' => $c->strength,
            'defence' => $c->defence,
            'speed' => $c->speed,
            'luck' => $c->luck,
        ];
    }

    private function chance(float $probability): bool
    {
        return mt_rand(0, 10000) / 10000 <= $probability;
    }
}
