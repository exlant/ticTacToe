<?php
namespace core;
use core\db\mongoDB;

class endCore extends errorHandlerCore{
    function shutdown(){
        $error = error_get_last();
        if(is_array($error)){
            $code = $error['type'];
            $errstr = $error['message'];
            $file = $error['file'];
            $line = $error['line'];
            $this->setError($code,$errstr,$file,$line);
        }
    }    
    
}

