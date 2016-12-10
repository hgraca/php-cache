<?php

namespace Hgraca\Cache\Null;

use DateTime;
use Hgraca\Cache\CacheInterface;
use Hgraca\Cache\Exception\CacheItemNotFoundException;

final class NullCache implements CacheInterface
{
    /** @var DateTime */
    private $creationTime;

    /** @var int */
    private $misses;

    public function __construct()
    {
        $this->creationTime = new DateTime();
    }

    public function fetch(string $id)
    {
        $this->misses++;
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
            static::STATS_MISSES => $this->misses,
            static::STATS_UPTIME => $this->creationTime->getTimestamp(),
            static::STATS_MEMORY_USAGE => memory_get_usage(),
            static::STATS_MEMORY_AVAILABLE => ini_get('memory_limit') - memory_get_usage(),
            static::STATS_ITEM_COUNT => 0,
        ];
    }
}
