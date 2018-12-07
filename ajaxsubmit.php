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

textarea {
	width: 400px;
	height: 50px;
	border: 3px solid #d8e5ed;
	border-radius: 5px;
	font-size: 16px;
	resize: none;
}

ul {
	text-align: -webkit-center;
	list-style: none;
	margin: 0;
	padding: 0;
	font-family: sans-serif;
}

li {
	text-align: justify;
	padding: 20px 20px 0;
	margin: 20px 0 0;
	border-top: 1px solid #d8e5ed;
	width: 400px;
}

</style>
";
$content .= "<div id='commentblock'>";
for ($i = 0; $i < count($urls); ++$i) {
	$content = $content . "<p>"
		. "<div style=\"text-align: center;\">"
		. "<video width=480px height=360px controls=\"true\" src=\"" . $urls[$i] . "\">" . $urls[$i] . "</video>"
		. "<input class= 'url' type='hidden' id=" . $i . " value=" . $urls[$i] . " disabled>"
		. "</p>"

		. $names[$i];
	$content = $content . "&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;";
	for ($j = 1; $j <= 5; ++$j) {
		$content = $content . "<svg width='20' height='20'>
		<polygon points=\"10,1 4,19.8 19,7.8 1,7.8 16,19.8\" id='" . $i . "STAR" . $j . "' style=\"fill:grey;\" 
		onmouseover=\"colorStars(" . $j . "," . $i . ",'yellow');this.style.fill='yellow';\" onmouseout=\"colorStars(stars[" . $i . "]," . $i . ",starColor);\" onClick=\"setStar(" . $j . "," . $i . ");updateVideoRating(" . $i . "," . $j . ")\"/>
		</svg>";
	}
	$content .= "&emsp;<input id='" . $i . "STARN' type='textarea' size=4 value='0' style='border-width:0px;font-size:20px' disbled></input>";
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
