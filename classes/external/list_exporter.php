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
 * Provides {@link block_todo\external\list_exporter} class.
 *
 * @package     block_todo
 * @copyright   2018 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_todo\external;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use core\external\exporter;

/**
 * Exporter of the todo list items.
 */
class list_exporter extends exporter {

    /**
     * Return the list of standard exported properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'instanceid' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    /**
     * Return the list of additional properties.
     *
     * @return array
     */
    protected static function define_other_properties() {
        return [
            'items' => [
                'type' => item_exporter::read_properties_definition(),
                'multiple' => true,
                'optional' => false,
            ],
        ];
    }

    /**
     * Returns a list of objects that are related.
     *
     * We need the context to be used when formatting the todotext field.
     *
     * @return array
     */
    protected static function define_related() {
        return [
            'context' => 'context',
            'items' => 'block_todo\item[]',
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {

        $items = [];

        foreach ($this->related['items'] as $item) {
            $itemexporter = new item_exporter($item, ['context' => $this->related['context']]);
            $items[] = $itemexporter->export($output);
        }

        return [
            'items' => $items,
        ];
    }
}
