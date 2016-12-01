<?php
namespace Hgraca\Cache\Test\PhpFile\Adapter\FileSystem;

use Hgraca\Cache\PhpFile\Adapter\FileSystem\FileSystemAdapter;
use Hgraca\FileSystem\Exception\FileNotFoundException;
use Hgraca\FileSystem\Exception\InvalidPathException;
use Hgraca\FileSystem\Exception\PathAlreadyExistsException;
use Hgraca\FileSystem\FileSystemInterface;
use Hgraca\FileSystem\InMemoryFileSystem;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase;

final class FileSystemAdapterTest extends PHPUnit_Framework_TestCase
{
    /** @var MockInterface|FileSystemInterface */
    private $fileSystem;

    /** @var FileSystemAdapter */
    private $adapter;

    public function setUp()
    {
        $this->fileSystem = Mockery::mock(InMemoryFileSystem::class, [InMemoryFileSystem::IDEMPOTENT]);
        $this->adapter       = new FileSystemAdapter($this->fileSystem);
    }

    public function testFileExists()
    {
        $pathExists       = __DIR__ . '/file.txt';
        $pathDoesNotExist = __DIR__ . '/AAAA.txt';
        $this->fileSystem->shouldReceive('fileExists')->with($pathExists)->andReturn(true);
        $this->fileSystem->shouldReceive('fileExists')->with($pathDoesNotExist)->andReturn(false);

        self::assertTrue($this->adapter->fileExists($pathExists));
        self::assertFalse($this->adapter->fileExists($pathDoesNotExist));
    }

    /**
     * @expectedException \Hgraca\Cache\PhpFile\Port\FileSystem\Exception\InvalidPathException
     */
    public function testFileExists_throwsException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('fileExists')->with($path)->andThrow(InvalidPathException::class);

        $this->adapter->fileExists($path);
    }

    public function testReadFile()
    {
        $path = 'some/path';
        $contents = '123';
        $this->fileSystem->shouldReceive('readFile')->with($path)->andReturn($contents);

        self::assertEquals($contents, $this->adapter->readFile($path));
    }

    /**
     * @expectedException \Hgraca\Cache\PhpFile\Port\FileSystem\Exception\InvalidPathException
     */
    public function testReadFile_throwsInvalidPathException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('readFile')->with($path)->andThrow(InvalidPathException::class);

        $this->adapter->readFile($path);
    }

    /**
     * @expectedException \Hgraca\Cache\PhpFile\Port\FileSystem\Exception\FileNotFoundException
     */
    public function testReadFile_throwsFileNotFoundException()
    {
        $path = 'some/path';
        $this->fileSystem->shouldReceive('readFile')->with($path)->andThrow(FileNotFoundException::class);

        $this->adapter->readFile($path);
    }

    public function testWriteFile()
    {
        $path     = 'some/path';
        $contents = '123';
        $this->fileSystem->shouldReceive('writeFile')->once()->with($path, $contents);

        $this->adapter->writeFile($path, $contents);
    }

    /**
     * @expectedException \Hgraca\Cache\PhpFile\Port\FileSystem\Exception\InvalidPathException
     */
    public function testWriteFile_throwsInvalidPathException()
    {
        $path = 'some/path';
        $contents = '123';
        $this->fileSystem->shouldReceive('writeFile')->with($path, $contents)->andThrow(InvalidPathException::class);

        $this->adapter->writeFile($path, $contents);
    }

    /**
     * @expectedException \Hgraca\Cache\PhpFile\Port\FileSystem\Exception\PathAlreadyExistsException
     */
    public function testWriteFile_throwsPathAlreadyExistsException()
    {
        $path = 'some/path';
        $contents = '123';
        $this->fileSystem->shouldReceive('writeFile')->with($path, $contents)->andThrow(PathAlreadyExistsException::class);

        $this->adapter->writeFile($path, $contents);
    }

    public function testGetFileCreationTimestamp()
    {
        $path     = 'some/path';
        $this->fileSystem->shouldReceive('getFileCreationTimestamp')
            ->once()
            ->with($path);

        $this->adapter->getFileCreationTimestamp($path);
    }

    /**
     * @expectedException \Hgraca\Cache\PhpFile\Port\FileSystem\Exception\InvalidPathException
     */
    public function testGetFileCreationTimestamp_throwsInvalidPathException()
    {
        $path     = 'some/path';
        $this->fileSystem->shouldReceive('getFileCreationTimestamp')
            ->with($path)
            ->andThrow(InvalidPathException::class);

        $this->adapter->getFileCreationTimestamp($path);
    }
}
