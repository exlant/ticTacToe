<?php
namespace Project\Exlant\ticTacToe\controller;

class ticTacToeCubeCheck
{
    const minCubeLength = 3;  // минимальная длины стороны куба 
    const maxCubeLength = 10; // максимальная длина стороны куба

    private $cubeLength = null;             //длина стороны проверяемого куба           (integer)
    private $cube = array();                   // куб                                      (array)
    private $winner = null; // имя победителя                                           (string)
    private $stack = array(0,0,0,0,0,0,0,0,0,0,0,0,0); //13, число разных направлений проверок           (array)
    private $side = array(0,0,0,0,0,0,0,0,0,0,0,0,0);  // координаты зачеркнутой стоки по номеру стека   (array) 
    private $winnerNumberStack = null; // номер стека, который выйграл           (int)
    private $countFullCell = 0;        // счетчик для количество уже занятых ячеек (integer)
    private $numAllCubeCell = 0;       // количество всех ячеек куба               (integer)
    
    
    public function __construct($cube,$cubeLength) 
    {
        $this->setCubeLength($cubeLength); //устанавливает длину стороны куба
        $this->setNumAllCubeCell();        // устанавливаем количество всех ячеек куба
        $this->setCube($cube);             // устанавливем массив с кубом
        $this->checkWinner();              // ищет побидителя
    }

    private function matchCell($stackNumber,$c) // проверяет соответствие ячеек $c - координаты (array)
    {
        if($this->getCubeCell($c['z1'], $c['y1'], $c['x1']) !== 'empty' 
                and $this->getCubeCell($c['z1'], $c['y1'], $c['x1']) === $this->getCubeCell($c['z2'], $c['y2'], $c['x2'])){  //если есть соответствие +1 в стэк
            $this->stack[$stackNumber]++;
            $this->setSide($c['z1'], $c['y1'], $c['x1'], $stackNumber);
            
            if($this->stack[$stackNumber] === $this->getCubeLength()){ //если стэк равен
                $this->setWinner($this->getCubeCell($c['z1'], $c['y1'], $c['x1']));
                $this->setWinnerNumberStack($stackNumber);
                $this->setSide($c['z2'], $c['y2'], $c['x2'], $stackNumber);
            }
        }
    }

    private function checkWinner()              // проверяет нет ли в кубе выйграшной строчки
    {
        for($z = 0; $z <= $this->getCubeLength(); $z++){
            for($y = 0, $yRev = $this->getCubeLength(), $this->zeroingStack(); $y <= $this->getCubeLength(); $y++, $yRev--){
                if($y !== $this->getCubeLength() and $yRev !== 0){ //исключаем вызов несуществующих ячеек массива
                    //проверка диагоналей начало
                    if($z === 0){ //проверка диагоналей куба, делается за один первый проход
                        $this->matchCell(3, array('z1' => $y, 'y1' => $y,    'x1' => $yRev, 'z2' => $y+1, 'y2' => $y+1,    'x2' => $yRev-1));
                        $this->matchCell(4, array('z1' => $y, 'y1' => $y,    'x1' => $y,    'z2' => $y+1, 'y2' => $y+1,    'x2' => $y+1));
                        $this->matchCell(5, array('z1' => $y, 'y1' => $yRev, 'x1' => $y,    'z2' => $y+1, 'y2' => $yRev-1, 'x2' => $y+1));
                        $this->matchCell(6, array('z1' => $y, 'y1' => $yRev, 'x1' => $yRev, 'z2' => $y+1, 'y2' => $yRev-1, 'x2' => $yRev-1));
                    }
                    // проверка диагоналей по оси Z
                    $this->matchCell(7, array('z1' => $z,     'y1' => $y,    'x1' => $y, 'z2' => $z,      'y2' => $y+1,    'x2' => $y+1));
                    $this->matchCell(8, array('z1' => $z,     'y1' => $yRev, 'x1' => $y, 'z2' => $z,      'y2' => $yRev-1, 'x2' => $y+1));
                    // проверка диагоналей по оси Y
                    $this->matchCell(9, array('z1' => $y,     'y1' => $z,    'x1' => $y, 'z2' => $y+1,    'y2' => $z,      'x2' => $y+1));
                    $this->matchCell(10, array('z1' => $yRev, 'y1' => $z,    'x1' => $y, 'z2' => $yRev-1, 'y2' => $z,      'x2' => $y+1));
                    //проверка диагоналей по оси X
                    $this->matchCell(11, array('z1' => $y,    'y1' => $y,    'x1' => $z, 'z2' => $y+1,    'y2' => $y+1,    'x2' => $z));
                    $this->matchCell(12, array('z1' => $yRev, 'y1' => $y,    'x1' => $z, 'z2' => $yRev-1, 'y2' => $y+1,    'x2' => $z));

                    if($this->getWinner()){
                        break 2;
                    }
                }
                // проверка диагоналей конец
                for($x = 0, $this->zeroingStack('min'), $this->checkDraw($z,$y,$this->getCubeLength()); $x < $this->getCubeLength(); $x++){
                    //проверка по оси X
                    $this->matchCell(0, array('z1' => $z, 'y1' => $y, 'x1' => $x, 'z2' => $z,   'y2' => $y,   'x2' => $x+1));
                    //проверка по оси Y
                    $this->matchCell(1, array('z1' => $z, 'y1' => $x, 'x1' => $y, 'z2' => $z,   'y2' => $x+1, 'x2' => $y));
                    //проверка по оси Z
                    $this->matchCell(2, array('z1' => $x, 'y1' => $z, 'x1' => $y, 'z2' => $x+1, 'y2' => $z,   'x2' => $y));
                    if($this->getWinner()){
                        break 3;
                    }
                    
                    //проверка на ничью
                    $this->checkDraw($z, $y, $x);
                }//end for 3
            }//end for 2   
        }//end for 1
    }//and checkWinner

