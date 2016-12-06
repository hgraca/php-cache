<?php

namespace Hgraca\Cache\PhpFile\Adapter\FileSystem;

use Hgraca\Cache\PhpFile\Port\FileSystem\Exception\FileNotFoundException as CacheFileNotFoundException;
use Hgraca\Cache\PhpFile\Port\FileSystem\Exception\InvalidPathException as CacheInvalidPathException;
use Hgraca\Cache\PhpFile\Port\FileSystem\Exception\PathAlreadyExistsException as CachePathAlreadyExistsException;
use Hgraca\Cache\PhpFile\Port\FileSystem\FileSystemInterface as CacheFileSystemInterface;
use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\InvalidPathException;
use Hgraca\FileSystem\Exception\PathAlreadyExistsException;
use Hgraca\FileSystem\FileSystemInterface;
use Hgraca\FileSystem\LocalFileSystem;

final class FileSystemAdapter implements CacheFileSystemInterface
{
    /**
     * @var FileSystemInterface
     */
    private $fileSystem;

    public function __construct(FileSystemInterface $fileSystem = null)
    {
        $this->fileSystem = $fileSystem ?? new LocalFileSystem(LocalFileSystem::IDEMPOTENT);
    }

    /**
     * @throws CacheInvalidPathException
     */
    public function fileExists(string $path): bool
    {
        try {
            return $this->fileSystem->fileExists($path);
        } catch (InvalidPathException $e) {
            throw new CacheInvalidPathException('', 0, $e);
        }
    }

    /**
     * @throws CacheFileNotFoundException
     * @throws CacheInvalidPathException
     */
    public function readFile(string $path): string
    {
        try {
            return $this->fileSystem->readFile($path);
        } catch (InvalidPathException $e) {
            throw new CacheInvalidPathException('', 0, $e);
        } catch (FileNotFoundException $e) {
            throw new CacheFileNotFoundException('', 0, $e);
        }
    }

    /**
     * @throws CachePathAlreadyExistsException
     * @throws CacheInvalidPathException
     */
    public function writeFile(string $path, string $content)
    {
        try {
            $this->fileSystem->writeFile($path, $content);
        } catch (InvalidPathException $e) {
            throw new CacheInvalidPathException('', 0, $e);
        } catch (PathAlreadyExistsException $e) {
            throw new CachePathAlreadyExistsException('', 0, $e);
        }
    }

    /**
     * @throws CacheInvalidPathException
     */
    public function getFileCreationTimestamp(string $path): int
    {
        try {
            return $this->fileSystem->getFileCreationTimestamp($path);
        } catch (InvalidPathException $e) {
            throw new CacheInvalidPathException('', 0, $e);
        }
    }
}
