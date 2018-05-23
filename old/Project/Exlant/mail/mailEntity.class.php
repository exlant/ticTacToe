<?php

namespace Project\Exlant\mail;

class mailEntity
{
    private $_body = '';                     // текст сообщения (string)
    private $_subject = '';                  // тема сообщения  (string)
    private $_mailCallBack = '';             // обратная почта  (string)
    private $_settingId = 'exlantSt';        // идентификатор параметров почты  (string)
    private $_config = array();              // массив с параметрами   (array)
    private $_name = '';                     // Логин отправителя (string)
    
    public function __construct() 
    {
        
    }
    
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function setBody($body)
    {
        $this->_body = $body;
        return $this;
    }
    public function getBody()
    {
        return $this->_body;
    }
    public function getMergeBody()
    {
        return $this->_body
            ."\n\r\n\r"
            . 'Логин отправителя: '.$this->getName()
            ."\n\r"
            . 'Обратная почта отправителя: '.$this->getMailCallBack();
    }
    
    public function setSubject($subject)
    {
        $this->_subject = $subject;
        return $this;
    }
    public function getSubject()
    {
        return $this->_subject;
    }
    
    public function setMailCallBack($mailCallBack)
    {
        $this->_mailCallBack = $mailCallBack;
        return $this;
    }
    public function getMailCallBack()
    {
        return $this->_mailCallBack;
    }
    
    public function setSettingId($settingId)
    {
        $this->_settingId = $settingId;
        return $this;
    }
    public function getSettingId()
    {
        return $this->_settingId;
    }
    
    public function setConfig()
    {
        $xmlstring =  file_get_contents(dirname(__FILE__).'/config.xml');
        $xml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        $this->_config = $array[$this->getSettingId()];
        return $this;
    }
    public function getConfig()
    {
        return $this->_config;
    }
    
}

