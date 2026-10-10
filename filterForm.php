<!--
 * filterForm.php
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

    $server = $_SESSION["server"];
    $user = $_SESSION["user"];
    $pass = $_SESSION["pass"];
    $dbname = $_SESSION["dbname"];
    $connection = mysqli_connect("$server","$user","$pass","$dbname");
    
    // Verifica a ligação à base de dados
    if(!$connection){
        die("A ligação à base de dados falhou." . mysqli_connect_error());
    }

    // AnoNasc, AnoFalec
    $query = "SELECT MIN(AnoNasc) AS AnoNascMin, MAX(AnoNasc) AS AnoNascMax, MIN(AnoFalec) AS AnoFalecMin, MAX(AnoFalec) AS AnoFalecMax FROM matematico;";
    
    // Nacionalidades
    $query .= "SELECT * FROM nacionalidade;";
    
    // Áreas Matemáticas
    $query .= "SELECT * FROM area_matematica;";

    // Outras Áreas
    $query .= "SELECT * FROM area_outra;";

    // Instituições
    $query .= "SELECT Id, Nome FROM instituicao ORDER BY Nome;";

    // Relação (a instituições)
    $query .= "SELECT DISTINCT Relacao FROM relacionado ORDER BY Relacao;";

    // Cidades
    $query .= "SELECT CONCAT(inst.Cidade, ', ', inst.Pais)
               FROM instituicao AS inst
               ORDER BY inst.Pais, inst.Cidade;";
               
    // Países
    $query .= "SELECT DISTINCT Pais FROM instituicao ORDER BY Pais;";

    // Primeiro Nome
    $query .= "SELECT PrimeiroNome FROM matematico ORDER BY PrimeiroNome;";
    
    // Último Nome
    $query .= "SELECT UltimoNome FROM matematico ORDER BY UltimoNome;";

    
    $rows = mysqlResults ($connection, $query);
    
    //pre_r($rows);
    $rows_json = json_encode($rows);

    // Guarda AnoNascMin/Max e AnoFalecMin/Max em $_SESSION
    $_SESSION["AnoNascMin"] = $rows[0][0];
    $_SESSION["AnoNascMax"] = $rows[0][1];
    $_SESSION["AnoFalecMin"] = $rows[0][2];
    $_SESSION["AnoFalecMax"] = $rows[0][3];

    mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="nouislider.css" rel="stylesheet">
    <script src="nouislider.js"></script>
    <script src="wNumb.min.js"></script>

    <link href="index_styles.css" rel="stylesheet" type="text/css"> 
</head>

