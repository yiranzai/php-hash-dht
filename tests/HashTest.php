<?php

declare(strict_types=1);

namespace Yiranzai\Dht;

/**
 * Class HashTest
 * @package Yiranzai\Dht
 */
class HashTest extends \PHPUnit\Framework\TestCase
{

    public function testCache()
    {
        $hash = new Hash();
        $file = new Filesystem();

        $hash->addEntityNode('db_server_one')->addEntityNode('db_server_two');
        $resOne = $hash->getLocation('key_one');
        Hash::cache($hash->toArray());
        $hash   = new Hash(Hash::getCache());
        $resTwo = $hash->getLocation('key_one');
        $this->assertSame($resOne, $resTwo);

        Hash::cache($hash);
        $hash     = new Hash(Hash::getCache());
        $resThree = $hash->getLocation('key_one');
        $this->assertSame($resOne, $resThree);

        Hash::cache($hash->toJson());
        $hash    = new Hash(Hash::getCache());
        $resFour = $hash->getLocation('key_one');
        $this->assertSame($resOne, $resFour);

        Hash::cache($hash->toJson(), 'tmp/');
        $hash    = new Hash(Hash::getCache('tmp/'));
        $resFive = $hash->getLocation('key_one');
        $this->assertSame($resOne, $resFive);
        $file->deleteDirectory('tmp/');
        $this->assertFalse($file->exists('tmp/'));
    }

    public function testLocationException()
    {
        $this->expectException(\Exception::class);
        $hash = new Hash();
        $this->assertFalse($hash->getLocation('test'));
    }

    public function testAlgo()
    {
        $hash = new Hash();
        $this->assertSame(
            (int)sprintf('%u', hash('sha256', 'algo_test')),
            $hash->algo('sha256')->hashGenerate('algo_test')
        );
    }

    public function testLinkMethod()
    {
        $hash = new Hash([
            'file'      => 1,
            'cachePath' => 'tmp'
        ]);
        $file = new Filesystem();
        $this->assertTrue($file->exists('tmp'));
        $this->assertTrue($file->isDirectory('tmp'));
        $file->deleteDirectory('tmp');
        $this->assertFalse($file->exists('tmp'));
        $data = $hash->path('test')->virtualNodeNum(3)->addEntityNode('db_server_one')->toArray();
        $this->assertSame('test', $data['cachePath']);
        $this->assertSame(3, $data['virtualNodeNum']);
        $this->assertCount(3, $data['locations']);
    }

    public function testCacheException()
    {
        $this->expectException(\Exception::class);
        Hash::getCache('test');
    }

    public function testDeleteNode()
    {
        $hashOne = new Hash();
        $hashTwo = new Hash();
        $this->assertTrue($hashOne->addEntityNode('db_server_one')
            ->addEntityNode('db_server_two')->existsNode('db_server_two'));
        $this->assertFalse($hashTwo->addEntityNode('db_server_one')
            ->addEntityNode('db_server_two')->deleteEntityNode('db_server_two')->existsNode('db_server_two'));
    }

    /**
     * Test that true does in fact equal true
     */
    public function testNode()
    {
        $hash = new Hash();
        $this->assertTrue($hash->addEntityNode('db_server_one')->existsNode('db_server_one'));
    }


    /**
     * test same node exception
     */
    public function testSameNodeException()
    {
        $this->expectException(\Exception::class);
        $hash = new Hash();
        $hash->addEntityNode('db_server_one')->addEntityNode('db_server_one');
    }
}
