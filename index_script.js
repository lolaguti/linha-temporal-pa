/*
 * index_script.js
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

function openForm() {
    document.getElementById("myForm").style.display = "block";
}
function closeForm() {
    document.getElementById("myForm").style.display = "none";
}

window.onload = function() {
    // Array proveniente da base de dados
    var dataArray = JSON.parse(dataJSON);
    
    // Aviso de ausência de resultados
    if (dataArray.length===0){
        alert("A pesquisa não devolveu nenhum resultado.");
    }

    // Desativar botão de edição para não editores e botão de login para editores (já fizeram login)
    editBtn = document.getElementById("editBtn");
    loginBtn = document.getElementById("loginBtn");
    if (role == "editor") {
        loginBtn.disabled = true;
    } else {
        editBtn.disabled = true;
    }
    
    createContainers(dataArray);
    

    // Cria as caixas da timeline
    function createContainers(dataArray){
        var timelineContainer = document.getElementById('timeline-container');
        
        for (var i = 0; i < dataArray.length; i++) {
            var container = document.createElement('div');
            container.className = i % 2 !== 0 ? 'container right' : 'container left';

            var content = document.createElement('div');
            content.className = 'content';

            // Nome
            var l1 = document.createElement('h2');
            l1.textContent = dataArray[i].PrimeiroNome;
            if (dataArray[i].UltimoNome != null) {
                l1.textContent += " " + dataArray[i].UltimoNome;
            }
            
            content.appendChild(l1);
            
            // Ano de Nascimento e Falecimento
            var l2 = document.createElement('h3');
            if (dataArray[i].AnoFalec !== null ) {
                if (dataArray[i].AnoNasc < 0 && dataArray[i].AnoFalec < 0) {
                    l2.textContent = (-dataArray[i].AnoNasc) + " a.C. - " + (-dataArray[i].AnoFalec) + " a.C."
                } else if (dataArray[i].AnoNasc < 0 && dataArray[i].AnoFalec > 0) {
                    l2.textContent = (-dataArray[i].AnoNasc) + " a.C. - " + dataArray[i].AnoFalec
                } else if (dataArray[i].AnoNasc > 0 && dataArray[i].AnoFalec > 0) {
                    l2.textContent = dataArray[i].AnoNasc + " - " + dataArray[i].AnoFalec
                }
            } else {
                if (dataArray[i].AnoNasc < 0) {
                    l2.textContent = -(dataArray[i].AnoNasc ) + " a.C."
                } else {
                    l2.textContent = dataArray[i].AnoNasc
                }
            }
            
            content.appendChild(l2);

            
            // Evento de "click"
            content.addEventListener("click", (function(data) { 
                return function() { 
                    showDetails(data); 
                };
            })(dataArray[i]));
    

            container.appendChild(content);
            timelineContainer.appendChild(container);
        }
    }

    // Cria os popups
    function showDetails(data) {
        // Elimina o popup existente (se este existir)
        var popupOld = document.querySelector('.popup');

        if (popupOld) {
            popupOld.remove();
        }

        // Cria o popup novo
        var popup = document.createElement('div');
        popup.className = 'popup';
        
        var contentWrapper = document.createElement('div');
        contentWrapper.className = 'popup-content-wrapper';
        
        // Foto
        if (data.Foto != null && data.Foto.match(/\.(jpeg|jpg|gif|png)$/) != null) {
            var figure = document.createElement('figure');
            var foto = document.createElement('img');
            foto.src = data.Foto;
            foto.className = 'popup-foto';

            var figcaption = document.createElement('figcaption');
            figcaption.textContent = 'Fonte: '+data.Foto;

            figure.appendChild(foto);
            figure.appendChild(figcaption);
            contentWrapper.appendChild(figure);
        }
    
        
        var content = document.createElement('div');
        content.className = 'popup-content';
        
        // Nome
        var l1 = document.createElement('h2');
        l1.textContent = data.PrimeiroNome;
        if (data.UltimoNome != null) {
            l1.textContent += " " + data.UltimoNome;
        }
        content.appendChild(l1);

             
        // Ano de Nascimento e Falecimento
        var l2 = document.createElement('p');
        if (data.AnoNasc < 0) {
            l2.textContent = 'Ano de Nascimento: ' + (-data.AnoNasc) + ' a.C.';
        } else {
            l2.textContent = 'Ano de Nascimento: ' + data.AnoNasc;
        }
        content.appendChild(l2);
    
        if (data.AnoFalec !== null ) {
            var l3 = document.createElement('p');
            if (data.AnoFalec < 0) {
                l3.textContent = 'Ano de Falecimento: ' + (-data.AnoFalec) + ' a.C.';
            } else {
                l3.textContent = 'Ano de Falecimento: ' + data.AnoFalec;
            }
            content.appendChild(l3);
        }

        // Sexo
        if (data.Sexo != "desconhecido") {
            var l4 = document.createElement('p');
            l4.textContent = 'Sexo: ' + data.Sexo;
            content.appendChild(l4);
        }
        
        // Nacionalidades
        if (data.Nacionalidades != null) {
            var l5 = document.createElement('p');
            if (data.Nacionalidades.indexOf(',') > -1) {
                l5.textContent = 'Nacionalidades: ' + data.Nacionalidades;    
            } else {
                l5.textContent = 'Nacionalidade: ' + data.Nacionalidades;
            }
            content.appendChild(l5);
        }

        // Áreas de Estudo
        if (data.AreaMat != null) {
            var l6 = document.createElement('p');
            if (data.AreaMat.indexOf(',') > -1) {
                l6.textContent = 'Áreas de Estudo: ' + data.AreaMat;    
            } else {
                l6.textContent = 'Área de Estudo: ' + data.AreaMat;
            }
            content.appendChild(l6);
        }
    
        // Outras Áreas de Estudo
        if (data.AreaOutra != null) {
            var l7 = document.createElement('p');
            if (data.AreaOutra.indexOf(',') > -1) {
                l7.textContent = 'Outras áreas de Estudo: ' + data.AreaOutra;    
            } else {
                l7.textContent = 'Outra área de Estudo: ' + data.AreaOutra;
            }
            content.appendChild(l7);
        }
    
        // Instituições        
        if (data.Instituicoes !== null) {
            var l8 = document.createElement('p');
            var instituicoes = data.Instituicoes.split('; ');
    
            if (instituicoes.length > 1) {
                l8.textContent = 'Instituições:';
                content.appendChild(l8);
    
                var list = document.createElement('ul');
                
                for (var i = 0; i < instituicoes.length; i++) {
                    var itemInst = document.createElement('li');
                    var instituicao = instituicoes[i].split(" -- ");
                    //alert (instituicao[0]);
                    // alert (instituicao[1]);
                    
                    if (instituicao[1].indexOf("/")>-1) {
                        var txtInst = document.createTextNode(instituicao[0]);
                        itemInst.appendChild(txtInst);
                        
                        var relacoes = instituicao[1].split("/ ");

                        var sublist = document.createElement('ul');
                        for (var j = 0; j < relacoes.length; j++) {
                            var itemRel = document.createElement('li');
                            var txtRel = document.createTextNode(relacoes[j]);
                            itemRel.appendChild(txtRel);
                            sublist.appendChild(itemRel);
                        }
                        itemInst.appendChild(sublist);
                    } else {
                        var itemInst = document.createElement('li');
                        var txtInst = document.createTextNode(instituicao[0] + " - " + instituicao[1]);
                        itemInst.appendChild(txtInst);
                    }
                    
                    list.appendChild(itemInst);
                }
                content.appendChild(list);
            } else {
                var instituicao = instituicoes[0].split(" -- ");
                l8.textContent = instituicao[0] + " - " + instituicao[1];
                content.appendChild(l8);
            }
        }
   
        // Contribuições
        if (data.Contribuicoes != null) {
            var l9 = document.createElement('div');
            var contribuicoes = data.Contribuicoes.split(';');
    
            var contribuicao = [];
            if (contribuicoes.length > 1) {
                for (var i = 0; i < contribuicoes.length; i++) {
                    contribuicao = contribuicoes[i].split('link: ');
    
                    var btn = document.createElement('button');
                    btn.innerText = contribuicao[0];
    
                    if (contribuicao[1] != null) {
                        btn.addEventListener('click', (function(link) { 
                            return function() { 
                                openLink(link); 
                            };
                        })(contribuicao[1]));
                    }
                    l9.appendChild(btn);
                } 
            } else {
                contribuicao = contribuicoes[0].split('link: ');
    
                var btn = document.createElement('button');
                btn.innerText = contribuicao[0];
    
                if (contribuicao[1] != null) {
                    btn.addEventListener('click', (function(link) { 
                        return function() { 
                            openLink(link); 
                        };
                    })(contribuicao[1]));
                }
                l9.appendChild(btn);
            }
            content.appendChild(l9);
        }
    
        // Evento de fechar o popup
        popup.addEventListener('click', function() {
            document.body.removeChild(popup);
        });


        contentWrapper.appendChild(content);
        popup.appendChild(contentWrapper);
        document.body.appendChild(popup);
    }
   
    function openLink(link) {
        window.open(link, '_blank');
    }
}

