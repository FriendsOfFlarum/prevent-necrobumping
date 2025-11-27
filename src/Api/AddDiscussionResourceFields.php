<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Api;

use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\PreventNecrobumping\Util;

class AddDiscussionResourceFields
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Integer::make('fof-prevent-necrobumping')
                ->nullable()
                ->get(function (Discussion $discussion, $context) {
                    return Util::getDays($this->settings, $discussion);
                }),
        ];
    }
}
