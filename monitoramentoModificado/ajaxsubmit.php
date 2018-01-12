<?php   
   //$_POST['cid']."\n".$_POST['urls']."\n".$_POST['names'];
	$dbType = 'mysql';
	$dbHost = 'localhost';
	$dbName = 'moodlebavi';
	$dbUser = 'root';
	$dbPass = 'root';
	$dbPort = '3306';
	$dbChar = 'UTF8';

	$conn = $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName,$dbPort);
	if ($mysqli->connect_errno) {
   		echo "Erro ao atualizar a base de dados: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    	exit(0);
	}
	$urls = json_decode($_POST['urls'],true);//Urls de cada video
	$names = json_decode($_POST['names'],true);//Urls de cada video
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
		$sql = "INSERT INTO mdl_course_modules (course,module,instance,section,idnumber,added,score,indent,visible,visibleoncoursepage,visibleold,groupmode,groupingid,completion,completiongradeitemnumber,completionview,completionexpected,showdescription,availability,deletioninprogress) VALUES (2,20,".$id_mdl_url.",".$_POST['section'].",\"\",unix_timestamp(now()),0,0,1,1,1,0,0,1,NULL,0,0,0,NULL,0)";
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
		$sqlaux = "SELECT sequence FROM mdl_course_sections WHERE id=".$_POST['section'];
		$sequence='32,33,34,35,36,37,38,39';
		$queryResult = $conn->query($sqlaux);
		if ($queryResult->num_rows >0) {
			$row = $queryResult->fetch_assoc();
    		$sequence = $row['sequence'];
    	}else{
    		echo "(3)Erro ao atualizar a base de dados:\n".$conn->error;
    		exit(3);
    	}

		$sql = "UPDATE mdl_course_sections set sequence = \"".$sequence.",".$id_mdl_course_modules."\" where id = ".$_POST['section'];
		if ($conn->query($sql) === TRUE) {
    		
    	}else{
    		echo "(4)Erro ao atualizar a base de dados:\n".$conn->error;
    		exit(4);
    	}

		//Query 4 -> mdl_course_sections
		//-- instanceid = id do mdl_course_modules
		//-- contextlelve = 70 (nivel de qualquer modulo)
		//-- path = 1<sistema>/3<categoria do curso>/21<constante para todos os modulos; descobrir por que>/id dessa entrada
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
			//atualizar path com resultado de sqlaux concatenado com o id do anteriorS
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

	//echo $_POST['cid']."\n".$_POST['urls']."\n".$_POST['names'];
	$conn->close();
	echo "sucesso";
?>