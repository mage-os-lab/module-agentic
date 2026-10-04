<?php

declare(strict_types=1);

namespace MageOS\Agentic\Test\Unit\Model\Ucp;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use MageOS\Agentic\Model\Ucp\UcpConfig;
use PHPUnit\Framework\TestCase;

class UcpConfigTest extends TestCase
{
    /**
     * The UCP flag is read from its own path at store scope.
     *
     * @return void
     */
    public function testTheEnabledFlagIsReadAtStoreScope(): void
    {
        $this->assertTrue($this->config([], [UcpConfig::XML_UCP_ENABLED => true])->isUcpEnabled());
        $this->assertFalse($this->config([], [UcpConfig::XML_UCP_ENABLED => false])->isUcpEnabled());
    }

    /**
     * The public key is read trimmed, and '' when unset.
     *
     * @return void
     */
    public function testThePublicKeyIsReadTrimmed(): void
    {
        $config = $this->config([UcpConfig::XML_UCP_SIGNING_JWK => " {\"kid\":\"k\"}\n"]);

        $this->assertSame('{"kid":"k"}', $config->getPublicKeyJwk());
        $this->assertSame('', $this->config([])->getPublicKeyJwk());
    }

    /**
     * @param array<string,string> $values
     * @param array<string,bool> $flags
     * @return UcpConfig
     */
    private function config(array $values, array $flags = []): UcpConfig
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(
            static fn (string $path, string $scope): ?string
                => $scope === ScopeInterface::SCOPE_STORE ? ($values[$path] ?? null) : null
        );
        $scopeConfig->method('isSetFlag')->willReturnCallback(
            static fn (string $path, string $scope): bool
                => $scope === ScopeInterface::SCOPE_STORE && ($flags[$path] ?? false)
        );

        return new UcpConfig($scopeConfig);
    }
}
