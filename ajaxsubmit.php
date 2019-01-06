<?php   
/*
Script that creates the page activities with the videos
@author Miguel Alvim, Marluce Ap. Vitor
@date 19/08/2018
*/
require_once('../../config.php');
defined('MOODLE_INTERNAL') || die();

global $DB;

$cfgovrmodules = get_config('block_ovr');
$moodleData_Path = $cfgovrmodules->cache;
//Validanting the values passed by POST; ALL must be valid
/*
If any is incorrect, the script ends with a -1 error
*/
if (!isset($_POST["cid"]) || !isset($_POST["rotName"]) || !isset($_POST["section"]) || !isset($_POST["names"]) || !isset($_POST["urls"]) /*|| empty($_POST["cid"]) || empty($_POST["rotName"]) || empty($_POST["section"]) || empty($_POST["names"]) || empty($_POST["urls"])*/ ) {
	echo "(-1)Um ou mais valores não foram corretamente passados (via POST)";
	exit(-1);
}

//Transforming values from post(JSON) to PHP arrays
$urls = json_decode($_POST['urls'], true);//Urls for each video
$names = json_decode($_POST['names'], true);//Names of each video
$searchText = $_POST['searchText'];//Search query

/*####################################################Adding the Page Activity####################################################*/
//$Content has the HTML and Javascript code for the pages; it includes and entire set of scripts for the proper work of the 
//star rating system;
//TODO: Transfer the scripts to an separated file for easier future modificantions
$content = "<script>
var stars = Array();";
for ($i = 0; $i < count($urls); ++$i) {
	$content = $content . "stars[" . $i . "]=0;";
}
$jsonData = "";
for ($i = 0; $i < count($urls); ++$i) {
	$jsonData .= $i . ":" . "'" . $urls[$i] . "'";
	if ($i < (count($urls) - 1))
	$jsonData .= ",";
}

$content = $content . "
getVideoRating({" . $jsonData . "});
var starColor = 'yellow';
function setStar(i,s){
	stars[s] = i;
	starColor = 'yellow';
}
function colorStars(i,s,color){
	var j=1;
	for(;j<=5;++j){
		if(j<=i)
		document.getElementById(s+'STAR'+j).style.fill=color;
		else
		document.getElementById(s+'STAR'+j).style.fill='grey';
	}
}
function updateVideoRating(videoID, s){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			getVideoRating({0:document.getElementById(videoID).value});
		}
	};      	  
	xhttp.open('POST','../../blocks/ovr/estrela.php', true);
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send('url='+document.getElementById(videoID).value+'&stars='+s);
}

function getVideoRating(videosULRs){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			var json = JSON.parse(this.responseText);
			if(json.length>0){
				for(var i=0;i<json.length;++i){
					for(var j=0;j<stars.length;++j){
						if(document.getElementById(j).value == json[i].url){								
							stars[j] = Math.floor(parseFloat(json[i].star)+0.5);									
							colorStars((stars[j]),j,starColor);
							document.getElementById((j+'STARN')).value = parseFloat(json[i].star).toFixed(2);
						}
					}
				}
			}
		}
	};      	  
	xhttp.open('POST','../../blocks/ovr/databaseStarRead.php', true);
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send('urls='+JSON.stringify(videosULRs));
}
</script>";
$content .= "
<style>
#commentblock {
	text-align: center;
}

form#comment textarea {
	width: 400px;
	height: 50px;
	border: 3px solid #d8e5ed;
	border-radius: 5px;
	font-size: 16px;
	resize: none;
}

#comment-list {
	text-align: -webkit-center;
	list-style: none;
	margin: 0;
	padding: 0;
	font-family: sans-serif;
}

