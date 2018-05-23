<?php
namespace Project\Exlant\ticTacToe\model\db;
use core\db\mongoDB;

class playGameDataMongoDB extends mongoDB
{
    const collection = 'rooms';
    
    private $creater = null;     //создатель комнаты(по нему происходит идентификация)  (string) // __construct
    private $login = null;       //логин игрока                                         (string) // __construct
    
    public function __construct($creater,$login) {
        parent::__construct();
        parent::setCollection(self::collection);
        $this->creater = $creater;
        $this->login = $login;
    }
    
    private function getCreater()       // гетер для creater
    {
        return $this->creater;
    }
    
    public function getLogin()         //гетер для login
    {
        return $this->login;
    }   
        
    public function playerExit($login, $playerMove)  // меняем поле(exit) игрока с no на yes
    {
        if($playerMove){
            $this->_update['$set']['players'][$login]['exit'] = 'yes';
            return $this;
        }
        $this->setUpdate('players.'.$this->getLogin().'.exit', '$set', 'yes');
        return $this;
    }
    
    public function setUpdateCurentPlayer($players, $player, $time)
    {
        // проверка или существует ключ массив с игроками в update
        $this->setUpdate('players', '$set', $players);
        $this->_update['$set']['players'][$player['name']]['move'] = null;
        $this->_update['$set']['players'][$player['name']]['timeLeft'] = $time;
        return $this;
    }
    
    public function setUpdateNextPlayer($players, $nextNick, $time)
    {
        // проверка или существует ключ массив с игроками в update
        $this->setUpdate('players', '$set', $players);
        $this->_update['$set']['players'][$nextNick]['move'] = true;
        $this->_update['$set']['players'][$nextNick]['timeShtamp'] = $time;
        return $this;
    }
    // запись хода игрока
    public function setUpdateRoomAfterMove($key, $move, $figure)
    {
        $this->setUpdate($key, '$set', $figure)
             ->setUpdate('movies', '$push', array(
                'login' => $this->getLogin(),
                'move' => $move)
        )
             ->setUpdate('change', '$inc', 1)
             ->setFind($key, 'empty')
             ->setFind('creater', $this->getCreater())
             ->setFind('status', 'start')
             ->setFind('players.'.$this->getLogin().'.status', 'play');
        return $this;
    }
    // удаляет последний элемент с массива movies и warnings
    // 
    //
    public function setMoveBack($queries, $gameArrayPuth)
    {
        $this->setUpdate('warnings', '$pop', 1)
             ->setUpdate('movies', '$pop', 1)
             ->setUpdate($gameArrayPuth, '$set', 'empty')
             ->setUpdate('change', '$inc', 1)
            ;
    }
    // выставляет стандартныйе параметры поиска играющей комнаты
    public function setStandartFindStartGame()
    {
        $this->setFind('creater', $this->getCreater())
             ->setFind('status', 'start');
        return $this;
    }
    
    public function setUpdatePoints($points, $winnerRow)
    {
        $this->_update['$set']['players'][$this->getLogin()]['points'] += $points;
        $this->setUpdate('winnerRow', '$push', $winnerRow);
        
        return $this;
    }
    
    public function setUpdateWinnerRowMoveBack($value)
    {
        $this->setUpdate('winnerRow', '$pull', $value);
        return $this;
    }
    
    public function setUpdateWinnerRow($winnerRow)
    {
        $this->setUpdate('winnerRow', '$push', $winnerRow);
        return $this;
    }
    
    public function setUpdateEndGame($winner)
    {
        $this->setUpdate('status', '$set', 'end')
             ->setUpdate('timeEnd', '$set', time())
             ->setUpdate('winner', '$set', $winner);
        return $this;
    }
    // меняет статус пользователяв  комнате на view / play
    public function changePlayerStatus($login, $status, $playerMove)
    {
        if($playerMove){
            $this->_update['$set']['players'][$login]['status'] = $status;
            return $this;
        }
        $this->setUpdate('players.'.$login.'.status', '$set', $status);
        return $this;
    }
    // устанавливае свободное место, с параметрами вышедшего игрока
    public function setFreePlace($freePlace)
    {
        $this->setUpdate('freePlace', '$push', $freePlace);
        return $this;
    }
    
