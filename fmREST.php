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
- TODO: easier methods to set data and finds
- TODO: other methods to save token between calls: storage?
- TODO: if login function is unsuccessful during function calls, return login error

http://www.sosimplesoftware.com/fmrest.php

2017-04-15 Created Ken d'Oronzio
********************************************/

class fmREST {
    public $host = '';
    public $db = '';
    public $layout = '';
    public $user = '';
    public $pass = '';
	
	public $debug_array = array();
	
	
	function findRecords ($criteria) {
		$this->login();

		$url = "https://" . $this->host . "/fmi/rest/api/find/" . rawurlencode($this->db) . "/" . rawurlencode($this->layout) ;
		$result = $this->callCURL ($url, 'POST', $criteria);

		$this->debug ('findRecords pass 1' , $result);
		
		if ( isset($result['errorCode']) && $result ['errorCode'] == 952 ) {
			$_COOKIE ['token']=''; 
			$this->login();
			$result = $this->callCURL ($url, 'POST', $criteria);
			$this->debug ('findRecords pass 2' , $result);
		}
		
		return $result; //error, foundcount, json and array
	}

	function getRecords ($parameters='') {
		$this->login();

		$url = "https://" . $this->host . "/fmi/rest/api/record/" . rawurlencode($this->db) . "/" . rawurlencode($this->layout);
		$result = $this->callCURL ($url, 'GET', $parameters);

		$this->debug ('getRecords pass 1',$result);
		
		if ( isset($result['errorCode']) && $result ['errorCode'] == 952 ) {
			$_COOKIE ['token']=''; 
			$this->login();
			$result = $this->callCURL ($url, 'GET');			
			$this->debug ('getRecords pass 2',$result);
		}
		
		return $result; //error, foundcount, json and array
	}
	
	function editRecord ($id, $record) {
		$this->login();

		$url = "https://" . $this->host . "/fmi/rest/api/record/" . rawurlencode($this->db) . "/" . rawurlencode($this->layout) . '/' . $id ;
		$result = $this->callCURL ($url, 'PUT', $record);
		
		$this->debug ('update record data ' . $id . ': ', $record);
		$this->debug ('editRecord ' . $id . ' pass 1', $result);

		if ( isset($result['errorCode']) &&  $result ['errorCode'] == 952 ) {
			$_COOKIE ['token']=''; 
			$this->login();
			$result = $this->callCURL ($url, 'PUT', $record);
			$this->debug ('editRecord ' . $id . ' pass 2', $result);
			
		}
		
		return $result; //error, foundcount, json and array
	}
	
	function createRecord ($record) {
		$this->login();

		$url = "https://" . $this->host . "/fmi/rest/api/record/" . rawurlencode($this->db) . "/" . rawurlencode($this->layout) ;
		$result = $this->callCURL ($url, 'POST', $record);

		$this->debug ('create record data : ', $record);
		$this->debug ('createRecord pass 1', $result);
		if ( isset($result['errorCode']) &&  $result ['errorCode'] == 952 ) {
			$_COOKIE ['token']=''; 
			$this->login();
			$result = $this->callCURL ($url, 'POST', $record);
			$this->debug ('createRecord pass 2', $result);
		}
		
		return $result; //error, foundcount, json and array
	}
	
	function getRecord ($id, $parameters='') {
		$this->login();

		$url = "https://" . $this->host . "/fmi/rest/api/record/" . rawurlencode($this->db) . "/" . rawurlencode($this->layout) . '/' . $id ;
		$result = $this->callCURL ($url, 'GET');

		$this->debug ('getRecord ' . $id . ' pass 1', $result);

		if ( isset($result['errorCode']) &&  $result ['errorCode'] == 952 ) {
			$_COOKIE ['token']=''; 
			$this->login();
			$result = $this->callCURL ($url, 'GET', $parameters);
			$this->debug ('getRecord ' . $id . ' pass 2', $result);
		}
		return $result; //error, foundcount, json and array
	}	

	function deleteRecord ($id) {
		$this->login();

		$url = "https://" . $this->host . "/fmi/rest/api/record/" . rawurlencode($this->db) . "/" . rawurlencode($this->layout) . '/' . $id ;
		$result = $this->callCURL ($url, 'DELETE');

		$this->debug ('deleteRecord ' . $id . ' pass 1', $result);
		if ( isset($result['errorCode']) &&  $result ['errorCode'] == 952 ) {
			$_COOKIE ['token']=''; 
			$this->login();
			$result = $this->callCURL ($url, 'DELETE');
			$this->debug ('deleteRecord ' . $id . ' pass 1', $result);
		}
		return $result; //error
	}	
	
	function setGlobalFields ($fields) {
		$this->login();

		$url = "https://" . $this->host . "/fmi/rest/api/global/" . rawurlencode($this->db) . "/" . rawurlencode($this->layout) ;
		$result = $this->callCURL ($url, 'PUT', $fields);

		$this->debug ('setGlobalFields pass 1', $result);

		if ( isset($result['errorCode']) && $result ['errorCode'] == 952 ) {
			$_COOKIE ['token']=''; 
			$this->login();
			$result = $this->callCURL ($url, 'PUT', $fields);
			$this->debug ('setGlobalFields pass 1', $result);
		}
		return $result; //error, foundcount, json and array
	}
	
	
	function login () {		
		$this->debug ('login start cookie',$_COOKIE);
		if (!empty ($_COOKIE['token'])) {
			$this->debug ('login existing token', $_COOKIE['token']);
			return (array('token'=>$_COOKIE['token'],'errorCode'=>0,'result'=>'Existing')); 
		}

		$url = "https://" . $this->host . "/fmi/rest/api/auth/" . rawurlencode($this->db) ;
		$payload =  array ("user" => $this->user, "password" => $this->pass, "layout" => $this->layout);

		$result = $this->callCURL ($url, 'POST', $payload);
		
		$this->debug ('login result',$result);

		if (isset ($result['token'])) {
			$token = $result['token'];
		
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
			return array('errorCode'=>0,'result'=>'No connection'); 
		}
	
		$url = "https://" . $this->host . "/fmi/rest/api/auth/" . rawurlencode($this->db) ;
		$result = $this->callCURL ($url, 'DELETE');

		$this->debug ('logout result', $result);
		return $result; //error
	}
	

	function callCURL ($url, $method, $payload='') {
		if (is_array ($payload)) $payload = json_encode ($payload);
	
	    $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);         //follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         //return the transfer as a string 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);         //don't verify SSL CERT 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);         //don't verify SSL CERT 
		curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE); //Don'T use cache
         
        if (!empty ($_COOKIE['token'])) {
        	$this->debug ('not empty token on call', $_COOKIE['token']);
        	curl_setopt ($ch, CURLOPT_HTTPHEADER, array ('FM-Data-token:'. $_COOKIE['token'] , 'Content-Type:application/json'));
        } else curl_setopt ($ch, CURLOPT_HTTPHEADER, array ('Content-Type:application/json'));
		if (!empty ($payload)) {
			if ($method == 'GET') $url = $url . '?' . $payload;
			else curl_setopt($ch, CURLOPT_POSTFIELDS, $payload ); 
		}

	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url); 

		$result = curl_exec($ch); 
        $error = curl_error ($ch);
        $info = curl_getinfo ($ch);

	     curl_close($ch);  
  	  
		 $this->debug ('url', $url);
		 $this->debug ("call error: ", $error);
		 $this->debug ("call result: ", $result);
		 $this->debug ("call info: ", $info);

       return json_decode($result, true);    

	}
		
    function __construct($host='',$db='',$layout='',$user='',$pass='') {
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
