<?php

declare(strict_types=1);

namespace Trade\Inventory;

use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use Trade\Merchant\MerchantRecipe;

class TradeInventory extends SimpleInventory {

    protected Player $holder;

    public const INPUT_A = 0; // Item que o jogador oferece
    public const INPUT_B = 1; // Segundo item (opcional)
    public const SELL    = 2; // Item que o jogador recebe

    public function __construct(Player $holder){
        parent::__construct(3); // 3 slots
        $this->holder = $holder;
    }

    public function getHolder(): Player{
        return $this->holder;
    }

    // ==============
    // SLOT HANDLING
    // ==============

    public function setBuyA(Item $item): void{
        $this->setItem(self::INPUT_A, $item);
    }

    public function setBuyB(Item $item): void{
        $this->setItem(self::INPUT_B, $item);
    }

    public function setSell(Item $item): void{
        $this->setItem(self::SELL, $item);
    }

    public function getBuyA(): Item{
        return $this->getItem(self::INPUT_A);
    }

    public function getBuyB(): Item{
        return $this->getItem(self::INPUT_B);
    }

    public function getSell(): Item{
        return $this->getItem(self::SELL);
    }

    // =====================
    // RECEITA DO COMÉRCIO
    // =====================

    /**
     * Recebe um MerchantRecipe e atualiza os slots do inventário
     */
    public function applyRecipe(MerchantRecipe $recipe): void {
        $this->setBuyA($recipe->getBuyA());
        $this->setBuyB($recipe->getBuyB() ?? null);
        $this->setSell($recipe->getSell());

    }

    /**
     * Retorna todas as ofertas no formato NBT (usado em UpdateTradePacket)
     */
    public function getOffersNbt(): ListTag {
        $list = new ListTag();

        $recipe = new CompoundTag();
        $recipe->setTag("buyA", $this->getBuyA()->nbtSerialize());
        $recipe->setTag("buyB", $this->getBuyB()->nbtSerialize());
        $recipe->setTag("sell", $this->getSell()->nbtSerialize());

        $list->push($recipe);
        return $list;
    }
}
