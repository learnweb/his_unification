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
 * Version info for this plugin.
 *
 * @package    local_lsf_unification
 * @copyright  2011 Olaf Koehler WWU
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2025123100;
$plugin->component = 'local_lsf_unification';
$plugin->cron      = 86400;      // Once a day.
$plugin->requires  = 2024100700; // Require Moodle 4.5.
$plugin->supports  = [405, 501];
$plugin->maturity  = MATURITY_ALPHA;
