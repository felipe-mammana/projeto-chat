<?php   
$host="localhost";     
$user="root";        
$password="";         
$dd= "treinamento";   


$cone= mysqli_connect($host,$user,$password,$dd);


if(!$cone){
    echo "erro de conexão";  
}else{            
}

date_default_timezone_set('America/Sao_Paulo');

// MySQL (MUUUITO IMPORTANTE)
mysqli_query($cone, "SET time_zone = '-03:00'");
?>