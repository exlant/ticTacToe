<?php
namespace Project\Exlant\administration;

class modelAdmin
{
    private $_db = null; // объект с базой
    private $_collections = null; // коллекции
    
    public function __construct() 
    {
        $this->setDb('mongo');
        $this->getDb()->getAllCollections();
    }
    
    private function setDb($type)
    {
        $db = array(
            'mongo' => 'Project\Exlant\administration\mongoDbAdmin'
            );
        if(isset($db[$type])){
            $this->_db = $db[$type]::getInstance();
            return true;
        }
        return false;
    }
    
    private function getDb()
    {
        return $this->_db;
    }
    
    public function getPages($page = null)
    {
        return $this->getDb()->find('pages',$page);
    }
    
    public function getCollections()
    {
        return $this->getDb()->getAllCollections();
    }
    
    public function getStruct($collection)
    {
        return $this->getDb()->find($collection);
    }
    
    public function getKeys($collection, $type = 'all')
    {
        switch($type){
            case 'all':
                return $this->getDb()->getCollectionKeys($collection);
            case 'first' : 
                $keys = $this->getDb()->getCollectionKeys($collection);
                if(!$keys){
                    return false;
                }
                $data = array();
                foreach($keys as $key => $value){
                    if(is_string($value)){
                        $data[] = $value; 
                    }
                }    
                return $data;
            default : 
                return $this->getDb()->getCollectionKeys($collection);
        }
        
    }
    
    public function find($collection, $find = array(), $needle = array())
    {
        return $this->getDb()->find($collection, $find, $needle);
    }
    
    public function createFind($find, $type = '$in', $id = false)
    {
        $data = array($type => array());
        foreach($find as $key => $value){
            if(is_array($value)){
                $id = ($key === '_id') ? true : false;
                $data[$key] = $this->createFind($value, $type, $id);
            }
            if(is_string($value)){
                if(is_numeric($value)){
                    $value = (int)$value;
                }
                if($id === true){
                    $value = new \MongoId($value);
                }
                array_push($data[$type], $value);
            }
        }
        if(!$data[$type]){
            unset($data[$type]);
        }
        return $data;
    }
    
    protected function deleteElements($collection, $id)
    {
        $this->getDb()->deleteElement($collection, $id);
    }
    
    protected function addNewElements($collection)
    {
        $elements = array();
        for($i = 0; ; $i++){
            if(!isset($_POST[$i])){
                break;
            }
            $elements[] = $_POST[$i];
        }
        $this->getDb()->batchInsert($collection, $elements);
        
    }
    
    protected function addCollection($collectionName)
    {
        $this->getDb()->addCollection($collectionName);
        return $this;
    }
    
    protected function deleteCollection($collectionName)
    {
        $this->getDb()->deleteCollection($collectionName);
        return $this;
    }
    
}

