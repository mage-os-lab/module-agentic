<?php

declare(strict_types=1);

namespace MageOS\Agentic\Model\WellKnown\Endpoint;

use MageOS\Agentic\Api\WellKnownEndpointInterface;
use MageOS\Agentic\Model\Ucp\ProfileBuilder;
use MageOS\Agentic\Model\Ucp\UcpConfig;

/**
 * Serves /.well-known/ucp — the Universal Commerce Protocol discovery manifest.
 */
class UcpEndpoint implements WellKnownEndpointInterface
{
    /**
     * @param UcpConfig $config
     * @param ProfileBuilder $profileBuilder
     */
    public function __construct(
        private readonly UcpConfig $config,
        private readonly ProfileBuilder $profileBuilder
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'ucp';
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->config->isUcpEnabled();
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/json; charset=utf-8';
    }

    /**
     * @inheritDoc
     */
    public function getCacheControl(): string
    {
        return 'public, max-age=300';
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return (string) json_encode($this->profileBuilder->build(), JSON_UNESCAPED_SLASHES);
    }
}