    public function setRemoveFreePlace($freePlace)
    {
        $this->setUpdate('freePlace', '$pull', $freePlace);
        return $this;
    }
    // параметры поиска для выхода игрока из комнаты, или перевод его в зрители
    public function setFindForOutPlayer()
    {
        $this->setFind('creater', $this->getCreater())
             ->setFind('status', array('$in' => array('start', 'end')))
             ->setFind('players.'.$this->getLogin().'.exit', 'no');
        return $this;
    }
    
    public function setAddPlayer($player, $figure)
    {
        $this->setUpdate('players.'.$this->getLogin(), '$set', $player);
        $this->setUpdate('busyFigure.'.$this->getLogin(), '$set', $figure);
        return $this;
    }
    // устанавливает изменение в комнате
    public function setChangeInRoom()
    {
        $this->setUpdate('change', '$inc', 1);
        return $this;
    }
    
    private function getGameResult($type)
    {
        $result = array(
            'win' => array('$inc' => array(
                    'statistics.tictactoe.win' => 1,
                    'statistics.entire.win' => 1
                    )),
            'lose' => array('$inc' => array(
                    'statistics.tictactoe.lose' => 1,
                    'statistics.entire.lose' => 1
                    )),
            'draw' => array('$inc' => array(
                    'statistics.tictactoe.draw' => 1,
                    'statistics.entire.draw' => 1
                    ))
        );
        
        return $result[$type];
    }
    
    public function addToStatistics($type, $login)
    {
        $find = array('nick' => $login);
        $update = $this->getGameResult($type);
        $this->setCollection('users')
             ->update($find,$update);
        $this->setCollection(self::collection);
        return $this;
    }
    
    public function setWarnings($warning)
    {
        $this->setUpdate('warnings', '$push', $warning);
        
        return $this;
    }
    
    public function delateWarnings()
    {
        if(!isset($this->getUpdate()['$pull'])){
            return false;
        }
        $update = array(
            '$pull' => $this->getUpdate()['$pull']
        );
        unset($this->_update['$pull']);
        $this->getCollection()
             ->update($this->getFind(), $update);
    }
    
    public function unsetWarnings($unsetArrayField, $one, $two = null)
    {
        if($two !== null){
            $this->setUpdate('warnings.'.$one.'.'.$two.'.availableCell', '$pull', $unsetArrayField);
        }else{
            $this->setUpdate('warnings.'.$one, '$pull', $unsetArrayField);
        }
        return $this;
    }
    
    public function updateQuery($query, $key, $value)
    {
        $this->setUpdate('queries.'.$query.'.'.$key, '$set', $value);
        
        return $this;
    }
    
    public function setQuery($queries, $value, $query = '')
    {
        $newQueries = array();
        foreach ($queries as $q => $players){
            foreach ($players as $player => $val){
                if($q === $query and $player === $this->getLogin()){
                    $newQueries[$q][$player] = $value;
                }else{
                    $newQueries[$q][$player] = 0;
                }
            }
        }       
        $this->setUpdate('queries', '$set', $newQueries);
             
        return $this;
    }
    
    public function  recordPlayedGame($room)
    {
        $this->setCollection('gamePlayed')
             ->insert($room);
        $this->setCollection(self::collection);
        return $this;
    }
    
    public function startNewGame($players, $startMoveFigure, $gameAray, $queries)
    {
        $this->setFindForOutPlayer()
             ->setQuery($queries, 0)
             ->setUpdate('change', '$set', 0)
             ->setUpdate('status', '$set', 'start')
             ->setUpdate('winner', '$set', null)
             ->setUpdate('winnerRow', '$set', array())
             ->setUpdate('timeStart', '$set', time())
             ->setUpdate('timeEnd', '$set', 0)
             ->setUpdate('warnings', '$set', array())
             ->setUpdate('players', '$set', $players)
             ->setUpdate('movies', '$set', array())
             ->setUpdate('gameArray', '$set', $gameAray)
             ->setUpdate('startMove', '$set', $startMoveFigure)
             ->updateDB();
    }
}
