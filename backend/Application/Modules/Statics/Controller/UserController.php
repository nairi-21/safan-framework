<?php
namespace Application\Modules\Statics\Controller;

use \Framework\Safan;

class UserController extends \Framework\Core\Mvc\Controller{
    /**
     * Web application home page action
     *
     * @Route('/statics/user/login')
     * @Template('user/index.php')
     */
	public function loginAction(){
		return $this->render('loginForm');
	}
	
	
}