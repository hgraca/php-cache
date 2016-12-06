<?php

namespace Hgraca\Cache\PhpFile\Port\FileSystem;

use Hgraca\Cache\PhpFile\Port\FileSystem\Exception\FileNotFoundException;
use Hgraca\Cache\PhpFile\Port\FileSystem\Exception\InvalidPathException;
use Hgraca\Cache\PhpFile\Port\FileSystem\Exception\PathAlreadyExistsException;

interface FileSystemInterface
{
    /**
     * @throws InvalidPathException
     */
    public function fileExists(string $path): bool;

    /**
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    public function readFile(string $path): string;

    /**
     * @throws PathAlreadyExistsException
     * @throws InvalidPathException
     */
    public function writeFile(string $path, string $content);

    /**
     * @throws InvalidPathException
     */
    public function getFileCreationTimestamp(string $path): int;
}
