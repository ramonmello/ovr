<?php
	/*
		Implementation of the BAVi API.
		Based on the JAVA implementation.
		Version php_0.2
		@Author Miguel Alvim
	*/
	/*
		Constants for the BAViClient functions
	*/
	define('TRANSCRIPTION', '0');//Only audio transcription
	define('ANNOTATION', '1');//Only semantic notation of the text
	define('TRANSCRIPTION_AND_ANNOTATION', '2');//Transcriptnio and notation
	define('ANNOTATION_AND_RECOMMENDATION', '3');//Only semantic notation and recomemndation
	define('TRANSCRIPTION_ANNOTATION_AND_RECOMMENDATION', '4');//Do the entire process
	define('RECOMMENDATION', '5');//Returns recommendations based on keywords passed as extra metadata
	define('QUERY_VIDEO_ONLY', '6');//Querys for video content
	define('QUERY_TEXT_ONLY', '7');//Querys for text content
	define('QUERY_TEXT_AND_VIDEO', '8');//Querys for text and video content
	define('SERVER_TRANSCRIPTION', 'transcription');
	define('SERVER_ANNOTATION', 'annotation');
	define('SERVER_RECOMMENDATION', 'recommendation');
	define('SERVER_CHECKFINISH', 'checkfinish');
	define('SERVER_QUERY', 'query');
	
	/*
		This class is used to represent a BAVi client. It has all the interaction functions with the server.
			Uses objects of the BAViRequest type.
		@author Miguel Alvim
		@version 0.2
	*/
	class BAViClient{
		private $restUrl = "RestServer/webresources/";
		/*
			Function used when the object is created
			@param $url the url of the BAVi server
			@param $restServerName the name of the Rest Server.
		*/
		function __construct($url,$restServerName) { 
			//If there are no '/' on the end of url or the begining of resServerName, we ad it.
			if(strcmp(substr($url,-1),"/")!=0 && strcmp(substr($url,0),"/")){
				$url = $url."/";
			}
			$this->restUrl = $url.$restServerName."/webresources/";
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
			Function used to query the BAVi database of all the(or some of) media related to the keywords
			@param &$req requirement object with all the necessary data(no need for the 'content'; instead we need 'keywords' in the metadata): IS PASSED AS REFERENCE
			@param $type if you want text only, video only or both - NOT YET IN USE
		*/
		public function query(&$que, $type){
			return $this->restConnection($que, SERVER_QUERY, false);
			// switch($type){
				// case QUERY_VIDEO_ONLY:{
					// $que->put("lookfor", "0");//video only
					// return $this->restConnection($que, SERVER_QUERY, $response);
				// }break;
				// case QUERY_TEXT_ONLY:{
					// $que->put("lookfor", "1");//text only
					// return $this->restConnection($que, SERVER_QUERY, $response);
				// }break;
				// case QUERY_TEXT_AND_VIDEO:{
					// $que->put("lookfor", "2");//video & text
					// return $this->restConnection($que, SERVER_QUERY, $response);
				// }break;		
			// }
			// return "No correct query type detected.";
		}			
		/*
			Function used to defined what of the BAVi services the requisition will use
			@param &$req requirement object with all the necessary data: IS PASSED AS REFERENCE
			@param $actions what action the server will peform
			@param $response if the server has to respond(true = wait to the process do be over, false = no response from the server aside from the
			request ID)
		*/
		public function send(&$req, $actions, $response){
			switch($actions){
				case TRANSCRIPTION:{
					$req->put("header", "1");
					$req->put("target", "asr");
					return $this->restConnection($req, SERVER_TRANSCRIPTION, $response);
				}break;
				case ANNOTATION:{
					$req->put("header", "1");
					$req->put("target", "ann");
					return $this->restConnection($req, SERVER_ANNOTATION, $response);	
				}break;
				case TRANSCRIPTION_AND_ANNOTATION:{
					$req->put("header", "1");
					$req->put("target", "asr");
					return $this->restConnection($req, SERVER_TRANSCRIPTION, $response);
				}break;
				case ANNOTATION_AND_RECOMMENDATION:{
					$req->put("header", "2");
					$req->put("target", "ann");
					return $this->restConnection($req, SERVER_ANNOTATION, $response);
				}break;
				case TRANSCRIPTION_ANNOTATION_AND_RECOMMENDATION:{
					$req->put("header", "3");
					$req->put("target", "asr");
					return $this->restConnection($req, SERVER_TRANSCRIPTION, $response);
				}break;
				case RECOMMENDATION:{
					$req->put("header", "3");
					$req->put("target", "asr");
					return $this->restConnection($req, SERVER_RECOMMENDATION, $response);
				}break;
			}
			return "{\"error\": \"BAVi actions not found\"}";
		}
		/*
			Function used to defined what of the BAVi services the callback will use
			@param &$req requirement object with all the necessary data: IS PASSED AS REFERENCE
			@param $actions what action the server will peform
			@param $destiny destiny URL of the callback		
		*/
		public function sendCallback(&$req, $actions, $destiny){
			$result = "";
			switch($actions){
				case TRANSCRIPTION:{
					$req->put("header", "1");
					$req->put("target", "asr");
					$result = $this->restConnection($req, SERVER_TRANSCRIPTION, $response);
				}break;
				case ANNOTATION:{
					$req->put("header", "1");
					$req->put("target", "ann");
					$result = $this->restConnection($req, SERVER_ANNOTATION, $response);	
				}break;
				case TRANSCRIPTION_AND_ANNOTATION:{
					$req->put("header", "1");
					$req->put("target", "asr");
					$result = $this->restConnection($req, SERVER_TRANSCRIPTION, $response);
				}break;
				case ANNOTATION_AND_RECOMMENDATION:{
					$req->put("header", "2");
					$req->put("target", "ann");
					$result = $this->restConnection($req, SERVER_ANNOTATION, $response);
				}break;
				case TRANSCRIPTION_ANNOTATION_AND_RECOMMENDATION:{
					$req->put("header", "3");
					$req->put("target", "asr");
					$result = $this->restConnection($req, SERVER_TRANSCRIPTION, $response);
				}break;
				case RECOMMENDATION:{
					$req->put("header", "3");
					$req->put("target", "asr");
					$result = $this->restConnection($req, SERVER_RECOMMENDATION, $response);
				}break;
				default:{
					exit("BAVi actions not found");
				}
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
			curl_close($curl);
			return $result;
		}
		/*
			This function is used to send POSTs requests to the BAVi server
			@param &$req requirement object with all the necessary data: IS PASSED AS REFERENCE
			@param $method what process is expected of the server
			@param $reponse boolean value, flagging if the server has to send a response			
		*/
		private function restConnection(&$req,$method,$response){
			$responseText = "false";
			if($response){
				$responseText = "true";
			}
			if($method!=SERVER_QUERY)
				$urlParameters = "media=".$req->generateJson()."&response=".$responseText;
			else
				$urlParameters = $req->generateMessage();
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
		
		@author Miguel Alvim
		@version 0.2
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
			// $key = strtolower($key);
			$this->JSON[$key] = $val;		
		}				
		/*
			This function is used to store metadata
			@param $key metadata to be added/modified
			@param $val new value
		*/
		public function addMetadata($key,$val){
			// $key = strtolower($key);
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
		public function printData(){
			foreach($this->JSON as $key => $value)
				echo $key."  ".$value."<br>"; 
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
		This class is used to represent a BAVi query to the server.
		
		@author Miguel Alvim
		@version 0.2
	*/
	class BAViQuery{
		private $Params;	
		private $ReturnSize;	
		function __construct() { 
			$this->JSON = array();
			$ReturnSize =-1;
        }		
		/*
			This function is used to store the Params elements
			@param $key Params element to be added/modified
			@param $val new value
		*/
		public function put($key,$val){
			// $key = strtolower($key);
			$this->Params[$key] = $val;		
		}	
		public function setReturnSize($val){
			if($val>0)
				$this->Params[$key] = $val;		
		}	
		/*
			Takes the elemenst inputed through the put function and creates a string with them, already formated for the POST message
		*/
		public function generateMessage(){
			$Parameters = "";
			$NotFirst = false;
			foreach($this->Params as $key => $value){
				if($NotFirst){
					$Parameters = $Parameters."&";
				}else{
					$NotFirst = true;
				}
				$Parameters = $Parameters.$key."=".$value;
			}
			return $Parameters;
		}
	}
?>