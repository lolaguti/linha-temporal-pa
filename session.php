<!--
 * session.php
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
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Timeout da sessão
    if (isset($_SESSION["last_activity"]) && (time() - $_SESSION["last_activity"] > 1800)) { // last request was more than 30 minutes ago
        session_unset();
        session_destroy();
        
        echo "<script>alert('O tempo de inatividade ultrapassou 30 min. Não foi possível concluir a ação.');</script>";
        require "index.php";
        exit();
    }
    $_SESSION["last_activity"] = time();

    // Valores de acesso à base de dados
    if (!isset($_SESSION["role"])) {
        require ".sgr.php";
        
        $_SESSION["server"] = $server;
        $_SESSION["user"] = $user;
        $_SESSION["pass"] = $pass;
        $_SESSION["dbname"] = $dbname;

        $_SESSION["role"] = "user";
    }
?>
