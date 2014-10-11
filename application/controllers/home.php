<?php

use Shared\Controller as Controller;
use Framework\Registry as Registry;

class Home extends Controller {


    public function getIndex() {
        $cache = Registry::get('cache');
        $cache->set('test', "Geoff");
        $cache->set('test1', "Chapman");
        $cache->deleteAll();
        $name = $cache->get('test');
        var_dump($name);
    }



    public function getTest(){

    	$cache = Registry::get('cache');
    	if ($this->_ajax()) {
    	$test = array("Chapman");
    	echo json_encode($test);
    	}
    } 
}