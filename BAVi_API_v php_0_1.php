<?php
	/*
		Implementation of the BAVi API.
		Based on the JAVA implementation.
		Version php_0.1
	*/
	/*
		Constants for the BAViClient functions
	*/
	define('TRANSCRIPTION', '0');//Only audio transcription
	define('ANNOTATION', '1');//Only semantic notation of the text
	define('TRANSCRIPTION_AND_ANNOTATION', '2');//Transcriptnio and notation
	define('ANNOTATION_AND_RECOMMENDATION', '3');//Only semantic notation and recomendation
	define('TRANSCRIPTION_ANNOTATION_AND_RECOMMENDATION', '4');//Do the entire process
	define('SERVER_TRANSCRIPTION', 'transcription');
	define('SERVER_ANNOTATION', 'annotation');
	define('SERVER_RECOMMENDATION', 'recommendation');
	define('SERVER_CHECKFINISH', 'checkfinish');
	
	/*
		This class is used to represent a BAVi client. It has all the interaction functions with the server.
			Uses objects of the BAViRequest type.
		@author Miguel Alvim
		@version 0.1
	*/
	class BAViClient{
		private $restUrl = "RestServer/webresources/";
		/*
			Function used when the object is created
			@param $url the url of the BAVi server
		*/
		function __construct($url) { 
			$this->restUrl = $url."RestServer/webresources/";
        }		
		/*
			Function used to require the status of a requisition made with 'send'
			@param $req the requisition object. It needs to represent a requisition that has a 'internalid' metadata
		*/
		public function request($req) {
			$urlParameters = "internalid=".$req->getMetadata("internalid");
			$result = "";
				$url = $this->restUrl.SERVER_CHECKFINISH;
			return $this->makeConnection($url,$urlParameters);
		}		
		/*
			Function used to defined what of the BAVi services the requisition will use
			@param &$req requirement object with all the necessary data: IS PASSED AS REFERENCE
			@param $actions what action the server will peform
			@param $response if the server has to response
		*/
		public function send(&$req, $actions, $response){
			if($actions == TRANSCRIPTION) {
				$req->put("header", "1");
				$req->put("target", "asr");
				return $this->restConection($req, SERVER_TRANSCRIPTION, $response);
			}else
			if($actions == ANNOTATION) {
				$req->put("header", "1");
				$req->put("target", "ann");
				return $this->restConection($req, SERVER_ANNOTATION, $response);	
			}else
			if($actions == TRANSCRIPTION_AND_ANNOTATION) {
				$req->put("header", "2");
				$req->put("target", "asr");
				return $this->restConection($req, SERVER_TRANSCRIPTION, $response);
			}else
			if($actions == ANNOTATION_AND_RECOMMENDATION) {
				$req->put("header", "2");
				$req->put("target", "ann");
				return $this->restConection($req, SERVER_ANNOTATION, $response);
			}else
			if($actions == TRANSCRIPTION_ANNOTATION_AND_RECOMMENDATION) {
				$req->put("header", "3");
				$req->put("target", "asr");
				return $this->restConection($req, SERVER_TRANSCRIPTION, $response);
			}else
			return "BAVi actions not found";
		}
		/*
			Function used to defined what of the BAVi services the callback will use
			@param &$req requirement object with all the necessary data: IS PASSED AS REFERENCE
			@param $actions what action the server will peform
			@param $destiny destiny URL of the callback		
		*/
		public function sendCallback(&$req, $actions, $destiny){
			$result = "";
			if($actions == TRANSCRIPTION) {
				$req->put("header", "1");
				$req->put("target", "asr");
				$result = $this->restConection($req, SERVER_TRANSCRIPTION, true);
			}else
			if($actions == ANNOTATION) {
				$req->put("header", "1");
				$req->put("target", "ann");
				$result = $this->restConection($req, SERVER_ANNOTATION, true);	
			}else
			if($actions == TRANSCRIPTION_AND_ANNOTATION) {
				$req->put("header", "2");
				$req->put("target", "asr");
				$result = $this->restConection($req, SERVER_TRANSCRIPTION, true);
			}else
			if($actions == ANNOTATION_AND_RECOMMENDATION) {
				$req->put("header", "2");
				$req->put("target", "ann");
				$result = $this->restConection($req, SERVER_ANNOTATION, true);
			}else
			if($actions == TRANSCRIPTION_ANNOTATION_AND_RECOMMENDATION) {
				$req->put("header", "3");
				$req->put("target", "asr");
				$result = $this->restConection($req, SERVER_TRANSCRIPTION, true);
			}else{
				exit("BAVi actions not found");
			}
			$this->callBack($result, $destiny);
		}	
		/*
			This function is used to create the connection and send a POST to the url passed. USES THE CURL CLASS
			@param $url the url to send the POST
			@param $params parameters to the POST menssage
		*/
		private function makeConnection($url,$params){
			$curl = curl_init($url);
				echo curl_error($curl);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
				$result = curl_exec($curl);
				echo curl_error($curl);
				$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				// echo("Sending 'POST' request to URL : ". $url."<br>");
				// echo("Post parameters : " . $urlParameters."<br>");
				// echo("Response Code : " . $status."<br><br>");
			curl_close($curl);
			return $result;
		}
		/*
			This function is used to send POSTs requests to the BAVi server
			@param &$req requirement object with all the necessary data: IS PASSED AS REFERENCE
			@param $method what process is expected of the server
			@param $reponse boolean value, flagging if the server has to send a response			
		*/
		private function restConection(&$req,$method,$response){
			$responseText = "false";
			if($response){
				$responseText = "true";
			}
			$urlParameters = "media=".$req->generateJson()."&response=".$responseText;
			$url = $this->restUrl.$method;
			$result = $this->makeConnection($url,$urlParameters);
			$json = json_decode($result,true);
			if(is_array($json) && array_key_exists("requisitionID",$json)){
				$req->addMetadata("internalid", $json["requisitionID"]);
			}
			return $result;
		}
		/*
			This function is used make callbacks; Sends the results of the midia data to the server pointed by the user
			@param $result result of the data
			@param $destiny server to be send
		*/
		private function callBack($result, $destiny) {
			$urlParameters = $result;
			$url = $destiny;
			echo("Sending 'POST' request to URL : " . $url."<br>");
			echo("Post parameters : " . $urlParameters."<br>");
			echo("Response Code : " . $this->makeConnection($url,$urlParameters)."<br>");
		}	
	}
	/*
		This class is used to represent a BAVi request to the server.
		It is necessary to inform at least these elements with the put function:
			put("Content", "Conteudo do midia- Texto, Uri do video..");
			put("IdMedia", "ID da Midia");
			put("IdService", "ID do Servico");
		
		@author Miguel Alvim
		@version 0.1
	*/
	class BAViRequest{
		private $JSON;	
		private $MetaData;	
		function __construct() { 
			$this->JSON = array();
			$this->MetaData = array();
        }		
		/*
			This function is used to store the json elements
			@param $key json element to be added/modified
			@param $val new value
		*/
		public function put($key,$val){
			$this->JSON[$key] = $val;		
		}				
		/*
			This function is used to store metadata
			@param $key metadata to be added/modified
			@param $val new value
		*/
		public function addMetadata($key,$val){
			$this->MetaData[$key] = $val;		
		}		
		/*
			This function is used to obtain the json elements
			@param $key metadata to be retrieve
		*/
		public function getMetadata($key){
			return $this->MetaData[$key];
		}
		/*
			This function is used to print all the json elements; Used for debug
		*/
		public function printMetadata(){
			foreach($this->MetaData as $key => $value)
				echo $key."  ".$value."<br>"; 
		}
		/*
			Takes the elemenst inputed through the put function and creates a json with them.
		*/
		public function generateJson(){
			return json_encode(array_merge($this->JSON,$this->MetaData));
		}
	}
	/*
		Example of Usage
	*/
	$test = new BAViRequest;
	$test->put("content", "http://va05-cps.rnp.br/riotransfer/rnp/treinamentos/videoaulas/modulo_3/modulo_3.mp4");
	$test->put("idservice", "VideoTeste");
	$test->put("idmedia", "videoaula@RNP");
	$test->put("version", "php_0.1");
	$test->addMetadata("keyword", "noticia");
	$apiTest = new BAViClient("http://138.121.71.4:8082/");
	echo $apiTest->send($test,TRANSCRIPTION_ANNOTATION_AND_RECOMMENDATION, false);
	echo "<br><br>";
	echo $apiTest->request($test);
		// $test2 = new BAViRequest;
		// $test2->put("content", "http://va05-cps.rnp.br/riotransfer/rnp/treinamentos/videoaulas/modulo_3/modulo_3.mp4");
		// $test2->put("idservice", "VideoTeste");
		// $test2->put("idmedia", "videoaula@RNP");
		// $test2->put("version", "php_0.1");
		// $test2->addMetadata("keyword", "noticia");
		// $test2->addMetadata("internalid",'9215149657072806');
		// echo $apiTest->request($test2);	
?>