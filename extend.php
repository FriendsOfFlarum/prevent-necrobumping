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

use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Extend;
use Flarum\Post\Event\Saving;
use Flarum\Settings\Event\Saving as SettingsSaving;
use FoF\Extend\Extend as FoFExtend;
use FoF\PreventNecrobumping\Console\LockInactiveDiscussionsCommand;
use FoF\PreventNecrobumping\Console\LockInactiveDiscussionsSchedule;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new FoFExtend\ExtensionSettings())
        ->setPrefix('fof-prevent-necrobumping.')
        ->addKeys(['message.title', 'message.description', 'message.agreement']),

    (new Extend\Settings())
        ->default('fof-prevent-necrobumping.show_discussion_cta', false)
        ->serializeToForum('fof-prevent-necrobumping.show_discussion_cta', 'fof-prevent-necrobumping.show_discussion_cta', 'boolval')
        ->serializeToForum('fof-prevent-necrobumping-hard.days', 'fof-prevent-necrobumping-hard.days', 'intval'),

    (new Extend\Event())
        ->listen(Saving::class, Listeners\ValidateNecrobumping::class)
        ->listen(SettingsSaving::class, Listeners\ValidateNecrobumpingSettings::class),

    (new Extend\Console())
        ->command(LockInactiveDiscussionsCommand::class)
        ->schedule(LockInactiveDiscussionsCommand::class, LockInactiveDiscussionsSchedule::class),

    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->attributes(Listeners\AddForumAttributes::class),
];
