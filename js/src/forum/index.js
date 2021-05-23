import app from 'flarum/forum/app';

import { extend, override } from 'flarum/common/extend';
import ReplyComposer from 'flarum/forum/components/ReplyComposer';

import NecrobumpingCheck from './components/NecrobumpingCheck';

const isNecrobumping = (discussion) => {
    if (!discussion) return false;

    const days = discussion.attribute('fof-prevent-necrobumping');
    const lastPostedAt = discussion.lastPostedAt();

    if (lastPostedAt && days && dayjs().subtract(days, 'days').isAfter(lastPostedAt.getTime())) {
        return days;
    }

    return false;
};

app.initializers.add('fof/prevent-necrobumping', () => {
    override(ReplyComposer.prototype, 'view', function (orig, vnode) {
        this.attrs.disabled = this.attrs.disabled || (isNecrobumping(this.attrs.discussion) && !this.composer.fields.fofNecrobumping);

        return orig(vnode);
    });

    extend(ReplyComposer.prototype, 'headerItems', function (items) {
        const days = isNecrobumping(this.attrs.discussion);

        if (days) {
            items.add(
                'fof-necrobumping',
                NecrobumpingCheck.component({
                    days,
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
    NecrobumpingCheck,
};
