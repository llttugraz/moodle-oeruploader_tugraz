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
 * @copyright  2017 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace oeruploader_tugraz;

use local_oer\helper\filehelper;
use local_oer\logger;

/**
 * Class uploader
 */
class uploader {
    /**
     * @var int
     */
    private $courseid = 0;

    /**
     * Constructor
     *
     * @param int $courseid
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Upload file to repository
     *
     * @param string $file
     * @param array $info
     * @param array $general
     * @return \stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function upload($file, $info, $general) {
        $stats = new \stdClass();

        $url = get_config('oeruploader_tugraz', 'upload_url');
        if (empty($url)) {
            throw new \Exception('URL config field empty, cannot upload without URL');
        }
        $tokenparam = get_config('oeruploader_tugraz', 'token_name');
        $token      = get_config('oeruploader_tugraz', 'token');

        $curl = new \curl();

        $cfile = new \CURLFile($file, 'application/zip', 'oer_course_file');

        $params   = [
                'package'   => $cfile,
                $tokenparam => $token,
        ];
        $settings = [
                'CURLOPT_USERAGENT' => 'TC/OER',
                'FAILONERROR'       => true,
        ];
        $curl->setopt($settings);
        $result = $curl->post($url, $params);
        $error  = $curl->get_errno();
        if ($error) {
            logger::add($this->courseid, logger::LOGERROR,
                        'Upload failed. Package ' . $info['number'] . '/' . $general['packages'] . 'Errno: ' . $error .
                        ' Message: ' . $result);
        } else {
            logger::add($this->courseid, logger::LOGSUCCESS,
                        'Upload successful. Package ' . $info['number'] . '/' . $general['packages']
                        . '. ' . $info['files'] . ' files with ' . filehelper::get_readable_filesize($info['filesize']) .
                        ' uploaded');
        }

        return $stats;
    }
}
