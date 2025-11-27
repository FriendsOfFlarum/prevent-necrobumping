import Component, { ComponentAttrs } from 'flarum/common/Component';
import Stream from 'flarum/common/utils/Stream';
import type Mithril from 'mithril';
export interface InactiveDiscussionAlertAttrs extends ComponentAttrs {
    days: number;
    discussion: any;
    set: (value: boolean) => void;
}
export default class InactiveDiscussionAlert extends Component<InactiveDiscussionAlertAttrs> {
    checked: Stream<boolean>;
    oninit(vnode: Mithril.Vnode<InactiveDiscussionAlertAttrs, this>): void;
    view(): JSX.Element;
    onchange(): void;
}
