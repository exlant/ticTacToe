<?php
namespace Project\Exlant;

use Project\Exlant\baseMongoDB;
use core\startCore;

class model
{
    private $_Data = null; // объект с базой данных
    
    
    function __construct() {
        if($this->getData() === null){
            $this->setData(new baseMongoDB());
        }
    }
    
    private function setData($instance)   // сетер для объекта с mongodb
    {
        $this->_Data = $instance;
        return $this;
    }
    
    private function getData()          // гетер для объекта с mongodb
    {
        return $this->_Data;
    }
    
    protected function setPageParams($path)
    {
        $pageParams = $this->getData()->getPages($path);
        startCore::setObject('pageParams', $pageParams);        
    }
    
    protected function getGuestCounter()
    {
        $counter = $this->getData()->getGuestCounter();
        $this->getData()->iterateGuestCounter();
        return $counter;
    }
}

