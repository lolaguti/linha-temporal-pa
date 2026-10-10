<!--
 * login.php
 * 
 * Copyright 2024 Grupo 1 PA2324 <pa2324g1@gentzen>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 -->

<?php 
    require "session.php";
    
    $server = $_SESSION["server"];
    $user = $_SESSION["user"];
    $pass = $_SESSION["pass"];
    $dbname = $_SESSION["dbname"];
    $connection = mysqli_connect("$server","$user","$pass","$dbname");

    // Verifica a ligação à base de dados
    if(!$connection){
        die("A ligação à base de dados falhou." . mysqli_connect_error());
    } 
    

    // Valores do formulário
    $userName = $_POST["user"];
    $password = $_POST["password"];

    // Verifica se os dados inseridos estão corretos
    $query = "CALL checkUser ('" . $userName . "', sha1('" . $password . "'));";
    echo $query;
    $result = mysqli_query($connection, $query);
    $row = mysqli_fetch_array($result, MYSQLI_NUM);

    // Se os dados estiverem corretos, é atribuído o papel de editor
    if ($row[0]==1) {
        $_SESSION["role"] = "editor";
    } else {
        $_SESSION["role"] = "user";
        echo "<script>alert('O utilizador ou a palavra passe está incorreto. Por favor tente de novo.');</script>";
    }
     
	$_POST = array();
    
    require "index.php";
?>