<body>    
    <div id="filters">
        <form action="index.php" id="filter" method="post">
            <!-- Nome -->
            <label for="PrimeiroNome">Primeiro  Nome:</label>
            <input type="text" id="PrimeiroNome" name="PrimeiroNome" list="PNome" maxlength="30">
            <datalist id="PNome">
            </datalist>

            <label for="UltimoNome">Último Nome:</label>
            <input type="text" id="UltimoNome" name="UltimoNome" list="UNome" maxlength="30">
            <datalist id="UNome">
            </datalist>
            
            <!-- Ano de Nascimento e Falecimento -->
            <label>Ano de Nascimento:</label>
            <div id="rangeAnoNasc"></div>
            <input type="number" id="AnoNascMin" name="AnoNascMin" class="input_filter" min="0">
            <select id="min_nasc" name="AnoNascMin_acdc" class="select_filter">
                <option value="a.C.">a.C.</option>
                <option value="d.C.">d.C.</option>
            </select><br>
            <input type="number" id="AnoNascMax" name="AnoNascMax" class="input_filter" min="0">
            <select id="max_nasc" name="AnoNascMax_acdc" class="select_filter">
                <option value="a.C.">a.C.</option>
                <option value="d.C.">d.C.</option>
            </select><br>

            <label>Ano de Falecimento:</label>
            <div id="rangeAnoFalec"></div>
            <input type="number" id="AnoFalecMin" name="AnoFalecMin" class="input_filter" min="0">
            <select id="min_falec" name="AnoFalecMin_acdc" class="select_filter">
                <option value="a.C.">a.C.</option>
                <option value="d.C.">d.C.</option>
            </select><br>
            <input type="number" id="AnoFalecMax" name="AnoFalecMax" class="input_filter" min="0">
            <select id="max_falec" name="AnoFalecMax_acdc" class="select_filter">
                <option value="a.C.">a.C.</option>
                <option value="d.C.">d.C.</option>
            </select><br>
            

            <!-- Sexo -->
            <label>Sexo:</label>
            <input type="radio" id="SexoM" name="Sexo" value="M">M
            <input type="radio" id="SexoF" name="Sexo" value="F">F
            <input type="radio" id="SexoDesc" name="Sexo" value="Desconhecido">Desconhecido


            <!-- Nacionalidade e Áreas de Estudo -->
            <label>Nacionalidades:</label>
            <div id="Nacionalidades" class="multiselect"> </div>

            <label>Áreas de Estudo:</label>
            <div id="AreaMat" class="multiselect"> </div>

            <label>Outras Áreas de Estudo:</label>
            <div id="AreaOutra" class="multiselect"> </div>


            <!-- Instituição -->
            <label>Instituição:</label>
            <select id="NomeInst" name="NomeInst" class="selectMed">
                <option value="blank">Selecione uma opção</option>
            </select>
            
            <label>Relação:</label>
            <select id="RelInst" name="RelInst" class="selectPeq">
                <option value="blank">Selecione uma opção</option>
            </select>
            

            <!-- Cidade e País de Estudo -->
            <label>Cidade/País:</label>
            <select id="CidadePais" name="CidadePais" class="selectPeq">
                <option value="Cidade">Cidade</option>
                <option value="Pais">País</option>
            </select>

            <select id="Cidade" name="Cidade" class="selectMed" form="filter" style="display: block;">
                <option value="blank">Selecione uma opção</option>
            </select>
            
            <select id="Pais" name="Pais" class="selectMed" form="filter" style="display: none;">
                <option value="blank">Selecione uma opção</option>
            </select>
            

            <!-- Submit e Reset -->
            <div id="submitButtons">
                <input type="submit" value="Pesquisar" class="filter_button">
                <input type="reset" class="filter_button">
            </div>
        </form>
    </div>

    <script type="text/javascript">   
        // Array proveniente da base de dados
        var dataJSON = '<?=$rows_json?>';
        var dataArray = JSON.parse(dataJSON);
        

        // Divide os diferentes tipos de dados em vários arrays
        var temp = [];
        var splitArray = [];
        dataArray.map(x => {  
            if(x) {
                temp.push(x)
            } else {
                splitArray.push(temp)
                temp = []
            }
        });

        temp.length != 0 ? splitArray.push(temp) : '' 
        //console.log(splitArray[9][0]);
        
        // PrimeiroNOme e UltimoNome - Options
        var datalist_p = document.getElementById("PNome");
        for (var i = 0; i < splitArray[8].length; i++) {
            var option = document.createElement('option');
            option.value = splitArray[8][i];
            datalist_p.appendChild(option);
        }

        var datalist_u = document.getElementById("UNome");
        for (var i = 0; i < splitArray[9].length; i++) {
            var option = document.createElement('option');
            option.value = splitArray[9][i];
            datalist_u.appendChild(option);
        }

        // Barras de deslizar (AnoNasc e AnoFalec)
        var anoNascMin = parseInt('<?=$_SESSION["AnoNascMin"]?>');
        var anoNascMax = parseInt('<?=$_SESSION["AnoNascMax"]?>');
        var anoFalecMin = parseInt('<?=$_SESSION["AnoFalecMin"]?>');
        var anoFalecMax = parseInt('<?=$_SESSION["AnoFalecMax"]?>');
        filtersAno(anoNascMin, anoNascMax, anoFalecMin, anoFalecMax)


        // Filtros (Nacionalidade, AreaMat, AreaOutra, NomeInst, RelInst)
        createCheckbox(splitArray[1], "Nacionalidades");
        createCheckbox(splitArray[2], "AreaMat");
        createCheckbox(splitArray[3], "AreaOutra");

        var selectInst = document.getElementById("NomeInst");
        for (var i = 0; i < splitArray[4].length; i++) {
            var option = document.createElement('option');
            option.value = splitArray[4][i][0];
            option.textContent = splitArray[4][i][1];
            selectInst.appendChild(option);
        }

        createSelect(splitArray[5], "RelInst");


        // Filtro de cidade ou país
        var select = document.getElementById("CidadePais");
	    select.addEventListener("input", selectCidadePais);

        createSelect(splitArray[6], "Cidade");
        createSelect(splitArray[7], "Pais");
        

        // Cria lista de checkboxes para filtro
        function createCheckbox (array, name) {
            var list = document.getElementById(name);
            for (var i = 0; i < array.length; i++) {
                var checkbox = document.createElement('input');
                checkbox.type = "checkbox";
                checkbox.name = name + "[]";
                checkbox.value = array[i];
                checkbox.id = array[i];
                list.appendChild(checkbox);

                var label = document.createElement('label');
                label.htmlFor = array[i];
                label.appendChild(document.createTextNode(array[i]));
                label.class = "checkbox-label";
                list.appendChild(label);

                list.appendChild(document.createElement("br"));
            }
        }

        // Cria lista em dropdown para filtro
        function createSelect (array, name) {
            var select = document.getElementById(name);

            for (var i = 0; i < array.length; i++) {
                var option = document.createElement('option');
                option.value = option.textContent = array[i];
                select.appendChild(option);
            }
        }

        // Cria barras de deslizar para o filtro de AnoNasc e AnoFalec
        function filtersAno(minNasc, maxNasc, minFalec, maxFalec) {
            var sliderAnoNasc = document.getElementById("rangeAnoNasc");
            var sliderAnoFalec = document.getElementById("rangeAnoFalec");

            noUiSlider.create(sliderAnoNasc, {
                start: [minNasc, maxNasc],
                connect: true,
                range: {
                    'min': minNasc,
                    'max': maxNasc
                },
                step: 1,
                format: wNumb( { decimals: 0 })
            });

            noUiSlider.create(sliderAnoFalec, {
                start: [minFalec, maxFalec],
                connect: true,
                range: {
                    'min': minFalec,
                    'max': maxFalec
                },
                step: 1,
                format: wNumb( { decimals: 0 })
            });

            var minRangeNasc = document.getElementById('AnoNascMin');
            var maxRangeNasc = document.getElementById('AnoNascMax');

            var minRangeFalec = document.getElementById('AnoFalecMin');
            var maxRangeFalec = document.getElementById('AnoFalecMax');

            sliderAnoNasc.noUiSlider.on('update', function (values, handle) {
                var value = parseInt(values[handle])
                if (handle) {
                    var select = document.getElementById('max_nasc');
                    if (value<0){
                        select.value='a.C.';
                    } else {
                        select.value='d.C.';
                    }
                    maxRangeNasc.value =Math.abs(value) ;
                } else {
                    var select = document.getElementById('min_nasc');
                    if (value<0){
                        select.value='a.C.';
                    } else {
                        select.value='d.C.';
                    }
                    minRangeNasc.value = Math.abs(value);
                }
            });

            sliderAnoFalec.noUiSlider.on('update', function (values, handle) {
                var value = parseInt(values[handle])
                if (handle) {
                    var select = document.getElementById('max_falec');
                    if (value<0){
                        select.value='a.C.';
                    } else {
                        select.value='d.C.';
                    }
                    maxRangeFalec.value = Math.abs(value);
                } else {
                    var select = document.getElementById('min_falec');
                    if (value<0){
                        select.value='a.C.';
                    } else {
                        select.value='d.C.';
                    }
                    minRangeFalec.value = Math.abs(value);
                }
            });
        }

        // Mostra e esconde os filtros de Cidade e Pais consoante o selecionado
        function selectCidadePais() {
            var select = document.getElementById("CidadePais").value; 

            var cidadeSelect = document.getElementById("Cidade");
            var paisSelect = document.getElementById("Pais");
            
            if (select === "Cidade") {
                cidadeSelect.style.display = "block";
                paisSelect.style.display = "none";
            }
            if (select === "Pais") {
                cidadeSelect.style.display = "none";
                paisSelect.style.display = "block";
            }
	    }
    </script>

    <style>
        .input_filter{
            width: 45%;
        }
        .select_filter{
            width: 45%;
        }
    </style>
</body>   
</html>

