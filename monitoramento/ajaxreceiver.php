<?php
	include_once('baviAPI_02.php');
	$query= new BAViQuery;
	$query->put("content", $_GET['keyword']);
	$query->put("idservice", "Teste");
	$query->put("op", "mediaresource");

	$api= new BAViClient("http://138.121.71.4:8082/","ServidorRest");
	$resultado =  $api->query($query,1);
	$arrayResult =  json_decode($resultado ,true);
	$retorno = "";
		foreach($arrayResult["results"]["bindings"] as $key => $value){
			$retorno = $retorno." ". $arrayResult["results"]["bindings"][$key]["link"]["value"];
	 	}
	echo $retorno;
?>