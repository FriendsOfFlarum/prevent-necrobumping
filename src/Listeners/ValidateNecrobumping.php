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

use Flarum\Post\Event\Saving;
use FoF\PreventNecrobumping\Validators\NecrobumpingPostValidator;
use Illuminate\Support\Arr;

class ValidateNecrobumping
{
    protected $validator;

    public function __construct(NecrobumpingPostValidator $validator)
    {
        $this->validator = $validator;
    }

    public function handle(Saving $event) {
        if (!$event->post->exists && $event->post->discussion->last_posted_at->diffInDays(\Carbon\Carbon::now())) {
            $this->validator->assertValid([
                'fof-necrobumping' => Arr::get($event->data, 'attributes.fof-necrobumping')
            ]);
        };
    }
}
