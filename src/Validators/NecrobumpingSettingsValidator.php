<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Validators;

use Flarum\Foundation\AbstractValidator;

class NecrobumpingSettingsValidator extends AbstractValidator
{
    protected $rules = [
        'fof-prevent-necrobumping.days' => 'integer|min:0',
        'fof-prevent-necrobumping-hard.days' => 'integer|min:0',
    ];

    protected function getMessages(): array
    {
        return [
            'fof-prevent-necrobumping.days.min' => app('translator')->trans('fof-prevent-necrobumping.admin.settings.soft_min_error'),
        ];
    }
}
