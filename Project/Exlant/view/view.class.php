<?php

namespace Project\Exlant\view;



class view
{
//    static private $_abc = array(                           // алфавит
//        'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т'
//    ); 
    static private $_imgUrl = '/images/tictactoe/';          // путь к папке с картинками 
    static private $_abc = array(                           // алфавит
        '0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19'
    );   
    
    
    private static $_figures = array(
        'cross' => 'Крестик',
        'zero' => 'Нолик',
        'triangle' => 'Треугольник',
        'foursquare' => 'Квадратик',
        'none' => 'Не выбранно'
    );
    // список игроков в определенной комнате
    static public function addPlayers($playersInput, $freeFigures, $currentUserLogin, $action)
    {
        //список игроков
        $playersOutput = '';
        if($action === 'creater'){
            $playersOutput .= '<script type="text/javascript">'
                            .'var userCreater = "'.$currentUserLogin.'";'
                            .'var tictactoeAddPlayer = 1;'
                            .'</script>';
        }
        foreach($playersInput as $value){ //массив с игроками, которые находятся в комнате
            $figure = self::$_figures[$value['figure']];
            if($currentUserLogin === $value['name']){       // если пользователь совпал с игроком со списка 
                if($value['figure'] === 'none'){ // если у него не выбрана фигура, создаем форму для выбора фигуры
                    $figure = '<form action="" method="post">'
                    . '<select name="figure">';
                    foreach($freeFigures as $key => $val){ // итерируем массив с оставшимися фигурами
                        $figure .= '<option value='.$key.'>'.$val.'</option>';
                    }
                    $figure .= '</select>'
                    . '<div class="setFigure">'
                            . '<input type="submit" value="Выбрать">'
                            . '<input type="hidden" name="type" value="setFigure">'
                    . '</div>'
                    . '</form>';
                }
                if($action !== 'creater'){
                    $figure .= '<div><a href="'.DOMEN.'/'.TICTACTOE.'/exitRoom">Выйти</a></div>';
                }
            }
            //создание переменной с фигурой конец
            $ready = ($value['figure'] !== 'none') ? 'ok' : 'none';
            $playersOutput .= '<div class="player" data-ready="'.$ready.'">'
                    . '<div class="name">'.self::reduceLength($value['name'], 11).'</div>'
                    . '<div class="figure">'.$figure.'</div>';
            if($action === 'creater' and $currentUserLogin !== $value['name']){ //если это создатель комнаты, то создаем ссылки для удаления игроков из данной комнаты
                $playersOutput .= '<div class="dropPlayerUrl">'
                        . '<a href="'.DOMEN.'/'.TICTACTOE.'/dropOpponent/'.$value['name'].'">Выкинуть</a>'
                        . '</div>';
            }

            $playersOutput .= '</div>';
        }
        return $playersOutput;
    }
    // список комнат и их параметров для игры в крестики-нолики
    static public function viewRooms($rooms)
    {
        $output = '';
        if(!$rooms){
            return '<tr class="notRoom"><td colspan="10">Нет созданных комнат</td></tr>';
        }
        
        $iterator = 1;
        foreach ($rooms as $room){
            $output .= '<tr>
                    <td>'.$iterator.'</td>
                    <td>'.self::reduceLength($room['creater'], 15).'</td>
                    <td>'.$room['type'].'</td>
                    <td>'.$room['sideLength'].'</td>
                    <td>'.$room['figureInArow'].'</td>
                    <td>'.$room['pointsText'].'</td>
                    <td>'.$room['blitzText'].'</td>
                    <td>'.$room['numPlayers'].'</td>
                    <td>'.$room['playersIn'].'</td>
                    <td id="enterRoom">'.$room['url'].'</td>
                </tr>';
            $iterator++;          
        }
        return $output;
    }
    // игроки онлайн
    static public function usersOnline($users)
    {
        $output = '';
        foreach($users as $value){
            $output .= '<div class="userItem">'
            . '<a href="'.DOMEN.'/users/'.$value['nick'].'">'.self::reduceLength($value['nick'], 15).'</a>'
            . '</div>';
        }
        return $output;
    }
    // обрезание длины строки, до нужного количества символов
    static public function reduceLength($str, $minLength = 10)
    {
        if(strlen($str) > $minLength){
           $str = '<span title="'.$str.'" class="minText" >'.  substr($str, 0, $minLength).'...</span>';
        }
        return $str;
    }
    // игроки и зрители комнаты для игры в крестики нолики
    static public function viewRoomsUsers($players, $viewers, $login)
    {
        $output = '<div class="wrapperUsers">';
        if($players) {
            $output .= '<div class="players">
                <div class="title">
                    Игроки
                </div>
                <ul>';
            foreach($players as $player){
                if($player['exit'] === 'no'){
                    $url = ($player['name'] === $login) ? ' | <a href="'.DOMEN.'/'.TICTACTOE.'/quitGame">Выйти</a>' : '';
                    $moving = ($player['move']) ? 'moving' : '';
                    
                    $output .= '<li class="player '.$moving.'">'
                        .self::reduceLength($player['name'], 11).' | '
                        . '<img class="fieldFigure" src="'.DOMEN.self::$_imgUrl.$player['figure'].'.gif">'
                        .' | '
                        .$player['timeOut']
                        .$url;
                }
            }
            $output .= '</ul>'
                    . '</div>';
        } if($viewers){
            $output .= '<div class="viewers">
                <div class="title">
                    Зрители
                </div>';

            foreach($viewers as $viewer){
                $url = ($viewer['name'] === $login) ? ' | <a href="'.DOMEN.'/'.TICTACTOE.'/quitGame">Выйти</a>' : '';
                $output .= '<div class="player">'
                        .$viewer['name']. ' | '
                        .$url
                    .'</div>';
            }
            $output .='</div>';
        }
        $output .= '</div>';
        return $output;
    }

