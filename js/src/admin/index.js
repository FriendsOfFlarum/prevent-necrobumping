import app from 'flarum/admin/app';

import SettingsPage from './components/SettingsPage';

app.initializers.add('fof/prevent-necrobumping', () => {
    app.extensionData.for('fof-prevent-necrobumping').registerPage(SettingsPage);
});
