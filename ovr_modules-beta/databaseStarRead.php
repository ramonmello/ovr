<?php   
/*
	Script de criação das URLs e Labels dos vídeos obtidos pela busca no servidor BAVi.
	@author Marluce Vitor && Miguel Alvim
	@version ALPHA
	@date 04/10/2018
*/

require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();

global $DB;
	
	$urls = json_decode($_POST['urls'],true);//Urls de cada video
	$totUrls = count($urls);
	
	$query = "SELECT e.url,e.star,e.votos FROM mdl_estrela as e WHERE ";
	
	for($i=0;$i<$totUrls;++$i){
		$query .= "url = \"".$urls[$i]."\"";
		if($i<$totUrls-1){
			$query .= " OR ";
		}
	}


	$users = array_values($DB->get_records_sql($query, $params));

	if ($users=== FALSE) {
	 	echo "(1)Erro ao consultar a base de dados:\n".$DB->error;

	 	exit(1);
	 }else{

	 echo JSON_encode($users);
	 }

?>
