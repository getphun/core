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
        $params = [
            'greeting' => 'Let get some phun'
        ];
        
        $this->respond('index', $params);
    }
}