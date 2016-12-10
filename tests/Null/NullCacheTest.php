<?php

namespace Hgraca\Cache\Test\Null;

use Hgraca\Cache\CacheInterface;
use Hgraca\Cache\Exception\CacheItemNotFoundException;
use Hgraca\Cache\Null\NullCache;
use PHPUnit_Framework_TestCase;

final class NullCacheTest extends PHPUnit_Framework_TestCase
{

    public function testNewCache_HasNoItems()
    {
        $cache = $this->setUpCache();
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_ITEM_COUNT]);
    }

    public function testSave_IncreasesCount()
    {
        $cache = $this->setUpCache();
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_ITEM_COUNT]);
        self::assertFalse($cache->save('key', 'data'));
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_ITEM_COUNT]);
    }

    public function testContains()
    {
        $cache = $this->setUpCache();
        $key   = 'key';
        self::assertFalse($cache->contains($key));
        $cache->save($key, 'data');
        self::assertFalse($cache->contains($key));
    }

    public function testDelete()
    {
        $cache = $this->setUpCache();
        $key   = 'key';
        $cache->save($key, 'data');
        self::assertTrue($cache->delete($key));
        self::assertFalse($cache->contains($key));
    }

    public function testFetch()
    {
        $cache = $this->setUpCache();
        $cache->save($key = 'key', $data = 'data');
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_HITS]);
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_MISSES]);
        self::expectException(CacheItemNotFoundException::class);
        self::assertEquals($data, $cache->fetch($key));
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_HITS]);
        self::assertEquals(1, $cache->getStats()[CacheInterface::STATS_MISSES]);
    }

    public function testGetStats()
    {
        $cache = $this->setUpCache();
        self::assertEquals(
            [
                CacheInterface::STATS_HITS,
                CacheInterface::STATS_MISSES,
                CacheInterface::STATS_UPTIME,
                CacheInterface::STATS_MEMORY_USAGE,
                CacheInterface::STATS_MEMORY_AVAILABLE,
                CacheInterface::STATS_ITEM_COUNT,
            ],
            array_keys($cache->getStats())
        );
    }

    private function setUpCache(): CacheInterface
    {
        return new NullCache();
    }
}
