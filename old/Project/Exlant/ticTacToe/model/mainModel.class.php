<?php
namespace Project\Exlant\ticTacToe\model;
//подключаем mongoDB
use core\db\mongoDB;
use Project\Exlant\ticTacToe\controller\time;

class mainModel extends mongoDB
{
    const collection = 'rooms';
    
    protected $gameType = array('2d','3d');
    protected $fildLength = array('min' => 3, 'max' => 20);
    protected $roundTime = array('min' => 30, 'max' => 300);
    protected $figureInArow = array('min' => 3, 'max' => 6);
    protected $pointsNum = array('min' => 3, 'max' => 20);
    protected $figure = array(
        'cross' => 'Крестик',
        'zero' => 'Нолик',
        'triangle' => 'Треугольник',
        'foursquare' => 'Квадратик',
    );
    protected $players = array('min' => 2, 'max' => 4);
    private $_busyFigure = array();
    private $_startMoveFigure;   // фигура, которая первая начинает ходить
    private $roomsSingle = null; //существующие комнаты, с одним игроком
    public $userBusyInfo = array(
        'action' => null,
        'joiner' => 'empty',
        'roomStatus' => null,
    );
    public $roomSettings = array(); // все параметры комнаты в которой находится пользователь
        
    public function __construct() 
    {
        parent::__construct();
        $this->setCollection(self::collection);
    }
            
    protected function setSingleRooms($login) //достаем из базы созданные комнаты, без создателя
    {
        $find = array(
            'status' => array('$in' => array('created','start','end')),
            'players.'.$login.'.exit' => array('$ne' => 'no')
            );
        $needle = array('_id',
            'creater', 
            'type', 
            'roundTime', 
            'sideLength', 
            'players',
            'numPlayers',
            'joiner', 
            'status',
            'points',
            'pointsNum',
            'blitz',
            'figureInArow',
            );
        $cursor = $this->getCollection()
             ->find($find,$needle);
        
        foreach($cursor as $value){
            $countPlayers = 0;
            array_walk($value['players'], function($item) use(&$countPlayers){
                if($item['exit'] === 'no')
                    $countPlayers++;
            });
            $value['playersIn'] = $countPlayers;
            $blitz = self::convertBlitz($value['blitz']);
            $points = self::convertPoints($value['points'], $value['pointsNum']);
            
            $value['pointsText'] = $points['text'];
            $value['blitzText'] = $blitz['text'];
            unset($value['pointsNum']);
            unset($value['blitz']);
            unset($value['points']);
            unset($value['players']);
            if($value['status'] === 'created' and $value['creater'] !== $login){
                if($value['joiner'] === 'empty'){
                    $value['url'] = '<a href="'.DOMEN.'/'.TICTACTOE.'/enterRoom/'.$value['creater'].'">Зайти</a>';
                }
                if($value['joiner'] === 'full'){
                    $value['url'] = 'Полная';
                }
                if(isset($value['players'][$login])){
                    $value['url'] = '<a href="'.DOMEN.'/'.TICTACTOE.'/exitRoom">Выйти</a>';
                }
                $data[] = $value;
            }else{
                $value['url'] = '<a href="'.DOMEN.'/'.TICTACTOE.'/followGame/'.$value['_id'].'">Следить</a>';
                $data[] = $value;
            }
            
        }
        $this->roomsSingle = (isset($data)) ? $data : null;
        return $this;
    }
    
    public function getSingleRooms() // гетер для созданных комнат
    {
        return $this->roomsSingle;
    }
    
