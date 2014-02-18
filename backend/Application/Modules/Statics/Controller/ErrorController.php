<?php

namespace Application\Modules\Statics\Controller;

use \Framework\Safan;

class ErrorController extends \Framework\Core\Mvc\Controller
{
	/**
     * Error 404, Page not found action
     *
	 * @Route('/error')
     * @Template('error/404.php')
	 */
	public function error404Action(){
		return $this->render('404');
	}

    /**
     * Check Requirements
     *
     */
	public function logAction(){
        $logger = Safan::app()->getObjectManager()->get('logger');
        if(Safan::app()->getDebugMode())
            $logger->printLogs();
    }
}







