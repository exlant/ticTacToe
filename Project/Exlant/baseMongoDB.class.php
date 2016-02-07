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
    
    public function getGuestCounter()
    {
        $this->setFind('settingsName', 'questSettings');
        $needle = array('questCounter');
        return $this->setCollection('settings')
                    ->findOne($this->getFind(), $needle)['questCounter'];
    }
    
    public function iterateGuestCounter()
    {
        $this->setUpdate('questCounter', '$inc', 1)
             ->updateDB();
    }
}