.li-comment {
	text-align: justify;
	padding: 20px 20px 0;
	margin: 20px 0 0;
	border-top: 1px solid #d8e5ed;
	width: 400px;
}
<style>
#like {
	fill: #9999;
	-webkit-transition-duration: 0.4s; /* Safari */
	transition-duration: 0.4s;
}
#like:hover {
	fill: blue;
}
</style>
</style>
";
$content .= "<div id='commentblock' class='like'>";
for ($i = 0; $i < count($urls); ++$i) {
	$content = $content . "<p>"
	. "<div style=\"text-align: center;\">"
	. "<video width=480px height=360px controls=\"true\" src=\"" . $urls[$i] . "\">" . $urls[$i] . "</video>"
	. "<input class= 'url' type='hidden' id=" . $i . " value=" . $urls[$i] . " disabled>"
	. $names[$i]
	. "</p>";
	
	//curtir
	$content = $content."<svg id='like' class='" . $i ."' width='30' height='30' aling='ligh' viewBox='0 0 478.2 478.2' style='enable-background:new 0 0 478.2 478.2;'>
	<style>
	#like {
		fill: #9999;
		-webkit-transition-duration: 0.3s; /* Safari */
		transition-duration: 0.3s;
	}
	#like:hover {
		fill: #33ccff;
	}
	</style>
	<path d='M441.11,252.677c10.468-11.99,15.704-26.169,15.704-42.54c0-14.846-5.432-27.692-16.259-38.547    c-10.849-10.854-23.695-16.278-38.541-16.278h-79.082c0.76-2.664,1.522-4.948,2.282-6.851c0.753-1.903,1.811-3.999,3.138-6.283    c1.328-2.285,2.283-3.999,2.852-5.139c3.425-6.468,6.047-11.801,7.857-15.985c1.807-4.192,3.606-9.9,5.42-17.133    c1.811-7.229,2.711-14.465,2.711-21.698c0-4.566-0.055-8.281-0.145-11.134c-0.089-2.855-0.574-7.139-1.423-12.85    c-0.862-5.708-2.006-10.467-3.43-14.272c-1.43-3.806-3.716-8.092-6.851-12.847c-3.142-4.764-6.947-8.613-11.424-11.565    c-4.476-2.95-10.184-5.424-17.131-7.421c-6.954-1.999-14.801-2.998-23.562-2.998c-4.948,0-9.227,1.809-12.847,5.426    c-3.806,3.806-7.047,8.564-9.709,14.272c-2.666,5.711-4.523,10.66-5.571,14.849c-1.047,4.187-2.238,9.994-3.565,17.415    c-1.719,7.998-2.998,13.752-3.86,17.273c-0.855,3.521-2.525,8.136-4.997,13.845c-2.477,5.713-5.424,10.278-8.851,13.706    c-6.28,6.28-15.891,17.701-28.837,34.259c-9.329,12.18-18.94,23.695-28.837,34.545c-9.899,10.852-17.131,16.466-21.698,16.847    c-4.755,0.38-8.848,2.331-12.275,5.854c-3.427,3.521-5.14,7.662-5.14,12.419v183.01c0,4.949,1.807,9.182,5.424,12.703    c3.615,3.525,7.898,5.38,12.847,5.571c6.661,0.191,21.698,4.374,45.111,12.566c14.654,4.941,26.12,8.706,34.4,11.272    c8.278,2.566,19.849,5.328,34.684,8.282c14.849,2.949,28.551,4.428,41.11,4.428h4.855h21.7h10.276    c25.321-0.38,44.061-7.806,56.247-22.268c11.036-13.135,15.697-30.361,13.99-51.679c7.422-7.042,12.565-15.984,15.416-26.836    c3.231-11.604,3.231-22.74,0-33.397c8.754-11.611,12.847-24.649,12.272-39.115C445.395,268.286,443.971,261.055,441.11,252.677z
	M100.5,191.864H18.276c-4.952,0-9.235,1.809-12.851,5.426C1.809,200.905,0,205.188,0,210.137v182.732    c0,4.942,1.809,9.227,5.426,12.847c3.619,3.611,7.902,5.421,12.851,5.421H100.5c4.948,0,9.229-1.81,12.847-5.421    c3.616-3.62,5.424-7.904,5.424-12.847V210.137c0-4.949-1.809-9.231-5.424-12.847C109.73,193.672,105.449,191.864,100.5,191.864z     M67.665,369.308c-3.616,3.521-7.898,5.281-12.847,5.281c-5.14,0-9.471-1.76-12.99-5.281c-3.521-3.521-5.281-7.85-5.281-12.99    c0-4.948,1.759-9.232,5.281-12.847c3.52-3.617,7.85-5.428,12.99-5.428c4.949,0,9.231,1.811,12.847,5.428    c3.617,3.614,5.426,7.898,5.426,12.847C73.091,361.458,71.286,365.786,67.665,369.308z'/></svg>&#160&#160&#160&#160&#160&#160&#160&#160&#160&#160&#160";
	
	for ($j = 1; $j <= 5; ++$j) {
		$content = $content . "<svg width='20' height='20'>
		<polygon points=\"10,1 4,19.8 19,7.8 1,7.8 16,19.8\" id='" . $i . "STAR" . $j . "' style=\"fill:grey;\" 
		onmouseover=\"colorStars(" . $j . "," . $i . ",'yellow');this.style.fill='yellow';\" onmouseout=\"colorStars(stars[" . $i . "]," . $i . ",starColor);\" onClick=\"setStar(" . $j . "," . $i . ");updateVideoRating(" . $i . "," . $j . ")\"/>
		</svg>";
	}
	$content .= "&emsp;<input id='" . $i . "STARN' type='textarea' size=4 value='0' style='border-width:0px;font-size:20px' disbled></input>";
	//descurtir
	$content = $content."<svg id='deslike' class='" . $i ."' width='30' height='30' aling='ligh' viewBox='0 0 478.2 478.2' style='enable-background:new 0 0 478.2 478.2;'>
	<style>
	#deslike {
		transform: rotate(180deg);
		fill: #9999;
		-webkit-transition-duration: 0.3s; /* Safari */
		transition-duration: 0.3s;
	}
	#deslike:hover {
		fill: #33ccff;
	}
	</style>
	<path transform: rotate(180deg); d='M441.11,252.677c10.468-11.99,15.704-26.169,15.704-42.54c0-14.846-5.432-27.692-16.259-38.547    c-10.849-10.854-23.695-16.278-38.541-16.278h-79.082c0.76-2.664,1.522-4.948,2.282-6.851c0.753-1.903,1.811-3.999,3.138-6.283    c1.328-2.285,2.283-3.999,2.852-5.139c3.425-6.468,6.047-11.801,7.857-15.985c1.807-4.192,3.606-9.9,5.42-17.133    c1.811-7.229,2.711-14.465,2.711-21.698c0-4.566-0.055-8.281-0.145-11.134c-0.089-2.855-0.574-7.139-1.423-12.85    c-0.862-5.708-2.006-10.467-3.43-14.272c-1.43-3.806-3.716-8.092-6.851-12.847c-3.142-4.764-6.947-8.613-11.424-11.565    c-4.476-2.95-10.184-5.424-17.131-7.421c-6.954-1.999-14.801-2.998-23.562-2.998c-4.948,0-9.227,1.809-12.847,5.426    c-3.806,3.806-7.047,8.564-9.709,14.272c-2.666,5.711-4.523,10.66-5.571,14.849c-1.047,4.187-2.238,9.994-3.565,17.415    c-1.719,7.998-2.998,13.752-3.86,17.273c-0.855,3.521-2.525,8.136-4.997,13.845c-2.477,5.713-5.424,10.278-8.851,13.706    c-6.28,6.28-15.891,17.701-28.837,34.259c-9.329,12.18-18.94,23.695-28.837,34.545c-9.899,10.852-17.131,16.466-21.698,16.847    c-4.755,0.38-8.848,2.331-12.275,5.854c-3.427,3.521-5.14,7.662-5.14,12.419v183.01c0,4.949,1.807,9.182,5.424,12.703    c3.615,3.525,7.898,5.38,12.847,5.571c6.661,0.191,21.698,4.374,45.111,12.566c14.654,4.941,26.12,8.706,34.4,11.272    c8.278,2.566,19.849,5.328,34.684,8.282c14.849,2.949,28.551,4.428,41.11,4.428h4.855h21.7h10.276    c25.321-0.38,44.061-7.806,56.247-22.268c11.036-13.135,15.697-30.361,13.99-51.679c7.422-7.042,12.565-15.984,15.416-26.836    c3.231-11.604,3.231-22.74,0-33.397c8.754-11.611,12.847-24.649,12.272-39.115C445.395,268.286,443.971,261.055,441.11,252.677z
	M100.5,191.864H18.276c-4.952,0-9.235,1.809-12.851,5.426C1.809,200.905,0,205.188,0,210.137v182.732    c0,4.942,1.809,9.227,5.426,12.847c3.619,3.611,7.902,5.421,12.851,5.421H100.5c4.948,0,9.229-1.81,12.847-5.421    c3.616-3.62,5.424-7.904,5.424-12.847V210.137c0-4.949-1.809-9.231-5.424-12.847C109.73,193.672,105.449,191.864,100.5,191.864z     M67.665,369.308c-3.616,3.521-7.898,5.281-12.847,5.281c-5.14,0-9.471-1.76-12.99-5.281c-3.521-3.521-5.281-7.85-5.281-12.99    c0-4.948,1.759-9.232,5.281-12.847c3.52-3.617,7.85-5.428,12.99-5.428c4.949,0,9.231,1.811,12.847,5.428    c3.617,3.614,5.426,7.898,5.426,12.847C73.091,361.458,71.286,365.786,67.665,369.308z'/></svg>";
	
	$content .= "<div>
	<form id='comment'>
	<textarea id=" . $i . " name = 'comment' placeholder='Comente...'></textarea>
	</form>
	
	<ul id='comment-list'></ul>
	</div>
	</div>";
}
$content .= "</div>";
$content .= "<script src='./../../blocks/ovr/front-end/public/bundle.js'></script>";

