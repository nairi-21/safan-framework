<?php

namespace Application\Widgets\AuthenticationLoginForm;

use \Framework\Safan;

class Widget extends \Framework\Core\Widget\WidgetManager
{
    /**
     * render login form
     * @var param is array
     */
    public function run($params = array()){
        return $this->render('form');
    }

}