# Prevent Necrobumping by FriendsOfFlarum

![License](https://img.shields.io/badge/license-MIT-blue.svg) [![Latest Stable Version](https://img.shields.io/packagist/v/fof/prevent-necrobumping.svg)](https://packagist.org/packages/fof/prevent-necrobumping) [![OpenCollective](https://img.shields.io/badge/opencollective-fof-blue.svg)](https://opencollective.com/fof/donate) [![Donate](https://img.shields.io/badge/donate-datitisev-important.svg)](https://datitisev.me/donate)

A [Flarum](http://flarum.org) extension. Warn before necrobumping old discussions.

> **Necrobump (verb)**:
>
> To revive a long dormant forum thread by adding a new post, thus bringing it to the top of the forum list. Often a tactic of trolls attempting to control a forum.
>
> https://www.urbandictionary.com/define.php?term=necrobump

![screenshot](https://i.imgur.com/8gF7nXh.png)
![screenshot admin](https://i.imgur.com/IUhPJy2.png)

### Installation

```sh
composer require fof/prevent-necrobumping:"*"
```

### Updating

```sh
composer update fof/prevent-necrobumping
```

## Configuration

### Basic Settings

Configure the extension in the admin panel under Extensions → Prevent Necrobumping:

- **Days Until Discussion Considered Inactive**: Set the number of days after which a discussion is considered inactive (default: 30)
- **Show "Start New Discussion" Prompt**: Display a suggestion to start a new discussion when users attempt to reply to inactive discussions

### Tag-Specific Thresholds

If you have the `flarum/tags` extension installed, you can configure different inactivity thresholds for specific tags. When a discussion has multiple tags with different thresholds, the shortest period applies.

### Customizing Translations

You can customize the alert messages and prompts shown to users when they attempt to reply to inactive discussions. The extension provides the following translation keys:

#### Forum Translations

```yaml
fof-prevent-necrobumping:
  forum:
    composer:
      inactive_discussion_alert:
        title: The last reply to this discussion was {time}.
        description: Consider whether your reply adds new value or if the topic has already been resolved.
        cta: Consider starting a fresh discussion if you have a new question or perspective.
        cta_button: => core.ref.start_a_discussion
        confirmation: I understand this discussion is inactive, but my reply is relevant.
      error: This discussion is too old to reply to.
```

#### Admin Translations

```yaml
fof-prevent-necrobumping:
  admin:
    settings:
      general_heading: General Settings
      days_label: Days Until Discussion Considered Inactive
      days_help: Mark discussions as inactive after this many days since the last post. Set to <code>0</code> to disable by default.
      show_discussion_cta_label: Show "Start New Discussion" Prompt
      show_discussion_cta_help: Display a suggestion to start a new discussion when users attempt to reply to inactive discussions.
      tags_title: Tag-Specific Settings
      tags_help: Override the global inactivity threshold for specific tags. Leave empty to use the default. When multiple tags have different values, the shortest period applies.
      tags_placeholder: Default
```

#### Managing Translations

You can customize these translations in two ways:

1. **Using FoF Linguist** (Recommended)

   Install the [FoF Linguist](https://github.com/FriendsOfFlarum/linguist) extension to manage translations directly from your admin panel:

   ```sh
   composer require fof/linguist
   ```

   Then navigate to Admin → Extensions → Linguist to edit the translation keys listed above.

2. **Using Language Packs**

   If you're using a language pack, you can submit translation updates to the language pack repository. For example:

   - [Flarum Language Packs](https://github.com/flarum-lang)
   - Create a pull request with your translations for the keys above

### Links

[![OpenCollective](https://img.shields.io/badge/donate-friendsofflarum-44AEE5?style=for-the-badge&logo=open-collective)](https://opencollective.com/fof/donate) [![GitHub](https://img.shields.io/badge/donate-datitisev-ea4aaa?style=for-the-badge&logo=github)](https://datitisev.me/donate/github)

- [Packagist](https://packagist.org/packages/fof/prevent-necrobumping)
- [GitHub](https://github.com/FriendsOfFlarum/prevent-necrobumping)

An extension by [FriendsOfFlarum](https://github.com/FriendsOfFlarum), commissioned by [webdeveloper.com](https://webdeveloper.com).
