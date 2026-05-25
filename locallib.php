<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Internal library of functions.
 *
 * @package   local_lsf_unification
 * @copyright 2026 Tamaro Walter
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns reusable data for the mail templates that is used in mail header and footer.
 * @return int
 */
function lsf_unification_basic_mail_data(): array {
    return [
        'unilogourl' => (new moodle_url('/local/lsf_unification/img/unims.png'))->out(),
        'learnweblogourl' => (new moodle_url('/local/lsf_unification/img/learnweb_light.png'))->out(),
        'zhlinfo_html' => get_string('zhlinfo_html', 'local_lsf_unification'),
        'zhlinfo_text' => get_string('zhlinfo_text', 'local_lsf_unification'),
        'uniinfo' => get_string('uniinfo', 'local_lsf_unification'),
        'uni' => get_string('uni', 'local_lsf_unification', ['year' => date('Y')]),
        'noreply' => get_string('noreply', 'local_lsf_unification'),
    ];
}
