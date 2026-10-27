<?php

declare(strict_types=1);

namespace MageOS\Agentic\Test\Integration;

use Magento\Config\Model\Config\Structure;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\Console\CommandListInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * The identifiers this module owns, as Magento sees them once the configuration is merged: the
 * settings under `mageos_agentic`, the ACL resource `MageOS_Agentic::config` and the `ucp:keygen`
 * command. A store's data, admin roles and other modules refer to these, so each is pinned here.
 *
 * @magentoAppArea adminhtml
 */
class OwnerRegistrationsTest extends TestCase
{
    /**
     * @return void
     */
    public function testTheSettingsHaveASectionOfTheirOwn(): void
    {
        $structure = Bootstrap::getObjectManager()->get(Structure::class);
        $paths     = array_keys($structure->getFieldPaths());

        foreach (['general/enabled', 'signing/private_key', 'signing/public_key_jwk'] as $field) {
            $this->assertContains('mageos_agentic/' . $field, $paths);
            $this->assertNotContains('mageos_seo_ucp/' . $field, $paths);
        }

        // Core's Magento_Securitytxt has security.txt's settings.
        foreach (['enabled', 'contact_email', 'expires', 'policy_url'] as $field) {
            $this->assertNotContains('mageos_agentic/security_txt/' . $field, $paths);
        }

        $section = $structure->getElement('mageos_agentic');
        $this->assertSame('Agentic Commerce (UCP)', (string) $section->getLabel());
        $this->assertSame('MageOS_Agentic::config', $section->getAttribute('resource'));
    }

    /**
     * @return void
     */
    public function testTheAclResourceSitsWithEveryOtherConfigurationSection(): void
    {
        $acl = Bootstrap::getObjectManager()->get(AclBuilder::class)->getAcl();

        $this->assertTrue($acl->hasResource('MageOS_Agentic::config'));
        $this->assertSame(
            [
                'Magento_Backend::admin',
                'Magento_Backend::stores',
                'Magento_Backend::stores_settings',
                'Magento_Config::config',
                'MageOS_Agentic::config',
            ],
            $this->ancestry('MageOS_Agentic::config')
        );
    }

    /**
     * The name keeps to one colon.
     *
     * @return void
     */
    public function testTheKeygenCommandIsRegistered(): void
    {
        $names = array_map(
            static fn ($command): string => (string) $command->getName(),
            Bootstrap::getObjectManager()->get(CommandListInterface::class)->getCommands()
        );

        $this->assertContains('ucp:keygen', $names);
        $this->assertNotContains('mageos:seo:ucp:keygen', $names);
    }

    /**
     * The resource IDs from the root down to the one given, as the merged ACL tree has them.
     *
     * @param string $resourceId
     * @return string[]
     */
    private function ancestry(string $resourceId): array
    {
        $tree = Bootstrap::getObjectManager()->get(ProviderInterface::class)->getAclResources();

        return $this->find($tree, $resourceId, []) ?? [];
    }

    /**
     * Depth-first search for a resource, carrying the path to it.
     *
     * @param array<int, array<string, mixed>> $nodes
     * @param string $resourceId
     * @param string[] $path
     * @return string[]|null
     */
    private function find(array $nodes, string $resourceId, array $path): ?array
    {
        foreach ($nodes as $node) {
            $here   = $path;
            $here[] = (string) $node['id'];
            if ($node['id'] === $resourceId) {
                return $here;
            }
            $found = $this->find($node['children'] ?? [], $resourceId, $here);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }
}
