<?php
/********************************************
fmREST Class

The MIT License (MIT)

Copyright 2018 Paradise Partners, Inc DBA soSIMPLE Software

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Simplifies PHP connections to FileMaker 16's REST-based Data API.
- Autoconnect when running functions
- Save token for 15 minutes to reuse
- In case of broken token, reconnect and run function again
- Connection must be established before any output is made
- Requires secure (https) connection

- TODO: oAuth login
- TODO: easier methods to set data and finds (eg. $fm -> addRequest()) or utility functions ($fm->buildQuery())
- TODO: check environment: ssl/https, first call on page, fmversion, other from our web page?


http://www.sosimplesoftware.com/fmrest.php

2017-04-15 Created Ken d'Oronzio
2018-01-03 Check for variables 
2018-01-04 Destroy cookie variable during logout
2018-04-26 Began FM17 changes
ADDED: if login() function is unsuccessful during function calls, return login error
ADDED: login() should confirm token/connection is valid and refresh if it's not
ADDED: SSL certificate host checks
ADDED: Upload Container
2019-03-28 Began FM18 changes
ADDED: all new FMS18 functions
CHANGED: login now requires empty JSON object instead of empty JSON array
ADDED: version and fmversion tests
ADDED: errorcheck and throw error turned into functions
CHANGED: variable for cookie token name, default to fmtoken
CHANGED: layout can be set in initial setup ("new fmREST") OR in each relevent function
CHANGED: show_debug is now a class property instead of a global variable - can send "HTML" or true, or pull $debug_array
CHANGED: logout function can specify token id for manual operation/non-secure environments
DOC: port can be appended to $host
CHANGE: fail after initial login attempt for each function


********************************************/

class fmREST {
    public $host = '';
    public $db = '';
    public $user = '';
    public $pass = '';
	
    public $version = 'vLatest'; 
    public $fmversion = 18; 
    public $layout = '';    

	public $secure = true;
	public $token_name = 'fmtoken';
	public $show_debug = false;
	
	public $debug_array = array();
	
	function productInfo () {
		if ($this->fmversion < 18) return $this->throwRestError (-1, "This function is not supported in FileMaker 17");
		$url = "https://" . $this->host . "/fmi/data/".$this->version."/productInfo";
		$result = $this->callCURL ($url, 'GET');
		$this->updateDebug ('productInfo result', $result);
		return $result;			
	}

	function databaseNames () { //doesn't work when you're logged in (because both basic & bearer headers are sent)
		if ($this->fmversion < 18) return $this->throwRestError (-1, "This function is not supported in FileMaker 17");
		$url = "https://" . $this->host . "/fmi/data/".$this->version."/databases";
		$header = "Authorization: Basic " . base64_encode ($this->user . ":" . $this->pass);
		$result = $this->callCURL ($url, 'GET', array(), array ($header));
		$this->updateDebug ('databaseNames result', $result);
		return $result;			
	}
	
	function layoutNames () {
		if ($this->fmversion < 18) return $this->throwRestError (-1, "This function is not supported in FileMaker 17");
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/";
		$result = $this->callCURL ($url, 'GET');	
		$this->updateDebug ('layoutNames result pass 1', $result);

		$result = $this->checkValidResult($result);
		if (!$result){
			$result = $this->callCURL ($url, 'GET');	
			$this->updateDebug ('layoutNames result pass 2', $result);
		}
		
		return $result;	
	}
	
	function scriptNames () {
		if ($this->fmversion < 18) return $this->throwRestError (-1, "This function is not supported in FileMaker 17");
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/scripts/";
		$result = $this->callCURL ($url, 'GET');	
		$this->updateDebug ('scriptNames result pass 1', $result);

		$result = $this->checkValidResult($result);
		if (!$result) {
			$result = $this->callCURL ($url, 'GET');	
			$this->updateDebug ('scriptNames result pass 2', $result);
		}
		return $result;	
	}
	
	function layoutMetadata ( $layout = NULL ) {
		if ($this->fmversion < 18) return $this->throwRestError (-1, "This function is not supported in FileMaker 17");
 		if (empty ($layout)) $layout = $this->layout;
	
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/". rawurlencode($layout);
		$result = $this->callCURL ($url, 'GET');	
		$this->updateDebug ('layoutMetadata result pass 1', $result);

		$result = $this->checkValidResult($result);
		if (!$result){ 
			$result = $this->callCURL ($url, 'GET');	
			$this->updateDebug ('layoutMetadata result pass 2', $result);
		}
		return $result;	
	}
	
