<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) 2018 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Listeners;

use Carbon\Carbon;
use Flarum\Post\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\PreventNecrobumping\Validators\NecrobumpingPostValidator;
use Illuminate\Support\Arr;

class ValidateNecrobumping
{
    protected $validator;
    /**
     * @var SettingsRepositoryInterface
     */
    private $settings;

    public function __construct(NecrobumpingPostValidator $validator, SettingsRepositoryInterface $settings)
    {
        $this->validator = $validator;
        $this->settings = $settings;
    }

    public function handle(Saving $event)
    {
        if ($event->post->exists || $event->post->number == 1) {
            return;
        }

        $lastPostedAt = $event->post->discussion->last_posted_at;
        $days = $this->settings->get('fof-prevent-necrobumping.days');

        if ($lastPostedAt && $days && $lastPostedAt->diffInDays(Carbon::now()) >= $days) {
            $this->validator->assertValid([
                'fof-necrobumping' => Arr::get($event->data, 'attributes.fof-necrobumping'),
            ]);
        }
    }
}
