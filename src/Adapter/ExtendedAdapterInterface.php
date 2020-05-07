<?php

namespace Xeviant\ReactFilesystem\Adapter;

use React\Filesystem\AdapterInterface;
use React\Promise\PromiseInterface;

interface ExtendedAdapterInterface extends AdapterInterface
{
    public function putContents($path, $content, $options = 0);
    public function name($path): PromiseInterface;
    public function is_writable($path): PromiseInterface;
    public function is_readable($path): PromiseInterface;
    public function basename($path): PromiseInterface;
    public function dirname($path): PromiseInterface;
    public function extension($path): PromiseInterface;
    public function type($path): PromiseInterface;
    public function glob($path, $flags = 0): PromiseInterface;
    public function mimeType($path): PromiseInterface;
    public function fileperms($path): PromiseInterface;
    public function copyDir($source, $destination): PromiseInterface;
}