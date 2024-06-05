<?php
App::uses('AppModel', 'Model');

class MachineProduct extends AppModel {

  public function hasMachine($productId,$machineId){
    $machineProduct=$this->find('first',[
      'conditions'=>[
        'MachineProduct.product_id'=>$productId,
        'MachineProduct.machine_id'=>$machineId,
      ],
      'recursive'=>-1,
      'order'=>'MachineProduct.id DESC',
    ]);
    if (empty($machineProduct)){
      return false;
    }
    //if ($productId == 83){
      //echo 'machineId is '.$machineId.'<br/>';
      //echo 'bool assigned is '.$machineProduct['MachineProduct']['bool_assigned'].'<br/>';
    //}
    return $machineProduct['MachineProduct']['bool_assigned'];
  }
  
  public function getMachineIdsForProduct($productId){
    $machines=$this->Machine->find('list',[
      'fields'=>'Machine.id',
    ]);
    foreach ($machines as $machineId=>$machineName){
      if (!$this->hasMachine($productId,$machineId)){
        //if ($productId==83){
        //  echo 'unsetting machine '.$machineId.'<br/>';
        //}
        unset($machines[$machineId]);
      }
    }
    return $machines;
  }
  
  public function getMachineIdsForProductIds($productIds=[]){
    if (empty($productIds)){
      return null;
    }
    $productMachines=[];
    foreach ($productIds as $productId){
      $productMachines[$productId]=array_keys($this->getMachineIdsForProduct($productId));
    }
    return $productMachines;
  }
  

	public $validate = array(
		'machine_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'product_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Machine' => array(
			'className' => 'Machine',
			'foreignKey' => 'machine_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Product' => array(
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
