<?php

declare(strict_types=1);

namespace MageOS\Agentic\Test\Unit\Model\WellKnown;

use MageOS\Agentic\Model\Ucp\ProfileBuilder;
use MageOS\Agentic\Model\Ucp\UcpConfig;
use MageOS\Agentic\Model\WellKnown\Endpoint\UcpEndpoint;
use PHPUnit\Framework\TestCase;

class EndpointTest extends TestCase
{
    public function testUcpEndpoint(): void
    {
        $config = $this->createStub(UcpConfig::class);
        $config->method('isUcpEnabled')->willReturn(true);
        $profile = $this->createStub(ProfileBuilder::class);
        $profile->method('build')->willReturn(['ucp' => ['version' => '2026-08-25']]);

        $endpoint = new UcpEndpoint($config, $profile);

        $this->assertSame('ucp', $endpoint->getName());
        $this->assertTrue($endpoint->isEnabled());
        $this->assertStringContainsString('application/json', $endpoint->getContentType());
        $this->assertStringContainsString('max-age=300', $endpoint->getCacheControl());
        $this->assertJsonStringEqualsJsonString('{"ucp":{"version":"2026-08-25"}}', $endpoint->render());
    }
}
