<?php
require_once 'core'.DIRECTORY_SEPARATOR.'autoload.class.php';   // включаем автоподгузку классов                
use core\startCore;                                              // загружаем ядро
use Project\Exlant\view\view;

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?= startCore::$objects['pageParams']['title'] ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="<?= startCore::$objects['pageParams']['description'] ?>">
    <meta name="Keywords" content="<?= startCore::$objects['pageParams']['keywords'] ?>">
    <?=  startCore::getCSS() ?>
    <?=  startCore::getJS() ?>  
</head>
    <body>
<?php
    $errorHandler->setGlobalVariables();
    //echo $errorHandler->getGlobalVariables();
      
    if(startCore::$authorization->userID 
            and startCore::$authorization->getAccessLvl() !== 'manager'){
    $user = startCore::$authorization->userData;
    $userStat = (isset($user['statistics']['entire'])) ? $user['statistics']['entire'] : null;
?>
        <script type="text/javascript">
            var userLogin = "<?=$user['nick'] ?>";
        </script>
    <div class="mainWrapper">
    <div class="leftContainer">
        <div class="autorizationPanel">
            <div class="title">Добро пожаловать</div> 
            <div class="login"><?= view::reduceLength($user['nick'], 15) ?></div>
            <div class="statistics">Побед: <?= (isset($userStat['win'])) ? $userStat['win'] : 0 ?></div>
            <div class="statistics">Поражений: <?= (isset($userStat['lose'])) ? $userStat['lose'] : 0 ?></div>
            <div class="statistics">Ничьих: <?= (isset($userStat['draw'])) ? $userStat['draw'] : 0 ?></div>
            <div class="out">
                <a href="<?=DOMEN ?>/out">Выйти</a>
            </div>
        </div>
        <div class="menu">
            <div class="title">Меню</div>
            <div class="menuItem">
                <a href="<?= DOMEN.'/'.TICTACTOE ?>">Крестики-нолики</a>
            </div>
            <div class="menuItem">
                <a href="<?= DOMEN.'/' ?>sendMessage">Отправить сообщение</a>
            </div>
        </div>
        <div class="usersOnline">
            <div class="title">Пользователи он-лайн</div>
            <div class="container">
            <?= view::usersOnline(startCore::$authorization->getUsersOnline()) ?>
            </div>
        </div>
    </div><div class="centerContainer">
        <?php
            if(startCore::$controller->getRoute() === 'tictactoe'){
                require_once VIEW.'tictactoe'.$slash.'index.php';
            }
        ?>
    </div>
    <?php
//    if(isset(startCore::$objects['ticTacToe'])){
//    $userBusyInfo = (startCore::$objects['ticTacToe']->getUserBusyInfo() !== null)
//           ? $errorHandler->viewStruct(startCore::$objects['ticTacToe']->getUserBusyInfo())
//           : 'user busy info danied';
//    $getSomeThing = (startCore::$objects['ticTacToe']->getSomeThing('rooms') !== null)
//            ? $errorHandler->viewStruct(startCore::$objects['ticTacToe']->getSomeThing('rooms'))
//            : 'Rooms not defined';
//    }else{
//        $userBusyInfo = 'tictactoe not exist';
//        $getSomeThing = $userBusyInfo;
//    }
//    $userData = (isset(startCore::$authorization->userData))
//            ? $errorHandler->viewStruct(startCore::$authorization->userData)
//            : 'User data not defined';
//    
//    echo '<table border="1" >'
//        . '<tr><th>UserBusyInfo</th><th>getSomeThing(rooms)</th><th>userData</th></tr>'
//        . '<tr style="vertical-align:top">';
//        echo '<td>'.$userBusyInfo.'</td>';
//        echo '<td>'.$getSomeThing.'</td>';
//        echo '<td>'.$userData.'</td>';
//        echo '</tr>'
//        . '</table>';
    ?>
    </div> <!--main wrapper-->
<?php
    }elseif(startCore::$authorization->getAccessLvl() === 'manager'){
        require_once VIEW.'manager.php';
        
    }else{
        require_once VIEW.'registration.php';
    }
?>
    </body>
</html>