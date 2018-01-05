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
 * Provides {@link block_todo_external_testcase} class.
 *
 * @package     block_todo
 * @category    test
 * @copyright   2018 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Tests for the external API of the plugin.
 *
 * @copyright 2018 David Mudrak <david@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_todo_external_testcase extends advanced_testcase {

    /** @var stdClass */
    protected $user;

    /** @var array */
    protected $anotheruser;

    /**
     * Set up for every test
     */
    public function setUp() {
        global $DB;
        $this->resetAfterTest();

        $this->user = self::getDataGenerator()->create_user();
        self::setUser($this->user);

        $this->anotheruser = self::getDataGenerator()->create_user();
    }

    /**
     * Test that users who can't add the block to their dashboard, can't add new todo items.
     *
     * @expectedException required_capability_exception
     */
    public function test_add_item_no_permission() {

        $context = context_user::instance($this->user->id);

        $userroles = get_archetype_roles('user');
        $authrole = array_pop($userroles);
        unassign_capability('block/todo:myaddinstance', $authrole->id);

        block_todo\external\api::add_item('This should throw required_capability_exception');
    }

    /**
     * Test adding a new todo item.
     */
    public function test_add_item() {

        $items = block_todo\item::get_my_todo_items();
        $this->assertEmpty($items);

        $todotext = '<h1>Meditate!</h1>';
        $raw = block_todo\external\api::add_item($todotext);
        $result = external_api::clean_returnvalue(block_todo\external\api::add_item_returns(), $raw);

        $this->assertEquals($result['todotext'], strip_tags($todotext));
        $this->assertSame($result['done'], false);
        $this->assertEquals($result['usermodified'], $this->user->id);

        $items = block_todo\item::get_my_todo_items();
        $this->assertEquals(1, count($items));
    }

    /**
     * Test that the done status of an item can be toggled.
     */
    public function test_toggle_item() {

        $todotext = 'Write a unit test';
        $raw = block_todo\external\api::add_item($todotext);
        $result = external_api::clean_returnvalue(block_todo\external\api::add_item_returns(), $raw);
        $this->assertSame($result['done'], false);

        $raw = block_todo\external\api::toggle_item($result['id']);
        $result = external_api::clean_returnvalue(block_todo\external\api::toggle_item_returns(), $raw);
        $this->assertSame($result['done'], true);

        $raw = block_todo\external\api::toggle_item($result['id']);
        $result = external_api::clean_returnvalue(block_todo\external\api::toggle_item_returns(), $raw);
        $this->assertSame($result['done'], false);
    }

    /**
     * Test that the done status can't be toggled by another user.
     *
     * @expectedException invalid_parameter_exception
     */
    public function test_toggle_item_by_another_user() {

        $todotext = 'This is my todo!';
        $raw = block_todo\external\api::add_item($todotext);
        $result = external_api::clean_returnvalue(block_todo\external\api::add_item_returns(), $raw);

        self::setUser($this->anotheruser);
        block_todo\external\api::toggle_item($result['id']);
    }

    /**
     * Test that the done status of an item can be deleted.
     */
    public function test_delete_item() {

        $todotext = 'Write a unit test';
        $raw = block_todo\external\api::add_item($todotext);
        $result = external_api::clean_returnvalue(block_todo\external\api::add_item_returns(), $raw);
        $itemid = $result['id'];
        $this->assertTrue(block_todo\item::record_exists($itemid));

        $raw = block_todo\external\api::delete_item($itemid);
        $result = external_api::clean_returnvalue(block_todo\external\api::delete_item_returns(), $raw);
        $this->assertSame($result, $itemid);
        $this->assertFalse(block_todo\item::record_exists($itemid));
    }

    /**
     * Test that the item can't be deleted by another user.
     *
     * @expectedException invalid_parameter_exception
     */
    public function test_delete_item_by_another_user() {

        $todotext = 'This is not yours!';
        $raw = block_todo\external\api::add_item($todotext);
        $result = external_api::clean_returnvalue(block_todo\external\api::add_item_returns(), $raw);

        self::setUser($this->anotheruser);
        block_todo\external\api::delete_item($result['id']);
    }
}
