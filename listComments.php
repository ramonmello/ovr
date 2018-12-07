<?php
/*
@author Ramon Oliveira
@date 19/11/2018
 */


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

$url = $_POST['url'];

$comments = $DB->get_records_sql('SELECT comment, id FROM {comments_ovr} WHERE url = ?', array($url));

if ($comments == null) {
  echo "(listComments)Error on consulting the database:\n" . $conn->error;
  exit(1);
}

if ($comments != null) {
  $allResults = array();
  foreach ($comments as $comment) {
    //Creates an array with all the results from Query 1
    array_push($allResults, ($comment));
  }
  //Transforms the array into a JSON
  echo JSON_encode($allResults);
} else {
  echo "{}";
}