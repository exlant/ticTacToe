<?php
require_once '/var/www/tictactoe.pp.ua/public/core/db/mongoDB.class.php';
use core\db\mongoDB;

class autoRemove
{
    private $_timeOnLine = 3600; // время бездействия пользователя, что бы оставаться онлайн
    private $_roomLive = 600;    // Жизнь комнаты после окончания игры, если игроки и зрители не все вышли 
    private $_Data = null;       // Объект с базой даных
    
    public function __construct()
    {
        $this->_Data = new mongoDB();       
    }
    
    public function inspectRooms()
    {
        $this->_Data->setCollection('rooms');
       
        foreach($this->getRooms() as $room){
             var_dump($room);
            $time = ($room['status'] === 'end') ? $room['timeEnd'] : $room['timeStart'];
            if($this->checkRoomEndTime($time) 
                    or $this->checkRoomPlayerOn($room['players'])){
                $this->removeRome($room['_id'])
                     ->recordRoomToPlayedGame($room);
            }
        }
    }
    
    public function inspectUsersOnline()
    {
        $find = array(
            'timeOnline' => array('$lt' => time() - $this->_timeOnLine),
            'online' => 1
            );
        $update = array('$set' => array(
            'online' => 0
        ));
        $this->_Data
                ->setCollection('users')
                ->update($find, $update, array('multiple' => true));
    }
    // достает из базы комнаты сыгранные и на этапе создания
    private function getRooms()
    {
        $find = array(
            'status' => array(
                '$ne' => 'start'
                )
            );
        return $this->_Data
                ->getCollection()
                ->find($find);        
    }
    // проверяет время бездействующих комнат
    private function checkRoomEndTime($time)
    {
        if($time + $this->_roomLive < time()){
            return true;
        }
        return false;
    }
    // проверяет, есть ли в комнате игроки или зрители
    private function checkRoomPlayerOn($players)
    {
        foreach ($players as $player){
            if($player['exit'] === 'no'){
                return false;
            }
        }
        return true;
    }
    // удаляет комнату по id
    private function removeRome($id)
    {
        $this->_Data
                ->getCollection()
                ->remove(array('_id' => $id));
        return $this;
    }
    // записует комнату в gamePlayed для статистики
    private function recordRoomToPlayedGame($room)
    {
        unset($room['_id']);
        $this->_Data->setCollection('gamePlayed')
             ->insert($room);
        $this->_Data->setCollection('rooms');
        return $this;
    }
}

$autoRemove = new autoRemove();
$autoRemove->inspectRooms();
$autoRemove->inspectUsersOnline();