<?php
namespace core;

class errorHandlerCore
{
    private $patch_error_log = '';         //путь к логу с ошибками
    private $admin_mail = '';               // почта на которую будут отправляться ошибки
    private $mail = 0;                  // 1 или 0 отправлять сообщение об ошибке на почту
    private $log = 0;                   // 1 или 0 вести/не вести лог
    private $display = 1;               // 1 или 0 отображать/не отображать фатальные ошибки в браузере
    private $debug = 0;                 // 1 или 0 полный/неполный отчет об ошибке(полный - с массивом cookie,session,request,server,file)

    private $params = array(            // параметры отображения суперглобальных массивов
        '_GET' => 1,
        '_POST' => 1,
        '_COOKIE' => 1,
        '_SESSION' => 1,
        '_SERVER' => 0,
        '_REQUEST' => 0,
        '_FILES' => 0,
    );

    private $styleCSS = array(
        'globalVariable' => array(
            'main' => ' width: 1346px;',
            'title' => ' border-bottom: 1px solid black;
                 padding: 5px;
                 text-align: center;
                 background-color: #70b3e6;
                 font-weight: bold;',
            'cell' => ' display: inline-block;
                 border: 1px solid black;
                 margin-left: 10px;',
            ),
        'viewStruct' => array(
            'table' => ' border: 1px solid black;',
            'th' => ' border: 1px solid black;
                 background-color: #78f684;
                 padding: 3px;',
            'td1' => ' border: 1px solid black;
                 background-color: #f4f4f6;
                padding: 3px;',
            'td2' => ' border: 1px solid black;
                 padding: 3px;
                 background-color: white;',
        ),

    );

    private $globalVariables = null;         //глобальные переменные

    private $u_error = NULL;               //сообщение об ошибке пользователя
    private $u_array_error = NULL;         //массив для сообщений об ошибке пользователя
    private $errno = NULL;
    private $error = NULL;
    private $errfile = NULL;
    private $errline = NULL;

    private $error_type = array(            //типы ошибок
            1 => 'Ошибка',
            2 => 'Опасность',
            4 => 'Синтактическая ошибка',
            8 => 'Предупреждение',
            16 => 'Ошибка ядра',
            32 => 'Предупреждение ядра',
            64 => 'Ошибка компилирования',
            128 => 'Опасность компилирования',
            256 => 'Ошибка пользователя',
            512 => 'Опастность пользователя',
            1024 => 'Предупреждение пользователя',
            2048 => 'Предупреждение времени выполнения',
            4096 => 'Фатальная ошибка'
    );

    private function setUserError($error)
    {
        $this->u_error = $error;
        return $this;
    }

    public function getUserError()
    {
        return $this->u_error;
    }

    private function setUserErrorArray($error)
    {
        $this->u_array_error[] = $error;
        return $this;
    }

    public function getUserErrorArray()
    {
        return $this->u_array_error;
    }

    // просмотр структуры(массив, объект) в графическом виде
    private function is_arrayObject($value)
    {
        if(is_array($value) or is_object($value)){
            return true;
        }
        return false;
    }

    public function viewStruct($struct)
    {
        $iterator = 0;
        $str = '<table class="viewStruct" style="'.$this->styleCSS['viewStruct']['table'].'">'
            . '<tr>'
                . '<th style="'.$this->styleCSS['viewStruct']['th'].'">Ключ</th>'
                . '<th style="'.$this->styleCSS['viewStruct']['th'].'">Содержимое</th>'
            . '</tr>';
        foreach ($struct as $key => $value){
            $styleTd = ($iterator%2 == 0) ? $this->styleCSS['viewStruct']['td1'] : $this->styleCSS['viewStruct']['td2'];
            $str .= '<tr>'
                    . '<td style="'.$styleTd.'">'.$key.'</td>'
                    . '<td style="'.$styleTd.'">'
                        . '('.gettype($value).') ';
            $str .= $this->is_arrayObject($value) ? $this->viewStruct($value): '<b>'.$value.'</b>';
            $str .=  '</td>'
                    . '</tr>';
            $iterator++;
        }

        $str .= '</table>';
        return $str;
    }

    public function setGlobalVariables($params = null) //запись глобальных переменных (post,get,cookie,server, etc)
    {                                                  //входящие данные массив с параметрами, где ключ это имя глобального массива
        if(is_array($params)){                         //значение это записывать, или нет этот глобальный массив
            foreach($this->params as $key => $value){
                if(!isset($params[$key]) or $params[$key] !== 0 or $params[$key] !==1){
                    $params[$key] = $value;
                }
            }
        }else{
            $params = $this->params;
        }

        $str = '<div class="globalVariables" style="'.$this->styleCSS['globalVariable']['main'].'">';
            foreach($params as $key => $value){
                if($value === 1){
                    $varName = $key;
                    global ${$varName};
                    $str .= '<div id="'.$key.'" style="'.$this->styleCSS['globalVariable']['cell'].'">'
                        . '<div class="headerGlobalVariables" style="'.$this->styleCSS['globalVariable']['title'].'">'.$key.'</div>'
                        .$this->viewStruct(${$varName})
                        . '</div>';
                }
            }
        $str .= '</div>';
        $this->globalVariables = $str;
        return $this;
    }

    public function getGlobalVariables()
    {
        return $this->globalVariables;
    }

    public function setError($errno,$error,$errfile,$errline)
    {
        //session_destroy();
        $this->error = $error;
        $this->errno = $errno;
        $this->errfile = $errfile;
        $this->errline = $errline;
        $this->handle_error();

        switch ($errno){
            case(E_ERROR)://1
                $this->handle_error();
            break;
            case(E_WARNING)://2
                $this->handle_error();
            break;

            case(E_NOTICE)://8
                $this->handle_error();
            break;
            case(E_COMPILE_ERROR)://64
                $this->handle_error();
            break;
            case(E_USER_ERROR)://256
                $this->handle_error();
            break;
            case(E_USER_WARNING)://512          //устанавливает не критическую, выводимую пользователю ошибку, в переменную
                //$this->handle_error();
                $this->setUserError($error);
            break;
            case(E_USER_NOTICE)://1024          //устанавливает не критическую, выводимую пользователю ошибку, в массив
                //$this->handle_error();
                $this->setUserErrorArray($error);
            break;
            case(E_STRICT)://2048
                $this->handle_error();
            break;

            default :
                $this->handle_error();

        }
    }
    private function display_error()
    {
        echo 'errno - ',$this->error_type[$this->errno],'(',$this->errno,')<br>'
                . 'error - ',$this->error,'<br>'
                . 'errfile - ',$this->errfile,'<br>'
                . 'errline - ',$this->errline,'<br><br><br>';

    }
    private function handle_error($die=0,$display=0,$debug=0,$log=0,$mail=0)
    {
        if($this->display || $display){
            $this->display_error();
        }
        if($this->debug || $debug){

        }
        if($this->log || $log){

        }
        if($this->mail || $mail){

        }
        if($die){
            header('location: error.html');
            die();
        }
    }

}


