import Component from 'flarum/Component';

export default class NecrobumpingCheck extends Component {
    config(isInitialized) {
        if (isInitialized) return;

        this.props.disable(true);
    }

    view() {
        return (
            <div>
                <div className="Alert">
                    <span className="Alert-body">
                        <h4>
                            {app.translator.transChoice('fof-prevent-necrobumping.forum.composer.warning.title', this.props.days, {
                                days: this.props.days,
                            })}
                        </h4>

                        <p>{app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.description')}</p>

                        <label>
                            <input type="checkbox" onchange={this.onchange.bind(this)} />
                            {app.translator.trans('fof-prevent-necrobumping.forum.composer.warning.checkbox_label')}
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
