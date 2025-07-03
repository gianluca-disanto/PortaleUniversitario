<?php
$host = "localhost";        
$user = "root";            
$password = "";             
$database = "gestione_esami"; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}
?>
