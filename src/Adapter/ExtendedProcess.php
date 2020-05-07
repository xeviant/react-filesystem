<?php

namespace Xeviant\ReactFilesystem\Adapter;

use React\EventLoop\LoopInterface;
use React\Filesystem\ChildProcess\Process;
use React\Promise\PromiseInterface;
use WyriHaximus\React\ChildProcess\Messenger\Messenger;
use function React\Promise\resolve;

class ExtendedProcess extends Process
{
    const RPC_IDS = [
        'name',
        'basename',
        'dirname',
        'extension',
        'type',
        'glob',
        'mimeType',
        'fileperms',
        'isReadable',
        'isWritable',
        'copyDir'
    ];
    public function __construct(Messenger $messenger)
    {
        parent::__construct($messenger);

        foreach (self::RPC_IDS as $method) {
            $messenger->registerRpc($method, $this->wrapper($method));
        }
    }

    public static function create(Messenger $messenger, LoopInterface $loop)
    {
        return new self($messenger);
    }

    public function name($payload)
    {
        $result['name'] = pathinfo($payload['path'], PATHINFO_FILENAME);

        return resolve($result);
    }

    public function isWritable($payload)
    {
        $result['writable'] = is_writable($payload['path']);

        return resolve($result);
    }

    public function isReadable($payload)
    {
        $result['readable'] = is_readable($payload['path']);

        return resolve($result);
    }

    public function fileperms($payload)
    {
        $result['permissions'] = fileperms($payload['path']);

        return resolve($result);
    }

    public function basename($payload)
    {
        $result['name'] = basename($payload['path']);

        return resolve($result);
    }

    public function dirname($payload)
    {
        $result['name'] = pathinfo($payload['path'], PATHINFO_DIRNAME);

        return resolve($result);
    }

    public function extension($payload)
    {
        $result['extension'] = @pathinfo($payload['path'], PATHINFO_EXTENSION);

        return resolve($result);
    }

    public function type($payload)
    {
        $result['type'] = @filetype($payload['path']);

        return resolve($result);
    }

    public function glob($payload)
    {
        $result['paths'] = glob($payload['path'], $payload['flags']);

        return resolve($result);
    }

    public function mimeType($payload)
    {
        $result['mimeType'] = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $payload['path']);

        return resolve($result);
    }

    public function symlink(array $payload)
    {
        if (! windows_os()) {
            return parent::symlink($payload);
        }

        $mode = is_dir($payload['from']) ? 'J' : 'H';

        exec("mklink /{$mode} ".escapeshellarg($payload['to']).' '.escapeshellarg($payload['from']));

        return resolve(['result' => true ]);
    }

    /**
     * @param array $payload
     * @return PromiseInterface
     */
    public function mkdir(array $payload)
    {
        if (
        @mkdir(
            $payload['path'],
            $payload['mode']
        )
        ) {
            return \React\Promise\resolve([]);
        }

        return \React\Promise\reject([
            'error' => error_get_last(),
        ]);
    }

    public function copyDir(array $payload)
    {
        $result['copied'] = $this->copik($payload['source'], $payload['destination']);

        return resolve($result);
    }

    protected function copik($src, $dst) {
        $dir = opendir($src);

        @mkdir($dst);

        while(( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->copik($src .'/'. $file, $dst .'/'. $file);
                }
                else {
                    copy($src .'/'. $file,$dst .'/'. $file);
                }
            }
        }
        closedir($dir);

        return true;
    }

    /**
     * @param array $payload
     * @return PromiseInterface
     */
    public function readdir(array $payload)
    {
        $contents = @scandir($payload['path'], $payload['flags']);

        if (! $contents) {
            return resolve([]);
        }

        $list = [];

        foreach ($contents as $node) {
            $path = $payload['path'] . DIRECTORY_SEPARATOR . $node;
            if ($node == '.' || $node == '..' || (!is_dir($path) && !is_file($path))) {
                continue;
            }

            $list[] = [
                'type' => is_dir($path) ? 'dir' : (is_link($path) ? 'link' : 'file'),
                'name' => $node,
            ];
        }
        return \React\Promise\resolve($list);
    }
}