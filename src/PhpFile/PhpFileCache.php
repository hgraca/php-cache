<?php

namespace Hgraca\Cache\PhpFile;

use Hgraca\Cache\CacheInterface;
use Hgraca\Cache\Exception\CacheItemNotFoundException;
use Hgraca\Cache\PhpFile\Adapter\FileSystem\FileSystemAdapter;
use Hgraca\Cache\PhpFile\Port\FileSystem\FileSystemInterface;
use InvalidArgumentException;

final class PhpFileCache implements CacheInterface
{
    const TYPE_PERSISTENT = true;
    const TYPE_NOT_PERSISTENT = false;

    const KEY_LIFETIME = 'lifetime';
    const KEY_CREATION_TIME = 'creationTime';
    const KEY_DATA = 'data';

    /** @var bool */
    private $contentHasChanged = false;

    /** @var array */
    private $cache;

    /** @var string */
    private $cacheFilePath;

    /** @var int */
    private $mode;

    /** @var int */
    private $hits;

    /** @var int */
    private $misses;

    /** @var bool */
    private $persistent;

    /**
     * @var FileSystemInterface
     */
    private $fileSystem;

    public function __construct(
        string $cacheFileName,
        int $mode = self::MODE_VAR_EXPORT,
        bool $persistent = self::TYPE_PERSISTENT,
        FileSystemInterface $fileSystem = null
    ) {
        $this->cacheFilePath = $cacheFileName;
        $this->mode = $mode;
        $this->persistent = $persistent;
        $this->fileSystem = $fileSystem ?? new FileSystemAdapter();

        $this->cache = $this->unpack();
    }

    /**
     * Saves its data in the cache file at destruction time, if it has changed
     */
    public function __destruct()
    {
        $this->deleteAllStale();

        if ($this->contentHasChanged) {
            $this->pack();
        }
    }

    /**
     * @throws CacheItemNotFoundException
     *
     * @return mixed
     */
    public function fetch(string $id)
    {
        if (!$this->contains($id)) {
            ++$this->misses;

            throw new CacheItemNotFoundException();
        }
        ++$this->hits;

        return $this->cache[$id][self::KEY_DATA];
    }

    public function contains(string $id): bool
    {
        if (!array_key_exists($id, $this->cache) || $this->isStale($id)) {
            return false;
        }

        return true;
    }

    public function save(string $id, $data, int $lifeTime = 0, int $creationTime = null): bool
    {
        $this->cache[$id] = [
            self::KEY_DATA => $data,
            self::KEY_LIFETIME => $lifeTime,
            self::KEY_CREATION_TIME => $creationTime ?? time(),
        ];

        $this->contentHasChanged = true;

        return true;
    }

    public function delete(string $id): bool
    {
        if (isset($this->cache[$id])) {
            unset($this->cache[$id]);
            $this->contentHasChanged = true;
        }

        return true;
    }

    public function getStats(): array
    {
        $this->deleteAllStale();

        return [
            static::STATS_HITS => $this->hits,
            static::STATS_MISSES => $this->misses,
            static::STATS_UPTIME => $this->fileSystem->fileExists($this->cacheFilePath)
                ? $this->fileSystem->getFileCreationTimestamp($this->cacheFilePath) - time()
                : 0,
            static::STATS_MEMORY_USAGE => memory_get_usage(),
            static::STATS_MEMORY_AVAILABLE => ini_get('memory_limit') - memory_get_usage(),
            static::STATS_ITEM_COUNT => count($this->cache),
        ];
    }

    private function toArray(): array
    {
        return $this->cache;
    }

    private function pack()
    {
        if (!$this->persistent) {
            return null;
        }

        switch ($this->mode) {
            case self::MODE_VAR_EXPORT:
                $this->fileSystem->writeFile(
                    $this->cacheFilePath,
                    '<?php $array = ' . var_export($this->toArray(), true) . ';'
                );
                break;
            case self::MODE_SERIALIZER:
                $this->fileSystem->writeFile($this->cacheFilePath, serialize($this->toArray()));
                break;
            default:
                throw new InvalidArgumentException('Serialization mode unknown: ' . $this->mode);
        }
    }

    private function unpack(): array
    {
        if (!$this->persistent || !$this->fileSystem->fileExists($this->cacheFilePath)) {
            return [];
        }

        switch ($this->mode) {
            case self::MODE_VAR_EXPORT:
                $array = [];
                include $this->cacheFilePath;

                return $array;

            case self::MODE_SERIALIZER:
                return unserialize($this->fileSystem->readFile($this->cacheFilePath));

            default:
                throw new InvalidArgumentException('Serialization mode unknown: ' . $this->mode);
        }
    }

    private function deleteAllStale()
    {
        foreach ($this->cache as $id => $item) {
            if ($this->isStale($id)) {
                $this->delete($id);
            }
        }
    }

    private function isStale(string $id)
    {
        $item = $this->cache[$id];

        return (0 !== $item[self::KEY_LIFETIME]) &&
            (time() - $item[self::KEY_CREATION_TIME] >= $item[self::KEY_LIFETIME]);
    }
}
