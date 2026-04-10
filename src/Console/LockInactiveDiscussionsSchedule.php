<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Console;

use Illuminate\Console\Scheduling\Event;

class LockInactiveDiscussionsSchedule
{
    public function __invoke(Event $event): void
    {
        $event->dailyAt('00:00')
            ->withoutOverlapping();
    }
}
