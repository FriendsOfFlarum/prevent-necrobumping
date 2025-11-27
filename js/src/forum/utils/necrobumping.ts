import app from 'flarum/forum/app';
import type Discussion from 'flarum/common/models/Discussion';

/**
 * Check if a discussion is considered necrobumping based on configured days
 *
 * @param discussion - The discussion to check
 * @returns The number of days configured for necrobumping, or false if not necrobumping
 */
export function isNecrobumping(discussion?: Discussion): number | false {
  if (!discussion) return false;

  // Check if this is a private discussion (fof-byobu integration)
  if (app.initializers.has('fof-byobu') && discussion.attribute<boolean>('isPrivateDiscussion')) {
    return false;
  }

  const days = discussion.attribute<number>('fof-prevent-necrobumping');
  const lastPostedAt = discussion.lastPostedAt();

  if (lastPostedAt && days && dayjs().subtract(days, 'days').isAfter(lastPostedAt.getTime())) {
    return days;
  }

  return false;
}
