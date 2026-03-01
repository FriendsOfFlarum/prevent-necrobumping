import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import ReplyComposer from 'flarum/forum/components/ReplyComposer';
import InactiveDiscussionAlert from './components/InactiveDiscussionAlert';

/**
 * Check if a discussion is considered necrobumping based on configured days
 *
 * @param discussion - The discussion to check
 * @returns The number of days configured for necrobumping, or false if not necrobumping
 */
const isNecrobumping = (discussion) => {
  if (!discussion) return false;

  // Check if this is a private discussion (fof-byobu integration)
  if (app.initializers.has('fof-byobu') && discussion.attribute('isPrivateDiscussion')) {
    return false;
  }

  const days = discussion.attribute('fof-prevent-necrobumping');
  const lastPostedAt = discussion.lastPostedAt();

  if (lastPostedAt && days && dayjs().subtract(days, 'days').isAfter(lastPostedAt.getTime())) {
    return days;
  }

  return false;
};

app.initializers.add('fof/prevent-necrobumping', () => {
  override(ReplyComposer.prototype, 'view', function (orig, vnode) {
    const necrobumpingDays = isNecrobumping(this.attrs.discussion);
    this.attrs.disabled = this.attrs.disabled || (!!necrobumpingDays && !this.composer.fields.fofNecrobumping);

    return orig(vnode);
  });

  extend(ReplyComposer.prototype, 'headerItems', function (items) {
    const days = isNecrobumping(this.attrs.discussion);

    if (days) {
      items.add(
        'fof-necrobumping',
        InactiveDiscussionAlert.component({
          days,
          discussion: this.attrs.discussion,
          set: (v) => (this.composer.fields.fofNecrobumping = v),
        })
      );
    }
  });

  extend(ReplyComposer.prototype, 'data', function (data) {
    data['fof-necrobumping'] = this.composer.fields.fofNecrobumping;
  });
});

export const components = {
  InactiveDiscussionAlert,
};
