<?php

$time = time() - 60 * 100; // Время бездействия пользователя, что бы оставаться online

$find = array('time' => array(
    '$lte' => $time,
));

$connect = new \MongoClient;
$collection = $connect->selectDB('ticTacToe')
        ->selectCollection('usersOnline')
        ->remove($find);




