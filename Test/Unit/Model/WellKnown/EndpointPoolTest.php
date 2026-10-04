<?php

declare(strict_types=1);

namespace MageOS\Agentic\Test\Unit\Model\WellKnown;

use MageOS\Agentic\Api\WellKnownEndpointInterface;
use MageOS\Agentic\Model\WellKnown\EndpointPool;
use PHPUnit\Framework\TestCase;

class EndpointPoolTest extends TestCase
{
    private function endpoint(string $name): WellKnownEndpointInterface
    {
        $endpoint = $this->createStub(WellKnownEndpointInterface::class);
        $endpoint->method('getName')->willReturn($name);

        return $endpoint;
    }

    public function testRegistersEndpointsByName(): void
    {
        $ucp = $this->endpoint('ucp');
        $pool = new EndpointPool([$ucp, $this->endpoint('change-password')]);

        $this->assertTrue($pool->has('ucp'));
        $this->assertTrue($pool->has('change-password'));
        $this->assertSame($ucp, $pool->get('ucp'));
    }

    public function testUnknownNameIsAbsent(): void
    {
        $pool = new EndpointPool([$this->endpoint('ucp')]);

        $this->assertFalse($pool->has('robots.txt'));
        $this->assertNull($pool->get('robots.txt'));
    }

    public function testEmptyPool(): void
    {
        $pool = new EndpointPool();

        $this->assertFalse($pool->has('ucp'));
        $this->assertNull($pool->get('ucp'));
    }
}