	function oldLayoutMetadata ( $layout = NULL ) {
		if ($this->fmversion < 18) return $this->throwRestError (-1, "This function is not supported in FileMaker 17");
 		if (empty ($layout)) $layout = $this->layout;
	
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/". rawurlencode($layout) . "/metadata";
		$result = $this->callCURL ($url, 'GET');	
		$this->updateDebug ('oldLayoutMetadata pass 1', $result);

		$result = $this->checkValidResult($result);
		if (!$result) {
			$result = $this->callCURL ($url, 'GET');	
			$this->updateDebug ('oldLayoutMetadata pass 2', $result);
		}

		return $result;	
	}

	function createRecord ($data, $layout=NULL) {
		if (empty ($layout)) $layout = $this->layout;
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/" . rawurlencode($layout) . "/records" ;
		$result = $this->callCURL ($url, 'POST', $data);

		$this->updateDebug ('create record data : ', $data);
		$this->updateDebug ('createRecord pass 1', $result);
		
		$result = $this->checkValidResult($result);
		if (!$result){
			$result = $this->callCURL ($url, 'POST', $data);
			$this->updateDebug ('createRecord pass 2', $result);
		}
		
		return $result; //error, foundcount, json and array
	}
	
	function deleteRecord ($id, $scripts, $layout=NULL) {
		if (empty ($layout)) $layout = $this->layout;
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/" . rawurlencode($layout) . '/records/' . $id ;
		$result = $this->callCURL ($url, 'DELETE',  $scripts);

		$this->updateDebug ('deleteRecord ' . $id . ' pass 1', $result);
		$result = $this->checkValidResult($result);
		if (!$result) {			
			$result = $this->callCURL ($url, 'DELETE', $scripts);
			$this->updateDebug ('deleteRecord ' . $id . ' pass 2', $result);
		}
		return $result; //error
	}	

	function editRecord ($id, $record, $layout=NULL) {
		if (empty ($layout)) $layout = $this->layout;
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/" . rawurlencode($layout) . '/records/' . $id ;
		$result = $this->callCURL ($url, 'PATCH', $record);
		
		$this->updateDebug ('update record data ' . $id . ': ', $record);
		$this->updateDebug ('editRecord ' . $id . ' pass 1', $result);

		$result = $this->checkValidResult($result);
		if (!$result) {			
			$result = $this->callCURL ($url, 'PATCH', $record);
			$this->updateDebug ('editRecord ' . $id . ' pass 2', $result);		
		}
		
		return $result; //error, foundcount, json and array
	}
	
	function getRecord ($id, $parameters= array (), $layout=NULL) {
		if (empty ($layout)) $layout = $this->layout;
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/" . rawurlencode($layout) . '/records/' . $id ;
		$result = $this->callCURL ($url, 'GET', $parameters);

		$this->updateDebug ('getRecord ' . $id . ' pass 1', $result);

		$result = $this->checkValidResult($result);
		if (!$result) {			
			$result = $this->callCURL ($url, 'GET', $parameters);
			$this->updateDebug ('getRecord ' . $id . ' pass 2', $result);
		}
		return $result; //error, foundcount, json and array
	}	

	function executeScript ( $scriptName, $scriptParameter, $layout=NULL, $id=NULL ) {
		if ($this->fmversion == 18) {
			if (empty ($layout)) $layout = $this->layout;
			$login = $this->login();
			if (!$this->checkValidLogin($login)) return $login;
	
			$url = "/layouts/" . rawurlencode($layout) . '/script/' . rawurlencode($scriptName);
			$parameters['script.param'] = $scriptParameter;
			$result = $this->callCURL ($url, 'GET', $parameters);
	
			$this->updateDebug ('executeScript ' . $scriptName . ' pass 1', $result);
	
			$result = $this->checkValidResult($result);
			if (!$result) {			
				$result = $this->callCURL ($url, 'GET', $parameters);
				$this->updateDebug ('executeScript ' . $scriptName . ' pass 2', $result);
			}
			return $result; //error, foundcount, json and array		
		}
		if ($this->fmversion == 17) {
			if (empty ($layout)) $layout = $this->layout;
			if (empty($id)) return $this->throwRestError (-1, "This function call without id is not supported in FileMaker 17");
			$login = $this->login();
			if (!$this->checkValidLogin($login)) return $login;
	
			$url = "/layouts/" . rawurlencode($layout) . '/records/' . rawurlencode($id);
			$parameters['script'] = $scriptName;
			$parameters['script.param'] = $scriptParameter;
			$result = $this->callCURL ($url, 'GET', $parameters);
	
			$this->updateDebug ('executeScript ' . $scriptName . ' pass 1', $result);
	
			$result = $this->checkValidResult($result);
			if (!$result) {			
				$result = $this->callCURL ($url, 'GET', $parameters);
				$this->updateDebug ('executeScript ' . $scriptName . ' pass 2', $result);
			}
			return $result; //error, foundcount, json and array	
		}
	}	
	
