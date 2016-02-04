<?php

namespace Project\Exlant\ticTacToe\controller;

class time {
    
    private $_blitz = null; // 
    
    public function __construct($blitz) 
    {
        $this->_blitz = $blitz;
    }
    
    public function timeLeft($timeLeft, $timeShamp)
    {
        if($this->_blitz){
            return $timeLeft - (time() - $timeShamp);
        }
        return $timeLeft;
    }
    // временная метка перед в начале хода
    public function timeShtamp($timeLeft)
    {
        if($this->_blitz){
            return time();
        }
        return time() + (int)$timeLeft;
    }
    
    // время оставшееся на ход, которое будет показано пользователю (секунды)
    public function getPlayerTime($timeLeft, $timeShtamp, $move)
    {
        $timeReturn = 0;
        if($this->_blitz){
            if($move){
                $timeReturn = $timeLeft - (time() - $timeShtamp);
            }else{
                $timeReturn = $timeLeft;
            }
        }else{
            if($move){
               $timeReturn = $timeShtamp - time();
            }else{
               $timeReturn = $timeLeft; 
            }
        }
        
        return ($timeReturn < 0) ? 0 : $timeReturn;
    }
}
