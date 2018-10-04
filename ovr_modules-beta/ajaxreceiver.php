<?php
	// inclusão API, para consulta no BD BAVi
	include_once('baviAPI_02.php');
	$query= new BAViQuery;
	$query->put("content", $_GET['keyword']);
	$query->put("idservice", "TesteDev");
	$query->put("op", "mediaresource");

	$api= new BAViClient("http://138.121.71.4:8082/","ServidorRest");
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
