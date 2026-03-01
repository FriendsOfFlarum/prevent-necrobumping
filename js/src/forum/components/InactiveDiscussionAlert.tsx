import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import Switch from 'flarum/common/components/Switch';
import Stream from 'flarum/common/utils/Stream';
import ItemList from 'flarum/common/utils/ItemList';
import Link from 'flarum/common/components/Link';
import icon from 'flarum/common/helpers/icon';
import { initiateNewDiscussion } from '../utils/discussionUtils';
import type Discussion from 'flarum/common/models/Discussion';
import type Mithril from 'mithril';

export interface InactiveDiscussionAlertAttrs extends ComponentAttrs {
  /**
   * The number of days since the discussion was last active
   */
  days: number;

  /**
   * The discussion that is being replied to
   */
  discussion: Discussion;

  /**
   * Callback function to set the necrobumping confirmation status
   */
  set: (value: boolean) => void;
}

export default class InactiveDiscussionAlert<
  CustomAttrs extends InactiveDiscussionAlertAttrs = InactiveDiscussionAlertAttrs,
> extends Component<CustomAttrs> {
  /**
   * Stream to track the checked state of the confirmation checkbox
   */
  checked!: Stream<boolean>;

  oninit(vnode: Mithril.Vnode<CustomAttrs, this>) {
    super.oninit(vnode);
    this.checked = Stream(false);
  }

  view() {
    return (
      <div className="NecrobumpingAlert Alert Alert--warning">
        <div className="Alert-body">
          <div className="NecrobumpingAlert-content">{this.contentItems().toArray()}</div>
        </div>
      </div>
    );
  }

  /**
   * Build a list of content items for the alert.
   */
  contentItems(): ItemList<Mithril.Children> {
    const items = new ItemList<Mithril.Children>();

    items.add('icon', this.iconItem(), 100);
    items.add('message', this.messageItem(), 90);

    return items;
  }

  /**
   * Build the icon element.
   */
  iconItem(): Mithril.Children {
    return <div className="NecrobumpingAlert-icon">{icon('fas fa-clock')}</div>;
  }

  /**
   * Build the message element with title, details, and checkbox.
   */
  messageItem(): Mithril.Children {
    return (
      <div className="NecrobumpingAlert-message">
        {this.titleItem()}
        <div className="NecrobumpingAlert-details">{this.detailsItems().toArray()}</div>
      </div>
    );
  }

  /**
   * Build the title element.
   */
  titleItem(): Mithril.Children {
    const lastPostedAt = this.attrs.discussion.lastPostedAt();
    const time = lastPostedAt ? dayjs(lastPostedAt).fromNow() : null;

    return (
      <strong className="NecrobumpingAlert-title">
        {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.title', { time })}
      </strong>
    );
  }

  /**
   * Build a list of detail items (description, CTA, checkbox).
   */
  detailsItems(): ItemList<Mithril.Children> {
    const items = new ItemList<Mithril.Children>();

    items.add('description', this.descriptionItem(), 100);

    if (app.forum.attribute('fof-prevent-necrobumping.show_discussion_cta') && app.forum.attribute('canStartDiscussion')) {
      items.add('cta', this.ctaItem(), 90);
    }

    items.add('checkbox', this.checkboxItem(), 80);

    return items;
  }

  /**
   * Build the description paragraph.
   */
  descriptionItem(): Mithril.Children {
    return <p>{app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.description')}</p>;
  }

  /**
   * Build the call-to-action paragraph with link.
   */
  ctaItem(): Mithril.Children {
    return (
      <p>
        {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.cta')}{' '}
        <Link
          onclick={(e: Event) => {
            e.preventDefault();
            initiateNewDiscussion();
          }}
        >
          {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.cta_button')}
        </Link>
      </p>
    );
  }

  /**
   * Build the checkbox element.
   */
  checkboxItem(): Mithril.Children {
    return (
      <div className="NecrobumpingAlert-checkbox">
        <Switch state={this.checked()} onchange={this.onchange.bind(this)}>
          {app.translator.trans('fof-prevent-necrobumping.forum.composer.inactive_discussion_alert.confirmation')}
        </Switch>
      </div>
    );
  }

  /**
   * Handle checkbox state change
   */
  onchange(): void {
    const newStatus = !this.checked();
    this.attrs.set(newStatus);
    this.checked(newStatus);
  }
}
