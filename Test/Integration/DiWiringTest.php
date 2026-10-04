<?php

declare(strict_types=1);

namespace MageOS\Agentic\Test\Integration;

use Magento\Framework\App\RouterList;
use Magento\TestFramework\Helper\Bootstrap;
use MageOS\Agentic\Model\Router\WellKnownRouter;
use MageOS\Agentic\Model\WellKnown\EndpointPool;
use MageOS\Seo\Model\Router\PublicPaths;
use PHPUnit\Framework\TestCase;

/**
 * This module's registrations, as the merged DI configuration builds them.
 *
 * @magentoAppArea frontend
 */
class DiWiringTest extends TestCase
{
    /**
     * @return void
     */
    public function testTheEndpointPoolServesOnlyTheUcpProfile(): void
    {
        /** @var EndpointPool $pool */
        $pool = Bootstrap::getObjectManager()->get(EndpointPool::class);

        $this->assertTrue($pool->has('ucp'));
        // Core's Magento_Securitytxt serves security.txt.
        $this->assertFalse($pool->has('security.txt'));
        // Retired: see RetiredAiPluginManifestTest.
        $this->assertFalse($pool->has('ai-plugin.json'));
    }

    /**
     * The /.well-known/ router is in the frontend router list, before core's URL-rewrite router.
     *
     * @return void
     */
    public function testTheWellKnownRouterRunsBeforeTheUrlRewriteRouter(): void
    {
        $routers = iterator_to_array(Bootstrap::getObjectManager()->create(RouterList::class));

        $this->assertInstanceOf(WellKnownRouter::class, $routers['mageos_agentic_wellknown'] ?? null);
        $this->assertSame(
            ['mageos_agentic_wellknown', 'urlrewrite'],
            array_values(array_intersect(array_keys($routers), ['mageos_agentic_wellknown', 'urlrewrite']))
        );
    }

    /**
     * The documents and their internal URL are registered with MageOS_Seo as public, so no session
     * is started for them.
     *
     * @return void
     */
    public function testTheWellKnownPathsArePublic(): void
    {
        $publicPaths = Bootstrap::getObjectManager()->get(PublicPaths::class);

        $this->assertTrue($publicPaths->isPublicPath('/.well-known/ucp'));
        $this->assertTrue($publicPaths->isPublicPath('/mageos-agentic/wellknown/index/endpoint/ucp'));
    }
}
