<?PHP

error_reporting(1);
ini_set('display_errors', true );

if (!isset ($_REQUEST['action'])) $_REQUEST['action']='';
$result = array();

include_once ('fmREST.php');

$host = 'myhost.domain.com';
$db = 'fmREST-sample';
$user = 'fmrest';
$pass = 'paradise';
$layout = 'sample';

$fm = new fmREST ($host, $db, $user, $pass, $layout);
$fm -> show_debug = false; //turn this to true or "html" to show automatically. We're manually including debug information with <print_r ($fm->debug_array);>
$fm -> secure = true; //not required - defaults to true

/* uncomment the following two lines when working with FileMaker 17 Server */
// $fm -> version = "v1";
// $fm -> fmversion = 17;


if ($_REQUEST['action'] == 'createrecord') {
	//create record
	$record['Text1'] = $_REQUEST['Text1'];
	$record['Text2'] =  $_REQUEST['Text2'];
	$data['fieldData'] =  $record;
	//	$data['script'] = $_REQUEST['Script'];
	//	$data['script.param'] = $_REQUEST['Parameter'];
	$result = $fm -> createRecord ($data);
}

elseif ($_REQUEST['action'] == 'deleterecord') {
	//delete record
	$recordId = $_REQUEST['recordid'];
	//	$data['script'] = $_REQUEST['Script'];
	//	$data['script.param'] = $_REQUEST['Parameter'];
	$result = $fm -> deleteRecord ($recordId, $data); 
}

elseif ($_REQUEST['action'] == 'editrecord') {
	//edit record
	$recordId = $_REQUEST['recordid'];
	$record['Text1'] = $_REQUEST['Text1'];
	$record['Text2'] =  $_REQUEST['Text2'];

	$data['fieldData'] =  $record;

	$result = $fm -> editRecord ($recordId, $data); 
} 

elseif ($_REQUEST['action'] == 'getrecord') {
	//get record
	$recordId = $_REQUEST['recordid'];
	//	$data['script'] = $_REQUEST['Script'];
	//	$data['script.param'] = $_REQUEST['Parameter'];
	$result = $fm -> getRecord ($recordId, $parameters); 
}

elseif ($_REQUEST['action'] == 'getrecords') {
	//get records
	//	$parameters['_limit'] = 1;
	//	$data['script'] = $_REQUEST['Script'];
	//	$data['script.param'] = $_REQUEST['Parameter'];
	$result = $fm -> getRecords ($parameters, "sample"); 

}

elseif ($_REQUEST['action'] == 'uploadcontainer') {
	//upload container
	$recordId = $_REQUEST['recordid'];
	$fieldName = 'Container';
	$file = $_FILES['file'];
	$result = $fm -> uploadContainer ($recordId, $fieldName, $file ); 
}

elseif ($_REQUEST['action'] == 'findrecords') {
	//find records
	$request1['Text1'] = $_REQUEST['Text1'];
	$query = array ($request1);
	$data['query'] = $query;
	//	$data['limit'] = 2;
	//	$data['script'] = $_REQUEST['Script'];
	//	$data['script.param'] = $_REQUEST['Parameter'];
	$result = $fm -> findRecords ($data); 
}

elseif ($_REQUEST['action'] == 'setglobalfields') {
	//set Global field(s)
	$fields['sample::Global'] = $_REQUEST['Global'];
	$data['globalFields'] = $fields;
	$result = $fm -> setGlobalFields ($data); 
}

