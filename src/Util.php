<?php


namespace FoF\PreventNecrobumping;


use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;

class Util
{
    public static function getDays(SettingsRepositoryInterface $settings, Discussion $discussion): ?int
    {
        $days = $settings->get('fof-prevent-necrobumping.days');
        $tags = $discussion->tags;

        if ($tags && $tags->isNotEmpty()) {
            $tagDays = $tags->map(function ($tag) use ($settings) {
                return $settings->get("fof-prevent-necrobumping.days.tags.{$tag->id}");
            })->filter(function ($days) {
                return $days != null && $days != '' && !is_nan($days) && (int) $days >= 0;
            });

            if ($tagDays->isNotEmpty()) {
                $days = $tagDays->contains(0)
                    ? null
                    : $tagDays->min();
            }
        }

        return is_nan($days) || $days < 1 ? null : (int) $days;
    }
}
