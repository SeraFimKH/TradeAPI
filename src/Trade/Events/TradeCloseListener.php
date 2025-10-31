<?php





namespace Trade\Events;



use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\player\Player;


use Trade\TradeAPI;
class TradeCloseListener implements Listener
{

    /** @var TradeAPI */
    private $plugin;

    public function __construct(TradeAPI $plugin) {
        $this->plugin = $plugin;
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void {
        $pk = $event->getPacket();
        if ($pk instanceof ContainerClosePacket && $pk->windowId === 255) {
            $this->getPlugin()->closeWindow($event->getOrigin()->getPlayer(), false);
        } elseif ($pk instanceof ActorEventPacket && $pk->eventId !== ActorEvent::COMPLETE_TRADE) {
            $this->getPlugin()->doCloseInventory($this->getPlugin()->getInventory($event->getOrigin()->getPlayer()));
        }
    }

    public function onPlayerCommandPreprocess(CommandEvent $event): void {
        $player = $event->getSender();
        if (!$player instanceof Player) {
            return;
        }
        $this->getPlugin()->doCloseInventory($this->getPlugin()->getInventory($player));
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $this->getPlugin()->doCloseInventory($this->getPlugin()->getInventory($event->getPlayer()));
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $this->getPlugin()->doCloseInventory($this->getPlugin()->getInventory($event->getPlayer()));
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $this->getPlugin()->addInventory($event->getPlayer());
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $this->plugin->doCloseInventory($this->getPlugin()->getInventory($event->getPlayer()));
        $this->plugin->removeInventory($event->getPlayer());
    }


    /**
     * @return TradeAPI
     */
    private function getPlugin(): TradeAPI{
        return $this->plugin;
    }

}
