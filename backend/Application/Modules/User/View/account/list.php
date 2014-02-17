<div class="list">
    <?php foreach($users as $key => $value) : ?>
        <div class="userContainer w200 bDC mr10 mb10 p5 bgF1 bF1 left">
            <div class="userThumbnail center posRel oh">
                <a href="<?= \Framework\Api::app()->baseUrl . DS . 'user' . DS . $value->id; ?>" class="oh mCenter posRel bgWhite w180 h180 block">
                    <img src="<?= $profilePics[$value->id] ?>" alt="user <?=$value->id; ?> profile pic block" class="h180 block" />
                </a>    
            </div>
            <div class="center">
                <div class="row pt5 pb5 h50 posRel oh">
                    <a href="<?= \Framework\Api::app()->baseUrl . DS . 'user' . DS . $value->id?>"><?= $value->displayName ?></a>
                    <div class="c666 f08">
                        <?php if(isset($experiences[$value->id])) : ?>
                            <a href="<?= \Framework\Api::app()->baseUrl . '/job-position/' . $experiences[$value->id]->positionID ?>" class="cOrange link f08">
                                <?= isset($positions[$experiences[$value->id]->positionID]) ? $positions[$experiences[$value->id]->positionID]->name  : '' ?>
                            </a>
                            <?php if(isset($companies[$experiences[$value->id]->companyID])) : ?> 
                                <span class="ml5 mr5 f07"> at </span> 
                                <a href="<?= \Framework\Api::app()->baseUrl . '/company/' . $experiences[$value->id]->companyID ?>" class="cBlue f08 linked">
                                    <?= $companies[$experiences[$value->id]->companyID]->displayName ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>    
            </div>
            <div class="clear"></div>
        </div>
    <?php endforeach; ?>
</div>
