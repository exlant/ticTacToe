<?php
    use core\startCore;
    $login = startCore::$objects['viewUsers']->getLogin();
    $selfLogin = startCore::$objects['viewUsers']->getSelfLogin();
    $title = startCore::$objects['viewUsers']->getTitle();
    $userData = startCore::$objects['viewUsers']->getUserData();
?>

<div class="centerTitle"><?=$title?></div>
<div class="viewUsers">
    <?php
    if(!$login){
        $users = startCore::$objects['viewUsers']->getAllUsers();
        for($i = 0, $num = 1; $i < count($users); $i++, $num++){
            echo '<div class="user">'
            . '<a href="'.DOMEN.'/users/'.$users[$i].'" >'.$num.'. '.$users[$i].'</a>'
            . '</div>';
        }
        
    }elseif(!$userData){
        echo 'Пользователь '.$login.' не найден!';
    }elseif($login === $selfLogin){
        
    ?>  
    <script type="text/javascript">
        var selfLogin = "<?=$selfLogin?>";
        var mail = "<?=$userData['mail']?>";
    </script>
        <div class="editProfile">
            <div class="login container">
                <div class="title">
                    Ваш логин: 
                </div>
                <div class="input">
                    <input type="text" name="login" value="<?=$selfLogin?>">
                </div>
                <div class="helper"></div>
            </div>
            <div class="mailView container">
                <div class="title">
                    Отображать почту в профиле: 
                </div>
                <div class="input">
                    <select>
                        <option value="1" <?=$userData['mailViewSelect']['yes']?>>Да</option>
                        <option value="0" <?=$userData['mailViewSelect']['no']?>>Нет</option>
                    </select>
                </div>
                <div class="helper"></div>
            </div>
            <div class="mail container">
                <div class="title">
                    Ваша почта: 
                </div>
                <div class="input">
                    <input type="text" name="mail" value="<?=$userData['mail']?>">
                </div>
                <div class="helper"></div>
            </div>
            <div class="pass container">
                <div class="title">
                    Изменить пароль: 
                </div>
                <div class="input">
                    <input type="password" name="pass" placeholder="Введите новый пароль" value="">
                </div>
                <div class="helper"></div>
            </div>
            <div class="passConfirm container">
                <div class="title">
                    Повторите пароль: 
                </div>
                <div class="input">
                    <input type="password" name="passConfirm" placeholder="Повторите пароль" value="">
                </div>
                <div class="helper"></div>
            </div>
            <div class="container">
                <div class="title">
                    Дата регистрации: 
                </div>
                <div class="input">
                    <?=$userData['date']?>
                </div>
            </div>
            <div class="container">
                <div class="title">
                    Побед: 
                </div>
                <div class="input">
                    <?=$userData['statistics']['entire']['win']?>
                </div>
            </div>
            <div class="container">
                <div class="title">
                    Поражений: 
                </div>
                <div class="input">
                    <?=$userData['statistics']['entire']['lose']?>
                </div>
            </div>
            <div class="container">
                <div class="title">
                    Ничьих: 
                </div>
                <div class="input">
                    <?=$userData['statistics']['entire']['draw']?>
                </div>
            </div>
        </div>
    <?php
    }else{
    ?>
    <div class="editProfile">
        <div class="container">
            <div class="title">
                Логин: 
            </div>
            <div class="input">
                <?=$login?>
            </div>
        </div>
        <div class="container">
            <div class="title">
                Почта: 
            </div>
            <div class="input">
                <?=(isset($userData['mailView']) and $userData['mailView'] === 1) 
                        ? $userData['mail'] 
                        : 'скрыто' 
                ?>
            </div>
        </div>
        <div class="container">
            <div class="title">
                Дата регистрации: 
            </div>
            <div class="input">
                <?=$userData['date']?>
            </div>
        </div>
        <div class="container">
            <div class="title">
                Побед: 
            </div>
            <div class="input">
                <?=$userData['statistics']['entire']['win']?>
            </div>
        </div>
        <div class="container">
            <div class="title">
                Поражений: 
            </div>
            <div class="input">
                <?=$userData['statistics']['entire']['lose']?>
            </div>
        </div>
        <div class="container">
            <div class="title">
                Ничьих: 
            </div>
            <div class="input">
                <?=$userData['statistics']['entire']['draw']?>
            </div>
        </div> 
    </div>
    <?php
    }
    ?>
    
</div>

