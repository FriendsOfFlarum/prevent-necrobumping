import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import Switch from 'flarum/common/components/Switch';
import Stream from 'flarum/common/utils/Stream';
import Link from 'flarum/common/components/Link';
import Icon from 'flarum/common/components/Icon';
import type Mithril from 'mithril';

import { initiateNewDiscussion } from '../utils/discussionUtils';

export interface InactiveDiscussionAlertAttrs extends ComponentAttrs {
  days: number;
  discussion: any;
  set: (value: boolean) => void;
}

export default class InactiveDiscussionAlert extends Component<InactiveDiscussionAlertAttrs> {
  checked!: Stream<boolean>;

  oninit(vnode: Mithril.Vnode<InactiveDiscussionAlertAttrs, this>) {
    super.oninit(vnode);
    this.checked = Stream(false);
  }

  view() {
    const lastPostedAt = this.attrs.discussion.lastPostedAt();
    const time = lastPostedAt ? dayjs(lastPostedAt).fromNow() : null;

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
                <p>{app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.description')}</p>

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
                  <Switch state={this.checked()} onchange={this.onchange.bind(this)}>
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

  onchange() {
    const newStatus = !this.checked();
    this.attrs.set(newStatus);
    this.checked(newStatus);
  }
}
