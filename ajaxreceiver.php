<?php
	// inclusão API, para consulta no BD BAVi
	require_once('../../config.php');
	defined('MOODLE_INTERNAL') || die();
    include_once('baviAPI_02.php');

	global $COURSE, $DB, $PAGE;
	$query= new BAViQuery;

	$cfgovrmodules = get_config('block_ovr');
	$query->put("content", $_GET['keyword']);
	$query->put("idservice", $cfgovrmodules->base);
	$query->put("op", "mediaresource");

	$api= new BAViClient($cfgovrmodules->protocol."://".$cfgovrmodules->url.":".$cfgovrmodules->port."/","ServidorRest");
	$resultado =  $api->query($query,1);
	$arrayResult =  json_decode($resultado ,true);
	$retorno = array();

	// Criação de um lupe para tratar os dados da consulta
		foreach($arrayResult["results"]["bindings"] as $key => $value){
			$recebe = $arrayResult["results"]["bindings"][$key]["link"]["value"];
			if(! in_array($recebe,$retorno,false))
				array_push ($retorno, $recebe);
	 	}
	echo json_encode($retorno);
?>
