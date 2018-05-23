<?php
namespace core;

use Project\Exlant\registration\controller\authorizationController;
use Project\Exlant\controller;

class startCore
{
    private static $start = 0;  //счетчик для избежания повторного запуска конструктора
    private static $CSS = array(); //контейнер для подключаемых стилей
    private static $JS = array(); //контейнер для подключаемых js-файлов

    public static $ticTacToe = null;
    public static $authorization = null;
    public static $controller = null;

    public static $objects = array();


    public function __construct($errorHandler, $ajax){
        if(self::$start === 0){
            self::$start++; //повышаем счетчик +1
            //запускаем функцию ob_start
            //ob_start(array('core\startCore','method_call'));

            //запускаем сессию
            //session_destroy();
            session_start([
                'cookie_lifetime' => 86400,
                'use_strict_mode' => 1,
            ]);
            // проверяем включены ли cookie
            $this->checkCookie();
            //выставляем кодировку utf8
            header('Content-Type: text/html; charset=utf-8');
            //отключаем кеширование
            header('Cache-Control: no-cache, no-store');
            //задаем временную зону
            date_default_timezone_set('Europe/Kiev');
            //выключаем вывод ошибок
            //ini_set('display_errors', 'off');
	    error_reporting(E_ALL);
            //устанавливаем обработчик ошибок
            set_error_handler(array($errorHandler, 'setError'));
            //устанавливаем метод страбатываемый при завершении скрипта
            register_shutdown_function(array(new endCore(), 'shutdown'));
            //подключаем классы
            $this->setInstance($ajax);
        }
    }
    //метод для работы с буффером
    public function method_call($buffer){
        //возвращаем буффер
        return $buffer;
    }

    private function checkCookie()
    {
        if(filter_input(INPUT_GET, 'cookie') === 'check'){
            if(filter_input(INPUT_COOKIE, 'testCookie') == 1){
                header('Location: '.DOMEN);
            }else{
                die('Для работы сайта включите cookie!<br>'
                . 'И перейдите по ссылке: <a href="'.DOMEN.'">'.DOMEN.'</a>');
            }
        }
        if(!filter_input(INPUT_COOKIE, 'PHPSESSID')){
            setcookie('testCookie', '1', time()+60, '/', JUSTDOMEN);
            header('Location: '.DOMEN.'?cookie=check');
        }
    }

    private function setInstance($ajax)
    {
        if($ajax === 0){
            self::$authorization = new authorizationController();
            self::$controller = new controller();
        }
    }

    public static function setObject($key,$object)
    {
        self::$objects[$key] = $object;
    }


    public static function setCSS($nameCSS)
    {
        self::$CSS[] = '<link rel="stylesheet" type="text/css" href="'
                .DOMEN.'/view/css/'.$nameCSS
                . '">';
    }
    public static function getCSS()
    {
        $str = '';
        if(self::$CSS){
            foreach(self::$CSS as $value){
                $str .= $value."\n\r";
            }
        }
        return $str;
    }

    public static function setJS($nameJS)
    {
        self::$JS[] = '<script type="text/javascript" src="'
                .DOMEN.'/view/js/'.$nameJS
                . '"></script>';
    }
    public static function getJS()
    {
        $str = '';
        if(self::$JS){
            foreach(self::$JS as $value){
                $str .= $value."\n\r";
            }
        }
        return $str;
    }
}

