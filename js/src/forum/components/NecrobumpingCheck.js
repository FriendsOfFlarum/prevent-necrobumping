import Component from 'flarum/Component';

export default class NecrobumpingCheck extends Component {
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
                    <span className="Alert-body">
                        <h4>
                            {(customTitle && customTitle.replace(/\[time]/i, time)) ||
                                app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.title', {
                                    time,
                                })}
                        </h4>

                        <p>{customDescription || app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.description')}</p>

                        <label>
                            <input type="checkbox" onchange={this.onchange.bind(this)} />
                            {customAgreement || app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.checkbox_label')}
                        </label>
                    </span>
                </div>
            </div>
        );
    }

    onchange() {
        this.checked = !this.checked;

        this.props.set(this.checked);
        this.props.disable(!this.checked);
    }
}
