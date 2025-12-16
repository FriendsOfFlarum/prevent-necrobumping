<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Policy;

use Carbon\Carbon;
use Flarum\Extension\ExtensionManager;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Discussion\Discussion;
use Flarum\User\User;
use Flarum\User\Access\AbstractPolicy;
use FoF\PreventNecrobumping\Util;
use FoF\PreventNecrobumping\Validators\NecrobumpingPostValidator;
use Flarum\Lock\Event\DiscussionWasLocked;
use Flarum\Lock\Event\DiscussionWasUnlocked;

class DiscussionPolicyExtender extends AbstractPolicy
{
    /**
     * @var NecrobumpingPostValidator
     */
    protected $validator;

    /**
     * @var SettingsRepositoryInterface
     */
    private $settings;

    /**
     * @var ExtensionManager
     */
    protected $extensions;

    public function __construct(NecrobumpingPostValidator $validator, SettingsRepositoryInterface $settings, ExtensionManager $extensions)
    {
        $this->validator = $validator;
        $this->settings = $settings;
        $this->extensions = $extensions;
    }

    public function reply(User $actor, Discussion $discussion): void
    {
        $this->applyLimitSideEffects($discussion, $actor);
    }

    private function applyLimitSideEffects(Discussion $discussion, $actor): void
    {
        // If nothing has been posted, ignore
        if (! $discussion->last_posted_at) {
            return;
        }

        $eventActor = $actor instanceof User ? $actor : ($discussion->user instanceof User ? $discussion->user : null);
        $diffDays = $discussion->last_posted_at->diffInDays(Carbon::now());

        if ($this->extensions->isEnabled('flarum-tags')) {
            $excludedTags = Util::getExcludedTagIds($this->settings);

            if ($excludedTags && Util::hasExcludedTag($discussion, $excludedTags)) {
                if ($discussion->is_locked) {
                    $discussion->is_locked = false;
                    $discussion->raise(new DiscussionWasUnlocked($discussion, $eventActor));
                    $discussion->save();
                }

                return;
            }
        }

        $lockDays = Util::getAutoLockDays($this->settings, $discussion);

        if (! $lockDays) {
            if ($discussion->is_locked) {
                $discussion->is_locked = false;
                $discussion->raise(new DiscussionWasUnlocked($discussion, $eventActor));
                $discussion->save();
            }

            return;
        }

        $shouldLock = $diffDays >= $lockDays;
        $needsChange = (! $shouldLock && $discussion->is_locked) || ($shouldLock && ! $discussion->is_locked);

        if ($needsChange) {
            $discussion->is_locked = $shouldLock;
            $discussion->raise($shouldLock ? new DiscussionWasLocked($discussion, $eventActor) : new DiscussionWasUnlocked($discussion, $eventActor));
            $discussion->save();
        }
    }
}
