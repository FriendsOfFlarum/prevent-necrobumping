import app from 'flarum/forum/app';

/**
 * Initiates a new discussion by opening the composer
 */
export function initiateNewDiscussion() {
  const deferred = m.deferred();

  if (app.session.user) {
    app.composer
      .load(() => import('flarum/forum/components/DiscussionComposer'), { user: app.session.user })
      .then(() => {
        app.composer.show();
        deferred.resolve();
      });
  } else {
    app.modal.show(() => import('flarum/forum/components/LogInModal'));
    deferred.resolve();
  }

  return deferred.promise;
}
