import app from 'flarum/forum/app';

import Component from 'flarum/common/Component';
import Checkbox from 'flarum/common/components/Checkbox';
import Stream from 'flarum/common/utils/Stream';
import ItemList from 'flarum/common/utils/ItemList';

export default class NecrobumpingCheck extends Component {
  oninit(vnode) {
    super.oninit(vnode);

    this.checked = Stream(false);
  }

  view() {
    return (
      <div>
        <div className="Alert">
          <div className="Alert-body">{this.viewItems().toArray()}</div>
        </div>
      </div>
    );
  }

  viewItems() {
    const items = new ItemList();

    const customAgreement = app.data['fof-prevent-necrobumping.message.agreement'];

    items.add('hide', <div className="hide">{this.alertItems().toArray()}</div>, 100);

    items.add(
      'checkbox',
      <Checkbox state={this.checked()} onchange={this.onchange.bind(this)}>
        {customAgreement || app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.checkbox_label')}
      </Checkbox>,
      90
    );

    return items;
  }

  alertItems() {
    const items = new ItemList();

    const customTitle = app.data['fof-prevent-necrobumping.message.title'];
    const customDescription = app.data['fof-prevent-necrobumping.message.description'];

    const time = dayjs().add(this.attrs.days, 'days').fromNow(true);

    items.add(
      'title',
      <h4>
        {(customTitle && customTitle.replace(/\[time]/i, time)) ||
          app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.title', {
            time,
          })}
      </h4>,
      100
    );

    items.add('description', <p>{customDescription || app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.description')}</p>, 90);

    return items;
  }

  onchange() {
    const newStatus = !this.checked();
    const interval = setInterval(() => m.redraw());

    if (newStatus) {
      this.$('.hide').slideUp(250, () => clearInterval(interval));
    } else {
      this.$('.hide').slideDown(250, () => clearInterval(interval));
    }

    this.attrs.set(newStatus);

    this.checked(newStatus);
  }
}
