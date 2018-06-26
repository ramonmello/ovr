<?php   
/*
	Script de criação das URLs e Labels dos vídeos obtidos pela busca no servidor BAVi.
	@author Miguel Alvim
	@version ALPHA
	@date 14/06/2018
*/
//Valores de acesso ao banco e cache do moodle
	$dbType = 'mysql';
	$dbHost = 'localhost';
	$dbName = 'moodle';
	$dbUser = 'root';
	$dbPass = 'root';
	$dbPort = '3306';
	$dbChar = 'UTF8';

	$conn = $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName,$dbPort);
	if ($mysqli->connect_errno) {
   		echo "(0)Erro ao acessar a base de dados: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    	exit(0);
	}
	$urls = json_decode($_POST['urls'],true);//Urls de cada video
	$totUrls = count($urls);
	
	$query = "SELECT url,star,votos FROM mdl_estrela WHERE ";
	
	for($i=0;$i<$totUrls;++$i){
		$query .= "url = \"".$urls[$i]."\"";
		if($i<$totUrls-1){
			$query .= " OR ";
		}
	}
	$queryResult = $conn->query($query);
	if ($queryResult === FALSE) {
		echo "(1)Erro ao consultar a base de dados:\n".$conn->error;
		exit(1);
	}	
	if ($queryResult->num_rows >0) {
		$allResults = Array();
		while($row = $queryResult->fetch_assoc()){
			array_push($allResults,($row));
		}
		echo JSON_encode($allResults);
	}else{
		echo "{}";
	}
?>
