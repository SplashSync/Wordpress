---
lang: en
permalink: configure/advanced
title: Advanced
description: Advanced mode, Splash server address and communication protocol.
updated: 2026-09-24
---

These parameters sit at the bottom of the **Connection** tab. They change the way your site
reaches our servers.

> [!CAUTION]
> Only use them upon our request. A wrong value cuts the communication with Splash, without any
> explicit error on the WordPress side: your server simply stops answering.

### Enable advanced mode

> Disabled by default.

Unlocks the two parameters below. As long as this box stays unticked, the plugin uses its
default values, which suit virtually every installation.

### Server Url

> Default value: `www.splashsync.com/ws/soap`

The address of the Splash server your site contacts. You only have a reason to change it in two
cases: our support points you to a dedicated infrastructure, or you are setting up a test
environment against a local server.

### Protocol

> Default value: `Generic PHP SOAP`

The transport used to talk to Splash. As long as advanced mode stays off, the plugin relies on
PHP's native SOAP implementation, whatever the drop-down happens to display.

| Protocol | When to use it |
|---|---|
| **Generic PHP SOAP** | Default value and the recommended choice. Relies on the PHP `soap` extension. |
| **NuSOAP Librairie** | Bundled implementation, to be kept for hosts without the `soap` extension. |

> [!WARNING]
> The drop-down pre-selects **NuSOAP** as long as no value has been saved, while the protocol
> actually in use is `Generic PHP SOAP`. Enabling advanced mode then saving without touching
> this field therefore switches your site to NuSOAP silently. Pick the protocol you want
> explicitly before saving.

### Check after every change

Saving anything in this tab replays the self-tests and a connection attempt. Go through the
**Informations** tab to confirm that the ping and the server connection still answer before
leaving the page.
