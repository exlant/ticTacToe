<?php
namespace Project\Exlant\ticTacToe\controller;
use core\db\mongoDB;
class ticTacToeFoursquare
{
    const minFoursquareLength = 3;  // минимальная длина стороны поля 
    const maxFoursquareLength = 20; // максимальная длина стороны поля

    private $foursquare = array();     // поле                                (array)
    private $winner = null;            // имя победителя                      (string)
    private $winnerRow = array();      // выйграшный ряд                     (array)
    private $points = 0;                // очки                               (integer)
    private $warnings = array();        // массив с возможными угрозами        (array)  
    private $movingPlayer = null;      // ходящий игрок                       (string)
    private $movingFigure = null;      // фигура игрока                       (string)
    private $move = null;              // сделанный ход                       (array)
    private $rowLength = 0;           // количество фигур в ряд для победы    (integer)
    private $_lines = array(
            'diagonalA',
            'diagonalB',
            'vertical',
            'gorizontal'
    );
    
    private $_direction = array(
        'minus',
        'plus'
    );
    
    private $_directionFiller = array(
        'obstacle' => 0,       // припятствие, стенка или чужая фигура
        'empty'    => 0,       // количество пустых клеток в направлении
        'movies'   => array(), // координаты хода
        'emptyWithoutSkip' => array(),
    );
    private $_lineFiller = array(
            'border'      => 0,          // количество границ, 
            'inArow'      => array(),    // фигуры без пустых клеток
            'warnings'    => array(),    // запись координат с угрозами
            'emptyBorder' => array(),    // пустые клетки на границе
            'emptyInArow' => array(),    // пустые клетки в ряду
    );
    private $_stack = array();   

    public function __construct($movingPlayer, $foursquare, $move, $rowLength, $points) 
    {
        $this->setFoursquare($foursquare)   // устанавливает поле
             ->setMove($move)               // устанавливает сделаный ход
             ->setRowLength($rowLength)     // устанавливает длину ряда
             ->setMovingPlayer($movingPlayer)// логин походившего игрока
             ->setMovingFigure()            // фигура игрока, который ходит
             ->createStack()                // заполняем стэк
             ->executing()                  // проверяет ряды во все напровлени от поставленного хода
             ->setWarnings();               // выставляет угрозы        
        if($points !== 'yes'){
            $this->setWinner();
        }else{
            $this->setPoints();
        }
    }

    private function checkString($y, $x, $stringName, $direction)
    {
        $cell = $this->getForsquareCell($y, $x);
        // найдена граница поля, прекращается проверка в данном напровлении
        if($cell === 'notExist'){
            $this->_stack[$stringName][$direction]['obstacle'] = 1;
            // если предыдущая клетка не пуста, то прибавляем границе +1
            if($this->getLastMove($stringName, $direction, 'value', 1) !== 'empty'){
                $this->_stack[$stringName]['border']++;
            }        
            return true;
        }
        // запись текущей координаты 
        if(empty($this->_stack[$stringName][$direction]['movies'])){
            array_push($this->_stack[$stringName][$direction]['movies'], $this->getMove());
        }
        array_push($this->_stack[$stringName][$direction]['movies'], array('y' => $y, 'x' => $x));
        // клетка пуста, если пуста два раза подряд, проверка прекращается
        if($cell === 'empty'){
            if($this->getLastMove($stringName, $direction) === 'empty'){
                $this->_stack[$stringName][$direction]['obstacle'] = 1;
                return true;
            }
            $this->_stack[$stringName][$direction]['empty']++;
            array_push($this->_stack[$stringName]['emptyBorder'], array('y' => $y, 'x' => $x));
            if(count($this->_stack[$stringName][$direction]['emptyWithoutSkip']) > 0){
                $this->_stack[$stringName][$direction]['emptyWithoutSkip'] = array();
            }else{
                array_push($this->_stack[$stringName][$direction]['emptyWithoutSkip'], array('y' => $y, 'x' => $x));
            }
            
            return true;
        }
        // клетка совпала с ходящим
        if($cell === $this->getMovingFigure()){
            // если в ряд уже больше чем надо
            if(count($this->_stack[$stringName]['warnings']) > $this->getRowLength()){
                $this->_stack[$stringName][$direction]['obstacle'] = 1;
            }
            // фигуры в ряд без пустых клеток
            if($this->_stack[$stringName][$direction]['empty'] === 0){
                // условный выйграшный ряд
                if(empty($this->_stack[$stringName]['inArow'])){
                    array_push($this->_stack[$stringName]['inArow'],$this->getMove());
                }
                array_push($this->_stack[$stringName]['inArow'], array('y' => $y, 'x' => $x));
            }            
            // запись рядов предствляющих угрозы
            if(empty($this->_stack[$stringName]['warnings'])){
                array_push($this->_stack[$stringName]['warnings'], $this->getMove());
            }
            array_push($this->_stack[$stringName]['warnings'], array('y' => $y, 'x' => $x));
            // пустые клетки в ряду
            if($this->getLastMove($stringName, $direction) === 'empty'){
                $keyEmptyBorder = array_search($this->getLastMove($stringName, $direction, 'coord'), $this->_stack[$stringName]['emptyBorder']);
                unset($this->_stack[$stringName]['emptyBorder'][$keyEmptyBorder]);
                array_push($this->_stack[$stringName]['emptyInArow'], $this->getLastMove($stringName, $direction, 'coord'));
            }
            return true;
        }
        // в клетке стоит другая фигура
        $this->_stack[$stringName][$direction]['obstacle'] = 1;
        // если предыдущая клетка не пуста, то прибавляем границе +1
        if($this->getLastMove($stringName, $direction) !== 'empty'){
            $this->_stack[$stringName]['border']++;
        } 
        return true;   
    }
    
