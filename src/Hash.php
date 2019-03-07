<?php

namespace Yiranzai\Dht;

/**
 * Class Hash
 * @package Yiranzai\Dht
 */
class Hash implements \JsonSerializable
{

    public const DEFAULT_ALGO = 'time33';
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
     * @var string
     */
    private $algo = 'time33';
    /**
     * all node cache
     *
     * @var array
     */
    private $locations = [];
    /**
     * virtual node num
     *
     * @var int
     */
    private $virtualNodeNum = 24;
    /**
     * entity node cache
     *
     * @var
     */
    private $nodes = [];

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
     * @param string $str
     * @return $this
     */
    public function algo(string $str): self
    {
        if ($this->isSupportHashAlgos($str)) {
            $this->algo = $str;
        }
        return $this;
    }

    /**
     * @param string $str
     * @return bool
     */
    public function isSupportHashAlgos(string $str): bool
    {
        return $str === self::DEFAULT_ALGO || in_array($str, $this->supportHashAlgos(), true);
    }

    /**
     * @return array
     */
    private function supportHashAlgos(): array
    {
        return hash_algos();
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
     * @return string
     */
    public function toJson(): string
    {
        $json = json_encode($this->jsonSerialize());
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
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
     * @return string
     */
    private function hashGenerate(string $str): string
    {
        if ($this->algo === self::DEFAULT_ALGO) {
            return $this->time33($str);
        }
        return sprintf('%u', hash($this->algo, $str));
    }

    /**
     * @param string $str
     * @return int
     */
    private function time33(string $str): int
    {
        $hash = 0;
        $s    = md5($str);
        $len  = 32;
        for ($i = 0; $i < $len; $i++) {
            $hash = ($hash << 5) + $hash + ord($s{$i});
        }
        return $hash & 0x7FFFFFFF;
    }

    /**
     * 寻找字符串所在的机器位置
     * @param string $key
     * @return bool|mixed
     */
    public function getLocation(string $key)
    {
        if (empty($this->locations)) {
            return false;
        }

        $position = $this->hashGenerate($key);
        //默认取第一个节点
        $node = current($this->locations);
        foreach ($this->locations as $k => $v) {
            //如果当前的位置，小于或等于节点组中的一个节点，那么当前位置对应该节点
            if ($position <= $k) {
                $node = $v;
                break;
            }
        }
        return $node;
    }

    /**
     * 添加一个节点
     * @param string $node
     * @return Hash
     */
    public function addEntityNode(string $node): self
    {
        if ($this->existsNode($node)) {
            throw new \RuntimeException('This node already exists');
        }
        $this->nodes[$node] = [];
        //生成虚拟节点
        for ($i = 0; $i < $this->virtualNodeNum; $i++) {
            $tmp                   = $this->hashGenerate($node . $i);
            $this->locations[$tmp] = $node;
            $this->nodes[$node][]  = $tmp;
        }
        //对节点排序
        ksort($this->locations, SORT_NUMERIC);
        return $this;
    }

    /**
     * @param string $node
     * @return bool
     */
    public function existsNode(string $node): bool
    {
        return array_key_exists($node, $this->nodes);
    }

    /**
     * delete a entity node
     *
     * @param $node
     * @return Hash
     */
    public function deleteEntityNode($node): self
    {
        foreach ($this->nodes[$node] as $v) {
            unset($this->locations[$v]);
        }
        return $this;
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

    /**
     * @param int $num
     * @return $this
     */
    public function virtualNodeNum(int $num): self
    {
        $this->virtualNodeNum = $num;
        return $this;
    }
}