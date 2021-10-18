<?php

declare(strict_types=1);

namespace Tests;

use GildedRose\GildedRose;
use GildedRose\Item;
use PHPUnit\Framework\TestCase;

class GildedRoseTest extends TestCase
{
    protected array $items = [];

    /**
     * @var GildedRose
     */
    protected $app;

    protected function setUp(): void
    {
        $this->items = [
            new Item('+5 Dexterity Vest', 10, 20),
            new Item('Aged Brie', 2, 0),
            new Item('Elixir of the Mongoose', 5, 7),
            new Item('Sulfuras, Hand of Ragnaros', 0, 80),
            new Item('Sulfuras, Hand of Ragnaros', -1, 80),
            new Item('Backstage passes to a TAFKAL80ETC concert', 15, 20),
            new Item('Backstage passes to a TAFKAL80ETC concert', 10, 49),
            new Item('Backstage passes to a TAFKAL80ETC concert', 5, 49),
            new Item('Conjured Mana Cake', 3, 6),
        ];

        $this->app = new GildedRose($this->items);
    }

    public function testFoo(): void
    {
        $items = [new Item('foo', 0, 0)];
        $gildedRose = new GildedRose($items);
        $gildedRose->updateItems();
        $this->assertSame('foo', $items[0]->name);
    }

    public function testItCreatesItems(): void
    {
        $this->assertCount(9, $this->app->getItems());
    }

    public function testValueDecreaseByOneEveryday(): void
    {
        $this->app->updateItems();
        $this->assertSame(19, $this->app->getItems()[0]->quality);
    }

    public function testSellInDecreaseByOneEveryday(): void
    {
        $this->app->updateItems();
        $this->assertSame(9, $this->app->getItems()[0]->sell_in);
    }

    public function testQualityCannotBeNegative(): void
    {
        // | name | sell | quality |
        $item = new Item('Mana', 1, 4);
        $gild = new GildedRose([$item]);

        $this->updateGild($gild, 100);

        foreach ($gild->getItems() as $item) {
            $this->assertSame(0, $item->quality);
        }
    }

    public function testQualityCannotExceedMaxLimitOf50(): void
    {
        $item = new Item('Aged Brie', 1, 4);
        $gild = new GildedRose([$item]);

        $this->updateGild($gild, 100);

        $this->assertSame(50, $gild->getItems()[0]->quality);
    }

    public function testQualityDecreasesFasterWhenSellInOverdue(): void
    {
        // | name | sell | quality |
        $item = new Item('Mana', 1, 4);
        $gild = new GildedRose([$item]);

        $gild->updateItems();

        $this->assertSame(0, $item->sell_in);
        $this->assertSame(3, $item->quality);

        $gild->updateItems();

        $this->assertSame(-1, $item->sell_in);
        $this->assertSame(1, $item->quality);

        $gild->updateItems();

        $this->assertSame(-2, $item->sell_in);
        $this->assertSame(0, $item->quality);
    }

    public function testQualityOfBrieIncreaseOverTime(): void
    {
        // | name | sell | quality |
        $item = new Item('Aged Brie', 1, 10);
        $gild = new GildedRose([$item]);

        $gild->updateItems();

        $this->assertSame(0, $item->sell_in);
        $this->assertSame(11, $item->quality);

        $gild->updateItems();

        $this->assertSame(-1, $item->sell_in);
        $this->assertSame(13, $item->quality);
    }

    public function testSulfurasObjectNeverUpdates(): void
    {
        $item = new Item('Sulfuras, Super Stuff', 1, 10);
        $gild = new GildedRose([$item]);

        $this->updateGild($gild, 100);

        $this->assertSame(1, $item->sell_in);
        $this->assertSame(10, $item->quality);
    }

    public function testBackstagePassIncreaseQualityUntilExpirationLimit(): void
    {
        $item = new Item('Backstage passes to a TAFKAL80ETC concert', 12, 10);
        $gild = new GildedRose([$item]);

        $gild->updateItems();

        $this->assertSame(11, $item->sell_in);
        $this->assertSame(11, $item->quality);

        $gild->updateItems();
        $this->assertSame(10, $item->sell_in);
        $this->assertSame(13, $item->quality);

        $this->updateGild($gild, 4);
        $this->assertSame(6, $item->sell_in);
        $this->assertSame(21, $item->quality);

        $this->updateGild($gild, 2);
        $this->assertSame(4, $item->sell_in);
        $this->assertSame(27, $item->quality);

        $this->updateGild($gild, 6);
        $this->assertSame(-2, $item->sell_in);
        $this->assertSame(0, $item->quality);
    }

    public function testQualityOfConjuredItemsDegradesTwiceFasterThanRegularObject(): void
    {
        $regular = new Item('Regular object', 10, 50);
        $conjured = new Item('Conjured object', 10, 50);
        $gild = new GildedRose([$regular, $conjured]);

        $gild->updateItems();

        $this->assertSame(9, $conjured->sell_in);
        $this->assertSame(48, $conjured->quality);

        $this->assertSame(9, $regular->sell_in);
        $this->assertSame(49, $regular->quality);

        $this->updateGild($gild, 9);

        $this->assertSame(0, $conjured->sell_in);
        $this->assertSame(30, $conjured->quality);

        $this->assertSame(0, $regular->sell_in);
        $this->assertSame(40, $regular->quality);

        $gild->updateItems();

        $this->assertSame(-1, $conjured->sell_in);
        $this->assertSame(26, $conjured->quality);

        $this->assertSame(-1, $regular->sell_in);
        $this->assertSame(38, $regular->quality);
    }

    private function updateGild(GildedRose $gild, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $gild->updateItems();
        }
    }
}
