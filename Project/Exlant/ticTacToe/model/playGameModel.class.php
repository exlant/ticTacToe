<?php
namespace Project\Exlant\ticTacToe\model;
use Project\Exlant\ticTacToe\model\db\playGameDataMongoDB;
use Project\Exlant\ticTacToe\controller\ticTacToeFoursquare;
use Project\Exlant\ticTacToe\controller\time;

class playGameModel
{
    private $_Data = null;        // экземпляр класса mongoDb
    public $time = null;         // объект отвечающий за время
    
    public $login = null;
    private $_roomParam = array();   // параметры комнаты                                   (array)
    private $_winner = null;      // логин победителя                                    (string)
    private $_winnerRow = array();  // координаты выйгравшей линии                         (array)   
    private $_warnings = array();   // массив с угрозами
    private $_gameArray = array();   // массив с игровым полем                              (array)
    private $_players = array();     // массив с игроками                                   (array)
    private $_viewers = array();     // массив с зрителями                                  (array)
    //private $_busyFigure = array();  // массив с задеянными фигурами
    private $_playerLeftTime = null; // оставшееся время игрока на ход                   (int)
    private $_movingPlayer = null; //логин игрока, который должен ходить                 (string) 
    private $_chekGameArray = null; // объект проверки поля на победителя                (object)
    private $_lastMove = array(); // последний сделанный ход
    
    public function __construct(playGameDataMongoDB $mongoDB, $roomParam) {
        $this->_Data = $mongoDB;
        $this->_roomParam = $roomParam;       // устанавливаем roomParam
        $this->time = new time($roomParam['blitz']);
    }
    
