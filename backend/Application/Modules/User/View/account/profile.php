<?php $widgetManager->begin('UserCover', array('user' => $user)); ?>
<!-- Start Personal Information -->
<div class="row mt40 ml5 mr5 mb5 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.PERSONAL_INFORMATION') ?></span>
    </div>
    <div class="bgWhite pt20 pb50 pl10 pr10 posRel" data-container="personal">
        <?php $widgetManager->begin('UserBusinessCart', array('user' => $user, 'location' => $location)); ?>
    </div> 
</div>
<!-- End Personal Information -->

<!-- Start Education -->
<?= $widgetManager->begin('UserEducationCart', array('userID' => $user->id, 'layout' => true)) ?>    
<!-- End Education -->

<!-- Start Experience -->
<?= $widgetManager->begin('UserExperienceCart', array('userID' => $user->id, 'layout' => true)) ?>    
<!-- End Experience -->

<!-- Start Projects -->
<!--<div class="row m5 mt20 posRel">
    <div class="title p10 bgF1 bDC c666 f12 posRel">
        <span><?= $T('MODULES.USER.PROJECTS') ?></span>
        <a href="#" class="editBtn posAbs posR10 posT10"></a>
    </div>
    <div class="bgWhite p10">
         <?= $widgetManager->begin('UserProjectCart', array('userID' => $user->id, 'layout' => true)) ?>    
    </div> 
</div> -->
<!-- End projects -->

<!-- Start Skills -->
<?= $widgetManager->begin('UserSkillCart', array('userID' => $user->id, 'skillType' => \Application\Models\Skills::SKILL_TYPE_DEFAULT, 'layout' => true)) ?>    
<!-- End Skills -->

<!-- Start Languages -->
<?= $widgetManager->begin('UserSkillCart', array('userID' => $user->id, 'skillType' => \Application\Models\Skills::SKILL_TYPE_LANGUAGE, 'layout' => true)) ?>    
<!-- End Languages -->

<!-- Start Interests -->
<?= $widgetManager->begin('UserInterestCart', array('userID' => $user->id, 'layout' => true)) ?>    
<!-- End Interests -->
