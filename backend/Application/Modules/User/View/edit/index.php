<script>Api.setScript('<?= \Framework\Api::app()->resourceUrl . '/js/ui/jqueryUI.js' ?>')</script>
<script>Api.setStylesheet('<?= \Framework\Api::app()->resourceUrl . '/css/ui/jqueryUI.css' ?>')</script>

<?php $widgetManager->begin('UserCover', array('user' => $user)); ?>

<!-- Start Personal Information -->
<div class="row mt40 ml5 mr5 mb5 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.PERSONAL_INFORMATION') ?></span>
        <a href="#" class="editBtn posAbs posR10 posT10" id="userPersonalBtnChange"></a>
    </div>
    <div class="bgWhite pt20 pb50 pl10 pr10 posRel" data-container="personal">
        <?php $widgetManager->begin('UserBusinessCart', array('user' => $user, 'location' => $location)); ?>
    </div> 
</div>
<!-- End Personal Information -->

<!-- Start Education -->
<div class="row m5 mt20 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.EDUCATION') ?></span>
        <a href="#" class="addBtn posAbs posR10 posT0" id="educationBtnAdd">+</a>
    </div>
    <div class="bgWhite p10" data-container="education">
        <?= $widgetManager->begin('UserEducationCart', array('userID' => $user->id)) ?>    
    </div> 
</div>
<!-- End Education -->

<!-- Start Experience -->
<div class="row m5 mt20 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.EXPERIENCE') ?></span>
        <a href="#" class="addBtn posAbs posR10 posT0" id="experienceBtnAdd">+</a>
    </div>
    <div class="bgWhite p10" data-container="experience">
        <?= $widgetManager->begin('UserExperienceCart', array('userID' => $user->id)) ?>    
    </div> 
</div>
<!-- End Experience -->

<!-- Start Projects -->
<!--<div class="row m5 mt20 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.PROJECTS') ?></span>
        <a href="#" class="editBtn posAbs posR10 posT10"></a>
    </div>
    <div class="bgWhite p10">
         <?= $widgetManager->begin('UserProjectCart', array('userID' => $user->id)) ?>    
    </div> 
</div> -->
<!-- End projects -->

<!-- Start Skills -->
<div class="row m5 mt20 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.SKILLS') ?></span>
        <a href="#" class="editBtn posAbs posR10 posT10 skillBtnChange"></a>
    </div>
    <div class="bgWhite p10" data-container="skill">
        <?= $widgetManager->begin('UserSkillCart', array('userID' => $user->id, 'skillType' => \Application\Models\Skills::SKILL_TYPE_DEFAULT)) ?>    
    </div> 
</div>
<!-- End Skills -->

<!-- Start Languages -->
<div class="row m5 mt20 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.LANGUAGES') ?></span>
        <a href="#" class="editBtn posAbs posR10 posT10" id="languageBtnChange"></a>
    </div>
    <div class="bgWhite p10" data-container="language">
        <?= $widgetManager->begin('UserSkillCart', array('userID' => $user->id, 'skillType' => \Application\Models\Skills::SKILL_TYPE_LANGUAGE)) ?>    
    </div> 
</div>
<!-- End Languages -->

<!-- Start Interests -->
<div class="row m5 mt20 mb50 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.INTERESTS') ?></span>
        <a href="#" class="editBtn posAbs posR10 posT10 interestBtnChange"></a>
    </div>
    <div class="bgWhite p10" data-container="interest">
        <?= $widgetManager->begin('UserInterestCart', array('userID' => $user->id)) ?>    
    </div> 
</div>
<!-- End Interests -->

