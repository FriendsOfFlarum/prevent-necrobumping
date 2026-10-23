import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import Switch from 'flarum/common/components/Switch';
import Link from 'flarum/common/components/Link';
import Icon from 'flarum/common/components/Icon';
import Button from 'flarum/common/components/Button';
import type Mithril from 'mithril';

import { initiateNewDiscussion } from '../utils/discussionUtils';

export interface InactiveDiscussionAlertAttrs extends ComponentAttrs {
  days: number;
  discussion: any;
  checked: boolean;
  set: (value: boolean) => void;
}

export default class InactiveDiscussionAlert extends Component<InactiveDiscussionAlertAttrs> {
  /** Set when the user toggles, so focus can follow the control that replaced the one they used. */
  refocus = false;

  view() {
    const lastPostedAt = this.attrs.discussion.lastPostedAt();
    const time = lastPostedAt ? dayjs(lastPostedAt).fromNow() : null;

    // Once the user has confirmed, shrink to a single line so the editor gets its space back.
    if (this.attrs.checked) {
      return (
        <div className="NecrobumpingAlert NecrobumpingAlert--confirmed Alert Alert--warning" role="status">
          <div className="Alert-body NecrobumpingAlert-content">
            <Icon name="fas fa-clock" className="NecrobumpingAlert-icon" />
            <span className="NecrobumpingAlert-message">
              {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.confirmed')}
            </span>
            <Button className="Button Button--link NecrobumpingAlert-undo" onclick={this.onchange.bind(this)}>
              {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.undo')}
            </Button>
          </div>
        </div>
      );
    }

    return (
      <div className="NecrobumpingAlert Alert Alert--warning">
        <div className="Alert-body">
          <div className="NecrobumpingAlert-content">
            <div className="NecrobumpingAlert-icon">
              <Icon name="fas fa-clock" />
            </div>

            <div className="NecrobumpingAlert-message">
              <strong className="NecrobumpingAlert-title">
                {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.title', { time })}
              </strong>

              <div className="NecrobumpingAlert-details">
                <p className="NecrobumpingAlert-description">
                  {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.description')}
                </p>

                {app.forum.attribute('fof-prevent-necrobumping.show_discussion_cta') && app.forum.attribute('canStartDiscussion') && (
                  <p>
                    {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.cta')}{' '}
                    <Link
                      onclick={(e: MouseEvent) => {
                        e.preventDefault();
                        initiateNewDiscussion();
                      }}
                    >
                      {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.cta_button')}
                    </Link>
                  </p>
                )}

                <div className="NecrobumpingAlert-checkbox">
                  <Switch state={false} onchange={this.onchange.bind(this)}>
                    {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.confirmation')}
                  </Switch>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  onupdate(vnode: Mithril.VnodeDOM<InactiveDiscussionAlertAttrs, this>) {
    super.onupdate(vnode);

    if (this.refocus) {
      this.refocus = false;
      this.$(this.attrs.checked ? '.NecrobumpingAlert-undo' : 'input').trigger('focus');
    }
  }

  onchange() {
    this.refocus = true;
    this.attrs.set(!this.attrs.checked);
  }
}
