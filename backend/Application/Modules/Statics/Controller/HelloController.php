<?php
namespace Application\Modules\Statics\Controller;

use \Framework\Api;

class HelloController extends \Framework\Core\Mvc\Controller{
    /**
     * Web application home page action
     *
     * @Route('/statics/hello')
     * @Template('hello/index.php')
     */
	public function indexAction(){
		return $this->render('index');
	}
	
	
}