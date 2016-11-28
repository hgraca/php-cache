<?php
namespace Hgraca\Cache;

use Hgraca\Cache\Exception\CacheItemNotFoundException;

interface CacheInterface
{
    const STATS_HITS             = 'hits';
    const STATS_MISSES           = 'misses';
    const STATS_UPTIME           = 'uptime';
    const STATS_MEMORY_USAGE     = 'memory_usage';
    const STATS_MEMORY_AVAILABLE = 'memory_available';
    const STATS_ITEM_COUNT       = 'item_count';

    /**
     * @throws CacheItemNotFoundException
     */
    public function fetch(string $id): string;

    public function contains(string $id): bool;

    public function save(string $id, string $data, int $lifeTime = 0): bool;

    public function delete(string $id): bool;

    /**
     * Retrieves cached information from the data store.
     *
     * The server's statistics array has the following values:
     * - hits             Number of keys that have been requested and found present.
     * - misses           Number of items that have been requested and not found.
     * - uptime           Time that the server is running.
     * - memory_usage     Memory used by this server to store items.
     * - memory_available Memory allowed to use for storage.
     * - item_count       Number of items in the cache.
     *
     * @return array An associative array with server's statistics.
     */
    public function getStats(): array;
}
