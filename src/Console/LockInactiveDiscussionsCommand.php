<?php

/*
 * This file is part of fof/prevent-necrobumping.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PreventNecrobumping\Console;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Extension\ExtensionManager;
use Flarum\Settings\SettingsRepositoryInterface;
use FoF\PreventNecrobumping\Job\LockInactiveDiscussionsChunkJob;
use FoF\PreventNecrobumping\Util;
use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\Queue;

class LockInactiveDiscussionsCommand extends Command
{
    protected $signature = 'fof-necrobumping:lock-inactive-discussions';
    protected $description = 'Locks discussions that have passed the configured necrobumping lock threshold.';

    public function __construct(
        protected SettingsRepositoryInterface $settings,
        protected Queue $queue,
        protected ExtensionManager $extensions
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->extensions->isEnabled('glowingblue-discussion-lifecycle')) {
            $this->info('Skipping necrobumping auto-lock because glowingblue-discussion-lifecycle is enabled.');

            return Command::SUCCESS;
        }

        $lockDays = Util::getConfiguredLockDays($this->settings);

        if ($lockDays <= 0) {
            $this->info('No necrobumping lock threshold configured. Nothing to do.');

            return Command::SUCCESS;
        }

        $cutoff = Carbon::now()->subDays($lockDays);
        $excludedTagIds = $this->extensions->isEnabled('flarum-tags')
            ? Util::getExcludedTagIds($this->settings)
            : [];

        $query = Discussion::query()
            ->whereNotNull('last_posted_at')
            ->where('last_posted_at', '<=', $cutoff)
            ->where('is_locked', false)
            ->where('is_private', false);

        if ($this->extensions->isEnabled('flarum-approval')) {
            $query->where('is_approved', true);
        }

        if ($excludedTagIds !== []) {
            $query->whereDoesntHave('tags', function ($query) use ($excludedTagIds) {
                $query->whereIn('tags.id', $excludedTagIds);
            });
        }

        $queuedChunks = 0;
        $queuedDiscussions = 0;

        $query
            ->select(['id'])
            ->orderBy('id')
            ->chunkById(100, function ($discussions) use (&$queuedChunks, &$queuedDiscussions) {
                $discussionIds = $discussions->pluck('id')->all();

                if ($discussionIds === []) {
                    return;
                }

                $this->queue->push(new LockInactiveDiscussionsChunkJob($discussionIds));

                $queuedChunks++;
                $queuedDiscussions += count($discussionIds);
            });

        $this->info("Queued necrobumping auto-lock job(s). Chunks: {$queuedChunks}, discussions: {$queuedDiscussions}.");

        return Command::SUCCESS;
    }
}
