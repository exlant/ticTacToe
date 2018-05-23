<?php

$ajax = 1;
require __DIR__ . '/vendor/autoload.php';
require_once 'core' . DIRECTORY_SEPARATOR . 'autoload.class.php';

use core\startCore;
use core\db\mongoDB;
use Project\Exlant\view\view;
use Project\Exlant\ticTacToe\controller\mainController as tictactoePlayGame;
use Project\Exlant\users\usersModel;

class ajax extends mongoDB
{
    private $_idC = null;  // id переданная через cookie
    private $_hashC = null;// хеш переданный через cookie
    private $_actionP = null; // выплняемое действие
    private $_objectP = null; // объект над которым будет выполняться действие
    private $_collectionP = null; // имя коллекции
    private $_columNameP = null;// имя столбца post
    private $_elementIdP = null; // id элемента в коллекции (post)
    private $_cellValueP = null; // значение ячейки (post)
    private $_cellTypeP = null;  // тип ячейки (string/array)
    private $_columDefaultP = null; // значение столбца по умолчанию
    private $_managerS = array(); // сессия для админки
    private $_accessP = null;      // если 1 значит запрос от пользователя
    private $_query = null;        // запрос пользователя на ход назад, ничью, сыграть заново
    private $_value = null;
    private $_key = null;
    private $_change = 0;       // определяет обновились ли данные в игре

    public function __construct()
    {
        parent::__construct();
        $this->setCGPS();
        $array = ($_GET) ? $_GET : $_POST;
        if(isset($array['login'])){
            $this->searchDublicateNick($array['login']);
            return true;
        }
        if(isset($array['captcha'])){
            $this->checkCaptcha($array['captcha']);
            return true;
        }

        $this->protectAccess();

    }

    private function protectAccess()
    {
        if($this->getIdC() and $this->getHashC()){
            //если id и хеш найдены в базе, то авторизируем пользователя
            $find = array(
                '_id' => new \MongoId($this->getIdC()),
                'hash' => $this->getHashC(),
                'visibility' => 1,
            );
            if($this->getAccessP() === '1'){
                $needle = array('_id', 'nick');
                $user = $this->setCollection('users')->findOne($find,$needle);
                $this->userFunction($user['nick']);
                return true;
            }
            if($this->getManagerS()['access'] === 'manager'){
                $needle = array('_id');

                //если id и хеш найдены в базе админки, разрешаем действие
                $user = $this->setCollection('manager')->findOne($find,$needle);

                if($user){
                    $this->protectedFunctions();
                }
            }
            return true;
        }
        return false;
    }

    private function userFunction($login)
    {
        if($this->getObjectP() === 'tictactoe'){
            if($this->getActionP() === 'updateRoomsPage'){
                $this->updateRoomsPage($login);
            }

            if($this->getActionP() === 'updatePlayData'){
                $this->updatePlayData($login);
            }
            if($this->getActionP() === 'sendQuery'){
                $this->sendQuery($login);
            }
            if($this->getActionP() === 'checkQuery'){
                $this->checkQuery($login);
            }
            if($this->getActionP() === 'exitFromGame'){
                $this->exitFromGame($login);
            }
            if($this->getActionP() === 'playerMove'){
                $this->playerMove($login);
            }
            if($this->getActionP() === 'takePlace'){
                $this->takePlace($login);
            }
        }
        if($this->getObjectP() === 'editProfile'){
            if($this->getActionP() === 'editData'){
                $this->editData($login);
            }
        }
        if($this->getObjectP() === 'main'){
            if($this->getActionP() === 'updateUsersOnline'){
                $this->updateUsersOnline();
            }
        }
    }

