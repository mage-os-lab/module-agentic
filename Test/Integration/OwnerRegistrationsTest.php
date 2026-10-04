<?php

declare(strict_types=1);

namespace MageOS\Agentic\Test\Integration;

use Magento\Config\Model\Config\Structure;
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
     * The resource sits under MageOS_Seo's SEO resource, where admin roles already find it.
     *
     * @return void
     */
    public function testTheAclResourceIsRegisteredUnderSeo(): void
    {
        $acl = Bootstrap::getObjectManager()->get(AclBuilder::class)->getAcl();

        $this->assertTrue($acl->hasResource('MageOS_Agentic::config'));
        $this->assertTrue($acl->inheritsResource('MageOS_Agentic::config', 'MageOS_Seo::seo'));
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
}
