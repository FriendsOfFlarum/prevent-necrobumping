import IndexPage from 'flarum/forum/components/IndexPage';

/**
 * Initiates a new discussion either using a provided context or the default
 * IndexPage prototype.
 *
 * @param {IndexPage} context - The context to use for initiating the discussion, defaults to IndexPage prototype.
 * @returns {Void} - Doesn't return anything.
 */
export function initiateNewDiscussion(context = IndexPage.prototype) {
  IndexPage.prototype.newDiscussionAction.call(context).catch(() => {});
}
