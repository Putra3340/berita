<?php
$conn = new mysqli("localhost", "user", "123", "beritadb");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
session_start();
?>
