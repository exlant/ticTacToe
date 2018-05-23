<?php
    use core\startCore;
    use Project\Exlant\view\view;
    $rooms = startCore::$objects['ticTacToe']->getSingleRooms();
?>
<table class="viewRoom">
    <thead>
        <tr>
            <th>№</th>
            <th>Создатель</th>
            <th>Длина</th>
            <th>Фигур</th>
            <th>Очки</th>
            <th>Блиц</th>
            <th>Игроков</th>
            <th>Зашло</th>
            <th>Заявка</th>
        </tr>
    </thead>
    <tbody>
    <?php
        echo view::viewRooms($rooms);
    ?>
    </tbody>
</table>
<?php
if($userBusyInfo['action'] !== 'creater' and $userBusyInfo['action'] !== 'created'){ 
?>
    <div class="addRoom">
        <a href="<?= DOMEN.'/'.TICTACTOE ?>/addRoom">
            <input type="button" value="Создать комнату">
        </a>
    </div>
<?php
}