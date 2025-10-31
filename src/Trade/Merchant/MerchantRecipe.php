<?php

/*
 * TradeAPI, simple to provide Trade UI V2.
 * Copyright (C) 2020  Organic (nnnlog)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Trade\Merchant;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;

class MerchantRecipe {

    /**
     * Adiciona valores ao CompoundTag da receita
     */
    private static function add(CompoundTag $tag, string $key, $value, $minValue = 0): void {
        if (is_int($value) || is_float($value)) {
            if ($value > $minValue) {
                $tag->setInt($key, (int)$value);
            }
        }

        if ($value instanceof Item) {
            $tag->setTag($key, $value->nbtSerialize(-1));
        }
    }

    private Item $buyA;
    private ?Item $buyB = null;
    private Item $sell;

    private int $maxUses = 999;
    private int $tier = -1;
    private int $buyCountA = -1;
    private int $buyCountB = -1;
    private int $uses = -1;
    private int $rewardExp = -1;
    private int $demand = -1;
    private int $traderExp = -1;
    private float $priceMultiplierA = -1.0;
    private float $priceMultiplierB = -1.0;

    public function __construct(Item $buyA, Item $sell, ?Item $buyB = null, int $tier = -1, int $buyCountA = -1, int $buyCountB = -1, int $maxUses = 999) {
        $this->buyA = $buyA;
        $this->sell = $sell;
        $this->buyB = $buyB;
        $this->tier = $tier;
        $this->buyCountA = $buyCountA;
        $this->buyCountB = $buyCountB;
        $this->maxUses = $maxUses;
    }

    // ======== SETTERS ========

    public function setBuyA(Item $buyA): void {
        $this->buyA = $buyA;
    }

    public function setBuyB(?Item $buyB): void {
        $this->buyB = $buyB;
    }

    public function setSell(Item $sell): void {
        $this->sell = $sell;
    }

    public function setTier(int $tier): void {
        $this->tier = $tier;
    }

    public function setBuyCountA(int $buyCountA): void {
        $this->buyCountA = $buyCountA;
    }

    public function setBuyCountB(int $buyCountB): void {
        $this->buyCountB = $buyCountB;
    }

    public function setMaxUses(int $maxUses): void {
        $this->maxUses = $maxUses;
    }

    // ======== GETTERS ========

    public function getBuyA(): Item {
        return $this->buyA;
    }

    public function getBuyB(): ?Item {
        return $this->buyB;
    }

    public function getSell(): Item {
        return $this->sell;
    }

    public function getTier(): int {
        return $this->tier;
    }

    public function getMaxUses(): int {
        return $this->maxUses;
    }

    // ======== SERIALIZAÇÃO ========

    public function toNBT(): CompoundTag {
        $tag = CompoundTag::create();

        self::add($tag, "buyA", $this->buyA);
        self::add($tag, "sell", $this->sell);
        self::add($tag, "buyB", $this->buyB);
        self::add($tag, "tier", $this->tier, -1);
        self::add($tag, "buyCountA", $this->buyCountA);
        self::add($tag, "buyCountB", $this->buyCountB);
        self::add($tag, "uses", max($this->uses, 0), -1);
        self::add($tag, "rewardExp", max($this->rewardExp, 0));
        self::add($tag, "demand", max($this->demand, 0));
        self::add($tag, "traderExp", max($this->traderExp, 0));
        self::add($tag, "priceMultiplierA", max($this->priceMultiplierA, 0.0));
        self::add($tag, "priceMultiplierB", max($this->priceMultiplierB, 0.0));

        return $tag;
    }
}