    static private function setBacklight($coordinate, $lastMove, $warnings, $winnerSide)
    {
        if(array_search($coordinate, $winnerSide) !== false){
            return '_winner';
        }
        if(array_search($coordinate, $warnings) !== false){
            return '_warning';
        }
        if(array_search($coordinate, $lastMove) !== false){
            return '_move';
        }
        return '';
    }
    
    // поле 2d для игры в крестики-нолики 
    static public function field2d($login, $field, $lastMove, $movingPlayer, $figure, $warnings, $winnerSide)
    {
        $out = '<table class="type2d">';
        $countField = count($field);
        $out .= '<thead><td  class="coordination"></td>';
        for($a = 0; $a < $countField; $a++ ){
            $out .= '<td class="coordination">'.self::$_abc[$a].'</td>';
        }
        $out .= '</thead>';
        foreach($field as $sideY => $valueY){
            $out .= '<tr><td class="coordination">'.$sideY.'</td>';
            foreach($valueY as $sideX => $valueX){
                $out .= '<td>';
                if($valueX !== 'empty'){
                    $backLlight = self::setBacklight(array('y' => $sideY, 'x' => $sideX), $lastMove, $warnings, $winnerSide);
                    $out .= '<img class="fieldFigure" src="'.DOMEN.self::$_imgUrl.$figure[$valueX].$backLlight.'.gif">';
                }else{
                    if($movingPlayer === $login){
                        $out .= '<a href="'.DOMEN.'/'.TICTACTOE.'/playerMove/y-'.$sideY.'_x-'.$sideX.'">'
                            . '<img class="fieldEmtyMove" src="'.DOMEN.self::$_imgUrl.'emptiness.png">'
                            . '</a>';                                                   
                    }else{
                        $out .= '<img class="fieldEmty" src="'.DOMEN.self::$_imgUrl.'emptiness.png">';
                    }
                }
                $out .= '</td>';
            }
            $out .= '</tr>';
        }
        $out .= '</table>';
        
        return $out;
    }
}