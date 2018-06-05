<?php
/********************************************
fmREST Class

The MIT License (MIT)

Copyright 2017 Paradise Partners, Inc DBA soSIMPLE Software

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
- TODO: easier methods to set data and finds (eg. $fm -> addRequest())
- TODO: check environment: ssl/https, first call on page, other from our web page?
- TODO: port (can currently be appended to $host)
- TODO: logout function to specify token for manual operation
- TODO: fail after initial login attempt for each function
- TODO: add layout as a parameter to each function


http://www.sosimplesoftware.com/fmrest.php

2017-04-15 Created Ken d'Oronzio
2018-01-03 Check for variables 
2018-01-04 Destroy cookie variable during logout
2018-04-26 Began FM17 changes
ADDED: if login() function is unsuccessful during function calls, return login error
ADDED: login() should confirm token/connection is valid and refresh if it's not
ADDED: SSL certificate host checks
ADDED: Upload Container

********************************************/

class fmREST {
    public $host = '';
    public $db = '';
    public $user = '';
    public $pass = '';
	
    public $layout = ''; //no longer required for authentication, but used in most functions - 
    // should add layout as a parameter to: findrecords, getrecords, editrecord, createrecord, getrecord, deleterecord, uploadcontainer
    // last parameter and optional, should default to $this -> layout
    

	public $secure = true;
	public $debug_array = array();
	
	
	function createRecord ($data) {
		$login = $this->login();

		$url = "/layouts/" . rawurlencode($this->layout) . "/records" ;
		$result = $this->callCURL ($url, 'POST', $data);

		$this->debug ('create record data : ', $data);
		$this->debug ('createRecord pass 1', $result);
		if ( isset($result['messages'][0]['code']) &&  $result['messages'][0]['code'] == 952 ) { //not connected (token invalid)
			$_COOKIE ['token']=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) return $login;
			$result = $this->callCURL ($url, 'POST', $data);
			$this->debug ('createRecord pass 2', $result);
		}
		
