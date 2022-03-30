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
 * @copyright  2019-2022 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace oeruploader_tugraz;

use local_oer\helper\filehelper;
use local_oer\release;

/**
 * Class queue
 */
class queue {
    /**
     * Queue
     */
    const QUEUE = 'oeruploader_tugraz_queue';

    /**
     * Add course to queue
     *
     * @param int      $courseid
     * @param int|null $uploadsize
     * @return void
     * @throws \dml_exception
     */
    public static function add(int $courseid, ?int $uploadsize = null) {
        global $DB, $USER;
        if (!$DB->record_exists('course', ['id' => $courseid])) {
            return;
        }
        $queue               = new \stdClass();
        $queue->courseid     = $courseid;
        $queue->attempt      = 0;
        $queue->coursesize   = $uploadsize == null ? self::calculate_upload_filesize($courseid) : $uploadsize;
        $queue->timecreated  = time();
        $queue->timemodified = time();
        $queue->usermodified = $USER->id;

        if ($DB->record_exists(self::QUEUE, ['courseid' => $courseid])) {
            $record               = $DB->get_record(self::QUEUE, ['courseid' => $courseid]);
            $record->coursesize   = $queue->coursesize;
            $record->timemodified = time();
            $record->usermodified = $USER->id;
            $DB->update_record(self::QUEUE, $record);
        } else {
            $DB->insert_record(self::QUEUE, $queue);
        }
    }

    /**
     * Delete course from queue
     *
     * @param int $courseid
     * @return bool
     * @throws \dml_exception
     */
    public static function delete(int $courseid) {
        global $DB;
        return $DB->delete_records(self::QUEUE, ['courseid' => $courseid]);
    }

    /**
     * Add course to end of queue
     *
     * @param int $courseid
     * @throws \dml_exception
     */
    public static function requeue(int $courseid) {
        global $DB, $USER;
        $record = $DB->get_record(self::QUEUE, ['courseid' => $courseid]);
        self::delete($courseid);
        unset($record->id);
        $record->attempt++;
        $record->timemodified = time();
        $record->usermodified = $USER->id;
        $DB->insert_record(self::QUEUE, $record);
    }

    /**
     * Calculate file sizes of course
     *
     * @param int $courseid
     * @return false|int|mixed
     * @throws \dml_exception
     */
    public static function calculate_upload_filesize(int $courseid) {
        $release = new release($courseid);
        $files   = $release->get_released_files();
        $size    = 0;
        foreach ($files as $file) {
            $size += $file['storedfile']->get_filesize();
        }
        return $size;
    }

    /**
     * Get next in queue
     *
     * @return false|mixed
     * @throws \dml_exception
     */
    public static function get_next() {
        global $DB;
        $record = $DB->get_records(self::QUEUE, [], 'id', '*', 0, 0);
        if ($record) {
            return reset($record);
        }
        return false; // Queue is empty.
    }

    /**
     * Get amount of courses in queue
     *
     * @return int
     * @throws \dml_exception
     */
    public static function get_queue_size() {
        global $DB;
        return $DB->count_records(self::QUEUE);
    }

    /**
     * Get the whole queue
     *
     * @return array
     * @throws \dml_exception
     */
    public static function get_queue() {
        global $DB;
        $queue = $DB->get_records(self::QUEUE);

        $result = [];
        $pos    = 1;
        foreach ($queue as $key => $element) {
            $course   = get_course($element->course);
            $result[] = [
                    'position'   => $pos,
                    'coursename' => $course->fullname,
                    'courseid'   => $element->course,
                    'added'      => userdate($element->timecreated),
                    'attempt'    => $element->attempt,
                    'size'       => filehelper::get_readable_filesize($element->coursesize, true),
            ];
            $pos++;
        }

        return $result;
    }

    /**
     * Get the filesizes of all courses in queue
     *
     * @return mixed
     * @throws \dml_exception
     */
    public static function get_queue_upload_size() {
        global $DB;
        $sql        = 'SELECT SUM(coursesize) FROM {' . self::QUEUE . '}';
        $uploadsize = $DB->get_record_sql($sql);
        return filehelper::get_readable_filesize(reset($uploadsize), true);
    }

    /**
     * Update queue
     *
     * @param int $courseid
     * @throws \dml_exception
     */
    public static function update($courseid) {
        if (($uploadsize = self::calculate_upload_filesize($courseid)) > 0) {
            self::add($courseid, $uploadsize);
        } else {
            self::delete($courseid);
        }
    }
}
