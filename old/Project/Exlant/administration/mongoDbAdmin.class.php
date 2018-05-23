<?php
namespace Project\Exlant\administration;

use core\db\mongoDB;

class mongoDbAdmin
{
    private static $_instance = null; // объект себя
    private $_mongoDb = null;         // объект с оберткой для mongoDB
    
    private function __construct()
    {
        $this->mongoDb();      
    }
    
    static function getInstance()
    {
        if(self::$_instance === null){
            self::$_instance = new mongoDbAdmin();
        }
        return self::$_instance;
    }
    
    private function mongoDb()
    {
        if($this->_mongoDb === null){
            $this->_mongoDb = new mongoDB();
        }
        return $this->_mongoDb;
    }
    
    public function getAllCollections()
    {
        $data = array();
        $cursor = $this->mongoDb()->getDB()->getCollectionNames();
        foreach($cursor as $value){
            $data[] = $value;
        }
        
        return $data;
    }
    
    public function find($collections, $find = array(), $needle = array())
    {
        $cursor = $this->mongoDb()
             ->setCollection($collections)
             ->find($find, $needle);
        $data = array();
        foreach($cursor as $value){
            $data[] = $value;
        }
        return $data;
    }
    
    public function getCollectionKeys($collection)
    {
        $element = $this->mongoDb()
                ->setCollection($collection)
                ->findOne();
        
        if(!$element){
            return false;
        }
        function getKeys($element)
        {
            $data = array();
            foreach($element as $key => $value){
                if($key !== '_id'){
                    if(is_array($value)){
                        $data[$key] = getKeys($value);
                    }else{
                        $data[] = $key;
                    }
                    
                }
            }
            return $data;
        }
        
        return getKeys($element);
    }
    
    public function batchInsert($collection, $array)
    {
        $this->mongoDb()
             ->setCollection($collection)
             ->batchInsert($array);
    }
    
    public function deleteElement($collection, $ids)
    {
        $data = array();
        foreach($ids['_id'] as $id){
            $data[] = new \MongoId($id);
        }
        $this->mongoDb()
             ->setCollection($collection)
             ->remove(array('_id' => array('$in' => $data)));
    }
    
    public function addCollection($collectionName)
    {
        $this->mongoDb()
             ->getDB()
             ->createCollection($collectionName);
    }
    
    public function deleteCollection($collectionName)
    {
        $this->mongoDb()
             ->setCollection($collectionName)
             ->drop();
    }
}


