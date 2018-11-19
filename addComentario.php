<?php
/*
@author Ramon Oliveira
@date 19/11/2018
*/


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

$url = $_POST['url'];//Url de cada video
$comentario = $_POST['comentario'];

$query= "select * from {estrela} where url=\"".$url."\"";
$queryResult = $DB->get_record_sql($query, $params);

$record = new stdClass();
$record->estrelaid = $queryResult->id;
$record->comentario = $comentario;
$record->timemodified = time();

$insert =-1;
$insert = $DB->insert_record('comentarios', $record, true);
if ($insert === -1) {
	echo "(1)Error on updating the database:\n";
	exit(1);
}