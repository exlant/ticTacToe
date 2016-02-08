<?php
$slash = DIRECTORY_SEPARATOR;
define('DOMEN', 'http://'.$_SERVER['HTTP_HOST']);
define('JUSTDOMEN', 'tictactoe.pp.ua');
define('DOMEN_PATCH','tictactoe.pp.ua');           // константа папка домена сайта
define('VIEW','Project'.$slash.'Exlant'.$slash.'view'.$slash); //путь к view

// routes
define('TICTACTOE', 'tictactoe');        //route к модулю крестики нолики
define('USERS', 'users');                //route к модулю пользователи
define('SENDMESSAGE', 'sendMessage');    //route к модулю отправить сообщение


