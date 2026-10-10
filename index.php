<!--
 * index.php
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
    require_once "auxFunctions.php";
    require "filterForm.php";

	// Verifica a conexão à base de dados
    $server = $_SESSION["server"];
    $user = $_SESSION["user"];
    $pass = $_SESSION["pass"];
    $dbname = $_SESSION["dbname"];
    
    $connection = mysqli_connect("$server","$user","$pass","$dbname");

    if(!$connection){
        die("A ligação à base de dados falhou." . mysqli_connect_error());
    } 


    // Atualiza os dados de anos para o formato da base de dados
    $anoArray = ["AnoNascMin","AnoNascMax","AnoFalecMin","AnoFalecMax"];
    $anoArray_acdc = ["AnoNascMin_acdc", "AnoNascMax_acdc", "AnoFalecMin_acdc", "AnoFalecMax_acdc"];
    
    for ($i = 0; $i <= 3; $i++) {
        if (isset($_POST[$anoArray_acdc[$i]]) and $_POST[$anoArray_acdc[$i]] == "a.C.") {
            $_POST[$anoArray[$i]]=-$_POST[$anoArray[$i]];
        }
    }

    
    // Verifica se o formulário está vazio     
    $not_empty_form = false;
    
    if (!isset($_POST["action"])){
        foreach ($_POST as $key => $value) {
            switch ($key) {
                case "AnoNascMin_acdc":
                case "AnoNascMax_acdc":
                case "AnoFalecMin_acdc":
                case "AnoFalecMax_acdc":
                case "RelInst":
                case "CidadePais":
                    break;

                case "AnoNascMin":
                case "AnoNascMax":
                case "AnoFalecMin":
                case "AnoFalecMax":
                    if ($value != $_SESSION[$key]) {
                        $not_empty_form = true;
                    }
                    break;

                case "NomeInst":
                    if ($value != "blank") {
                        $not_empty_form = true;
                    }
                    break;
                
                case "Cidade":
                    if ($value != "blank" and $_POST["CidadePais"] == "Cidade") {
                        $not_empty_form = true;
                    }
                    break;

                case "Pais":
                    if ($value != "blank" and $_POST["CidadePais"] == "Pais") {
                        $not_empty_form = true;
                    }
                    break;

                default:
                    if ($value != null) {
                        $not_empty_form = true;
                    }    
                    break;    
            }

            if ($not_empty_form == true) {
                break;
            }
        }
    }
    
    
    
    // Constrói a query
    if ($not_empty_form){
        // Timeline de Matemáticos Filtrada
        $select = "SELECT M.*, GROUP_CONCAT(DISTINCT nacionalidade SEPARATOR ', ') AS 'Nacionalidades', 
                               GROUP_CONCAT(DISTINCT estuda_mat.Nome_Area SEPARATOR ', ') AS 'AreaMat', 
                               GROUP_CONCAT(DISTINCT estuda_outra.Nome_Area SEPARATOR ', ') AS 'AreaOutra',
                               GROUP_CONCAT(DISTINCT I.Instituicao SEPARATOR '; ') AS 'Instituicoes',
                               GROUP_CONCAT(DISTINCT CONCAT(C.Nome, ' link: ', C.Link) SEPARATOR '; ') AS 'Contribuicoes'
                    FROM ((((matematico AS M LEFT JOIN mat_nacionalidade ON M.Id = mat_nacionalidade.Id_Matematico) 
                                            LEFT JOIN estuda_mat ON M.Id=estuda_mat.Id_Matematico) 
                                            LEFT JOIN estuda_outra ON M.Id=estuda_outra.Id_Matematico)
                                            LEFT JOIN (SELECT matematico.Id AS IdMat,
       									                      CONCAT(inst.Nome, ' (', inst.Cidade,', ', inst.Pais, ') -- ', 
                                                                     GROUP_CONCAT(relacionado.Relacao SEPARATOR '/ ')) AS Instituicao
	  							                       FROM matematico LEFT JOIN 
                                   	                        (relacionado JOIN instituicao AS inst ON relacionado.Id_Instituicao=inst.Id) 
         			 					                    ON matematico.Id=relacionado.Id_Matematico
                                                       GROUP BY inst.Id, IdMat
                                                       ORDER BY IdMat) AS I ON I.IdMat=M.Id)
                                            LEFT JOIN (autor JOIN contribuicao AS C ON autor.Id_Contribuicao=C.Id) ON M.Id=autor.Id_Matematico";
        $where = " ";
        $having = "";
        
        updateQuery("PrimeiroNome", "INSTR(M.PrimeiroNome,'" . $_POST["PrimeiroNome"] ."')>0");
        updateQuery("UltimoNome", "INSTR(M.UltimoNome,'" . $_POST["UltimoNome"] ."')>0");
        updateQuery("AnoNascMin","M.AnoNasc>=" . $_POST["AnoNascMin"]);
        updateQuery("AnoNascMax","M.AnoNasc<=" . $_POST["AnoNascMax"]);
        updateQuery("AnoFalecMin","IFNULL(M.AnoFalec>=" . $_POST["AnoFalecMin"] . ",TRUE)"); 
        updateQuery("AnoFalecMax","IFNULL(M.AnoFalec<=" . $_POST["AnoFalecMax"] . ",TRUE)");
        updateQuery("Sexo", "M.Sexo='postVar'");
        updateQuery_arrays("Nacionalidades", "INSTR(Nacionalidades,'postVar')>0");
        updateQuery_arrays("AreaMat", "INSTR(AreaMat,'postVar')>0");
        updateQuery_arrays("AreaOutra", "INSTR(AreaOutra,'postVar')>0");
        
        if ($_POST["NomeInst"] != "blank") {

            $where .= "WHERE M.Id in (SELECT mat.Id
                      FROM matematico AS mat LEFT JOIN relacionado AS rel ON mat.Id=rel.Id_Matematico
                      WHERE rel.Id_Instituicao=" . $_POST["NomeInst"];
            
            if ($_POST["RelInst"] != "blank") {
                $where .= " AND rel.Relacao = '" .$_POST["RelInst"] . "'";
            }

            $where .= ")";
        }

        if ($_POST["CidadePais"] == "Cidade") {
            if ($_POST["Cidade"] != "blank") {
                updateQuery("Cidade", "INSTR(Instituicoes,'(postVar)')>0");
            }
        } else {
            if ($_POST["Pais"] != "blank") {
                updateQuery("Pais", "INSTR(Instituicoes,'postVar)')>0");
            }
        }

        $query = $select . $where . " GROUP BY M.Id " . $having . " ORDER BY anoNasc";
    } else {
        // Timeline de Matemáticos Standard
        $query = "CALL matematicos_standard";
    }
    
    
    // Save data in array
    $results = mysqli_query($connection, $query);
    $rows = [];
    while($row = mysqli_fetch_array($results, MYSQLI_ASSOC)) //gets only associative columns
    {
        $rows[] = $row;
    }
    $_SESSION["rows"] = $rows;

    $rows_json = json_encode($rows);

    mysqli_close($connection);

    if(empty($rows)){
        echo "<script>alert('A pesquisa não devolveu nenhum resultado.');</script>";
        $_POST = [];
        echo "<script>window.location = 'index.php';</script>";
    
        exit();
    } 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeLine</title>
 
    <link rel="stylesheet" type="text/css" href="index_styles.css">

    <script type='text/javascript'> 
        var dataJSON='<?=$rows_json?>';
        var role='<?=$_SESSION["role"]?>';
    </script>
    <script type="text/javascript" src="index_script.js"></script>
</head>
<body>
    <div class="menu">
        <form action="print.php" method="post">
            <button type="submit" class="menu_button">Imprimir</button>
        </form>

        <form action="edit.php" method="post">
            <button id="editBtn" type="submit" class="menu_button" >Editar</button>
        </form>
    
        <button id="loginBtn" class="menu_button" onclick="openForm()">Login</button>
        

		<div id="myForm" class="form-popup">
        <form action="login.php" class="form-container" method="post">
            <label for="user">Nome de Utilizador:</label>
            <input type="text" id="user" name="user" placeholder="Introduzir nome de utilizador" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Introduzir password" autocomplete=off required>
            <br>

            <button type="submit" class="btn">Login</button>
            <button type="button" class="btn cancel" onclick="closeForm()">Fechar</button>
        </form>
        </div>
    </div>

    <div class="timeline">
        <div id="timeline-container" class="timeline-container">
        </div>
    </div>
</body>
</html>
