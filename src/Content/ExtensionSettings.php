<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) 2018 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Content;

use Flarum\Frontend\Document;
use Flarum\Settings\SettingsRepositoryInterface;

class ExtensionSettings
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    protected $prefix = 'fof-prevent-necrobumping.';
    protected $keys = ['days', 'message.title', 'message.description', 'message.agreement'];

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(Document $document)
    {
        foreach ($this->keys as $key) {
            $document->payload[$this->prefix.$key] = $this->settings->get($this->prefix.$key);
        }
//        $document->payload['fof-prevent-necrobumping.days'] = $this->settings->get('fof-prevent-necrobumping.days');
    }
}
