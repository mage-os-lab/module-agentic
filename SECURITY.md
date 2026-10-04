# Security policy

## Supported versions

Security fixes are made on the latest release.

## Reporting a vulnerability

Please don't open a public GitHub issue for a security problem. Use GitHub's private advisory flow instead:

1. Go to https://github.com/mage-os-lab/module-agentic/security/advisories/new
2. Fill in a concise title and a clear description with reproduction steps.
3. Submit. The maintainers get notified privately.

## What to expect

- Initial acknowledgement within five working days.
- Triage + severity assessment within ten working days.
- A fix plan or a published advisory within thirty days of the report, depending on severity.
- Coordinated disclosure. We'll credit the reporter in the release notes unless you prefer anonymity.

## Scope

In scope:
- XSS, CSRF, privilege escalation, SSRF, or path traversal in any module code under this repository.
- Exposure of signing-key material: the private key is stored encrypted and must never be served or
  printed.
- Authorization bypass in the module's admin configuration or CLI command.

Out of scope (not a MageOS_Agentic vulnerability):
- Issues in Magento / Mage-OS core, MageOS_Seo, or unrelated third-party modules.
- Social engineering, physical attacks, denial-of-service by volume.
- Findings that require admin-role access already granted by the merchant.

## Hardening

- Keep Magento / Mage-OS on a supported security patch level.
- Run `composer audit` regularly and apply dependency updates.
- Enable GitHub Dependabot alerts for your own fork (it's on by default for public repos).