    private function executing()
    {
        for($num = 1; $num <= $this->getRowLength(); $num++){
            if($this->_stack['diagonalA']['plus']['obstacle'] === 0){
                $this->checkString($this->getMove()['y'] + $num, $this->getMove()['x'] + $num,
                        'diagonalA', 'plus');
            }
            if($this->_stack['diagonalA']['minus']['obstacle'] === 0){
                $this->checkString($this->getMove()['y'] - $num, $this->getMove()['x'] - $num,
                        'diagonalA', 'minus');
            }
            if($this->_stack['diagonalB']['plus']['obstacle'] === 0){
                $this->checkString($this->getMove()['y'] + $num, $this->getMove()['x'] - $num,
                        'diagonalB', 'plus');
            }
            if($this->_stack['diagonalB']['minus']['obstacle'] === 0){
                $this->checkString($this->getMove()['y'] - $num, $this->getMove()['x'] + $num,
                        'diagonalB', 'minus');
            }
            if($this->_stack['vertical']['plus']['obstacle'] === 0){
                $this->checkString($this->getMove()['y'] + $num, $this->getMove()['x'],
                        'vertical', 'plus');
            }
            if($this->_stack['vertical']['minus']['obstacle'] === 0){
                $this->checkString($this->getMove()['y'] - $num, $this->getMove()['x'],
                        'vertical', 'minus');
            }
            if($this->_stack['gorizontal']['plus']['obstacle']=== 0){
                $this->checkString($this->getMove()['y'], $this->getMove()['x'] + $num,
                        'gorizontal', 'plus');
            }                   
            if($this->_stack['gorizontal']['minus']['obstacle'] === 0){
                $this->checkString($this->getMove()['y'], $this->getMove()['x'] - $num,
                        'gorizontal', 'minus');
            }
        }
        return $this;
    }//end executing
    
