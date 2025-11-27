<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Tests\unit;

use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\PreventNecrobumping\Util;
use Illuminate\Support\Collection;
use Mockery as m;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    #[Test]
    public function returns_global_days_setting_when_no_tags()
    {
        $settings = m::mock(SettingsRepositoryInterface::class);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days')
            ->andReturn(7);

        $discussion = m::mock(Discussion::class)->makePartial();
        $discussion->shouldReceive('getAttribute')
            ->with('tags')
            ->andReturn(null);

        $days = Util::getDays($settings, $discussion);

        $this->assertEquals(7, $days);
    }

    #[Test]
    public function returns_null_when_days_is_zero()
    {
        $settings = m::mock(SettingsRepositoryInterface::class);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days')
            ->andReturn(0);

        $discussion = m::mock(Discussion::class)->makePartial();
        $discussion->shouldReceive('getAttribute')
            ->with('tags')
            ->andReturn(null);

        $days = Util::getDays($settings, $discussion);

        $this->assertNull($days);
    }

    #[Test]
    public function returns_null_when_days_is_negative()
    {
        $settings = m::mock(SettingsRepositoryInterface::class);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days')
            ->andReturn(-5);

        $discussion = m::mock(Discussion::class)->makePartial();
        $discussion->shouldReceive('getAttribute')
            ->with('tags')
            ->andReturn(null);

        $days = Util::getDays($settings, $discussion);

        $this->assertNull($days);
    }

    #[Test]
    public function returns_minimum_tag_days_when_tags_exist()
    {
        $settings = m::mock(SettingsRepositoryInterface::class);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days')
            ->andReturn(30);

        $tag1 = (object) ['id' => 1];
        $tag2 = (object) ['id' => 2];

        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days.tags.1')
            ->andReturn(10);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days.tags.2')
            ->andReturn(20);

        $discussion = m::mock(Discussion::class)->makePartial();
        $discussion->shouldReceive('getAttribute')
            ->with('tags')
            ->andReturn(new Collection([$tag1, $tag2]));

        $days = Util::getDays($settings, $discussion);

        $this->assertEquals(10, $days);
    }

    #[Test]
    public function returns_null_when_any_tag_has_zero_days()
    {
        $settings = m::mock(SettingsRepositoryInterface::class);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days')
            ->andReturn(30);

        $tag1 = (object) ['id' => 1];
        $tag2 = (object) ['id' => 2];

        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days.tags.1')
            ->andReturn(10);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days.tags.2')
            ->andReturn(0);

        $discussion = m::mock(Discussion::class)->makePartial();
        $discussion->shouldReceive('getAttribute')
            ->with('tags')
            ->andReturn(new Collection([$tag1, $tag2]));

        $days = Util::getDays($settings, $discussion);

        $this->assertNull($days);
    }

    #[Test]
    public function ignores_null_tag_settings()
    {
        $settings = m::mock(SettingsRepositoryInterface::class);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days')
            ->andReturn(30);

        $tag1 = (object) ['id' => 1];
        $tag2 = (object) ['id' => 2];

        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days.tags.1')
            ->andReturn(null);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days.tags.2')
            ->andReturn(15);

        $discussion = m::mock(Discussion::class)->makePartial();
        $discussion->shouldReceive('getAttribute')
            ->with('tags')
            ->andReturn(new Collection([$tag1, $tag2]));

        $days = Util::getDays($settings, $discussion);

        $this->assertEquals(15, $days);
    }

    #[Test]
    public function uses_global_setting_when_all_tags_have_null_settings()
    {
        $settings = m::mock(SettingsRepositoryInterface::class);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days')
            ->andReturn(30);

        $tag1 = (object) ['id' => 1];
        $tag2 = (object) ['id' => 2];

        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days.tags.1')
            ->andReturn(null);
        $settings->shouldReceive('get')
            ->with('fof-prevent-necrobumping.days.tags.2')
            ->andReturn(null);

        $discussion = m::mock(Discussion::class)->makePartial();
        $discussion->shouldReceive('getAttribute')
            ->with('tags')
            ->andReturn(new Collection([$tag1, $tag2]));

        $days = Util::getDays($settings, $discussion);

        $this->assertEquals(30, $days);
    }
}