	function getRecords ($parameters=array(), $layout=NULL) {
		if (empty ($layout)) $layout = $this->layout;
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/" . rawurlencode($layout) . "/records";
		$result = $this->callCURL ($url, 'GET', $parameters);

		$this->updateDebug ('getRecords pass 1',$result);
		
		$result = $this->checkValidResult($result);
		if (!$result) {			
			$result = $this->callCURL ($url, 'GET', $parameters);			
			$this->updateDebug ('getRecords pass 2',$result);
		} 
		
		return $result; //error, foundcount, json and array
	}
	
	function uploadContainer ($id, $fieldName, $file, $repetition = 1, $layout=NULL) {
		if (empty ($layout)) $layout = $this->layout;
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/" . rawurlencode($layout) . '/records/' . $id . '/containers/' . rawurlencode($fieldName) . '/' . $repetition ;
		$cfile = curl_file_create($file['tmp_name'], $file['type'], $file['name']);
		$file = array ('upload' => $cfile);

		$result = $this->callCURL ($url, 'POSTFILE', $file);

		$this->updateDebug ('file ', $file);
		$this->updateDebug ('uploadContainer ' . $id . ' pass 1', $result);
		$result = $this->checkValidResult($result);
		if (!$result) {			
			$result = $this->callCURL ($url, 'POSTFILE', '', $file);
			$this->updateDebug ('uploadContainer ' . $id . ' pass 2', $result);
		}
		return $result; //error
	}	
	
	function findRecords ($data, $layout=NULL) { 
		if (empty ($layout)) $layout = $this->layout;
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url = "/layouts/" . rawurlencode($layout) . "/_find";
		$result = $this->callCURL ($url, 'POST', $data);

		$this->updateDebug ('findRecords pass 1' , $result);
		
		$result = $this->checkValidResult($result);
		if (!$result) {			
			$result = $this->callCURL ($url, 'POST', $data);
			$this->updateDebug ('findRecords pass 2' , $result);
		}
		
		return $result; 
	}

	function setGlobalFields ($fields) {
		$login = $this->login();
		if (!$this->checkValidLogin($login)) return $login;

		$url =  "/globals" ;
		$result = $this->callCURL ($url, 'PATCH', $fields);

		$this->updateDebug ('setGlobalFields pass 1', $result);

		$result = $this->checkValidResult($result);
		if (!$result) {			
			$result = $this->callCURL ($url, 'PATCH', $fields);
			$this->updateDebug ('setGlobalFields pass 1', $result);
		}
		return $result; //error, foundcount, json and array
	}
	
	
	function login () {		
		$this->updateDebug ('login start cookie',$_COOKIE);
		if (!empty ($_COOKIE[$this->token_name])) {
			$this->updateDebug ('login existing token', $_COOKIE[$this->token_name]);
			return (array('response'=> array ('token'=>$_COOKIE[$this->token_name]),'messages' => [array('code'=>0,'message'=>'Already have a token.')])); 
		}

		$url =  "/sessions" ;
		$header = "Authorization: Basic " . base64_encode ($this->user . ":" . $this->pass);
		$result = $this->callCURL ($url, 'POST', array(), array ($header));	
		$this->updateDebug ('login result',$result);

		if (isset ($result['response']['token'])) {
			$token = $result['response']['token'];
			setcookie($this->token_name, $token, time()+(14*60), '','',true,true);  
			$_COOKIE[$this->token_name] = $token;
		}

		$this->updateDebug ('login end cookie',$_COOKIE);								
		return $result;

	}	
	
	function logout ( $token = NULL ) {
		if (empty ($token)) $token = $_COOKIE[$this->token_name];
		
		if (empty ($token)) {
			$this->updateDebug ('logout no token');
			return ($this->throwRestError(0,'No Token'));
		}
	
		$url = "/sessions/" . $token ;
		$result = $this->callCURL ($url, 'DELETE');

		$this->updateDebug ('logout result', $result);

		if ($token == $_COOKIE[$this->token_name]) {
			setcookie($this->token_name, '');  
			$_COOKIE [$this->token_name]=''; 
		}
		return $result; 
	}
	

