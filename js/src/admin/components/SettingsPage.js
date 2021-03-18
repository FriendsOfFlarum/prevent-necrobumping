import { settings } from '@fof-components';
import classList from 'flarum/common/utils/classList';
import ExtensionPage from 'flarum/common/components/ExtensionPage';

const {
    items: { StringItem, NumberItem },
} = settings;

const sortTags = (tags) => flarum.core.compat['tags/utils/sortTags'](tags);

export default class SettingsPage extends ExtensionPage {
    oninit(vnode) {
        super.oninit(vnode);

        this.setting = this.setting.bind(this);
    }

    content() {
        return [
            <div class="container">
                <div class="NecroPage">
                    <div className="Form-group">
                        <label>{app.translator.trans('fof-prevent-necrobumping.admin.settings.days_label')}</label>
                        <NumberItem name="fof-prevent-necrobumping.days" simple min="0" required setting={this.setting} />

                        <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.days_help')}</p>
                    </div>
                    <div className="Form-group">
                        <label>{app.translator.trans('fof-prevent-necrobumping.admin.settings.message_title_label')}</label>
                        <StringItem name="fof-prevent-necrobumping.message.title" simple setting={this.setting} />

                        <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.message_title_help')}</p>
                    </div>
                    <StringItem name="fof-prevent-necrobumping.message.description" setting={this.setting}>
                        {app.translator.trans('fof-prevent-necrobumping.admin.settings.message_description_label')}
                    </StringItem>
                    <StringItem name="fof-prevent-necrobumping.message.agreement" setting={this.setting}>
                        {app.translator.trans('fof-prevent-necrobumping.admin.settings.message_agreement_label')}
                    </StringItem>

                    {app.store.models.tags && (
                        <div class="Form-group">
                            <h3>{app.translator.trans('fof-prevent-necrobumping.admin.settings.tags_title')}</h3>
                            <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.tags_help')}</p>

                            <div className="necrobumping--tags">
                                {sortTags(app.store.all('tags')).map((tag) => (
                                    <div
                                        className={classList([
                                            'Form-group',
                                            tag.isChild() && 'isChild',
                                            !tag.isPrimary() && !tag.isChild() && 'isSecondary',
                                        ])}
                                    >
                                        <label>{tag.name()}</label>
                                        <NumberItem name={`fof-prevent-necrobumping.days.tags.${tag.id()}`} simple min="0" setting={this.setting} />
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {this.submitButton()}
                </div>
            </div>,
        ];
    }
}
