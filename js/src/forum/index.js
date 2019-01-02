import { extend, override } from 'flarum/extend';
import TextEditor from 'flarum/components/TextEditor';
import ReplyComposer from 'flarum/components/ReplyComposer';

import NecrobumpingCheck from './components/NecrobumpingCheck';

app.initializers.add('fof/prevent-necrobumping', () => {
    const days = app.data['fof-prevent-necrobumping.days'];

    extend(TextEditor.prototype, 'view', function(vdom) {
        const $textarea = vdom.children.find(e => e.tag === 'textarea');

        if (!this.disabled) delete $textarea.attrs.disabled;
        else $textarea.attrs.disabled = true;
    });

    extend(ReplyComposer.prototype, 'headerItems', function(items) {
        if (Date.now() - this.props.discussion.lastPostedAt().getTime() < days * 86400000) return;

        items.add(
            'fof-necrobumping',
            NecrobumpingCheck.component({
                editor: this.editor,
                set: v => (this.fofNecrobumping = v),
                disable: d => (this.editor.disabled = d),
                days,
            })
        );
    });

    extend(ReplyComposer.prototype, 'data', function(data) {
        data['fof-necrobumping'] = this.fofNecrobumping;
    });
});
