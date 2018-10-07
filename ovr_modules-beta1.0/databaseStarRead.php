<?php   
/*
	Script de criação das URLs e Labels dos vídeos obtidos pela busca no servidor BAVi.
	@author Miguel Alvim
	@version ALPHA
	@date 14/06/2018
*/
require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

//Valores de acesso ao banco e cache do moodle
$urls = json_decode($_POST['urls'],true);//Urls de cada video
$totUrls = count($urls);


//$query = "SELECT url, star, votos FROM mdl_estrela WHERE ";
	$query = "";
for($i=0;$i<$totUrls;++$i){
  $query .= "url = \"".$urls[$i]."\"";
  if($i<$totUrls-1){
    $query .= " OR ";
  }
}
$queryResult = $DB->get_records_select('estrela', $query, null, null, 'url, star, votos');
if ($queryResult === FALSE) {
  echo "(1)Erro ao consultar a base de dados:\n".$conn->error;
  exit(1);
}	
if ($queryResult != null) {
  $allResults = Array();
  foreach($queryResult as $row){
    array_push($allResults,($row));
  }
  echo JSON_encode($allResults);
}else{
  echo "{}";
}
?>

