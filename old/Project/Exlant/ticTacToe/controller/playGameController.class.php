<?php
namespace Project\Exlant\ticTacToe\controller;

use Project\Exlant\ticTacToe\model\playGameModel;
use Project\Exlant\ticTacToe\model\db\playGameDataMongoDB;

class playGameController extends playGameModel
{
    public function __construct($login, $roomParam) 
    {
        parent::__construct(new playGameDataMongoDB($roomParam['creater'], $login), $roomParam);
        $this->systemProcess($login);               // методы без взаимодействия с пользователем
        $this->userAction($login);            // взаимодействие с пользователем      
    }
    
    private function systemProcess($login)
    {
        $this->setWinnerData() // ищет и если существует достает из базы данные по победителю, и устанавливает их в $winner, $winnerRow
             ->setLogin($login)
             ->setGameArray()  // устанавливает игровой массив                (array)
             ->setUsers();      // разделяет юзеров на игроков и зрителей (_players, _viewers)
                                // а также устанавливает время хода
        if($this->getRoomParam()['status'] === 'start'){
             $this->setWarnings()   // устанавливает предупреждения
                  ->setLastMove();  // последний сделанный ход
        }
        if($this->getWinner() === null){  // если победителя нет
            $this->timer($this->getMovingPlayer());
        }
        
        
    }
    
    private function userAction($login)
    {
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            if(filter_input(INPUT_GET, 'action') === 'takePlace'){
                $this->takePlace(filter_input(INPUT_GET, 'property'));                
            }
            
            if(filter_input(INPUT_GET, 'action')){
                //$this->redirectToPage();
            }
        }
    }
     
    private function timer($login)
    {
        if($this->getPlayerLeftTime() <= 0){
            $this->exitFromGame($login);
        }
    }
    
    protected function redirectToPage($page = '')
    {
        $page = ($page) ? '/'.$page : ''; 
        header('location:'.DOMEN.'/'.TICTACTOE.$page); //переадресация 
        exit();     //остановка скрипта  
    }
        
}

