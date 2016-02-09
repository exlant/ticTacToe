(function($){
// размещает элемент по центру родительского элемента
// можно задать смещение по оси X, Y,
// минимальный отступ сверху и слева
// реагирует на изменение окна
$.fn.moveToCenter = function(x,y,minX, minY)
{
    x = (x && typeof(x) === "number") ? x : 0;
    y = (y && typeof(y) === "number") ? y : 0;
    minX = (minX && typeof(minX) === "number") ? minX : 5;
    minY = (minY && typeof(minY) === "number") ? minY : 5;
    
    function move(){
        
        var width = (this.parent().width() - this.width() - x)/2;
        var height = (this.parent().height() - this.height() - y)/2;
        width = setMinVal(width, minX);
        height = setMinVal(height, minY);
        var style = {
                    "margin-left": width+"px",
                    "margin-top": height+"px"
        };
        this.css(style);
    };
    
    function setMinVal(current, min){
        return (current > min) ? current : min; 
    }
    move.bind(this)();
    $(window).on("resize", move.bind(this));
    return this;
};

$.fn.reduceText = function(minWidth){
    minWidth = (minWidth && typeof(minWidth) === "number") ? minWidth : this.width();
    var html = "<span class='calculateLength'>"+this.html()+"</span>";
    var calculateLengthElement = $(html).appendTo($("body"));
    var fontSize = this.css("font-size");
    var fontWeight = this.css("font-weight");
    var fontFamily = this.css("font-family");
    if(calculateLengthElement.width() > minWidth){
        calculateLengthElement.css({
            fontSize: fontSize,
            fontWeight: fontWeight,
            fontFamily: fontFamily
        });
        var minText = "<div title='"+this.text()+"' style='width:"+minWidth+"px' class='minText'>"+this.html()+"</div>...";
        this.html(minText);
    }
};
// вычесляет padding элемента
$.fn.padding = function(how)
{
    how = (how || "global");
    var stack = {
        global: ["padding"],
        vertical: ["padding-top","padding-bottom"],
        gorizontal: ["padding-left", "padding-right"]
    };
    
    for(var i = 0, padding = 0; i < stack[how].length; i++){
        padding += parseInt(this.css(stack[how][i]));
    };
    
    return padding;
};

$.expr[':'].regex = function(elem, index, match) {
   var matchParams = match[3].split(','),
   validLabels = /^(data|css):/,
   attr = {
      method: matchParams[0].match(validLabels) ? matchParams[0].split(':')[0] : 'attr',
      property: matchParams.shift().replace(validLabels,'')
   },
   regexFlags = 'ig',
   regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
   return regex.test(jQuery(elem)[attr.method](attr.property));
};

})(jQuery);

var DOMEN = "http://"+location.hostname+"/";
var TICTACTOE =  "tictactoe";
var AJAX = "";

