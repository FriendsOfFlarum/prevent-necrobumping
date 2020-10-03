import Component from 'flarum/Component';
import Checkbox from 'flarum/components/Checkbox';
import Stream from 'flarum/utils/Stream';

export default class NecrobumpingCheck extends Component {
    oninit(vnode) {
        super.oninit(vnode);

        this.checked = Stream(false);
    }

    oncreate(vnode) {
        super.oncreate(vnode);

        this.attrs.disable(true);
    }

    view() {
        const customTitle = app.data['fof-prevent-necrobumping.message.title'];
        const customDescription = app.data['fof-prevent-necrobumping.message.description'];
        const customAgreement = app.data['fof-prevent-necrobumping.message.agreement'];

        const time = dayjs().add(this.attrs.days, 'days').fromNow(true);

        return (
            <div>
                <div className="Alert">
                    <div className="Alert-body">
                        <div className="hide">
                            <h4>
                                {(customTitle && customTitle.replace(/\[time]/i, time)) ||
                                    app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.title', {
                                        time,
                                    })}
                            </h4>

                            <p>{customDescription || app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.description')}</p>
                        </div>

                        <Checkbox state={this.checked()} onchange={this.onchange.bind(this)}>
                            {customAgreement || app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.checkbox_label')}
                        </Checkbox>
                    </div>
                </div>
            </div>
        );
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
        this.attrs.disable(!newStatus);

        this.checked(newStatus);
    }
}