    private function setWarnings()
    {
        if($this->getRowLength() > 3){
        foreach($this->_stack as $key => $val){
            $warnings = array();
            if($this->getRowLength() === count($val['warnings'])
                    and count($val['emptyInArow']) === 2){
                $warnings['availableCell'] = $val['emptyInArow'];
                $warnings['movies'] = $val['warnings'];
                // нужно занять все доступные клетки, что бы снять угрозу
                $warnings['add'] = 'all';
                $this->warnings[] = $warnings;
                continue;
            }
            if($this->getRowLength() - 1 === count($val['inArow'])
                    and $val['border'] <= 1
                    and count($val['emptyInArow']) < 2){
                // array_values() для mongoDb, которое не считает массивом елемент,
                // в котором индексация не с нуля и не попорядку
                $warnings['availableCell'] = array_values(array_merge($val['minus']['emptyWithoutSkip'],$val['plus']['emptyWithoutSkip']));
                $warnings['movies'] = $val['inArow'];
                // нужно занять все доступные клетки, что бы снять угрозу
                $warnings['add'] = 'all';
                $this->warnings[] = $warnings;
                continue;
            }
            
            if($this->getRowLength() - 1 === count($val['warnings']) 
                    and count($val['emptyInArow']) < 2
                    and $val['border'] <= 1
                    and $this->getRowLength() > count($val['inArow'])){
                $warnings['availableCell'] = $val['emptyInArow'];
                $warnings['movies'] = $val['warnings'];
                $this->warnings[] = $warnings;
                
            }
            if($this->getRowLength() > 4){
                if($this->getRowLength() - 2 === count($val['inArow'])
                        and $val['border'] === 0){
                    $warnings['availableCell'] = $val['emptyBorder'];
                    $warnings['movies'] = $val['inArow'];
                    $this->warnings[] = $warnings;
                    continue;
                }
                if($this->getRowLength() - 2 === count($val['warnings']) 
                        and $val['border'] === 0 
                        and count($val['emptyInArow']) < 2
                        and $this->getRowLength() > 3){
                    $warnings['availableCell'] = array_merge($val['emptyBorder'], $val['emptyInArow']);
                    $warnings['movies'] = $val['warnings'];
                    $this->warnings[] = $warnings;
                    continue;
                }
            }
        }
        }
    }
    
    public function getWarnings()
    {
        return $this->warnings;
    }
    
    private function setWinner() //сетер для победителя
    {
        foreach ($this->_stack as $val){
            if(count($val['inArow']) === $this->getRowLength()){
                $this->winner = $this->getMovingPlayer();
                $this->winnerRow[] = $val['inArow'];
            }
        }
        return $this;
    }

    public function getWinner() //гетер для победителя
    {
        return $this->winner;
    }

    public function getWinnerRow() // гетер для выйграшной строчки
    {
        return $this->winnerRow;
    }
    
    private function setPoints()
    {
        foreach($this->_stack as $val){
            if(count($val['inArow']) === $this->getRowLength()){
                $this->points++;
                $this->winnerRow[] = $val['inArow'];
            }
        }
        
        return $this;
    }
    
    public function getPoints()
    {
        return $this->points;
    }

    private function setFoursquare($foursquare)
    {
        $this->foursquare = $foursquare;
        return $this;
    }

    public function getForsquareCell($y, $x)
    {
        if(isset($this->foursquare[$y][$x])){
            return $this->foursquare[$y][$x];
        }
        return 'notExist';
    }

    private function setMove($move)
    {
        $intMove = array();
        foreach($move as $kay => $val){
            $intMove[$kay] = (int)$val; 
        }
        $this->move = $intMove;
        return $this;
    }

    private function getMove()
    {
        return $this->move;
    }

    private function setRowLength($rowLength)
    {
        $this->rowLength = (int)$rowLength;
        return $this;
    }

    private function getRowLength()
    {
        return $this->rowLength;
    }
    
    private function setMovingFigure()
    {
        $this->movingFigure = $this->getForsquareCell($this->getMove()['y'], $this->getMove()['x']);
        return $this;
    }
    
    private function getMovingFigure()
    {
        return $this->movingFigure;
    }
    
    private function setMovingPlayer($movingPlayer)
    {
        $this->movingPlayer = $movingPlayer;
        return $this;
    }
    
    public function getMovingPlayer()
    {
        return $this->movingPlayer;
    }
    
    private function createStack()
    {
        foreach($this->_direction as $line){
            $direction[$line] = $this->_directionFiller;   
        }
        $filler = array_merge($direction, $this->_lineFiller);
        foreach($this->_lines as $line){
            $this->_stack[$line] = $filler;
            
        }
        return $this;
    }
    
    // $number если не было еще записи в массив ходов, то должна быть 1, если была то 
    private function getLastMove($stringName, $direction, $param = 'value', $number = 2)
    {
        if(empty($this->_stack[$stringName][$direction]['movies'])){
            return false;
        }
        $num = count($this->_stack[$stringName][$direction]['movies']);
        $prev = $num - $number;
        $cell = $this->_stack[$stringName][$direction]['movies'][$prev];
        if($param === 'value'){
            return $this->getForsquareCell($cell['y'], $cell['x']);
        }elseif($param === 'coord'){
            return $cell;
        }
    }
}
