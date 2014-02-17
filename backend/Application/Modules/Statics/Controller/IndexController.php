<?php
namespace Application\Modules\Statics\Controller;

use \Framework\Api;

class IndexController extends \Framework\Core\Mvc\Controller{
    /**
     * Web application home page action
     *
     * @Route('')
     * @Template('index/index.php')
     */
	public function indexAction(){
		return $this->render('index');
	}
	
	
}