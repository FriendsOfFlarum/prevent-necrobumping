<?php

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
                })
        ];
    }
}
