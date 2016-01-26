$(function(){
   $("div.mainWrapper").moveToCenter(0,10000);
   
   function updateUsersOnline()
    {
        var data = {
            access: 1,
            object: "main",
            action: "updateUsersOnline"
        };
        
        var userOnline = $("div.usersOnline div.container");
        setInterval(function(){
            sendAjax(data, userOnline);
        },60000);
    }    
    updateUsersOnline();
    
    function sendAjax(data, element)
    {
        $.ajax({
            type: "POST",
            url: "http://tictactoe.develop/ajax.php",
            data: data,
            async: true,
            success: function(msg){
                element.html(msg);
            }
        });
    }
   
});