    private function zeroingStack($type = 'full') //сбрасывание стека на 0, (происходит при прохождении цикла массива)
    {
        if($type === 'full'){ //полное сбрасывание(для диагоналей)
            $this->stack = array(0,0,0,0,0,0,0,0,0,0,0,0,0);
            $this->side = array(0,0,0,0,0,0,0,0,0,0,0,0,0);
        }
        if($type === 'min'){ //неполное сбрасывание для обычных осей
            $this->stack[0] = 0;
            $this->stack[1] = 0;
            $this->stack[2] = 0;
            $this->side[0] = 0;
            $this->side[1] = 0;
            $this->side[2] = 0;
        }

    }

    private  function setCubeLength($length) //сетер для длины стороны куба
    {
        if($length >= self::minCubeLength and $length <= self::maxCubeLength){
            $this->cubeLength = $length - 1;
        }else{
            $this->cubeLength = self::minCubeLength - 1;
        }
        return $this;
    }

    private function getCubeLength() // гетер для длины стороны куба
    {
        return $this->cubeLength;
    }
    
    private function checkDraw($z, $y, $x)
    {
        if($this->getCubeCell($z, $y, $x) !== 'empty'){
            $this->countFullCell++;
        }
        if($this->getNumAllCubeCell() === $this->countFullCell){
            $this->setWinner('draw');
        }
    }
    
    private function setNumAllCubeCell()
    {
        $length = $this->getCubeLength() + 1;
        $this->numAllCubeCell = $length * $length * $length;
        return $this;
    }
    
    private function getNumAllCubeCell()
    {
        return $this->numAllCubeCell;
    }

    private function setWinner($winner) //сетер для победителя
    {
        $this->winner = $winner;
        return $this;
    }

    public function getWinner() //гетер для победителя
    {
        return $this->winner;
    }
    
    private function setCube($cube)
    {
        $this->cube = $cube;
        return $this;
    }
    
    private function getCubeCell($z,$y,$x)
    {
        return $this->cube[$z][$y][$x];
    }
    
    private function setSide($z,$y,$x,$stackNumber)
    {
        if(!is_array($this->side[$stackNumber])){
            $this->side[$stackNumber] = array(); 
        } 
        $this->side[$stackNumber][] = array('z' => $z, 'y' => $y, 'x' => $x);
        return $this;
    }
    
    public function getSide()
    {
        if(isset($this->side[$this->getWinnerNumberStack()])){
            return $this->side[$this->getWinnerNumberStack()];
        }
        return array();
    }
    
    private function setWinnerNumberStack($number)
    {
        $this->winnerNumberStack = $number;
        return $this;
    }

    private function getWinnerNumberStack()
    {
        return $this->winnerNumberStack;
    }

}