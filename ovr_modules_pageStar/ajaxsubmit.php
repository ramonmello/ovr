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
	$dbName = 'moodleServer';
	$dbUser = 'root';
	$dbPass = 'root';
	$dbPort = '3306';
	$dbChar = 'UTF8';
	$moodleData_Path = "/var/www/moodledataServer/cache";
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
	$searchText = $_POST['searchText'];//Termo utilizado na busca
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
		var starColor = 'red';
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
     		xhttp.open('POST','estrela.php', true);
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
     		xhttp.open('POST','databaseStarRead.php', true);
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
		$content = $content."&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160 &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; <svg style='fill:  #1d41b9; width: 25px; aling='ligh'; ' version='1.1' id='Capa_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'viewBox='0 0 478.2 478.2' style='enable-background:new 0 0 478.2 478.2;' xml:space='preserve'> <rect class='btn' x='0' y='0' width='10' height='10' onclick='alert('click!')' /><path d='M457.575,325.1c9.8-12.5,14.5-25.9,13.9-39.7c-0.6-15.2-7.4-27.1-13-34.4c6.5-16.2,9-41.7-12.7-61.5 c-15.9-14.5-42.9-21-80.3-19.2c-26.3,1.2-48.3,6.1-49.2,6.3h-0.1c-5,0.9-10.3,2-15.7,3.2c-0.4-6.4,0.7-22.3,12.5-58.1 c14-42.6,13.2-75.2-2.6-97c-16.6-22.9-43.1-24.7-50.9-24.7c-7.5,0-14.4,3.1-19.3,8.8c-11.1,12.9-9.8,36.7-8.4,47.7 c-13.2,35.4-50.2,122.2-81.5,146.3c-0.6,0.4-1.1,0.9-1.6,1.4c-9.2,9.7-15.4,20.2-19.6,29.4c-5.9-3.2-12.6-5-19.8-5h-61 c-23,0-41.6,18.7-41.6,41.6v162.5c0,23,18.7,41.6,41.6,41.6h61c8.9,0,17.2-2.8,24-7.6l23.5,2.8c3.6,0.5,67.6,8.6,133.3,7.3 c11.9,0.9,23.1,1.4,33.5,1.4c17.9,0,33.5-1.4,46.5-4.2c30.6-6.5,51.5-19.5,62.1-38.6c8.1-14.6,8.1-29.1,6.8-38.3 c19.9-18,23.4-37.9,22.7-51.9C461.275,337.1,459.475,330.2,457.575,325.1z M48.275,447.3c-8.1,0-14.6-6.6-14.6-14.6V270.1 c0-8.1,6.6-14.6,14.6-14.6h61c8.1,0,14.6,6.6,14.6,14.6v162.5c0,8.1-6.6,14.6-14.6,14.6h-61V447.3z M431.975,313.4 c-4.2,4.4-5,11.1-1.8,16.3c0,0.1,4.1,7.1,4.6,16.7c0.7,13.1-5.6,24.7-18.8,34.6c-4.7,3.6-6.6,9.8-4.6,15.4c0,0.1,4.3,13.3-2.7,25.8 c-6.7,12-21.6,20.6-44.2,25.4c-18.1,3.9-42.7,4.6-72.9,2.2c-0.4,0-0.9,0-1.4,0c-64.3,1.4-129.3-7-130-7.1h-0.1l-10.1-1.2 c0.6-2.8,0.9-5.8,0.9-8.8V270.1c0-4.3-0.7-8.5-1.9-12.4c1.8-6.7,6.8-21.6,18.6-34.3c44.9-35.6,88.8-155.7,90.7-160.9 c0.8-2.1,1-4.4,0.6-6.7c-1.7-11.2-1.1-24.9,1.3-29c5.3,0.1,19.6,1.6,28.2,13.5c10.2,14.1,9.8,39.3-1.2,72.7 c-16.8,50.9-18.2,77.7-4.9,89.5c6.6,5.9,15.4,6.2,21.8,3.9c6.1-1.4,11.9-2.6,17.4-3.5c0.4-0.1,0.9-0.2,1.3-0.3 c30.7-6.7,85.7-10.8,104.8,6.6c16.2,14.8,4.7,34.4,3.4,36.5c-3.7,5.6-2.6,12.9,2.4,17.4c0.1,0.1,10.6,10,11.1,23.3 C444.875,295.3,440.675,304.4,431.975,313.4z'/> </svg> ";
		$content = $content."<svg style='fill: #1d41b9;transform: rotate(180deg); width: 25px; aling='ligh'; ' version='1.1' id='Capa_1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'viewBox='0 0 478.2 478.2' style='enable-background:new 0 0 478.2 478.2;' xml:space='preserve'> <path d='M457.575,325.1c9.8-12.5,14.5-25.9,13.9-39.7c-0.6-15.2-7.4-27.1-13-34.4c6.5-16.2,9-41.7-12.7-61.5 c-15.9-14.5-42.9-21-80.3-19.2c-26.3,1.2-48.3,6.1-49.2,6.3h-0.1c-5,0.9-10.3,2-15.7,3.2c-0.4-6.4,0.7-22.3,12.5-58.1 c14-42.6,13.2-75.2-2.6-97c-16.6-22.9-43.1-24.7-50.9-24.7c-7.5,0-14.4,3.1-19.3,8.8c-11.1,12.9-9.8,36.7-8.4,47.7 c-13.2,35.4-50.2,122.2-81.5,146.3c-0.6,0.4-1.1,0.9-1.6,1.4c-9.2,9.7-15.4,20.2-19.6,29.4c-5.9-3.2-12.6-5-19.8-5h-61 c-23,0-41.6,18.7-41.6,41.6v162.5c0,23,18.7,41.6,41.6,41.6h61c8.9,0,17.2-2.8,24-7.6l23.5,2.8c3.6,0.5,67.6,8.6,133.3,7.3 c11.9,0.9,23.1,1.4,33.5,1.4c17.9,0,33.5-1.4,46.5-4.2c30.6-6.5,51.5-19.5,62.1-38.6c8.1-14.6,8.1-29.1,6.8-38.3 c19.9-18,23.4-37.9,22.7-51.9C461.275,337.1,459.475,330.2,457.575,325.1z M48.275,447.3c-8.1,0-14.6-6.6-14.6-14.6V270.1 c0-8.1,6.6-14.6,14.6-14.6h61c8.1,0,14.6,6.6,14.6,14.6v162.5c0,8.1-6.6,14.6-14.6,14.6h-61V447.3z M431.975,313.4 c-4.2,4.4-5,11.1-1.8,16.3c0,0.1,4.1,7.1,4.6,16.7c0.7,13.1-5.6,24.7-18.8,34.6c-4.7,3.6-6.6,9.8-4.6,15.4c0,0.1,4.3,13.3-2.7,25.8 c-6.7,12-21.6,20.6-44.2,25.4c-18.1,3.9-42.7,4.6-72.9,2.2c-0.4,0-0.9,0-1.4,0c-64.3,1.4-129.3-7-130-7.1h-0.1l-10.1-1.2 c0.6-2.8,0.9-5.8,0.9-8.8V270.1c0-4.3-0.7-8.5-1.9-12.4c1.8-6.7,6.8-21.6,18.6-34.3c44.9-35.6,88.8-155.7,90.7-160.9 c0.8-2.1,1-4.4,0.6-6.7c-1.7-11.2-1.1-24.9,1.3-29c5.3,0.1,19.6,1.6,28.2,13.5c10.2,14.1,9.8,39.3-1.2,72.7 c-16.8,50.9-18.2,77.7-4.9,89.5c6.6,5.9,15.4,6.2,21.8,3.9c6.1-1.4,11.9-2.6,17.4-3.5c0.4-0.1,0.9-0.2,1.3-0.3 c30.7-6.7,85.7-10.8,104.8,6.6c16.2,14.8,4.7,34.4,3.4,36.5c-3.7,5.6-2.6,12.9,2.4,17.4c0.1,0.1,10.6,10,11.1,23.3 C444.875,295.3,440.675,304.4,431.975,313.4z'/></svg> &#160&#160&#160&#160&#160&#160";
		for($j=1;$j<=5;++$j){
		$content = $content."<svg width='20' height='20'>
			<polygon points=\"\"10,1 4,19.8 19,7.8 1,7.8 16,19.8\"\" id='".$i."STAR".$j."' style=\"\"fill:grey;\"\" onmouseover=\"\"colorStars(".$j.",".$i.",'yellow');this.style.fill='yellow';\"\" onmouseout=\"\"colorStars(stars[".$i."],".$i.",starColor);\"\" onClick=\"\"setStar(".$j.",".$i.");updateVideoRating(".$i.",".$j.")\"\" />
		</svg>";
		}
		$content .= "<input id='".$i."STARN' type='textarea' size=4 value='0' style='border-width:0px;font-size:20px' disbled></input>";
		$content = $content."</p></div></div>";
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