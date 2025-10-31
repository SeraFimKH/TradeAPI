<?php

namespace Trade\Commands;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use Trade\Inventory\TradeInventory;
use Trade\Merchant\MerchantRecipe;
use Trade\Merchant\MerchantRecipeList;
use Trade\Merchant\TraderProperties;
use Trade\TradeAPI;

class TradeCommand extends BaseCommand
{
    public TradeAPI $plugin;

    public function __construct(TradeAPI $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct($plugin, "trade", "Teste", []);
        $this->setPermission("trade.op");
    }

    protected function prepare(): void
    {

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) {
            return;
        }


        $reciper = new MerchantRecipeList(
            new MerchantRecipe(VanillaItems::EMERALD()->setCount(2), VanillaItems::WHEAT(), null, 0),
            new MerchantRecipe(VanillaItems::EMERALD()->setCount(2), VanillaItems::WHEAT(), null, 0),
        );

        $prop = new TraderProperties();
        $prop->maxTradeTier = 3;
        $prop->tradeTier = 2;
        $prop->traderName = "[Test] Trade API";
        $prop->xp = intval($args[0] ?? mt_rand(0, 50));


        TradeAPI::getInstance()->sendWindow($sender, $reciper, $prop);
    }

    public function getPermission()
    {
        // TODO: Implement getPermission() method.
    }


    /**
     * @return TradeAPI
     */
    public function getPlugin(): TradeAPI
    {
        return $this->plugin;
    }
}