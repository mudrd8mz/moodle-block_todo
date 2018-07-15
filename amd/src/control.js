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
 * Provides the block_todo/control module
 *
 * @package     block_todo
 * @category    output
 * @copyright   2018 David Mudrák <david@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module block_todo/control
 */
define(['jquery', 'core/log', 'core/ajax', 'core/templates', 'core/str'], function($, Log, Ajax, Templates, Str) {
    'use strict';

    /**
     * Initializes the block controls.
     */
    function init(instanceid) {
        Log.debug('block_todo/control: initializing controls of the todo block instance ' + instanceid);

        var region = $('[data-region="block_todo-instance-' + instanceid +'"]').first();

        if (!region.length) {
            Log.error('block_todo/control: wrapping region not found!');
            return;
        }

        var control = new TodoControl(region);
        control.main();
    }

    /**
     * Controls a single ToDo block instance contents.
     *
     * @constructor
     * @param {jQuery} region
     */
    function TodoControl(region) {
        var self = this;
        self.region = region;
    }

    /**
     * Run the controller.
     *
     */
    TodoControl.prototype.main = function () {
        var self = this;

        self.addTextForm = self.region.find('[data-control="addform"]').first();
        self.addTextInput = self.addTextForm.find('input').first();
        self.addTextButton = self.addTextForm.find('button').first();
        self.itemsList = self.region.find('ul').first();

        self.initAddFeatures();
        self.initEditFeatures();
    };

    /**
     * Initialize the controls for adding a new todo item.
     *
     * @method
     */
    TodoControl.prototype.initAddFeatures = function () {
        var self = this;

        self.addTextForm.on('submit', function(e) {
            e.preventDefault();
            self.addNewTodo();
        });

        self.addTextButton.on('click', function() {
            self.addTextForm.submit();
        });
    };

    /**
     * Initialize the controls for modifying existing items.
     *
     * @method
     */
    TodoControl.prototype.initEditFeatures = function () {
        var self = this;

        self.itemsList.on('click', '[data-item]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(e.currentTarget).attr('data-item');
            self.toggleItem(id);
        });

        self.itemsList.on('click', '[data-control="delete"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(e.currentTarget).closest('[data-item]').attr('data-item');
            self.deleteItem(id);
        });
    };

    /**
     * Add a new todo item.
     *
     * @method
     * @return {Deferred}
     */
    TodoControl.prototype.addNewTodo = function () {
        var self = this;
        var text = $.trim(self.addTextInput.val());

        if (!text) {
            return Str.get_string('placeholdermore', 'block_todo').then(function(text) {
                self.addTextInput.prop('placeholder', text);
                return $.Deferred().resolve();
            });
        }

        self.addTextInput.prop('disabled', true);

        return Ajax.call([{
            methodname: 'block_todo_add_item',
            args: {
                todotext: text
            }

        }])[0].fail(function(reason) {
            Log.error('block_todo/control: unable to add the item');
            Log.debug(reason);
            self.addTextButton.addClass('btn-danger');
            self.addTextButton.html('<i class="fa fa-exclamation-circle" aria-hidden="true"></i>');
            return $.Deferred().reject();

        }).then(function(response) {
            return Templates.render('block_todo/item', response).fail(function(reason) {
                Log.error('block_todo/control: unable to render the new item:' + reason);
            });

        }).then(function(item) {
            self.itemsList.prepend(item);
            self.addTextInput.val('');
            self.addTextInput.prop('disabled', false);
            self.addTextInput.focus();
            return $.Deferred().resolve();
        });
    };

    /**
     * Toggle the done status of the given item.
     *
     * @method
     * @return {Deferred}
     */
    TodoControl.prototype.toggleItem = function (id) {
        var self = this;

        if (!id) {
            return $.Deferred().resolve();
        }

        return Ajax.call([{
            methodname: 'block_todo_toggle_item',
            args: {
                id: id
            }

        }])[0].fail(function(reason) {
            Log.error('block_todo/control: unable to toggle the item');
            Log.debug(reason);
            return $.Deferred().reject();

        }).then(function(response) {
            return Templates.render('block_todo/item', response).fail(function(reason) {
                Log.error('block_todo/control: unable to render the new item:' + reason);
            });

        }).then(function(item) {
            self.itemsList.find('[data-item="' + id + '"]').replaceWith(item);
            return $.Deferred().resolve();
        });
    };

    /**
     * Delete the given item.
     *
     * @method
     * @return {Deferred}
     */
    TodoControl.prototype.deleteItem = function (id) {
        var self = this;

        if (!id) {
            return $.Deferred().resolve();
        }

        return Ajax.call([{
            methodname: 'block_todo_delete_item',
            args: {
                id: id
            }

        }])[0].fail(function(reason) {
            Log.error('block_todo/control: unable to delete the item');
            Log.debug(reason);
            return $.Deferred().reject();

        }).then(function(deletedid) {
            self.itemsList.find('[data-item="' + deletedid + '"]').remove();
            return $.Deferred().resolve();
        });
    };

    return {
        init: init
    };
});
