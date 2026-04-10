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
use Illuminate\Support\Arr;

class ValidateNecrobumpingSettings
{
    protected NecrobumpingSettingsValidator $validator;
    private SettingsRepositoryInterface $settings;

    public function __construct(NecrobumpingSettingsValidator $validator, SettingsRepositoryInterface $settings)
    {
        $this->validator = $validator;
        $this->settings = $settings;
    }

    public function handle(SettingsSaving $event)
    {
        $settingsKeys = [
            'fof-prevent-necrobumping.days',
            'fof-prevent-necrobumping-hard.days',
        ];

        $data = Arr::only($event->settings, $settingsKeys);

        if ($data === []) {
            return;
        }

        $softDays = (int) ($data['fof-prevent-necrobumping.days']
            ?? $this->settings->get('fof-prevent-necrobumping.days', 0)
            ?? 0);
        $hardDays = (int) ($data['fof-prevent-necrobumping-hard.days']
            ?? $this->settings->get('fof-prevent-necrobumping-hard.days', 0)
            ?? 0);

        $this->validator->assertValid([
            'fof-prevent-necrobumping.days'         => $softDays,
            'fof-prevent-necrobumping-hard.days'    => $hardDays,
        ]);

        if ($softDays < 0) {
            $message = resolve('translator')->trans(
                'fof-prevent-necrobumping.admin.settings.soft_min_error'
            );

            throw new ValidationException([
                'fof-prevent-necrobumping.days' => $message,
            ]);
        }

        if ($hardDays > 0 && $hardDays < $softDays) {
            $message = resolve('translator')->trans(
                'fof-prevent-necrobumping.admin.settings.hard_min_error',
                ['soft' => $softDays]
            );

            throw new ValidationException([
                'fof-prevent-necrobumping-hard.days' => $message,
            ]);
        }
    }
}
