<?php

namespace Xeviant\ReactFilesystem\Node;

use React\Filesystem\Node\FileInterface;
use React\Promise\PromiseInterface;

interface ExtendedFileInterface extends FileInterface
{
    public function putContents($contents, $options = 0);
    public function appendContent($contents);
    public function name();
    public function basename();
    public function dirname();
    public function extension();
    public function type();
    public function mimeType();
    public function permissions();

    /**
     * @return PromiseInterface<bool>
     */
    public function isReadable();
}