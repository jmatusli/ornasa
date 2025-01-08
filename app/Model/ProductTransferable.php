<?php
App::uses('AppModel', 'Model');

class ProductTransferable extends AppModel {

  
    function getTransferableTo($productId){
   
	 return $this->find('list',[
      'conditions'=>[
        'product_from'=>$productId,
      ],'fields'=>[
        'ProductTransferable.product_to'
      ]
    ]);
	
	  
 
  }
    function getTransferableFrom($productId){
   
	 return $this->find('list',[
      'conditions'=>[
        'product_to'=>$productId,
      ],'fields'=>[
        'ProductTransferable.product_from'
      ]
    ]);
	
	  
 
  }
}
