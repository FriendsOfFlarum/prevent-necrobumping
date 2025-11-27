<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Validators;

use Flarum\Foundation\AbstractValidator;

class NecrobumpingPostValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    protected array $rules = [
        'fof-necrobumping' => 'accepted',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getMessages(): array
    {
        return [
            'fof-necrobumping.accepted' => $this->translator->trans('fof-prevent-necrobumping.forum.composer.error'),
        ];
    }
}
