<?php

namespace Xeviant\ReactFilesystem\Node;

use React\Filesystem\Node\File;
use Xeviant\ReactFilesystem\Adapter\ExtendedAdapterInterface;

class DecoratedFile extends File implements ExtendedFileInterface
{
    /**
     * @var ExtendedAdapterInterface
     */
    protected $adapter;

    public function putContents($contents, $options = 0)
    {
        return $this->adapter->putContents($this->path, $contents, $options);
    }

    public function appendContent($contents)
    {
        return $this->adapter->appendContents($this->path, $contents);
    }

    public function name()
    {
        return $this->adapter->name($this->path);
    }

    public function basename()
    {
        return $this->adapter->basename($this->path);
    }

    public function dirname()
    {
        return $this->adapter->dirname($this->path);
    }

    public function extension()
    {
        return $this->adapter->extension($this->path);
    }

    public function type()
    {
        return $this->adapter->type($this->path);
    }

    public function mimeType()
    {
        return $this->adapter->mimeType($this->path);
    }

    public function permissions()
    {
        return $this->adapter->fileperms($this->path);
    }

    public function isReadable()
    {
        return $this->adapter->is_readable($this->path);
    }
}