//Query 1 -> mdl_page

$record = new stdClass();
$record->course = $_POST['cid'];
$record->name = $searchText;
$record->intro = "";
$record->introformat = 1;
$record->content = $content;
$record->contentformat = 1;
$record->legacyfiles = 0;
$record->legacyfileslast = null;
$record->display = 5;
$record->displayoptions = "a:2:{s:12:\"printheading\";s:1:\"1\";s:10:\"printintro\";s:1:\"0\";}";
$record->revision = 1;
$record->timemodified = time();

$id_mdl_url = -1;
$id_mdl_url = $DB->insert_record('page', $record, true);
if ($id_mdl_url === -1) {
	echo "(1)Error on updating the database:\n";
	exit(1);
}

//Query 2 -> mdl_course_modules
$record2 = new stdClass();
$record2->course = $_POST['cid'];
$record2->module = 15;
$record2->instance = $id_mdl_url;
$record2->secion = $_POST['section'];
$record2->idnumber = "";
$record2->added = time();
$record2->score = 0;
$record2->indent = 0;
$record2->visible = 1;
$record2->visibleold = 1;
$record2->groupmode = 0;
$record2->groupingid = 0;
$record2->completion = 1;
$record2->completiongradeitemnumber = null;
$record2->completionview = 0;
$record2->completionexpected = 0;
$record2->showdescription = 0;
$record2->availability = null;
$record2->deletioninprogress = 0;

