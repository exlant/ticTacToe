<?php
namespace Project\Exlant\ticTacToe\controller;
use Project\Exlant\ticTacToe\model\mainModel;
use Project\Exlant\ticTacToe\controller\playGameController;
use core\startCore;

class mainController extends mainModel
{
    public function __construct($login)
    {
        parent::__construct();
        $this->setRoomSettings($login); 
        $this->whetherInRoom($login); //устанавливает UserBusyInfo
        if($this->getUserBusyInfo()['roomStatus'] !== 'start' and $this->getUserBusyInfo()['roomStatus'] !== 'end'){  //если игрок не находиться в игре
            $this->methodGet($login);
            $this->methodPost($login);            
            if($this->getUserBusyInfo()['roomStatus'] === 'created'){
                $this->textAddPlayersPage($login);
            }
            $this->setSingleRooms($login);  //достать из базы все одиночные комнаты
        }else{
            $playGame = new playGameController($login, $this->getRoomSettings());
            startCore::setObject('playGame', $playGame);
        }
        $this->setJSCss(); //устанавливает css
    }
    
    private function methodGet($login)
    {
        if($_SERVER['REQUEST_METHOD'] === 'GET'){
            if(filter_input(INPUT_GET, 'action') === 'addRoom'){
                $this->addRoom($login);
            }
            if(filter_input(INPUT_GET, 'action') === 'dropRoom'){
                $this->dropRoom($login);
            }
            if(filter_input(INPUT_GET, 'action') === 'enterRoom'){
                $creater = filter_input(INPUT_GET, 'property');
                $this->enterRoom($creater,$login);
            }
            if(filter_input(INPUT_GET, 'action') === 'exitRoom'){
                $this->exitRoom($login);
            }
            if(filter_input(INPUT_GET, 'action') === 'dropOpponent'){
                $this->dropOpponent($login,filter_input(INPUT_GET, 'property'));
            }
            if(filter_input(INPUT_GET, 'action') === 'startGame'){
                $this->startGame($login);
            }
            if(filter_input(INPUT_GET, 'action') === 'followGame'){
                $this->followGame($login, filter_input(INPUT_GET, 'property'));
            }
            if(filter_input(INPUT_GET, 'action')){
                $this->redirectToPage();
            }

        }
    }
    
    private function methodPost($login)
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(filter_input(INPUT_POST, 'type') === 'createRoom' 
                    and $this->getUserBusyInfo()['roomStatus'] === 'creating'){
                $this->createRoom($login,
                        filter_input(INPUT_POST, 'fildLength'),
                        filter_input(INPUT_POST, 'figure'),
                        filter_input(INPUT_POST, 'players'),
                        filter_input(INPUT_POST, 'roundTime'),
                        filter_input(INPUT_POST, 'figureInArow'),
                        filter_input(INPUT_POST, 'points'),
                        filter_input(INPUT_POST, 'pointsNum'),
                        filter_input(INPUT_POST, 'blitz')
                        );
            }
            