    private function protectedFunctions()
    {
        if($this->getObjectP() === 'cell'){
            if($this->getActionP() === 'add'){
                $this->addColumn();
            }
            if($this->getActionP() === 'delete'){
                $this->deleteColumn();
            }
            if($this->getActionP() === 'toArray'){
                $this->toArray();
            }
            if($this->getActionP() === 'edit'){
                $this->editCellValue();
            }
        }

    }
    // обновление игрового поля крестики-нолики
    private function updatePlayData($login)
    {
        $data = array();
        new tictactoePlayGame($login);
        $roomParams = startCore::$objects['playGame']->getRoomParam();      // параметры комнаты
        $players = startCore::$objects['playGame']->getPlayers();           // массив с игроками                   (array)
        $movingPlayer = startCore::$objects['playGame']->getMovingPlayer(); // игрок, который сейчас ходит (login) (string)
        $winner = startCore::$objects['playGame']->getWinner();
        if($roomParams['change'] !== $this->getChange()){
            $lastMove = startCore::$objects['playGame']->getLastMove();           // последний ход                   (array)
            $viewers = startCore::$objects['playGame']->getViewers();           // массив со зрителями                 (array)
            $warnings = startCore::$objects['playGame']->getWarnings();         // массив с предупреждениями
            $winnerSide = startCore::$objects['playGame']->getWinnerRow();
            $gameArray = startCore::$objects['playGame']->getGameArray();       // игровое поле                         (array)
            $newplayers = array_merge($players, $roomParams['freePlace']);
            $data['field'] = view::field2d($login, $gameArray, $lastMove, $movingPlayer, $warnings, $winnerSide);
            $data['users'] = view::viewRoomsUsers($newplayers, $viewers, $roomParams['points'], $login, $roomParams['status']);
            $data['change'] = $roomParams['change'];
        }
        if($roomParams['status'] === 'start'){
            $data['time'] = $players[$movingPlayer]['timeOut'];
        }
        $data['winner'] = ($winner) ? $winner : '';

        $data['queries'] = startCore::$objects['playGame']->checkQuery($login); // запросы
        echo json_encode($data);
    }

    private function exitFromGame($login)
    {
        new tictactoePlayGame($login);
        startCore::$objects['playGame']->exitFromGame($login, $this->getValue());
        echo true;
    }

    private function playerMove($login)
    {
        new tictactoePlayGame($login);
        $object = json_decode($this->getValue());
        startCore::$objects['playGame']->setPlayerMove(array(
            'y' =>(int)$object->sidey,
            'x' =>(int)$object->sidex
        ));
        echo true;
    }

    private function takePlace($login)
    {
        new tictactoePlayGame($login);
        startCore::$objects['playGame']->takePlace($this->getValue());
    }

    // обновление пользователей онлайн
    private function updateUsersOnline()
    {
        $find = array('online' => 1);
        $needle = array('nick');
        $data = array();
        $cursor = $this->getCollection()
                          ->find($find, $needle);
        foreach($cursor as $value){
            $data[] = $value;
        }
        echo view::usersOnline($data);
    }
    // обновление комнат и созданние комнаты в режиме ожидания других игроков
    private function updateRoomsPage($login)
    {
        $tictactoe = new tictactoePlayGame($login);
        $data = array();
        $userBusyInfo = $tictactoe->getUserBusyInfo();
        $data['rooms'] = view::viewRooms($tictactoe->getSingleRooms());
        $data['roomsHash'] = md5($data['rooms']);
        $data['status'] = $userBusyInfo['roomStatus'];
        if($userBusyInfo['roomStatus'] === 'created'){
            $data['addPlayers'] = view::addPlayers(
                    $userBusyInfo['players'],
                    $userBusyInfo['freeFigure'],
                    $login,
                    $userBusyInfo['action']);
            $data['addPlayersHash'] = md5($data['addPlayers']);
            $data['readyTogo'] = ($userBusyInfo['creater'] === $login)
                    ? $userBusyInfo['readyToGo']
                    : 'notCreater';
        }
        echo json_encode($data);
    }
    // запросы на ход назад, ничью, сдаться,
    private function sendQuery($login)
    {
        new tictactoePlayGame($login);
        startCore::$objects['playGame']->sendQuery($this->getQuery(), $this->getValue());
    }

    // редактирование профиля
    private function editData($login)
    {
        $model = new usersModel();
        if(!$model->setNewData($login, $this->getKey(), $this->getValue()))
        {
            echo 'danied';
            return;
        }
        echo 'Ok';
    }

