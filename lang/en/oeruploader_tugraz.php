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
 * Graz University of Technology specific subplugin for Open Educational Resources Plugin.
 *
 * @package    oeruploader_tugraz
 * @author     Christian Ortner <christian.ortner@tugraz.at>
 * @copyright  2021 Educational Technologies, Graz, University of Technology
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']             = 'OER File(s) to repository uploader - TU Graz';
$string['upload_url']             = 'URL';
$string['upload_url_description'] = 'Endpoint of the repository API, where the files are uploaded to.';
$string['token_name']             = 'Param';
$string['token_name_description'] = 'Name of token parameter';
$string['token']                  = 'Token';
$string['token_description']      = 'Enter token for uploading';
$string['queue_oer']              = 'OER Queue';
$string['uploadinprogress']       = 'The OER files are currently being uploaded to the repository.';
$string['trylater']               = 'This may take some time, please try again later';
$string['uploadtask']             = 'Upload files to repository';
$string['queueheading']           = 'List of courses enqueued for upload.';
$string['queuedefinition']        = 'These courses will be uploaded on next upload window.';
$string['queuesize']              = 'At the moment there are {$a} courses enqueued.';
$string['queueposition']          = 'Position';
$string['attempt']                = 'Attempt';
$string['fullsize']               = 'The size of files released for upload in all ' .
                                    'enqueued courses is {$a}. ' .
                                    'This is an uncompressed estimation.';
