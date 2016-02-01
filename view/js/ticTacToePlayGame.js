$(function(){
    $("table.type2d").moveToCenter(20,40);
    //отправка запроса на ход назад, ничью, сдаться, 
    $("div.centerContainer div.wrapper div.field div.fieldButton div").on("click", function(){
        if(this.id === 'outGame' || this.id === 'surrender'){
            var data = {
                access: 1,
                object: "tictactoe",
                action: "exitFromGame",
                value: this.id
            };
            sendAjax(data, data.value);
            
            return true;
        }
        
        sendQuery(2, this);
    });
    
    $("div.centerContainer div.wrapper div.users").on("click", "div.button", function(){
        if(this.id === "buttonNo"){
            var data = {
                access: 1,
                object: "tictactoe",
                action: "exitFromGame",
                value: "outGame"
            };
            sendAjax(data, data.value);
        }else if(this.id === "buttonYes"){
            var data = {
                access: 1,
                object: "tictactoe",
                action: "sendQuery",
                query: "playAgain",
                value: 1
            };
            sendAjax(data, "sendQuery");
        }
    });
    
    $("div.centerContainer div.wrapper div.field").on("click", "a", function(e){
        e.preventDefault();
        var data = {
            access: 1,
            object: "tictactoe",
            action: "playerMove",
            value: JSON.stringify($(this).data())
            };
        sendAjax(data, data.action);
    });
        
    var stack = {
        queryDialog: 0,
        divDialog: null,
        issetQuery: null
    };
    // массив с запросами 
    var text = {
        moveBack: 'сделать ход назад!',
        draw: 'ничью!',
        playAgain: 'сыграть еще раз!',
        confirm: 'confirm'
    };
    
    function updateData()
    {
        var updateInterval = (blitz === "yes") ? 1000 : 3000;
        var data = {
            access: 1,
            object: "tictactoe",
            action: "updatePlayData",
            change: change
        };
        setInterval(function(){
            data.change = change;
            sendAjax(data, data.action);
        },updateInterval);
    }
    updateData();
    
    function clear(){
        if(stack.divDialog){
            stack.divDialog.remove();
            stack.queryDialog = 0;
        }
            
    };
        
    function createDialogWindow(text, buttons)
    {       
        // обозначаем что показали диалоговое окно
        stack.queryDialog = 1;
        
        if(buttons === 1){
            buttons = "<input name='Ok' type='button' value='Ok'>";
            
            
        }else if(buttons === 2){
            buttons = "<input name='Yes' type='button' value='Да'> "
                     +"<input name='No' type='button' value='Нет'>";
        }
        
        var html = "<div class='dialogWindow'>"
                       +"<div class='header'><img src='images/close.gif'></div>"
                       +"<div class='text'>"+text+"</div>"
                       +"<div class='buttons'>"+buttons+"</div>"
                   +"</div>";
        $("body").prepend(html);
        
        stack.divDialog = $("div.dialogWindow").moveToCenter();
        var img = $("div.dialogWindow div.header img");
        img.on("mouseover", function(){
            img.attr("src", "images/close_on.gif");
            img.css("cursor","pointer");
        });
        img.on("mouseout", function(){
            img.attr("src", "images/close.gif");
        });
        img.on("click", function(){
            clear();
            sendQuery(-1);
        });
        stack.divDialog.on("click", "input", function(){
            var value = 1;
            if(this.name === "No"){
                value = -1;
            }
            sendQuery(value);
            clear();
            
        });
    }
    
    // отправляет запрос на ход назад, ничью, сыграть еще раз
    function sendQuery(value, evant)
    {
        var query = (stack.issetQuery) ? stack.issetQuery : evant.id;
        
        if(text[query]){
            var data = {
                access: 1,
                object: "tictactoe",
                action: "sendQuery",
                query: query,
                value: value
            };
            sendAjax(data, "sendQuery");
        }
        stack.issetQuery = null;
    }
    
    function sendAjax(data, type)
    {
        // перед выходом ставим 0 в запросы playAgain
        if(type === "outGame"){
            var playAgain = {
                access: 1,
                object: "tictactoe",
                action: "sendQuery",
                query: "playAgain",
                value: 0
            };
            sendAjax(playAgain, "sendQuery");
        }
        $.ajax({
            type: "POST",
            url: "http://tictactoe.develop/ajax.php",
            data: data,
            dataType: "json",
            async: true,
            success: function (msg) {
                //console.log(msg);
                if(type === "updatePlayData"){
                    if(msg.field){
                        $("div.field table").replaceWith(msg.field);
                        $("div.field table").moveToCenter(20,40);
                        $("div.users div.wrapperUsers").replaceWith(msg.users);
                        change = msg.change;
                    }
                    if(msg.queries && stack.queryDialog === 0){
                        var message = "Игрок "+msg.queries.login+", предлагает "+text[msg.queries.query];
                        stack.issetQuery = msg.queries.query;
                        createDialogWindow(message, 2);                 
                    }
                    if(msg.queries && msg.queries.value === -1){
                            clear();
                            stack.issetQuery = "confirm";
                            var message = "Игрок "+msg.queries.login+", отклонил запрос '"+text[msg.queries.query]+"'";
                            createDialogWindow(message, 1);
                            return true;
                    }
                }
                if(type === "outGame"){
                    location.href = DOMEN+'/'+TICTACTOE;
                }
                if(type === "playerMove"){
                    //console.log(msg);
                    var data = {
                        access: 1,
                        object: "tictactoe",
                        action: "updatePlayData",
                        change: change
                    };
                    sendAjax(data, data.action);
                }
            }
        });
    }
});