<?PHP

error_reporting(1);
ini_set('display_errors', true );

if (!isset ($_REQUEST['action'])) $_REQUEST['action']='';
$result = array();

include_once ('fmREST.php');

 $debug=1;

$host = 'myserver.com';
$db = 'my database name';
$layout = 'my layout name';
$user = 'account name';
$pass = 'password';

$fm = new fmREST ($host, $db, $user, $pass, $layout);


if ($_REQUEST['action'] == 'createrecord') {
	//create record
	$record['First'] = $_REQUEST['first'];
	$record['Last'] =  $_REQUEST['last'];
	$data['fieldData'] =  $record;
//	$data['script'] = 'duplicate record';
//	$data['script.param'] = 'new parameter';
	$result = $fm -> createRecord ($data);
}

elseif ($_REQUEST['action'] == 'deleterecord') {
	//delete record
	$recordId = $_REQUEST['recordid'];
	$data['script'] = 'duplicate record';
	$data['script.param'] = 'new parameter';
	$result = $fm -> deleteRecord ($recordId, $data); 
}

elseif ($_REQUEST['action'] == 'editrecord') {
	//edit record
	$recordId = $_REQUEST['recordid'];
	$record['First'] = $_REQUEST['first'];
	$record['Last'] =  $_REQUEST['last'];

	$data['fieldData'] =  $record;

	$result = $fm -> editRecord ($recordId, $data); 
} 

elseif ($_REQUEST['action'] == 'getrecord') {
	//get record
	$recordId = $_REQUEST['recordid'];
//	$parameters['script'] = 'duplicate record';
//	$parameters['script.param'] = 'new parameter';
	$result = $fm -> getRecord ($recordId, $parameters); 
}

elseif ($_REQUEST['action'] == 'getrecords') {
	//get records
//	$parameters['_limit'] = 1;
//	$parameters['script'] = 'duplicate record';
//	$parameters['script.param'] = 'new parameter';
	$result = $fm -> getRecords ($parameters); 

}

elseif ($_REQUEST['action'] == 'uploadcontainer') {
	//upload container
	$recordId = $_REQUEST['recordid'];
	$fieldName = 'container';
	$file = $_FILES['file'];
	$result = $fm -> uploadContainer ($recordId, $fieldName, $file ); 
}

elseif ($_REQUEST['action'] == 'findrecords') {
	//find records
	$request1['first'] = $_REQUEST['first'];
	$query = array ($request1);
	$data['query'] = $query;
//	$data['limit'] = 2;
//	$data['script'] = 'find script';
//	$data['script.param'] = 'new parameter';
	$result = $fm -> findRecords ($data); 
}

elseif ($_REQUEST['action'] == 'setglobalfields') {
	//set global field(s)
	$fields['TEST::global'] = $_REQUEST['global'];
	$data['globalFields'] = $fields;
	$result = $fm -> setGlobalFields ($data); 
}




elseif ($_REQUEST['action'] == 'login') {
	//login
	$result = $fm -> login (); 
}

elseif ($_REQUEST['action'] == 'logout') {
	//logout
	$result = $fm -> logout (); 
}

?>

<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<title>fmREST.php</title>
</head>
<body>
<div style="margin: 50px;">
<h3>fmREST.php</h3>
<h4>soSIMPLEsoftware.com</h4>
	<div style="margin: 50px;">
		<form method='post' enctype="multipart/form-data">
		
			<!--User <input name='user'></input><br />
			Password <input name='password'></input><br /-->
			Record ID <input name='recordid'></input><br />
			First <input name='first'></input><br />
			Last <input name='last'></input><br />
			Global <input name='global'></input><br />
			File <input name='file' type='file'></input><br />
			<br />

			  <!--input type="radio" name="action" value="login" > Login<br>
			  <input type="radio" name="action" value="logout" > Logout<br-->
			  <input type="radio" name="action" value="createrecord" > Create Record <small>(uses First & Last)</small><br>
			  <input type="radio" name="action" value="deleterecord" > Delete Record <small>(uses Record ID)</small><br>
			  <input type="radio" name="action" value="editrecord" > Edit Record <small>(uses Record ID & First & Last)</small><br>
			  <input type="radio" name="action" value="getrecord" > Get Record <small>(uses Record ID)</small><br>
			  <input type="radio" name="action" value="getrecords" > Get Records<br>
			  <input type="radio" name="action" value="uploadcontainer" > Upload Container <small>(uses Record ID & File)</small><br><br>
			  <input type="radio" name="action" value="findrecords" > Find Records <small>(uses First)</small><br><br>
			  <input type="radio" name="action" value="setglobalfields" > Set Global Fields <small>(uses Global)</small><br><br>
			  			  
			  <input type="radio" name="action" value="login" > Log In Manually <small>(happens automatically with all above actions)</small><br>
			  <input type="radio" name="action" value="logout" > Log Out Manually <small>(will automatically log out in 15 minutes)</small><br>
			<br/>
			
			<input type='submit' />
		</form>
	</div>
</div>

Request:
<pre>
FILES: 
<?PHP print_r ($_FILES); ?>

POST: 
<?PHP print_r ($_POST); ?>
	
GET: 
<?PHP print_r ($_GET); ?>

COOKIES: 
<?PHP print_r ($_COOKIE); ?>
</pre>

Result:
<pre>
	<?PHP print_r ($result); ?>
</pre>
</body>
</html>