elseif ($_REQUEST['action'] == 'productinfo') {
	//Layout Names(s)
	$result = $fm -> productInfo (); 
}
elseif ($_REQUEST['action'] == 'databasenames') {
	//Layout Names(s)
	$result = $fm -> databaseNames (); 
}
elseif ($_REQUEST['action'] == 'layoutnames') {
	//Layout Names(s)
	$result = $fm -> layoutNames (); 
}
elseif ($_REQUEST['action'] == 'scriptnames') {
	//Script Names(s)
	$result = $fm -> scriptNames (); 
}
elseif ($_REQUEST['action'] == 'layoutmetadata') {
	//Layout metadata - require layout name
	$result = $fm -> layoutMetadata (); 
}
elseif ($_REQUEST['action'] == 'oldlayoutmetadata') {
	//Old Layout metadata - require layout name
	$result = $fm -> oldLayoutMetadata (); 
}
elseif ($_REQUEST['action'] == 'executescript') {
	//execute script - requires script name. parameter & require layout name optional
	$result = $fm -> executeScript ($_REQUEST['Script'],$_REQUEST['Parameter']); 
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
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

<title>fmREST.php</title>
</head>
<body>
<div>
	<div style="margin: 50px;margin-top:10px;width:350px;">
		fmREST.php - <a href="https://sosimplesoftware.com/fmrest">soSIMPLEsoftware.com</a> 
		<form method='post' enctype="multipart/form-data">
		
			<div class="border rounded p-3">
				<input name='recordid' class="form-control" placeholder="Record ID">
				<input name='Text1' class="form-control" placeholder="Text1">
				<input name='Text2' class="form-control" placeholder="Text2">
				<input name='Global' class="form-control" placeholder="Global">
				<input name='Script' class="form-control" placeholder="Script">
				<input name='Parameter' class="form-control" placeholder="Script Parameter">

				<div class="custom-file">
				  <input type="file" class="custom-file-input" name="file">
				  <label class="custom-file-label" for="customFile">Choose a file</label>
				</div>
			</div>

<div class="accordion" id="accordionExample">
  <div class="card">
    <div class="card-header" id="headingOne">
      <h2 class="mb-0">
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
         Get Metadata (FM18 only)
        </button>
      </h2>
    </div>

    <div id="collapseOne" class="collapse collapsed" aria-labelledby="headingOne" data-parent="#accordionExample">
      <div class="card-body">
			  <input type="radio" name="action" value="productinfo" > Product Info <small> </small><br>
			  <input type="radio" name="action" value="databasenames" > Database Names <small> </small><br>
			  <input type="radio" name="action" value="layoutnames" > Layout Names <small> </small><br>
			  <input type="radio" name="action" value="scriptnames" > Script Names <small> </small><br>
			  <input type="radio" name="action" value="layoutmetadata" > Layout Metadata <small> </small><br>
			  <input type="radio" name="action" value="oldlayoutmetadata" > Old Layout Metadata <small> </small><br><br>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingTwo">
      <h2 class="mb-0">
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Work with Records
        </button>
      </h2>
    </div>
    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
      <div class="card-body">
			  <input type="radio" name="action" value="getrecords" > Get Records<br>
			  <input type="radio" name="action" value="createrecord" > Create Record <small>(uses Text1 & Text2)</small><br>
			  <input type="radio" name="action" value="getrecord" > Get Single Record by ID <small>(uses Record ID)</small><br>
			  <input type="radio" name="action" value="editrecord" > Edit Record <small>(uses Record ID & Text1 & Text2)</small><br>
			  <input type="radio" name="action" value="deleterecord" > Delete Record <small>(uses Record ID)</small><br>
			  <input type="radio" name="action" value="duplicaterecord" > Duplicate Record <small>(uses Record ID)</small><br>
			  <input type="radio" name="action" value="findrecords" > Find Records <small>(uses Text1)</small><br><br>
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header" id="headingThree">
      <h2 class="mb-0">
        <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
          Other Utilities
        </button>
      </h2>
    </div>
    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
      <div class="card-body">
			  <input type="radio" name="action" value="executescript" > Execute Script <small> Script Name and Script Parameter (FM18 only) </small><br><br>

			  <input type="radio" name="action" value="uploadcontainer" > Upload Container <small>(uses Record ID & File)</small><br><br>

			  <input type="radio" name="action" value="setglobalfields" > Set Global Fields <small>(uses Global)</small><br><br>
			  <input type="radio" name="action" value="login" > Log In Manually <small>(happens automatically with all above actions)</small><br>
			  <input type="radio" name="action" value="logout" > Log Out Manually <small>(will automatically log out in 15 minutes)</small><br>
      </div>
    </div>
  </div>
</div>
<button type="submit" class="btn btn-primary btn-lg btn-block">Submit</button>
					</form>
		<?PHP if (!empty ($result)) {
			echo "<div class='alert ";
				if ($result['messages'][0]['code'] == 0) echo "alert-success"; 
				else echo "alert-danger";
			echo "' role='alert'>";
			echo "Action: " . $_REQUEST['action'] . "<br />";
			echo "Result: " . $result['messages'][0]['message']; 	
// 			if ($result['messages'][0]['code'] == 0) {
// 				echo "<hr><pre>";
// 				print_r ($result['response']);  
// 				echo "</pre>";
// 			}


			echo "</div>";
		} ?>
	</div>
</div>

<div style="margin-left:10px;" class="bg-light">
	<div class="bg-info">
		<strong>Result:</strong>
		<pre>
			<?PHP print_r ($result); ?>
		</pre>
	</div>
	<strong>Request:</strong>
	<pre>
		POST: 
		<?PHP print_r ($_POST); ?>
		<hr>	
		GET: 
		<?PHP print_r ($_GET); ?>
		<hr>
		FILES: 
		<?PHP print_r ($_FILES); ?>
		<hr>
		COOKIES: 
		<?PHP print_r ($_COOKIE); ?>
	</pre>
	<hr>
	<strong>Debug Log:</strong>
	<pre>
		<?PHP print_r ($fm->debug_array); ?>
	</pre>
</div>
</body>
</html>
