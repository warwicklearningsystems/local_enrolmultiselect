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
 * Code to search for users in response to an ajax call from a user selector.
 *
 * @package core_user
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');

use \local_enrolmultiselect\type\settings\department;
use \local_enrolmultiselect\type\settings\potential_department;
use \local_enrolmultiselect\togglestore;


$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/enrolmultiselect/ajaxtoggle.php');

echo $OUTPUT->header();

// Check access.
require_login();
require_sesskey();

// Get and validate the selectorid parameter.
$currentSelectorHash = required_param('current_selectorid', PARAM_ALPHANUM);
$potentialSelectorHash = required_param('potential_selectorid', PARAM_ALPHANUM);
if (!isset($USER->userselectors[$currentSelectorHash]) || !isset($USER->userselectors[$potentialSelectorHash])) {
    try{
        print_error('unknownuserselector');
    }catch(Exception $e){
        exit( json_encode( [ 'error' => $e->getMessage() ] ) );
    }
}

if( togglestore::TOGGLE_POTENTIAL_MAP_NAME == $_POST [ 'toggle_type' ] ){
    togglestore::set( $currentSelectorHash, togglestore::TOGGLE_ITEMS_TO_EXCLUDE_MAP_NAME, $_POST[ 'selected_options' ] );
    togglestore::set( $potentialSelectorHash, togglestore::TOGGLE_ITEMS_TO_INCLUDE_MAP_NAME, $_POST[ 'selected_options' ] );
}

if( togglestore::TOGGLE_CURRENT_MAP_NAME == $_POST[ 'toggle_type'] ){
    togglestore::set( $potentialSelectorHash, togglestore::TOGGLE_ITEMS_TO_EXCLUDE_MAP_NAME, $_POST[ 'selected_options' ] );
    togglestore::set( $currentSelectorHash, togglestore::TOGGLE_ITEMS_TO_INCLUDE_MAP_NAME, $_POST[ 'selected_options' ] );
}
echo json_encode( [ 'success' => 'true'] );