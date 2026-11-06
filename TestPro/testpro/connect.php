<?php
$host="localhost";
$user="root"; 
$pass="";
$database="nongkhai"; 


$link=mysqli_connect($host,$user,$pass,$database);
if(!$link){
    echo "Can't connection MySQL";
    exit();
}
?>