# PRODUCT.md — autoupgrade

## Product role

The **Update assistant** (`autoupgrade`) is the official PS native tool for **updating** a PrestaShop store to a more recent version. **It orchestrates the full update sequence** — requirements check, optional backup, file replacement, database migration, and module updates — while keeping the store restorable at any point.

It is available as a back-office wizard and as a CLI tool for integrators and automated pipelines.

---

## Users

**Agencies and developers**
The main profile in practice. They manage upgrades on behalf of merchants, using the back-office wizard in most cases and the CLI when the context requires it. They have enough technical knowledge to diagnose and react if something goes wrong.

**Merchants**
Some merchants — typically smaller stores — run the upgrade themselves via the back-office wizard. Others generally delegate to an agency or developer due to store complexity.

---

## Key flows

### Update wizard (5 steps)

**1. Version selection**
The user selects the target PrestaShop version. Requirements (PHP version, file permissions, module compatibility) are checked at this stage.

**2. Update options**
The user configures behaviour before the update runs:
- Uninstall incompatible modules (default: on)
- Regenerate email templates (default: on)
- Disable all overrides (default: off)

**3. Backup (optional)**
Creates a snapshot of store files and the database. Mandatory prerequisite if a restore is needed later.

**4. Update**
Downloads or reads a local archive, replaces store files, runs SQL migrations, updates modules.

Three update channels:
- `online` — latest PS version compatible with server PHP
- `online_recommended` — recommended version based on PHP
- `local` — ZIP + XML archive placed manually on the server

**5. Post-update**
Confirms the update is complete and surfaces any follow-up actions.

### Restore
Suggested automatically if the update fails. Can also be triggered manually at any point from the back office or CLI, as long as a backup exists.

---

## Business rules

- **Version support**: PrestaShop >= 1.7, PHP >= 7.1.

- **Module compatibility**: Incompatible modules are uninstalled before upgrade; updated versions are fetched from the PrestaShop release or the Marketplace.
- **Backup is opt-in**: The module proposes a backup before the update action but does not force it. No backup = no restore path.
- **Restore scope**: If an error occurs during the update process, the restore will be suggested. If the back office page is no longer accessible, it can also be triggered via CLI.
- **Non-native modules**: Disabled by default before upgrade to reduce compatibility risk (ignored for PS v9+).
- **Overrides**: Disabled by default during upgrade for better compatibility.

---

## Out of scope

- Manual or partial file patching
- Third-party module compatibility guarantee after upgrade
- Downgrading to an arbitrary PrestaShop version (only restore to a backup is supported)
- PrestaShop 1.6 and older (unmaintained — handled by the separate `4.14.x` branch)
- Multi-store upgrade orchestration (each store upgraded independently)
