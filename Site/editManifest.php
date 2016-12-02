<?php
#Start the session
    session_start();
    if(!isset($_SESSION['username'])) {
        header('Location: login.php');
    }

    include('config/connection.php');
    include('config/setup.php');
    include('checkPerson.php');

    if ($mysqli->connect_errno) {
        printf("Connect failed: %s\n", $mysqli->connect_error);
        exit();
    }

    if($_GET['mid']){
        $mid = $_GET['mid'];
        $_SESSION['mid'] = $mid;
    }else{
        $mid = $_SESSION['mid'];
    }
    print "mid: ".$mid ."<br>\n";
    print "Session[mid]: " . $_SESSION['mid'] . "<br>\n";

    $sql = "SELECT * FROM manifest WHERE MID = '$mid'";


    if($result = mysqli_query($dbc, $sql)){
        if(mysqli_num_rows($result)) {
            $data=mysqli_fetch_assoc($result);
        }else{
            echo "<script type='text/javascript'>alert('ERROR: bad Mysql result')</script>";
        }
        $StandardVersions = $data['StandardVersions'];
        $Creator = $data['Creator'];    //PID for person table to retrieve first name and last name
        $UploadComment = $data['UploadComment'];
        $UploadTitle = $data['UploadTitle'];
        $DsTitle = $data['DsTitle'];
        $DsTimeInterval = $data['DsTimeInterval'];
        $RetrievedTimeInterval = $data['RetrievedTimeInterval'];
        $DsDateCreated = $data['DsDateCreated'];
        $JsonFile = $data['JsonFile'];
        $DataSet = $data['DataSet'];
    }

    $sql = "SELECT * FROM person WHERE PID='$Creator'";
    if($result = mysqli_query($dbc, $sql)){
        if(mysqli_num_rows($result)) {
            $data=mysqli_fetch_assoc($result);
        }else{
            echo "<script type='text/javascript'>alert('ERROR: bad Mysql result')</script>";
        }
        
        $FirstName = $data['FirstName'];
        $LastName = $data['LastName'];
    }







    if($_POST) {
        $StandardVersions = htmlspecialchars($_POST['StandardVersions']);
        if(strlen($StandardVersions) > 255){
            echo "<script type='text/javascript'>alert('ERROR: Standard Versions cannot be > 255 chars')</script>";
        }
        $FirstName = htmlspecialchars($_POST['FirstName']);
        if(strlen($FirstName) > 255 || strlen($FirstName) == 0){
            echo "<script type='text/javascript'>alert('ERROR: First Name cannot be > 255 or 0 chars')</script>";
        }        
        $LastName = htmlspecialchars($_POST['LastName']);
        if(strlen($LastName) > 255 || strlen($LastName) == 0){
            echo "<script type='text/javascript'>alert('ERROR: Last Name cannot be > 255 or 0 chars')</script>";
        }        
        $UploadComment = htmlspecialchars($_POST['UploadComment']);
        if(strlen($UploadComment) > 1000){
            echo "<script type='text/javascript'>alert('ERROR: Upload Comment cannot be > 1000 chars')</script>";
        }        
        $UploadTitle = htmlspecialchars($_POST['UploadTitle']);
        if(strlen($UploadTitle) > 1000 || strlen($UploadTitle) == 0){
            echo "<script type='text/javascript'>alert('ERROR: Upload Title cannot be > 1000 chars or 0 chars')</script>";
        }        
        $DsTitle = htmlspecialchars($_POST['DsTitle']);
        if(strlen($DsTitle) > 1000){
            echo "<script type='text/javascript'>alert('ERROR: Dataset Title cannot be > 1000 chars')</script>";
        }        
        $DsTimeInterval = htmlspecialchars($_POST['DsTimeInterval']);
        if(strlen($DsTimeInterval) > 255){
            echo "<script type='text/javascript'>alert('ERROR: Dataset Time Interval cannot be > 255 chars')</script>";
        }
        $RetrievedTimeInterval = htmlspecialchars($_POST['RetrievedTimeInterval']);
        if(strlen($RetrievedTimeInterval) > 255){
            echo "<script type='text/javascript'>alert('ERROR: Retrieved Time Interval cannot be > 255 chars')</script>";
        }        
        $DsDateCreated = date('Y-m-d', strtotime($_POST['DsDateCreated']));
        $JsonFile = htmlspecialchars($_POST['JsonFile']);
        if(strlen($JsonFile) > 255){
            echo "<script type='text/javascript'>alert('ERROR: JSON File URL cannot be > 255 chars')</script>";
        }        
        $DataSet = htmlspecialchars($_POST['DataSet']);
        if(strlen($DataSet) > 255 || strlen($DataSet) == 0){
            echo "<script type='text/javascript'>alert('ERROR: Dataset URL cannot be > 255 or 0 chars')</script>";
        }        

        //$sql = "SELECT PID FROM person WHERE FirstName='$FirstName' AND LastName='$LastName'";

        $PID = checkPerson($dbc, $FirstName, $LastName); //from checkPerson.php
        $Creator = $PID; //just to make it obvious in the sql statement
        	$sql = "UPDATE manifest SET StandardVersions='$StandardVersions', Creator='$Creator', UploadDate=now(), UploadComment='$UploadComment',
            UploadTitle='$UploadTitle', DsTitle='$DsTitle', DsTimeInterval='$DsTimeInterval', RetrievedTimeInterval='$RetrievedTimeInterval',
            DsDateCreated='$DsDateCreated', JsonFile='$JsonFile', DataSet='$DataSet' WHERE MID='$mid'";

        
	if($result = mysqli_query($dbc, $sql)){	//Should test this for success
                echo "<script type='text/javascript'>alert('Manifest successfully edited! Redirecting...')</script>";
//                echo "<script type='text/javascript'>window.location = 'browseManifests.php'</script>";
        }else{
               echo "<script type='text/javascript'>alert('Database insertion error! Manifest edit failed!')</script>";
               printf("dbc error: %s\n", $dbc->error);
        }
/* //this block commented out with implementation of checkPerson function
        if($result = mysqli_query($dbc, $sql)){
            if(mysqli_num_rows($result)) {
                $data=mysqli_fetch_assoc($result);
            }else{
                echo "<script type='text/javascript'>alert('Author does not exist! Add person to database before adding manifest authored by that person!')</script>";
            }
            $PID = $data['PID'];    //This PID from person table gives us the Creator field we need for foreign key reference
            $Creator = $PID; //just to make it obvious in the sql statement
            $sql = "INSERT INTO manifest VALUES('$StandardVersions', DEFAULT, $Creator, now(), 
                '$UploadComment', '$UploadTitle', '$DsTitle', '$DsTimeInterval', '$RetrievedTimeInterval', 
                '$DsDateCreated', '$JsonFile', '$DataSet')"; //DEFAULT for MID since it auto-increments; can be changed depending on final implementation
	       if($result = mysqli_query($dbc, $sql)){	//Should test this for success
                echo "<script type='text/javascript'>alert('Manifest created! Redirecting...')</script>";
                echo "<script type='text/javascript'>window.location = 'browseManifests.php'</script>";
           }else{
               echo "<script type='text/javascript'>alert('Database insertion error! Manifest creation failed!')</script>";
               printf("dbc error: %s\n", $dbc->error);
           }
        }else{
            echo "<script type='text/javascript'>alert('Mysqli query error! Manifest creation failed!')</script>";
            printf("dbc error: %s\n", $dbc->error);
        }*/
    }