$id_mdl_course_modules = -1;
$id_mdl_course_modules = $DB->insert_record('course_modules', $record2, true);
if ($id_mdl_course_modules === -1) {
	echo "(2)Error on updating the database:\n";
	exit(2);
}

//Query 3 -> mdl_course_sections
$sequence = '32,33,34,35,36,37,38,39';
$queryResult = $DB->get_record_sql('SELECT sequence FROM {course_sections} WHERE section = ? and course =  ?', array($_POST['section'], $_POST['cid']));
if ($queryResult != null) {
	$sequence = $queryResult->sequence;
} else {
	echo "(3)Error on updating the database:\n" . $conn->error;
	exit(3);
}
//Query 4 -> mdl_course_sections
$sql = "UPDATE {course_sections} set sequence = \"" . $sequence . "," . $id_mdl_course_modules . "\" where section = " . $_POST['section'] . " AND course=" . $_POST['cid'];
if ($DB->execute($sql) === true) {
	
} else {
	echo "(4)Error on updating the database:\n" . $conn->error;
	exit(4);
}
//Query 5 -> mdl_context
$queryResult = $DB->get_record_sql('SELECT path FROM {context} WHERE contextlevel = ? and path like ?', array('50', '/1/3/%'));
$path = "/1";
if ($queryResult != null) {
	$path = $queryResult->path;
} else {
	echo "(5)Error on updating the database:\n" . $conn->error;
	exit(5);
}
//Query 6 -> mdl_context
$queryResult = $DB->get_record_sql('SELECT id FROM {context} ORDER BY id DESC LIMIT 1', null);
$lastID = -1;
if ($queryResult != null) {
	echo JSON_encode($queryResult);
} else {
	echo "(3)Error on updating the database:\n";
	exit(3);
}
//Query 7 -> mdl_context
$record3 = new stdClass();
$record3->contextlevel = 70;
$record3->instanceid = $id_mdl_course_modules;
$record3->path = $path . "/" . ($lastID + 1);
$record3->depth = (substr_count($path, "/") + 1);


$thisContextID = -1;
$thisContextID = $DB->insert_record('context', $record3, true);
if ($thisContextID != $lastID + 1) {
	$sqlaux = "UPDATE {context} set path = " . $path . "/" . ($thisContextID) . " WHERE id=" . $thisContextID;
}
if ($thisContextID === -1) {
	echo "(7)Error on updating the database:\n" . $conn->error;
	exit(7);
} 

/*#######################################################Clean Cache#######################################################*/
//Deleting the cache related to the course activities (The moodle itself has an method to do that... but I dind't foud a exact way of doing it, so I dit it myself; it does exactly the same thing as the moodle method does)
$cacheDirPath = $moodleData_Path .
"/cachestore_file/default_application/core_coursemodinfo";//Directory where exhibition cache data is
$cacheDir = opendir($cacheDirPath);
$readyToDelete = false;
while ($ourCourseDir = readdir($cacheDir)) {
	if (is_dir($cacheDirPath . "/" . $ourCourseDir)) {
		if (preg_match("/^" . $_POST['cid'] . "-/", $ourCourseDir)) {//Directory of the current course
			$readyToDelete = true;
			$cacheDirPath = $cacheDirPath . "/" . $ourCourseDir;
			break;
		}
	}
}
closedir($cacheDir);
$cacheDir = opendir($cacheDirPath);
if ($readyToDelete) {
	while ($file = readdir($cacheDir)) {
		if ($file != "." && $file != "..") {
			unlink($cacheDirPath . "/" . $file);
		}
	}
}
closedir($cacheDir);
echo "success";
?>
