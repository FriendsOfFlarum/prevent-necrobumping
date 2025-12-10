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

    public function handle(Saving $event)
    {
        $post = $event->post;
        $discussion = $post->discussion;

        if ($post->exists || $post->number === 1 || !$discussion) {
            return;
        }

        if ($this->extensions->isEnabled('fof-byobu') && $discussion->is_private) {
            return;
        }

        $lastPostedAt = $discussion->last_posted_at;
        $days = Util::getDays($this->settings, $discussion);

        $hard = (int) $this->settings->get('fof-prevent-necrobumping-hard.days', 0);

        if (!$lastPostedAt) {
            return;
        }

        $diffDays = $lastPostedAt->diffInDays(Carbon::now());

        if ($hard > 0 && $diffDays >= $hard) {
            $message = resolve('translator')->trans('fof-prevent-necrobumping.forum.composer.warning.hard_error');

            throw new ValidationException([
                'fof-prevent-necrobumping-hard.days' => $message,
            ]);
        }

        if ($days && $diffDays >= $days) {
            $this->validator->assertValid([
                'fof-necrobumping' => Arr::get($event->data, 'attributes.fof-necrobumping'),
            ]);
        }
    }
}
