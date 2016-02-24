<?php
class createCaptcha
{
    private $_errors = array();        //ошибки скрипта
    private $width = 200;              //Ширина изображения
    private $height = 50;              //Высота изображения
    private $fontSize = 16;            //Размер шрифта
    private $maxL = 7;                //максимальное количество символов
    private $minL = 5;                 //минимальное количество символов
    private $_lettersAmount = null;    //Количество символов на каптче
    private $fonLetAmount = 30;        //Количество символов на фоне
    private $font = "./cour.ttf";      //Путь к шрифту
    //буквы для генерации каптчи
    private $_letters = 'abcdefghijkmnopqrstuvwxyz0123456789';
    //длина строки с символами
    private $_lettersLen = null;
    //массив с названием переменных, которые нельзя изменить из вне
    private $_notSetParams = array('_errors', '_src', '_code',  '_letters', '_lettersAmount', '_lettersLen',  '_notSetParams', '_colors');
    // цвета знаков каптчи
    private $_colors = array("90","110","130","150","170","190","210");
    // сгенерированная картинка
    private $_src = null;
    // код сгенерированной каптчи
    private $_code = null;
    
    public function __construct($params = array())
    {
        // название параметров = названия переменных, значения числа
        // кроме параметра font, значение путь к шрифту
        if($params and !$this->setParams($params)){
            return false;
        }
        $this->lettersLen()    //вычесляем число символов в строке символов для генерации каптчи
             ->lettersAmount() //вычесляем количество символов каптчи
             ->createImage()   //создаем картинку
             ->setCode()        // записуем код в сессию
             ->outputImage();  // выводим готовую картинку
    }
    
    //устанавливает настройки каптчи
    private function setParams($params)
    {
        foreach($params as $key => $val){
            if(isset($this->$key) && !in_array($key, $this->_notSetParams)){
                if($key !== 'font'){
                    if(is_numeric($val)){
                        $this->$key = $val;
                    }else{
                        $this->setErrors('Значение параметра <b>'.$key.'</b>, должно быть числом, а установленно - <b>'.$val.'</b>!');
                        return false;
                    }
                    
                }else{
                    $pattern = '|^[a-z.0-9/]+\.ttf$|i';
                    if(preg_match($pattern, $val) and file_exists($val)){
                        $this->$key = $val;
                    }else{
                        $this->setErrors('Не верно указан путь к шрифту!');
                        return false;
                    }
                    
                }
            }else{
                $this->setErrors('Нет такого параметра!');
                return false;
            }
        }
        return true;
    }
    // ошибки
    private function setErrors($error)
    {
        $this->_errors[] = $error;
        return $this;
    }
    // вывод ошибок
    public function getErrors()
    {
        return $this->_errors;
    }
    //вычесляем число символов в строке символов для генерации каптчи
    private function lettersLen()
    {
        $this->_lettersLen = strlen($this->_letters)-1;
        return $this;
    }
    //вычесляем количество символов каптчи
    private function lettersAmount()
    {
        $this->_lettersAmount = rand($this->minL, $this->maxL);
        return $this;
    }
    //создаем картинку
    private function createImage()
    {
        $src = imagecreatetruecolor($this->width,$this->height);    //создаем изображение               
        $fon = imagecolorallocate($src,255,255,255);    //создаем фон
        imagefill($src,0,0,$fon);                       //заливаем изображение фоном
        $this->createFonLetters($src)         //создаем фон из знаков
             ->createLetters($src);           //создаем знаки для каптчи
        $this->_src = $src;
        return $this;
    }
    
    //добавляем на фон символы
    private function createFonLetters($src)
    {
        for($i=0;$i < $this->fonLetAmount;$i++)          
        {
            //случайный цвет
            $color = imagecolorallocatealpha($src,rand(0,255),rand(0,255),rand(0,255),100); 
            //случайный символ
            $letter = $this->_letters[rand(0, $this->_lettersLen)]; 
            //случайный размер                              
            $size = rand($this->fontSize-2,$this->fontSize+2);                                            
            imagettftext($src,$size,rand(0,45),
                rand($this->width*0.1,$this->width-$this->width*0.1),
                rand($this->height*0.2,$this->height),$color,$this->font,$letter);
        }
        return $this;
    }
    //записуем символы в каптчу
    private function createLetters($src)
    {
        $code = '';
        // сдвигаем символы на центр картинки
        $center = ($this->width - ($this->fontSize * ($this->_lettersAmount+2)))/2;
        for($i=0; $i < $this->_lettersAmount; $i++)      //то же самое для основных букв
        {
           $color = imagecolorallocatealpha($src,$this->_colors[rand(0,sizeof($this->_colors)-1)],
                $this->_colors[rand(0,sizeof($this->_colors)-1)],
                $this->_colors[rand(0,sizeof($this->_colors)-1)],rand(20,40)); 
           $letter = $this->_letters[rand(0,$this->_lettersLen)];
           $size = rand($this->fontSize*2-2,$this->fontSize*2+2);
           $x = ($i+1)*$this->fontSize + rand(1,5) + $center;      //даем каждому символу случайное смещение
           $y = (($this->height*2)/3) + rand(0,5);                            
           $code .= $letter;                        //запоминаем код
           imagettftext($src,$size,rand(0,15),$x,$y,$color,$this->font,$letter); 
        }
        $this->_code = $code;
        return $this;
    }
    
    // записуем код в сессию
    private function setCode()
    {
        session_start();
        $_SESSION['captcha']['code'] = $this->_code;
        return $this;
    }
    
    // выводим готовую картинку
    private function outputImage()
    {
        header ("Content-type: image/gif");         //выводим готовую картинку
        imagegif($this->_src); 
    }
}
$captca = new createCaptcha($_GET);

if($captca->getErrors()){
    foreach($captca->getErrors() as $val){
        echo $val."<br>";
    }
}