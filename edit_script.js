/*
 * edit_script.js
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
 */

window.onload = function() {
    // Separa os dados em vários arrays (Matemáticos, Instituições, Relações, 
    // Contribuições, Nacionalidades, Áreas Matemáticas, Outras Áreas)
    var dataArray = JSON.parse(dataJSON);
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

    temp.length != 0 ? splitArray.push(temp) : ""    
    splitArray = splitArray.filter(subarray => subarray.length > 0);


    // Escolhe o formulário (Instituições, Contribuições, Nacionalidade, Área Matemática, Outras Áreas)
    select = document.getElementById("selectForm");
	select.addEventListener("input", functionSelect); 
    
     
    // CRIAR LISTAS
    listaMat = document.getElementById("listaMat");
    listaInst = document.getElementById("listaInst");
    listaCont = document.getElementById("listaCont");
    listaNac = document.getElementById("listaNac");
    listaAreaMat = document.getElementById("listaAreaMat");
    listaAreaOutra = document.getElementById("listaAreaOutra");
    
    fotoLink = document.getElementById("FotoLink");
    plusInst = document.getElementById("plusInst");
    plusCont = document.getElementById("plusCont");
    goCont = document.getElementById("ContLinkGo");
    plusNac = document.getElementById("plusNac");
    plusAreaMat = document.getElementById("plusAreaMat");
    plusAreaOutra = document.getElementById("plusAreaOutra");
    
    var dataMat = splitArray[0];
    var dataInst = splitArray[1];
    var dataRel = splitArray[2];
    var dataCont = splitArray[3];
    var dataNac = splitArray[4];
    var dataAreaMat = splitArray[5];
    var dataAreaOutra = splitArray[6];

    // Matemáticos
    for (var i = 0; i < dataMat.length; i++) {
        var line = document.createElement("li");
        line.textContent = dataMat[i][2];
        if (dataMat[i][3] != null) {
            line.textContent += " " + dataMat[i][3];
        }
        
        line.addEventListener("click", (function(item) {
            return function() {
                fillFormMat(item);
            };
        })(dataMat[i]));
    
        listaMat.appendChild(line);
    }
	fotoLink.onchange = function() {
        updateFoto(fotoLink.value);
    };

    // Instituições
	plusInst.onclick = function() { 
        newInst(dataRel);
    }; 
    for (var i = 0; i < dataInst.length; i++) {
        itemInst(dataInst[i], false);
    }
    
    // Contribuições
	plusCont.onclick = function() { 
        newCont();
    }; 
    goCont.onclick = function() {
        inputLink = document.getElementById("ContLink");
        if (inputLink.value != "") {
            window.open(inputLink.value, "_blank");
        } 
    }
    for (var i = 0; i < dataCont.length; i++) {
        itemCont(dataCont[i]);
    }
    
    // Nacionalidades
    plusNac.onclick = function() { 
        newNac();
    }; 
    for (var i = 0; i < dataNac.length; i++) {
        itemNac(dataNac[i], false);
    }

    // Áreas Matemáticas
    plusAreaMat.onclick = function() { 
        newAreaMat();
    }; 
    for (var i = 0; i < dataAreaMat.length; i++) {
        itemAreaMat(dataAreaMat[i], false);
    }

    // Outras Áreas
    plusAreaOutra.onclick = function() { 
        newAreaOutra();
    }; 
    for (var i = 0; i < dataAreaOutra.length; i++) {
        itemAreaOutra(dataAreaOutra[i], false);
    }
    

    // REQUERIMENTOS DO SUBMIT DO FORM
    submitInserir = document.getElementById("actionInserir");
    submitEditar = document.getElementById("actionEditar");
    submitEliminar = document.getElementById("actionEliminar");
    submitInserirInst = document.getElementById("actionInserirInst");
    submitEditarInst = document.getElementById("actionEditarInst");
    submitEliminarInst = document.getElementById("actionEliminarInst");
    submitInserirCont = document.getElementById("actionInserirCont");
    submitEditarCont = document.getElementById("actionEditarCont");
    submitEliminarCont = document.getElementById("actionEliminarCont");
    submitInserirNac = document.getElementById("actionInserirNac");
    submitEliminarNac = document.getElementById("actionEliminarNac");
    submitInserirAreaMat = document.getElementById("actionInserirAreaMat");
    submitEliminarAreaMat = document.getElementById("actionEliminarAreaMat");
    submitInserirAreaOutra = document.getElementById("actionInserirAreaOutra");
    submitEliminarAreaOutra = document.getElementById("actionEliminarAreaOutra");

    inputId = document.getElementById("Id");
    inputIdInst = document.getElementById("InstId");
    inputIdCont = document.getElementById("ContId");
    
    inputFields = ["PrimeiroNome", "UltimoNome", 
        "AnoNasc", "AnoFalec", "FotoLink", 
        "InstId", "InstNome", "InstCidade", "InstPais", 
        "ContId", "ContNome", "ContLink"];


    // Matemáticos
    submitInserir.onclick = function() {
        if (!checkAnoNascFalec()) {
            alert("O ano de falecimento não pode ser anterior ao ano de nascimento");
            return false;
        }

        requiredFields = ["PrimeiroNome", "AnoNasc"];
        otherFields = inputFields.filter(value => !requiredFields.includes(value));
        requiredInput(requiredFields, otherFields);
    };

    submitEditar.onclick = function() {
        if(inputId.value == "") {
            alert("Nenhum matemático selecionado.");
            return false;
        }

        if (!checkAnoNascFalec()) {
            alert("O ano de falecimento não pode ser anterior ao ano de nascimento");
            return false;
        }

        requiredFields = ["PrimeiroNome", "AnoNasc"];
        otherFields = inputFields.filter(value => !requiredFields.includes(value));
        requiredInput(requiredFields, otherFields);

        if(!emptyRequired()){
            var confirm = confirmResult("Tem a certeza que pretende editar este matemático?")
            if(confirm == true) {
                inputId.disabled = false;
            }
            return confirm;
        }
    };

    submitEliminar.onclick = function() {
        if(inputId.value == "") {
            alert("Nenhum matemático selecionado.");
            return false;
        }
        
        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);
        
        var confirm = confirmResult("Tem a certeza que pretende eliminar este matemático?")
        if(confirm == true) {
            inputId.disabled = false;
        }
        return confirm;
    };

    // Instituições        
    submitInserirInst.onclick = function() {
        if (!checkInstituicoes()) {
            alert("Nenhuma instituição selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);
        
        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nPretende apenas inserir as instituições selecionadas?");
        } else if (!checkRelacoes()){
            var result = confirmResult("A omissão do tipo de relação não associa a instituição ao matemático selecionado, inserindo-a apenas na base de dados.\nPretende prosseguir?")
            if(result == true) {
                inputId.disabled = false;
            }
            return result;
        }
    };

    submitEditarInst.onclick = function() {
        if(inputIdInst.value == "") {
            alert("Nenhuma instituição selecionada.");
            return false;
        }

        requiredFields = ["InstId", "InstNome", "InstCidade", "InstPais"];
        otherFields = inputFields.filter(value => !requiredFields.includes(value));
        requiredInput(requiredFields, otherFields);

        if(!emptyRequired()){
            var confirm = confirmResult("Tem a certeza que pretende editar esta instituição?")
            if(confirm == true) {
                inputIdInst.disabled = false;
            }
            return confirm;
        }
    };

    submitEliminarInst.onclick = function() {
        if (!checkInstituicoes()) {
            alert("Nenhuma instituição selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);
        
        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nEsta ação irá eliminar as instituições selecionadas e todas as relações associadas.\nPretende prosseguir?");
        } else {
            var result = confirmResult("A omissão do tipo de relação irá eliminar todas as relações do matemático às instituições selecionadas.\nPretende prosseguir?")
            if(result == true) {
                inputId.disabled = false;
            }
            return result;
        }
    };

    // Contribuições
    submitInserirCont.onclick = function() {
        if (!checkContribuicoes()) {
            alert("Nenhuma contribuição selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);

        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nPretende apenas inserir as contribuições selecionadas?");
        } else {
            inputId.disabled = false;
        }
    };

    submitEditarCont.onclick = function() {
        if(inputIdCont.value == "") {
            alert("Nenhuma contribuição selecionada.");
            return false;
        }

        requiredFields = ["ContId", "ContNome"];
        otherFields = inputFields.filter(value => !requiredFields.includes(value));
        requiredInput(requiredFields, otherFields);

        if(!emptyRequired()){
            var confirm = confirmResult("Tem a certeza que pretende editar esta contribuição?")
            if(confirm == true) {
                inputIdCont.disabled = false;
            }
            return confirm;
        }
    };

    submitEliminarCont.onclick = function() {
        if (!checkContribuicoes()) {
            alert("Nenhuma contribuição selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);
        
        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nEsta ação irá eliminar as contribuições selecionadas e todas as relações associadas.\nPretende prosseguir?");
        } else {
            var result = confirmResult("Tem a certeza que pretende eliminar as relações entre o matemático e as contribuições selecionadas?");    
            if(result == true) {
                inputId.disabled = false;
            }
            return result;
        }
    };

    // Nacionalidades
    submitInserirNac.onclick = function() {
        if (!checkNacionalidades()) {
            alert("Nenhuma nacionalidade selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);

        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nPretende inserir apenas os dados selecionados?")
        } else {
            inputId.disabled = false;
        }
    };
    
    submitEliminarNac.onclick = function() {
        if (!checkNacionalidades()) {
            alert("Nenhuma nacionalidade selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);
        
        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nEsta ação irá eliminar as nacionalidades selecionadas e todas as relações associadas.\nPretende prosseguir?");
        } else {
            var result = confirmResult("Tem a certeza que pretende eliminar as relações entre o matemático e as nacionalidades selecionadas?");    
            if(result == true) {
                inputId.disabled = false;
            }
            return result;
        }
    };

    // Áreas Matemáticas
    submitInserirAreaMat.onclick = function() {
        if (!checkAreaMat()) {
            alert("Nenhuma área selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);

        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nPretende inserir apenas os dados selecionados?")
        } else {
            inputId.disabled = false;
        }
    };

    submitEliminarAreaMat.onclick = function() {
        if (!checkAreaMat()) {
            alert("Nenhuma área selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);
        
        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nEsta ação irá eliminar as áreas selecionadas e todas as relações associadas.\nPretende prosseguir?");
        } else {
            var result = confirmResult("Tem a certeza que pretende eliminar as relações entre o matemático e as áreas selecionadas?");    
            if(result == true) {
                inputId.disabled = false;
            }
            return result;
        }
    };

    // Outras Áreas
    submitInserirAreaOutra.onclick = function() {
        if (!checkAreaOutra()) {
            alert("Nenhuma área selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);

        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nPretende inserir apenas os dados selecionados?")
        } else {
            inputId.disabled = false;
        }
    };
    
    submitEliminarAreaOutra.onclick = function() {
        if (!checkAreaOutra()) {
            alert("Nenhuma área selecionada.")
            return false;
        }

        requiredFields = [];
        otherFields = inputFields;
        requiredInput(requiredFields, otherFields);
        
        if (inputId.value == "") {
            return confirm("Nenhum matemático selecionado.\nEsta ação irá eliminar as áreas selecionadas e todas as relações associadas.\nPretende prosseguir?");
        } else {
            var result = confirmResult("Tem a certeza que pretende eliminar as relações entre o matemático e as áreas selecionadas?");    
            if(result == true) {
                inputId.disabled = false;
            }
            return result;
        }
    };

    // RESET DO FORM
    form = document.getElementById("formEdit");
    form.onreset = function() {
        // Esconde a foto e as informações extra
        updateFoto("");
        showDetails("[]");

        // Abre o form das instituições
        document.getElementById("selectForm").value = "Instituição";
        functionSelect();

        // Esconde as caixas de relações
        listaInst = document.getElementById("listaInst");
        relDiv = listaInst.querySelectorAll(".selectRel");
        for (var i = 0; i < relDiv.length; i++) {
            relDiv[i].style = "display: none;";
        }
    }; 

    // FUNÇÕES AUXILIARES
    // Mostra mais informação relativamente ao matemático selecionado
    function showDetails(data) {
        // Reset da informação
        extraInst = document.getElementById("extraInst");
        extraCont = document.getElementById("extraCont");
        extraNac = document.getElementById("extraNac");
        extraAreaMat = document.getElementById("extraAreaMat");
        extraAreaOutra = document.getElementById("extraAreaOutra");

        extraInst.innerHTML = "";
        extraCont.innerHTML = "";
        extraNac.innerHTML = "";
        extraAreaMat.innerHTML = "";
        extraAreaOutra.innerHTML = "";
        
        // Não adicionar informação se data for vazio
        if (data.length === 0) {
            return;
        }
        
        // Instituições
        var l1 = document.createElement('p');
        if (data[10] !== null) {
            l1.textContent = "Instituições:";
            extraInst.appendChild(l1);

            var instituicoes = data[10].split("; ");
            var list = document.createElement("ul");
            
            for (var i = 0; i < instituicoes.length; i++) {
                var itemInst = document.createElement("li");
                var instituicao = instituicoes[i].split(" -- ");
                
                var txtInst = document.createTextNode(instituicao[0].replace(/\s*\(.*?\)\s*/g, ''));
                itemInst.appendChild(txtInst);
                
                var relacoes = instituicao[1].split("/ ");
                var sublist = document.createElement("ul");
                for (var j = 0; j < relacoes.length; j++) {
                    var itemRel = document.createElement("li");
                    var txtRel = document.createTextNode(relacoes[j]);
                    itemRel.appendChild(txtRel);
                    sublist.appendChild(itemRel);
                }
                itemInst.appendChild(sublist);
                
                list.appendChild(itemInst);
            }

            extraInst.appendChild(list);
        } else {
            l1.textContent = "Não há instituições associadas a este matemático.";
            extraInst.appendChild(l1);
        }

        // Contribuições
        var l2 = document.createElement("p");
        if (data[11] !== null) {
            l2.textContent = "Contribuições:";
            extraCont.appendChild(l2);
            
            var contribuicoes = data[11].split(";");
            var list = document.createElement("ul");
            
            for (var i = 0; i < contribuicoes.length; i++) {
                var itemCont = document.createElement("li");
                var contribuicao = contribuicoes[i].split("link: ");
                
                var txtCont = document.createTextNode(contribuicao[0]);
                itemCont.appendChild(txtCont);
                
                list.appendChild(itemCont);
            }
            extraCont.appendChild(list);
        } else {
            l2.textContent = "Não há contribuições associadas a este matemático.";
            extraCont.appendChild(l2);
        }

        // Nacionalidades
        var l3 = document.createElement("p");
        if (data[7] != null) {
            l3.textContent = "Nacionalidades:";
            extraNac.appendChild(l3);
            
            var nacionalidades = data[7].split(", ");
            var list = document.createElement("ul");
            
            for (var i = 0; i < nacionalidades.length; i++) {
                var itemNac = document.createElement("li");
                var txtNac = document.createTextNode(nacionalidades[i]);
                itemNac.appendChild(txtNac);
                
                list.appendChild(itemNac);
            }
            extraNac.appendChild(list);
        } else {
            l3.textContent = "Não há nacionalidades associadas a este matemático.";    
            extraNac.appendChild(l3);
        }

        // Áreas de Estudo
        var l4 = document.createElement("p");
        if (data[8] != null) {
            l4.textContent = "Áreas Matemáticas:";
            extraAreaMat.appendChild(l4);
            
            var areasMat = data[8].split(", ");
            var list = document.createElement("ul");
            
            for (var i = 0; i < areasMat.length; i++) {
                var itemAreaMat = document.createElement("li");
                var txtAreaMat = document.createTextNode(areasMat[i]);
                itemAreaMat.appendChild(txtAreaMat);
                
                list.appendChild(itemAreaMat);
            }
            extraAreaMat.appendChild(list);
        } else {
            l4.textContent = "Não há áreas matemáticas associadas a este matemático.";    
            extraAreaMat.appendChild(l4);
        }
    
        // Outras Áreas de Estudo
        var l5 = document.createElement("p");
        if (data[9] != null) {
            l5.textContent = "Áreas (Não Matemáticas):";
            extraAreaOutra.appendChild(l5);
            
            var areasOutra = data[9].split(", ");
            var list = document.createElement("ul");
            
            for (var i = 0; i < areasOutra.length; i++) {
                var itemAreaOutra = document.createElement("li");
                var txtAreaOutra = document.createTextNode(areasOutra[i]);
                itemAreaOutra.appendChild(txtAreaOutra);
                
                list.appendChild(itemAreaOutra);
            }
            extraAreaOutra.appendChild(list);
        } else {
            l5.textContent = "Não há áreas (não matemáticas) associadas a este matemático.";    
            extraAreaOutra.appendChild(l5);
        }
        
    }

    // Mostra o formulário auxiliar e a lista de informação pretendidos e esconde os restantes
    function functionSelect() {
		select = document.getElementById("selectForm").value; 

        divInst = document.getElementById("instituicao");
        divCont = document.getElementById("contribuicao");
        divNac = document.getElementById("nacionalidade");
        divAreaMat = document.getElementById("areaMatematica");
        divAreaOutra = document.getElementById("areaOutra");

        extraInst = document.getElementById("extraInst");
        extraCont = document.getElementById("extraCont");
        extraNac = document.getElementById("extraNac");
        extraAreaMat = document.getElementById("extraAreaMat");
        extraAreaOutra = document.getElementById("extraAreaOutra");

		
        switch (select) {
            case "Instituição":
                divInst.style.display = "block";
                divCont.style.display = "none";
                divNac.style.display = "none";
                divAreaMat.style.display = "none";
                divAreaOutra.style.display = "none";
    
                extraInst.style.display = "block";
                extraCont.style.display = "none";
                extraNac.style.display = "none";
                extraAreaMat.style.display = "none";
                extraAreaOutra.style.display = "none";
                break;
            case "Contribuição":
                divInst.style.display = "none";
                divCont.style.display = "block";
                divNac.style.display = "none";
                divAreaMat.style.display = "none";
                divAreaOutra.style.display = "none";
    
                extraInst.style.display = "none";
                extraCont.style.display = "block";
                extraNac.style.display = "none";
                extraAreaMat.style.display = "none";
                extraAreaOutra.style.display = "none";
                break;
            case "Nacionalidade":
                divInst.style.display = "none";
                divCont.style.display = "none";
                divNac.style.display = "block";
                divAreaMat.style.display = "none";
                divAreaOutra.style.display = "none";
    
                extraInst.style.display = "none";
                extraCont.style.display = "none";
                extraNac.style.display = "block";
                extraAreaMat.style.display = "none";
                extraAreaOutra.style.display = "none";
                break;
            case "AreaMatematica":
                divInst.style.display = "none";
                divCont.style.display = "none";
                divNac.style.display = "none";
                divAreaMat.style.display = "block";
                divAreaOutra.style.display = "none";
    
                extraInst.style.display = "none";
                extraCont.style.display = "none";
                extraNac.style.display = "none";
                extraAreaMat.style.display = "block";
                extraAreaOutra.style.display = "none";
                break;
            case "AreaOutra":
                divInst.style.display = "none";
                divCont.style.display = "none";
                divNac.style.display = "none";
                divAreaMat.style.display = "none";
                divAreaOutra.style.display = "block";
    
                extraInst.style.display = "none";
                extraCont.style.display = "none";
                extraNac.style.display = "none";
                extraAreaMat.style.display = "none";
                extraAreaOutra.style.display = "block";
                break;
        }
	}

    // Mostra a imagem no formulário dos matemáticos consoante o link inserido
    function updateFoto(link) {
        fotoImg = document.getElementById("FotoImg");
        fotoImg.src = link;
        
        if(link != "") {
            fotoImg.style.display = "block";
        } else {
            fotoImg.style.display = "none";
        }
    }

    // Preenche o formulário com os dados do matemático escolhido
    function fillFormMat(data) {
        // Campos do formulário
        Id = document.getElementById("Id");
        PrimeiroNome = document.getElementById("PrimeiroNome");
        UltimoNome = document.getElementById("UltimoNome");
        AnoNasc = document.getElementById("AnoNasc");
        AnoFalec = document.getElementById("AnoFalec");
        FotoLink = document.getElementById("FotoLink");

        // Dados do matemático
        Id.value = data[0];
        PrimeiroNome.value = data[2];
        UltimoNome.value = data[3];
        AnoNasc.value = Math.abs(data[4]);
        if (data[5] === null) {
            AnoFalec.value = "";
        } else {
           AnoFalec.value = Math.abs(data[5]); 
        }
        FotoLink.value = data[1];
        
        if(data[4]<0){
            document.getElementById("AnoNasc_ac").checked = true;
        } else {
            document.getElementById("AnoNasc_dc").checked = true;
        }      
        
        if(data[5]<0){
            document.getElementById("AnoFalec_ac").checked = true;
        } else {
            document.getElementById("AnoFalec_dc").checked = true;
        }
        
        switch(data[6]) {
            case 'M':
                document.getElementById("SexoM").checked = true;
                break;
            case 'F':
                document.getElementById("SexoF").checked = true;
                break;
            default:
                document.getElementById("SexoDesc").checked = true;
        }

        updateFoto(FotoLink.value);

        showDetails(data);
    }

    // Cria item da lista de instituições
    function itemInst(data, checkStatus) {
        // Cria a checkbox para selecionar a instituição
        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.id = "checkInst" + data[0]; // id da instituição
        checkbox.name = "inst[]";
        checkbox.value = data;
        checkbox.checked = checkStatus;

        // Mostra a lista das relações caso a instituição esteja selecionada e esconde caso contrário
        checkbox.addEventListener("click", (function(item) {
            return function() {
                listRel = document.getElementById("checkRel" + data[0]);
                if(this.checked) {
                    listRel.style = "display:block";
                } else {
                    listRel.style = "display:none";
                }
            };
        })(data));
        listaInst.appendChild(checkbox);

        // Mostra o nome da instituição
        var label = document.createElement("label");
        label.htmlFor = "checkInst" + data[0]; // checkbox correspondente
        label.appendChild(document.createTextNode(data[1])); // nome da instituição
        
        // Preenche o formulário com os dados da instituição
        label.addEventListener("click", (function(item) {
            return function() {
                fillFormInst(item);
            };
        })(data));
        listaInst.appendChild(label);

        listaInst.appendChild(document.createElement("br"));

        // Cria a lista das relações para esta instituição
        var listRel = document.createElement("div");
        listRel.id = "checkRel" + data[0]; // id da instituição
        listRel.className = "selectRel"

        for (var j = 0; j < dataRel.length; j++) {
            var checkboxRel = document.createElement("input");
            checkboxRel.type = "checkbox";
            checkboxRel.name = "selectRel" + data[0] + "[]"; // id da instituição
            checkboxRel.value = dataRel[j]; // nome da relação
            checkboxRel.id = dataRel[j] + data[0];
            listRel.appendChild(checkboxRel);

            var label = document.createElement("label");
            label.htmlFor = dataRel[j] + data[0]; // checkbox correspondente
            label.appendChild(document.createTextNode(dataRel[j])); // nome da relação
            label.class = "checkbox-label";
            listRel.appendChild(label);

            listRel.appendChild(document.createElement("br"));
        }
        if (checkStatus) {
            listRel.style = "display: block;";
        } else {
            listRel.style = "display: none;";
        }
        listaInst.appendChild(listRel);
    }

    // Preenche o formulário com os dados da instituição escolhida
    function fillFormInst(data) {
        // Campos do formulário
        InstId=document.getElementById("InstId");
        InstNome=document.getElementById("InstNome");
        InstCidade=document.getElementById("InstCidade");
        InstPais=document.getElementById("InstPais");
        
        // Dados da instituição
        InstId.value=data[0];
        InstNome.value=data[1];
        InstCidade.value=data[2];
        InstPais.value=data[3];
    }
    
    // Adiciona uma nova instituição à lista com os dados inseridos no formulário
    var numInst = 0; // número de novas contribuições adicionadas
    function newInst(dataRel) {
        // Campos do formulário
        InstNome = document.getElementById("InstNome");
        InstCidade = document.getElementById("InstCidade");
        InstPais = document.getElementById("InstPais");

        
        // Adiciona o novo item e repõe o formulário apenas se todos os campos obrigatórios estiverem preenchidos
        if(InstNome.value != "" && InstCidade.value != "" && InstPais.value != "") {
            // Dados do formulário
            numInst -= 1;
            var data = [numInst, InstNome.value, InstCidade.value, InstPais.value];
            
            itemInst(data, true);
            InstNome.value = "";
            InstCidade.value = "";
            InstPais.value = "";
        } else {
            alert("Devem ser preenchidos todos os campos para adicionar a nova instituição.");
        }
    }

    // Cria item da lista de contribuições
    function itemCont(data, checkStatus) {
        // Cria a checkbox para selecionar a contribuição
        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.id = "checkCont" + data[0]; // id da contribuição
        checkbox.name = "cont[]";
        checkbox.value = data;
        checkbox.checked = checkStatus;
        listaCont.appendChild(checkbox);

        // Mostra o nome da contribuição
        var label = document.createElement("label");
        label.htmlFor = "checkCont" + data[0]; // checkbox correspondente
        label.appendChild(document.createTextNode(data[1])); // nome da contribuição
        
        // Preenche o formulário com os dados da contribuição
        label.addEventListener("click", (function(item) {
            return function() {
                fillFormCont(item);
            };
        })(data));
        listaCont.appendChild(label);

        listaCont.appendChild(document.createElement("br"));
    }

    // Preenche o formulário com os dados da contribuição escolhida
    function fillFormCont(data) {
        // Campos do formulário
        ContId = document.getElementById("ContId");
        ContNome = document.getElementById("ContNome");
        ContLink = document.getElementById("ContLink");

        // Dados da contribuição
        ContId.value=data[0];
        ContNome.value=data[1];
        ContLink.value=data[2];
    }
    
    // Adiciona uma nova contribuição à lista com os dados inseridos no formulário
    var numCont = 0; // número de novas contribuições adicionadas
    function newCont(dataRel) {
        // Campos do formulário
        ContNome = document.getElementById("ContNome");
        ContLink = document.getElementById("ContLink");
        
        // Comparar o link inserido ao formato
        var regexp = /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
    
        // Adiciona o novo item e repõe o formulário apenas se o campo "Nome" estiver preenchido
        if(ContNome.value != "") {
            if (ContLink.value != "" && !regexp.test(ContLink.value)) {
                alert("Por favor insira um link válido.");
                return false;
            }
            
            // Dados do formulário
            numCont -= 1;
            var data = [numCont, ContNome.value, ContLink.value];

            itemCont(data, true);
            ContNome.value = "";
            ContLink.value = "";
            
        } else {
            alert("Deve ser preenchido o campo de 'Nome' para adicionar a nova contribuição.");
        }  
    }

    // Cria item da lista de nacionalidades
    function itemNac(data, checkStatus) {
        // Cria a checkbox para selecionar a nacionalidade
        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.id =  "checkNac" + data; // nome da nacionalidade
        checkbox.name = "nac[]";
        checkbox.value = data;
        checkbox.checked = checkStatus;
        listaNac.appendChild(checkbox);

        // Mostra o nome da nacionalidade
        var label = document.createElement("label");
        label.htmlFor = "checkNac" + data; // checkbox correspondente
        label.appendChild(document.createTextNode(data));
        label.class = "checkbox-label";
        listaNac.appendChild(label);

        listaNac.appendChild(document.createElement("br"));
    }

    // Adiciona uma nova nacionalidade à lista com os dados inseridos no formulário
    function newNac() {
        // Campos do formulário
        NacNome = document.getElementById("NacNome");

        // Adiciona o novo item e repõe o formulário apenas se o campo "Nome" estiver preenchido
        if(NacNome.value != "") {
            itemNac(NacNome.value, true);
            NacNome.value = "";
        }
    }

    // Cria item da lista de áreas matemáticas
    function itemAreaMat(data, checkStatus) {
        // Cria a checkbox para selecionar a área
        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.id = "checkAreaMat" + data; // nome da área
        checkbox.name = "areaMat[]";
        checkbox.value = data;
        checkbox.checked = checkStatus;
        listaAreaMat.appendChild(checkbox);

        // Mostra o nome da área
        var label = document.createElement("label");
        label.htmlFor = "checkAreaMat" + data; // checkbox correspondente
        label.appendChild(document.createTextNode(data));
        label.class = "checkbox-label";
        listaAreaMat.appendChild(label);

        listaAreaMat.appendChild(document.createElement("br"));
    }

    // Adiciona uma nova área à lista com os dados inseridos no formulário
    function newAreaMat() {
        // Campos do formulário
        AreaMatNome = document.getElementById("AreaMatNome");

        // Adiciona o novo item e repõe o formulário apenas se o campo "Nome" estiver preenchido
        if(AreaMatNome.value != "") {
            itemAreaMat(AreaMatNome.value, true);
            AreaMatNome.value = "";
        }
    }

    // Cria item da lista de outras áreas
    function itemAreaOutra(data, checkStatus) {
        // Cria a checkbox para selecionar a área
        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.id = "checkAreaOutra" + data; // nome da área
        checkbox.name = "areaOutra[]";
        checkbox.value = data;
        checkbox.checked = checkStatus;
        listaAreaOutra.appendChild(checkbox);

        // Item
        var label = document.createElement("label");
        label.htmlFor = "checkAreaOutra" + data; // checkbox correspondente
        label.appendChild(document.createTextNode(data));
        label.class = "checkbox-label";
        listaAreaOutra.appendChild(label);

        listaAreaOutra.appendChild(document.createElement("br"));
    }

    // Adiciona uma nova área à lista com os dados inseridos no formulário
    function newAreaOutra() {
        // Campos do formulário
        AreaOutraNome = document.getElementById("AreaOutraNome");

        // Adiciona o novo item e repõe o formulário apenas se o campo "Nome" estiver preenchido
        if(AreaOutraNome.value != "") {
            itemAreaOutra(AreaOutraNome.value, true);
            AreaOutraNome.value = "";
        }
    }

    // Verifica se o ano de nascimento é anterior ao ano de nascimento
    function checkAnoNascFalec () {
        anoNasc = document.getElementById("AnoNasc").value;
        anoFalec = document.getElementById("AnoFalec").value;

        if (anoFalec == "") {
            return true;
        }

        anoNasc_ac = document.getElementById("AnoNasc_ac").checked;
        anoFalec_ac = document.getElementById("AnoFalec_ac").checked;

        if (anoNasc_ac) {
            anoNasc = -anoNasc;
        }
        if (anoFalec_ac) {
            anoFalec = -anoFalec;
        }

        return anoNasc<=anoFalec;
    }

    // Torna os campos de input indicados em required e os outros em não required
    function requiredInput(requiredFields, otherFields) {
        for (var i = 0; i < requiredFields.length; i++) {
            document.getElementById(requiredFields[i]).setAttribute("required", "required");
        };

        for (var i = 0; i < otherFields.length; i++) {
            document.getElementById(otherFields[i]).removeAttribute("required");
        };
    }

    // Verifica se algum campo required está vazio
    function emptyRequired() {
        requiredFields = document.querySelectorAll("input[required]");
        for (var i = 0; i < requiredFields.length; i++) {
            if(requiredFields[i].value == "") {
                return true;
            }
        }
        return false;
    }

    // Verifica se há instituições selecionadas
    function checkInstituicoes() {
        listaInst = document.getElementById("listaInst");
        selectedInst = listaInst.querySelectorAll("input[type='checkbox'][name='inst[]']:checked");
        if (selectedInst.length == 0) {
                return false;
        }
        return true;
    }

    // Verifica se as instituições selecionadas têm as relações escolhidas
    function checkRelacoes() {
        listaInst = document.getElementById("listaInst");
        selectedInst = listaInst.querySelectorAll("input[type='checkbox'][name='inst[]']:checked");
        
        for (var i = 0; i < selectedInst.length; i++) {
            var instId = selectedInst[i].id.replace("checkInst","");
            listaRel = document.getElementById("checkRel" + instId); 
            selectedRel = listaRel.querySelectorAll("input[type='checkbox']:checked");
            if (selectedRel.length == 0) {
                return false;
            }
        }
        return true;
    }  
    
    // Verifica se há instituições selecionadas
    function checkContribuicoes() {
        listaCont = document.getElementById("listaCont");
        selectedCont = listaCont.querySelectorAll("input[type='checkbox'][name='cont[]']:checked");
        if (selectedCont.length == 0) {
                return false;
        }
        return true;
    }

    // Verifica se há contribuições selecionadas
    function checkContribuicoes() {
        listaCont = document.getElementById("listaCont");
        selectedCont = listaCont.querySelectorAll("input[type='checkbox'][name='cont[]']:checked");
        if (selectedCont.length == 0) {
                return false;
        }
        return true;
    }

    // Verifica se há nacionalidades selecionadas
    function checkNacionalidades() {
        listaNac = document.getElementById("listaNac");
        selectedNac = listaNac.querySelectorAll("input[type='checkbox'][name='nac[]']:checked");
        if (selectedNac.length == 0) {
                return false;
        }
        return true;
    }
    
    // Verifica se há áreas matemáticas selecionadas
    function checkAreaMat() {
        listaAreaMat = document.getElementById("listaAreaMat");
        selectedAreaMat = listaAreaMat.querySelectorAll("input[type='checkbox'][name='areaMat[]']:checked");
        if (selectedAreaMat.length == 0) {
                return false;
        }
        return true;
    }
    
    // Verifica se há * selecionadas
    function checkAreaOutra() {
        listaAreaOutra = document.getElementById("listaAreaOutra");
        selectedAreaOutra = listaAreaOutra.querySelectorAll("input[type='checkbox'][name='areaOutra[]']:checked");
        if (selectedAreaOutra.length == 0) {
                return false;
        }
        return true;
    }

    // Devolve o resultado de uma mensagem de confirmação
    function confirmResult(message) {
        return confirm(message);
    }
}
