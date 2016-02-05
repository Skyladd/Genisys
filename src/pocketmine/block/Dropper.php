<?php
/**
 * Author: PeratX
 * QQ: 1215714524
 * Time: 2016/2/3 15:40
 * Copyright(C) 2011-2016 iTX Technologies LLC.
 * All rights reserved.
 *
 * OpenGenisys Project
 */
namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\EnumTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Dropper as TileDropper;
use pocketmine\tile\Tile;

class Dropper extends Solid implements ElectricalAppliance{

	protected $id = self::DROPPER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function getHardness() {
		return 3.5;
	}

	public function getName() : string{
		return "Dropper";
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$dispenser = null;
		//$this->meta = $player instanceof Player ? $player->getDirection() : 0;
		$this->meta = $face;

		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new CompoundTag("", [
			new EnumTag("Items", []),
			new StringTag("id", Tile::DROPPER),
			new IntTag("x", $this->x),
			new IntTag("y", $this->y),
			new IntTag("z", $this->z)
		]);
		$nbt->Items->setTagType(NBT::TAG_Compound);

		if($item->hasCustomName()){
			$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
		}

		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}

		Tile::createTile(Tile::DROPPER, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);

		return true;
	}

	public function activate(){
		$tile = $this->getLevel()->getTile($this);
		if($tile instanceof TileDropper){
			$tile->activate();
		}
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			$t = $this->getLevel()->getTile($this);
			$dispenser = null;
			if($t instanceof TileDropper){
				$dispenser = $t;
			}else{
				$nbt = new CompoundTag("", [
					new EnumTag("Items", []),
					new StringTag("id", Tile::DROPPER),
					new IntTag("x", $this->x),
					new IntTag("y", $this->y),
					new IntTag("z", $this->z)
				]);
				$nbt->Items->setTagType(NBT::TAG_Compound);
				$dispenser = Tile::createTile(Tile::DROPPER, $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
			}

			if($player->isCreative()){
				return true;
			}
			$player->addWindow($dispenser->getInventory());
		}

		return true;
	}

	public function getDrops(Item $item) : array {
		return [
			[$this->id, 0, 1],
		];
	}
}