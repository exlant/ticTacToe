<div class="centerTitle">Крестики-нолики</div>
<?php
    use core\startCore;
    $userBusyInfo = startCore::$objects['ticTacToe']->getUserBusyInfo();
    $userData = startCore::$authorization->userData;
    
    if($userBusyInfo['roomStatus'] !== 'start' and $userBusyInfo['roomStatus'] !== 'end'){
        require_once 'creatingRoom.php'; //создание комнаты
        require_once 'viewRoom.php'; // итерация комнат
    }else{
        require_once 'playGame.php';   // игра
    }
?>   
