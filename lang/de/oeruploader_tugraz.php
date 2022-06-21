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

$string['pluginname']             = 'OER Datei(en) hochladen zum Repositorium - TU Graz';
$string['upload_url']             = 'URL';
$string['upload_url_description'] = 'Endpunkt der Repositoriums API, wohin die Dateien hochgeladen werden.';
$string['token_name']             = 'Parameter';
$string['token_name_description'] = 'Bezeichnung des Parameters für den Token';
$string['token']                  = 'Token';
$string['token_description']      = 'Token zum Hochladen der Dateien.';
$string['queue_oer']              = 'OER Warteschlange';
$string['uploadinprogress']       = 'Derzeit werden die OER Dateien zum Repositorium hochgeladen.';
$string['trylater']               = 'Dies kann einige Zeit in Anspruch nehmen, bitte versuchen Sie es später noch einmal.';
$string['uploadtask']             = 'Dateien zum Repositorium hochladen';
$string['queueheading']           = 'Liste von Kursen, welche zum Hochladen vorgesehen sind.';
$string['queuedefinition']        = 'OER Dateien dieser Kurse werden zum nächsten Hochladezeitpunkt hochgeladen.';
$string['queuesize']              = 'Derzeit befinden sich {$a} Kurse in der Warteliste.';
$string['queueposition']          = 'Position';
$string['attempt']                = 'Versuch #';
$string['fullsize']               = 'Die Grösse aller zum Hochladen vorgesehenen Dateien ist {$a}. ' .
                                    'Grösse basiert auf den unkomprimierten Dateigrössen.';
$string['privacy:metadata']       = 'Dieses Plugin speichert keine persönlichen Daten.';
