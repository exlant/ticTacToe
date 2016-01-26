<?php
namespace core\db;
class mongoDB
{
    const dataBase = 'ticTacToe';

    static protected $connect = null;//соединение mongoConnect
    static protected $db = null;     //база данных
    static public $queries = array();
    static public $countQueries = 0;

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
        if(!self::$connect = new \MongoClient()){
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
}

