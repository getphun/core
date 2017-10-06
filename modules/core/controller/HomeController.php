<?php 
/**
 * Default site home controller. Please use your own controller
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Controller;

class HomeController extends \Controller
{
    public function indexAction(){
        $params = [];
        $this->respond('index', $params, 10);
    }
    
    public function notFoundAction(){
        $this->res->setStatus(404);
        $this->respond('404');
    }
}