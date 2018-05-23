/* 
 * 26.02.2015
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
// создаем xml http запрос
function getXmlHttpRequest(){
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
    
}



