<?php   

require_once('../../config.php');

defined('MOODLE_INTERNAL') || die();
global $DB;

//Tranformando os valores passados por post(formatados em JSON) em arrays padrão PHP
$url =$_POST['url'];//Url de cada video
$stars = $_POST['stars'];//Stars de cada video

//criar função para fazer essa consulta
$query= "select * from mdl_estrela where url=\"".$url."\"";
$params['url'] = $url;
$queryResult = $DB->get_records_sql($query, $params);
$userid = $USER->id;

if($queryResult!=null){
	//echo "Alguém já votou nesse vídeo\n";
	$row = array($queryResult);
	$query2= "SELECT * from mdl_estrela_registro where estrelaid = ".$row['id']." AND userid = ".$userid;
	$queryResult2 = $DB->get_records_sql($query2,$params);
	if($queryResult2->num_rows === 0){
		//echo "O usuario logado ainda não votou nesse vídeo\n";
		$starAux = $row['star'];
		$votosAux  = $row['votos'];
		$starAux = ($starAux*$votosAux+$stars)/($votosAux+1);
		$queryUpdate = "UPDATE mdl_estrela set star = ".$starAux.",votos=".($votosAux+1)." WHERE url=\"".$url."\"";
		if ($DB->get_records_sql($queryUpdate, $params) === FALSE) {
			echo "(1)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(1);
		}
		$sql2 = "INSERT INTO mdl_estrela_registro (estrelaid, userid, voto) VALUES (".$row['id'].", ".$userid.", ".$stars." )";
		if ($DB->get_records_sql($sql2, $params) === FALSE) {
			echo "\n(3)Erro ao atualizar a base de dados:\n ".$conn->error;
			exit(3);
		}
	}
}
else{
	//echo "Primeiro voto no vídeo";
	$sql = "INSERT INTO mdl_estrela(url,star,votos) VALUES (\"".$url."\",".$stars.",".(1).")";
	if ($DB->get_records_sql($sql, $params) === FALSE) {
		echo "(2)Erro ao atualizar a base de dados:\n".$conn->error;
		exit(2);
	}
	$query= "select * from mdl_estrela where url=\"".$url."\"";
	$queryResult = $DB->get_records_sql($query, $params);
	$row = $queryResult->fetch_assoc();
	$sql2 = "INSERT INTO mdl_estrela_registro (estrelaid, userid, voto) VALUES (".$row['id'].", ".$userid.", ".$stars." )";
	if ($DB->get_records_sql($sql2, $params) === FALSE) {
		echo "\n(3)Erro ao atualizar a base de dados:\n ".$conn->error;
		exit(3);
	}
}
echo "sucesso";
?>
