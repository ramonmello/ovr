<?php   
/*
	Script resposible for updating and creating the necessary entries in the DB for the star rating system
	@author Miguel Alvim, Ramon Oliveira
*/

require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();
global $DB;

//Transforming the values passed by POST(In JSON) to PHP arrays
$url =$_POST['url'];//Url de cada video
$stars = $_POST['stars'];//Stars de cada video

/**
 * Query 1 -> mdl_estrela 
 * Makes a search on the mdl_estrela table in for the video URL
 * $url = video url to be searched
 */
$queryResult = $DB->get_record_select("estrela", "url=\"".$url."\"");
$userid = $USER->id;

/**
 * If the query returned a non null value, then the video has already been inserted in the star rating abase and has a rating
 */

if($queryResult != NULL){
/**
 * Query 2 -> mdl_estrela_registro
 * Looks for a result in the table that shares the video id and the user id.
 * In case of NULL, the logged user has not voted on that video.
 * $queryResult->id = table ID for the star rating related to the video that is been queried
 * $userid = logged user id
 */
	$queryResult2 = $DB->get_record_select('estrela_registro',"estrelaid = ".$queryResult->id." AND userid = ".$userid);
	if($queryResult2 == NULL){
		$starAux = $queryResult->star;
		$votosAux  = $queryResult->votos;
		$starAux = ($starAux*$votosAux+$stars)/($votosAux+1);

		/**
		 * Query 3 -> mdl_estrela
		 * Updates the average value of the votes in the mdl_estrela table
		 * $starAux = Updated average star rating score 
		 */
		$queryUpdate = "UPDATE {estrela} set star = ".$starAux.",votos=".($votosAux+1)." WHERE url=\"".$url."\"";
		if ($DB->execute($queryUpdate) === FALSE) {
			echo "(3)Error on updating the database:\n".$conn->error;
			exit(3);
		}

		/**
		 * Query 4 -> mdl_estrela_registro
		 * Registers the vote of the user on the video
		 * $queryResult->id = video id on the mdl_estrela table
		 * $userid = logged user id
		 * $stars = number of stars [0,5] that the user rated the video
		 */
		$sql2 = "INSERT INTO {estrela_registro} (estrelaid, userid, voto) VALUES (".$queryResult->id.", ".$userid.", ".$stars." )";
		if ($DB->execute($sql2) === FALSE) {
			echo "\n(4)Error on updating the database:\n ".$conn->error;
			exit(4);
		}
	}
}
/**
 * If the video has no evaluation, this part is executed;
 * We create the necessary entries on the database and so on.
 */
else{
	/**
	 * Query 5 -> mdl_estrela
	 * Inserts a new entry on the mdl_estrela table, containing:
	 * $url = video url
	 * $stars = first evaluation, created by the first user to rate the video
	 * (1) = total of votes on the video (quite obvious value; we maintain the total of votes to be able to recalculate the score after each vote)
	 */
	$sql = "INSERT INTO {estrela}(url,star,votos) VALUES (\"".$url."\",".$stars.",".(1).")";
	if ($DB->execute($sql) === FALSE) {
		echo "(5)Error on updating the database:\n".$conn->error;
		exit(5);
	}
	$query= "select * from {estrela} where url=\"".$url."\"";
	$queryResult = $DB->get_record_sql($query, $params);

	/**
	 * Query 6 -> mdl_estrela_registro
	 * Saves the vote of the logged user; this table also allows us to see if a user has already voted
	 * $queryResult->id = video id on the tabela mdl_estrela table
	 * $userid = logged user id
	 * $stars = rating that was given by the user
	 */
	$sql2 = "INSERT INTO {estrela_registro} (estrelaid, userid, voto) VALUES (".$queryResult->id.", ".$userid.", ".$stars." )";
	if ($DB->execute($sql2) === FALSE) {
		echo "\n(6)Error on updating the database:\n ".$conn->error;
		exit(6);
	}
}
echo "success";
?>
