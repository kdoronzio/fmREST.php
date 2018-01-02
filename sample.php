<?PHP

error_reporting(E_ALL);
ini_set('display_errors', true );

if (!isset ($_REQUEST['action'])) $_REQUEST['action']='';
$result = array();

include_once ('fmREST.php');

// $debug=1;

$host = 'localhost';
$db = 'Contacts';
$layout = 'Contacts';
$user = 'admin';
$pass = 'paradise';

$fm = new fmREST ($host, $db, $layout, $user, $pass);


if ($_REQUEST['action'] == 'createrecord') {
	//create record
	$record['First'] = $_REQUEST['first'];
	$record['Last'] =  $_REQUEST['last'];
	$data['data'] =  $record;
	$result = $fm -> createRecord ($data);
}

elseif ($_REQUEST['action'] == 'editrecord') {
	//edit record
	$recordId = $_REQUEST['recordid'];
	$record['First'] = $_REQUEST['first'];
	$record['Last'] =  $_REQUEST['last'];

	$data['data'] =  $record;

	$result = $fm -> editRecord ($recordId, $data); 
} 

elseif ($_REQUEST['action'] == 'getrecord') {
	//get record
	$recordId = $_REQUEST['recordid'];
	$result = $fm -> getRecord ($recordId); 
}

elseif ($_REQUEST['action'] == 'getrecords') {
	//get records
	$parameters = "offset=1&range=50";
	$result = $fm -> getRecords ($parameters); 

}

elseif ($_REQUEST['action'] == 'findrecords') {
	//find records
	$request1['first'] = $_REQUEST['first'];
	$query = array ($request1);
	$criteria['query'] = $query;
	$result = $fm -> findRecords ($criteria); 
}

elseif ($_REQUEST['action'] == 'deleterecord') {
	//delete record
	$recordId = $_REQUEST['recordid'];
	$result = $fm -> deleteRecord ($recordId); 
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
		<form method='post'>
		
			<!--User <input name='user'></input><br />
			Password <input name='password'></input><br /-->
			Record ID <input name='recordid'></input><br />
			First <input name='first'></input><br />
			Last <input name='last'></input><br />
			<br />

			  <!--input type="radio" name="action" value="login" > Login<br>
			  <input type="radio" name="action" value="logout" > Logout<br-->
			  <input type="radio" name="action" value="createrecord" > Create Record<br>
			  <input type="radio" name="action" value="deleterecord" > Delete Record<br>
			  <input type="radio" name="action" value="getrecord" > Get Record<br>
			  <input type="radio" name="action" value="getrecords" > Get Records<br>
			  <input type="radio" name="action" value="findrecords" > Find Records<br>
			  <input type="radio" name="action" value="editrecord" > Edit Record<br>
			  <!--input type="radio" name="action" value="setglobal" > Set Global<br-->
			<br/>
			
			<input type='submit' />
		</form>
	</div>
</div>

Request:
<pre>
	<?PHP print_r ($_REQUEST); ?>
</pre>

Result:
<pre>
	<?PHP print_r ($result); ?>
</pre>
</body>
</html>
