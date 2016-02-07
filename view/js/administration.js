$(function(){
    $("div.wrapper").moveToCenter(0,1000);
    // добавление новых элементов в коллекцию  
    $("form.addNewElement").on("click", "input[name='addOne'], input[name='addToAll'], input[name='addInDb']", addInput);
    $("form.addNewElement").on("click", "input[name='deleteOne'], input[name='deleteAll'], input[name='deleteInDb']", deleteInput);
    $("form.addNewElement").on("click", "input[name='arrayOne'], input[name='arrayAll'], input[name='arrayDb']", toArray);
    // редактирование элементов
    $("form.editElement").on("blur", "input[type='text']", editCellValue);
    $("form.editElement").on("click", "input[name='addOne'], input[name='addToAll']", editAddNewCell);
    $("form.editElement").on("click", "input[name='deleteOne'], input[name='deleteAll']", editDeleteCell);
    $("form.editElement").on("click", "input[name='arrayOne'], input[name='arrayAll']", editToArray);
    
    
    // добавляет новое поле к создаваемому элементу
    // addOne - Только к текущему элементу
    // addToAll - ко всем создаваемым элементам
    // addINDb - ко всем создаваемым элементам, и ко всем элементам коллекции
    function addInput()
    {
        // элемент по которому кликнули
        var element = $(this);
        // div в который содержит параметры добавления нового элемента
        var parent = element.parent().parent();
        // название нового поля
        var attrText = parent.find("div.buttonsForAddNewEllement input[name='attrText']").val();
        // elementId - идентификатор, для поиска остальных эдементов
        // inputId - идентификатор для нового элемента
        // elementCycle - номер цикла в котором находится элемент
        // elementInputPuth - путь массива к текущему элементу, для вывода в названии нового элемента
        // arrayPuth - путь массива для запроса к базе(mongodb)
        var elementId = parent.data("inputid"); 
        var inputId = elementId+"["+attrText+"]";
        var elementCycle = parent.data("cycle");
        var elementInputPuth = parent.data("inputputh");
        var arrayPuth = (parent.data("arrayputh")) ? parent.data("arrayputh")+"."+attrText : attrText;
        
        var type = (element.attr("name") === 'addOne') ? 0 : (element.attr("name") === 'addToAll') ? 1 : 2;
        // имена других полей в коллекции, приоверка на дубликаты
        var otherElementsNames = new Array();
        element.parents("div.inputGroups:first").children("div.strings, div.arrays").children("div[data-arrayputh]").each(function(){
            otherElementsNames.push($(this).data("arrayputh").split('.').pop());
        });
        if(checkCellName(attrText,otherElementsNames) === false){
            return false;
        }
        
        // значение по умолчанию нового поля
        var attributeDefault = parent.find("div.buttonsForAddNewEllement input[name='attrDefault']").val();
        // html div с новыми input
        function createHtml(inputCycle,type)
        {
            var collor = ["#ebeb90", "#c0d3f0", "#c4ffdc"];
            var inputPuth = (elementInputPuth) 
                ? elementInputPuth.replace(/^[\d]{1,2}/,inputCycle)+" -> "+attrText 
                : inputCycle+" -> "+attrText;
            // полный путь с названием нового поля, 
            var inputName = inputCycle+inputId;
            // новый элемент
            var addelement = "<div class='newElement'"
                + "data-inputid='"+inputId+"' data-cycle='"+inputCycle+"' data-arrayputh='"+arrayPuth+"' data-inputputh='"+inputPuth+"'"
                + "style='background-color:"+collor[type]+"'>"
                + "<input class='subInput' type='text' name='"+inputName+"'>"
                + " - "+inputPuth+"<br>"
                + " <input name='deleteOne' type='button' value='-1 input'>";
            if(type > 0){
                addelement += "<input name='deleteAll' type='button' value='Удалить все'>";
            }
            if(type > 1){
                addelement +="<input name='deleteInDb' type='button' value='Удалить из базы'>";
            }
            addelement += " <input name='arrayOne' type='button' value='В массив'>";
            if(type > 0){
                addelement += "<input name='arrayAll' type='button' value='Все в массив'>";
            }
            if(type > 1){
                addelement +="<input name='arrayDb' type='button' value='Массив в базу'>";
            }
            addelement += "</div>";
            return addelement;
        }
        
        if(type === 0){
            $(createHtml(elementCycle,type)).prependTo(parent.children("div.strings"));
            return true;
        }else{
            // все элементы, в которые будет добавляться новый поле
            var elements = $("form div.element div.inputGroups[data-inputid='"+elementId+"']");
            for(var i = 0; i < elements.length; i++){
                // вставка нового элемента на страницу
                $(createHtml($(elements[i]).data("cycle"),type)).prependTo($(elements[i]).children("div.strings"));
            }
        }
        if(type === 2){
            // данные для отправки серверу о создании нового поля
            var data = {
                action: "add",
                object: "cell",
                collection: $("form input[name='collection']").val(),
                columsName: arrayPuth,
                columsDefault: attributeDefault 
            };
            // отправка данных
            sendAjax(data);
        }
        
    };
    // удаляет поле у создаваемого элемента(ов)
    // deleteOne - только у текущего элемента
    // deleteAll - у всех создаваемых элементов
    // deleteINDb - у всех создаваемых элементов, и у всех элементов в коллекции
    function deleteInput()
    {
        // элемент по которому кликнули
        var element = $(this);
        // div в который содержит параметры элемента
        var parent = element.parent();
        // id(идентификатор, для поиска остальных идентичных элементов),
        var elementId = parent.data("inputid");
        var arrayPuth = parent.data("arrayputh");
        
        if(element.attr('name') === "deleteOne"){
            parent.remove();
        }else{
            $("form div.element div.inputGroups div[data-inputid='"+elementId+"']").remove();   
        }
        if(element.attr('name') === "deleteInDb"){
            // данные для отправки серверу о создании нового поля
            var data = {
                action: "delete",
                object: "cell",
                collection: $("form input[name='collection']").val(),
                columsName: arrayPuth 
            };
            // отправка данных
            sendAjax(data);
        }
        
    };
    // преобразовывает поле из строки в массив у создоваемого элемента
    // arrayOne - только у текущего элемента
    // arrayAll - у всех создаваемых элементов
    // arrayDb - у всех создаваемых элементов, и у всех элементов в коллекции
    function toArray()
    {
        // элемент по которому кликнули
        var element = $(this);
        // div в который содержит параметры добавления нового элемента
        var parent = element.parent();
        // elementId - идентификатор, для поиска остальных эдементов
        // inputId - идентификатор для нового элемента
        // elementCycle - номер цикла в котором находится элемент
        // elementInputPuth - путь массива к текущему элементу, для вывода в названии нового элемента
        // arrayPuth - путь массива для запроса к базе(mongodb)
        var inputId = parent.data("inputid");
        var inputValue = parent.find("input[type='text']").val();
        var elementCycle = parent.data("cycle");
        var elementInputPuth = parent.data("inputputh");
        var arrayPuth = parent.data("arrayputh");
        
        var type = (element.attr("name") === 'arrayOne') ? 0 : (element.attr("name") === 'arrayAll') ? 1 : 2;
        
        function createHtml(inputCycle,type){
            var inputPuth = elementInputPuth.replace(/^[\d]{1,2}/,inputCycle);
            // полный путь с названием нового поля, 
            var inputName = inputCycle+inputId;
            
            var newElement = "<div class='newArray' "
                + "data-inputid='"+inputId+"' data-cycle='"+inputCycle+"' data-arrayputh='"+arrayPuth+"' data-inputputh='"+inputPuth+"'>"
                + "<input data-inputid='"+inputId+"' class='mainInput' type='text' name='"+inputName+"' value='"+inputValue+"'> - "+inputPuth+"<br>"
                + "<input name='deleteOne' type='button' value='-1 input'>";
            if(type > 0){
                newElement += "<input name='deleteAll' type='button' value='Удалить все'>";
            }
            if(type > 1){
                newElement +="<input name='deleteInDb' type='button' value='Удалить из базы'>";
            }
            
            newElement += '<div class="inputGroups" '
                + 'data-inputid="'+inputId+'" data-cycle="'+inputCycle+'" data-arrayputh="'+arrayPuth+'" data-inputputh="'+inputPuth+'">'
                + '<div class="buttonsForAddNewEllement">'
                + '<div class="title">Добавленние нового поля к '+inputPuth+'</div>'
                + '<input class="subInput" type="text" name="attrText" placeholder="name">'
                + '<input class="subInput" type="text" name="attrDefault" placeholder="by default"><br>'
                + '<input name="addOne" type="button" value="+ 1 input">';
                if(type > 0){
                    newElement += '<input name="addToAll" type="button" value="Всем эллементам">';
                }
                if(type > 1){
                    newElement += '<input name="addInDb" type="button" value="Всем + в базу">';
                }
            newElement += '</div>'
                + '<div class="strings"></div>'
                + '<div class="arrays"></div>';
                + '</div>';
            return newElement;
        }
        if(type === 0){
            $(createHtml(elementCycle, type)).prependTo(parent.parent().parent().children("div.arrays"));
            parent.remove();
        }else{
            // все элементы, которые будут преобразованы в массив
            var elements = $("form div.element div.inputGroups div.strings div.newElement[data-inputid='"+inputId+"']");
            for(var i = 0; i < elements.length; i++){
                element = $(elements[i]);
                $(createHtml(element.data("cycle"),type)).prependTo(element.parent().parent().children("div.arrays"));
                element.remove();               
            }
        }
        
        if(type === 2){
            // данные для отправки серверу
            var data = {
                action: "toArray",
                object: "cell",
                collection: $("form input[name='collection']").val(),
                columsName: arrayPuth
            };
            // отправка данных
            sendAjax(data);
        }
        
        
    };
    // редактирует поле у редактируемого элемента
    function editCellValue()
    {
        var element = $(this);
        if(element.attr("name") === "attrText" || element.attr("name") === "attrDefault"){
            return false;
        }
        if(element.data('value') === element.val()){
            return false;
        }
        var elementID = element.parents("div.element").data("elementid");
        var cellName = element.attr("name");
        var cellType  = element.data("type");
        if(cellType === "array"){
            var otherElementsNames = new Array();
            element.parents("div.inputGroups:first").children("div.strings, div.arrays").children("div[data-inputname]").each(function(){
                otherElementsNames.push($(this).data("inputname").split('.').pop());
            });
        }
        var data = {
            action: "edit",
            object: "cell",
            collection: $("form input[name='collection']").val(),
            elementID: elementID,
            columsName: cellName,
            cellValue: element.val(),
            cellType: cellType
        };
        sendAjax(data);
        if(cellType === 'array'){
            location.reload(true);
        }
        
    }
    // добавляет новое поле для редактируемых элементов
    // addOne - только у текущего элемента
    // addToAll - у всех редактируемых элементов
    function editAddNewCell()
    {
        // элемент по которому кляцнули, кнопка
        var element = $(this);
        // имя элемента, от имени зависит, сколько будем добавлять новых элементов, один или все (addOne|addToAll)
        var editType = element.attr('name');
        // родитель с data - данными
        var inputGroups = element.parents("div.inputGroups:first");
        // имя родителя, куда будем добавлять новый элемент
        var parentNameNewElement = inputGroups.data("inputparent");
        // id нового(ых) элемента(ов), для изменения в базе данных
        var elementId = new Array();
        // имя новой ячейки(элемента)
        var nameValue = element.parent().children("input[name='attrText']").val();
        // путь к элементу в базе данных
        var elementName = (parentNameNewElement) ? parentNameNewElement+'.'+nameValue : nameValue;
        //
        var elementPuth = inputGroups.data("elementputh")+' -> '+nameValue;
        // значение ячейки(елемента)
        var elementValue = element.parent().children("input[name='attrDefault']").val();
        
        var otherElementsNames = new Array();
        element.parents("div.inputGroups:first").children("div.strings, div.arrays").children("div[data-inputname]").each(function(){
            otherElementsNames.push($(this).data("inputname").split('.').pop());
        });
        if(checkCellName(nameValue,otherElementsNames) === false){
            return false;
        }
        if(editType === "addOne"){
            elementId.push(element.parents("div.element:first").data("elementid"));
        }else if(editType === "addToAll"){
            $("div.element").each(function(){
                elementId.push($(this).data("elementid")); 
            });
        }
        function createHtml()
        {
            var collor = {
                addOne: "#ebeb90",
                addToAll: "#c0d3f0"
            };
            // новый элемент
            var addelement = "<div class='newElement'"
                + "data-inputname='"+elementName+"' data-elementputh='"+elementPuth+"'"
                + "style='background-color:"+collor[editType]+"'>"
                + "<input class='subInput' type='text' name='"+elementName+"' value='"+elementValue+"' data-type='string' data-value='"+elementValue+"'>"
                + " - "+elementPuth+"<br>"
                + " <input name='deleteOne' type='button' value='-1 input'>";
            if(editType === "addToAll"){
                addelement += "<input name='deleteAll' type='button' value='Удалить все'>";
            }
            addelement += " <input name='arrayOne' type='button' value='В массив'>";
            if(editType === "addToAll"){
                addelement += "<input name='arrayAll' type='button' value='Все в массив'>";
            }
            
            addelement += "</div>";
            return addelement;
        }
        var html = createHtml();
        for(var i = 0; i < elementId.length; i++){
            $(html).prependTo("div.element[data-elementid='"+elementId[i]+"'] div.inputGroups[data-inputparent='"+parentNameNewElement+"'] div.strings:first");
        }
        
        var data = {
            action: "add",
            object: "cell",
            collection: $("form input[name='collection']").val(),
            elementID: elementId.join(','),
            columsName: elementName,
            columsDefault: elementValue       
        };
        sendAjax(data);
    }
    // удаляет поле у редактируемого элемента
    // deleteOne - только у текущего элемента
    // deleteAll - у всех редактируемых элементов
    function editDeleteCell()
    {
        // элемент по которому кляцнули, кнопка
        var element = $(this);
        // id элемента(ов), для удаления в базе данных
        var elementId = new Array();
        // имя элемента, от имени зависит, сколько будем удалять элементов, один или все (deleteOne|deleteAll)
        var editType = element.attr('name');
        // путь к элементу в базе данных
        var elementName = element.parent().data("inputname");
        if(editType === "deleteOne"){
            elementId.push(element.parents("div.element:first").data("elementid"));
            element.parent().remove();
        }else if(editType === "deleteAll"){
            $("div.element").each(function(){
                elementId.push($(this).data("elementid")); 
            });
            $("form div.inputGroups div[data-inputname='"+elementName+"']").remove();
        }
        var data = {
            action: "delete",
            object: "cell",
            collection: $("form input[name='collection']").val(),
            elementID: elementId.join(','),
            columsName: elementName       
        };
        sendAjax(data);     
    }
    // преобразовывает поле из строки в массив у редактируемого элемента
    // arrayOne - только у текущего элемента
    // arrayAll - во всех элементах, выбраных для редактирования
    function editToArray()
    {
        var button = $(this);
        var parent = button.parent();
        var editType = button.attr('name');
        var elementName = parent.data("inputname");
        var elementValue = parent.children("input[type='text']").attr("name");
        var elementPuth = parent.data("elementputh");
        var elementParent = parent.parents("div.inputGroups:first").data("inputparent");
        var elementId = new Array();
        
        function createHtml()
        {
           var html;
            html = '<div class="newArray" data-inputname="'+elementName+'" data-elementputh="'+elementPuth+'" >'
                + '<input class="mainInput" type="text" name="'+elementName+'" value="'+elementValue+'"  data-type="array" data-value="'+elementValue+'"> - '+elementPuth+'<br>';
                + '<input name="deleteOne" type="button" value="-1 input">';
            if(editType === "arrayAll"){
                html += '<input name="deleteAll" type="button" value="Удалить все">';
            }
                html += '<div class="inputGroups" data-inputparent="'+elementName+'" data-elementputh="'+elementPuth+'" >'
                    + '<div class="buttonsForAddNewEllement">'
                    + '<div class="title">Добавленние нового поля к '+elementPuth+'</div>'
                        + '<input class="subInput" type="text" name="attrText" placeholder="name">'
                        + '<input class="subInput" type="text" name="attrDefault" placeholder="by default"><br>'
                        + '<input name="addOne" type="button" value="+ 1 input">';
                        if(editType === "arrayAll"){
                            html += '<input name="addToAll" type="button" value="Всем эллементам">';
                        }
                html += '</div>'
                    + '<div class="strings"></div>'
                    + '<div class="arrays"></div>'
                + '</div>'
            + '</div>';
            return html;    
        }
        var html = createHtml();
        
        if(editType === "arrayOne"){
            elementId.push(button.parents("div.element:first").data("elementid"));
            
        }else if(editType === "arrayAll"){
            $("div.element").each(function(){
                elementId.push($(this).data("elementid")); 
            });
        }
        for(var i = 0; i < elementId.length; i++){
            $(html).prependTo("div.element[data-elementid='"+elementId[i]+"'] div.inputGroups[data-inputparent='"+elementParent+"'] div.arrays:first");
            parent.remove();
        }
        var data = {
                action: "toArray",
                object: "cell",
                collection: $("form input[name='collection']").val(),
                elementId: elementId,
                columsName: elementName
        };
        sendAjax(data);
    }
    
    function sendAjax(data)
    {
        $.ajax({
            type: "POST",
            url: "/ajax.php",
            data: data,
            async: true,
            success: function(msg){
                //$(msg).insertAfter("div.centerContainer");
            }
        });
    }
    
    function checkCellName(name,otherElementsNames)
    {
        var pattern = /^[a-z0-9_]{1,30}$/i;
        if(!name.match(pattern)){
            return false;
        }
        if($.inArray(name,otherElementsNames) > -1){
            return false;
        }
        return true;
    }
});