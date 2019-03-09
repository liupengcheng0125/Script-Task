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
echo "connection successfully";

$sql = "CREATE DATABASE IF NOT EXISTS myDB";
if (mysqli_query($conn, $sql)) {
    echo "create database successfully"; 
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
    echo "Table users created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}
 
mysqli_close($conn);

?>