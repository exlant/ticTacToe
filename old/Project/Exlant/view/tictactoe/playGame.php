<?php
use core\startCore;
use Project\Exlant\view\view;
use Project\Exlant\ticTacToe\model\mainModel;

$login = startCore::$authorization->userData['nick'];               // логин данного пользователя          (string)
$players = startCore::$objects['playGame']->getPlayers();           // массив с игроками                   (array)
$viewers = startCore::$objects['playGame']->getViewers();           // массив со зрителями                 (array)
$movingPlayer = startCore::$objects['playGame']->getMovingPlayer(); // игрок, который сейчас ходит (login) (string)
$winner = startCore::$objects['playGame']->getWinner();             // логин победителя, если такой есть     (string)
$winnerSide = startCore::$objects['playGame']->getWinnerRow();
$roomParams = startCore::$objects['playGame']->getRoomParam();      // параметры комнаты                    (array)
$gameArray = startCore::$objects['playGame']->getGameArray();       // игровое поле                     (array)
$warnings = startCore::$objects['playGame']->getWarnings();
$lastMove = startCore::$objects['playGame']->getLastMove();
$newplayers = array_merge($players, $roomParams['freePlace']);
?>
<script type="text/javascript">
    var change = <?=$roomParams['change']?>;
</script>
<div class="wrapper">
    <div class="field">
        <div class="fieldButton">
            <div id="moveBack">Ход назад</div><!--
            --><div id="draw">Предложить ничью</div><!--
            --><div id="surrender">Сдаться</div><!--
            --><div id="outGame">Выйти</div>
        </div>
        <?= view::field2d($login, $gameArray, $lastMove, $movingPlayer, $warnings, $winnerSide)?>
    </div><!--
 --><div class="users">
        <div class="roomParameters">
            <div class="title">
                Параметры
            </div>
            <ul class="parameters">
                <li>Длина поля - <?= $roomParams['sideLength']?> 
                <li>Фигур в ряд - <?= $roomParams['figureInArow']?>
                <li>Блитс - <?= mainModel::convertBlitz($roomParams['blitz'])['text']?>
                <li>Время на ход - <?= $roomParams['roundTime']?>
                <li>Игра на очки - <?= mainModel::convertPoints($roomParams['points'], $roomParams['pointsNum'])['text']?>
                <li>Число игроков - <?= $roomParams['numPlayers']?>
                <li>Создатель - <?= $roomParams['creater']?>
                <li>Победитель - <span class="winner"><?= ($winner) ? $winner : '' ?></span>
            </ul>    
        </div>
        <?= view::viewRoomsUsers($newplayers, $viewers, $roomParams['points'], $login, $roomParams['status']) ?>
    </div><!-- 
 -->
    <?php
    if($roomParams['type'] === '3d'){
        echo '<div class="type3d">';
        foreach($gameArray as $sideZ => $valueZ){
            echo '<div class="gameSideZ">';
            foreach($valueZ as $sideY => $valueY){
                echo '<div class="gameSideY">';
                foreach($valueY as $sideX => $valueX){
                    $winnerCell = (in_array('z'.$sideZ.'_x'.$sideX.'_y'.$sideY, $winnerSide)) ? ' winnerCell' : '';
                    echo '<div class="gameSideX'.$winnerCell.'">';
                    if($valueX !== 'empty'){
                        echo $valueX;
                    }else{
                        if($movingPlayer === $login){
                            echo '<a href="'.DOMEN.'/'.TICTACTOE.'/playerMove/z-'.$sideZ.'_y-'.$sideY.'_x-'.$sideX.'">'
                                . 'click'
                                . '</a>';
                        }else{
                            echo '___';
                        }
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
    ?>
</div>