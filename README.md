# MageOS_Agentic

Agentic commerce discovery for Magento Open Source and Mage-OS: the
[Universal Commerce Protocol](https://ucp.dev/) business profile at `/.well-known/ucp`, served
through a `/.well-known/` endpoint registry that other modules can add documents to.

This module was part of [mage-os/module-seo](https://github.com/mage-os-lab/module-seo)
(MageOS_Seo) on and before 2026-10-02. Its history up to then is kept in that repository.

---

## What it serves

| URL | Purpose | Default |
| --- | --- | --- |
| `/.well-known/ucp` | UCP 2026-08-25 business profile: the services and capabilities installed modules register (none by default) and the public signing keys. See [docs/ucp.md](docs/ucp.md) | Off |

The profile is a **discovery document only**. This module implements no UCP service, so it
declares only what another installed module registers; with nothing registered it is valid and
declares nothing.

`/.well-known/security.txt` is core's: Magento_Securitytxt serves it, configured under
**Stores → Configuration → Security → Security.txt**.

---

## Requirements

- PHP 8.3 – 8.5
- Magento Open Source / Mage-OS **2.4.7 or newer** (`magento/framework ^103.0.7`)
- **MageOS_Seo** (`mage-os/module-seo`). This module's settings sit under its admin tab and ACL
  resource, and the `/.well-known/` documents use its public-path registry (no session, so they
  stay shared-cacheable) and its canonical-path redirect.

---

## Installation

```bash
composer require mage-os/module-agentic
bin/magento module:enable MageOS_Agentic
bin/magento setup:upgrade
bin/magento cache:flush
```

---

## Admin configuration

**Stores → Configuration → MageOS SEO → Agentic Commerce (UCP)** (`mageos_agentic`), under the ACL
resource `MageOS_Agentic::config`:

| Group | Purpose | Default |
| --- | --- | --- |
| UCP Profile | Serve `/.well-known/ucp`, declaring what installed modules register ([docs/ucp.md](docs/ucp.md)) | Off |
| Signing Keys | Public JWK + encrypted private key (set by the keygen command) | — |

### Generate UCP signing keys

```bash
bin/magento ucp:keygen --website=1
bin/magento cache:flush config
```

This generates an ECDSA P-256 keypair, stores the private key **encrypted**, and stores/prints the
public JWK, which the profile publishes in `keys`. The private key is never printed, and a stored
key carrying private material makes the endpoint answer 500 rather than serve it.

---

## Extending the module

Each extension point is a pool wired via `di.xml`, so another module contributes from its **own**
`di.xml` without modifying this one.

| Extension point | Interface | Resolution |
| --- | --- | --- |
| Well-known endpoints | `WellKnownEndpointInterface` | by path segment |
| UCP capability providers | `UcpCapabilityProviderInterface` | collect-all, grouped by name ([docs/ucp.md](docs/ucp.md)) |
| UCP service providers | `UcpServiceProviderInterface` | collect-all, one per transport binding |

Example — serve a further `/.well-known/` document from your module's `di.xml`:

```xml
<type name="MageOS\Agentic\Model\WellKnown\EndpointPool">
    <arguments>
        <argument name="endpoints" xsi:type="array">
            <item name="change-password" xsi:type="object">Vendor\Account\Model\ChangePasswordEndpoint</item>
        </argument>
    </arguments>
</type>
```

---

## Development

```bash
composer install

# Run all quality gates
composer test

# Or individually
vendor/bin/phpunit -c phpunit.xml.dist --testsuite unit
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/php-cs-fixer fix --dry-run --diff --allow-risky=yes
vendor/bin/phpcs --standard=phpcs.xml.dist
XDEBUG_MODE=coverage vendor/bin/infection --threads=4  # gate: minMsi in infection.json5
```

Integration tests live under `Test/Integration/` and run in CI against a live Magento install via
[`graycoreio/github-actions-magento2`](https://github.com/graycoreio/github-actions-magento2).
They cannot be run locally without a full Magento installation.
