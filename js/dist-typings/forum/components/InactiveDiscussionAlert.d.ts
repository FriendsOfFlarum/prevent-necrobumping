import Component, { ComponentAttrs } from 'flarum/common/Component';
import type Mithril from 'mithril';
export interface InactiveDiscussionAlertAttrs extends ComponentAttrs {
    days: number;
    discussion: any;
    checked: boolean;
    set: (value: boolean) => void;
}
export default class InactiveDiscussionAlert extends Component<InactiveDiscussionAlertAttrs> {
    /** Set when the user toggles, so focus can follow the control that replaced the one they used. */
    refocus: boolean;
    view(): JSX.Element;
    onupdate(vnode: Mithril.VnodeDOM<InactiveDiscussionAlertAttrs, this>): void;
    onchange(): void;
}
