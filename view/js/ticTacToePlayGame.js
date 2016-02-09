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
            
            var func = (this.id === 'outGame') ? function(msg){
                location.href = DOMEN+'/'+TICTACTOE;
            } : null;
            playAgain(0);
            sendAjax(data, func);
            return true;
        }
        
        // запросы на moveBack and draw
        sendQuery(2, this);
    });
    function playAgain(value)
    {
        var data = {
            access: 1,
            object: "tictactoe",
            action: "sendQuery",
            query: "playAgain",
            value: value
        };
        sendAjax(data);   
    }
    
    $("div.centerContainer div.wrapper div.users").on("click", "#takePlace", function(e){
        e.preventDefault();
        var figure = $(this).data("figure");
        var data = {
            access: 1,
            object: "tictactoe",
            action: "takePlace",
            value: figure
        };
        sendAjax(data);   
    });
    
    $("div.centerContainer div.wrapper div.users").on("click", "div.button", function(){
        if(this.id === "buttonNo"){
            var data = {
                access: 1,
                object: "tictactoe",
                action: "exitFromGame",
                value: "outGame"
            };
            var func = function(msg){
                location.href = DOMEN+'/'+TICTACTOE;
            };
            sendAjax(data, func);
        }else if(this.id === "buttonYes"){
            playAgain(1);
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
        var func = function(msg){
            updateData()
        };
        sendAjax(data, func);
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
        var data = {
            access: 1,
            object: "tictactoe",
            action: "updatePlayData",
        };
        var func = function(msg){
            if(msg.field){
                $("div.field table").replaceWith(msg.field);
                $("div.field table").moveToCenter(20,40);
                $("div.users div.wrapperUsers").replaceWith(msg.users);
                change = msg.change;
            }
            
            if(msg.time){
                $("div.users div.wrapperUsers div.players ul li span.movingTime").text(msg.time);
            }
            
            var winner = $("div.centerContainer div.wrapper div.users div.roomParameters ul li span.winner");
            if(winner !== msg.winner){
                winner.text(msg.winner);
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
            }
        }
        setInterval(function(){
            data.change = change;
            sendAjax(data, func);
        },1000);
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
                       +"<div class='header'>Сообщение</div>"
                       +"<div class='text'>"+text+"</div>"
                       +"<div class='buttons'>"+buttons+"</div>"
                   +"</div>";
        $("body").prepend(html);
        
        stack.divDialog = $("div.dialogWindow").moveToCenter();
        
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
            sendAjax(data);
        }
        stack.issetQuery = null;
    }
    
    function sendAjax(data, func)
    {
        $.ajax({
            type: "POST",
            url: "/ajax.php",
            data: data,
            dataType: "json",
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