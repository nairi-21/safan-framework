<!DOCTYPE html>
<html language="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="icon" href="<?=\Framework\Safan::app()->resourceUrl?>/images/favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="<?=\Framework\Safan::app()->resourceUrl?>/images/favicon.ico" type="image/x-icon" />
	<link type="text/css" href="<?=\Framework\Safan::app()->resourceUrl?>/css/default/form.css" rel="stylesheet" />
	<link type="text/css" href="<?=\Framework\Safan::app()->resourceUrl?>/css/default/main.css" rel="stylesheet" /> 
	<link type="text/css" href="<?=\Framework\Safan::app()->resourceUrl?>/css/default/style.css" rel="stylesheet" />
	<script type="text/javascript" src="<?=\Framework\Safan::app()->resourceUrl?>/js/default/jquery.js"></script>
	<script type="text/javascript" src="<?=\Framework\Safan::app()->resourceUrl?>/js/default/safan.js"></script>
	<title><?=$this->pageTitle?></title>
</head>
<body>
	<div id="page">
		<?=$this->getLayout()?>
	</div>
</body>
</html>
