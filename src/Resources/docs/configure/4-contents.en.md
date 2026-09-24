---
lang: en
permalink: configure/contents
title: Contents
description: Synchronization options for your WordPress posts and pages.
updated: 2026-09-24
---

The **Contents** tab covers the editorial side of your site: posts and pages. They are
synchronized even without WooCommerce, and need no setting at all to work.

The two available options only deal with custom fields.

![The Contents tab of the Splash Sync settings](../assets/img/settings-contents.png "Contents tab")

### Posts Custom Fields

> Disabled by default.

Exposes your posts metadata as extra fields, in read and write. How it works in detail —
protected keys skipped, 200-key cap, list built from the database — is described on the
**Products** page of this section.

Turn it on when your posts carry business data written by a plugin: a guest author, a source,
an expiry date.

### Pages Custom Fields

> Disabled by default.

Same principle, applied to pages.

> [!TIP]
> Pages built with a page builder often store their whole layout in a single metadata. It can
> be large and unreadable: check what you are exposing before letting Splash carry it from one
> site to another.
