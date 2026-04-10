<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping;

use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;

class Util
{
    public const LOCK_DAYS_KEY = 'fof-prevent-necrobumping.lock_days';
    public const LOCK_EXCLUDED_TAGS_KEY = 'fof-prevent-necrobumping.lock_days_exclude_tags';

    public static function getConfiguredLockDays(SettingsRepositoryInterface $settings): int
    {
        return (int) $settings->get(self::LOCK_DAYS_KEY, 0);
    }

    public static function getDays(SettingsRepositoryInterface $settings, Discussion $discussion): ?int
    {
        $days = $settings->get('fof-prevent-necrobumping.days');
        /** @phpstan-ignore-next-line */
        $tags = $discussion->tags;

        if ($tags && $tags->isNotEmpty()) {
            $tagDays = $tags->map(function ($tag) use ($settings) {
                return $settings->get("fof-prevent-necrobumping.days.tags.{$tag->id}");
            })->filter(function ($days) {
                return $days !== null && $days !== '' && !is_nan((float) $days);
            });

            if ($tagDays->isNotEmpty()) {
                $days = $tagDays->contains(0)
                    ? null
                    : $tagDays->min();
            }
        }

        return is_nan((float) $days) || (int) $days < 1 ? null : (int) $days;
    }

    public static function getExcludedTagIds(SettingsRepositoryInterface $settings): array
    {
        $raw = (string) $settings->get(self::LOCK_EXCLUDED_TAGS_KEY, '');

        return array_values(array_filter(array_map('intval', explode(',', $raw))));
    }

    public static function getAutoLockDays(SettingsRepositoryInterface $settings, Discussion $discussion): ?int
    {
        $days = self::getConfiguredLockDays($settings);

        return $days > 0 ? $days : null;
    }

    public static function hasExcludedTag(Discussion $discussion, array $excludedTagIds): bool
    {
        if ($excludedTagIds === []) {
            return false;
        }

        if ($discussion->relationLoaded('tags')) {
            return $discussion->tags && $discussion->tags->whereIn('id', $excludedTagIds)->isNotEmpty();
        }

        return $discussion->tags()->whereIn('tags.id', $excludedTagIds)->exists();
    }
}