    protected function addRoom($creater) //добавляет комнату в коллекцию rooms, этап создания комнаты, комната существует без характеристик. 
    {
        $room = array(
            'creater' => $creater,      // ник создателя комнаты
            'joiner' => 'empty',        // 'empty' - если есть свободные места, 'full' - места закончились
            'status' => 'creating',     // 'creating', 'created', 'start', 'end'
            'type' => null,             // '2d', '3d'
            'roundTime' => null,        // время на ход
            'sideLength' => null,       // длина стороны поля
            'winner' => null,           // победитель
            'winnerRow' => array(),     // координаты выйгравшей строки
            'timeStart' => time(),        // время начала, временная метка 
            'timeEnd' => null,          // время конца
            'figureInArow' => null,     // количество фигур в ряд для победы
            'points'    => null,        // игра на очки
            'pointsNum' => null,        // yes - игра на очки
            'warnings' => array(),      // предупреждения, массив c (availableCell - координаты клетки, куда нужно походить, что бы снять предупреждение, movies - координаты предупреждениия, add - флаг, если all - то нужно занять все свободные клетки, что бы снять предупреждение)
            'blitz' => null,            // если 'yes' - игра блитс
            'players' => array(         // массив с игроками и зрителями
                $creater => array(  
                    'name' => $creater,     // логин игрока
                    'points' => 0,          // количество очей, если игра на очи
                    'status' => 'play',     // 'play', 'view' - играет, сморит
                    'exit' => 'no',         // если 'yes' - игрок, визетер вышел
                    'timeLeft' => 0,        // сколько осталось времени на ход
                    'timeShtamp' => 0       // временная метка
                    )
            ),
            'numPlayers' => null,       // количество игроков
            'freeFigure' => $this->figure,// не занятые фигуры
            'busyFigure' => null,        // занятые фигуры
            'movies' => array(),    // массив со сделанными ходами,(login, move),
            'change'    => 0,       // были изменения ставим +1, 
            'queries'     => null,
            'freePlace' => array(),  // свободные места, если кто-то вышел из комнаты в игре, его могут заменить
            'startMove' => null,     // фигура, того кто начинает ходить
        );
        $this->getCollection()
             ->insert($room);
    }
    
    protected function createRoom($login,$fildLength,$figure,$numberPlayers,$roundTime, $figureInArow, $points, $pointsNum, $blitz)
    {                                               //добавляются характеристики комнаты
        $find = array(
            'creater' => $login,
            'status' => 'creating'
            );
        $freeFigures = $this->setFreeFigure($figure);
        
        $update = array('$set' => array(
            'status' => 'created',
            'type' => '2d',     // только в 2d
            'sideLength' => $fildLength,
            'figureInArow' => $figureInArow,
            'numPlayers' => $numberPlayers,
            'freeFigure' => $freeFigures,
            'points'    => $points,
            'pointsNum' => $pointsNum,
            'blitz' => $blitz,
            'roundTime' => $roundTime,
            'players' => array(
                $login => array(
                    'name' => $login,
                    'figure' => $figure,
                    'status' => 'play',
                    'exit' => 'no',
                    'timeLeft' => $roundTime,
                    'timeShtamp' => 0,
                    'points' => 0,
                    )
            )
        ));
        $this->getCollection()
             ->update($find,$update);
    }
    
    protected function dropRoom($creater) //удалить созданную комнату
    {
        $find = array(
            'creater' => $creater,
            'status' => array('$in' => array('creating','created'))
            );
        $this->getCollection()
             ->remove($find);
    }
        
    protected function enterRoom($creater,$login) //вход в комнату, добавляем вошедшего игрока в поле players
    {
        $find = array('creater' => $creater,      // находим комнату к которой присоединился игрок
                      'joiner' => 'empty',
                      'status' => 'created'
            );
        $needle = array('players', 'roundTime');  // нужные данные комнаты
        $playersData = $this->getCollection()     // достаем комнату из базы
             ->findOne($find,$needle);
        $playerInput = array($login => array(      // создаем данные вошедшего игрока
                                'name' => $login, 
                                'figure' => 'none',
                                'status' => 'play',
                                'exit' => 'no',
                                'timeLeft' => $playersData['roundTime'],
                                'timeShtamp' => 0,
                                'points' => 0
            
                    ));
        $players = array_merge($playersData['players'], $playerInput);  //объединяем массив с уже существующими игроками с новым игроком
        $update = array('$set' => array(
                   'players' => $players,
                        ));
        $this->getCollection()          //записуем объединенный массив в базу данных
             ->update($find,$update);
        
    }
    
