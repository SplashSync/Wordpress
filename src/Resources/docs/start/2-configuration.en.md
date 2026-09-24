---
lang: en
permalink: start/configure
title: Configure the Plugin
description: Connect your site to your Splash account, pick the default user and check the self-tests.
updated: 2026-09-24
---

### Open the settings

Once the plugin is active, its settings live in your WordPress admin, under
**Settings > Splash Sync**.

![The Splash Sync settings screen, reached from the WordPress menu](../assets/img/settings-overview.png "Settings > Splash Sync")

The screen is split into tabs: **Connection**, then one tab per family of objects —
**Products**, **Orders**, **Users**, **Contents** — and an **Informations** tab that runs
the self-tests.

### Connect to your Splash account

Your site needs a pair of keys to identify itself. Create them on your Splash workspace: open
**My Servers**, then click **New Server**. Splash hands you an identifier and an encryption key
for this site.

![The New Server button on the My Servers page of the Splash workspace](../assets/img/splash-new-server.png "My Servers")

Report both on the **Connection** tab, taking care not to drop a single character.

![The Connection tab of the plugin settings](../assets/img/settings-connection.png "Plugin settings")

##### User

Pick the account Splash acts as for every operation it performs on your site. We strongly
recommend creating a **dedicated** user for Splash.

> [!IMPORTANT]
> The plugin enforces WordPress capabilities: this user must hold the rights needed to read
> and write the objects you intend to synchronize.

### Adjust the objects options

The other tabs hold the options of each family of objects. They all work out of the box, so
come back to them only when you need to:

* **Products** — expose product custom fields to Splash.
* **Orders** — expose orders & invoices custom fields, append line options to item names, and
  synchronize the delivery & billing addresses entered on each order as read-only objects.
* **Users** — turn off the synchronization of customers shipping or billing addresses.
* **Contents** — expose posts & pages custom fields.

Every one of them is detailed, option by option, in the **Configuration** section.

### Check the self-tests

Every time you save your settings, the plugin verifies them and checks that it talks to Splash
properly. The **Informations** tab lists what your site exposes and replays both tests.

![Self-tests results on the Informations tab](../assets/img/settings-informations.png "Informations tab")

> [!WARNING]
> All tests must pass. As long as one of them fails, no synchronization will happen.
