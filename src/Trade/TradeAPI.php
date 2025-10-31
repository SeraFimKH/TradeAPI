<?php



namespace Trade;





use muqsit\simplepackethandler\SimplePacketHandler;
use pocketmine\entity\Entity;
use pocketmine\event\EventPriority;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ItemStackRequestPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\IntMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerUIIds;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\PlaceStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;


use Trade\Commands\TradeCommand;
use Trade\Events\TradeCloseListener;
use Trade\Events\TradeListener;
use Trade\Inventory\TradeInventory;
use Trade\Merchant\MerchantRecipeList;
use Trade\Merchant\TraderProperties;


class TradeAPI extends PluginBase
{

    /** @var TradeInventory */
    private static  $tradeInventory = [];

    /** @var TraderProperties[] */
    private $process = [];

    /** @var TradeAPI[] */
    private static TradeAPI $instance;


    protected function onLoad(): void{
       self::$instance = $this;
    }

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents(new TradeCloseListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new TradeListener(), $this);
        $this->getServer()->getCommandMap()->register("trade", new TradeCommand($this));


//        $handler = SimplePacketHandler::createInterceptor($this, EventPriority::NORMAL, true);
//        $handler->interceptIncoming(function(ItemStackRequestPacket $packet, NetworkSession $session): bool {
//            $player = $session->getPlayer();
//            $inventory = TradeAPI::getInventory($player);
//
//            if ($inventory !== null) {
//                foreach ($packet->getRequests() as $request) {
//                    foreach ($request->getActions() as $action) {
//                        if ($action instanceof PlaceStackRequestAction) {
//
//                            if ($action->getDestination()->getContainerName()->getContainerId() == ContainerUIIds::CURSOR){
//                                var_dump("pegando 100%");
//                            }
//
//
//
//                            return false;
//                        }
//                    }
//                }
//            }
//            return true;
//        });
    }

    protected function onDisable(): void
    {
        foreach (self::$tradeInventory as $name => $inventory) {
            if ($this->getServer()->getPlayerExact($name) instanceof Player) {
                $this->doCloseInventory($inventory);
            }
        }
    }

    public static function addInventory(Player $player): void {
        self::$tradeInventory[$player->getName()] = new TradeInventory($player);
    }

    public static function removeInventory(Player $player): void {
        if (isset(self::$tradeInventory[$player->getName()])) {
            unset(self::$tradeInventory[$player->getName()]);
        }
    }

    public static function getInventory(Player $player): ?TradeInventory {
        return self::$tradeInventory[$player->getName()] ?? null;
    }


    public function isTrading(Player $player): bool {
        return isset($this->process[$player->getName()]);
    }

    public function sendWindow(Player $player, MerchantRecipeList $recipeList, TraderProperties $properties): void {
        $this->closeWindow($player);

        $pk = new UpdateTradePacket();
        $pk->windowId = 255;
        $pk->windowType = WindowTypes::TRADING;
        $pk->windowSlotCount = 3;
        $pk->displayName = $properties->traderName;
        $pk->isV2Trading = true;
        $pk->isEconomyTrading = false;
        $pk->tradeTier = $properties->tradeTier;
        $pk->playerActorUniqueId = $player->getId();
        $network = new CacheableNbt(CompoundTag::create()
            ->setTag("Recipes", $recipeList->toNBT())
            ->setTag("TierExpRequirements", new ListTag([
                CompoundTag::create()->setInt("0", 0),
                CompoundTag::create()->setInt("1", 10),
                CompoundTag::create()->setInt("2", 60),
                CompoundTag::create()->setInt("3", 160),
                CompoundTag::create()->setInt("4", 310),
            ])));
        $pk->offers = $network;
        $metadata = [
            EntityMetadataProperties::TRADE_TIER         => new IntMetadataProperty($pk->tradeTier),
            EntityMetadataProperties::TRADE_XP           => new IntMetadataProperty($properties->xp),
            EntityMetadataProperties::MAX_TRADE_TIER     => new IntMetadataProperty($properties->maxTradeTier),
            EntityMetadataProperties::TRADING_PLAYER_EID => new IntMetadataProperty($player->getId())
        ];

        if ($properties->entity instanceof Entity) {
            $pk->traderActorUniqueId = $properties->entity->getId();

            foreach ($metadata as $k => $metadataProperty) {
                $properties->entity->getNetworkProperties()->setInt($k, $metadataProperty->getValue());
            }
        } else {
            $apk = new AddActorPacket();
            $apk->type = EntityIds::NPC;
            $apk->position = $player->getPosition()->add(0, -2, 0);
            $apk->metadata = $metadata;
            $apk->syncedProperties = new PropertySyncData([1, 1], [1.0, 1.0]);

            $properties->eid = $apk->actorUniqueId = $apk->actorRuntimeId = $pk->traderActorUniqueId = Entity::nextRuntimeId();

            $player->getNetworkSession()->sendDataPacket($apk);
        }

        $this->process[$player->getName()] = clone $properties;

        $player->getNetworkSession()->sendDataPacket($pk);
    }




    public function closeWindow(Player $player, bool $sendPacket = true): void {
        if (($prop = $this->process[$player->getName()] ?? null) instanceof TraderProperties) {
            if ($sendPacket) {
                $pk = new ContainerClosePacket();
                $pk->windowId = 3;

                $pk->windowType = WindowTypes::TRADING;
                $player->getNetworkSession()->sendDataPacket($pk);
            }

            if ($prop->entity instanceof Entity) {
                $prop->entity->getNetworkProperties()->setInt(EntityMetadataProperties::TRADING_PLAYER_EID, -1);
            } else {
                $pk = new RemoveActorPacket();
                $pk->actorUniqueId = $prop->eid;
                $player->getNetworkSession()->sendDataPacket($pk);
            }

            $this->doCloseInventory(self::getInventory($player));

            unset($this->process[$player->getName()]);
        }
    }



    public function doCloseInventory(TradeInventory $inventory): void {
        for ($slot = 0; $slot <= 1; $slot++) {
            $item = $inventory->getItem($slot);

            if ($inventory->canAddItem($item)) {
                $inventory->addItem($item);
            }
        }

        $inventory->clearAll();
    }

    public static function getInstance(): TradeAPI
    {
        return self::$instance;
    }



}