<?php

namespace Xeviant\ReactFilesystem;

use React\Filesystem\FilesystemInterface;
use Xeviant\ReactFilesystem\Adapter\ExtendedAdapterInterface;
use Xeviant\ReactFilesystem\Node\ExtendedFileInterface;

interface ExtendedFilesystemInterface extends FilesystemInterface
{
    public function file($filename): ExtendedFileInterface;
    public function getAdapter(): ExtendedAdapterInterface;
    public static function createFromExtendedAdapter(ExtendedAdapterInterface $adapter);
}