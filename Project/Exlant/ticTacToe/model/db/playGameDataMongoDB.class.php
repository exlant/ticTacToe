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
        
    public function quitGame()                          // меняем поле(exit) игрока с no на yes
    {
        $find = array(
            'creater' => $this->getCreater(),
            'status' => array('$in' => array('start', 'end')),
            'players.'.$this->getLogin().'.exit' => 'no'
            
        );
        $update = array('$set' => array('players.'.$this->getLogin().'.exit' => 'yes'));
        $this->getCollection()
             ->update($find,$update);
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
    
    public function setUpdateRoomAfterMove($key, $move)
    {
        $this->setUpdate($key, '$set', $this->getLogin())
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
        $this->setQuery($queries, 'confirm', 0) // выставляет и параметры поиска
             ->setUpdate('warnings', '$pop', 1)
             ->setUpdate('movies', '$pop', 1)
             ->setUpdate($gameArrayPuth, '$set', 'empty')
             ->setUpdate('change', '$inc', 1)
            ;
    }
    
    public function setUpdatePoints($points, $winnerRow)
    {
        $this->_update['$set']['players'][$this->getLogin()]['points'] += $points;
        $this->setUpdate('winnerRow', '$push', $winnerRow);
        
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
    
    public function changePlayerStatus($movingPlayer)
    {
        $find = array(
            'creater' => $this->getCreater(),
            'status' => 'start',
        );
        $update = array('$set' => array('players.'.$movingPlayer.'.status' => 'view'));   // переводм игрока в режим посмотра в коллекции room
        $this->getCollection()
             ->update($find,$update);
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
    }
    
    public function setWarnings($warning, $countMovies)
    {
        $num =  $countMovies - 1;
        $this->setUpdate('warnings.'.$num, '$set', $warning);
        
        return $this;
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
    
    public function updateQuery($query)
    {
        $this->setUpdate('queries.'.$query.'.'.$this->getLogin(), '$set', 1)
             ->setFind('creater', $this->getCreater())
             ->setFind('status', 'start');
        
        return $this;
    }
    
    public function setQuery($queries, $query, $value)
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
        $this->setUpdate('queries', '$set', $newQueries)
             ->setFind('creater', $this->getCreater())
             ->setFind('status', 'start');
        return $this;
    }
    
    public function addPlusQuery()
    {
        
    }
    
    public function getQuery()
    {
        $find = array(
            'creater' => $this->getCreater(),
            'status' => 'start'
        );
        $needle = array('query');
        return $this->getCollection()
                    ->findOne($find, $needle);
    }
}
