<?php

namespace FoF\PreventNecrobumping\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Flarum\Post\Post;

class AddPostResourceFields
{
    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('fof-necrobumping')
                ->writable(function (Post $post, Context $context) {
                    return $context->creating();
                })
                ->set(function (Post $post, bool $value, Context $context) {
                    // Don't actually set this on the model - it's only used for validation
                    // The field is checked in ValidateNecrobumping listener
                })
        ];
    }
}