    private function dropPlayer($find,$player)
    {
        $updateStatus = array('$set' => array('joiner' => 'empty')); 
        $updatePlayers = array('$unset' => array('players.'.$player => ''));
        $result = $this->getCollection()
                        ->findOne($find,array('players.'.$player.'.figure'));
        $figure = $result['players'][$player]['figure'];
        if($figure !== 'none'){
            $setFigure = array('$set' => array('freeFigure.'.$figure => $this->figure[$figure]));
            $this->getCollection()
                 ->update($find,$setFigure);
            
        }
        $this->getCollection()
             ->update($find,$updateStatus);
        $this->getCollection()
             ->update($find,$updatePlayers);
    }
    
    protected function exitRoom($login)    // выйти из комнаты
    {
        $find = array(
            'players.'.$login => array('$exists' => TRUE),
            'status' => 'created'
            );
        $this->dropPlayer($find, $login);        
    }
    
    protected function dropOpponent($login,$player)   // выкидует опонента из комнаты
    {
        $find = array(
            'creater' => $login,
            'status' => 'created'
            );
        $this->dropPlayer($find, $player);
    }
    
    protected function checkPlaying($login)         // проверяет не играет ли уже игрок в другой комнате,
    {
        $find = array(
            'players.'.$login.'.exit' => 'no',
            'status' => array('$in' => array('start' , 'end'))
            );
        $needle = array('_id');
        $result = $this->getCollection()
                       ->findOne($find);
        if($result){
            return true;
        }
        return false;
    }

    // создает строчку busyFigure, для занятых фигур
    private function chooseWhoStart($players,$roundTime) //выбираем кто из игроков будет ходить первым
    {
        $time = new time($this->getRoomSettings()['blitz']);
        foreach($players as $key => $value){    // добавляем всем игрокам поле отвечающее за ход
            $players[$key]['move'] = null;
            $this->_busyFigure[$value['name']] = $value['figure'];
        }
        $array_key = array_rand($players);      // выбираем игрока, который будет ходить
        $players[$array_key]['move'] = TRUE;    // передаем ему ход
        $players[$array_key]['timeLeft'] = $roundTime; //ставим время на ход
        $players[$array_key]['timeShtamp'] = $time->timeShtamp($roundTime);
        $this->_startMoveFigure = $players[$array_key]['figure'];
        return $players;
    }
    
    private function createQueriesArray($players)
    {
        $queries = array(
            'confirm',
            'moveBack',
            'draw',
            'playAgain',
        );
        $new = array();
        foreach($queries as $query){
            foreach($players as $player => $val){
                if($query === 'playAgain'){
                    $new[$query][$val['figure']] = 0;
                }else{
                    $new[$query][$player] = 0;
                }
                
            }
        }
        return $new;        
    }
    
    protected function startingGame($login,$players,$gameArray, $roundTime)  //запускаем игру, меняем статус игры на start
    {
        
        $players = $this->chooseWhoStart($players,$roundTime);  // рендомно выбираем, кто из игроков будет ходить первым
        $queries = $this->createQueriesArray($players);
        $find = array(
            'creater' => $login,
            'status' => 'created'
            );                    
        $update = array('$set' => array(
            'status'    => 'start',
            'players'   => $players,
            'gameArray' => $gameArray,
            'timeStart' => time(),
            'busyFigure'=> $this->_busyFigure,
            'queries'   => $queries,
            'startMove' => $this->_startMoveFigure
            ));
        $this->getCollection()
             ->update($find,$update);
    }
    // следить за игрой
    protected function followGame($login, $id)
    {
        $find = array(
                '_id' => new \MongoId($id),
                
            );       
        $viewers = array('name' => $login, 'status' => 'view', 'exit' => 'no');
        $update = array(
            '$set' => array('players.'.$login => $viewers),
            '$inc' => array('change' => 1));
        $this->getCollection()
             ->update($find, $update);
    }

    protected function setUserBusyInfo($key,$value) // сетер для UserBusyInfo
    {
        $this->userBusyInfo[$key] = $value;
        return $this;
    }
    
    public function getUserBusyInfo() // гетер для userBusyInfo
    {
       return $this->userBusyInfo;
    }
    
