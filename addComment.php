<?php
/*
@author Ramon Oliveira
@date 19/11/2018
 */


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

$url = $_POST['url'];//Url de cada video
$comment = $_POST['comment'];

$record = new stdClass();
$record->url = $url;
$record->comment = $comment;

$insert = -1;
$insert = $DB->insert_record('comments_ovr', $record, true);
if ($insert === -1) {
	echo "(1)Error on updating the database:\n";
	exit(1);
}