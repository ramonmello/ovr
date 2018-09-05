<?php   
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
$moodleData_Path = "/var/www/moodledata/cache";
//Validando valores passados via POST; Todos precissão ser válidos para que a inserção funcione
/*
Se qualquer valor for inválido, o script termina em erro -1 e retorna uma mensagem.
*/
if(!isset($_POST["cid"]) || !isset($_POST["rotName"]) || !isset($_POST["section"]) || !isset($_POST["names"]) || !isset($_POST["urls"]) /*|| empty($_POST["cid"]) || empty($_POST["rotName"]) || empty($_POST["section"]) || empty($_POST["names"]) || empty($_POST["urls"])*/){
	echo "(-1)Um ou mais valores não foram corretamente passados (via POST)";
	exit(-1);
}
//Gerando conexão com o banco de dados.
/*
Se houver falha na conexão, o script termina em erro 0 e retorna uma mensagem.
*/
$conn = $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName,$dbPort);
if ($mysqli->connect_errno) {
	echo "Erro ao atualizar a base de dados: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	exit(0);
}
//Tranformando os valores passados por post(formatados em JSON) em arrays padrão PHP
$urls = json_decode($_POST['urls'],true);//Urls de cada video
$names = json_decode($_POST['names'],true);//Nomes de cada video
$searchText = $_POST['searchText'];//Termo utilizado na buscam
/*
Cada inserção cria um rótulo, que é inserido primeiro.
A partir desse ponto, se houver um erro o processo de inserção será cancelado.
Isso pode gerar lixo no banco de dados
TODO: Criar processo de reversão das inserções perante a um erro.
Importante: Cada etapa (uma query ao banco) possui um código único de erro que é relatado caso algo de errado na quela etapa. Os erros referentes ao Label vão de 8 a 14 e os das urls vão de 0 a 7
*/
/*######################################################dicionando Rótulo######################################################*/
/*
// Query 8 -> mdl_label
// -- module fixo em 20(tipo url no moodle)
// -- section = sessão atual da atividade
// -- instance = id da url adicionada na tabela mdl_label
$sql = "INSERT INTO mdl_label (course,name,intro,introformat,timemodified) VALUES (".$_POST['cid'].",\"".$_POST['rotName']."\",'<p>'\"".$_POST['rotName']."\"'</p>',1,unix_timestamp(now()))";
$id_mdl_label=-1;
if ($conn->query($sql) === TRUE) {
	$id_mdl_label = $conn->insert_id;
}else{
	echo "\n(8)Erro ao criar Rótulo:\n".$conn->error;
	exit(8);
}
// Query 9 -> mdl_course_modules
// -- module fixo em 12(tipo label no moodle)
// -- section = sessão atual da atividade
// -- instance = id da url adicionada na tabela mdl_label
$sql = "INSERT INTO mdl_course_modules (course,module,instance,section,idnumber,added,score,indent,visible,visibleold,groupmode,groupingid,completion,completiongradeitemnumber,completionview,completionexpected,showdescription,availability,deletioninprogress) VALUES (".$_POST['cid'].",12,".$id_mdl_label.",".$_POST['section'].",\"\",unix_timestamp(now()),0,0,1,1,0,0,1,NULL,0,0,0,NULL,0)";
$id_mdl_course_modules=-1;
if ($conn->query($sql) === TRUE) {
	$id_mdl_course_modules = $conn->insert_id;
}else{
	echo "(9)Erro ao criar Rótulo:\n".$conn->error;
	exit(9);
}
// Query 10 -> mdl_course_sections
// -- id = section usada no insert anterior
// -- sequence = valor atual da sequencia, concatenado de ",XZ", onde XZ é o id do campo adicionado no mdl_course_modules
$sqlaux = "SELECT sequence FROM mdl_course_sections WHERE section=".$_POST['section']." AND course=".$_POST['cid'];
$sequence='';
$queryResult = $conn->query($sqlaux);
if ($queryResult->num_rows >0) {
	$row = $queryResult->fetch_assoc();
	$sequence = $row['sequence'];
}else{
	echo "(10)Erro ao criar Rótulo:\n".$conn->error;
	exit(10);
}
$sql = "UPDATE mdl_course_sections set sequence = \"".$sequence.",".$id_mdl_course_modules."\" where section = ".$_POST['section']." AND course=".$_POST['cid'];
if ($conn->query($sql) === TRUE) {
	
}else{
	echo "(11)Erro ao criar Rótulo:\n".$conn->error;
	exit(11);
}
// Query 12 -> mdl_course_sections
// -- instanceid = id do mdl_course_modules
// -- contextlelve = 70 (nivel de qualquer modulo)
// -- path = 1<sistema>/3<categoria do curso>/21<constante para todos os modulos; descobrir por que>/id dessa entrada
$sqlaux = "SELECT path FROM mdl_context WHERE contextlevel = 50 and path like '/1/3/%'";
$queryResult = $conn->query($sqlaux);
$path = "/1";
if ($queryResult->num_rows >0) {
	$row = $queryResult->fetch_assoc();
	$path = $row['path'];
}else{
	echo "(12)Erro ao criar Rótulo:\n".$conn->error;
	exit(12);
}
// Query 13 -> mdl_context
// Recuperando o último objeto inserido na tabela
$sqlaux = "SELECT id FROM mdl_context ORDER BY id DESC LIMIT 1";
$queryResult = $conn->query($sqlaux);
$lastID=-1;
if ($queryResult->num_rows >0) {
	$row = $queryResult->fetch_assoc();
	$lastID = $row['id'];
}else{
	echo "(13)Erro ao criar Rótulo:\n".$conn->error;
	exit(13);
}
// Query 14 -> mdl_context
// Atualizando path com resultado de sqlaux concatenado com o id do anterior
// Se após a inserção for detectada uma concorrencia no acesso a tabela (alguem acessou ela no mesmo tempo que nós), verificamos se o ID que usamos foi o correto, se não, atualizamos a entrada feita com o correto
$sql = "INSERT INTO mdl_context (contextlevel,instanceid,path,depth)VALUES (70,".$id_mdl_course_modules.",\"".$path."/".($lastID+1)."\",".(substr_count($path,"/")+1).")";
$thisContextID=-1;
if ($conn->query($sql) === TRUE) {
	$thisContextID = $conn->insert_id;
	if($thisContextID != $lastID+1){
		$sqlaux = "UPDATE mdl_context set path = ".$path."/".($thisContextID)." WHERE id=".$thisContextID;
	}
}else{
	echo "(14)Erro ao criar Rótulo:\n".$conn->error;
	exit(14);
}
*/
/*######################################################Adicionando Page######################################################*/
//Query 1 -> mdl_page
//-- module fixo em 20(tipo url no moodle)
//-- section = sessão atual da atividade
//-- instance = id da url adicionada na tabela mdl_page$content 
$content ="<script>
var stars = Array();";
for($i=0;$i<count($urls);++$i){
	$content = $content."stars[".$i."]=0;";
}
$jsonData = "";
for($i=0;$i<count($urls);++$i){
	$jsonData .= $i.":"."'".$urls[$i]."'";
	if($i<(count($urls)-1))
	$jsonData .= ",";
}

