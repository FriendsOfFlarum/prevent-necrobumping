<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Job;

use Flarum\Discussion\Discussion;
use Flarum\Lock\Event\DiscussionWasLocked;
use Flarum\Queue\AbstractJob;
use Flarum\User\User;

class LockInactiveDiscussionsChunkJob extends AbstractJob
{
    /**
     * @param array<int> $discussionIds
     */
    public function __construct(protected array $discussionIds)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        if ($this->discussionIds === []) {
            return;
        }

        $discussions = Discussion::query()
            ->with('user')
            ->select(['id', 'user_id', 'is_locked'])
            ->whereIn('id', $this->discussionIds)
            ->where('is_locked', false)
            ->orderBy('id')
            ->get();

        foreach ($discussions as $discussion) {
            $discussion->is_locked = true;

            if (class_exists(DiscussionWasLocked::class) && $discussion->user instanceof User) {
                $discussion->raise(new DiscussionWasLocked($discussion, $discussion->user));
            }

            $discussion->save();
        }
    }
}
