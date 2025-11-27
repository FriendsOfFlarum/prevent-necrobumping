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

use Flarum\Api\Resource\DiscussionResource;
use Flarum\Api\Resource\PostResource;
use Flarum\Extend;
use Flarum\Post\Event\Saving;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\Settings())
        ->default('fof-prevent-necrobumping.days', 0)
        ->default('fof-prevent-necrobumping.show_discussion_cta', false)
        ->serializeToForum('fof-prevent-necrobumping.show_discussion_cta', 'fof-prevent-necrobumping.show_discussion_cta', 'boolval'),

    (new Extend\ApiResource(DiscussionResource::class))
        ->fields(Api\AddDiscussionResourceFields::class),

    (new Extend\ApiResource(PostResource::class))
        ->fields(Api\AddPostResourceFields::class),

    (new Extend\Event())
        ->listen(Saving::class, Listeners\ValidateNecrobumping::class),
];
