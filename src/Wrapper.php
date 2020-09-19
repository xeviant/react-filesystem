<?php

namespace Xeviant\ReactFilesystem;

use React\Promise\PromiseInterface;
use function React\Promise\all;

class Wrapper
{
    private ExtendedFilesystemInterface $asyncFs;

    public function __construct(ExtendedFilesystemInterface $asyncFs)
    {
        $this->asyncFs = $asyncFs;
    }

    public function getAsyncFilesystem(): ExtendedFilesystemInterface
    {
        return $this->asyncFs;
    }

    /**
     * Ensure a directory exists.
     *
     * @param  string  $path
     * @param  int  $mode
     * @param  bool  $recursive
     * @return PromiseInterface<bool>
     */
    public function ensureDirectoryExists($path, $mode = 0755, $recursive = true)
    {
        return $this->isDirectory($path)->then(function ($isDirectory) use ($recursive, $path, $mode) {
            if (! $isDirectory) {
                return $this->makeDirectory($path, $mode, $recursive)->then(fn() => $this->isDirectory($path));
            }
            return true;
        });
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param  string  $directory
     * @return PromiseInterface<bool>
     */
    public function isDirectory($directory)
    {
        return $this->type($directory)->then(fn($type) => $type === 'dir');
    }

    /**
     * Get the file type of a given file.
     *
     * @param  string  $path
     * @return PromiseInterface<string>
     */
    public function type($path)
    {
        return $this->asyncFs->file($path)->type();
    }

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @param  int  $mode
     * @param  bool  $recursive
     * @param  bool  $force
     * @return PromiseInterface<bool>
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
    {
        $dir = $this->asyncFs->dir($path);

        if ($force) {
            $promise = $dir->createRecursive($mode);
        } else {
            $promise = $recursive
                ? $dir->createRecursive($mode)->then(fn() => true)
                : $dir->create($mode)->then(fn() => true);
        }

        return $promise->then(fn() => true);
    }

    /**
     * Determine if the given path is readable.
     *
     * @param  string  $path
     * @return PromiseInterface<bool>
     */
    public function isReadable($path)
    {
        return $this->asyncFs->getAdapter()->is_readable($path);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return PromiseInterface<bool>
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $result = [];
        return all(array_map(function ($path) use (&$success, &$result) {
            return $this->asyncFs->file($path)
                ->remove()
                ->then(fn() => $result[] = true)
                ->otherwise(fn() => $result[] = true);
        }, $paths))->then(fn($results) => !in_array(false, $result));
    }
}
