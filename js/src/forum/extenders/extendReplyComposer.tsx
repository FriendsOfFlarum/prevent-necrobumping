import app from 'flarum/forum/app';
import { extend, override } from 'flarum/common/extend';
import { isNecrobumping } from '../utils/necrobumping';
import type { IComposerBodyAttrs } from 'flarum/forum/components/ComposerBody';
import type Discussion from 'flarum/common/models/Discussion';
import InactiveDiscussionAlert from '../components/InactiveDiscussionAlert';
import LockInactiveDiscussionAlert from '../components/LockInactiveDiscussionAlert';

// Extend ReplyComposer attrs type to include discussion
interface ReplyComposerAttrs extends IComposerBodyAttrs {
  discussion: Discussion;
}

function lockDays(): number {
  return Number(app.forum.attribute('fof-prevent-necrobumping.lock_days') || 0);
}

function shouldShowLockWarning(softDays: number | false): softDays is number {
  return !!softDays && lockDays() > 0 && lockDays() >= softDays;
}

export default function extendReplyComposer() {
  override('flarum/forum/components/ReplyComposer', 'view', function (orig) {
    const attrs = this.attrs as unknown as ReplyComposerAttrs;
    const necrobumpingDays = isNecrobumping(attrs.discussion);

    attrs.disabled = attrs.disabled || (!!necrobumpingDays && !this.composer.fields.fofNecrobumping);

    return orig.call(this);
  });

  extend('flarum/forum/components/ReplyComposer', 'headerItems', function (items) {
    const attrs = this.attrs as unknown as ReplyComposerAttrs;
    const softDays = isNecrobumping(attrs.discussion);

    if (softDays) {
      items.add(
        'fof-necrobumping',
        <InactiveDiscussionAlert days={softDays} discussion={attrs.discussion} set={(v: boolean) => (this.composer.fields.fofNecrobumping = v)} />
      );
    }

    if (shouldShowLockWarning(softDays)) {
      items.add(
        'fof-necrobumping-lock',
        <LockInactiveDiscussionAlert days={lockDays()} discussion={attrs.discussion} />
      );
    }
  });

  extend('flarum/forum/components/ReplyComposer', 'data', function (data: Record<string, any>) {
    data['fof-necrobumping'] = this.composer.fields.fofNecrobumping;
  });
}
