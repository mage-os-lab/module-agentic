<?php

declare(strict_types=1);

namespace MageOS\Agentic\Model\Ucp;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration reader for the UCP / well-known subsystem.
 *
 * Most UCP values are website-scoped; reads use store scope so they resolve through the normal
 * default -> website -> store hierarchy for whichever store is active on the request.
 */
class UcpConfig
{
    public const XML_UCP_ENABLED         = 'mageos_agentic/general/enabled';
    public const XML_UCP_SIGNING_JWK     = 'mageos_agentic/signing/public_key_jwk';
    public const XML_UCP_SIGNING_PRIVATE = 'mageos_agentic/signing/private_key';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Whether the UCP profile is enabled.
     *
     * @return bool
     */
    public function isUcpEnabled(): bool
    {
        return $this->flag(self::XML_UCP_ENABLED);
    }

    /**
     * The stored public signing key JWK JSON, or empty string when keygen has not been run.
     *
     * @return string
     */
    public function getPublicKeyJwk(): string
    {
        return $this->value(self::XML_UCP_SIGNING_JWK);
    }

    /**
     * Read a string config value at store scope.
     *
     * @param string $path
     * @return string
     */
    private function value(string $path): string
    {
        return trim((string) $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE));
    }

    /**
     * Read a boolean config flag at store scope.
     *
     * @param string $path
     * @return bool
     */
    private function flag(string $path): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE);
    }
}
