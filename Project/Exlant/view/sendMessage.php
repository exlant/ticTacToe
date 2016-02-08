<div class="centerTitle">Отправить сообщение разработчику</div>
<div class="sendMessage">
    <form action="" method="post">
        <input type="hidden" name="type" value="sendMessage">
        <div class="captcha">
            <div class="title">
                <img src="images/captcha.php?width=500&height=50">
            </div>
            <div class="textInput">
                <input type="text" placeholder="Введите текст с картинки" name="captcha">
            </div>
        </div>
        <div class="subject">
            <div class="title">Тема сообщения:</div>
            <div class="textInput">
                <input type="text" placeholder="Введите тему сообщения" name="subject">
            </div>
        </div>
        <div class="mail">
            <div class="title">Ваш mail(необязательно)</div>
            <div class="textInput">
                <input type="text" placeholder="Введите ваш mail" name="mail">
            </div>
        </div>
        <div class="body">
            <div class="title">Текст сообщения</div>
            <div class="textArea">
                <textarea name="body"></textarea>
            </div>
        </div>        
        <div class="submit">
            <input type="submit" value="Отправить">
        </div>
    </form>
</div>
