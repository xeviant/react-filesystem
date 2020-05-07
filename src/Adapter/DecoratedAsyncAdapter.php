<?php

namespace Xeviant\ReactFilesystem\Adapter;

use React\Filesystem\ChildProcess\Adapter;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Pool\Options;
use WyriHaximus\React\ChildProcess\Pool\PoolInterface;
use function React\Promise\resolve;

class DecoratedAsyncAdapter extends Adapter implements ExtendedAdapterInterface
{
    public const CHILD_CLASS_NAME = 'Xeviant\ReactFilesystem\Adapter\ExtendedProcess';

    protected function setUpPool($options)
    {
        $poolOptions = [
            Options::MIN_SIZE => 0,
            Options::MAX_SIZE => 50,
            Options::TTL => 3,
        ];
        $poolClass = static::DEFAULT_POOL;

        if (isset($options['pool']['class']) && is_subclass_of($options['pool']['class'], static::POOL_INTERFACE)) {
            $poolClass = $options['pool']['class'];
        }

        call_user_func_array($poolClass . '::createFromClass', [
            self::CHILD_CLASS_NAME,
            $this->loop,
            $poolOptions,
        ])->then(function (PoolInterface $pool) {
            $this->pool = $pool;
        });
    }

    public function putContents($path, $content, $options = 0)
    {
        return $this->callFilesystem('putContents', [
            'path' => $path,
            'chunk' => base64_encode($content),
            'flags' => $options,
        ])->then(function ($payload) {
            return resolve($payload['written']);
        });
    }

    public function name($path): PromiseInterface
    {
        return $this->callFilesystem('name', ['path' => $path])
            ->then(fn($payload) => resolve($payload['name']));
    }

    public function is_readable($path): PromiseInterface
    {
        return $this->callFilesystem('isReadable', ['path' => $path])
            ->then(fn($payload) => resolve($payload['readable']));
    }

    public function is_writable($path): PromiseInterface
    {
        return $this->callFilesystem('isWritable', ['path' => $path])
            ->then(fn($payload) => resolve($payload['writable']));
    }

    public function basename($path): PromiseInterface
    {
        return $this->callFilesystem('basename', ['path' => $path])
            ->then(fn($payload) => resolve($payload['name']));
    }

    public function dirname($path): PromiseInterface
    {
        return $this->callFilesystem('dirname', ['path' => $path])
            ->then(fn($payload) => resolve($payload['name']));
    }

    public function extension($path): PromiseInterface
    {
        return $this->callFilesystem('extension', ['path' => $path])
            ->then(fn($payload) => resolve($payload['extension']));
    }

    public function type($path): PromiseInterface
    {
        return $this->callFilesystem('type', ['path' => $path])
            ->then(fn($payload) => resolve($payload['type']));
    }

    public function glob($path, $flags = 0): PromiseInterface
    {
        return $this->callFilesystem('glob', ['path' => $path, 'flags' => $flags])
            ->then(fn($payload) => resolve($payload['paths']));
    }

    public function mimeType($path): PromiseInterface
    {
        return $this->callFilesystem('mimeType', ['path' => $path])
            ->then(fn($payload) => resolve($payload['mimeType']));
    }

    public function fileperms($path): PromiseInterface
    {
        return $this->callFilesystem('fileperms', ['path' => $path])
            ->then(fn($payload) => resolve($payload['permissions']));
    }

    public function mkdir($path, $mode = self::CREATION_MODE)
    {
        return $this->callFilesystem('mkdir', [
            'path' => $path,
            'mode' => $mode,
        ]);
    }

    public function copyDir($source, $destination): PromiseInterface
    {
        return $this->callFilesystem('copyDir', [
            'source' => $source,
            'destination' => $destination,
        ])->then(fn($result) => $result['copied'] ?? false);
    }
}