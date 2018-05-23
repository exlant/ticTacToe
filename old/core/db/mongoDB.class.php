<?php

namespace core\db;

class mongoDB
{
    const dataBase = 'ticTacToe';

    static protected $connect;//соединение mongoConnect
    static protected $db = null;     //база данных
    static public $queries = array();
    static public $countQueries = 0;

    protected $_find = array();    // стэк для поиска
    protected $_update = array();   // стэк для изменений, которые буду потом записаны в бд
    private $collection = null; //коллекция(таблица);
    private $collectionName = null; // имя коллекции

    public function __construct()
    {
        if(!self::$connect){
            $this->connect();
            $this->setDB(self::dataBase);
        }
    }
    private function connect()
    {
        self::$connect = new \MongoClient('mongodb://localhost:27017');
        if (!self::$connect) {
            trigger_error('Can\'t connect to MongoDB', E_USER_ERROR);
        }
    }

    public function setDB($db)
    {
        self::$db = self::$connect->$db;
        return $this;
    }

    public function getDB()
    {
        return self::$db;
    }

    public function setCollection($collection)
    {
        $this->collectionName = $collection;
        $this->collection = self::$db->$collection;
        return $this->collection;
    }

    public function find($find, $needle = array())
    {
        $push = array(
            'type' => 'find',
            'collection' => $this->collectionName,
            'find' => $find,
            'needle' => $needle
        );
        array_push(self::$queries, $push);
        return $this->collection->find($find, $needle);
    }

    public function findOne($find, $needle = array())
    {
        $push = array(
            'type' => 'findOne',
            'collection' => $this->collectionName,
            'find' => $find,
            'needle' => $needle
        );
        array_push(self::$queries, $push);
        return $this->collection->findOne($find, $needle);
    }

    public function update($find, $update)
    {
        $push = array(
            'type' => 'update',
            'collection' => $this->collectionName,
            'find' => $find,
            'update' => $update
        );
        array_push(self::$queries, $push);
        return $this->collection->update($find, $update);
    }

    public function updateDB()  //обновляет базу
    {
        if($this->getFind() and $this->getUpdate()){
            $this->getCollection()
                 ->update($this->getFind(), $this->getUpdate());
        }
        $this->_update = array();
        $this->_find = array();
        return $this;
    }

    public function insert($insert)
    {
        $push = array(
            'type' => 'insert',
            'collection' => $this->collectionName,
            'insert' => $insert
        );
        array_push(self::$queries, $push);
        return $this->collection->insert($insert);
    }

    public function remove($find)
    {
        $push = array(
            'type' => 'remove',
            'collection' => $this->collectionName,
            'find' => $find
        );
        array_push(self::$queries, $push);
        return $this->collection->remove($find);
    }

    public function getCollection()
    {
        self::$countQueries++;
        return $this;
    }

    public function getUpdate()
    {
        return $this->_update;
    }

    public function setUpdate($key, $type, $value)
    {
        $array = $this->getUpdate();
        if(!isset($array[$type][$key])){
            $this->_update[$type][$key] = $value;
        }
        return $this;
    }

    public function getFind()
    {
        return $this->_find;
    }

    public function setFind($key, $value)
    {
        $this->_find[$key] = $value;
        return $this;
    }

    public function dropCollection()
    {
        if($this->getCollection()){
            return $this->getCollection()->drop(); //array 'ns' => string 'DBname.CollectionName' (length=22)
                                                   //'nIndexesWas' => int 1
                                                   // 'ok' => float 1

                                                   //or

                                                    //array (size=3)
                                                    //'ok' => float 0
                                                    //'errmsg' => string 'ns not found' (length=12)
                                                    // 'code' => int 26
        }
        return FALSE;
    }
    public static function getQueryStatistics()
    {
        return array(
            'count' => self::$countQueries,
            'query' => self::$queries
        );
    }

    public function testCollection($array)
    {
        $this->setCollection('testCollection')
             ->insert($array);
        return $this;
    }
}