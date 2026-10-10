<!--
 * edit.php
 * 
 * Copyright 2024 Grupo 1 PA2324 <pa2324g1@gentzen>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.´
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 -->
 
<!--
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 -->

<?php 
    require "session.php";
    require_once "auxFunctions.php";

    // Impede o acesso de não editores à página
	//if ($_SESSION["role"] == "user") {
	//	require "index.php";
	//	echo "<script>alert('Acesso proibído, área restrita.');</script>";
	//	exit();
	//}

    // Verifica a conexão à base de dados
    $server = $_SESSION["server"];
    $user = $_SESSION["user"];
    $pass = $_SESSION["pass"];
    $dbname = $_SESSION["dbname"];
    $connection = mysqli_connect("$server","$user","$pass","$dbname");
    
    if(!$connection){
        die("A ligação à base de dados falhou." . mysqli_connect_error());
    }  


    // Completa a ação pedida
    $query = "";
    foreach ($_POST as $key => $value) {
        $_POST[$key] = str_replace("\"","\\\\\\\"",$value);
    }
    
    if (isset($_POST["action"])) {
            switch ($_POST["action"]) {
                // Matemáticos
                case "Inserir":
                    anos_acdc();
    
                    $insert = "INSERT INTO matematico (PrimeiroNome, AnoNasc, Sexo";
                    $values = "VALUES ('" . $_POST["PrimeiroNome"] ."', " . $_POST["AnoNasc"] . ", '" . $_POST["Sexo"] . "'";
    
                    if ($_POST["UltimoNome"]!="") {
                        $insert .= ", UltimoNome";
                        $values .= ", '" . $_POST["UltimoNome"] . "'";
                    }
                    if ($_POST["AnoFalec"]!="") {
                        $insert .= ", AnoFalec";
                        $values .= ", " . $_POST["AnoFalec"];
                    }
                    if ($_POST["FotoLink"]!="") {
                        $insert .= ", Foto";
                        $values .= ", '" . $_POST["FotoLink"] . "'";
                    }
    
                    $insert .= ") ";
                    $values .= ");";
    
                    $query .= $insert . $values;
                    break;
    
                case "Editar":
                    anos_acdc();
    
                    $update = "UPDATE matematico SET PrimeiroNome='" . $_POST["PrimeiroNome"] . "', AnoNasc="
                             . $_POST["AnoNasc"] . ", Sexo='" . $_POST["Sexo"] . "', ";
                                
                    if ($_POST["UltimoNome"]=="") {
                        $update .= "UltimoNome=NULL, ";
                    } else {
                        $update .= "UltimoNome='". $_POST["UltimoNome"] ."', ";
                    }
                    if ($_POST["AnoFalec"]=="") {
                        $update .= "AnoFalec=NULL, ";
                    } else {
                        $update .= "AnoFalec=". $_POST["AnoFalec"] .", ";
                    }
                    if ($_POST["FotoLink"]=="") {
                        $update .= "Foto=NULL";
                    } else {
                        $update .= "Foto='". $_POST["FotoLink"] ."'";
                    }
                    
                    $update .= " WHERE Id = " . $_POST["Id"] . ";";
                    $query .= $update;
                    break;
    
                case "Eliminar":
                    $query .= "DELETE FROM matematico WHERE Id = " . $_POST["Id"] . ";";
                    break;
                
                // Instituições
                case "InserirInst":                    
                    if (isset($_POST["inst"])) {
                        foreach ($_POST["inst"] as $key => $value) {
                            $inst = explode(',', $value);

                            $keyRelacao = "selectRel" . $inst[0];
                            if (isset($_POST[$keyRelacao]) and isset($_POST["Id"])) {
                                foreach ($_POST[$keyRelacao] as $key1 => $value1) {
                                    $query .= "CALL inserir_instituicao (" . $inst[0] . ", '" 
                                              . $inst[1] . "', '" . $inst[2] . "', '" . $inst[3] 
                                              . "', " . $_POST["Id"] . ", '" . $value1 . "');";
                                }
                            } else {
                                $query .= "CALL inserir_instituicao (" . $inst[0] . ", '" 
                                              . $inst[1] . "', '" . $inst[2] . "', '" . $inst[3] 
                                              . "', 0, '');";
                            }
                        }
                    }
                    break;
    
                case "EditarInst":
                    $query .= "UPDATE instituicao SET Nome='" . $_POST["InstNome"] . "', Cidade='"
                             . $_POST["InstCidade"] . "', Pais='" . $_POST["InstPais"] . "' 
                             WHERE Id = " . $_POST["InstId"] . ";";
                    
                    break;

                case "EliminarInst":
                    if (isset($_POST["inst"])) {
                        foreach ($_POST["inst"] as $key => $value) {
                            $inst = explode(',', $value);

                            $keyRelacao = "selectRel" . $inst[0];
                            if (isset($_POST["Id"])) {
                                if (isset($_POST[$keyRelacao])) {
                                    foreach ($_POST[$keyRelacao] as $key1 => $value1) {
                                        $query .= "CALL eliminar_instituicao (" . $inst[0] . ", " . $_POST["Id"] . ", '" . $value1 . "');";
                                    }
                                } else {
                                    $query .= "CALL eliminar_instituicao (" . $inst[0] . ", " . $_POST["Id"] . ", '');";
                                }
                            } else {
                                $query .= "CALL eliminar_instituicao (" . $inst[0] . ", 0, '');";
                            }
                        }
                    }
                    break;

                // Contribuições
                case "InserirCont":
                    if (isset($_POST["cont"])) {
                        if (isset($_POST["Id"])) {
                            foreach ($_POST["cont"] as $key => $value) {
                                $cont = explode(',', $value);
                                $query .= "CALL inserir_contribuicao (" . $cont[0] . ", '" 
                                        . $cont[1] . "', '" . $cont[2] . "', " . $_POST["Id"] . ");"; 
                            }
                        } else {
                            foreach ($_POST["cont"] as $key => $value) {
                                $inst = explode(',', $value);
                                $query .= "CALL inserir_contribuicao (" . $inst[0] . ", '" 
                                        . $inst[1] . "', '" . $inst[2] . "', 0);"; 
                            }
                        }
                    }
                    break;
    
                case "EditarCont":
                    $query .= "UPDATE contribuicao SET Nome='" . $_POST["ContNome"] . "', Link='"
                             . $_POST["ContLink"] . "'  
                             WHERE Id = " . $_POST["ContId"] . ";";

                    break;
    
                case "EliminarCont":
                    if (isset($_POST["cont"])) {
                        if (isset($_POST["Id"])) {
                            foreach ($_POST["cont"] as $key => $value) {
                                $cont = explode(',', $value);
                                $query .= "CALL eliminar_contribuicao (" . $cont[0] . ", " . $_POST["Id"] . ");";
                            }
                        } else {
                            foreach ($_POST["cont"] as $key => $value) {
                                $cont = explode(',', $value);
                                $query .= "CALL eliminar_contribuicao (" . $cont[0] . ", 0);";
                            }
                        }
                    }
                    break;
                
                // Nacionalidades
                case "InserirNac":
                    if (isset($_POST["nac"])) {
                        if (isset($_POST["Id"])) {
                            foreach ($_POST["nac"] as $key => $value) {
                                $query .= "CALL inserir_nacionalidade ('" . $value . "', " . $_POST["Id"] . ");"; 
                            }
                        } else {
                            foreach ($_POST["nac"] as $key => $value) {
                                $query .= "CALL inserir_nacionalidade ('" . $value . "', 0);"; 
                            }
                        }
                    }
                    break;
                case "EliminarNac":
                    if (isset($_POST["nac"])) {
                        if (isset($_POST["Id"])) {
                            foreach ($_POST["nac"] as $key => $value) {
                                $query .= "CALL eliminar_nacionalidade ('" . $value . "', " . $_POST["Id"] . ");";
                            }
                        } else {
                            foreach ($_POST["nac"] as $key => $value) {
                                $query .= "CALL eliminar_nacionalidade ('" . $value . "', 0);";
                            }
                        }
                    }
                    break;
                
                // Áreas Matemáticas
                case "InserirAreaMat":
                    if (isset($_POST["areaMat"])) {
                        if (isset($_POST["Id"])) {
                            foreach ($_POST["areaMat"] as $key => $value) {
                                $query .= "CALL inserir_areaMat ('" . $value . "', " . $_POST["Id"] . ");"; 
                            }
                        } else {
                            foreach ($_POST["areaMat"] as $key => $value) {
                                $query .= "CALL inserir_areaMat ('" . $value . "', 0);"; 
                            }
                        }
                    }
                    break;
                case "EliminarAreaMat":
                    if (isset($_POST["areaMat"])) {
                        if (isset($_POST["Id"])) {
                            foreach ($_POST["areaMat"] as $key => $value) {
                                $query .= "CALL eliminar_areaMat ('" . $value . "', " . $_POST["Id"] . ");";
                            }
                        } else {
                            foreach ($_POST["areaMat"] as $key => $value) {
                                $query .= "CALL eliminar_areaMat ('" . $value . "', 0);";
                            }
                        }
                    }
                    break;

                // Outras Áreas
                case "InserirAreaOutra":
                    if (isset($_POST["areaOutra"])) {
                        if (isset($_POST["Id"])) {
                            foreach ($_POST["areaOutra"] as $key => $value) {
                                $query .= "CALL inserir_areaOutra ('" . $value . "', " . $_POST["Id"] . ");"; 
                            }
                        } else {
                            foreach ($_POST["areaOutra"] as $key => $value) {
                                $query .= "CALL inserir_areaOutra ('" . $value . "', 0);"; 
                            }
                        }
                    }
                    break;
                case "EliminarAreaOutra":
                    if (isset($_POST["areaOutra"])) {
                        if (isset($_POST["Id"])) {
                            foreach ($_POST["areaOutra"] as $key => $value) {
                                $query .= "CALL eliminar_areaOutra ('" . $value . "', " . $_POST["Id"] . ");";
                            }
                        } else {
                            foreach ($_POST["areaOutra"] as $key => $value) {
                                $query .= "CALL eliminar_areaOutra ('" . $value . "', 0);";
                            }
                        }
                    }
                    break;
              }
        }
    
    // Dados para as listas
    $query .= "CALL matematicos_edit;";

    $query .= "SELECT * FROM instituicao ORDER BY Nome;";
    $query .= "SELECT DISTINCT Relacao FROM relacionado ORDER BY Relacao;";

    $query .= "SELECT * FROM contribuicao ORDER BY Nome;";

    $query .= "SELECT * FROM nacionalidade ORDER BY Nome;";

    $query .= "SELECT * FROM area_matematica ORDER BY Nome;";
    $query .= "SELECT * FROM area_outra ORDER BY Nome;";
    
    
    $rows = mysqlResults ($connection, $query);
    $rows_json = json_encode($rows);

    mysqli_close($connection);
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeLine</title>

    <link rel="stylesheet" type="text/css" href="edit_styles.css">
    
    <script type='text/javascript'> var dataJSON='<?=$rows_json?>'; </script>
    <script src="edit_script.js"></script>