    private function getData()          // экземпляр класса mongoDb
    {
        return $this->_Data;
    }

    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }
    
    public function getLogin()
    {
        return $this->login;
    }
    
    public function getRoomParam()   // гетер для roomParam
    {
        return $this->_roomParam;
    }
    
    protected function setGameArray($gameArray = array())  //сетер для gameArray
    {
        $this->_gameArray = ($gameArray) ? $gameArray : $this->getRoomParam()['gameArray'];
        return $this;
    }


    public function getGameArray()    // гетер массива с игровым полем 
    {
        return $this->_gameArray;
    }
    
    protected function setWinnerData()
    {
        if($this->getRoomParam()['status'] === 'end'){
            $this->_winner = $this->getRoomParam()['winner'];
            $winnerRow = array();
            foreach($this->getRoomParam()['winnerRow'] as $row){
                foreach($row as $cell){
                    if(array_search($cell, $winnerRow) === false){
                        $winnerRow[] = $cell;
                    }
                }
                
            }
            $this->_winnerRow = $winnerRow;
        }
        
        return $this;
    }
    
    public function getWinner()
    {
        return $this->_winner;
    }
    
    public function getWinnerRow()
    {
        $coordinates = array();
        if($this->_winnerRow){
            foreach($this->_winnerRow as $line){
                foreach($line as $coordinate){
                    if(array_search($coordinate, $coordinates) === false){
                        $coordinates[] = $coordinate;
                    }
                }
            }
        }
        return $coordinates;
    }
    
    
    protected  function setUsers()          // устанавливает массив _players и массив _viewers
    {
        $users = $this->getRoomParam()['players'];
        $data = array();
        foreach($users as $player => $val){
            if($val['status'] === 'play'){
                $data['players'][$player] = $val;
                // время, которое будет выведено пользователю
                $data['players'][$player]['timeOut'] = $this->time
                        ->getPlayerTime($val['timeLeft'], $val['timeShtamp'], $val['move']);
                if($val['move']){
                    $this->setPlayerLeftTime($data['players'][$player]['timeOut']);
                    $this->setMovingPlayer($player);
                }
            }
            if($val['status'] === 'view'){
                $data['viewers'][$player] = $val;
            }
        }
        $this->_players = (isset($data['players'])) ? $data['players'] : null;
        $this->_viewers = (isset($data['viewers'])) ? $data['viewers'] : null;
        return $this;
        
    }
    
    public function getPlayers()
    {
        if($this->_players){
            return $this->_players;
        }
        return array();
    }
    
    public function getViewers()
    {
        return $this->_viewers;
    }
    // устанавливает время на ход, ходящему игроку в setUsers
    protected function setPlayerLeftTime($timeLeft)
    {
        $this->_playerLeftTime = $timeLeft;
        return $this;
    }
    
    public function getPlayerLeftTime()
    {
        return $this->_playerLeftTime;
    }
    
    // ищет игрока, который ходит
    // устанавливается в setUsers
    protected function setMovingPlayer($login)        
    {       
        $this->_movingPlayer = $login;
        return $this;   
    }
    
    public function getMovingPlayer()           // гетер для игрока, котрый ходит
    {
        if($this->_movingPlayer){
            return $this->_movingPlayer;
        }
        return null;
    }
    
    public function setPlayerMove($move) // записуем ход игрока, проверяем закончил ли он игру, 
    {
        if($move){  // если пришедшие данные хода корректны
            $this->setNextMovePlayer()
                 ->burnMoveToGameArray($move);
            $this->_chekGameArray = new ticTacToeFoursquare($this->getMovingPlayer(), $this->getGameArray(), $move, $this->getRoomParam()['figureInArow'], $this->getRoomParam()['points']); // ищем победителя в массиве с игровым полем
            if($this->getRoomParam()['points'] === 'yes'){
                $this->checkWinByPoints();
            }else{
                $this->checkWinner();
            }
            $this->checkWarnings($move);
            
            //var_dump($this->_Data->getUpdate());
            // записуем ход игрока в базу
            $this->_Data->updateDB();
        }
        
    }
    
    private function setMoveBack()
    {
        $lastMove = $this->getLastMove()[0];
        if($lastMove){
            if($this->getRoomParam()['type'] === '2d'){
                $gameArrayPuth = 'gameArray.'.$lastMove['y'].'.'.$lastMove['x'];
            }elseif($this->getRoomParam()['type'] === '3d'){
                $gameArrayPuth = 'gameArray.'.$lastMove['z'].'.'.$lastMove['y'].'.'.$lastMove['x'];
            }
            $this->setNextMovePlayer(-1)
                 ->_Data->setMoveBack($this->getRoomParam()['queries'],$gameArrayPuth);
        }
        if($this->getRoomParam()['points'] === 'yes'){
            $this->unsetFromWinnerRows();
        }
        
        // записыем ход назад в базу
        $this->_Data->setStandartFindStartGame()
                    ->updateDB();
        return $this;
    }
    // удаляет из winnerRows ряды, в которых есть ячейки, соответствующие отмененому ходу
    public function unsetFromWinnerRows()
    {
        $rowsArray = $this->getRoomParam()['winnerRow'];
        $lastMove = $this->getLastMove()[0];
        foreach($rowsArray as $rows){
            foreach($rows as $row){
                if(array_search($lastMove, $row) !== false){
                    $this->_Data->setUpdateWinnerRowMoveBack($rows);
                    break;
                }
            } 
        }
        return $this;
    }
    
    //записуем новый ход в массив, и в update()
    private function burnMoveToGameArray($move)
    {
        $newGameArray = $this->getGameArray();
        $figure = $this->getPlayers()[$this->getMovingPlayer()]['figure'];
        if($this->getRoomParam()['type'] === '2d'){
            $key = 'gameArray.'.$move['y'].'.'.$move['x'];
            $newGameArray[$move['y']][$move['x']] = $figure;
            
        }elseif($this->getRoomParam()['type'] === '3d'){
            $key = 'gameArray.'.$move['z'].'.'.$move['y'].'.'.$move['x'];
            $newGameArray[$move['z']][$move['y']][$move['x']] = $figure;
        }
        
        $this->_Data->setUpdateRoomAfterMove($key, $move, $figure);
        $this->setGameArray($newGameArray);
        return true;
    }
    
    private function checkWinByPoints()
    {
        if($this->_chekGameArray->getPoints() > 0){
            $this->_Data->setUpdatePoints($this->_chekGameArray->getPoints(), $this->_chekGameArray->getWinnerRow());
            $totalPoints = $this->_chekGameArray->getPoints() + $this->getRoomParam()['players'][$this->getMovingPlayer()]['points'];
            if($totalPoints >= $this->getRoomParam()['pointsNum']){
                $this->endGame($this->_chekGameArray->getMovingPlayer());
            } 
        }
        return $this;    
    }
    
    private function checkWinner()
    {
        if($this->_chekGameArray->getWinner() !== null){
            $this->_Data->setUpdateWinnerRow($this->_chekGameArray->getWinnerRow());
            $this->endGame($this->_chekGameArray->getWinner());   // если есть имя, то завершаем игру
        }
        return $this;
    }
    
    private function checkWarnings($move)
    {
        // проверка не перебил ли ход, какое то предупреждение
        $arrayW = $this->getRoomParam()['warnings']; 
        if($arrayW){
            foreach($arrayW as $one => $warnings){
                if(is_array($warnings)){
                    foreach($warnings as $two => $warning){
                        $cellKey = array_search($move, $warning['availableCell']);
                        if($cellKey !== false){
                            if(isset($warning['add']) and $warning['add'] === 'all'){
                                if(count($warning['availableCell']) === 1 or
                                        $this->checkOnMatchFigure($warning['movies'][0])){
                                    $this->_Data->unsetWarnings($warning, $one);
                                }else{
                                    $cell = $warning['availableCell'][$cellKey];
                                    $this->_Data->unsetWarnings($cell, $one, $two);
                                }
                            }else{
                                $this->_Data->unsetWarnings($warning, $one);
                            }
                        }
                    }
                }
            }
        }
        // удаляется прерванные предупреждения отдельным запросом
        // Cannot update '' at the same time
        //var_dump($this->_Data->getUpdate());
        $this->_Data->delateWarnings();
        $this->_Data->setWarnings($this->_chekGameArray->getWarnings());
        
    }
    
    private function checkOnMatchFigure($coordination)
    {
        $figureInWarnings = $this->_chekGameArray->getForsquareCell($coordination['y'],$coordination['x']);
        $figureMovingPlayer = $this->getPlayers()[$this->getLogin()]['figure'];
        if($figureInWarnings === $figureMovingPlayer){
            return true;
        }
        return false;
    }
    // устанавливает существующие в базе warnings в переменную $this->_warnings
    protected function setWarnings()
    {
        $allWarnings = array();
        if(is_array($this->getRoomParam()['warnings'])){
            foreach($this->getRoomParam()['warnings'] as $warnings){
                if(is_array($warnings)){
                    foreach($warnings as $warning){
                        foreach($warning['movies'] as $coordinate){
                            if(array_search($coordinate, $allWarnings) === false){
                                $allWarnings[] = $coordinate;
                            }
                        }                        
                    }
                } 
            }
        }
        $this->_warnings = $allWarnings;
        return $this;
    }
    
    public function getWarnings()
    {
        return $this->_warnings;
    }
    
    // определяет следующий/предыдущий ход, выставляет время походившему, и тому кто будет ходить 
    protected function setNextMovePlayer($type = 1)
    {                                                       
        $players = $this->getPlayers();
        
        while($player = current($players)){
            if($player['move']){
                $this->_Data->setUpdateCurentPlayer($this->getRoomParam()['players'], $player, 
                        $this->time->timeLeft($player['timeLeft'], $player['timeShtamp']));
                if($type === 1){
                    $next = next($players);
                    $nextNick = ($next) ? $next['name'] : reset($players)['name'];
                    
                }elseif($type === -1) {
                    $next = prev($players);
                    $nextNick = ($next) ? $next['name'] : end($players)['name'];           
                }
                break;
            }
            next($players);
        }
        
        $this->_Data->setUpdateNextPlayer($this->getRoomParam()['players'], $nextNick, $this->time
                ->timeShtamp($players[$nextNick]['timeLeft']));
        
        return $this;   
    }
    
    public function exitFromGame($login, $type = '')
    {
        $players = $this->getPlayers();
        $move = (isset($players[$login]['move'])) ? $players[$login]['move'] : null;
        $countPlayers = count($players);
        $playerMove = 0;
        $addToStatistic = 0;
        $setFreePlace = 0;
        //выбрасываем из комнаты
        if($type === 'outGame'){
            $this->_Data->playerExit($login, $playerMove);
            if($this->getRoomParam()['players'][$login]['status'] === 'play'){
                $this->_Data->setFreePlace($this->generateFreePlace($login, $playerMove));
            }
            $setFreePlace = 1;
        }
        if($this->getRoomParam()['players'][$login]['status'] === 'play' and $this->getRoomParam()['status']  === 'start' ){
            unset($players[$login]);
            if($countPlayers === 2){
                
                $this->endGame(array_pop($players)['name']);
                $addToStatistic = 1;
            }else {
                if($move){
                    $this->setNextMovePlayer();
                    $playerMove = 1;
                }
                // переводим в зрители
                $this->_Data->changePlayerStatus($login, 'view', $playerMove);
                if($setFreePlace !==1){
                    $this->_Data->setFreePlace($this->generateFreePlace($login, $playerMove));
                }
            }
            if($addToStatistic !== 1){
                $this->_Data->addToStatistics('lose', $login);
            }
            $queries = $this->generateNewQueries($login, $this->getRoomParam()['queries']);
            $this->_Data->setQuery($queries, 0);
        }
        $this->_Data->setChangeInRoom()
                    ->setFindForOutPlayer()
                    ->updateDB();
        return $this;
    }
    // устанавливает массив с параметрами вышедшего игрока
    private function generateFreePlace($login, $playerMove)
    {
        $freePlace = array(
            'figure' => $this->getPlayers()[$login]['figure'],
            'timeLeft' => ($playerMove) 
                ? $this->time->timeLeft($this->getPlayers()[$login]['timeLeft'], $this->getPlayers()[$login]['timeShtamp']) 
                : $this->getPlayers()[$login]['timeLeft'],
            'timeOut' => null,
            'points' => $this->getPlayers()[$login]['points'],
            'free'   => 1,
        );
        $freePlace['timeOut'] = $freePlace['timeLeft'];
        return $freePlace;
    }
    
    private function generateNewQueries($login, $queries, $type = 'remove')
    {
        foreach($queries as $query => &$players){
            if($type === 'add'){
                $players[$login] = 0;
            }else{
                unset($players[$login]);
            }
            
        }
        return $queries;
    }
    
    public function takePlace($figure)
    {
        $countPlayers = count($this->getPlayers());
        if((int)$this->getRoomParam()['numPlayers'] > $countPlayers
                and $this->getRoomParam()['players'][$this->getLogin()]['status'] === 'view'
                and !empty($this->getRoomParam()['freePlace']
                and $this->getRoomParam()['players'][$this->getLogin()]['exit'] === 'no')){
            foreach($this->getRoomParam()['freePlace'] as $value){
                if($value['figure'] === $figure){
                    $newPlayer = array(
                       'name' => $this->getLogin(),
                       'figure' => $figure,
                       'status' => 'play',
                       'exit' => 'no',
                       'timeLeft' => $value['timeLeft'],
                       'timeShtamp' => 0,
                       'points' => $value['points'],
                       'move' => null
                    );
                    $freePlace = $value;
                    break;
                }
            }
            $this->_Data;
            $this->_Data->setAddPlaer($newPlayer)
                 ->setChangeInRoom()
                 ->setRemoveFreePlace($freePlace)
                 ->setFindForOutPlayer()
                 ->setQuery($this->generateNewQueries($this->getLogin(), $this->getRoomParam()['queries'], 'add'), 0)
                 ->updateDB();
         //var_dump($this->_Data->getFind());
         //var_dump($this->_Data->getUpdate());
        }
    }

    protected function endGame($winner)
    {
        //меняем статус игры на end, записуем победителя
        $this->_Data->setUpdateEndGame($winner);
        //добавляем игроку в статистику побед, проиграшей, ничьих +1
        $players = $this->getPlayers();
        foreach($players as $login => $val){
            $type = 'lose';
            if($winner === $login){
                $type = 'win';
            }
            if($winner === 'draw'){
                $type = 'draw';
            }
            $this->_Data->addToStatistics($type, $login);
        }
    }
    // устанавливает последний сделанный ход
    protected function setLastMove()
    {
        $movies = $this->getRoomParam()['movies'];
        $move = end($movies);
        $this->_lastMove[] = $move['move'];
        return $this;
    }
    // получение последнего сделанного ходаs
    public function getLastMove()
    {
        return $this->_lastMove;
    }
    
    public function sendQuery($query, $value)
    {
        // запрос - 2;
        // подтвердить - 1;
        // отказ - "-1";
        if(!isset($this->getPlayers()[$this->getLogin()]) 
                or $this->getPlayers()[$this->getLogin()]['exit'] !== 'no'
                or $this->getPlayers()[$this->getLogin()]['status'] !== 'play'){
            return false;
        }
        $value = (int)$value;
        // массив с запросами игроков
        $queries = $this->getRoomParam()['queries'];
        if($value === 2 or $value === -1){
            // новый запрос, или отмена запроса, выставляет все предыдущие запросы пользователей в 0
            $this->_Data->setQuery($queries, $value, $query);
        }elseif($value === 1){
            $negativeAnswer = 0;
            // если это не подтверждение отмены действия
            if($query !== 'confirm'){
                // проверка на негативный ответ
                // если уже кто-то дал негативный ответ, то поддтвердить запрос уже нельзя
                array_walk_recursive($queries, function($item) use(&$negativeAnswer){
                    if($item === -1){
                        $negativeAnswer = 1;
                    }
                });
            }
            if($negativeAnswer !== 1){
                // запрос с значением 1(подтверждение запроса пользователя)
                $this->_Data->updateQuery($query);
            }           
        }
        $this->_Data->setStandartFindStartGame()
                    ->updateDB();
        return $this;
    }
    
    public function checkQuery($login)
    {
        if(!isset($this->getPlayers()[$login]) 
                or $this->getPlayers()[$login]['exit'] !== 'no'
                or $this->getPlayers()[$login]['status'] !== 'play'){
            return false;
        }
        $queries = $this->getRoomParam()['queries'];
        $countPlayers = count($this->getPlayers()); 
        $stack = null;
        foreach($this->getRoomParam()['queries'] as $query => $players){
            $count = 0;
            $break = 0;
            $stackNull = 0;
            foreach($players as $player => $value){
                $count += $value;
                
                // все игроки подтвердили ход назад
                if($count === $countPlayers + 1 and $query === "moveBack"){
                    $this->setMoveBack();
                    return null;
                }
                // все подтвердили confirm(подтверждение отмены запроса) 
                // обновляем массив с запросами
                if($count === $countPlayers - 1 and $query === "confirm"){
                    $this->_Data->setQuery($queries, 0)
                                ->setStandartFindStartGame()
                                ->updateDB();
                }
                // кто то сделал запрос
                if($value === 2 and $login !== $player){
                    $stack = array();
                    $stack['login'] = $player;
                    $stack['query'] = $query;
                    $break = 1;
                }
                // игрок уже подтвердил запрос, или confirm
                if($value === 1 and $login === $player){
                    $break = 1;
                    $stackNull = 1;
                }
                // кто-то из игроков отклонил запрос
                if($value === -1 and $login !== $player){
                    $stack = array();
                    $stack['login'] = $player;
                    $stack['query'] = $query;
                    $stack['value'] = -1;
                }
            }
            
            if($stackNull === 1){
                return null;
            }
            
            if($break === 1){
                break;
            }           
        }
        return $stack;
    }
}

