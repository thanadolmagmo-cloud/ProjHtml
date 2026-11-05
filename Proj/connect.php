<?php
$host="localhost";
$user="root"; 
$pass="";
$database="member"; 


$link=mysqli_connect($host,$user,$pass,$database);
if(!$link){
    echo "Can't connection MySQL";
    exit();
}
?>