</head>
<body>
    <div class="listaInfo">
        <form class="formVoltar" action="index.php" method="post">
            <button class="voltarBtn" type="submit">Voltar</button>
        </form>

        <div class="title">Matemáticos</div>
        
        
        <div id="listaMat" class="listaInfo"></div>
        
        <div id="extraInfo" class="listaInfo">
            <div id="extraInst" style="display: block;"></div>
            <div id="extraCont" style="display: none;"></div>
            <div id="extraNac" style="display: none;"></div>
            <div id="extraAreaMat" style="display: none;"></div>
            <div id="extraAreaOutra" style="display: none;"></div>
        </div>
    </div>
    
    <form id="formEdit" action="teste.php" class="formEdit" method="post" onkeypress="return event.keyCode != 13;">  
        <!-- Matemático -->
        <div id="titleMat" class="title">Dados do Matemático</div>

        <div id="formMat" class="formDiv">
            <label for="Id" class="inputLabel">Id:</label><br>
            <input type="number" id="Id" name="Id" class="formulario" disabled><br>

            <label for="PrimeiroNome" class="inputLabel">Primeiro Nome:</label><br>
            <input type="text" id="PrimeiroNome" name="PrimeiroNome" class="formulario" maxlength="30"> <br>
            
            <label for="UltimoNome" class="inputLabel">Último Nome:</label><br>
            <input type="text" id="UltimoNome" name="UltimoNome" class="formulario" maxlength="30"><br>
            
            <label for="FotoLink" class="inputLabel">Foto (link):</label><br>
            <input type="url" id="FotoLink" name="FotoLink" class="formulario"><br>
            
            <div class="fotoDiv">
                <img id ="FotoImg" class="fotoMat" alt="imagem do matemático selecionado"  style="display: none;"> <br>
            </div>
            
            <label for="AnoNasc" class="inputLabel">Ano de Nascimento:</label><br>
            <input type="number" id="AnoNasc" name="AnoNasc" class="formulario" min="0">
            <input type="radio" id="AnoNasc_ac" name="AnoNasc_acdc" value="a.C."> <label for="AnoNasc_ac" class="radioLabel">a.C.</label>
            <input type="radio" id="AnoNasc_dc" name="AnoNasc_acdc" value="d.C." checked> <label for="AnoNasc_dc" class="radioLabel">d.C.</label>
            <br> 

            <label for="AnoFalec" class="inputLabel">Ano de Falecimento:</label><br>
            <input type="number" id="AnoFalec" name="AnoFalec" class="formulario" min="0">
            <input type="radio" id="AnoFalec_ac" name="AnoFalec_acdc" value="a.C."> <label for="AnoFalec_ac" class="radioLabel">a.C.</label>
            <input type="radio" id="AnoFalec_dc" name="AnoFalec_acdc" value="d.C." checked> <label for="AnoFalec_dc" class="radioLabel">d.C.</label>
            <br> 
            
            <label for="SexoM" class="inputLabel">Sexo:</label>
            <input type="radio" id="SexoM" name="Sexo" value="M" checked> <label for="SexoM" class="radioLabel">M</label>
            <input type="radio" id="SexoF" name="Sexo" value="F"> <label for="SexoF" class="radioLabel">F</label>
            <input type="radio" id="SexoDesc" name="Sexo" value="Desconhecido"> <label for="SexoDesc" class="radiolabel">Desconhecido</label> <br>

            
            <button type="submit" id="actionInserir" name="action" value="Inserir"
                   title="Inserir o matemático novo com os dados preenchidos no formulário">
                   Inserir</button>
            <button type="submit" id="actionEditar" name="action" value="Editar"
                   title="Editar o matemático selecionado (identificado pelo seu id) e atualizar os dados para os preenchidos no formulário.">
                   Editar</button>
            <button type="submit" id="actionEliminar" name="action" value="Eliminar"
                   title="Eliminar o matemático selecionado (identificado pelo seu id).">
                   Eliminar</button>
            <input type="reset" id="resetMat"
                   title="Apagar todos os campos de preenchimento de todos os formulários da página.">            
        </div>

        <div id="formAux" class="formDiv">
            <div id="titleAux" class="title">
                <select id="selectForm">
                    <option value="Instituição" >Instituição</option>
                    <option value="Contribuição">Contribuição</option>
                    <option value="Nacionalidade">Nacionalidade</option>
                    <option value="AreaMatematica">Área Matemática</option>
                    <option value="AreaOutra">Outra Área</option>
                </select>
            </div>
            
            <!-- Instituição -->
            <div id="instituicao" class="columna3" >
                <div id="form_inst1" class="form1">
                    <div>
                        <label for="InstId">Id:</label><br>
                        <input type="number" id="InstId" name="InstId" class="formulario" disabled><br>

                        <label for="InstNome">Nome:</label><br>
                        <input type="text" id="InstNome" name="InstNome" class="formulario" maxlength="100">
                    </div>

                    <div >
                        <label for="InstCidade">Cidade:</label><br>
                        <input type="text" id="InstCidade" name="InstCidade" class="formulario" maxlength="30">
                        <br>

                        <label for="InstPais">País:</label><br>
                        <input type="text" id="InstPais" name="InstPais" class="formulario" maxlength="30">
                    </div>
                </div>

                <div id="form_inst2">
                    <input type="button" id="plusInst" value="+"
                        title="Adicionar os dados inseridos no formulário à lista de instituições.&#013Esta ação não afeta a base de dados, para inserir os dados de forma efetiva é necessário clicar em Inserir com as instituições selecionadas.">
                    <button type="submit" id="actionInserirInst" name="action" value="InserirInst"
                        title="Caso 1 - matemático e relações selecionadas: Inserir as instituições selecionadas, caso não existam, e associar o matemático às instituições selecionadas com as relações especificadas;&#013Caso 2 - matemático ou relações não selecionadas: Inserir as instituições selecionadas, caso não existam.">
                        Inserir</button>                        
                    <button type="submit" id="actionEditarInst" name="action" value="EditarInst"
                        title="Editar a instituição selecionada (identificada pelo seu id) e atualizar os dados para os preenchidos no formulário.">
                        Editar</button>  
                    <button type="submit" id="actionEliminarInst" name="action" value="EliminarInst"
                        title="Caso 1 - matemático e relações selecionadas: Eliminar as relações especificadas entre o matemático e as instituições selecionadas;&#013Caso 2 - apenas matemático selecionado: Eliminar todas as relações entre o matemático e as instituições selecionadas;&#013Caso 3 - nem matemático nem relações selecionadas: Eliminar todas as instituições selecionadas (e, consequentemente, todas as relações com qualquer matemático).">
                        Eliminar</button>
                </div>
                
                <div id="listaInst" class="lista">
                </div>    
            </div> 

            <!-- Contribuição -->
            <div id="contribuicao" class="columna3" style="display: none;">
                <div id="form_cont1" class="form1">
                    <div>
                    <label for="ContId">Id:</label><br>
                    <input type="number" id="ContId" name="ContId" class="formulario" disabled><br>
                    </div>

                    <div>
                    <label for="ContNome">Nome:</label><br>
                    <input type="text" id="ContNome" name="ContNome" class="formulario" maxlength="50">
                    </div> 

                    <div>
                    <label for="ContLink">Link:</label><br>
                    <input type="url" id="ContLink" name="ContLink" class="formulario">
                    <input type="button" id="ContLinkGo" value="Ir">
                    </div>
                </div>

                <div>
                <input type="button" id="plusCont" value="+"
                           title="Adicionar os dados inseridos no formulário à lista de contribuições.&#013Esta ação não afeta a base de dados, para inserir os dados de forma efetiva é necessário clicar em Inserir com as contribuições selecionadas.">
                    <button type="submit" id="actionInserirCont" name="action" value="InserirCont"
                           title="Caso 1 - matemático selecionado: Inserir as contribuições selecionadas, caso não existam, e associar o matemático às contribuições selecionadas;&#013Caso 2 - matemático não selecionado: Inserir as contribuições selecionadas, caso não existam.">
                           Inserir</button>
                    <button type="submit" id="actionEditarCont" name="action" value="EditarCont"
                           title="Editar a contribuição selecionada (identificada pelo seu id) e atualizar os dados para os preenchidos no formulário.">
                           Editar</button>
                    <button type="submit" id="actionEliminarCont" name="action" value="EliminarCont"
                           title="Caso 1 - matemático selecionado: Eliminar as associações entre o matemático e as contribuições selecionadas;&#013Caso 2 - matemático não selecionado: Eliminar todas as contribuições selecionadas (e, consequentemente, todas as relações com qualquer matemático).">
                           Eliminar</button>
                </div>
                
                <div id="listaCont" class="lista"></div>
            </div> 

            <!-- Nacionalidade -->
            <div id="nacionalidade" class="columna3" style="display: none;">
                <div id="form_nac">
                    <input type="text" id="NacNome" class="formulario" placeholder="Inserir nova nacionalidade" maxlength="50"></input>

                    <input type="button" id="plusNac" value="+"
                           title="Adicionar a nacionalidade inserida no formulário à lista de nacionalidades.&#013Esta ação não afeta a base de dados, para inserir os dados de forma efetiva é necessário clicar em Inserir com as nacionalidades selecionadas.">
                    <button type="submit" id="actionInserirNac" name="action" value="InserirNac"
                           title="Caso 1 - matemático selecionado: Inserir as nacionalidades selecionadas, caso não existam, e associar o matemático às nacionalidades selecionadas;&#013Caso 2 - matemático não selecionado: Inserir as nacionalidades selecionadas, caso não existam.">
                           Inserir</button>
                    <button type="submit" id="actionEliminarNac" name="action" value="EliminarNac"
                           title="Caso 1 - matemático selecionado: Eliminar as associações entre o matemático e as nacionalidades selecionadas;&#013Caso 2 - matemático não selecionado: Eliminar todas as nacionalidades selecionadas (e, consequentemente, todas as relações com qualquer matemático).">
                           Eliminar</button>
                </div>    
                
                <div id="listaNac" class="lista"></div>
            </div> 

            <!-- Área Matemática -->
            <div id="areaMatematica" class="columna3" style="display: none;">
                <div id="form_cont">
                    <input type="text" id="AreaMatNome" class="formulario" placeholder="Inserir nova área" maxlength="50"></input>

                    <input type="button" id="plusAreaMat" value="+"
                           title="Adicionar a área inserida no formulário à lista de áreas.&#013Esta ação não afeta a base de dados, para inserir os dados de forma efetiva é necessário clicar em Inserir com as áreas selecionadas.">
                    <button type="submit" id="actionInserirAreaMat" name="action" value="InserirAreaMat"
                           title="Caso 1 - matemático selecionado: Inserir as áreas selecionadas, caso não existam, e associar o matemático às áreas selecionadas;&#013Caso 2 - matemático não selecionado: Inserir as áreas selecionadas, caso não existam.">
                           Inserir</button>
                    <button type="submit" id="actionEliminarAreaMat" name="action" value="EliminarAreaMat"
                           title="Caso 1 - matemático selecionado: Eliminar as associações entre o matemático e as áreas selecionadas;&#013Caso 2 - matemático não selecionado: Eliminar todas as áreas selecionadas (e, consequentemente, todas as relações com qualquer matemático).">
                           Eliminar</button>
                </div>
                
                <div id="listaAreaMat" class="lista"></div>
            </div> 

            <!-- Outra Área -->
            <div id="areaOutra" class="columna3" style="display: none;">
                <div id="form_cont">
                    <input type="text" id="AreaOutraNome" class="formulario" placeholder="Inserir nova área" maxlength="50"></input>

                    <input type="button" id="plusAreaOutra" value="+"
                           title="Adicionar a área inserida no formulário à lista de áreas.&#013Esta ação não afeta a base de dados, para inserir os dados de forma efetiva é necessário clicar em Inserir com as contribuições selecionadas.">
                    <button type="submit" id="actionInserirAreaOutra" name="action" value="InserirAreaOutra"
                           title="Caso 1 - matemático selecionado: Inserir as áreas selecionadas, caso não existam, e associar o matemático às áreas selecionadas;&#013Caso 2 - matemático não selecionado: Inserir as áreas selecionadas, caso não existam.">
                           Inserir</button>
                    <button type="submit" id="actionEliminarAreaOutra" name="action" value="EliminarAreaOutra"
                           title="Caso 1 - matemático selecionado: Eliminar as associações entre o matemático e as áreas selecionadas;&#013Caso 2 - matemático não selecionado: Eliminar todas as áreas selecionadas (e, consequentemente, todas as relações com qualquer matemático).">
                           Eliminar</button>
                </div>    
            
                <div id="listaAreaOutra" class="lista"></div>
            </div> 
    </form>
</body>
</html>

