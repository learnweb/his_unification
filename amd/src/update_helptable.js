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
 * JavaScript that calls the functions to update the lsf unification tables
 *
 * @module     local_lsf_unification/update_helptable
 * @copyright  2026 Tamaro Walter
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import {prefetchStrings} from 'core/prefetch';
import {getString} from "core/str";

/**
 * Init function.
 */
export function init() {
    prefetchStrings('local_lsf_unification', ['update_helptable_notification']);
    const updateButton = document.querySelector('[data-action="update-helptable"]');

    if (updateButton) {
        updateButton.addEventListener('click', async () => {
            const data = {
                methodname: 'local_lsf_unification_update_helptable',
                args: {},
            };
            try {
                const result = await Ajax.call([data])[0];
                if (result) {
                    const message = await getString('update_helptable_notification', 'local_lsf_unification');
                    await Notification.addNotification({
                        message: message,
                        type: 'success',
                    });
                }
            } catch (error) {Notification.exception(error);
            }
        });
    }
}
