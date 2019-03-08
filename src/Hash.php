<?php

namespace Yiranzai\Dht;

/**
 * Class Hash
 * @package Yiranzai\Dht
 */
class Hash extends Dht
{

    public const DEFAULT_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR;
    /**
     * @var Filesystem
     */
    protected $file;
    /**
     * @var string
     */
    protected $cachePath = self::DEFAULT_PATH;
    /**
     * @var array
     */
    protected $guarded = ['file'];

    /**
     * Hash constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $item) {
                if (in_array($key, $this->guarded, true)) {
                    continue;
                }
                if ($key === 'algo') {
                    $this->algo($item);
                    continue;
                }
                $this->$key = $item;
            }
        }
        $this->file = new Filesystem();
        if (!$this->file->exists($this->cachePath)) {
            $this->file->makeDirectory($this->cachePath);
        }
    }

    /**
     * @param string|array $data
     * @param string       $path
     */
    public static function cache($data, $path = self::DEFAULT_PATH): void
    {
        $file = new Filesystem();
        if (!$file->exists($path)) {
            $file->makeDirectory($path);
        }
        if (is_array($data)) {
            $data = json_encode($data);
        } elseif ($data instanceof \JsonSerializable) {
            $data = json_encode($data);
        } else {
            json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('The data must be json or array');
            }
        }

        $file->put($path . 'config', $data);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public static function getCache($path = self::DEFAULT_PATH)
    {
        $file = new Filesystem();
        if (!$file->exists($path . 'config')) {
            throw new \RuntimeException('This path already exists');
        }
        $data = json_decode($file->get($path . 'config'));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('The data is invalid');
        }
        return $data;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = array();
        foreach ($this as $key => $value) {
            if (in_array($key, $this->guarded, true)) {
                continue;
            }
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * @param string $str
     * @return $this
     */
    public function path(string $str): self
    {
        $this->cachePath = $str;
        return $this;
    }
}
