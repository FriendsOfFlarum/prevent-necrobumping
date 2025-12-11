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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;

class TagSpecificThresholdsTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags');
        $this->extension('fof-prevent-necrobumping');

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
            ],
            'tags' => [
                ['id' => 1, 'name' => 'General', 'slug' => 'general', 'position' => 0],
                ['id' => 2, 'name' => 'Support', 'slug' => 'support', 'position' => 1],
                ['id' => 3, 'name' => 'News', 'slug' => 'news', 'position' => 2],
            ],
            'discussions' => [
                // Discussion 1: 10 days old, tagged with General (will have 30 day threshold)
                ['id' => 1, 'title' => 'General Discussion 10 days old', 'created_at' => Carbon::now()->subDays(15), 'last_posted_at' => Carbon::now()->subDays(10), 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1],
                // Discussion 2: 10 days old, tagged with Support (will have 5 day threshold)
                ['id' => 2, 'title' => 'Support Discussion 10 days old', 'created_at' => Carbon::now()->subDays(15), 'last_posted_at' => Carbon::now()->subDays(10), 'user_id' => 1, 'first_post_id' => 2, 'comment_count' => 1],
                // Discussion 3: 10 days old, tagged with News (will be disabled with 0 days)
                ['id' => 3, 'title' => 'News Discussion 10 days old', 'created_at' => Carbon::now()->subDays(15), 'last_posted_at' => Carbon::now()->subDays(10), 'user_id' => 1, 'first_post_id' => 3, 'comment_count' => 1],
                // Discussion 4: 10 days old, tagged with General AND Support (should use minimum: 5 days)
                ['id' => 4, 'title' => 'Multiple Tags 10 days old', 'created_at' => Carbon::now()->subDays(15), 'last_posted_at' => Carbon::now()->subDays(10), 'user_id' => 1, 'first_post_id' => 4, 'comment_count' => 1],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>General post</p></t>', 'is_private' => 0, 'number' => 1, 'created_at' => Carbon::now()->subDays(10)],
                ['id' => 2, 'discussion_id' => 2, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Support post</p></t>', 'is_private' => 0, 'number' => 1, 'created_at' => Carbon::now()->subDays(10)],
                ['id' => 3, 'discussion_id' => 3, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>News post</p></t>', 'is_private' => 0, 'number' => 1, 'created_at' => Carbon::now()->subDays(10)],
                ['id' => 4, 'discussion_id' => 4, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Multiple tags post</p></t>', 'is_private' => 0, 'number' => 1, 'created_at' => Carbon::now()->subDays(10)],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 1], // General
                ['discussion_id' => 2, 'tag_id' => 2], // Support
                ['discussion_id' => 3, 'tag_id' => 3], // News
                ['discussion_id' => 4, 'tag_id' => 1], // General
                ['discussion_id' => 4, 'tag_id' => 2], // Support
            ],
        ]);
    }

    /**
     * @test
     */
    public function uses_tag_specific_threshold_when_set()
    {
        // Global: 20 days, General tag: 30 days
        $this->setting('fof-prevent-necrobumping.days', 20);
        $this->setting('fof-prevent-necrobumping.days.tags.1', 30);

        // Discussion 1 is tagged with General (30 day threshold)
        // It's only 10 days old, so should not require confirmation
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply to discussion with tag-specific threshold',
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
    }

    /**
     * @test
     */
    public function uses_stricter_tag_threshold_over_global()
    {
        // Global: 20 days, Support tag: 5 days
        $this->setting('fof-prevent-necrobumping.days', 20);
        $this->setting('fof-prevent-necrobumping.days.tags.2', 5);

        // Discussion 2 is tagged with Support (5 day threshold)
        // It's 10 days old, so should require confirmation
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply without confirmation',
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

        // With confirmation, should succeed
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content'          => 'Reply with confirmation',
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

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function tag_specific_zero_disables_validation()
    {
        // Global: 7 days, News tag: 0 (disabled)
        $this->setting('fof-prevent-necrobumping.days', 7);
        $this->setting('fof-prevent-necrobumping.days.tags.3', 0);

        // Discussion 3 is tagged with News (validation disabled)
        // Should be able to reply without confirmation even though it's 10 days old
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply to discussion with disabled validation',
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

    /**
     * @test
     */
    public function uses_minimum_threshold_when_multiple_tags()
    {
        // Global: 20 days, General: 30 days, Support: 5 days
        $this->setting('fof-prevent-necrobumping.days', 20);
        $this->setting('fof-prevent-necrobumping.days.tags.1', 30);
        $this->setting('fof-prevent-necrobumping.days.tags.2', 5);

        // Discussion 4 has both General (30 days) and Support (5 days)
        // Should use the minimum: 5 days
        // Discussion is 10 days old, so should require confirmation
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply without confirmation',
                        ],
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => '4'],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function zero_threshold_on_any_tag_disables_validation_for_discussion()
    {
        // Global: 7 days, General: 10 days, Support: 0 (disabled)
        $this->setting('fof-prevent-necrobumping.days', 7);
        $this->setting('fof-prevent-necrobumping.days.tags.1', 10);
        $this->setting('fof-prevent-necrobumping.days.tags.2', 0);

        // Discussion 4 has both General (10 days) and Support (0 = disabled)
        // Should be disabled entirely because one tag has 0
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply to discussion with one tag disabled',
                        ],
                        'relationships' => [
                            'discussion' => [
                                'data' => ['type' => 'discussions', 'id' => '4'],
                            ],
                        ],
                    ],
                ],
            ])
        );

        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function falls_back_to_global_when_tag_has_no_specific_setting()
    {
        // Only set global, no tag-specific settings
        $this->setting('fof-prevent-necrobumping.days', 7);

        // Discussion 1 has General tag but no tag-specific setting
        // Should use global: 7 days
        // Discussion is 10 days old, so should require confirmation
        $response = $this->send(
            $this->request('POST', '/api/posts', [
                'authenticatedAs' => 2,
                'json'            => [
                    'data' => [
                        'type'       => 'posts',
                        'attributes' => [
                            'content' => 'Reply without confirmation',
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

        $this->assertEquals(422, $response->getStatusCode());
    }
}
