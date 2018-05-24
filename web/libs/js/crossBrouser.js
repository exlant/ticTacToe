/*
 * 04.03.2015 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function constructCrossBrouser(){
        this.canselA = function(e) {                // метод для отмены действия по ссылки 
            try{                                    // входные данные: событие
                e.preventDefault();
            }catch(a){
                e.returnValue = false;
            }
        };
        this.setEventListeer = function(element,event,handler){   // метод для добавления события на элемент, входные данные: элемент, событие, функция вызывающаяся на событие
            var len = element.length;
            if(len > 0){                       
                for(var i = 0; i < len; i++){
                    this.setEventListeer(element[i],event,handler);
                }
            }else{    
                try{
                    element.addEventListener(event,handler,false);
                }catch(a){
                    element.attachEvent("on"+event,handler);
                }
            }
        };
        this.getXmlHttpRequest = function(){
            if(window.XMLHttpRequest){    //  для всех нормальных браузеров, и для IE начиная с 9 версии
                try{
                    return new XMLHttpRequest();
                }catch(e){return null;}
            }else if(window.ActiveXObject){ // для IE ниже 7 версии
                try{
                    return new ActiveXObject("Msxml2.XMLHTTP");   // IE 6  версия
                }catch(e){ return null;}
                try{
                    return new ActiveXObject("Microsoft.XMLHTTP");   // IE ниже 6 версии
                }catch(e) { return null;}
            }
            return null;
        };
        this.less10 = function(number){
            if(number > 10)
                return number;
            else
                return "0"+number;
        };
        this.arraySearch = function (value,array){
            for(var i in array){
                if(array[i] === value)
                    return i;
            }
            return -1;
        };
        
    };
