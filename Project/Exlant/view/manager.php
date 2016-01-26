<?php
    use core\startCore;
    $manager = startCore::$objects['manager'];
?>
<div class="wrapper">
    <div class="greeting">
        <b><?=startCore::$authorization->userData['nick'] ?></b>, добро пожаловать в админку,   
    </div>
    <div class="leftContainer">
        <div class="autorizationPanel">
            <div class="title">Меню</div>
            <?php
                $manager->getMenu();
            ?>    
        </div>
    </div>
    <div class="centerContainer">
        <?php
            if($manager->getRoute() === 'allCollections'){
                if($manager->getAction() === null){
                    $allCollections = $manager->getCollections();
                    echo '<div class="mainInfo">Коллекции(таблицы):</div>'
                        . '<form action="'.DOMEN.'/allCollections/addCollection" method="post">'
                        . '<input class="addCollection" type="text" name="collectionName" placeholder="имя коллекции">'
                        . '<input class="addCollection" type="submit" value="Добавить">'
                        . '</form>';
                    foreach($allCollections as $value){
                        echo '<div class="collections">'
                            . '<div class="title">'.$value.'</div>'
                            . '<div class="actions">'
                                . ' <a href="'.DOMEN.'/allCollections/edit/'.$value.'/">Редактировать</a>'
                                . ' <a href="'.DOMEN.'/allCollections/addNewEntry/'.$value.'">Добавить новую запись</a>'
                                . ' <a href="'.DOMEN.'/allCollections/view/'.$value.'/">Просмотреть</a>'
                                . ' <a href="'.DOMEN.'/allCollections/deleteCollection/'.$value.'/">Удалить</a>'
                            . '</div>'
                        . '</div>';
                    }
                }else{
                    $html = '<div class="url">'
                        . '<a href="'.DOMEN.'/allCollections/">К выбору коллекции(таблицы)</a><br>'
                        . '</div>';
                    if($manager->getAction() === 'view' and $manager->getProperty()){
                        $struct = $manager->getStruct($manager->getProperty());
                        echo $manager->addHeader('Просмотр всех элементов коллекции', $html)
                            .$errorHandler->viewStruct($struct);
                    }
                    
                    if($manager->getAction() === 'addNewEntry' and $manager->getProperty()){
                        $keys = $manager->getKeys($manager->getProperty());
                        echo $manager->addHeader('Добавление нового элемента(ов)', $html)
                            . '<form class="entriesCount" action="" method="POST">'
                                . 'Количество записей: <input type="number" name="entriesCount" min="1" max="10" value="1">'
                                . ' <input type="submit" value="Выбрать">'
                            .'</form>'
                            .$manager->htmlFormAddNewElement($keys);
                    }
                    
                    if($manager->getAction() === 'edit' and $manager->getProperty()){
                        if(!$manager->getElementsId() or !$manager->getActionOnElements()){
                            $keys = $manager->getKeys($manager->getProperty(), 'first');
                            echo $manager->addHeader('Редактирование коллекции - '.$manager->getProperty(), $html);
                            if($keys){
                                $mainKey = ($manager->getMainKey()) ? $manager->getMainKey() : $keys[0];
                                $elements = $manager->find($manager->getProperty(), array(), array($mainKey));
                            echo '<form class="selectKeys" action="" method="POST">'
                                    . 'Выбрать ключ - <select name="mainKey">';
                                    foreach($keys as $value){
                                        echo '<option value="'.$value.'">'.$value.'</option>';
                                    }
                                    echo '</select> '
                                    . '<input type="submit" value="Выбрать">'
                                . '</form>';
                            echo '<form class="selectKeys" action="" method="POST">'
                                    . '<div class="title">Выберите элементы для редактирования:</div>';
                            foreach($elements as $value){
                                $key = (isset($value[$mainKey])) ? $mainKey : "_id";
                                echo '<label class="checkbox"><input type="checkbox" name="elementsId[_id][]" value="'.$value['_id'].'">'.$value[$key].'</label>';
                            }
                            echo '<div class="selectKeys">'
                            . '<label><input type="radio" name="actionOnElements" value="edit" checked="checked"> Редактировать</label>'
                            . ' | <label><input type="radio" name="actionOnElements" value="delete">Удалить</label>'
                            . '</div>'
                            . '<input class="submit" type="submit" value="Выбрать элементы">'
                            . '</form>';
                            }else{
                                echo 'В коллекции нет элементов!';
                            }                       
                        }elseif($manager->getActionOnElements() === 'edit'){
                            $html .= '<div class="url">'
                            . '<a href="'.DOMEN.'/allCollections/edit/'.$manager->getProperty().'">К коллекции - '.$manager->getProperty().'</a>'
                            . '</div>';
                            $find = $manager->createFind($manager->getElementsId());
                            $elements = $manager->find($manager->getProperty(), $find);
                            $manager->setEntriesCount(count($elements));
                            echo $manager->addHeader('Редактирование элементов', $html);
                            echo $manager->htmlForEditElement($elements);
                            
                        }
                        
                    }
                }  
            }
            if($manager->getRoute() === 'testing'){
                 echo '<div class="mainInfo">Добро пожаловать в тестирование:</div>';
            }
        ?>
    </div>
</div>