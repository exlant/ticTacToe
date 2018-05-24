/*
 * 26.02.2015 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function include(url){
    var script = window.document.createElement("script");
    script.src = url;
    document.getElementsByTagName("head")[0].appendChild(script);
}
include("libs/js/getXmlHttpRequest.js");

function requestConstruct(url,synchron){
    //this.url = url;
    this.request = getXmlHttpRequest();
    
    this.connect = function(){
        this.request.open("GET",url,synchron);
        this.request.send(null);
        if(synchron == true){
            var request = this.request;
            this.request.onreadystatechange = function(){
                if(request.readyState == 4){
                    if(!checkStatus(request))
                        return false;
                    
                    addText(request.responseText);
                    alert(request.getAllResponseHeaders());
                    
                }
            };
        }else{
            if(!checkStatus(this.request))
                return false;

            addText(this.request.responseText);
        }
    };
    
    function checkStatus(request){
        if(request.status == 200){
           return true;
        }
        alert(request.status+": "+request.statusText);
        return false;
    }
    
    
    function addText (text){
        div = document.getElementById("time");
        div.firstChild.nodeValue = text;  
    };
    
}

window.onload = function() {
    
var a = document.getElementById("setTime");
a.addEventListener("click",setTime,false);

function setTime(e) {
    e = e || event;
    try{
        e.preventDefault();
    }catch(x){
        e.returnValue = false;
    }
    var request = new requestConstruct("function.php?time=2",true);
    request.connect();
    
    return true;
}

var input = document.getElementById("cat").setCat;
input.addEventListener("keyup",getCat,false);

function getCat(e){
    e = e || event;
    
    var id = e.srcElement.value * 1;
    var request = new requestConstruct("getcategories.php?id="+id);
    request.connect();
}
};