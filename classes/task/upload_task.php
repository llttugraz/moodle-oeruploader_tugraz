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

namespace oeruploader_tugraz\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;
use local_oer\helper\activecourse;
use local_oer\helper\snapshothelper;
use local_oer\logger;
use oeruploader_tugraz\queue;
use local_oer\zipper;
use oeruploader_tugraz\uploader;

require_once($CFG->libdir . '/clilib.php');

/**
 * Class upload_task
 */
class upload_task extends scheduled_task {
    /**
     * Get name
     *
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('uploadtask', 'oeruploader_tugraz');
    }

    /**
     * Execute task
     *
     * @throws \dml_exception
     */
    public function execute() {
        $lastupload     = get_config('oeruploader_tugraz', 'lastupload');
        $latestsnapshot = snapshothelper::get_latest_snapshot_timestamp();
        if ($latestsnapshot->timecreated < $lastupload) {
            return;
        }

        $inprogress = get_config('oeruploader_tugraz', 'uploadinprogress');

        // A new upload process has started, so refresh the queue.
        // Files that are present in courses need a metadata time information update.
        if ($inprogress == 0) {
            logger::add(0, logger::LOGSUCCESS, 'Upload started');
            $courses = activecourse::get_list_of_courses(true);
            foreach ($courses as $course) {
                queue::update($course->courseid);
            }
        }

        // Run task for five minutes - then wait for next cron run to continue.
        // This process should not block other scheduled tasks.
        $runtime = time() + 60 * 5;
        while (queue::get_queue_size() > 0) {
            if (time() > $runtime) {
                // The only place where in progress can be set to true.
                // So when a timewindow is reached and upload starts this can be triggered.
                // Time window will be set to next semester, but the queue will uploaded until its finished.
                set_config('uploadinprogress', 1, 'oeruploader_tugraz');
                break;
            }

            $course   = queue::get_next();
            $uploader = new uploader($course->course);

            try {
                $zipper = new zipper();
                list($packages, $info) = $zipper->separate_files_to_packages($course->course);
                foreach ($packages as $key => $package) {
                    $file = $zipper->compress_file_package($course->course, $package);
                    $uploader->upload($file, $package, $info[$key], $info['general']);
                }
                queue::delete($course->course);
            } catch (\Exception $exception) {
                logger::add($course->course, logger::LOGERROR,
                            'Upload failed, course will be requeued: ' . $exception->getMessage());
                queue::requeue($course->course);
            }
        }

        if (queue::get_queue_size() == 0) {
            // Upload is finished.
            logger::add(0, logger::LOGSUCCESS, 'Upload finished');
            set_config('lastupload', time(), 'oeruploader_tugraz');
            set_config('uploadinprogress', 0, 'oeruploader_tugraz');
        }
    }
}
