<div class="whiteContainer shadowDC" id="mainStreamBar">
    <?php if($isCompanyRole) : ?>
        <a href="<?=\Framework\Api::app()->baseUrl . '/vacancies/create' ?>" class="left pl10 pr10 brF1">
            <span class="addBtn verticalM">+</span>
            <span class="verticalM f08 cBlue">Add Vacancy</span>
        </a>
    <?php else : ?>
        <a href="#" class="left pl10 pr10 brF1">
            <span class="addBtn verticalM">+</span>
            <span class="verticalM f08 cBlue">Add Post</span>
        </a>
        <a href="<?= \Framework\Api::app()->baseUrl . '/companies/create' ?>" class="left pl10 pr10 brF1">
            <span class="addBtn verticalM">+</span>
            <span class="verticalM f08 cBlue">Add Company</span>
        </a>
    <?php endif; ?> 
    <div class="clear"></div>
</div>