    // админ. редактирует строку
    private function editCellValue()
    {
        if(!$this->getColumNameP() or !$this->_collectionP or !$this->getElementIdP()){
            return false;
        }
        $modifier = ($this->getCellTypeP() === 'string') ? '$set' : '$rename';
        $value = (is_numeric($this->getCellValueP())) ? (int)$this->getCellValueP() : $this->getCellValueP();

        $find = array('_id' => new \MongoId($this->getElementIdP()));
        $update[$modifier] = array($this->getColumNameP() => $value);
        $this->setCollection($this->_collectionP)
             ->update($find, $update);
    }
    // админ. конвертирует строку  с id элементов в массив id елементов
    private function convertToFindArray($idString)
    {
        $find = array();
        if($idString){
            $ids = explode(',',$idString);
            foreach($ids as $value){
                $find['_id']['$in'][] = new \MongoId($value);
            }
        }
        return $find;
    }
    // админ. добавление строки в базе
    private function addColumn()
    {

        if(!$this->getColumNameP() or !$this->_collectionP){
            return false;
        }
        $find = $this->convertToFindArray($this->getElementIdP());
        $update['$set'] = array($this->getColumNameP() => $this->getColumDefaultP());
        $parameters = array('multiple' => true);
        $this->setCollection($this->_collectionP)
             ->update($find,$update, $parameters);

    }
    // админ. удаление строки в базе
    private function deleteColumn()
    {
        if(!$this->getColumNameP() or !$this->_collectionP){
            return false;
        }
        $find = $this->convertToFindArray($this->getElementIdP());
        $update['$unset'] = array($this->getColumNameP() => '');
        $parameters = array('multiple' => true);
        $this->setCollection($this->_collectionP)
            ->update($find,$update,$parameters);
    }
    // админ. перевод строки в массив
    private function toArray()
    {
        if(!$this->getColumNameP() or !$this->_collectionP){
            return false;
        }
        $find = $this->convertToFindArray($this->getElementIdP());
        $update['$set'] = array($this->getColumNameP() => new ArrayObject);
        $parameters = array('multiple' => true);
        $this->setCollection($this->_collectionP)
            ->update($find,$update,$parameters);
    }
    // проверка на существование логина при регистрации
    private function searchDublicateNick($login)
    {
        $search = array('nick' => $login);
        $needle = array('$id');
        echo (!$this->setCollection('users')->findOne($search, $needle)) ? 'Ok' : 'danied';
    }
    // проверка каптчи
    private function checkCaptcha($captcha)
    {
        echo ($captcha === $_SESSION['captcha']['code']) ? 'Ok' : 'danied';
    }

    private function setCGPS()
    {
        $this->_idC = filter_input(INPUT_COOKIE, 'string');
        $this->_hashC = filter_input(INPUT_COOKIE, 'hash');
        $this->_accessP = filter_input(INPUT_POST, 'access');
        $this->_actionP = filter_input(INPUT_POST, 'action');
        $this->_objectP = filter_input(INPUT_POST, 'object');
        $this->_elementIdP = filter_input(INPUT_POST, 'elementID');
        $this->_query = filter_input(INPUT_POST, 'query');
        $this->_key = filter_input(INPUT_POST, 'key');
        $this->_value = filter_input(INPUT_POST, 'value');
        $this->_change = filter_input(INPUT_POST, 'change');
        $this->_cellValueP = filter_input(INPUT_POST, 'cellValue');
        $this->_collectionP = filter_input(INPUT_POST, 'collection');
        $this->_columNameP = filter_input(INPUT_POST, 'columsName');
        $this->_cellTypeP = filter_input(INPUT_POST, 'cellType');
        $this->_columDefaultP = filter_input(INPUT_POST, 'columsDefault');
        if(isset($_SESSION['manager']['access'])){
            $this->_managerS['access'] = $_SESSION['manager']['access'];
        }
    }

    private function getAccessP()
    {
        return $this->_accessP;
    }

    private function getActionP()
    {
        return $this->_actionP;
    }

    private function getObjectP()
    {
        return $this->_objectP;
    }

    private function getIdC()
    {
        return $this->_idC;
    }

    private function getHashC()
    {
        return $this->_hashC;
    }

    private function getElementIdP()
    {
        return $this->_elementIdP;
    }

    private function getCellValueP()
    {
        return $this->_cellValueP;
    }

    private function getCellTypeP()
    {
        return $this->_cellTypeP;
    }

    private function getColumNameP()
    {
        return $this->_columNameP;
    }

    private function getColumDefaultP()
    {
        return $this->_columDefaultP;
    }

    private function getManagerS()
    {
        return $this->_managerS;
    }

    private function getQuery()
    {
        return $this->_query;
    }

    private function getValue()
    {
        return $this->_value;
    }

    private function getChange()
    {
        return (int)$this->_change;
    }

    private function getKey()
    {
        return $this->_key;
    }
}
$ajax = new ajax();