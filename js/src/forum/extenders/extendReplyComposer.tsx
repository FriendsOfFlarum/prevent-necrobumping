import { extend, override } from 'flarum/common/extend';
import { isNecrobumping } from '../utils/necrobumping';
import type { IComposerBodyAttrs } from 'flarum/forum/components/ComposerBody';
import type Discussion from 'flarum/common/models/Discussion';
import InactiveDiscussionAlert from '../components/InactiveDiscussionAlert';

// Extend ReplyComposer attrs type to include discussion
interface ReplyComposerAttrs extends IComposerBodyAttrs {
  discussion: Discussion;
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
    const days = isNecrobumping(attrs.discussion);

    if (days) {
      items.add(
        'fof-necrobumping',
        <InactiveDiscussionAlert days={days} discussion={attrs.discussion} set={(v: boolean) => (this.composer.fields.fofNecrobumping = v)} />
      );
    }
  });

  extend('flarum/forum/components/ReplyComposer', 'data', function (data: Record<string, any>) {
    data['fof-necrobumping'] = this.composer.fields.fofNecrobumping;
  });
}
