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

namespace oeruploader_tugraz;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../../tests/helper/testcourse.php');

use local_oer\metadata\courseinfo_sync;
use local_oer\snapshot;
use local_oer\testcourse;

/**
 * Class local_queue_testcase
 */
class queue_test extends \advanced_testcase {
    /**
     * Test add_to_queue
     *
     * @return void
     * @throws \dml_exception
     */
    public function test_add_to_queue() {
        $this->resetAfterTest(true);

        global $DB;

        queue::add(7);

        $this->assertEquals(0, $DB->count_records('oeruploader_tugraz_queue'),
                            'Empty when course does not exist, or course is not active');

        $this->setAdminUser();
        $helper = new testcourse();
        $course = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course->id);

        $now = time();
        queue::add($course->id);
        $this->assertEquals(1, $DB->count_records('oeruploader_tugraz_queue'));

        $record = $DB->get_record('oeruploader_tugraz_queue', ['courseid' => $course->id]);
        $this->assertEquals($course->id, $record->courseid);
        $this->assertEquals(0, $record->attempt);
        $this->assertTrue($record->timecreated >= $now);
        $this->assertTrue($record->timemodified >= $now);
        $this->assertEquals(2, $record->usermodified);
        $this->assertEquals(0, $record->coursesize);

        $now = time();
        queue::add($course->id, 200);
        $record = $DB->get_record('oeruploader_tugraz_queue', ['courseid' => $course->id]);
        $this->assertEquals($course->id, $record->courseid);
        $this->assertEquals(0, $record->attempt);
        $this->assertTrue($record->timecreated <= $now);
        $this->assertTrue($record->timemodified >= $now);
        $this->assertEquals(2, $record->usermodified);
        $this->assertEquals(200, $record->coursesize);
        $this->assertEquals(1, $DB->count_records('oeruploader_tugraz_queue'), 'Update course');
        $course2 = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course2->id);
        queue::add($course2->id);
        $this->assertEquals(2, $DB->count_records('oeruploader_tugraz_queue'), 'Added a second course');
    }

    /**
     * Test delete_from_queue
     *
     * @return void
     * @throws \dml_exception
     */
    public function test_delete_from_queue() {
        $this->resetAfterTest(true);
        global $DB;
        $this->setAdminUser();
        $helper  = new testcourse();
        $course1 = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course1->id);
        queue::add($course1->id);
        $this->assertEquals(1, $DB->count_records('oeruploader_tugraz_queue'));
        $course2 = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course2->id);
        queue::add($course2->id);
        $this->assertEquals(2, $DB->count_records('oeruploader_tugraz_queue'));
        $course3 = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course3->id);
        queue::add($course3->id);
        $this->assertEquals(3, $DB->count_records('oeruploader_tugraz_queue'));
        queue::delete($course2->id);
        $this->assertEquals(2, $DB->count_records('oeruploader_tugraz_queue'));
        queue::delete($course1->id);
        $this->assertEquals(1, $DB->count_records('oeruploader_tugraz_queue'));
        queue::delete($course3->id);
        $this->assertEquals(0, $DB->count_records('oeruploader_tugraz_queue'));
    }

    /**
     * Test requeue
     *
     * @return void
     * @throws \dml_exception
     */
    public function test_requeue() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        global $DB;
        $helper  = new testcourse();
        $course1 = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course1->id);
        queue::add($course1->id);
        $course2 = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course2->id);
        queue::add($course2->id);
        $course3 = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course3->id);
        queue::add($course3->id);

        $queue = $DB->get_records('oeruploader_tugraz_queue', [], 'id');
        $this->assertEquals($course1->id, reset($queue)->courseid);
        queue::requeue($course1->id);
        $queue = $DB->get_records('oeruploader_tugraz_queue', [], 'id');
        $this->assertEquals($course2->id, reset($queue)->courseid);
        $record = $DB->get_record('oeruploader_tugraz_queue', ['courseid' => $course1->id]);
        $this->assertEquals(1, $record->attempt);
        queue::requeue($course1->id);
        $queue = $DB->get_records('oeruploader_tugraz_queue', [], 'id');
        $this->assertEquals($course2->id, reset($queue)->courseid);
        $record = $DB->get_record('oeruploader_tugraz_queue', ['courseid' => $course1->id]);
        $this->assertEquals(2, $record->attempt);
        queue::requeue($course3->id);
        $queue = $DB->get_records('oeruploader_tugraz_queue', [], 'id');
        $this->assertEquals($course2->id, reset($queue)->courseid);
        $record = $DB->get_record('oeruploader_tugraz_queue', ['courseid' => $course3->id]);
        $this->assertEquals(1, $record->attempt);
        queue::requeue($course2->id);
        $queue = $DB->get_records('oeruploader_tugraz_queue', [], 'id');
        $this->assertEquals($course1->id, reset($queue)->courseid);
    }

    /**
     * Test filesize calculation
     *
     * @return void
     * @throws \dml_exception
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     */
    public function test_filesize_calculation() {
        $this->resetAfterTest(true);

        $this->setAdminUser();
        $helper = new testcourse();
        $course = $helper->generate_testcourse($this->getDataGenerator());
        $helper->sync_course_info($course->id);
        // No file has been added for upload - so filesize remains 0;.
        $this->assertEquals(0, queue::calculate_upload_filesize($course->id));
        // Release a file.
        $size = $helper->set_files_to($course->id, 1, true);
        $snapshot = new snapshot($course->id);
        $snapshot->create_snapshot_of_course_files();
        // Now a file has been added to be uploaded by oer tool, so some filesize should be estimated.
        $this->assertEquals($size, queue::calculate_upload_filesize($course->id));
        // Release multiple file.
        $size = $helper->set_files_to($course->id, 4, true);
        $snapshot->create_snapshot_of_course_files();
        // Now a file has been added to be uploaded by oer tool, so some filesize should be estimated.
        $this->assertEquals($size, queue::calculate_upload_filesize($course->id));
    }
    // TODO: add more tests (look into old testfile).
}
