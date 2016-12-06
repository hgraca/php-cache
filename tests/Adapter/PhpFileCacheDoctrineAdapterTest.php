<?php

namespace Hgraca\Cache\Test\Adapter;

use Hgraca\Cache\Adapter\PhpFileCacheDoctrineAdapter;
use Hgraca\Cache\CacheInterface;
use Hgraca\Cache\Exception\CacheItemNotFoundException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;

final class CacheDoctrineAdapterTest extends PHPUnit_Framework_TestCase
{
    /** @var MockInterface|CacheInterface */
    private $cacheMock;

    /** @var MockInterface|PhpFileCacheDoctrineAdapter */
    private $adapter;

    public function setUp()
    {
        $this->cacheMock = Mockery::mock(CacheInterface::class);
        $this->adapter = new PhpFileCacheDoctrineAdapter($this->cacheMock);
    }

    public function testFetch()
    {
        $key = 'key';
        $data = 'data';

        $this->cacheMock->shouldReceive('fetch')->once()->with($key)->andReturn($data);

        self::assertEquals($data, $this->adapter->fetch($key));
    }

    public function testFetchThrowsException()
    {
        $key = 'key';

        $this->cacheMock->shouldReceive('fetch')->once()->with($key)->andThrow(CacheItemNotFoundException::class);

        self::assertFalse($this->adapter->fetch($key));
    }

    public function testContains()
    {
        $key = 'key';

        $this->cacheMock->shouldReceive('contains')->once()->with($key)->andReturn(true);
        self::assertTrue($this->adapter->contains($key));

        $this->cacheMock->shouldReceive('contains')->once()->with($key)->andReturn(false);
        self::assertFalse($this->adapter->contains($key));
    }

    public function testSave()
    {
        $key = 'key';
        $data = 'data';
        $lifetime = 1;

        $this->cacheMock->shouldReceive('save')->once()->with($key, $data, $lifetime)->andReturn(true);
        self::assertTrue($this->adapter->save($key, $data, $lifetime));

        $this->cacheMock->shouldReceive('save')->once()->with($key, $data, $lifetime)->andReturn(false);
        self::assertFalse($this->adapter->save($key, $data, $lifetime));
    }

    public function testDelete()
    {
        $key = 'key';

        $this->cacheMock->shouldReceive('delete')->once()->with($key)->andReturn(true);
        self::assertTrue($this->adapter->delete($key));

        $this->cacheMock->shouldReceive('delete')->once()->with($key)->andReturn(false);
        self::assertFalse($this->adapter->delete($key));
    }

    public function testGetStats_withoutFile()
    {
        $this->cacheMock->shouldReceive('getStats')->once()->andReturn([
            CacheInterface::STATS_HITS => 1,
            CacheInterface::STATS_MISSES => 1,
            CacheInterface::STATS_UPTIME => 1,
            CacheInterface::STATS_MEMORY_USAGE => 1,
            CacheInterface::STATS_MEMORY_AVAILABLE => 1,
            CacheInterface::STATS_ITEM_COUNT => 1,
        ]);

        self::assertEquals(
            [
                CacheInterface::STATS_HITS,
                CacheInterface::STATS_MISSES,
                CacheInterface::STATS_UPTIME,
                CacheInterface::STATS_MEMORY_USAGE,
                CacheInterface::STATS_MEMORY_AVAILABLE,
            ],
            array_keys($this->adapter->getStats())
        );
    }
}
