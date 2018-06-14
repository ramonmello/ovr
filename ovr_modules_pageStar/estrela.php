<?php   
/*
	Script de criação das URLs e Labels dos vídeos obtidos pela busca no servidor BAVi.
	@author Miguel Alvim, Marluce Ap. Vitor
	@version ALPHA
	@date 19/08/2018
*/

//Valores de acesso ao banco e cache do moodle<?php   
/*
	Script de criação das URLs e Labels dos vídeos obtidos pela busca no servidor BAVi.
	@author Miguel Alvim, Marluce Ap. Vitor
	@version ALPHA
	@date 19/08/2018
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
   		echo "Erro ao atualizar a base de dados: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    	exit(0);
	}
//Tranformando os valores passados por post(formatados em JSON) em arrays padrão PHP
	$url =$_POST['url'];//Url de cada video
	$stars = $_POST['stars'];//Stars de cada video
	

	$query= "select star,votos from mdl_estrela where url=\"".$url."\"";
	$queryResult = $conn->query($query);
	if ($queryResult->num_rows >0) {
		$row = $queryResult->fetch_assoc();
		$starAux = $row['star'];
		$votosAux  = $row['votos'];
		$starAux = ($starAux*$votosAux+$stars)/($votosAux+1);
		$queryUpdate = "UPDATE mdl_estrela set star = ".$starAux.",votos=".($votosAux+1)." WHERE url=\"".$url."\"";
		if ($conn->query($queryUpdate) === FALSE) {
			echo "(1)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(1);
		}
    }else{
		$sql = "INSERT INTO mdl_estrela(url,star,votos) VALUES (\"".$url."\",".$stars.",".(1).")";
		if ($conn->query($sql) === FALSE) {
			echo "(2)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(2);
		}
	}
    echo "sucesso";	
?>
