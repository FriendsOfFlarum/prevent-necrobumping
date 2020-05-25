import { extend } from 'flarum/extend';
import TextEditor from 'flarum/components/TextEditor';
import ReplyComposer from 'flarum/components/ReplyComposer';

import find from 'array.prototype.find';

import NecrobumpingCheck from './components/NecrobumpingCheck';

app.initializers.add('fof/prevent-necrobumping', () => {
    extend(TextEditor.prototype, 'view', function (vdom) {
        const $textarea = find(vdom.children, (e) => e.tag === 'textarea');

        if ($textarea) {
            if (!this.disabled) delete $textarea.attrs.disabled;
            else $textarea.attrs.disabled = true;
        }
    });

    extend(ReplyComposer.prototype, 'headerItems', function (items) {
        const days = this.props.discussion && this.props.discussion.attribute('fof-prevent-necrobumping');
        const lastPostedAt = this.props.discussion.lastPostedAt();

        if (lastPostedAt && days && moment().subtract(days, 'days').isAfter(lastPostedAt.getTime())) {
            items.add(
                'fof-necrobumping',
                NecrobumpingCheck.component({
                    days,
                    editor: this.editor,
                    set: (v) => (this.fofNecrobumping = v),
                    disable: (d) => (this.editor.disabled = d),
                })
            );
        }
    });

    extend(ReplyComposer.prototype, 'data', function (data) {
        data['fof-necrobumping'] = this.fofNecrobumping;
    });
});
