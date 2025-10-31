<?php


namespace Trade\Events;


use muqsit\simplepackethandler\SimplePacketHandler;
use pocketmine\event\EventPriority;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\handler\ItemStackContainerIdTranslator;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\ItemStackRequestPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerUIIds;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackrequest\PlaceStackRequestAction;
use pocketmine\network\mcpe\protocol\types\inventory\stackresponse\ItemStackResponse;
use pocketmine\network\mcpe\protocol\types\inventory\TransactionData;
use Trade\Inventory\TradeInventory;
use Trade\TradeAPI;

class TradeListener implements Listener {

    public function onProcessTrade(DataPacketReceiveEvent $event): void {
        $player = $event->getOrigin()->getPlayer();
        $packet = $event->getPacket();

        if ($player === null) return;
        if (!$packet instanceof ItemStackRequestPacket) return;
        if (!TradeAPI::getInstance()->isTrading($player)) return;



        $handler = SimplePacketHandler::createInterceptor(TradeAPI::getInstance(), EventPriority::NORMAL, true);
        $handler->interceptIncoming(function(ItemStackRequestPacket $packet, NetworkSession $session): bool {
            $player = $session->getPlayer();
            $inventory = TradeAPI::getInventory($player);

            if ($inventory !== null) {
                foreach ($packet->getRequests() as $request) {
                    foreach ($request->getActions() as $action) {

                        if ($action instanceof PlaceStackRequestAction) {

                            if ($action->getSource()->getContainerName()->getContainerId() == ContainerUIIds::CURSOR){
                                $invPlayer = $player->getInventory()->getItem($action->getSource()->getSlotId());

                                var_dump($invPlayer->getName());
                            }






                            return false;
                        }
                    }
                }
            }
            return true;
        });

    }

}
