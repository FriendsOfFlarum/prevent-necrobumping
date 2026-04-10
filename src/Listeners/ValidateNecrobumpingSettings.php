<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Listeners;

use Flarum\Foundation\ValidationException;
use Flarum\Settings\Event\Saving as SettingsSaving;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\PreventNecrobumping\Validators\NecrobumpingSettingsValidator;
use FoF\PreventNecrobumping\Util;
use Illuminate\Support\Arr;

class ValidateNecrobumpingSettings
{
    public function __construct(
        protected NecrobumpingSettingsValidator $validator,
        private SettingsRepositoryInterface $settings
    ) {
    }

    public function handle(SettingsSaving $event)
    {
        $data = $this->extractRelevantSettings($event);

        if ($data === []) {
            return;
        }

        $softDays = $this->softDays($data);
        $lockDays = $this->lockDays($data);
        $excludedTags = $this->excludedTags($data);

        $this->validator->assertValid([
            'fof-prevent-necrobumping.days'         => $softDays,
            Util::LOCK_DAYS_KEY                     => $lockDays,
            Util::LOCK_EXCLUDED_TAGS_KEY            => $excludedTags,
        ]);

        if ($softDays < 0) {
            $this->throwSoftDaysValidationException();
        }

        if ($lockDays > 0 && $lockDays < $softDays) {
            $this->throwLockDaysValidationException($softDays);
        }
    }

    protected function extractRelevantSettings(SettingsSaving $event): array
    {
        return Arr::only($event->settings, [
            'fof-prevent-necrobumping.days',
            Util::LOCK_DAYS_KEY,
            Util::LOCK_EXCLUDED_TAGS_KEY,
        ]);
    }

    protected function softDays(array $data): int
    {
        return (int) ($data['fof-prevent-necrobumping.days']
            ?? $this->settings->get('fof-prevent-necrobumping.days', 0)
            ?? 0);
    }

    protected function lockDays(array $data): int
    {
        return (int) ($data[Util::LOCK_DAYS_KEY]
            ?? $this->settings->get(Util::LOCK_DAYS_KEY, 0)
            ?? 0);
    }

    protected function excludedTags(array $data): ?string
    {
        return $data[Util::LOCK_EXCLUDED_TAGS_KEY]
            ?? $this->settings->get(Util::LOCK_EXCLUDED_TAGS_KEY, null);
    }

    protected function throwSoftDaysValidationException(): void
    {
        $message = resolve('translator')->trans(
            'fof-prevent-necrobumping.admin.settings.soft_min_error'
        );

        throw new ValidationException([
            'fof-prevent-necrobumping.days' => $message,
        ]);
    }

    protected function throwLockDaysValidationException(int $softDays): void
    {
        $message = resolve('translator')->trans(
            'fof-prevent-necrobumping.admin.settings.lock_min_error',
            ['soft' => $softDays]
        );

        throw new ValidationException([
            Util::LOCK_DAYS_KEY => $message,
        ]);
    }
}
