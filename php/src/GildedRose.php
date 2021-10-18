<?php

declare(strict_types=1);

namespace GildedRose;

final class GildedRose
{
    public const QUALITY_MAX = 50;

    public const QUALITY_MIN = 0;

    public const QUALITY_EVOLUTION_PACE = 1;

    /**
     * @var Item[]
     */
    private $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function updateQuality(Item $item): void
    {
        if (preg_match('#^Aged Brie#', $item->name)) {
            $this->matureCheese($item);
        } elseif (preg_match('#^Backstage passes#', $item->name)) {
            $this->handlePassValue($item);
        } elseif (preg_match('#^Conjured#', $item->name)) {
            $item->sell_in >= 0 ? $item->quality -= self::QUALITY_EVOLUTION_PACE * 2 : $item->quality -= (self::QUALITY_EVOLUTION_PACE * 4);
        } else {
            $item->sell_in >= 0 ? $item->quality -= self::QUALITY_EVOLUTION_PACE : $item->quality -= (self::QUALITY_EVOLUTION_PACE * 2);
        }

        $item->quality < self::QUALITY_MIN ? $item->quality = self::QUALITY_MIN : false;
        $item->quality > self::QUALITY_MAX ? $item->quality = self::QUALITY_MAX : false;
    }

    public function matureCheese(Item $item): void
    {
        $item->sell_in >= 0 ? $item->quality += self::QUALITY_EVOLUTION_PACE : $item->quality += (self::QUALITY_EVOLUTION_PACE * 2);
    }

    public function updateSellIn(Item $item): void
    {
        --$item->sell_in;
    }

    public function updateItems(): void
    {
        foreach ($this->items as $item) {
            if (! str_contains($item->name, 'Sulfuras')) {
                $this->updateSellIn($item);
                $this->updateQuality($item);
            }
        }
    }

    private function handlePassValue(Item $item): void
    {
        if ($item->sell_in > 10) {
            $item->quality += self::QUALITY_EVOLUTION_PACE;
        } elseif ($item->sell_in > 5) {
            $item->quality += self::QUALITY_EVOLUTION_PACE * 2;
        } elseif ($item->sell_in >= 0) {
            $item->quality += self::QUALITY_EVOLUTION_PACE * 3;
        } else {
            $item->quality = 0;
        }
    }
}
