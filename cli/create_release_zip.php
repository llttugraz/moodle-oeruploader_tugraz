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
 * @copyright  2022 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '../../../../../../config.php');
global $CFG, $DB;

$zipper = new \local_oer\zipper();
$courses = \local_oer\helper\activecourse::get_list_of_courses(true);
foreach ($courses as $course) {
    if ($DB->record_exists('course', ['id' => $course->courseid])) {
        echo 'Course ' . $course->courseid . PHP_EOL;
        list($packages, $info) = $zipper->separate_files_to_packages($course->courseid, true, true);
        $zipfile  = $zipper->compress_file_package($course->courseid, $packages[0]);
        $filename = 'Course-' . $course->courseid . '_' . time() . '.zip';
        if ($zipfile) {
            echo 'Write zip file for course ' . $course->courseid;
            copy($zipfile, $CFG->dirroot . '/local/oer/uploader/tugraz/cli/' . $filename);
            $zipper->delete_temp_folder($zipfile);
        }
    } else {
        echo 'Course ' . $course->courseid . ' does not exist - needs to be cleaned up.' . PHP_EOL;
    }
}
