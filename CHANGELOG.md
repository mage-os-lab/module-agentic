# Changelog

All notable changes to this module are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versions follow
[Semantic Versioning](https://semver.org/). Releases are cut from git tags —
the tag is the source of truth for the version (composer.json carries no
hardcoded version field).

**Origin.** This module was part of
[mage-os/module-seo](https://github.com/mage-os-lab/module-seo) (MageOS_Seo) on and
before 2026-10-02. Its history up to then is kept in that repository.

## [Unreleased]

### Added

- **Split from mage-os/module-seo.** The `/.well-known/` endpoint registry and router, the UCP
  business profile at `/.well-known/ucp` and the `ucp:keygen` command come from MageOS_Seo,
  which this module requires: its admin tab and ACL parent hold these settings, and its public-path
  registry and canonical-path redirect serve the documents.
  - **Unchanged:** the settings (`mageos_agentic/*`, under Stores → Configuration → MageOS SEO →
    Agentic Commerce (UCP)), the ACL resource `MageOS_Agentic::config`, the internal URL
    `mageos-agentic/…` and the command.
  - **Breaking: the namespace is `MageOS\Agentic\`**, where it was `MageOS\Seo\`. The class names
    after it are unchanged: `Api\WellKnownEndpointInterface`, `Api\UcpCapabilityProviderInterface`,
    `Api\UcpServiceProviderInterface`, `Model\WellKnown\*`, `Model\Ucp\*`,
    `Model\Router\WellKnownRouter`, `Controller\Wellknown\Index` and
    `Console\Command\UcpKeygenCommand`. A module that registers a UCP provider or a well-known
    endpoint updates the type names in its di.xml and its imports.
  - Log lines start `MageOS_Agentic:`, where they started `MageOS_Seo:`.
- `Api\UcpServiceProviderInterface` and `Model\Ucp\ServicePool`: a module that serves a UCP service
  declares each transport binding in `/.well-known/ucp` from its own di.xml. Every service and
  capability entry is checked against the UCP 2026-08-25 business schema (`Model\Ucp\EntryValidator`),
  including the namespace binding of its `schema` URL. An entry that fails is logged with its
  provider and the reasons, and left out. See `docs/ucp.md`.

### Changed

- **Breaking: the settings, command and internal URL have their own prefix.**
  - The section is **Agentic Commerce (UCP)**, `mageos_agentic/{general,signing}/*` (was
    `mageos_seo_ucp`), under `MageOS_Agentic::config`. Nothing is migrated: enter the settings
    again and grant admin roles the new resource. Keys made with the old command are not carried
    over: run `bin/magento ucp:keygen` again.
  - `mageos:seo:ucp:keygen` becomes **`ucp:keygen`**.
  - The internal URL is `mageos-agentic/wellknown/index/…` (it 301s to the document).
- `Api\UcpCapabilityProviderInterface::getCapabilityData()` returns **one** capability entry
  (`version`, `schema`, …). It is listed under `getCapabilityKey()` along with any other provider's
  entries for that name, where before it was merged into the manifest as given. Constructors
  changed with it:
  - `Ucp\CapabilityPool` takes `EntryValidator` and `LoggerInterface` before `$providers`;
  - `Ucp\ProfileBuilder` takes `ServicePool` and `LoggerInterface`.

### Removed

- **`/.well-known/security.txt`.** Core's Magento_Securitytxt serves it: configure it under
  **Stores → Configuration → Security → Security.txt**.
  - **Settings removed:** **Agentic Commerce (UCP) → security.txt (RFC 9116)**
    (`mageos_agentic/security_txt/{enabled,contact_email,expires,policy_url}`). Saved values stay in
    `core_config_data`, unread.
  - **Code removed:** `Model\Ucp\SecurityTxtBuilder`, `Model\WellKnown\Endpoint\SecurityTxtEndpoint`,
    `Model\Config\Backend\SecurityTxtExpires`, `Block\Adminhtml\System\Config\Field\Date`, and
    `UcpConfig::isSecurityTxtEnabled()`, `getSecurityContactEmail()`, `getSecurityExpires()`,
    `getSecurityPolicyUrl()` with their `XML_SECURITY_TXT_*` constants.
- `/.well-known/ai-plugin.json` and its settings. It now answers 404.
  - **Settings removed:** **SEO Agentic Commerce (UCP) → AI Plugin Manifest**
    (`mageos_seo_ucp/ai_plugin/{enabled,description,legal_url}`) and **Merchant Name**
    (`mageos_seo_ucp/general/merchant_name`), which only the manifest read.
  - **Code removed:** `Model\Ucp\AiPluginBuilder`, `Model\WellKnown\Endpoint\AiPluginEndpoint`,
    `UcpConfig::isAiPluginEnabled()`, `getMerchantName()`, `getBaseUrl()`,
    `getAiPluginDescription()`, `getAiPluginLegalUrl()`, `getSupportEmail()` and their constants.
    `Ucp\UcpConfig`'s constructor no longer takes `StoreManagerInterface`.
  - **Why:** it made two false claims.
    - It was the manifest of OpenAI's ChatGPT plugins beta, which OpenAI ended in March–April 2024.
      OpenAI's plugins today are packages from its plugin directory, not a manifest a site hosts.
    - Its `auth: none` pointed agents at `catalogProductRepositoryV1`, whose methods need the admin
      ACL `Magento_Catalog::products`. Without core's Allow Anonymous Guest Access, an agent got a
      schema with no operations and a 401.
  - Saved values stay in `core_config_data`, unread.
- **SEO Agentic Commerce (UCP) → Advertised Capabilities**, the five toggles
  (`mageos_seo_ucp/capabilities/{catalog,cart,checkout,identity_linking,order_management}`), and
  **UCP Profile → Merchant ID** (`mageos_seo_ucp/general/merchant_id`). With them go
  `UcpConfig::getEnabledCapabilities()`, `getMerchantId()`, `getDomainHost()` and
  `UcpConfig::XML_UCP_MERCHANT_ID`.
  - The toggles declared UCP capabilities that nothing in the store implements.
  - UCP has no merchant member.

  Values already saved stay in `core_config_data`, unread.

### Fixed

- `/.well-known/ucp` did not follow UCP.
  - **Before:**
    - it served `$schema` (`https://json-schema.org`, which is not a schema), `version`,
      `merchant`, `transports`, and a `capabilities` map of `enabled` flags, none of which the
      spec defines;
    - it declared `dev.ucp.shopping`, and one capability per admin toggle, that nothing in the
      store served.
  - **Now:**
    - it is a UCP 2026-08-25 business profile: `{"ucp": {"version", "services", "capabilities",
      "payment_handlers"}}`, plus `keys`;
    - it declares only what an installed module registers, so out of the box it declares nothing.
  - **Signing key:**
    - it moves from `signing_keys` to `keys`;
    - a stored key carrying any private member (`d`, `p`, `q`, `dp`, `dq`, `qi`, `oth` or `k`, not
      only `d`) refuses the profile;
    - a stored key the schema would reject is logged and left out.

  See `docs/ucp.md`.
- The `/.well-known/` documents no longer start a session. A session sets a cookie, which stops
  shared caches storing the response at all, and makes PHP emit `Pragma: no-cache` over the
  caching policy the controller just set. None of these endpoints read session state.
  `/.well-known/` also gained a canonical-path redirect, so query-string and internal-URL variants
  collapse onto one cacheable URL.