    protected function setRoomSettings($login)
    {
        $find = array(
            'players.'.$login.'.exit' => 'no',
            );
        
        $this->roomSettings = $this->getCollection()
             ->findOne($find);
        
        return $this;
    }
    
    public function getRoomSettings()
    {
        return $this->roomSettings;
    }
    
    private function checkPlayersToStart($players, $numPlayers)
    {
        $numReadyToGo = 0;
        foreach($players as $value){
            if(isset($value['figure']) and $value['figure'] !== 'none'){
                $numReadyToGo++;
            }
        }
        if($numReadyToGo === (int)$numPlayers){
            return 'ok';
        }
        return 'none';
    }
    
    protected function whetherInRoom($login)  // создает userBusyInfo
    {
        $data = $this->getRoomSettings();
        if(!$data){
          return $this;
        }
        $allReady = $this->checkPlayersToStart($data['players'],$data['numPlayers']);
        
        $action = ($data['creater'] === $login) 
                ? 'creater' : 'joiner';
            
        $joiner = ($data['status'] !== 'creating') 
                ? $this->checkPlayers($login, $data['numPlayers'], $data['players'])
                : 'empty';
        
        $blitz = self::convertBlitz($data['blitz']);
        $points = self::convertPoints($data['points'], $data['pointsNum']);
        
        $this->setUserBusyInfo('action',$action)
             ->setUserBusyInfo('joiner',$joiner)
             ->setUserBusyInfo('roomStatus',$data['status'])
             ->setUserBusyInfo('players', $data['players'])
             ->setUserBusyInfo('creater', $data['creater'])
             ->setUserBusyInfo('type', $data['type'])
             ->setUserBusyInfo('roundTime', $data['roundTime'])
             ->setUserBusyInfo('sideLength', $data['sideLength'])
             ->setUserBusyInfo('numPlayers', $data['numPlayers'])
             ->setUserBusyInfo('freeFigure', $data['freeFigure'])
             ->setUserBusyInfo('readyToGo', $allReady)
             ->setUserBusyInfo('figureInArow', $data['figureInArow'])
             ->setUserBusyInfo('pointsText', $points['text'])
             ->setUserBusyInfo('pointsValue', $points['value'])
             ->setUserBusyInfo('blitzText', $blitz['text'])
             ->setUserBusyInfo('blitzValue', $blitz['value'])
             ->setUserBusyInfo('busyFigure', $data['busyFigure']);
 
        return $this;
    }
    
    private function checkPlayers($login, $numPlayers,$players)  //проверяет все ли игроки зашли в комнату, если все ставит статус (full)
    {
        
        $countPlayers = count($players);
        $numPlayers = (int)$numPlayers;
        if($numPlayers === $countPlayers){
            $find = array(
                'players.'.$login => array('$exists' => true),
                'status' => 'created'
                );
            $update = array('$set' => array('joiner' => 'full'));
            $this->getCollection()
                 ->update($find,$update);
            return 'full';
        }
        $find = array(
            'creater' => $login,
            'status' => 'created'
            );
            $update = array('$set' => array('joiner' => 'empty'));
            $this->getCollection()
                 ->update($find,$update);
        return 'empty';
    }
    
    protected  function setFigure($login,$figure)
    {
        $find = array(
            'status' => 'created',
            'players.'.$login => array('$exists' => TRUE),
            'players.'.$login.'figure' => array('$ne' => 'none')
        );
        $updateFreeFigure = array('$unset' => array('freeFigure.'.$figure => ''));
        $updatePlayersFigure = array('$set' => array('players.'.$login.'.figure' => $figure));
        $this->getCollection()
               ->update($find,$updateFreeFigure);
        $this->getCollection()
             ->update($find,$updatePlayersFigure);
    }

    private function setFreeFigure($figure)
    {
        $figures = $this->figure;
        unset($figures[$figure]);
        return $figures;
    }
    
    public static function convertBlitz($value)
    {
        return array(
            'text' => ($value === 'yes') ? 'Да' : 'Нет',
            'value' => $value,
        );
    }
    
    public static function convertPoints($value, $num)
    {
        return array(
            'text' => ($value === 'yes') ? $num : 'Нет',
            'value' => $value
        );
    }
    
    
}