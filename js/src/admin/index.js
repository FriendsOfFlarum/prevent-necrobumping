import SettingsModal from '@fof/components/admin/settings/SettingsModal';
import NumberItem from '@fof/components/admin/settings/items/NumberItem';

app.initializers.add('fof/prevent-necrobumping', () => {
    app.extensionSettings['fof-prevent-necrobumping'] = () =>
        app.modal.show(
            new SettingsModal({
                title: 'FriendsOfFlarum Prevent Necrobumping',
                size: 'medium',
                items: [
                    <p>{app.translator.trans('fof-prevent-necrobumping.admin.settings.days_help')}</p>,
                    <NumberItem key="fof-prevent-necrobumping.days" min="1">
                        {app.translator.trans('fof-prevent-necrobumping.admin.settings.days_label')}
                    </NumberItem>,
                ],
            })
        );
});
