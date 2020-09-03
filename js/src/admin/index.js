import classList from 'flarum/utils/classList';
import { settings } from '@fof-components';

const {
    SettingsModal,
    items: { StringItem, NumberItem },
} = settings;

const sortTags = (tags) => flarum.core.compat['tags/utils/sortTags'](tags);

app.initializers.add('fof/prevent-necrobumping', () => {
    app.extensionSettings['fof-prevent-necrobumping'] = () =>
        app.modal.show(SettingsModal, {
            title: 'FriendsOfFlarum Prevent Necrobumping',
            size: 'medium',
            className: 'FofPreventNecrobumping--Settings',
            items: (s) => [
                <div className="Form-group">
                    <label>{app.translator.trans('fof-prevent-necrobumping.admin.settings.days_label')}</label>
                    <NumberItem name="fof-prevent-necrobumping.days" simple min="0" required setting={s} />

                    <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.days_help')}</p>
                </div>,
                <div className="Form-group">
                    <label>{app.translator.trans('fof-prevent-necrobumping.admin.settings.message_title_label')}</label>
                    <StringItem name="fof-prevent-necrobumping.message.title" simple setting={s} />

                    <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.message_title_help')}</p>
                </div>,
                <StringItem name="fof-prevent-necrobumping.message.description" setting={s}>
                    {app.translator.trans('fof-prevent-necrobumping.admin.settings.message_description_label')}
                </StringItem>,
                <StringItem name="fof-prevent-necrobumping.message.agreement" setting={s}>
                    {app.translator.trans('fof-prevent-necrobumping.admin.settings.message_agreement_label')}
                </StringItem>,
                app.store.models.tags && (
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
                                    <NumberItem name={`fof-prevent-necrobumping.days.tags.${tag.id()}`} simple min="0" setting={s} />
                                </div>
                            ))}
                        </div>
                    </div>
                ),
            ],
        });
});
