import type Discussion from 'flarum/common/models/Discussion';
/**
 * Check if a discussion is considered necrobumping based on configured days
 *
 * @param discussion - The discussion to check
 * @returns The number of days configured for necrobumping, or false if not necrobumping
 */
export declare function isNecrobumping(discussion?: Discussion): number | false;
