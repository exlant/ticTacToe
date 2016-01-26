<?php
namespace Project\Exlant\administration;

use Project\Exlant\administration\modelAdmin;

class controllerAdmin extends modelAdmin
{
    // массив с меню
    private $_menu = array(
        'allCollections' => 'Коллекции',
        'testing' => 'Тестирование',
    );
    private $_mainPage = 'allCollections';
    
    private $_route = null;   // путь
    private $_action = null;  // действие
    private $_property = null;// свойства
    private $_mainKey = null; // названия ключа, для выбора элементов при редактировании коллекции
    private $_elementsId = null; // параметры для поиска элемента в коллекции(таблице)
    private $_actionOnElements = null;     // действие над элементом
    private $_entriesCount = 1;            // количество добавляемых записей
    private $_collectionName = null;    // имя коллекции
    
    public function __construct()
    {
        parent::__construct();
        $this->setGPS();
        $this->methodGet();
    }
    
    private function methodGet()
    {
        if($this->getRoute() === 'allCollections'){
            if($this->getActionOnElements() === 'delete'){
                $this->deleteElements($this->getProperty(),$this->getElementsId());
                header('location: '.DOMEN.'/allCollections/edit/'.$this->getProperty());
            }
            if($this->getActionOnElements() === 'addNewElements'){
                $this->addNewElements($this->getProperty());
                header('location: '.DOMEN.'/allCollections/addNewEntry/'.$this->getProperty());
            }
            if($this->getAction() === 'addCollection'){
                $this->addCollection($this->getCollectionName());
                header('location: '.DOMEN.'/'.$this->getRoute());
            }
            if($this->getAction() === 'deleteCollection'){
                $this->deleteCollection($this->getProperty());
                header('location: '.DOMEN.'/'.$this->getRoute());
            }
        }
        if($this->getRoute() === 'testing'){
            
        }
    }
    
    public function getMenu()
    {
        function menu($array){
            foreach($array as $key => $value){
                if(is_array($value)){
                    echo '<div class=subMenu>'
                        .'<div class="menuTitle">'.$value['title'].'</div>';
                    menu($value);
                }else{
                    if($key !== 'title' and $key !== 'self'){
                        $self = (isset($array['self'])) ? $array['self'].'/' : '';
                        echo '<div class="menuItem">'
                            . '<a href="'.DOMEN.'/'.$self.$key.'">'.$value.'</a>'
                            . '</div>';
                    }
                }
                if(is_array($value)){
                    echo '</div>';
                }
            }
        }
        menu($this->_menu);
    }
    
    public function addHeader($title, $add = '')
    {
        $html = '<div class="mainInfo">'.$title.'</div>'.$add;
        return $html;
    }
    
    private function createRemoveInput($type = 'add')
    {
        $str = '<input name="deleteOne" type="button" value="-1 input">'
                .'<input name="deleteAll" type="button" value="Удалить все">';
        if($type === 'add'){
            $str .= '<input name="deleteInDb" type="button" value="Удалить из базы">';
        }
        return $str;
    }
    
    private function createAddInput($array, $cycleIter, $id = '', $iPuth = '', $aPuth = ''){
        $data = 'data-inputid="'.$id.'" data-inputputh="'.$iPuth.'" data-arrayputh="'.$aPuth.'" data-cycle="'.$cycleIter.'"';
        $str = '<div class="inputGroups" '.$data.'>'
            . '<div class="buttonsForAddNewEllement">'
            . '<div class="title">Добавленние нового поля к '.$iPuth.'</div>'
            . '<input class="subInput" type="text" name="attrText" placeholder="name">'
            . '<input class="subInput" type="text" name="attrDefault" placeholder="by default"><br>'
            . '<input name="addOne" type="button" value="+ 1 input">'
            . '<input name="addToAll" type="button" value="Всем эллементам">'
            . '<input name="addInDb" type="button" value="Всем + в базу">'
            . '</div>';
        $newArray = '';
        $newStr = '';
        if(!$array){
            $str .= 'В базе еще не добавленно ни одного элемента!';
        }else{
            foreach($array as $key => $value){
                if(is_array($value)){
                    $inputId = ($id) ? $id."[".$key."]" : "[".$key."]";
                    $inputName = $cycleIter.$inputId;
                    $inputPuth = ($iPuth) ? $iPuth." -> ".$key : $cycleIter." -> ".$key;
                    $arrayPuth = ($aPuth) ? $aPuth.".".$key : $key;
                    $data = 'data-inputid="'.$inputId.'" data-arrayputh="'.$arrayPuth.'" data-inputputh="'.$inputPuth.'" data-cycle="'.$cycleIter.'"';
                    $newArray .= '<div class="newArray" '.$data.'>'
                     .'<input data-inputid="'.$inputId.'" class="mainInput" type="text" name="'.$inputName.'" value="'.$key.'"> - '.$inputPuth.'<br>'
                     .$this->createRemoveInput()       
                     .$this->createAddInput($value, $cycleIter, $inputId, $inputPuth, $arrayPuth)
                     .'</div>';
                }else{
                    $inputId = ($id) ? $id."[".$value."]" : "[".$value."]";
                    $inputName = $cycleIter.$inputId;
                    $inputPuth = ($iPuth) ? $iPuth." -> ".$value : $cycleIter." -> ".$value;
                    $arrayPuth = ($aPuth) ? $aPuth.".".$value : $value;
                    $data = 'data-inputid="'.$inputId.'" data-arrayputh="'.$arrayPuth.'" data-inputputh="'.$inputPuth.'" data-cycle="'.$cycleIter.'"';
                    $newStr .= '<div class="newElement" '.$data.'>'
                     .'<input data-inputid="'.$inputId.'" class="subInput" type="text" name="'.$inputName.'"> - '.$inputPuth.'<br>'
                     .$this->createRemoveInput()
                     .' <input name="arrayOne" type="button" value="+1 массив">'
                     . '<input name="arrayAll" type="button" value="Все в массив">'
                     . '<input name="arrayDb" type="button" value="Массив в базу">'
                     .'</div>';
                }
            }
        }
        $str .= '<div class="strings">'.$newStr.'</div>'
             . '<div class="arrays">'.$newArray.'</div>';
        $str .= '</div>';
        return $str;
    }
       
