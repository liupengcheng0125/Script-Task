<?php
$servername = "127.0.0.1";
$username = "springstudent";
$password = "springstudent";
$dbname = "myDB";
 
//set up command line directives
$shortopts = "uph";

$longopts  = array(
    "file:",     
    "create_table",    
    "dry_run",        
    "help",           
);
$options = getopt($shortopts, $longopts);
//var_dump($options);

if (array_key_exists('u', $options)) {
    echo "MySQL username is " . $username . "\n";
}

if (array_key_exists('p', $options)) {
    echo "MySQL password is " . $password . "\n";
}

if (array_key_exists('h', $options)) {
    echo "MySQL host is " . $servername . "\n";
}

if (array_key_exists('help', $options)) {
    echo "    --file [csv file name] – this is the name of the CSV to be parsed\n 
    --create_table – this will cause the MySQL users table to be built (and no further action will be taken)\n
    --dry_run – this will be used with the --file directive in the instance that we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered.\n
    -u – MySQL username\n
    -p – MySQL password\n
    -h – MySQL host\n";
}

if (array_key_exists('file', $options) and !array_key_exists('dry_run', $options)) {
    $filename = $options["file"];
    $data = load_data($filename,$servername,$username,$password,$dbname);
    insert_data($data,$servername,$username,$password,$dbname);
}

if (array_key_exists('create_table', $options)) {
	create_table($servername,$username,$password,$dbname);
}

if (array_key_exists('dry_run', $options) and array_key_exists('file', $options)) {
	$filename = $options["file"];
    create_database($servername,$username,$password);
    create_table($servername,$username,$password,$dbname);
    $data = load_data($filename,$servername,$username,$password,$dbname);
}



// create database if not exist
function create_database($servername,$username,$password){
	// create connection
	$conn = new mysqli($servername, $username, $password);
	 
	// test connection
	if ($conn->connect_error) {
	    die("connection failed: " . $conn->connect_error);
	} 
	echo "Database connection successfully\n";

	$sql = "CREATE DATABASE IF NOT EXISTS myDB";
	if (mysqli_query($conn, $sql)) {
	    //echo "create database successfully\n"; 
	} else {
	    echo "Error creating database: " . mysqli_error($conn);
	}
	mysqli_close($conn);
}

// build and rebuild the table if --create_table is called
function create_table($servername,$username,$password,$dbname){
	$conn = new mysqli($servername, $username, $password, $dbname);
	$sql = "CREATE TABLE IF NOT EXISTS users ( 
	name VARCHAR(30) NOT NULL,
	surename VARCHAR(30) NOT NULL,
	email VARCHAR(50) PRIMARY KEY
	)";

	if ($conn->query($sql) === TRUE) {
	    echo "Table users created successfully\n";
	} else {
	    echo "Error creating table: " . $conn->error;
	}

	$sql = "DELETE FROM users";
	if ($conn->query($sql) === TRUE) {
	    
	} else {
	    echo "Error deleting table: " . $conn->error;
	}

	mysqli_close($conn);
}

// load data from csv and insert into the database if the --file[csvfile] is called
function load_data($filename,$servername,$username,$password,$dbname){
	//load data from users.csv
	$row = 1;
	$stored_data = [];
	if (($handle = fopen($filename, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	        $num = count($data);
	        //echo "$num fields in line $row:\n";
	        $row++;
	        if($num == 3){
	        	array_push($stored_data, $data);
	        }
	    }
	    //var_dump($stored_data);
	    fclose($handle);
	echo "Load data successfully\n";
	return $stored_data;
	}
}

function insert_data($stored_data,$servername,$username,$password,$dbname){
	//insert into database
	$conn = new mysqli($servername, $username, $password, $dbname);

	$exist_email = [];
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}


	$sql = "SELECT email FROM users";
	$result = $conn->query($sql);
	 
	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()) {
	        array_push($exist_email,$row["email"]);
	    }
	} else {
	    
	}


	for($c = 1; $c < sizeof($stored_data); $c++){
		// prepare and bind
		$stmt = $conn->prepare("INSERT INTO users (name, surename, email) VALUES (?, ?, ?)");
		$stmt->bind_param("sss", $name, $surename, $email);
		// set parameters and execute
		$name = clean(ucfirst(strtolower($stored_data[$c][0])));
		$surename = clean(ucfirst(strtolower($stored_data[$c][1])));
		$email = strtolower($stored_data[$c][2]);
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	    	echo "Insertion error: email format is not valid: " . $email . "\n";
		}
		elseif (in_array($email, $exist_email)) {
			echo "Insertion failed! Email ". $email ." already in use.\n";
		}
		else{
			$stmt->execute();
		}

	}

	echo "Insertion complete!\n";

	mysqli_close($conn);
}


//  function for removing special characters 
function clean($string) {
   $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

?>