$(function(){
    var prefix = "div.centerContainer div.sendMessage ";
    var sendButton = $(prefix+"div.submit input");
    $(prefix).on("blur", "div.textInput input[type='text'], div.textArea textarea", function(){
        if(this.name === "mail"){
            return true;
        }
        if(this.name === "captcha"){
            checkCaptach($(this), 0);
            return true;
        }
        checkOnEmpty($(this));
        countErrors();
    });
    
    sendButton.on("click", function(e){
        e.preventDefault();
        checkOnEmpty($(prefix+"div.subject input"));
        checkOnEmpty($(prefix+"div.body textarea"));
        checkCaptach($(prefix+"div.captcha input"), 1);
    });
    
    var stack = {
        
    }
    
    function checkOkey(element)
    {
        stack[element.attr("name")] = 0;
        element.css("border", "1px inset green");
        element.css("backgroundColor", "#b3f1b0");
    }
    
    function checkDanied(element)
    {
        stack[element.attr("name")] = 1;
        element.css("border", "1px solid red");
        element.css("backgroundColor", "white");        
    }
    
    function checkOnEmpty(element)
    {
        if(!element.val()){
            checkDanied(element);
        }else{
            checkOkey(element);
        }
    }
    
    function checkCaptach(element,sendForm)
    {
        var data = "captcha="+element.val();
            sendAjax(data, function(msg){
                if(msg === "danied"){
                    checkDanied(element);
                }else if(msg === "Ok"){
                    checkOkey(element);
                }
                var countEr = countErrors();
                if(sendForm === 1 && countEr === 0){
                    $(prefix+"form").trigger("submit");
                }
                
            }, element, sendForm);
    }
    
    function countErrors()
    {
        var countErrors = 0;
        for(var key in stack){
            
            if(stack[key] === 1){
                countErrors++;
            }
        }
        if(countErrors > 0){
            sendButton.attr("disabled", "disabled");
        }else{
            sendButton.removeAttr("disabled");
        }
        return countErrors;
    }
    
    function sendAjax(data, func, element, sendForm)
    {
        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: data,
            dataType: "html",
            cache: false,
            async: true,
            success: function (msg) {
                if(typeof(func) === "function"){
                    func(msg);
                }
            }
        });
    }
});

