<?php
//session_destroy();

//die(phpinfo());
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../old/core' . DIRECTORY_SEPARATOR . 'autoload.class.php';   // включаем автоподгузку классов

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
    <div class="mainWrapper">
    <div class="leftContainer">
        <div class="autorizationPanel">
            <div class="title">Добро пожаловать</div>
            <div class="login"><?= view::reduceLength($user['nick'], 15) ?></div>
            <div class="statistics">Побед: <?= (isset($userStat['win'])) ? $userStat['win'] : 0 ?></div>
            <div class="statistics">Поражений: <?= (isset($userStat['lose'])) ? $userStat['lose'] : 0 ?></div>
            <div class="statistics">Ничьих: <?= (isset($userStat['draw'])) ? $userStat['draw'] : 0 ?></div>
            <div class="statistics">
                <a href="<?=DOMEN.'/users/'.$user['nick']?>">Редактирование профиля</a>
            </div>
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
                <a href="<?= DOMEN.'/'.SENDMESSAGE ?>">Отправить сообщение</a>
            </div>
            <div class="menuItem">
                <a href="<?= DOMEN.'/'.GUIDE ?>">Руководство</a>
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
            if(startCore::$controller->getRoute() === TICTACTOE){
                require_once '../old/' . VIEW.TICTACTOE.$slash.'index.php';
            }
            if(startCore::$controller->getRoute() === USERS){
                require_once '../old/' . VIEW.USERS.'.php';
            }
            if(startCore::$controller->getRoute() === SENDMESSAGE){
                require_once '../old/' . VIEW.SENDMESSAGE.'.php';
            }
            if(startCore::$controller->getRoute() === GUIDE){
                require_once '../old/' . VIEW.GUIDE.'.php';
            }
        ?>
    </div>
    </div> <!--main wrapper-->
<?php
    }elseif(startCore::$authorization->getAccessLvl() === 'manager'){
        require_once '../old/' . VIEW . 'manager.php';

    }else{
        require_once '../old/' . VIEW . 'registration.php';
    }
?>
    </body>
</html>