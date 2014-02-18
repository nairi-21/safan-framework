<div id="pageWrapper">
    <div class="header">
        <div class="headerWrapper">
            <a href="<?= \Framework\Safan::app()->baseUrl ?>" class="f16">Safan framework</a>
            <div class="menu right">
                <ul>
                    <li class="left m10 c666 f08">
                        <a href="<?= \Framework\Safan::app()->baseUrl ?>/statics/hello" class="linked">Hello world</a>
                    </li>
                    <li class="left m10 c666 f08">
                        <a href="<?= \Framework\Safan::app()->baseUrl ?>/statics/user/login" class="linked">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="contentWrapper">
            <?= $this->getContent() ?>
        </div>
    </div>
    <div class="footer">
        <div class="footerWrapper">
            <p class="center nomargin">&copy; <?= date('Y') ?> Safan framework</p>
            <p class="center nomargin">All right reserved</p>
        </div>
    </div>
</div>