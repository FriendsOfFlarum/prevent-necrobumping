import app from 'flarum/admin/app';
import isExtensionEnabled from 'flarum/admin/utils/isExtensionEnabled';
import classList from 'flarum/common/utils/classList';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import FieldSet from 'flarum/common/components/FieldSet';
import Button from 'flarum/common/components/Button';
import Stream from 'flarum/common/utils/Stream';
import sortTags from 'ext:flarum/tags/common/utils/sortTags';
import tagLabel from 'ext:flarum/tags/common/helpers/tagLabel';

export default class SettingsPage extends ExtensionPage {
  excludedLockTags!: Stream<number[]>;

  oninit(vnode: any) {
    super.oninit(vnode);

    const stored =
      this.setting('fof-prevent-necrobumping.lock_days_exclude_tags')() ||
      '';
    const parsed = stored
      .split(',')
      .map((id: string) => Number(id))
      .filter((id: number) => !Number.isNaN(id));

    this.excludedLockTags = Stream(parsed);
  }

  openExcludedTagsModal() {
    // Load the modal chunk on demand so the registry has the export.
    flarum.reg.asyncModuleImport('ext:flarum/tags/common/components/TagSelectionModal').then((TagSelectionModal: any) => {
      const selectedTags = (this.excludedLockTags() || [])
        .map((id: number) => app.store.getById('tags', id))
        .filter(Boolean);

      app.modal.show(TagSelectionModal.default ?? TagSelectionModal, {
        allowResetting: true,
        selectedTags,
        onsubmit: (tags: any[]) => {
          const ids = tags.map((tag) => Number(tag.id()));

          this.excludedLockTags(ids);
          this.setting('fof-prevent-necrobumping.lock_days_exclude_tags')(ids.join(','));
        },
      });
    });
  }

  content() {
    const softLimit = Number(this.setting('fof-prevent-necrobumping.days')() || 0);
    const lifecycleEnabled = isExtensionEnabled('glowingblue-discussion-lifecycle');

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
                type: 'number',
                setting: 'fof-prevent-necrobumping.lock_days',
                label: app.translator.trans('fof-prevent-necrobumping.admin.settings.lock_days_label'),
                help: app.translator.trans('fof-prevent-necrobumping.admin.settings.lock_days_help'),
                min: softLimit || 0,
              })}
              {lifecycleEnabled && (
                <p className="helpText">
                  {app.translator.trans('fof-prevent-necrobumping.admin.settings.lock_days_lifecycle_help')}
                </p>
              )}
              {'flarum-tags' in flarum.extensions && (
                <div className="Form-group">
                  <label>{app.translator.trans('fof-prevent-necrobumping.admin.settings.lock_days_exclude_tags_label')}</label>
                  <p className="helpText">{app.translator.trans('fof-prevent-necrobumping.admin.settings.lock_days_exclude_tags_help')}</p>
                  <Button className="Button" onclick={() => this.openExcludedTagsModal()}>
                    {app.translator.trans('flarum-tags.lib.tag_selection_modal.title')}
                  </Button>
                  {this.excludedLockTags().length > 0 && (
                    <ul className="TagList">
                      {this.excludedLockTags().map((id: number) => {
                        const tag = app.store.getById('tags', id);

                        return tag ? <li key={id}>{tagLabel(tag)}</li> : null;
                      })}
                    </ul>
                  )}
                </div>
              )}
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
