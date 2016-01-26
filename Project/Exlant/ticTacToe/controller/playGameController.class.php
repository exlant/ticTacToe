<?php
namespace Project\Exlant\ticTacToe\controller;

use Project\Exlant\ticTacToe\model\playGameModel;
use Project\Exlant\ticTacToe\controller\ticTacToeFoursquare;
use Project\Exlant\ticTacToe\model\db\playGameDataMongoDB;

class playGameController extends playGameModel
{
    public function __construct($login, $roomParam) 
    {
        parent::__construct(new playGameDataMongoDB($roomParam['creater'], $login), $roomParam);
        $this->systemProcess();               // методы без взаимодействия с пользователем
        $this->userAction($login);                            // взаимодействие с пользователем      
    }
    
    private function systemProcess()
    {
        $this->setWinnerData() // ищет и если существует достает из базы данные по победителю, и устанавливает их в $winner, $winnerRow
             ->setGameArray()  // устанавливает игровой массив                (array)
             ->setUsers();      // разделяет юзеров на игроков и зрителей (_players, _viewers)
                                // а также устанавливает время хода
        if($this->getRoomParam()['status'] === 'start'){
             $this->setWarnings()   // устанавливает предупреждения
                  ->setLastMove();  // последний сделанный ход
        }
        if($this->getWinner() === null){  // если победителя нет
            
            //$this->timer();
        }
        
    }
    
    private function userAction($login)
    {
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            if(filter_input(INPUT_GET, 'action') === 'quitGame'){
                $this->quitGame($login);
                
            }
            if($this->getWinner() === null){
                if(filter_input(INPUT_GET, 'action') === 'playerMove' and $this->getMovingPlayer() === $login){
                    $this->setPlayerMove(filter_input(INPUT_GET, 'property'));
                }
            }
            if(filter_input(INPUT_GET, 'action')){
                //$this->redirectToPage();
            }
        }
    }
     
    private function timer()
    {
        if($this->getPlayerLeftTime() <= 0){
            $this->setNextMovePlayer();             // передаем ход следующему игроку
            $this->dropUser();  // переводим текущего игрока в статус view, и записуем ему +1 в lose
            
            $countPlayers = 0;
            foreach($this->getPlayers() as $val){
                if($val['status'] === 'play'){
                    $countPlayers++;
                    $winner = $val['name'];     //если в массиве останется один игрок, он же будет победителем
                }
            }
            if($countPlayers < 2){
                $this->endGame($winner);
                $this->redirectToPage();
                exit();
            }
        }
    }
    
    protected function redirectToPage($page = '')
    {
        $page = ($page) ? '/'.$page : ''; 
        header('location:'.DOMEN.'/'.TICTACTOE.$page); //переадресация 
        exit();     //остановка скрипта  
    }
        
}

