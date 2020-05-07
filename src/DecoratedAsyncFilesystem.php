<?php

namespace Xeviant\ReactFilesystem;

use React\Filesystem\AdapterInterface;
use React\Filesystem\Filesystem;

class DecoratedAsyncFilesystem extends Filesystem implements ExtendedFilesystemInterface
{
    /**
     * @var ExtendedAdapterInterface
     */
    protected $adapter;

    public function file($filename): ExtendedFileInterface
    {
        return new DecoratedFile($filename, $this);
    }

    public function getAdapter(): ExtendedAdapterInterface
    {
        return $this->adapter;
    }

    public static function createFromExtendedAdapter(ExtendedAdapterInterface $adapter): ExtendedFilesystemInterface
    {
        return parent::createFromAdapter($adapter);
    }
}