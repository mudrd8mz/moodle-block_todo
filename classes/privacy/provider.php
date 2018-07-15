<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines {@link \block_todo\privacy\provider} class.
 *
 * @package     block_todo
 * @category    privacy
 * @copyright   2018 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_todo\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Privacy API implementation for the My ToDo list plugin.
 *
 * @copyright  2018 David Mudrák <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider {

    use \core_privacy\local\legacy_polyfill;

    /**
     * Describe all the places where the My ToDo list plugin stores some personal data.
     *
     * @param collection $collection Collection of items to add metadata to.
     * @return collection Collection with our added items.
     */
    public static function _get_metadata(collection $collection) {

        $collection->add_database_table('block_todo', [
           'timecreated' => 'privacy:metadata:db:blocktodo:timecreated',
           'timemodified' => 'privacy:metadata:db:blocktodo:timemodified',
           'todotext' => 'privacy:metadata:db:blocktodo:todotext',
           'done' => 'privacy:metadata:db:blocktodo:done',
        ], 'privacy:metadata:db:blocktodo');

        return $collection;
    }

    /**
     * Get the list of contexts that contain personal data for the specified user.
     *
     * @param int $userid ID of the user.
     * @return contextlist List of contexts containing the user's personal data.
     */
    public static function _get_contexts_for_userid($userid) {

        $contextlist = new contextlist();

        $sql = "SELECT DISTINCT c.id
                  FROM {block_todo} b
                  JOIN {context} c ON c.instanceid = b.usermodified AND c.contextlevel = :contextuser
                 WHERE b.usermodified = :userid";

        $params = [
            'contextuser' => CONTEXT_USER,
            'userid' => $userid
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export personal data stored in the given contexts.
     *
     * @param approved_contextlist $contextlist List of contexts approved for export.
     */
    public static function _export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (!count($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();

        $items = $DB->get_records('block_todo', ['usermodified' => $user->id], 'timecreated DESC',
            'id, timecreated, timemodified, todotext, done');

        foreach ($items as &$item) {
            unset($item->id);
            $item->timecreated = transform::datetime($item->timecreated);
            $item->timemodified = transform::datetime($item->timemodified);
            $item->done = transform::yesno($item->done);
        }

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER || $context->instanceid != $user->id) {
                continue;
            }
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'block_todo')],
                (object)['todo' => array_values($items)]
            );
        }
    }

    /**
     * Delete personal data for all users in the context.
     *
     * @param context $context Context to delete personal data from.
     */
    public static function _delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_USER) {
            $DB->delete_records('block_todo', ['usermodified' => $context->instanceid]);
        }
    }

    /**
     * Delete personal data for the user in a list of contexts.
     *
     * @param approved_contextlist $contextlist List of contexts to delete data from.
     */
    public static function _delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (!count($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER || $context->instanceid != $user->id) {
                continue;
            }

            $DB->delete_records('block_todo', ['usermodified' => $user->id]);
        }
    }
}
