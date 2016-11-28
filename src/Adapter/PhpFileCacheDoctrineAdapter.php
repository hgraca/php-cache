<?php
namespace Hgraca\Cache\Adapter;

use Doctrine\Common\Cache\Cache;
use Hgraca\Cache\CacheInterface;
use Hgraca\Cache\Exception\CacheItemNotFoundException;
use Hgraca\Cache\PhpFile\PhpFileCache;

final class PhpFileCacheDoctrineAdapter implements Cache
{
    const MODE_VAR_EXPORT = PhpFileCache::MODE_VAR_EXPORT;
    const MODE_SERIALIZER = PhpFileCache::MODE_SERIALIZER;

    /** @var PhpFileCache */
    protected $phpFileCache;

    public function __construct(CacheInterface $phpFileCache)
    {
        $this->phpFileCache = $phpFileCache;
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id The id of the cache entry to fetch.
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
     */
    public function fetch($id)
    {
        try {
            return $this->phpFileCache->fetch($id);
        } catch (CacheItemNotFoundException $e) {
            return false;
        }
    }

    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $id The cache id of the entry to check for.
     *
     * @return bool TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    public function contains($id)
    {
        return $this->phpFileCache->contains($id);
    }

    /**
     * Puts data into the cache.
     *
     * If a cache entry with the given id already exists, its data will be replaced.
     *
     * @param string $id       The cache id.
     * @param mixed  $data     The cache entry/data.
     * @param int    $lifeTime The lifetime in number of seconds for this cache entry.
     *                         If zero (the default), the entry never expires (although it may be deleted from the cache
     *                         to make place for other entries).
     *
     * @return bool TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->phpFileCache->save($id, $data, $lifeTime);
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id The cache id.
     *
     * @return bool TRUE if the cache entry was successfully deleted, FALSE otherwise.
     *              Deleting a non-existing entry is considered successful.
     */
    public function delete($id)
    {
        return $this->phpFileCache->delete($id);
    }

    /**
     * Retrieves cached information from the data store.
     *
     * The server's statistics array has the following values:
     *
     * - <b>hits</b>
     * Number of keys that have been requested and found present.
     *
     * - <b>misses</b>
     * Number of items that have been requested and not found.
     *
     * - <b>uptime</b>
     * Time that the server is running.
     *
     * - <b>memory_usage</b>
     * Memory used by this server to store items.
     *
     * - <b>memory_available</b>
     * Memory allowed to use for storage.
     *
     * @since 2.2
     *
     * @return array|null An associative array with server's statistics if available, NULL otherwise.
     */
    public function getStats()
    {
        $stats = $this->phpFileCache->getStats();

        unset($stats[CacheInterface::STATS_ITEM_COUNT]);

        return $stats;
    }
}
