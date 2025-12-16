import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import Icon from 'flarum/common/components/Icon';
import dayjs from 'dayjs';
import type Discussion from 'flarum/common/models/Discussion';

export interface LockInactiveDiscussionAlertAttrs extends ComponentAttrs {
  days: number;
  discussion: Discussion;
}

export default class LockInactiveDiscussionAlert extends Component<LockInactiveDiscussionAlertAttrs> {
  view() {
    const lastPostedAt = this.attrs.discussion.lastPostedAt();
    const daysSinceLastPost = lastPostedAt ? dayjs().diff(lastPostedAt, 'day') : 0;
    const daysRemaining = Math.max(this.attrs.days - daysSinceLastPost, 0); // How many days left from last post
    const titleKey =
      daysRemaining > 0
        ? 'fof-prevent-necrobumping.forum.lock_days.alert.title'
        : 'fof-prevent-necrobumping.forum.lock_days.alert.title_now';

    return (
      <div className="NecrobumpingAlert Alert Alert--warning">
        <div className="Alert-body">
          <div className="NecrobumpingAlert-content">
            <div className="NecrobumpingAlert-icon">
              <Icon name="fas fa-lock" />
            </div>

            <div className="NecrobumpingAlert-message">
              <strong className="NecrobumpingAlert-title">
                {app.translator.trans(titleKey, { days: daysRemaining })}
              </strong>
              {daysRemaining > 0 && (
                <p>{app.translator.trans('fof-prevent-necrobumping.forum.lock_days.alert.description', { days: daysRemaining })}</p>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  }
}