var minPass = 6; // минимальный пароль
var maxPass = 64; // максимальный пароль
var minLogin = 3; // минимальный логин
var maxLogin = 20; // максимальный логин
var validatingData = {
    // первое слово это input[name](или id элемента)
    // после "-" указывается функция для действия после проверки, по умолчния исполниться "default"
    // после ":" указвается тип проверки(из объекта validation), если не указать будет равняться input[name](или id элемента) 
    // 
    checkObjects: ["nick", "password", "passTest", "mail", "captcha"],
    // свойства, которые нельзя переопределить
    notChangeMethod:[
        "notChangeMethod",
        "setParameters",
        "validations",
        "addValidation",
        "async",
        "startValidation",
        "ajaxCheck"
    ],
    //данные из вне
    data: {},
    stack: {
        text: "" // содержит текст ошибки в неасинхронных проверках
    },
    // установка переданных параметров
    setParameters: function (parameters){
        for(var key in parameters){
            if(this[key] && $.inArray(key, this.notChangeMethod) < 0){
                this[key] = parameters[key];
            }
        }
    },

    async:{
        start: 0,
        complete: 0,
        data: {
            add: function(id, value){
                this[id] = value;
            }
        },
        status: function(status){
            this[status]++;
        },
        // если завершены начатые ajax запросы, запускаем переданную фунцию
        startFunc: function(func, id, params){
            var timer;
            function foo(){
                if(this.start === this.complete){
                    // запись переданных данных из ожидаемой функции
                    if(this.data[id] && params){
                        for(var key in this.data[id]){
                            params[key] = this.data[id][key];
                            delete this.data[id][key];
                        }
                    }
                    func(params);
                    if(timer){
                        clearInterval(timer);
                    }
                }
            }
            timer = setInterval(foo.bind(this), 200);
            foo.bind(this)();
        }
    },

    startValidation: function(){
        var pattern = /^([\w]+)-?([\w]*):?([\w]*)\[?([\w,]*)\]?/;
        var match;
        var id;             // id или input[name]
        var actionFunc;     // функция для действия после проверки
        var validFunction;  // функция для проверки
        var flags;          // флаги
        var element;         // проверяемый элемент
        var params = {};      // параметры элемента
        // перебираем все заданные элементы
        for(var i = 0; i < this.checkObjects.length; i++){
            //разбиваем строку на параметры
            match = this.checkObjects[i].match(pattern);
            id = match[1];
            actionFunc = (match[2] || "default");
            validFunction = (match[3] || id);
            flags = match[4].split(",");
            // запускаем проверку, и действие на нее
            // флаг async, action будет ждать завершения функции валидации 
            // функции проверки возвращают 1||0, т.е ошибка || нет ошибок
            element = $("form input[name='"+id+"']"); // проверяемый input
            element = (element.length > 0) ? element : $("#"+id);
            params = {
                    id: id,
                    element: element,
                    validation: this.validations[validFunction].bind(this, element)()
                };
            if($.inArray(validFunction, this.validations.async) > -1){
                this.async.startFunc(this.actions[actionFunc].bind(this), id, params);
            }else{
                this.actions[actionFunc].bind(this,params)();
            }
            this.async.startFunc(this.ending.bind(this));
        };
    },
    //добавляет методы проверки
    addValidation: function(){

    },
    // существующие проверки, можно добавить через метод addValidation
    validations: {
        async: ["nick", "captcha"],
        data: {},
        nick: function(login){   // проверка логина
            // итератор ошибок
            var iterator = 0;
            var text = "";
            var pattern = new RegExp("^(?!empty|draw|guest_)[-A-z0-9_]+$");
            // проверка регуляркой
            if(!login.val().match(pattern)){
                text += "Допустимы латинские символы,<br>цифры (0-9), знаки \"-\", \"_\"<br>";
                iterator++;
            }
            // проверка длины логина
            if(login.val().length < minLogin || login.val().length > maxLogin){
                text += "Количество символов от "+minLogin+" до "+maxLogin+"!";
                iterator++;
            }
            // проверка на наличие в базеДанных
            if(iterator === 0){
                text = "";
                this.ajaxCheck(login.attr("name"),"login="+login.val(), "Этот логин занят! Попробуйте другой.");
            }
            this.validations.data[login.attr("name")] = {text:text};
            return iterator;  
        },
        password: function(firstPass){ // проверка пароля
            // на существование
            var text = "";
            if(firstPass.val().length === 0){
                text = "Вы не ввели пароль!";
                this.validations.data[firstPass.attr("name")] = {text:text};
                return 1;
            }
            // соответствие с минимальной длиной
            if(firstPass.val().length < minPass){
                text = "Минимальное количество символов - "+minPass+"!";
                this.validations.data[firstPass.attr("name")] = {text:text};
                return 1;
            // соответствие с максимальной длиной
            }else if(firstPass.val().length > maxPass){
                text = "Максимальное количество символов - "+maxPass+"!";
                this.validations.data[firstPass.attr("name")] = {text:text};
                return 1;
            }
            this.validations.data[firstPass.attr("name")] = {text:""};
            return 0;
        },
        passTest: function(secPass){ // проверка на совпадение паролей
            var firstPass = $("form input[name='password']");
            var text = "";
            if(firstPass.val() !== secPass.val()){
                text = "Пароли не совпадают!";
                this.validations.data[secPass.attr("name")] = {text:text};
                return 1;
            }
            this.validations.data[secPass.attr("name")] = {text:""};
            return 0;
        },
        mail: function(mail) // проверка почты
        {
            var pattern = new RegExp(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,6})+$/);
            var text = "";
            if(!mail.val().match(pattern)){
                text = "Не корректный email!";
                this.validations.data[mail.attr("name")] = {text:text};
                return 1;
            }
            this.validations.data[mail.attr("name")] = {text:""};
            return 0;
        },
        captcha: function(captcha) // проверка каптчи
        {
            this.ajaxCheck(captcha.attr("name"), "captcha="+captcha.val(), "Не верно введен текст");
            return 0;
        }
    },
    // действие на валидацию
    actions:{
        stack:{},
        countErrors: 0,
        default: function(params)
        {
            var text = this.validations.data[params.id].text;
            // подсчет общей высоты главного дива с формой(mainDiv)
            function calculateHeight()
            {
                var height = 0;
                    for(var key in this.actions.stack){
                        height += this.actions.stack[key];
                    }
                return height + this.data.mainDivHeight;
            }
            // див в котором будет выведено сообщение
            var helper = $("#"+params.id);
            // скрыть или показать сообщение
            if(params.validation){
                // если была ошибка
                // если изменился текст сообщение одного и того же дива
                if(helper.html() !== text){
                    //была ли ошибка в этом диве, если нет увеличиваем счетчик ошибок
                    if(isNaN(this.actions.stack[helper.selector])){
                        this.actions.countErrors++;
                    }
                    // записуем текст ошибки
                    helper.html(text);
                    // записуем имя дива и высоту в стэк
                    this.actions.stack[helper.selector] = helper.height() + helper.padding("vertical");
                    // анимация 
                    this.data.mainDiv.animate({
                            "height": calculateHeight.bind(this)()
                        },
                        {
                            "duration": 300,
                            "complete": function(){
                                helper.slideDown(300);  
                            }
                        }
                    );
                    // красим border проверяемого инпута в красный цвет
                    params.element.css("border", "1px solid red");
                }
            }else{
                // если ошибок нет
                // проверяем была ли до этого ошибка в этом инпуте
                if(!isNaN(this.actions.stack[helper.selector])){
                    //убераем див с сообщением про ошибку
                    helper.html("").slideUp(300);
                    // красим border проверяемого инпута в зеленый цвет
                    params.element.css("border", "1px solid green");
                    // удаляем из стэка имя проверяемого дива
                    delete this.actions.stack[helper.selector];
                    // уменьшаем количество ошибок
                    this.actions.countErrors--;
                    // анимация для уменьшения основного дива
                    this.data.mainDiv.animate({
                            "height": calculateHeight.bind(this)()
                        },
                        {
                            "duration": 300
                        }
                    ); 
                };
            };

        }
    },
    ajaxCheck: function(id, data, newText)
    {
        var async = this.async;
        var validations = this.validations;
        //обозначаем начало ajax запроса
        async.status("start");
        $.ajax({
            type: "POST",
            url: "/ajax.php",
            data: data,
            async: true,
            cache: false,
            success: function(msg){
                if(msg === "danied"){
                    // передаем данные, которые будут переданы в функцию, 
                    // которая ждет выполнения этого запроса
                    validations.data[id] = {text:newText}; 
                    async.data.add(id, {validation: 1});
                }else if(msg === "Ok"){
                    validations.data[id] = {text:""};
                    async.data.add(id, {validation: 0});
                }
                //обозначаем завершение запроса
                async.status("complete");
            }
        });
    },
    // запускается после того как отработают все функции проверки
    ending: function(){}
};

