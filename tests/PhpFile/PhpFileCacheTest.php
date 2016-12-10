<?php

namespace Hgraca\Cache\Test\PhpFile;

use Hgraca\Cache\CacheInterface;
use Hgraca\Cache\Exception\CacheItemNotFoundException;
use Hgraca\Cache\PhpFile\Adapter\FileSystem\FileSystemAdapter;
use Hgraca\Cache\PhpFile\PhpFileCache;
use Hgraca\Cache\PhpFile\Port\FileSystem\FileSystemInterface;
use Hgraca\FileSystem\FileSystemAbstract;
use Hgraca\FileSystem\LocalFileSystem;
use PHPUnit_Framework_TestCase;

final class PhpFileCacheTest extends PHPUnit_Framework_TestCase
{
    /** @var string */
    private $cacheFilePath = __DIR__ . '/cacheFile.php';

    public function tearDown()
    {
        @unlink($this->cacheFilePath);
    }

    public function testNewCache_HasNoItems()
    {
        $cache = $this->setUpVolatileCache();
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_ITEM_COUNT]);
    }

    public function testSave_IncreasesCount()
    {
        $cache = $this->setUpVolatileCache();
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_ITEM_COUNT]);
        $cache->save('key', 'data');
        self::assertEquals(1, $cache->getStats()[CacheInterface::STATS_ITEM_COUNT]);
    }

    public function testContains()
    {
        $cache = $this->setUpVolatileCache();
        $key = 'key';
        self::assertFalse($cache->contains($key));
        $cache->save($key, 'data');
        self::assertTrue($cache->contains($key));
    }

    public function testContains_DetectsStale()
    {
        $key = 'key';
        $data = 'data';

        $cache = $this->setUpVolatileCache();
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_ITEM_COUNT]);
        self::assertFalse($cache->contains($key));
        $cache->save($key, $data, -1);
        self::assertFalse($cache->contains($key));
    }

    public function testDelete()
    {
        $cache = $this->setUpVolatileCache();
        $key = 'key';
        $cache->save($key, 'data');
        self::assertTrue($cache->contains($key));
        $cache->delete($key);
        self::assertFalse($cache->contains($key));
    }

    public function testFetch()
    {
        $cache = $this->setUpVolatileCache();
        $cache->save($key = 'key', $data = 'data');
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_HITS]);
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_MISSES]);
        self::assertEquals($data, $cache->fetch($key));
        self::assertEquals(1, $cache->getStats()[CacheInterface::STATS_HITS]);
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_MISSES]);
    }

    public function testFetch_CacheMissIncreasesCount()
    {
        $cache = $this->setUpVolatileCache();
        self::assertEquals(0, $cache->getStats()[CacheInterface::STATS_MISSES]);
        self::expectException(CacheItemNotFoundException::class);
        self::assertFalse($cache->fetch('unexistent_key'));
        self::assertEquals(1, $cache->getStats()[CacheInterface::STATS_MISSES]);
    }

    public function testGetStats_withExistentFile()
    {
        $cache = $this->setUpPersistentCacheVarExportMode();
        $cache->save('key', 'data');
        $cache->save('key2', 'data2', -1);
        self::assertFileNotExists($this->cacheFilePath);
        $cache->__destruct();
        $cache = null;
        self::assertFileExists($this->cacheFilePath);

        $cache = $this->setUpPersistentCacheVarExportMode();
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
        self::assertEquals(1, $cache->getStats()[CacheInterface::STATS_ITEM_COUNT]);
    }

    public function testGetStats_withoutFile()
    {
        $cache = $this->setUpPersistentCacheVarExportMode();
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

    public function testPackUnpack_VarExport()
    {
        $key1 = 'key1';
        $data1 = 'data1';
        $key2 = 'key2';
        $data2 = 'data2';
        $key3 = 'key3';
        $data3 = 'data3';

        $cache = $this->setUpPersistentCacheVarExportMode();
        $cache->save($key1, $data1);
        $cache->save($key2, $data2);
        $cache->save($key3, $data3);
        self::assertFileNotExists($this->cacheFilePath);
        $cache->__destruct();
        $cache = null;
        self::assertFileExists($this->cacheFilePath);

        $cache = $this->setUpPersistentCacheVarExportMode();
        self::assertEquals($data1, $cache->fetch($key1));
        self::assertEquals($data2, $cache->fetch($key2));
        self::assertEquals($data3, $cache->fetch($key3));
    }

    public function testPackUnpack_Serialize()
    {
        $key1 = 'key1';
        $data1 = 'data1';
        $key2 = 'key2';
        $data2 = 'data2';
        $key3 = 'key3';
        $data3 = 'data3';

        $cache = $this->setUpPersistentCacheSerializeMode();
        $cache->save($key1, $data1);
        $cache->save($key2, $data2);
        $cache->save($key3, $data3);
        self::assertFileNotExists($this->cacheFilePath);
        $cache->__destruct();
        $cache = null;
        self::assertFileExists($this->cacheFilePath);

        $cache = $this->setUpPersistentCacheSerializeMode();
        self::assertEquals($data1, $cache->fetch($key1));
        self::assertEquals($data2, $cache->fetch($key2));
        self::assertEquals($data3, $cache->fetch($key3));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPack_UnknownMode()
    {
        $key1 = 'key1';
        $data1 = 'data1';
        $key2 = 'key2';
        $data2 = 'data2';
        $key3 = 'key3';
        $data3 = 'data3';

        $cache = $this->setUpPersistentCacheUnknownMode();
        $cache->save($key1, $data1);
        $cache->save($key2, $data2);
        $cache->save($key3, $data3);
        self::assertFileNotExists($this->cacheFilePath);
        $cache->__destruct();
        $cache = null;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnpack_UnknownMode()
    {
        $key1 = 'key1';
        $data1 = 'data1';
        $key2 = 'key2';
        $data2 = 'data2';
        $key3 = 'key3';
        $data3 = 'data3';

        $cache = $this->setUpPersistentCacheSerializeMode();
        $cache->save($key1, $data1);
        $cache->save($key2, $data2);
        $cache->save($key3, $data3);
        self::assertFileNotExists($this->cacheFilePath);
        $cache->__destruct();
        $cache = null;
        self::assertFileExists($this->cacheFilePath);

        $this->setUpPersistentCacheUnknownMode();
    }

    private function setUpPersistentCacheVarExportMode(): PhpFileCache
    {
        return new PhpFileCache(
            $this->cacheFilePath,
            PhpFileCache::MODE_VAR_EXPORT,
            PhpFileCache::TYPE_PERSISTENT,
            $this->createFileSystem()
        );
    }

    private function setUpPersistentCacheSerializeMode(): PhpFileCache
    {
        return new PhpFileCache(
            $this->cacheFilePath,
            PhpFileCache::MODE_SERIALIZER,
            PhpFileCache::TYPE_PERSISTENT,
            $this->createFileSystem()
        );
    }

    private function setUpPersistentCacheUnknownMode(): PhpFileCache
    {
        return new PhpFileCache(
            $this->cacheFilePath,
            3,
            PhpFileCache::TYPE_PERSISTENT,
            $this->createFileSystem()
        );
    }

    private function setUpVolatileCache(): PhpFileCache
    {
        return new PhpFileCache(
            $this->cacheFilePath,
            PhpFileCache::MODE_VAR_EXPORT,
            PhpFileCache::TYPE_NOT_PERSISTENT,
            $this->createFileSystem()
        );
    }

    private function createFileSystem(): FileSystemInterface
    {
        return new FileSystemAdapter(new LocalFileSystem(FileSystemAbstract::IDEMPOTENT));
    }
}
