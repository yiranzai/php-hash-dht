<?php

declare(strict_types=1);

namespace Yiranzai\Dht;

/**
 * Class HashTest
 * @package Yiranzai\Dht
 */
class HashTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that true does in fact equal true
     */
    public function testCache()
    {
        $hash = new Hash();
        $hash->addEntityNode('db_server_one')->addEntityNode('db_server_two');
        $resOne = $hash->getLocation('key_one');
        Hash::cache($hash->toArray());
        $hash   = new Hash(Hash::getCache());
        $resTwo = $hash->getLocation('key_one');
        $this->assertSame($resOne, $resTwo);
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
