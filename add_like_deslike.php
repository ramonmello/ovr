<?php
/*
@author Ramon Oliveira
@date 02/01/2019
*/


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

$url = $_POST['url'];//Url de cada video
$event = $_POST['event'];

//$userid = $USER->id;

//$queryResult = $DB->get_record_select("likes_ovr", "url=\"".$url."\" AND userid=".$userid);

//if($queryResult == NULL AND $event == 'like'){
    
    $record = new stdClass();
    $record->userid = $userid;
    $record->url = $url;
    $record->like = 1;
    
    $insert = -1;
    $insert = $DB->insert_record('likes_ovr', $record, true);
    if ($insert === -1) {
        echo "(1)Error on updating the database:\n";
        exit(1);
    }
//}