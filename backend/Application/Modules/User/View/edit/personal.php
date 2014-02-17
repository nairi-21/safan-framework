<?php 
    if(!isset($isForm) || !$isForm) : 
        $widgetManager->begin('UserBusinessCart');   
    else : ?>

<form action="#" method="post" id="personalInformationForm">   
    <div class="w680 pb10 mb10 brBottomF1">
        <div class="left w220 mr10">
            <span class="left block c666 f09 tLeft">Full name:</span> 
            <input type="text" class="block p5" name="displayName" value="<?= $user->displayName ?>" /> 
        </div>
        <div class="left w220 mr5">
            <span class="left block c666 f09 tLeft">E-mail:</span> 
            <input type="text" class="block p5" name="email" value="<?= $user->email ?>" /> 
        </div>
        <div class="left w220 mr5">
            <span class="left block c666 f09 tLeft">Phone number:</span> 
            <input type="text" class="block p5" name="mobilePhone1" value="<?= (isset($location)) ? $location->mobilePhone1 : '' ?>" /> 
        </div>
        <div class="left pb10 pt10 mr20 block">
            <span class="block w100 c666 f09 tLeft pb3">Birthday:</span> 
            <select name="month" class="f07 w90">
                <?php 
                    $birthdayMonth = $user->birthday->format('n');
                    for ($i=1; $i<=12; $i++) {
                        $selectedMonth = ($i == $birthdayMonth) ? 'selected' : '';
                ?>      
                        <option value="<?=$i?>" <?=$selectedMonth?>><?=date('F', mktime(0,0,0,$i,1))?></option>
                <?php }?>
            </select>
            <select name="day" class="w50 f07">
            <?php 
                $birthdayDay = $user->birthday->format('d');
                for($i=1; $i<32; $i++){
                    $selectedDay = ($i == $birthdayDay) ? 'selected' : '';
                ?>
                <option value="<?=$i?>" <?=$selectedDay?>><?=$i?></option>
            <?php } ?>
            </select>
            <select name="year" class="w60 f07">
            <?php 
                $birthdayYear = $user->birthday->format('Y');
                $startYear = date('Y') - 130;
                $endYear = date('Y') - 5;
                for($i=$startYear; $i<$endYear; $i++){
                    $selectedYear = ($i == $birthdayYear) ? 'selected' : '';
            ?>      
                <option value="<?=$i?>" <?=$selectedYear?>><?=$i?></option>
            <?php } ?>
            </select>
        </div>
        <div class="left pt10 pb10 mr10">
            <span class="block pb10 w100 c666 f09 tLeft">Gender:</span>
            <div>
                <span class="cBlue">Male</span>
                <input type="radio" class="p10" name="gender" value="<?=\Application\Models\UsersInfo::USERSINFO_GENDER_MALE ?>" 
                    <?= ($user->gender == \Application\Models\UsersInfo::USERSINFO_GENDER_MALE) ? 'checked' : '' ?> /> 
                <span class="cBlue">Female</span>
                <input type="radio" class="p10" name="gender" value="<?=\Application\Models\UsersInfo::USERSINFO_GENDER_FEMALE ?>" 
                    <?= ($user->gender == \Application\Models\UsersInfo::USERSINFO_GENDER_FEMALE) ? 'checked' : '' ?>  /> 
            </div>
        </div>
        <div class="right w220 mr5">
            <div class="row pt10 pb10">
                <span class="left block w100 c666 f09 tLeft">Website:</span> 
                <input type="text" class="p5" name="website" value="<?= $user->website ?>" /> 
            </div>
        </div>
        <div class="clear"></div>
    </div>

    <div class="w680 pb10">
        <div class="left w220 mr10">
            <span class="left block w100 c666 f09 tLeft mr5">Country:</span> 
            <select name="country" class="block w210">
                <option>Select Country</option>
                <?php foreach($allCountries as $key => $value) : ?>
                <option value="<?= $value->countryID ?>" <?= (isset($location) && $location->countryID == $value->countryID) ? 'selected' : '' ?>><?= $value->name ?></option>
                <?php endforeach; ?>
            </select> 
        </div>
        <div class="left w220 mr5">
            <span class="left block w100 c666 f09 tLeft mr20">State:</span> 
            <input type="text" name="state" class="block p5" value="<?= isset($locationState) ? $locationState->name : '' ?>" /> 
        </div>
        <div class="left w220 mr5">
            <span class="left block w100 c666 f09 tLeft">City:</span> 
            <input type="text" name="city" class="block p5" value="<?= isset($locationCity) ? $locationCity->name : '' ?>" /> 
        </div>
        <div class="clear"></div>
    </div>

    <div class="w680 brBottomF1 pb10 mb10">
        <div class="left w220 mr10">
            <span class="left block w100 c666 f09 tLeft">Address:</span> 
            <input type="text" name="address" class="block" value="<?= isset($location) ? $location->address : ''?>" /> 
        </div>
        <div class="left w220 mr5">
            <span class="left block w100 c666 f09 tLeft">Fax:</span> 
            <input type="text" name="fax" class="block" value="<?= isset($location) ? $location->fax : '' ?>" /> 
        </div>
        <div class="left w220 mr5">
            <span class="left block w100 c666 f09 tLeft">Zip:</span> 
            <input type="text" name="zip" class="block" value="<?= isset($location) ? $location->zip : '' ?>" /> 
        </div>
        <div class="clear"></div>
    </div>
    <div class="posAbs posR10 posB10">
        <a href="#" class="cancel f10 ml10 right cancelPersonalInformation">Cancel</a>
        <a href="#" class="save f10 right savePersonalInformation">Save</a>
    </div>
</form>

<?php endif; ?>
