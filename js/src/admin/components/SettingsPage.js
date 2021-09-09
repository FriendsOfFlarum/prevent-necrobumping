import app from 'flarum/admin/app';
import classList from 'flarum/common/utils/classList';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import sortTags from 'flarum/tags/utils/sortTags';

export default class SettingsPage extends ExtensionPage {
    oninit(vnode) {
        super.oninit(vnode);
    }

    content() {
        return [
            <div class="container">
                <div class="NecroPage">
                    {this.buildSettingComponent({
                        type: 'number',
                        setting: 'fof-prevent-necrobumping.days',
                        label: app.translator.trans('fof-prevent-necrobumping.admin.settings.days_label'),
                        help: app.translator.trans('fof-prevent-necrobumping.admin.settings.days_help'),
                        min: 0,
                    })}
                    {this.buildSettingComponent({
                        type: 'string',
                        setting: 'fof-prevent-necrobumping.message.title',
                        label: app.translator.trans('fof-prevent-necrobumping.admin.settings.message_title_label'),
                        help: app.translator.trans('fof-prevent-necrobumping.admin.settings.message_title_help'),
                    })}
                    {this.buildSettingComponent({
                        type: 'string',
                        setting: 'fof-prevent-necrobumping.message.description',
                        label: app.translator.trans('fof-prevent-necrobumping.admin.settings.message_description_label'),
                    })}
                    {this.buildSettingComponent({
                        type: 'string',
                        setting: 'fof-prevent-necrobumping.message.agreement',
                        label: app.translator.trans('fof-prevent-necrobumping.admin.settings.message_agreement_label'),
                    })}

                    {app.store.models.tags && (
                        <div class="Form-group">
                            <hr />
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
                                        {this.buildSettingComponent({
                                            type: 'number',
                                            setting: `fof-prevent-necrobumping.days.tags.${tag.id()}`,
                                            label: tag.name(),
                                            min: '0',
                                        })}
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
