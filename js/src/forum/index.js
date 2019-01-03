import { extend, override } from 'flarum/extend';
import TextEditor from 'flarum/components/TextEditor';
import ReplyComposer from 'flarum/components/ReplyComposer';

import NecrobumpingCheck from './components/NecrobumpingCheck';

app.initializers.add('fof/prevent-necrobumping', () => {
    const days = Number(app.data['fof-prevent-necrobumping.days']);

    extend(TextEditor.prototype, 'view', function(vdom) {
        const $textarea = vdom.children.find(e => e.tag === 'textarea');

        if (!this.disabled) delete $textarea.attrs.disabled;
        else $textarea.attrs.disabled = true;
    });

    extend(ReplyComposer.prototype, 'headerItems', function(items) {
        if (
            moment()
                .subtract(days, 'days')
                .isAfter(this.props.discussion.lastPostedAt().getTime())
        ) {
            items.add(
                'fof-necrobumping',
                NecrobumpingCheck.component({
                    days,
                    editor: this.editor,
                    set: v => (this.fofNecrobumping = v),
                    disable: d => (this.editor.disabled = d),
                })
            );
        }
    });

    extend(ReplyComposer.prototype, 'data', function(data) {
        data['fof-necrobumping'] = this.fofNecrobumping;
    });
});
