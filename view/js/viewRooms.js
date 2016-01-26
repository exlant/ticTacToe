$(function () {
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
        var dataUsers = {
            access: 1,
            object: "tictactoe",
            action: "updateAddUsers"
        };
        var dataRooms = {
            access: 1,
            object: "tictactoe",
            action: "updateRooms"
        };

        var playersContainer = $("div.roomAddPlayers div.playersContainer");
        var roomsContainer = $("table.viewRoom tbody");
        setInterval(function () {
            sendAjax(dataRooms, roomsContainer);
            if (playersContainer.length > 0) {
                sendAjax(dataUsers, playersContainer);
            }
        }, 5000);
        // userLogin - определе в файле index.php
        // userCreater - определен в файле view.class.php метод addPlayers
        if (typeof(userCreater) !== "undefined" && userLogin === userCreater) {
            var roomTitle = $("div.roomAddPlayers div.title");
            var numPlayed = parseInt($("div.roomAddPlayers div#numPlayers").html());
            setInterval(function () {
                var numReady = 0;
                $("div.roomAddPlayers div.playersContainer div.player").each(function(){
                    if($(this).data("ready") === "ok"){
                        numReady++;
                    }
                });
                if (numReady === numPlayed) {
                    roomTitle.html("Вы создали комнату! <a href='" + DOMEN + "/" + TICTACTOE + "/dropRoom'>Удалить комнату</a> | <a href='" + DOMEN + "/" + TICTACTOE + "/startGame'>Начать</a>");
                } else {
                    roomTitle.html("Вы создали комнату! <a href='" + DOMEN + "/" + TICTACTOE + "/dropRoom'>Удалить комнату</a>");
                }
            }, 1000);
        }

    }
    updatePage();

    function sendAjax(data, element)
    {
        $.ajax({
            type: "POST",
            url: "http://tictactoe.develop/ajax.php",
            data: data,
            async: true,
            success: function (msg) {
                if (msg === "notFound") {
                    element.parent().remove();
                    return true;
                }
                if(msg === "starting"){
                    window.document.location.reload();
                    return true;
                }
                element.html(msg);
            }
        });
    }

});