?>
							

<!DOCTYPE html>
<html>
	<head>
		<title>Edit Manifest</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
		<?php include('config/css.php'); ?>
		<?php include('config/js.php'); ?>		
</head>
<body class='indigo lighten-5'>
	<?php include(D_TEMPLATE.'/navigation.php'); ?>
	
	<div>
		<div class="section" id="index-banner">
    <div class="white z-depth-1 container" style='padding: 1% 1% 1% 1%;'>
      <br><br>
      <div class="row">
      		<div class="col s4">
      			<h3>Edit Manifest</h3>
      		</div>

				<form action="editManifest.php" method="post" role="form">
					<div class="input-field col s12 form-group">						
						<label for="FirstName" >Author's First Name</label>
						<input id="FirstName" type="text" class="validate" name="FirstName" value="<?php echo "$FirstName" ?>">
			    	</div>
			    	<div class="input-field col s12 form-group">		        	
						<label for="LastName" type="text">Author's Last Name</label>
						<input id="LastName" type="text" class="validate" name="LastName" value="<?php echo "$LastName" ?>">
					</div>
					<div class="input-field col s12 form-group">						
						<label for="StandardVersions" >Standard Versions</label>
						<input id="StandardVersions" type="text" class="validate" name="StandardVersions" value="<?php echo "$StandardVersions" ?>">
			    	</div>
					<div class="input-field col s12 form-group">						
						<label for="UploadTitle" >Upload Title</label>
						<input id="UploadTitle" type="text" class="validate" name="UploadTitle" value="<?php echo "$UploadTitle" ?>">
			    	</div>
			    	<div class="input-field col s12 form-group">		        	
						<label for="UploadComment" type="text">Upload Comment</label>
						<input id="UploadComment" type="text" class="validate" name="UploadComment" value="<?php echo "$UploadComment" ?>">
					</div>
					<div class="input-field col s12 form-group">						
						<label for="DsTitle" >Dataset Title</label>
						<input id="DsTitle" type="text" class="validate" name="DsTitle" value="<?php echo "$DsTitle" ?>">
			    	</div>
					<div class="input-field col s12 form-group">						
						<label for="DsTimeInterval" >Dataset Time Interval</label>
						<input id="DsTimeInterval" type="text" class="validate" name="DsTimeInterval" value="<?php echo "$DsTimeInterval" ?>">
			    	</div>
					<div class="input-field col s12 form-group">						
						<label for="RetrievedTimeInterval" >Retrieved Time Interval</label>
						<input id="RetrievedTimeInterval" type="text" class="validate" name="RetrievedTimeInterval" value="<?php echo "$RetrievedTimeInterval" ?>">
			    	</div> 
					<div class=" col s12 form-group">						
						<label for="DsDateCreated" >Dataset Date Created</label>
						<input id="DsDateCreated" type="date" class="validate" name="DsDateCreated" value="<?php echo "$DsDateCreated" ?>">
			    	</div>					
                    <div class="input-field col s12 form-group">						
						<label for="JsonFile" >JSON File URL</label>
						<input id="JsonFile" type="text" class="validate" name="JsonFile" value="<?php echo "$JsonFile" ?>">
			    	</div>
					<div class="input-field col s12 form-group">						
						<label for="DataSet" >DataSet URL</label>
						<input id="DataSet" type="text" class="validate" name="DataSet" value="<?php echo "$DataSet" ?>">
			    	</div>
                    
					<div class="col s4">
						<button type="submit" value="submit" class="waves-effect waves-light btn">Commit Edit</button>
                        <a href='browseManifests.php' class="waves-effect waves-light btn">Cancel</a>
					</div>
				</form>

    </div>
      <div class="row center">
      </div>
      <br><br>
    </div>
  </div>
    <?php
        
          $query->close();
          $dbc->close();
        
    ?>
		
	</div>
	
	<?php include(D_TEMPLATE.'/footer.php'); ?>
  </body>
</html>