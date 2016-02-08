<?php
require_once 'patch.constant.php';
use core\startCore;
use core\errorHandlerCore;
// файл должен иметь разширение .class.php
class spl_autoload   // класс автозагрузки классов по вызову, подгружаются и родительские классы
{                         

    static function autoload($class_name) // метод, который запускается при создании нового объекта
    {   
        // Don't interfere with other autoloaders
        if (0 === strpos($class_name, 'Swift_')) {
            return;
        }
        $filename = self::loadpatch($class_name);
                
        if(file_exists($filename)){ //если файл с классом существует инклудим его 
            require_once $filename;
            return TRUE;
        }
        trigger_error('Класс '.$class_name.' не найден по пути: '.$filename, E_USER_ERROR); //если не найден FATAL ERROR и выход из скрипта
        return NULL;
    }
    
    static function loadpatch($class_name)
    {             
        $class_name = str_replace('\\','/',$class_name);	
	$stack = explode(DOMEN_PATCH,__DIR__);        // создаем абсоютный путь к папке с сайтом, константа DOMEN_PATCH содержит название папки с сайтом 
	$patch = $stack[0].DOMEN_PATCH.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.$class_name.'.class.php'; // склеиваем путь к файлу!
        return $patch;
    }
}
spl_autoload_register(array('spl_autoload','autoload'));
$errorHandler = new errorHandlerCore(); //инициализируем обработчик ошибок
$ajax = (isset($ajax)) ? $ajax : 0;
new startCore($errorHandler, $ajax);           // запускаем startCore, и передаем туда обработчик ошибок
