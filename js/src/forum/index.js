import { extend } from 'flarum/common/extend';
import TextEditor from 'flarum/common/components/TextEditor';
import ReplyComposer from 'flarum/common/components/ReplyComposer';

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
    extend(TextEditor.prototype, 'view', function (vdom) {
        if (!app.composer.bodyMatches(ReplyComposer)) return;

        const $textarea = vdom.children && vdom.children.find((e) => e.tag === 'textarea');
        const composer = app.composer;
        const { discussion, disabled } = composer.body.attrs;

        if ($textarea && isNecrobumping(discussion)) {
            if (!disabled) delete $textarea.attrs.disabled;
            else $textarea.attrs.disabled = true;
        }
    });

    extend(ReplyComposer.prototype, 'headerItems', function (items) {
        const days = isNecrobumping(this.attrs.discussion);

        if (days) {
            items.add(
                'fof-necrobumping',
                NecrobumpingCheck.component({
                    days,
                    set: (v) => (this.composer.fields.fofNecrobumping = v),
                    disable: (d) => (this.composer.body.attrs.disabled = d),
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
