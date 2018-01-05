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
 * Provides {@link block_todo\external\toggle_item} trait.
 *
 * @package     block_todo
 * @category    external
 * @copyright   2018 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_todo\external;

defined('MOODLE_INTERNAL') || die();

use block_todo\item;
use context_user;
use external_function_parameters;
use external_value;
use invalid_parameter_exception;

require_once($CFG->libdir.'/externallib.php');

/**
 * Trait implementing the external function block_todo_toggle_item.
 */
trait toggle_item {

    /**
     * Describes the structure of parameters for the function.
     *
     * @return external_function_parameters
     */
    public static function toggle_item_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'ID of the todo item'),
        ]);
    }

    /**
     * Toggle the done status of the item.
     *
     * @param int $id ID of the item
     */
    public static function toggle_item($id) {
        global $USER, $PAGE;

        $context = context_user::instance($USER->id);
        self::validate_context($context);
        require_capability('block/todo:myaddinstance', $context);

        $params = self::validate_parameters(self::toggle_item_parameters(), compact('id'));

        $item = item::get_record(['usermodified' => $USER->id, 'id' => $id]);

        if (!$item) {
            throw new invalid_parameter_exception('Unable to find your todo item with that ID');
        }

        $item->set('done', !$item->get('done'));
        $item->update();

        $itemexporter = new item_exporter($item, ['context' => $context]);

        return $itemexporter->export($PAGE->get_renderer('core'));
    }

    /**
     * Describes the structure of the function return value.
     *
     * @return external_single_structure
     */
    public static function toggle_item_returns() {
        return item_exporter::get_read_structure();
    }
}
