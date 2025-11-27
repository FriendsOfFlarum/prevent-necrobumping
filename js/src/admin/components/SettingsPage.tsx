import app from 'flarum/admin/app';
import classList from 'flarum/common/utils/classList';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import FieldSet from 'flarum/common/components/FieldSet';
import sortTags from 'ext:flarum/tags/common/utils/sortTags';
import tagLabel from 'ext:flarum/tags/common/helpers/tagLabel';

export default class SettingsPage extends ExtensionPage {
  content() {
    return (
      <div className="ExtensionPage-settings">
        <div className="container">
          <div className="Form">
            <FieldSet>
              <h2>{app.translator.trans('fof-prevent-necrobumping.admin.settings.general_heading')}</h2>
              {this.buildSettingComponent({
                type: 'number',
                setting: 'fof-prevent-necrobumping.days',
                label: app.translator.trans('fof-prevent-necrobumping.admin.settings.days_label'),
                help: app.translator.trans('fof-prevent-necrobumping.admin.settings.days_help'),
                min: 0,
              })}
              {this.buildSettingComponent({
                type: 'boolean',
                setting: 'fof-prevent-necrobumping.show_discussion_cta',
                label: app.translator.trans('fof-prevent-necrobumping.admin.settings.show_discussion_cta_label'),
                help: app.translator.trans('fof-prevent-necrobumping.admin.settings.show_discussion_cta_help'),
              })}
            </FieldSet>

            {'flarum-tags' in flarum.extensions && (
              <FieldSet className="PreventNecrobumping-TagSettings">
                <h2>{app.translator.trans('fof-prevent-necrobumping.admin.settings.tags_title')}</h2>
                <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.tags_help')}</p>
                <div className="necrobumping--tags">
                  {sortTags(app.store.all('tags')).map((tag) => (
                    <div
                      key={tag.id()}
                      className={classList([
                        'necrobumping-tag-item',
                        tag.isChild() && 'necrobumping-tag-item--child',
                        !tag.isPrimary() && !tag.isChild() && 'necrobumping-tag-item--secondary',
                      ])}
                    >
                      <label className="necrobumping-tag-label">{tagLabel(tag)}</label>
                      <input
                        className="FormControl necrobumping-tag-input"
                        type="number"
                        min="0"
                        placeholder={app.translator.trans('fof-prevent-necrobumping.admin.settings.tags_placeholder')}
                        bidi={this.setting(`fof-prevent-necrobumping.days.tags.${tag.id()}`)}
                      />
                    </div>
                  ))}
                </div>
              </FieldSet>
            )}

            <div className="Form-group Form-controls">{this.submitButton()}</div>
          </div>
        </div>
      </div>
    );
  }
}
