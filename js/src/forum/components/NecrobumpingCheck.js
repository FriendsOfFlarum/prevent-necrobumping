import Component from 'flarum/Component';
import Checkbox from 'flarum/components/Checkbox'

export default class NecrobumpingCheck extends Component {
    init() {
        super.init();

        this.checked = m.prop(false);
    }

    config(isInitialized) {
        if (isInitialized) return;

        this.props.disable(true);
    }

    view() {
        const customTitle = app.data['fof-prevent-necrobumping.message.title'];
        const customDescription = app.data['fof-prevent-necrobumping.message.description'];
        const customAgreement = app.data['fof-prevent-necrobumping.message.agreement'];

        const time = moment.duration(this.props.days, 'days').humanize();

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

                        {Checkbox.component({
                            state: this.checked(),
                            onchange: this.onchange.bind(this),
                            children: customAgreement || app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.checkbox_label'),
                        })}
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

        this.props.set(newStatus);
        this.props.disable(!newStatus);

        this.checked(newStatus);
    }
}
