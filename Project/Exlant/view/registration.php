<?php
use core\startCore;
$hidden = (isset($admin) and $admin) ? "<input type='hidden' name='manager' value='manager'>" : "";
if(filter_input(INPUT_GET, 'route') === 'registration'){ ?>
    <div class="registration">
        <form name="registration" action="" method="post">
            <input type="hidden" name="type" value="registration">
            <div class="title">Регистрация</div>
                <div class="inputForm">
                    <input type="text" placeholder="Введите логин" name="nick" value="<?= filter_input(INPUT_POST,'nick') ?>">
                </div>
                <div id="nick" class="helper"></div>
                <div class="inputForm">
                    <input type="password" placeholder="Введите пароль" name="password" value="<?= filter_input(INPUT_POST,'password') ?>">
                </div>
                <div id="password" class="helper"></div>
                <div class="inputForm">
                    <input type="password" placeholder="Повторите пароль" name="passTest" value="<?= filter_input(INPUT_POST,'passTest') ?>">
                </div>
                <div id="passTest" class="helper"></div>
                <div class="inputForm">
                    <input type="text" placeholder="Введите email" name="mail" value="<?= filter_input(INPUT_POST,'mail') ?>">
                </div>
                <div id="mail" class="helper"></div>
                <div class="inputForm">
                    <div class="captcha">
                        <img src="images/captcha.php?width=300">
                    </div>
                    <div class="input">
                        <input type="text" placeholder="Введите текст с картинки" name="captcha">
                    </div>
                </div>
                <div id="captcha" class="helper"></div>
                <input name="send" type="submit" value="Зарегистрироваться">
                <div class="errorHelper"><?= ($errorHandler->getUserError()) ? $errorHandler->getUserError() : ''; ?></div>
                <div class="quickMessage"><?php  
                    if(startCore::$controller->quickMessage){
                        echo startCore::$controller->quickMessage;
                        startCore::$controller->quickMessage = null;
                    }
                ?></div>
        </form>
    </div>
<?php }elseif(filter_input(INPUT_GET, 'route') === 'guest'){ ?>
    <div class="registration">
        <form name="authorization" action="" method="post">
            <input type="hidden" name="type" value="guest">
            <div class="title">Введите текст c картинки</div>
            
            <div class="inputForm">
                <div class="captcha">
                    <img src="images/captcha.php?width=300">
                </div>
                <div class="input">
                    <input type="text" placeholder="Введите текст с картинки" name="captcha">
                </div>
            </div>
            <div id="captcha" class="helper"></div>
            <input name="send" type="submit" value="Играть как гость">
            <div class="quickMessage"><?php  
                if(startCore::$controller->quickMessage){
                    echo startCore::$controller->quickMessage;
                    startCore::$controller->quickMessage = null;
                }
          ?></div>
        </form>
    </div>
<?php }else{?>

    <div class="authorization">
        <form name="authorization" action="" method="post">
            <input type="hidden" name="type" value="authorization">
            <?=$hidden?>
            <div class="title">Вход на сайт</div>
            <input type="text" name="nick" placeholder="Логин">
            <input type="password" name="password" placeholder="Пароль">
            <div class="errorHelper"><?= ($errorHandler->getUserError()) ? $errorHandler->getUserError() : ''; ?></div>
            <?php if(isset($_SESSION['triesAuth']) and $_SESSION['triesAuth'] > 5){ ?>
            <div class="inputForm">
                <div class="captcha">
                    <img src="images/captcha.php?width=300">
                </div>
                <div class="input">
                    <input type="text" placeholder="Введите текст с картинки" name="captcha">
                </div>
            </div>
            <div id="captcha" class="helper"></div>
            <?php }?>
            <input name="send" type="submit" value="Войти">
            <a href="<?=DOMEN ?>/registration">
                <div class="inputButton"><input type="button" value="Зарегистрироваться"></div>
            </a>
            <a href="<?=DOMEN ?>/guest">
                <div class="inputButton"><input type="button" value="Играть как гость"></div>
            </a>
        </form>
    </div>
<?php   
}