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
 * Plugin external functions and services are defined here.
 *
 * @package     block_todo
 * @category    external
 * @copyright   2018 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_todo_add_item' => [
        'classname' => 'block_todo\external\api',
        'methodname' => 'add_item',
        'classpath' => '',
        'description' => 'Adds a new item to the user\'s todo list',
        'type' => 'write',
        'capabilities' => 'block/todo:myaddinstance',
        'loginrequired' => true,
        'ajax' => true,
    ],

    'block_todo_toggle_item' => [
        'classname' => 'block_todo\external\api',
        'methodname' => 'toggle_item',
        'classpath' => '',
        'description' => 'Toggles the done status of the given item',
        'type' => 'write',
        'capabilities' => 'block/todo:myaddinstance',
        'loginrequired' => true,
        'ajax' => true,
    ],

    'block_todo_delete_item' => [
        'classname' => 'block_todo\external\api',
        'methodname' => 'delete_item',
        'classpath' => '',
        'description' => 'Removes the given item from the todo list',
        'type' => 'write',
        'capabilities' => 'block/todo:myaddinstance',
        'loginrequired' => true,
        'ajax' => true,
    ],
];