		return $result; //error, foundcount, json and array
	}
	
	function deleteRecord ($id, $scripts) {
		$login = $this->login();

		$url = "/layouts/" . rawurlencode($this->layout) . '/records/' . $id ;
		$result = $this->callCURL ($url, 'DELETE',  $scripts);

		$this->debug ('deleteRecord ' . $id . ' pass 1', $result);
		if ( isset($result['messages'][0]['code']) &&  $result['messages'][0]['code'] == 952 ) { //not connected (token invalid)
			$_COOKIE ['token']=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) return $login;
			$result = $this->callCURL ($url, 'DELETE', $scripts);
			$this->debug ('deleteRecord ' . $id . ' pass 2', $result);
		}
		return $result; //error
	}	

	function editRecord ($id, $record) {
		$login = $this->login();

		$url = "/layouts/" . rawurlencode($this->layout) . '/records/' . $id ;
		$result = $this->callCURL ($url, 'PATCH', $record);
		
		$this->debug ('update record data ' . $id . ': ', $record);
		$this->debug ('editRecord ' . $id . ' pass 1', $result);

		if ( isset($result['messages'][0]['code']) &&  $result['messages'][0]['code'] == 952 ) { //not connected (token invalid)
			$_COOKIE ['token']=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) return $login;
			$result = $this->callCURL ($url, 'PATCH', $record);
			$this->debug ('editRecord ' . $id . ' pass 2', $result);
			
		}
		
		return $result; //error, foundcount, json and array
	}
	
	function getRecord ($id, $parameters= array ()) {
		$login = $this->login();

		$url = "/layouts/" . rawurlencode($this->layout) . '/records/' . $id ;
		$result = $this->callCURL ($url, 'GET', $parameters);

		$this->debug ('getRecord ' . $id . ' pass 1', $result);

		if ( isset($result['messages'][0]['code']) &&  $result['messages'][0]['code'] == 952 ) { //not connected (token invalid)
			$_COOKIE ['token']=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) return $login;
			$result = $this->callCURL ($url, 'GET', $parameters);
			$this->debug ('getRecord ' . $id . ' pass 2', $result);
		}
		return $result; //error, foundcount, json and array
	}	

	function getRecords ($parameters=array()) {
		$login = $this->login();

		$url = "/layouts/" . rawurlencode($this->layout) . "/records";
		$result = $this->callCURL ($url, 'GET', $parameters);

		$this->debug ('getRecords pass 1',$result);
		
		if ( isset($result['messages'][0]['code']) && $result['messages'][0]['code'] == 952) { //not connected (token invalid)
			$_COOKIE ['token']=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) return $login;
			$result = $this->callCURL ($url, 'GET', $parameters);			
			$this->debug ('getRecords pass 2',$result);
		} 
		
		return $result; //error, foundcount, json and array
	}
	
	function uploadContainer ($id, $fieldName, $file, $repetition = 1) { //not connected (token invalid)
		$login = $this->login();

		$url = "/layouts/" . rawurlencode($this->layout) . '/records/' . $id . '/containers/' . $fieldName . '/' . $repetition ;
		$cfile = curl_file_create($file['tmp_name'], $file['type'], $file['name']);
		$file = array ('upload' => $cfile);

		$result = $this->callCURL ($url, 'POSTFILE', $file);

		$this->debug ('file ', $file);
		$this->debug ('uploadContainer ' . $id . ' pass 1', $result);
		if ( isset($result['messages'][0]['code']) &&  $result['messages'][0]['code'] == 952 ) { //not connected (token invalid)
			$_COOKIE ['token']=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) return $login;
			$result = $this->callCURL ($url, 'POSTFILE', '', $file);
			$this->debug ('uploadContainer ' . $id . ' pass 2', $result);
		}
		return $result; //error
	}	
	
	function findRecords ($data) { 
		$login = $this->login();

		$url = "/layouts/" . rawurlencode($this->layout) . "/_find";
		$result = $this->callCURL ($url, 'POST', $data);

		$this->debug ('findRecords pass 1' , $result);
		
		if ( isset($result['messages'][0]['code']) && $result['messages'][0]['code'] == 952 ) { //not connected (token invalid)
			$_COOKIE ['token']=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) return $login;
			$result = $this->callCURL ($url, 'POST', $data);
			$this->debug ('findRecords pass 2' , $result);
		}
		
		return $result; 
	}

	function setGlobalFields ($fields) {
		$login = $this->login();

		$url =  "/globals" ;
		$result = $this->callCURL ($url, 'PATCH', $fields);

		$this->debug ('setGlobalFields pass 1', $result);

		if ( isset($result['messages'][0]['code']) && $result['messages'][0]['code'] == 952 ) { //not connected (token invalid)
			$_COOKIE ['token']=''; 
			$login = $this->login();
			if ( $login['messages'][0]['code'] != 0) return $login;
			$result = $this->callCURL ($url, 'PATCH', $fields);
			$this->debug ('setGlobalFields pass 1', $result);
		}
		return $result; //error, foundcount, json and array
	}
	
	
	function login () {		
		$this->debug ('login start cookie',$_COOKIE);
		if (!empty ($_COOKIE['token'])) {
			$this->debug ('login existing token', $_COOKIE['token']);
			//this return needs to be changed to 'messages' format
			return (array('response'=> array ('token'=>$_COOKIE['token']),'messages' => [array('code'=>0,'message'=>'Already logged in.')])); 
		}

		$url =  "/sessions" ;
		$header = "Authorization: Basic " . base64_encode ($this->user . ":" . $this->pass);

		$result = $this->callCURL ($url, 'POST', array(), array ($header));
		
		$this->debug ('login result',$result);

		if (isset ($result['response']['token'])) {
			$token = $result['response']['token'];
		
			//using cookie: 
				//has to be set before any content, with the header
				//time should be refreshed each time we successfully hit a function - maybe within callCURL()
			setcookie("token", $token, time()+(14*60), '','',true,true);  
			$_COOKIE['token'] = $token;
		}

		$this->debug ('login end cookie',$_COOKIE);								
		return $result; //error

	}	
	
	function logout () {
		if (empty ($_COOKIE['token'])) {
			$this->debug ('logout no token');
			//this return needs to be changed to 'messages' format
			return (array ('response'=> array(), 'messages' => [array('code'=>0,'message'=>'No connection')])); 
		}
	
		$url = "/sessions/" . $_COOKIE['token'] ;
		$result = $this->callCURL ($url, 'DELETE');

		$this->debug ('logout result', $result);

		setcookie("token", '');  
		$_COOKIE ['token']=''; 
		return $result; //error
	}
	

	function callCURL ($url, $method, $payload='', $header=array()) {
		$url = "https://" . $this->host . "/fmi/data/v1/databases/" . rawurlencode($this->db) . $url;

		$this->debug ("pre-payload: ", $payload);
				 
		if ($method == 'POSTFILE') $contentType = 'multipart/form-data';
		else $contentType = 'application/json';
						
	    $ch = curl_init(); 
		
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);         //follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         //return the transfer as a string 
		if ($this -> secure)  {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);         //verify SSL CERT 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);         //verify SSL CERT 
		} else {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);         //don't verify SSL CERT 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);         //don't verify SSL CERT 
		}		
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1); //Don'T use cache

        if (!empty ($_COOKIE['token'])) {
        	$this->debug ('not empty token on call', $_COOKIE['token']);
			$header = array_merge ($header, array ('Authorization:Bearer '. $_COOKIE['token'] , 'Content-Type: '.$contentType));
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $header );
        } else {
			$header = array_merge ($header, array ('Content-Type: '.$contentType));
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
		}
		
		
		$this->debug ("payload: ", $payload);

		if ( isset ($payload) && is_array($payload)) {
			if ($method == 'GET' || $method == 'DELETE') {
				$url = $url . '?' . http_build_query($payload);
				unset ($payload);
			} elseif ($method != 'POSTFILE') {
				$payload = json_encode ($payload);
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
  	  		 $this->debug ('header', $header);

		 $this->debug ('url', $url);
		 $this->debug ("call error: ", $error);
		 $this->debug ("call result: ", $result);
		 $this->debug ("call info: ", $info);

	    if (! empty ($result)) {
	    	$result = json_decode ($result, true);
			return $result;
		} 
		elseif ( ! empty ($info['http_code'])) return (array ('response'=> array(), 'messages' => [array('code'=>$info['http_code'],'message'=>'HTTP Error')])); 
		elseif ( ! empty ($error)) return (array ('response'=> array(), 'messages' => [array('code'=>-1,'message'=>$error)]));
		else return (array ('response'=> array(), 'messages' => [array('code'=>-1,'message'=>'Empty Result')]));

	}
		
    function __construct($host='',$db='',$user='',$pass='', $layout='') {
        if (!empty ($host))$this->host = $host;
        if (!empty ($db)) $this->db = $db;
        if (!empty ($layout))$this->layout = $layout;
        if (!empty ($user))$this->user = $user;
        if (!empty ($pass))$this->pass = $pass;
	
		return true;
	}
	
	function __destruct() {
		global $debug;
		if ($debug) {
			echo "\nDEBUGGING ON: \n";
			print_r ($this->debug_array);
		}	
	}

	function debug ($label, $value = '') {
			$this -> debug_array [$label] = $value;
	}	
}
?>
