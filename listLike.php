<?php
/*
@author Ramon Oliveira
@date 03/01/2019
*/


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

$url = $_POST['url'];
$user = $USER->id;

$queryResult = $DB->get_record_select("likes_ovr", "url=\"".$url."\" AND userid = ".$user);

if($queryResult != NULL){
    echo $queryResult->ovrlike;
}
else {
    echo "";
}