	function callCURL ($url, $method, $payload='', $header=array()) {
		if ( substr ($url, 0, 4) != 'http') $url = "https://" . $this->host . "/fmi/data/".$this->version."/databases/" . rawurlencode($this->db) . $url;

		$this->updateDebug ("pre-payload: ", $payload);
				 
		if ($method == 'POSTFILE') $contentType = 'multipart/form-data';
		else $contentType = 'application/json';
						
	    $ch = curl_init(); 
		
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);         //follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         //return the transfer as a string 
		if ($this -> secure)  {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);         //verify SSL CERT 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);         //verify SSL CERT 
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);         //don't verify SSL CERT 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);         //don't verify SSL CERT 
		}		
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1); //Don'T use cache

        if (!empty ($_COOKIE[$this->token_name]) && empty (preg_grep('/^Authorization/i', $header))) {
        	$this->updateDebug ('not empty token on call', $_COOKIE[$this->token_name]);
			$header = array_merge ($header, array ('Authorization:Bearer '. $_COOKIE[$this->token_name] , 'Content-Type: '.$contentType));
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $header );
        } else {
			$header = array_merge ($header, array ('Content-Type: '.$contentType));
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
		}
		
		
		$this->updateDebug ("payload: ", $payload);

		if ( isset ($payload) && is_array($payload)) {
			if ($method == 'GET' || $method == 'DELETE') {
				$url = $url . '?' . http_build_query($payload);
				unset ($payload);
			} elseif ($method != 'POSTFILE') {
				if (empty($payload))$payload = json_encode ($payload, JSON_FORCE_OBJECT);
				else $payload = json_encode ($payload) ;
			}
			
		}

		if ( isset ($payload))curl_setopt($ch, CURLOPT_POSTFIELDS, $payload ); 
		

	    if ($method == 'POSTFILE') $method = 'POST';
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url); 

		$result = curl_exec($ch); 
        $error = curl_error ($ch);
        $info = curl_getinfo ($ch);

	     curl_close($ch);  
		 $this->updateDebug ('header', $header);

		 $this->updateDebug ('url', $url);
		 $this->updateDebug ("call error: ", $error);
		 $this->updateDebug ("call result: ", $result);
		 $this->updateDebug ("call info: ", $info);

	    if (! empty ($result)) {
	    	$result = json_decode ($result, true);
			return $result;
		} 
		elseif ( ! empty ($info['http_code'])) $this->throwRestError($info['http_code'],'HTTP Error '.$info['http_code']);
		elseif ( ! empty ($error)) return $this->throwRestError(-1,$error);
		else return $this->throwRestError(-1,'Empty Result');
	}
	
	function throwRestError ($num,$message) {
		return (array ('response'=> array(), 'messages' => [array('code'=>$num,'message'=>$message)]));	
	}

	function checkValidResult($result){
		if ( isset($result['messages'][0]['code']) &&  $result['messages'][0]['code'] != 0 ) { 
			$_COOKIE [$this->token_name]=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) {
				$this->updateDebug ('checkValidResult', '2nd login failed');
				return $login;			
			}
			$this->updateDebug ('checkValidResult', '2nd login succeeded');
			return false;
		}	
		$this->updateDebug ('checkValidResult', 'valid result');
	 	return $result;
	}

	function checkValidLogin($result){
		if ( isset($result['messages'][0]['code']) &&  $result['messages'][0]['code'] != 0 ) { //any error in result
			$this->updateDebug ('Failed initial login', $result);
			return false;
		}	
		$this->updateDebug ('Succeeded initial login', $result);	 	
	 	return true;
	}
		
    function __construct($host='',$db='',$user='',$pass='', $layout='') {
        if (!empty ($host))$this->host = $host;
        if (!empty ($db)) $this->db = $db;
        if (!empty ($user))$this->user = $user;
        if (!empty ($pass))$this->pass = $pass;
        if (!empty ($layout))$this->layout = $layout;
	
		return true;
	}
	
	function __destruct() {
		if (strtoupper ($this->show_debug) == "HTML") {
			echo "<br><strong>DEBUGGING ON: </strong><br>";
			echo "<pre>";
			print_r ($this->debug_array);
			echo "</pre>";
		} 
		elseif ($this->show_debug) {
			echo "\nDEBUGGING ON: \n";
			print_r ($this->debug_array);
		}	
	}

	function updateDebug ($label, $value = '') {
			$this -> debug_array [$label] = $value;
	}	
}
?>
