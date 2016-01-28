<?php
use Project\Exlant\view\view;
///////////////////  если комната создается начало
if($userBusyInfo['roomStatus'] == 'creating'){
?>
    <div class = "roomSettings">
        <div class="title">Вы создаете комнату для игры в крестики-нолики</div>
        <form action="<?= DOMEN.'/'.TICTACTOE ?>" method="post">
            <div class="creatingForm">
            <!--
            <div class="formFild">
                <div class="inputName">
                    Тип игры: 
                </div>
                <div class="inputForm">
                    <select name="gameType">
                        <option value="2d">2D</option>
                        <option value="3d">3D</option>
                    </select>
                </div>
                
                <div class="help">
                    3D - игра в кубе 
                </div>    
            </div>
            -->
            <div class="formFild">
                <div class="inputName">
                    Длина поля: 
                </div>
                <div class="inputForm">
                    <input type="number" min="3" max="20" value="3" name="fildLength">
                </div>
                <div class="help">
                    от 3 до 20
                </div> 
            </div>
            <div class="formFild">
                <div class="inputName">
                    Фигур в ряд: 
                </div>
                <div class="inputForm">
                    <input type="number" min="3" max="6" value="3" name="figureInArow">
                </div>
                <div class="help">
                    от 3 до 6
                </div> 
            </div>
            <div class="formFild">
                <div class="inputName">
                    На очки: 
                </div>
                <div class="inputForm">
                    <input id="checkPoint" type="checkbox" value="yes" name="points">
                </div>
                <div class="help">
                    1 очко за ряд фигур 
                </div> 
            </div>
            <div id="points" class="formFild">
                <div class="inputName">
                    Очи: 
                </div>
                <div class="inputForm">
                    <input type="number" min="3" max="20" value="3" name="pointsNum">
                </div>
                <div class="help">
                    от 3 до 20
                </div> 
            </div>
            <div class="formFild">
                <div class="inputName">
                    Ваша фигура: 
                </div>
                <div class="inputForm">
                    <select name="figure">
                        <option value="cross">Крестик</option>
                        <option value="zero">Нолик</option>
                        <option value="triangle">Треугольник</option>
                        <option value="foursquare">Квадратик</option>
                    </select>
                </div>
            </div>
            <div class="formFild">
                <div class="inputName">
                    Блиц: 
                </div>
                <div class="inputForm">
                    <input id="blitz" type="checkbox" value="yes" name="blitz">
                </div>
            </div>
            <div class="formFild">
                <div class="inputName">
                    Время на ход: 
                </div>
                <div class="inputForm">
                    <input type="number" step="5" min="30" max="300" value="30" name="roundTime">
                </div>
                <div class="help">
                    от 30 до 300 секунд
                </div> 
            </div>
            <div class="formFild">
                <div class="inputName">
                    Число игроков: 
                </div>
                <div class="inputForm">
                    <input type="number" min="2" max="4" value="2" name="players">
                </div>
                <div class="help">
                    от 2 до 4
                </div> 
            </div>
            </div>
            <div class="submit">
                <input type="submit" value="Создать"
                ><a href="<?=DOMEN.'/'.TICTACTOE.'/dropRoom' ?>"
                   ><input type="button" value="Отмена">
                </a>
                <input type="hidden" name="type" value="createRoom">
            </div>
        </form>
    </div>
<?php  
}
// комната создается конец
// добавление игроко начало
if($userBusyInfo['roomStatus'] === 'created'){
?>
<div class="roomAddPlayers">
    <div class="title">
    <?php
    //титолка
    
    if($userBusyInfo['action'] === 'creater'){
        $data['title'] = 'Вы создали комнату! <a href="'.DOMEN.'/'.TICTACTOE.'/dropRoom">Удалить комнату</a>';
        if($userBusyInfo['readyToGo'] === 'ok'){
            $data['title'] .= ' | <a href="'.DOMEN.'/'.TICTACTOE.'/startGame">Начать</a>'; 
        }
    }
    if($userBusyInfo['action'] === 'joiner'){
        $data['title'] = 'Вы присоединились к комнате игрока '.$userBusyInfo['creater'];
    }
    echo $data['title'];
    ?>
    </div>
    <div class="roomParam">
    <?php
        //настройки комнаты
        $data['settings'] = '';
        foreach($userBusyInfo['html']['roomSettings'] as $key => $value){
            $numPlayerId = ($key === 'numPlayers') ? 'id="numPlayers"' : '';
            if($userBusyInfo[$key]){
                $data['settings'] .= '<div>'
                                . '<div class="settingName">'.$value.':</div>'
                                . '<div '.$numPlayerId.' class="settingValue">'.$userBusyInfo[$key].'</div>'
                           . '</div>';
            }
        }
        echo $data['settings'];
    ?>
    </div><div class="playersContainer">
    <?php
    echo view::addPlayers($userBusyInfo['players'], 
                     $userBusyInfo['freeFigure'], 
                     $userData['nick'], 
                     $userBusyInfo['action']);
    ?>
    </div>
</div>    
<?php 
}