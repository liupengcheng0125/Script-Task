<?php
$servername = "127.0.0.1";
$username = "springstudent";
$password = "springstudent";
$dbname = "myDB";
 
// create connection
$conn = new mysqli($servername, $username, $password);
 
// test connection
if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
} 
echo "connection successfully\n";

$sql = "CREATE DATABASE IF NOT EXISTS myDB";
if (mysqli_query($conn, $sql)) {
    echo "create database successfully\n"; 
} else {
    echo "Error creating database: " . mysqli_error($conn);
}
mysqli_close($conn);


$conn = new mysqli($servername, $username, $password,$dbname);
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
 
mysqli_close($conn);

//load data from users.csv
$row = 1;
$stored_data = [];
if (($handle = fopen("users.csv", "r")) !== FALSE) {
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
}

//insert into database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




for($c = 1; $c < sizeof($stored_data); $c++){
	// prepare and bind
	$stmt = $conn->prepare("INSERT INTO users (name, surename, email) VALUES (?, ?, ?)");
	$stmt->bind_param("sss", $name, $surename, $email);
	//echo sizeof($stored_data) . "\n";
	//echo $c;
	// set parameters and execute
	$name = clean(ucfirst(strtolower($stored_data[$c][0])));
	$surename = clean(ucfirst(strtolower($stored_data[$c][1])));
	$email = $stored_data[$c][2];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    	echo "Error: email format is not valid!\n";
	}
	else{
		$stmt->execute();
	}

}

mysqli_close($conn);


// remove special characters 
function clean($string) {
   $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
}

?>