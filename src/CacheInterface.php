<?php

namespace Hgraca\Cache;

use Hgraca\Cache\Exception\CacheItemNotFoundException;

interface CacheInterface
{
    const MODE_VAR_EXPORT = 1;
    const MODE_SERIALIZER = 2;

    const STATS_HITS = 'hits';
    const STATS_MISSES = 'misses';
    const STATS_UPTIME = 'uptime';
    const STATS_MEMORY_USAGE = 'memory_usage';
    const STATS_MEMORY_AVAILABLE = 'memory_available';
    const STATS_ITEM_COUNT = 'item_count';

    /**
     * @throws CacheItemNotFoundException
     *
     * @return mixed
     */
    public function fetch(string $id);

    public function contains(string $id): bool;

    /**
     * @param string $id
     * @param mixed  $data
     * @param int    $lifeTime
     */
    public function save(string $id, $data, int $lifeTime = 0): bool;

    public function delete(string $id): bool;

    /**
     * Retrieves cached information from the data store.
     *
     * The server's statistics array has the following values:
     * - hits             Number of keys that have been requested and found present.
     * - misses           Number of items that have been requested and not found.
     * - uptime           Time that the cache exists, in seconds.
     * - memory_usage     Memory used by this server to store items.
     * - memory_available Memory allowed to use for storage.
     * - item_count       Number of items in the cache.
     *
     * @return array an associative array with server's statistics
     */
    public function getStats(): array;
}