            if(filter_input(INPUT_POST, 'type') === 'setFigure'){
                $this->setFigure($login,  filter_input(INPUT_POST, 'figure'));
            }
            // для ajax запросов
            if(filter_input(INPUT_POST, 'object') === 'tictactoe'){
                return true;
            }
            
        }
        
    }
    
    private function checkUserBusy($login)   // проверяет не находится ли игрок в игре, или в другой комнате
    {
        if($this->checkPlaying($login)){    // если игрок уже находится в игре, выходим из добавления комнаты
            return false;
        }
        if($this->getUserBusyInfo()['action'] === 'creater'){//если пользователь уже создал комнату
            parent::dropRoom($login);
        }
        if($this->getUserBusyInfo()['action'] === 'joiner'){//если пользователь был присоединен к комнате
            parent::exitRoom($login);
        }
        return true;
    }
    
    protected function addRoom($login) //добавляем комнату в коллекцию rooms
    {
        if($this->checkUserBusy($login)){  //проверяем не находится ли игрок в другой комнате, если да то выкидуем его оттуда
            parent::addRoom($login);   //добавляем новую комнату в таблицу
        }    
        $this->redirectToPage();
    }
    
    protected function createRoom($login,$fildLength,$figure,$numberPlayers,$roundTime, $figureInArow, $points, $pointsNum, $blitz)
    {
        if($fildLength < $this->fildLength['min'] or $fildLength > $this->fildLength['max']
                or !isset($this->figure)
                or $figureInArow < $this->figureInArow['min'] or $figureInArow > $this->figureInArow['max']
                or $pointsNum < $this->pointsNum['min'] or $pointsNum > $this->pointsNum['max']
                or $numberPlayers < $this->players['min'] or $numberPlayers > $this->players['max']
                or $roundTime < $this->roundTime['min'] or $roundTime > $this->roundTime['max']){
            $this->redirectToPage();
        }
        parent::createRoom($login,$fildLength,$figure,$numberPlayers,$roundTime, $figureInArow, $points, $pointsNum, $blitz);
        $this->redirectToPage();
    }
    
    protected function dropRoom($login)
    {
        parent::dropRoom($login);
        $this->redirectToPage();
    }
    protected function enterRoom($creater,$login)
    {
        $this->checkUserBusy($login);
        parent::enterRoom($creater,$login);
        $this->redirectToPage(); 
    }
    
    protected function setFigure($login, $figure){
        parent::setFigure($login,$figure);
        $this->redirectToPage();
    }
    
    protected function exitRoom($login) {
        parent::exitRoom($login);
        $this->redirectToPage();    
    }
    
    protected function dropOpponent($login,$player)
    {
        parent::dropOpponent($login,$player);
        $this->redirectToPage();
    }
    
    protected function followGame($login, $room)
    {
        $this->checkUserBusy($login);
        parent::followGame($login, $room);
    }


    private function redirectToPage($page = '')
    {
        $page = ($page) ? '/'.$page : ''; 
        header('location:'.DOMEN.'/'.TICTACTOE.$page); //переадресация 
        exit();     //остановка скрипта  
    }
    
    private function setJSCss()
    {
        $playerInfo = $this->getUserBusyInfo();
        if($playerInfo['roomStatus'] === 'creating'){
            startCore::setCSS('ticTacToeCreatingRoom.css'); //устанавливаем путь к css файлу
        }
        if($playerInfo['roomStatus'] === 'created'){
            startCore::setCSS('ticTacToeAddPlayers.css');
        }
        if($playerInfo['roomStatus'] !== 'start' and $playerInfo['roomStatus'] !== 'end'){
            startCore::setCSS('ticTacToeViewRooms.css');
            startCore::setJS('viewRooms.js');
        }else{
            startCore::setCSS('ticTacToePlayGame.css');
            startCore::setJS('ticTacToePlayGame.js');
        }
        
    }
    
    private function textAddPlayersPage($login)
    {
        $data = array();
        $data['roomSettings'] = array(
            'sideLength' => 'Длина стороны',
            'figureInArow' => 'Фигур в ряд',
            'pointsText' => 'Игра на очи',
            'blitzText' => 'Блиц',
            'roundTime' => 'Время на ход',
            'numPlayers' => 'Число игроков',
            
        );
        
        $data['figure'] = $this->figure;
        $this->setUserBusyInfo('html', $data);
    }
    
    protected function startGame($login)  //стартуем игру
    {
        if($this->getUserBusyInfo()['action'] === 'creater'     //стартуем игру, если данный игрок создатель комнаты, 
                && $this->getUserBusyInfo()['joiner'] === 'full'){ //и если зашли все игроки
            
            $properties = $this->getUserBusyInfo();
            $gameArray = $this->createGameArray($properties['type'], $properties['sideLength']);  //создаем игровое поле
            $this->startingGame($login,$properties['players'],$gameArray,$properties['roundTime']); // запускаем игру
        }
        $this->redirectToPage();
    }
    
    private function createGameArray($type,$sideLength)
    {
        $gameArray = array();
        if($type === '2d'){
            for($sideY = 0; $sideY < $sideLength; $sideY++){
                for($sideX = 0; $sideX < $sideLength; $sideX++){
                    $gameArray[$sideY][$sideX] = 'empty';
                }
                
            }
        }
        if($type === '3d'){
            for($sideZ = 0; $sideZ < $sideLength; $sideZ++){
                for($sideY = 0; $sideY < $sideLength; $sideY++){
                    for($sideX = 0; $sideX < $sideLength; $sideX++){
                        $gameArray[$sideZ][$sideY][$sideX] = 'empty';
                    }

                }
            }
        }
        return $gameArray;
    }
           
}