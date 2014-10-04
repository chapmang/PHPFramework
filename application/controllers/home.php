<?php

use Shared\Controller as Controller;

class Home extends Controller {


    public function getIndex() {
        
    }



    public function getTest(){
    	if ($this->_ajax()) {
    	$test = array("Chapman");
    	echo json_encode($test);
    	}
    } 
}