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
        return time() + $timeLeft;
    }
    
    // время оставшееся на ход, которое будет показано пользователю (секунды)
    public function getPlayerTime($timeLeft, $timeShtamp, $move)
    {
        if($this->_blitz){
            if($move){
                return $timeLeft - (time() - $timeShtamp);
            }else{
                return $timeLeft;
            }
        }
        if($move){
           return $timeShtamp - time();
        }else{
           return $timeLeft; 
        }
    }
}
