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

use Carbon\Carbon;
use Flarum\Extension\ExtensionManager;
use Flarum\Foundation\ValidationException;
use Flarum\Post\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\PreventNecrobumping\Util;
use FoF\PreventNecrobumping\Validators\NecrobumpingPostValidator;
use Illuminate\Support\Arr;

class ValidateNecrobumping
{
    public function __construct(
        protected NecrobumpingPostValidator $validator,
        private SettingsRepositoryInterface $settings,
        protected ExtensionManager $extensions
    ) {
    }

    public function handle(Saving $event)
    {
        $post = $event->post;
        $discussion = $post->discussion;

        if (!$this->shouldValidate($post, $discussion)) {
            return;
        }

        $diffDays = $this->discussionAgeInDays($discussion);

        if ($diffDays === null) {
            return;
        }

        if ($this->isHardBlocked($diffDays)) {
            $this->throwHardBlock();
        }

        if ($this->requiresConfirmation($discussion, $diffDays)) {
            $this->assertConfirmation($event);
        }
    }

    protected function shouldValidate($post, $discussion): bool
    {
        if ($post->exists || $post->number === 1 || !$discussion) {
            return false;
        }

        if ($this->extensions->isEnabled('fof-byobu') && $discussion->is_private) {
            return false;
        }

        return true;
    }

    protected function discussionAgeInDays($discussion): ?int
    {
        if (!$discussion->last_posted_at) {
            return null;
        }

        return $discussion->last_posted_at->diffInDays(Carbon::now());
    }

    protected function isHardBlocked(int $diffDays): bool
    {
        $hard = (int) $this->settings->get('fof-prevent-necrobumping-hard.days', 0);

        return $hard > 0 && $diffDays >= $hard;
    }

    protected function requiresConfirmation($discussion, int $diffDays): bool
    {
        $days = Util::getDays($this->settings, $discussion);

        return $days && $diffDays >= $days;
    }

    protected function assertConfirmation(Saving $event): void
    {
        $this->validator->assertValid([
            'fof-necrobumping' => Arr::get($event->data, 'attributes.fof-necrobumping'),
        ]);
    }

    protected function throwHardBlock(): void
    {
        $message = resolve('translator')->trans('fof-prevent-necrobumping.forum.composer.warning.hard_error');

        throw new ValidationException([
            'fof-prevent-necrobumping-hard.days' => $message,
        ]);
    }
}
