<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

class PreventNecrobumpingTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-prevent-necrobumping');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Active Discussion', 'created_at' => Carbon::now()->subDays(1), 'last_posted_at' => Carbon::now()->subHours(1), 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1],
                ['id' => 2, 'title' => 'Inactive Discussion (10 days)', 'created_at' => Carbon::now()->subDays(15), 'last_posted_at' => Carbon::now()->subDays(10), 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1],
                ['id' => 3, 'title' => 'Very Old Discussion (30 days)', 'created_at' => Carbon::now()->subDays(60), 'last_posted_at' => Carbon::now()->subDays(30), 'user_id' => 1, 'first_post_id' => 3, 'comment_count' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Active post</p></t>', 'is_private' => 0, 'number' => 1, 'created_at' => Carbon::now()->subHours(1)],
                ['id' => 2, 'discussion_id' => 2, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Old post</p></t>', 'is_private' => 0, 'number' => 1, 'created_at' => Carbon::now()->subDays(10)],
                ['id' => 3, 'discussion_id' => 3, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Very old post</p></t>', 'is_private' => 0, 'number' => 1, 'created_at' => Carbon::now()->subDays(30)],
            ],
        ]);
    }

    #[Test]
    public function can_reply_to_active_discussion_without_confirmation()
    {
        // Set necrobumping threshold to 7 days
        $this->setting('fof-prevent-necrobumping.days', 7);

        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'This is a reply to an active discussion',
                        ],
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => '1'],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('This is a reply to an active discussion', $body['data']['attributes']['content']);
    }

    #[Test]
    public function cannot_reply_to_inactive_discussion_without_confirmation()
    {
        // Set necrobumping threshold to 7 days
        $this->setting('fof-prevent-necrobumping.days', 7);

        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Necrobumping without confirmation',
                        ],
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => '2'],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());
    }

    #[Test]
    public function can_reply_to_inactive_discussion_with_confirmation()
    {
        // Set necrobumping threshold to 7 days
        $this->setting('fof-prevent-necrobumping.days', 7);

        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content'          => 'Necrobumping with confirmation',
                            'fof-necrobumping' => true,
                        ],
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => '2'],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals(201, $response->getStatusCode(), 'Response body: '.json_encode($body));
        $this->assertEquals('Necrobumping with confirmation', $body['data']['attributes']['content']);
    }

    #[Test]
    public function validation_respects_custom_threshold_allows_newer_discussions()
    {
        // Set necrobumping threshold to 20 days
        $this->setting('fof-prevent-necrobumping.days', 20);

        // Discussion 2 is 10 days old, should be allowed
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply to 10 day old discussion with 20 day threshold',
                        ],
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => '2'],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());
    }

    #[Test]
    public function validation_respects_custom_threshold_blocks_older_discussions()
    {
        // Set necrobumping threshold to 20 days
        $this->setting('fof-prevent-necrobumping.days', 20);

        // Discussion 3 is 30 days old, should require confirmation
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply to 30 day old discussion without confirmation',
                        ],
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => '3'],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());
    }

    #[Test]
    public function validation_disabled_when_days_is_zero()
    {
        // Disable necrobumping prevention
        $this->setting('fof-prevent-necrobumping.days', 0);

        // Should be able to reply to very old discussion without confirmation
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply to 30 day old discussion with validation disabled',
                        ],
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => '3'],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());
    }

    #[Test]
    public function discussion_resource_includes_last_posted_at()
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions/2', [
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('lastPostedAt', $body['data']['attributes']);
    }
}
