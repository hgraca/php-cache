<?php

namespace Hgraca\Cache\Null;

use Hgraca\Cache\CacheInterface;
use Hgraca\Cache\Exception\CacheItemNotFoundException;

final class NullCache implements CacheInterface
{

    public function fetch(string $id)
    {
        throw new CacheItemNotFoundException();
    }

    public function contains(string $id): bool
    {
        return false;
    }

    public function save(string $id, $data, int $lifeTime = 0): bool
    {
        return false;
    }

    public function delete(string $id): bool
    {
        return true;
    }

    public function getStats(): array
    {
        return [
            static::STATS_HITS => 0,
            static::STATS_MISSES => 0,
            static::STATS_UPTIME => 0,
            static::STATS_MEMORY_USAGE => memory_get_usage(),
            static::STATS_MEMORY_AVAILABLE => ini_get('memory_limit') - memory_get_usage(),
            static::STATS_ITEM_COUNT => 0,
        ];
    }
}
