<!--
 * auxFunctions.php
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
    // Devolve resultados de várias queries SQL num array
    function mysqlResults ($connection, $query) {
        $rows = [];
        if (mysqli_multi_query($connection, $query)) {
            do {
                // Armazenar resultados
                if ($result = mysqli_store_result($connection)) {
                    while ($row = mysqli_fetch_row($result)) {
                        $rows[] = $row;
                    }
                    mysqli_free_result($result);
                }
                // Adicionar divisória (caso haja mais resultados)
                if (mysqli_more_results($connection)) {
                    $rows[] = "";
                }
            } while (mysqli_next_result($connection));
        }
        return $rows;
    }
    
	// Imprime um array formatado
    function pre_r ($array) {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }

    // Adiciona as cláusulas "WHERE" à query
    function updateQuery ($post, $havingCondition) {
        if(isset($_POST[$post]) && $_POST[$post] != ''){
            $havingCondition = str_replace("postVar",$_POST[$post],$havingCondition);
            
            if ($GLOBALS["having"] == ""){
                $GLOBALS["having"] .= "HAVING " . $havingCondition;
            }else{
                $GLOBALS["having"] .= " AND " . $havingCondition; 
            }
        }   
    }
    
    function updateQuery_arrays ($post, $havingCondition) {
        if(isset($_POST[$post]) && $_POST[$post] != ''){
            foreach ($_POST[$post] as $key => $value) {
                $replaceCondition = str_replace("postVar",$value,$havingCondition);
                if ($GLOBALS["having"] == ""){
                    $GLOBALS["having"] .= "HAVING " . $replaceCondition;
                }else{
                    $GLOBALS["having"] .= " AND " . $replaceCondition; 
                }  
            }        
        }
    }

	// Transforma os dados de anos de nascimento e falecimento no formato da base de dados
    function anos_acdc () {
        if ($_POST["AnoNasc_acdc"]=="a.C.") {
            $_POST["AnoNasc"] = -$_POST["AnoNasc"];
        }

        if ($_POST["AnoFalec_acdc"]=="a.C." and isset($_POST["AnoFalec"]) and $_POST["AnoFalec"]!="") {
            $_POST["AnoFalec"] = -$_POST["AnoFalec"];
        }
    }

	// Cria informação da caixa de um matemático em latex
    function latexNode ($matematico) {
        $nome = strtoupper($matematico["PrimeiroNome"] . " " . $matematico["UltimoNome"]) . "\\\\";    
        if ($matematico["AnoNasc"]<0) {
            $matematico["AnoNasc"] = -$matematico["AnoNasc"] . " a.C.";
        } else {
            $matematico["AnoNasc"] = $matematico["AnoNasc"] . " d.C.";
        }
        if ($matematico["AnoFalec"] != "") {
            if ($matematico["AnoFalec"]<0) {
                $matematico["AnoFalec"] = -$matematico["AnoFalec"] . " a.C.";
            } else {
                $matematico["AnoFalec"] = $matematico["AnoFalec"] . " d.C.";
            }
        }
        
        $anos = "\;\;\\normalsize{(" . $matematico["AnoNasc"] . " - " . $matematico["AnoFalec"] . ")}";
        
        $sexo = $matematico["Sexo"];
        $nacionalidade = $matematico["Nacionalidades"];
        $areaMat = $matematico["AreaMat"];
        $areaOutra = $matematico["AreaOutra"];
        
        $contribuicao = preg_replace("/\slink:(.*?);/", ',', $matematico["Contribuicoes"].";");
        $contribuicao = rtrim($contribuicao, ',');
        $contribuicao = str_replace('\"','"', $contribuicao);
        
        $info = "\; \\textbf{" . $nome . "} $anos";

        if ($nacionalidade != "") {
            $nacionalidade = "\item Nacionalidade: $nacionalidade";
        }

        $areas = "";
        if ($areaMat != "") {
            $areas .= "\\textbf{" . $areaMat . "}";
        }
        if ($areaOutra != "") {
            if ($areas != "") {
                $areas .= ", ";
            }
            $areas .= $areaOutra;
        }
        if ($areas != 0) {
            $areas = "\item Áreas de Estudo: $areas";
        }

        if ($contribuicao != ";") {
            $contribuicao = "\item Contribuições: $contribuicao";
        }
        $contribuicao = rtrim($contribuicao, ";");
        
        $itemize = "";
        if ($nacionalidade != "" or $areas != "" or $contribuicao != "") {
            $itemize = "\\normalsize{
                    \begin{itemize}
                        $nacionalidade
                        $areas
                        $contribuicao
                    \\end{itemize}}";
        }

        $info .= $itemize;

        return $info;
    }

	// Cria caixa de informação de um matemático em latex (página par/ ímpar)
    function latexPar ($x, $info) {
        if ($x == 10) {
            echo "--($x,0)node[spot](a1){}
                    node[desc=a1.south, below]
                    {" . $info . "}";
        } else {
            echo "--($x,0)node[spot](a1){}
                    node[desc=a1.north, above]
                    {" . $info . "}";
        }
    }

    function latexImpar ($x, $info) {
        if ($x == 10) {
            echo "--($x,0)node[spot](a1){}
                    node[desc=a1.north, above]
                    {" . $info . "}";
        } else {
            echo "--($x,0)node[spot](a1){}
                    node[desc=a1.south, below]
                    {" . $info . "}";
        }
    }

?>

