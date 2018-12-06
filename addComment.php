<?php
/*
@author Ramon Oliveira
@date 19/11/2018
 */


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

$url = "http://138.121.71.4/material_1/regiane.mp4";//Url de cada video
$comentario = "Comentário para testes";

$record = new stdClass();
$record->url = $url;
$record->comment = $comentario;

$insert = -1;
$insert = $DB->insert_record('comments_ovr', $record, true);
if ($insert === -1) {
	echo "(1)Error on updating the database:\n";
	exit(1);
}