$content = $content."
getVideoRating({".$jsonData."});
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
	xhttp.open('POST','../../blocks/ovr_modules/estrela.php', true);
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
	xhttp.open('POST','../../blocks/ovr_modules/databaseStarRead.php', true);
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send('urls='+JSON.stringify(videosULRs));
}
</script>";
for($i=0;$i<count($urls);++$i){		
	$content = $content.
	"<p style=\"\"text-align: center;\"\">"
	."<video width=480px height=360px controls=\"\"true\"\" src=\"\"".$urls[$i]."\"\">".$urls[$i]."</video>"
	."<input type='hidden' id=".$i." value=".$urls[$i]." disabled>"
	."</p>"
	."<p style=\"\"text-align: center;\"\">"
	.$names[$i]
	."<br>"
	."</p>";
	$content = $content."<p style=\"\"text-align: center;\"\">";
	//curtir
	$content = $content."<svg width='30' height='30' onclick='alert('Curtir clicado!')' aling='ligh' id='like' viewBox='0 0 478.2 478.2' style='enable-background:new 0 0 478.2 478.2;'>
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
	<path id='like' d='M441.11,252.677c10.468-11.99,15.704-26.169,15.704-42.54c0-14.846-5.432-27.692-16.259-38.547    c-10.849-10.854-23.695-16.278-38.541-16.278h-79.082c0.76-2.664,1.522-4.948,2.282-6.851c0.753-1.903,1.811-3.999,3.138-6.283    c1.328-2.285,2.283-3.999,2.852-5.139c3.425-6.468,6.047-11.801,7.857-15.985c1.807-4.192,3.606-9.9,5.42-17.133    c1.811-7.229,2.711-14.465,2.711-21.698c0-4.566-0.055-8.281-0.145-11.134c-0.089-2.855-0.574-7.139-1.423-12.85    c-0.862-5.708-2.006-10.467-3.43-14.272c-1.43-3.806-3.716-8.092-6.851-12.847c-3.142-4.764-6.947-8.613-11.424-11.565    c-4.476-2.95-10.184-5.424-17.131-7.421c-6.954-1.999-14.801-2.998-23.562-2.998c-4.948,0-9.227,1.809-12.847,5.426    c-3.806,3.806-7.047,8.564-9.709,14.272c-2.666,5.711-4.523,10.66-5.571,14.849c-1.047,4.187-2.238,9.994-3.565,17.415    c-1.719,7.998-2.998,13.752-3.86,17.273c-0.855,3.521-2.525,8.136-4.997,13.845c-2.477,5.713-5.424,10.278-8.851,13.706    c-6.28,6.28-15.891,17.701-28.837,34.259c-9.329,12.18-18.94,23.695-28.837,34.545c-9.899,10.852-17.131,16.466-21.698,16.847    c-4.755,0.38-8.848,2.331-12.275,5.854c-3.427,3.521-5.14,7.662-5.14,12.419v183.01c0,4.949,1.807,9.182,5.424,12.703    c3.615,3.525,7.898,5.38,12.847,5.571c6.661,0.191,21.698,4.374,45.111,12.566c14.654,4.941,26.12,8.706,34.4,11.272    c8.278,2.566,19.849,5.328,34.684,8.282c14.849,2.949,28.551,4.428,41.11,4.428h4.855h21.7h10.276    c25.321-0.38,44.061-7.806,56.247-22.268c11.036-13.135,15.697-30.361,13.99-51.679c7.422-7.042,12.565-15.984,15.416-26.836    c3.231-11.604,3.231-22.74,0-33.397c8.754-11.611,12.847-24.649,12.272-39.115C445.395,268.286,443.971,261.055,441.11,252.677z
	M100.5,191.864H18.276c-4.952,0-9.235,1.809-12.851,5.426C1.809,200.905,0,205.188,0,210.137v182.732    c0,4.942,1.809,9.227,5.426,12.847c3.619,3.611,7.902,5.421,12.851,5.421H100.5c4.948,0,9.229-1.81,12.847-5.421    c3.616-3.62,5.424-7.904,5.424-12.847V210.137c0-4.949-1.809-9.231-5.424-12.847C109.73,193.672,105.449,191.864,100.5,191.864z     M67.665,369.308c-3.616,3.521-7.898,5.281-12.847,5.281c-5.14,0-9.471-1.76-12.99-5.281c-3.521-3.521-5.281-7.85-5.281-12.99    c0-4.948,1.759-9.232,5.281-12.847c3.52-3.617,7.85-5.428,12.99-5.428c4.949,0,9.231,1.811,12.847,5.428    c3.617,3.614,5.426,7.898,5.426,12.847C73.091,361.458,71.286,365.786,67.665,369.308z'/></svg>&#160&#160&#160&#160&#160&#160&#160&#160&#160&#160&#160";
	//Estrelas
	for($j=1;$j<=5;++$j){
		$content = $content."<svg width='20' height='20'>
		<polygon points=\"\"10,1 4,19.8 19,7.8 1,7.8 16,19.8\"\" id='".$i."STAR".$j."' style=\"\"fill:grey;\"\" 
		onmouseover=\"\"colorStars(".$j.",".$i.",'yellow');this.style.fill='yellow';\"\" onmouseout=\"\"colorStars(stars[".$i."],".$i.",starColor);\"\" onClick=\"\"setStar(".$j.",".$i.");updateVideoRating(".$i.",".$j.")\"\" />
		</svg>";
	}
	$content .= "<input id='".$i."STARN' type='textarea' size=4 value='0' style='border-width:0px;font-size:20px' disbled></input>";
	//descurtir
	$content = $content."<svg width='30' height='30' onclick='alert('Curtir clicado!')' aling='ligh' id='deslike' viewBox='0 0 478.2 478.2' style='enable-background:new 0 0 478.2 478.2;'>
	<style>
	#deslike {
		transform: rotate(180deg);
		fill: #9999;
		-webkit-transition-duration: 0.4s; /* Safari */
		transition-duration: 0.4s;
	}
	#deslike:hover {
		fill: blue;
	}
	</style>
	<path transform: rotate(180deg); d='M441.11,252.677c10.468-11.99,15.704-26.169,15.704-42.54c0-14.846-5.432-27.692-16.259-38.547    c-10.849-10.854-23.695-16.278-38.541-16.278h-79.082c0.76-2.664,1.522-4.948,2.282-6.851c0.753-1.903,1.811-3.999,3.138-6.283    c1.328-2.285,2.283-3.999,2.852-5.139c3.425-6.468,6.047-11.801,7.857-15.985c1.807-4.192,3.606-9.9,5.42-17.133    c1.811-7.229,2.711-14.465,2.711-21.698c0-4.566-0.055-8.281-0.145-11.134c-0.089-2.855-0.574-7.139-1.423-12.85    c-0.862-5.708-2.006-10.467-3.43-14.272c-1.43-3.806-3.716-8.092-6.851-12.847c-3.142-4.764-6.947-8.613-11.424-11.565    c-4.476-2.95-10.184-5.424-17.131-7.421c-6.954-1.999-14.801-2.998-23.562-2.998c-4.948,0-9.227,1.809-12.847,5.426    c-3.806,3.806-7.047,8.564-9.709,14.272c-2.666,5.711-4.523,10.66-5.571,14.849c-1.047,4.187-2.238,9.994-3.565,17.415    c-1.719,7.998-2.998,13.752-3.86,17.273c-0.855,3.521-2.525,8.136-4.997,13.845c-2.477,5.713-5.424,10.278-8.851,13.706    c-6.28,6.28-15.891,17.701-28.837,34.259c-9.329,12.18-18.94,23.695-28.837,34.545c-9.899,10.852-17.131,16.466-21.698,16.847    c-4.755,0.38-8.848,2.331-12.275,5.854c-3.427,3.521-5.14,7.662-5.14,12.419v183.01c0,4.949,1.807,9.182,5.424,12.703    c3.615,3.525,7.898,5.38,12.847,5.571c6.661,0.191,21.698,4.374,45.111,12.566c14.654,4.941,26.12,8.706,34.4,11.272    c8.278,2.566,19.849,5.328,34.684,8.282c14.849,2.949,28.551,4.428,41.11,4.428h4.855h21.7h10.276    c25.321-0.38,44.061-7.806,56.247-22.268c11.036-13.135,15.697-30.361,13.99-51.679c7.422-7.042,12.565-15.984,15.416-26.836    c3.231-11.604,3.231-22.74,0-33.397c8.754-11.611,12.847-24.649,12.272-39.115C445.395,268.286,443.971,261.055,441.11,252.677z
	M100.5,191.864H18.276c-4.952,0-9.235,1.809-12.851,5.426C1.809,200.905,0,205.188,0,210.137v182.732    c0,4.942,1.809,9.227,5.426,12.847c3.619,3.611,7.902,5.421,12.851,5.421H100.5c4.948,0,9.229-1.81,12.847-5.421    c3.616-3.62,5.424-7.904,5.424-12.847V210.137c0-4.949-1.809-9.231-5.424-12.847C109.73,193.672,105.449,191.864,100.5,191.864z     M67.665,369.308c-3.616,3.521-7.898,5.281-12.847,5.281c-5.14,0-9.471-1.76-12.99-5.281c-3.521-3.521-5.281-7.85-5.281-12.99    c0-4.948,1.759-9.232,5.281-12.847c3.52-3.617,7.85-5.428,12.99-5.428c4.949,0,9.231,1.811,12.847,5.428    c3.617,3.614,5.426,7.898,5.426,12.847C73.091,361.458,71.286,365.786,67.665,369.308z'/></svg>";
	
	$content = $content."</p>";
}
$sql ="INSERT INTO mdl_page (course,name,intro,introformat,content,contentformat,legacyfiles,legacyfileslast,display,displayoptions,revision,timemodified) 
VALUES(".$_POST['cid'].",\"".$searchText."\",\"\", 1,\"".$content."\", 1, 0, NULL, 5, \"a:2:{s:12:\"\"printheading\"\";s:1:\"\"1\"\";s:10:\"\"printintro\"\";s:1:\"\"0\"\";}\", 1,unix_timestamp(now()))";
$id_mdl_url=-1;
if ($conn->query($sql) === TRUE) {
	$id_mdl_url = $conn->insert_id;
}else{
	echo "(1)Erro ao atualizar a base de dados:\n".$conn->error;
	exit(1);
}

//Query 2 -> mdl_course_modules
//-- module fixo em 20(tipo url no moodle)
//-- section = sessão atual da atividade
//-- instance = id da url adicionada na tabela mdl_page
$sql = "INSERT INTO mdl_course_modules (course,module,instance,section,idnumber,added,score,indent,visible,visibleold,groupmode,groupingid,completion,completiongradeitemnumber,completionview,completionexpected,showdescription,availability,deletioninprogress) VALUES (".$_POST['cid'].",15,".$id_mdl_url.",".$_POST['section'].",\"\",unix_timestamp(now()),0,0,1,1,0,0,1,NULL,0,0,0,NULL,0)";
$id_mdl_course_modules=-1;
if ($conn->query($sql) === TRUE) {
	$id_mdl_course_modules = $conn->insert_id;
}else{
	echo "(2)Erro ao atualizar a base de dados:\n".$conn->error;
	exit(2);
}
//Query 3 -> mdl_course_sections
//-- id = section usada no insert anterior
//-- sequence = valor atual da sequencia, concatenado de ",XZ", onde XZ é o id do campo adicionado no mdl_course_modules
$sqlaux = "SELECT sequence FROM mdl_course_sections WHERE section=".$_POST['section']." AND course=".$_POST['cid'];
$sequence='32,33,34,35,36,37,38,39';
$queryResult = $conn->query($sqlaux);
if ($queryResult->num_rows >0) {
	$row = $queryResult->fetch_assoc();
	$sequence = $row['sequence'];
}else{
	echo "(3)Erro ao atualizar a base de dados:\n".$conn->error;
	exit(3);
}
//Query 4 -> mdl_course_sections
//-- instanceid = id do mdl_course_modules
//-- contextlelve = 70 (nivel de qualquer modulo)
//-- path = 1<sistema>/3<categoria do curso>/21<constante para todos os modulos; descobrir por que>/id dessa entrada
$sql = "UPDATE mdl_course_sections set sequence = \"".$sequence.",".$id_mdl_course_modules."\" where section = ".$_POST['section']." AND course=".$_POST['cid'];
if ($conn->query($sql) === TRUE) {
	
}else{
	echo "(4)Erro ao atualizar a base de dados:\n".$conn->error;
	exit(4);
}
//Query 5 -> mdl_context
//Obtemos o caminho atual para atividades do contexto atual(50, valor padrão)
$sqlaux = "SELECT path FROM mdl_context WHERE contextlevel = 50 and path like '/1/3/%'";
$queryResult = $conn->query($sqlaux);
$path = "/1";
if ($queryResult->num_rows >0) {
	$row = $queryResult->fetch_assoc();
	$path = $row['path'];
}else{
	echo "(5)Erro ao atualizar a base de dados:\n".$conn->error;
	exit(5);
}
//Query 6 -> mdl_context
//Recuperando o último objeto inserido na tabela
$sqlaux = "SELECT id FROM mdl_context ORDER BY id DESC LIMIT 1";
$queryResult = $conn->query($sqlaux);
$lastID=-1;
if ($queryResult->num_rows >0) {
	$allResults = Array();
	while($row = $queryResult->fetch_assoc()){
		array_push($allResults,JSON_encode($row));
	}
	echo JSON_encode($allResults);
}else{
	echo "(6)Erro ao atualizar a base de dados:\n".$conn->error;
	exit(6);
}
//Query 7 -> mdl_context
//Atualizando path com resultado de sqlaux concatenado com o id do anterior
//Se após a inserção for detectada uma concorrencia no acesso a tabela (alguem acessou ela no mesmo tempo que nós), verificamos se o ID que usamos foi o correto, se não, atualizamos a entrada feita com o correto
$sql = "INSERT INTO mdl_context (contextlevel,instanceid,path,depth)VALUES (70,".$id_mdl_course_modules.",\"".$path."/".($lastID+1)."\",".(substr_count($path,"/")+1).")";
$thisContextID=-1;
if ($conn->query($sql) === TRUE) {
	$thisContextID = $conn->insert_id;
	if($thisContextID != $lastID+1){
		$sqlaux = "UPDATE mdl_context set path = ".$path."/".($thisContextID)." WHERE id=".$thisContextID;
	}
}else{
	echo "(7)Erro ao atualizar a base de dados:\n".$conn->error;
	exit(7);
}

$conn->close();	
/*######################################################dicionando URLS######################################################*/
/*
for($i=0;$i<count($urls);++$i){		
	//Query 1 -> mdl_url
	//-- module fixo em 20(tipo url no moodle)
	//-- section = sessão atual da atividade
	//-- instance = id da url adicionada na tabela mdl_url
	$sql = "INSERT INTO mdl_url (course,name,intro,introformat,externalurl,display,displayoptions,parameters,timemodified)VALUES (".$_POST['cid'].",\"".$names[$i]."\",\"\",1,\"".$urls[$i]."\",0,\"a:1:{s:10:\"\"printintro\"\";i:1;}\",\"a:0:{}\",unix_timestamp(now()))";
		$id_mdl_url=-1;
		if ($conn->query($sql) === TRUE) {
			$id_mdl_url = $conn->insert_id;
		}else{
			echo "(1)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(1);
		}
		//Query 2 -> mdl_course_modules
		//-- module fixo em 20(tipo url no moodle)
		//-- section = sessão atual da atividade
		//-- instance = id da url adicionada na tabela mdl_url
		$sql = "INSERT INTO mdl_course_modules (course,module,instance,section,idnumber,added,score,indent,visible,visibleold,groupmode,groupingid,completion,completiongradeitemnumber,completionview,completionexpected,showdescription,availability,deletioninprogress) VALUES (".$_POST['cid'].",15,".$id_mdl_url.",".$_POST['section'].",\"\",unix_timestamp(now()),0,0,1,1,0,0,1,NULL,0,0,0,NULL,0)";
		$id_mdl_course_modules=-1;
		if ($conn->query($sql) === TRUE) {
			$id_mdl_course_modules = $conn->insert_id;
		}else{
			echo "(2)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(2);
		}
		//Query 3 -> mdl_course_sections
		//-- id = section usada no insert anterior
		//-- sequence = valor atual da sequencia, concatenado de ",XZ", onde XZ é o id do campo adicionado no mdl_course_modules
		$sqlaux = "SELECT sequence FROM mdl_course_sections WHERE section=".$_POST['section']." AND course=".$_POST['cid'];
		$sequence='32,33,34,35,36,37,38,39';
		$queryResult = $conn->query($sqlaux);
		if ($queryResult->num_rows >0) {
			$row = $queryResult->fetch_assoc();
			$sequence = $row['sequence'];
		}else{
			echo "(3)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(3);
		}
		//Query 4 -> mdl_course_sections
		//-- instanceid = id do mdl_course_modules
		//-- contextlelve = 70 (nivel de qualquer modulo)
		//-- path = 1<sistema>/3<categoria do curso>/21<constante para todos os modulos; descobrir por que>/id dessa entrada
		$sql = "UPDATE mdl_course_sections set sequence = \"".$sequence.",".$id_mdl_course_modules."\" where section = ".$_POST['section']." AND course=".$_POST['cid'];
		if ($conn->query($sql) === TRUE) {
			
		}else{
			echo "(4)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(4);
		}
		//Query 5 -> mdl_context
		//Obtemos o caminho atual para atividades do contexto atual(50, valor padrão)
		$sqlaux = "SELECT path FROM mdl_context WHERE contextlevel = 50 and path like '/1/3/%'";
		$queryResult = $conn->query($sqlaux);
		$path = "/1";
		if ($queryResult->num_rows >0) {
			$row = $queryResult->fetch_assoc();
			$path = $row['path'];
		}else{
			echo "(5)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(5);
		}
		//Query 6 -> mdl_context
		//Recuperando o último objeto inserido na tabela
		$sqlaux = "SELECT id FROM mdl_context ORDER BY id DESC LIMIT 1";
		$queryResult = $conn->query($sqlaux);
		$lastID=-1;
		if ($queryResult->num_rows >0) {
			$row = $queryResult->fetch_assoc();
			$lastID = $row['id'];
		}else{
			echo "(6)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(6);
		}
		//Query 7 -> mdl_context
		//Atualizando path com resultado de sqlaux concatenado com o id do anterior
		//Se após a inserção for detectada uma concorrencia no acesso a tabela (alguem acessou ela no mesmo tempo que nós), verificamos se o ID que usamos foi o correto, se não, atualizamos a entrada feita com o correto
		$sql = "INSERT INTO mdl_context (contextlevel,instanceid,path,depth)VALUES (70,".$id_mdl_course_modules.",\"".$path."/".($lastID+1)."\",".(substr_count($path,"/")+1).")";
		$thisContextID=-1;
		if ($conn->query($sql) === TRUE) {
			$thisContextID = $conn->insert_id;
			if($thisContextID != $lastID+1){
				$sqlaux = "UPDATE mdl_context set path = ".$path."/".($thisContextID)." WHERE id=".$thisContextID;
			}
		}else{
			echo "(7)Erro ao atualizar a base de dados:\n".$conn->error;
			exit(7);
		}
	}
	$conn->close();
	/*#######################################################Limpando a Cache#######################################################*/
	//Deletando Cache da página (O próprio moodle faria esta ação se utilizássemos a API de eventos, mas como sempre, ninguém consegue entender a API, então fazemos manualmente)
	$cacheDirPath = $moodleData_Path.
	"/cachestore_file/default_application/core_coursemodinfo";//Diretorio onde os arquivos de cache de exibição estão
	$cacheDir = opendir($cacheDirPath);
	//O primeiro digito do nome das pastas é o id do curso das mesmas, o segundo digito, separado por '-' pode variar de instalação a instalação, então primeiro procuramos por uma pasta que comece com o id do curso que estamos modifican
	$readyToDelete = false;
	while($ourCourseDir = readdir($cacheDir)){
		if(is_dir($cacheDirPath."/".$ourCourseDir)){
			if(preg_match("/^".$_POST['cid']."-/",$ourCourseDir)){//Diretorio do nosso curso encontrado
				$readyToDelete = true;
				$cacheDirPath = $cacheDirPath."/".$ourCourseDir;
				break;
			}
		}
	}
	closedir($cacheDir);
	$cacheDir = opendir($cacheDirPath);
	if($readyToDelete){
		while($file = readdir($cacheDir)) {
			if ($file != "." && $file != "..") {
				unlink($cacheDirPath."/".$file);
			}
		}
	}
	closedir($cacheDir);
	echo "sucesso";
	?>
	