<?php
/*
@author Ramon Oliveira
@date 02/01/2019
*/

require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB, $USER;

$url = $_POST['url'];//Url de cada video
$event = $_POST['event'];

$user = $USER->id;

$queryResult = $DB->get_record_select("likes_ovr", "url=\"".$url."\" AND userid=".$user);

if($queryResult != NULL){
    if($event == 'like'){
        $sql = "UPDATE {likes_ovr} set ovrlike=".(1)." WHERE url=\"".$url."\" AND userid=".$user;
    }
    else {
        $sql = "UPDATE {likes_ovr} set ovrlike=".(-1)." WHERE url=\"".$url."\" AND userid=".$user;
    }
    
    if ($DB->execute($sql) === true) {
        
    } else {
        echo "(1)Error on updating the database:\n";
        exit(1);
    }
}
else {
    
    $record = new stdClass();
    $record->userid = $user;
    $record->url = $url;
    if($event == 'like') {
        $record->ovrlike = 1;
    }
    else {
        $record->ovrlike = -1;
    }
    
    $insert = -1;
    $insert = $DB->insert_record('likes_ovr', $record, true);
    if ($insert === -1) {
        echo "(2)Error on updating the database:\n";
        exit(2);
    }
    
}