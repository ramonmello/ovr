<?php   
/*
This script queries all the urls that are being passed to the newly created page in order to know
what is the average score each video has, to be able to properly show it.
It is calles by ajax in the ajaxsubmit.php file.

@author Miguel Alvim, Ramon Oliveira
@date 14/06/2018
*/


require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

$urls = json_decode($_POST['urls'], true);//Each video url
$totUrls = count($urls);//the amount of urls added

/**
 * Assembles a string in the format: "url = ... OR url = ... OR ..."
 */
$query = "";
for($i=0;$i<$totUrls;++$i){
  $query .= "url = \"".$urls[$i]."\"";
  if($i<$totUrls-1){
    $query .= " OR ";
  }
}

/**
 * Query 1 -> mdl_estrela
 * Queries all passed urls and their star rating average scores and total number of votes
 * $query = string with all the urls
 */
$queryResult = $DB->get_records_select('estrela', $query, null, null, 'url, star, votos');
if ($queryResult === FALSE) {
  echo "(1)Erro ao consultar a base de dados:\n".$conn->error;
  exit(1);
}	
if ($queryResult != null) {
  $allResults = Array();
  foreach($queryResult as $row){
    //Creates an array with all the results from Query 1
    array_push($allResults,($row));
  }
  //Transforms the array into a JSON
  echo JSON_encode($allResults);
}else{
  echo "{}";
  }
  ?>