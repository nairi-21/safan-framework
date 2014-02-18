<div class="w300 mCenter bDC">
    <div class="title p5 c666">Login</div>
    <div class="p5 bgF1">
        <form id="loginForm" action="<?= \Framework\Safan::app()->baseUrl . '/statics/user/login' ?>" method="post">
            <div class="p5">
                <label class="block f08 c666">Login:</label>
                <input type="text" name="login" class="w260" />
            </div>
            <div class="p5">
                <label class="block f08 c666">Password:</label>
                <input type="password" name="password" class="w260" />
            </div>
            <div class="p5">
                <input type="submit" class="save" value="Login" />
            </div>
        </form>
    </div>
</div>