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
use FoF\PreventNecrobumping\Util;

class NecrobumpingSettingsValidator extends AbstractValidator
{
    protected array $rules = [
        'fof-prevent-necrobumping.days'         => 'integer|min:0',
        Util::LOCK_DAYS_KEY                     => 'integer|min:0',
        Util::LOCK_EXCLUDED_TAGS_KEY            => 'string|nullable',
    ];

    protected function getMessages(): array
    {
        return [
            'fof-prevent-necrobumping.days.min'
                => $this->translator->trans('fof-prevent-necrobumping.admin.settings.soft_min_error'),
        ];
    }
}
