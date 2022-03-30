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
 * Open Educational Resources Plugin
 *
 * @package    oeruploader_tugraz
 * @author     Christian Ortner <christian.ortner@tugraz.at>
 * @copyright  2017-2022 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings->add(new admin_setting_configtext('oeruploader_tugraz/upload_url',
                                                get_string('upload_url', 'oeruploader_tugraz'),
                                                get_string('upload_url_description', 'oeruploader_tugraz'),
                                                ''));
    $settings->add(new admin_setting_configtext('oeruploader_tugraz/token_name',
                                                get_string('token_name', 'oeruploader_tugraz'),
                                                get_string('token_name_description', 'oeruploader_tugraz'),
                                                'token'));
    $settings->add(new admin_setting_configtext('oeruploader_tugraz/token',
                                                get_string('token', 'oeruploader_tugraz'),
                                                get_string('token_description', 'oeruploader_tugraz'),
                                                ''));
}
