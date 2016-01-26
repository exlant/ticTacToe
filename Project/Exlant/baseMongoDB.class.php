<?php
namespace Project\Exlant;
use core\db\mongoDB;

class baseMongoDB extends mongoDB{
    
    const COOLECION = 'users';
    
    function __construct() {
        parent::__construct();
        $this->setCollection(self::COOLECION);
    }
    
    public function getPages($path)
    {
        $find = array('puth' => $path);
        return $this->setCollection('pages')
             ->findOne($find);
    }    
}
