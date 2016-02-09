$(function(){
    var prefix = "div.centerContainer div.viewUsers div.editProfile ";
    var buttonConfirm = "<input type='button' value='Подтвердить изменение'>";
    var element, helper, value;
    var data = {
            access: 1,
            object: "editProfile",
            action: "editData",
            key: "",
            value: ""
        };
    
    $(prefix+"div.login div.input input").on("blur",function(){
        if(selfLogin === this.value){
            return true;
        }
        var helperHtml = "Некорректный логин";
        setVar(this);
        var pattern = new RegExp(/^(?!empty|draw|guest_)[-A-z0-9_]+$/);
        // проверка регуляркой
        if(!this.value.match(pattern)){
            helper.html(helperHtml);
            return false;
        }
        // проверка длины логина
        if(this.value.length < minLogin || this.value.length > maxLogin){
            helper.html(helperHtml);
            return false;
        }
        // проверка на наличие в базеДанных
        sendAjax("login="+this.value, function(msg){
            if(msg === "Ok"){
                var button = $(buttonConfirm).data("key", "nick").data("value", value);
                helper.html(button);
            }else{
                helper.html("Логин занят");
            }
        });
        
    });
    $(prefix+"div.mailView div.input select").on("change",function(){
        data.key = 'mailView';
        data.value = this.value;
        sendAjax(data);       
    });
    
    $(prefix+"div.mail div.input input").on("blur",function(){
        if(mail === this.value){
            return true;
        }
        var helperHtml = "Некорректный mail";
        var pattern = new RegExp(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,6})+$/);
        setVar(this);
        if(!this.value.match(pattern)){
            helper.html(helperHtml);
            return false;
        }
        var button = $(buttonConfirm).data("key", "mail").data("value", this.value);
        helper.html(button);
    });
    
    $(prefix+"div.pass div.input input").on("blur",function(){
        setVar(this);
        if(!checkPass()){
            return false;
        }
        helper.html("Повторите пароль!");
    });
    
    function checkPass() 
    {
        if(value.length === 0){
            helper.html("Вы не ввели пароль!");
            return false;
        }
        // соответствие с минимальной длиной
        if(value.length < minPass){
            helper.html("Мин. пароль "+minPass+" символов!");
            return false;
        // соответствие с максимальной длиной
        }else if(value.length > maxPass){
            helper.html("Макс. пароль "+maxPass+" символов!");
            return false;
        }
        return true;
    }
    
    $(prefix+"div.passConfirm div.input input").on("blur",function(){
        setVar(this);
        if(!checkPass()){
            return false;
        }
        var firstPass = $(prefix+"div.pass div.input input").val();
        if(firstPass === value){
            var button = $(buttonConfirm).data("key", "pass").data("value", value);
            helper.html(button);
            return true;
        }
        helper.html("Пароли не совпадают!");
    });
    
    $(prefix).on("click", "input[type='button']", function(){
        var el = $(this);
        data.key = el.data("key");
        data.value = el.data("value");
        
        sendAjax(data, function(msg){
            if(msg === "Ok"){
                var helperText;
                if(data.key === 'nick'){
                    setTimeout(function(){
                        location.href = DOMEN+"users/"+value;
                    }, 3000);
                    helperText = "Логин был изменен!";
                }
                if(data.key === 'mail'){
                    helperText = "Почта была изменена!";
                }
                if(data.key === 'pass'){
                    helperText = "Пароль был изменен!";
                }
                helper.html(helperText);
                
            }
        });
    });
    
    function setVar($this)
    {
        element = $($this);
        value = $this.value;
        helper = element.parent().next();
    }
    
    function sendAjax(data, func)
    {
        $.ajax({
            type: "POST",
            url: DOMEN+"ajax.php",
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