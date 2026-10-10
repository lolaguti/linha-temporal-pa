<!--
 * print.php
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
    
    // Pesquisa que se pretende imprimir
    $rows = $_SESSION["rows"];
    $length = count($rows);
    $splitRows = array_chunk($rows, 3);
    
    // Criação do latex
    ob_start();
    echo "\documentclass{article}

\usepackage{tikz}
\usepackage[landscape, paperwidth=21cm, paperheight=29.7cm, margin=0mm]{geometry}
\usetikzlibrary {shapes.callouts}
\usepackage{rotating}


\definecolor{verde}{RGB}{0, 71, 53}
\definecolor{azul}{RGB}{211, 234, 252}


\\tikzset{spot/.style={draw=verde, 
                      ultra thick,
                      circle,                         
                      fill=white,
                      outer sep=2mm},
         time/.style={inner sep=3mm,
                      align = right},
         desc/.style={outer ysep=6mm, 
                      inner sep=5mm,
                      right=6mm, 
                      text width=8.5cm,
                      text=black,
                      font = \large,
                      fill=white, 
                      shape=rectangle callout, 
                      draw=verde, ultra thick,
                      rounded corners = 2mm,
                      callout absolute pointer={(#1)}}
}

\begin{document}
\pagecolor{azul}";

    $length = count($splitRows);

    foreach ($splitRows as $key => $value) {
        echo "\begin{figure}
        \centering
        \\resizebox{\\textwidth}{!}{
            \begin{tikzpicture}

            \draw[verde, ultra thick](0,0)";
        
	   if ($key % 2 == 0) {
			foreach($value as $key1 => $matematico) {
				latexPar(5*($key1+1), latexNode($matematico));
			}
	   } else {
            foreach($value as $key1 => $matematico) {
                latexImpar(5*($key1+1), latexNode($matematico));
            }
       }  
         
       echo "   --(20,0);
            \\end{tikzpicture}
       }
\\end{figure}";
	   if ($key != $length) {
			echo "\clearpage";
	   }
}
    

    echo "\\end{document}";

    $ficheiroLaTeX = ob_get_contents();
    $outputFile = fopen("timeLine.tex","w");
    fwrite($outputFile, $ficheiroLaTeX);
    fclose($outputFile);
    ob_end_clean();
    


    // Compilação do pdf
    shell_exec("pdflatex TimeLine.tex");
    require 'index.php';
    
    echo "<script>
            var popup = window.open('', 'PDF Popup', 'width=800,height=600');
            if (popup) {
                popup.document.write('<iframe src=\"timeLine.pdf\" width=\"100%\" style=\"height:100%\"></iframe>');
            } else {
                alert('É necessária autorização a popups para visualizar o pdf.');
            }
          </script>";
?>

