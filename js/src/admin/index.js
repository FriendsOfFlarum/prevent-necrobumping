import SettingsModal from '@fof/components/admin/settings/SettingsModal';
import NumberItem from '@fof/components/admin/settings/items/NumberItem';
import StringItem from '@fof/components/admin/settings/items/StringItem';

app.initializers.add('fof/prevent-necrobumping', () => {
    app.extensionSettings['fof-prevent-necrobumping'] = () =>
        app.modal.show(
            new SettingsModal({
                title: 'FriendsOfFlarum Prevent Necrobumping',
                size: 'medium',
                items: [
                    <div className="Form-group">
                        <label>{app.translator.trans('fof-prevent-necrobumping.admin.settings.days_label')}</label>
                        <NumberItem key="fof-prevent-necrobumping.days" simple min="1" required />

                        <br />
                        <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.days_help')}</p>
                    </div>,
                    <div className="Form-group">
                        <label>{app.translator.trans('fof-prevent-necrobumping.admin.settings.message_title_label')}</label>
                        <StringItem key="fof-prevent-necrobumping.message.title" simple />

                        <br />
                        <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.message_title_help')}</p>
                    </div>,
                    <StringItem key="fof-prevent-necrobumping.message.description">
                        {app.translator.trans('fof-prevent-necrobumping.admin.settings.message_description_label')}
                    </StringItem>,
                    <StringItem key="fof-prevent-necrobumping.message.agreement">
                        {app.translator.trans('fof-prevent-necrobumping.admin.settings.message_agreement_label')}
                    </StringItem>,
                ],
            })
        );
});
