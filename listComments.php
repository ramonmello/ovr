<?php
/*
@author Ramon Oliveira
@date 19/11/2018
 */


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

$url = $_POST['url'];

$comments = $DB->get_record_sql('SELECT comment FROM {comments_ovr} WHERE url = ?', array($url));

if ($comments == null) {
  echo "(listComments)Error on consulting the database:\n" . $conn->error;
  exit(1);
}

echo json_encode($comments);