    public function htmlFormAddNewElement($array)
    {
        $str = '<form class="addNewElement" action="" method="POST">';
        for($i = 0; $i < $this->getEntriesCount(); $i++){
            $str .= '<div id="'.$i.'" class="element">';
            $str .= $this->createAddInput($array,$i);
            $str .= '</div>';
        }
        $str .= '<input type="hidden" name="collection" value="'.$this->getProperty().'">'
            . '<input type="hidden" name="actionOnElements" value="addNewElements">'
            . '<input class="sendNewElement" type="submit" value="Создать новый элемент">'
        . '</form>';
        return $str;
    }
    
    private function createEditInput($array, $inputN = '', $elementP = 'main'){
        $str = '<div class="inputGroups" data-inputparent="'.$inputN.'" data-elementputh="'.$elementP.'" >'
            . '<div class="buttonsForAddNewEllement">'
            . '<div class="title">Добавленние нового поля к '.$elementP.'</div>'
            . '<input class="subInput" type="text" name="attrText" placeholder="name">'
            . '<input class="subInput" type="text" name="attrDefault" placeholder="by default"><br>'
            . '<input name="addOne" type="button" value="+ 1 input">'
            . '<input name="addToAll" type="button" value="Всем эллементам">'
            . '</div>';
        $newArray = '';
        $newStr = '';
        foreach($array as $key => $value){
            if($key === '_id'){
                continue;
            }
            $inputName = ($inputN) ? $inputN.'.'.$key : $key;
            $elementPuth = $elementP.' -> '.$key;
            $data = 'data-inputname="'.$inputName.'" data-elementputh="'.$elementPuth.'" ';
            if(is_array($value)){
                $newArray .= '<div class="newArray" '.$data.'>'
                 .'<input class="mainInput" type="text" name="'.$inputName.'" value="'.$key.'"  data-type="array" data-value="'.$key.'"> - '.$elementPuth.'<br>'
                 .$this->createRemoveInput('edit')       
                 .$this->createEditInput($value, $inputName, $elementPuth)
                 .'</div>';
            }else{
                $newStr .= '<div class="newElement" '.$data.'>'
                 .'<input class="subInput" type="text" name="'.$inputName.'" value="'.$value.'" data-type="string" data-value="'.$value.'"> - '.$elementPuth.'<br>'
                 .$this->createRemoveInput('edit')
                 .' <input name="arrayOne" type="button" value="+1 массив">'
                 . '<input name="arrayAll" type="button" value="Все в массив">'
                 .'</div>';
            }
        }
        $str .= '<div class="strings">'.$newStr.'</div>'
             . '<div class="arrays">'.$newArray.'</div>';
        $str .= '</div>';
        return $str;   
    }
    
    public function htmlForEditElement($array)
    {
        $str = '<form class="editElement" action="" method="POST">';
        foreach($array as $key => $value){
            $str .= '<div id="'.$key.'" class="element" data-elementid="'.$value['_id'].'">';
            $str .= $this->createEditInput($value);
            $str .= '</div>';
        }
        $str .= '<input type="hidden" name="collection" value="'.$this->getProperty().'">'
             . '</form>';
        return $str;
    }
        
    private function setGPS()
    {
        $this->_route = (filter_input(INPUT_GET, 'route')) ? filter_input(INPUT_GET, 'route') : $this->getMainPage();
        $this->_action = filter_input(INPUT_GET, 'action');
        $this->_property = filter_input(INPUT_GET, 'property');
        $this->_mainKey = filter_input(INPUT_POST, 'mainKey');
        $this->_elementsId = filter_input(INPUT_POST, 'elementsId', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $this->_actionOnElements = filter_input(INPUT_POST, 'actionOnElements');
        $this->_collectionName = filter_input(INPUT_POST, 'collectionName');
        
        $this->_entriesCount = (filter_input(INPUT_POST, 'entriesCount')) ? filter_input(INPUT_POST, 'entriesCount') : $this->_entriesCount; 
                
        return $this;
    }
    
    public function getRoute()
    {
        return $this->_route;
    }
    
    public function getAction()
    {
        return $this->_action;
    }
    
    public function getProperty()
    {
        return $this->_property;
    }
    
    public function getMainKey()
    {
        return $this->_mainKey;
    }
    
    public function getElementsId()
    {
        return $this->_elementsId;
    }
    
    public function getActionOnElements()
    {
        return $this->_actionOnElements;
    }
    
    public function setEntriesCount($count)
    {
        $this->_entriesCount = $count;
        return $this;
    }
    
    public function getEntriesCount()
    {
        return $this->_entriesCount;
    }
    
    public function getMainPage()
    {
        return $this->_mainPage;
    }
    
    public function getCollectionName()
    {
        return $this->_collectionName;
    }
}

