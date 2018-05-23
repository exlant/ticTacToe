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
    private $_playerLeftTime = null; // оставшееся время игрока на ход                   (int)
    private $_movingPlayer = null; //логин игрока, который должен ходить                 (string) 
    private $_chekGameArray = null; // объект проверки поля на победителя                (object)
    private $_lastMove = array(); // последний сделанный ход
    private $_queries = array(
        'moveBack' => array(
            'num' => 1,
            'function' => 'setMoveBack'
        ),
        'draw' => array(
            'num' => 1,
            'function' => 'setDraw'
        ),
        'playAgain' => array(
            'num' => 0,
            'function' => 'playAgain'
        ),
        'confirm' => array(
            'num' => -1,
            'function' => 'reflashConfirm'
        ),
    );
    private $_newPlayers = array(); // сюда пересоздается массив с игроками, для начала новой игры 
    private $_newStartMoveFigure = null; // фигура, которая будет первая ходить
    
    
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
            $this->_winner = ($this->getRoomParam()['winner'] === 'draw') 
                    ? 'Ничья' 
                    : $this->getRoomParam()['winner'];
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
            if($val['status'] === 'play' and $val['exit'] === 'no'){
                $data['players'][$player] = $val;
                // время, которое будет выведено пользователю
                $data['players'][$player]['timeOut'] = $this->time
                        ->getPlayerTime($val['timeLeft'], $val['timeShtamp'], $val['move']);
                if($val['move']){
                    $this->setPlayerLeftTime($data['players'][$player]['timeOut']);
                    $this->setMovingPlayer($player);
                }
                if($this->getRoomParam()['status'] === 'end' 
                        and $this->getRoomParam()['queries']['playAgain'][$val['figure']] === 1){
                    $data['players'][$player]['playAgain'] = 1;
                }else{
                    $data['players'][$player]['playAgain'] = 0;
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
            // записуем ход игрока в базу
            $this->_Data->updateDB();
            $this->checkOnDraw();
        }
        
    }
    
    private function checkOnDraw()
    {
        $length = $this->getRoomParam()['sideLength'];
        $cellNum = $length * $length;
        $moviesNum = count($this->getRoomParam()['movies']) + 1;
        if($cellNum === $moviesNum){
            $this->setDraw();
        }
        return;
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
                    ->setQuery($this->getRoomParam()['queries'], 0)
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
            if($query === 'playAgain'){
                continue;
            }
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
            $this->_Data->setAddPlayer($newPlayer,$figure)
                        ->setChangeInRoom()
                        ->setRemoveFreePlace($freePlace)
                        ->setFindForOutPlayer()
                        ->setQuery($this->generateNewQueries($this->getLogin(), $this->getRoomParam()['queries'], 'add'), 0)
                        ->updateDB();
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
        return $this;
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
    // записует сыгранную игру, и запускает новую 
    private function playAgain()
    {
        $room = $this->getRoomParam();
        unset($room['_id']);
        $gameAray = $this->createGameArray($this->getRoomParam()['sideLength']);
        $this->changePlayersData()
             ->chooseWhoStart()
             ->_Data->recordPlayedGame($room)
                    ->startNewGame($this->_newPlayers, $this->_newStartMoveFigure, $gameAray, $this->getRoomParam()['queries']);
    }
    // вспомагательная к playAgain, создает поле для игры
    private function createGameArray($sideLength)
    {
        $sideLength = (int)$sideLength;
        $gameArray = array();
        for($sideY = 0; $sideY < $sideLength; $sideY++){
            for($sideX = 0; $sideX < $sideLength; $sideX++){
                $gameArray[$sideY][$sideX] = 'empty';
            }

        }
        return $gameArray;
    }
    // вспомагательная к playAgain, создает поле для игры
    private function changePlayersData()
    {
        $timeLeft = (int)$this->getRoomParam()['roundTime'];
        $players = $this->getRoomParam()['players'];
        foreach($players as $player => &$param){
            if($param['status'] === 'play' and $param['exit'] === 'no'){
                $param['timeLeft'] = $timeLeft;
                $param['timeShtamp'] = 0;
                $param['timeOut'] = $timeLeft;
                $param['points'] = 0;
                $param['move'] = null;
            }
        }
        $this->_newPlayers = $players;
        return $this;
    }
    // вспомагательная к playAgain, создает поле для игры
    private function  chooseWhoStart()
    {
        $players = $this->getPlayers();
        $startMoveFigure = $this->getRoomParam()['startMove'];
        while($player = current($players)){
            if($player['figure'] === $startMoveFigure){
                
                $next = next($players);
                $login = ($next) ? $next['name'] : reset($players)['name'];
                break;
            }
            next($players);
        }
        
        $this->_newStartMoveFigure = $players[$login]['figure'];
        $this->_newPlayers[$login]['move'] = true;
        $this->_newPlayers[$login]['timeShtamp'] = $this->time->timeShtamp($this->getRoomParam()['roundTime']);
        return $this;
    }


    public function sendQuery($query, $value)
    {
        // запрос - 2;
        // подтвердить - 1;
        // отказ - "-1";
        $value = (int)$value;
        if(!isset($this->getPlayers()[$this->getLogin()]) 
                or $this->getPlayers()[$this->getLogin()]['exit'] !== 'no'
                or $this->getPlayers()[$this->getLogin()]['status'] !== 'play'){
            return $this;
        }
        if($query === "playAgain"){

            $this->_Data->updateQuery($query,$this->getRoomParam()['busyFigure'][$this->getLogin()], $value)
                        ->setChangeInRoom()
                        ->setFindForOutPlayer()
                        ->updateDB();
            return $this;
        }
        if($this->getRoomParam()['status'] === "end"){
            return $this;
        }
        
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
                $this->_Data->updateQuery($query,$this->getLogin(), $value);
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
        $countPlayers = count($this->getPlayers()); 
        foreach($this->getRoomParam()['queries'] as $query => $players){
            $stack = $this->queryMap(
                    $query, 
                    $players, 
                    $countPlayers+$this->_queries[$query]['num'], 
                    $this->_queries[$query]['function']
                    );
            if(!empty($stack)){
                break;
            }       
        }
        if(empty($stack)){
            return null;
        }
        return $stack;
    }
    // вспомогательная к checkQuery
    private function createQueryResponse($players, $login, $query, $value)
    {
        $stack = array(
            'out' => 0
        );
        if($value === -1){
            $stack['out'] = 1;
            if($this->getLogin() !== $login 
                    and $this->getRoomParam()['queries']['confirm'][$this->getLogin()] !== 1){
                $stack['login'] = $login;
                $stack['query'] = $query;
                $stack['value'] = -1;
            }
        }

        if($value === 2 and $this->getLogin() !== $login and $players[$this->getLogin()] !== 1){
            $stack['login'] = $login;
            $stack['query'] = $query;
            $stack['out'] = 1;
        }
        return $stack;
    }
    
    // вспомогательная к checkQuery, обновляет 
    private function queryMap($query, $players, $num, $func)
    {
        $count = 0;
        $stack = array();
        foreach($players as $login => $value){
            $count += $value;
            $stack  = $this->createQueryResponse($players, $login, $query, $value);
            if($stack['out'] === 1){
                break;
            }
        }
        unset($stack['out']);
        if($num === $count){
            $this->$func();
            return array();
        }
        return $stack;
    }
    // выполняется в checkQuery, обновляет запрос confirm
    private function reflashConfirm()
    {
        $this->_Data->setQuery($this->getRoomParam()['queries'], 0)
                    ->setStandartFindStartGame()
                    ->updateDB();
    }
    // выполняется в checkQuery, устанавливает ничью в игре
    private function setDraw()
    {
        $this->endGame('draw')
             ->_Data->setChangeInRoom()
                    ->setStandartFindStartGame()
                    ->setQuery($this->getRoomParam()['queries'],0)
                    ->updateDB();
    }
}

