$(function () {
    var stack = {
        roomsHash: '',
        addPlayersHash: '',
        roomTitle: '',
    };
    var playersContainer = $("div.roomAddPlayers div.playersContainer");
    var roomsContainer = $("table.viewRoom tbody");
    var roomTitle = $("div.roomAddPlayers div.title");
    
    $("table.viewRoom").on("click", "tbody tr", function () {
        var href = $(this).find("td#enterRoom a").attr("href");
        if (href) {
            location.href = href;
        }
    });
    
    $("input#checkPoint").on("click", function(){
        if($(this).prop('checked')){
           $("div#points").css("display", "inline-block"); 
        }else{
           $("div#points").css("display", "none"); 
        }
    });
    
    function updatePage()
    {
        var data = {
            access: 1,
            object: "tictactoe",
            action: "updateRoomsPage"
        };
        
        setInterval(function () {
            sendAjax(data);
        }, 2000);
    }
    updatePage();

    function sendAjax(data)
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/ajax.php",
            data: data,
            cache: false,
            async: true,
            success: function (msg) {
                if(stack.roomsHash !== msg.roomsHash){
                    stack.roomsHash = msg.roomsHash;
                    roomsContainer.html(msg.rooms);
                }
                
                if(msg.status === "start"){
                        window.document.location.reload();
                }
                
                if(msg.addPlayers){
                    if(stack.addPlayersHash !== msg.addPlayersHash){
                        stack.addPlayersHash = msg.addPlayersHash;
                        playersContainer.html(msg.addPlayers);
                    }
                    
                    if(msg.readyTogo === "ok" && stack.roomTitle !== "ok"){
                        stack.roomTitle = "ok" 
                        roomTitle.html(
                            "Вы создали комнату!"
                          +" <a href='" + DOMEN + "/" + TICTACTOE + "/dropRoom'>Удалить комнату</a>"
                          +" | <a href='" + DOMEN + "/" + TICTACTOE + "/startGame'>Начать</a>");
                    }else if(msg.readyTogo === "none" && stack.roomTitle !== "none"){
                        stack.roomTitle = "none" 
                        roomTitle.html(
                            "Вы создали комнату!"
                           +" <a href='" + DOMEN + "/" + TICTACTOE + "/dropRoom'>Удалить комнату</a>");
                    }
                    return true;
                }
                if(stack.addPlayersHash){
                    stack.addPlayersHash = '';
                    playersContainer.parent().remove();
                    return true;
                }
                    
            }
        });
    }

});

