<?php

declare(strict_types=1);

namespace MageOS\Agentic\Test\Integration\Controller;

use Magento\TestFramework\TestCase\AbstractController;

/**
 * The standard-router URL behind the /.well-known/ documents answers with a 301 to the document's
 * canonical path.
 *
 * @magentoAppArea frontend
 */
class InternalRoutesTest extends AbstractController
{
    /**
     * @return void
     */
    public function testAWellKnownDocumentsInternalUrlRedirectsToItsCanonicalPath(): void
    {
        $this->dispatch('mageos-agentic/wellknown/index/endpoint/ucp');

        $this->assertSame(301, $this->getResponse()->getHttpResponseCode());
        $this->assertRedirect($this->stringEndsWith('/.well-known/ucp'));
    }
}
