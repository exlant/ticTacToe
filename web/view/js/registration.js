// запуск функции после загрузки странички
$(function(){

    // div с формой
    var mainDiv = $("div.authorization, div.registration").first();
    // первичная высота главного дива с формой
    var mainDivHeight = mainDiv.height();
    // кнопка отправки формы
    var buttonSend = $("form input[name='send']");
        
    // для авторизации
    if(mainDiv.is(".authorization")){
        mainDiv.moveToCenter(0,100);
        $("form input[name='captcha']")
                .on("blur", {type: "one"}, validation);
        buttonSend.on("click", {type: "full"}, validation);     
    }
    
    // для регистрации
    if(mainDiv.is(".registration")){
        // событие на потерю фокуса для текстовых инпутов
        $("form input[type='text'], form input[type='password']")
                .on("blur", {type: "one"}, validation);
        // размещение формы по центру
        mainDiv.moveToCenter(0,100);
        // полная проверка на отправку формы
        buttonSend.on("click", {type: "full"}, validation);        
    };
    
    // передаем параметры объекту, который проверяет форму
    validatingData.setParameters({
        data: {
            mainDiv:mainDiv,
            mainDivHeight: mainDivHeight
        }
    });
    
    // проверка формы
    function validation(e)
    {   
        if(e.data.type === "full"){
            e.preventDefault(); // отменяем нажатие кнопки
            var parameters = {
                ending: function(){
                    if(this.actions.countErrors > 0){
                        buttonSend.attr("disabled", "disabled");
                    }else{
                        $("form").trigger("submit");
                    }
                }
            };
            if(mainDiv.is(".authorization")){
                parameters.checkObjects = ["captcha"];
            }
            validatingData.setParameters(parameters);
            
        }else{
            var inputName = this.getAttribute("name");
            validatingData.setParameters({
                checkObjects: [inputName],
                ending: function(){
                    if(this.actions.countErrors > 0){
                        buttonSend.attr("disabled", "disabled");
                    }else{
                        buttonSend.removeAttr("disabled");
                    }
                }
            });
            
        }
        validatingData.startValidation();
    }
});