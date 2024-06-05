<?php
App::uses('AppModel', 'Model');

class Machine extends AppModel {
	var $displayField="name";
  
  function getMachineList(){
    $machines=$this->find('list',[
      'conditions'=>[
        'bool_active'=>true,
      ],
      'order'=>'name ASC',
    ]);
    return $machines;
  }
  
  function getActiveMachines($machineIds=[]){
    $conditions=['bool_active'=>true,];
    if (!empty($machineIds)){
      $conditions['Machine.id']=$machineIds;
    }
    $machines=$this->find('all',[
      'fields'=>['Machine.id,Machine.name'],
      'conditions'=>$conditions,
      'recursive'=>-1,
      'order'=>'name ASC',
    ]);
    return $machines;
  }
  
   public function getMachineListForPlant($plantId){
    $machines=$this->find('list',[
      'conditions'=>[
        'Machine.bool_active'=>true,
        'Machine.plant_id'=>$plantId,
      ],
      'order'=>'Machine.name ASC',
    ]);
    return $machines;
  }
  
  function getMachineUtility($machineId,$startDate,$endDate){
    $machineUtilityArray=[];
    
      
    $productionRunModel=ClassRegistry::init('ProductionRun');
		$productionRunIds=$productionRunModel->getProductionRunIdsForMachineAndPeriod($machineId,$startDate,$endDate);
    
    $productionMovementModel=ClassRegistry::init('ProductionMovement');
    $productionMovementData=$productionMovementModel->getProductionMovementDataForProductionRuns($productionRunIds);
   
    $stockItemModel=ClassRegistry::init('StockItem');
    foreach ($productionMovementData['outputTotals'] as $rawMaterialId=>$productData){
      foreach ($productData as $productId=>$productionResultCodeData){
        foreach ($productionResultCodeData as $productionResultCodeId=>$productionData){
          $productionMovementData['outputTotals'][$rawMaterialId][$productId][$productionResultCodeId]['stockMovementData']=$stockItemModel->getStockMovementDataForUtility($productionData['stockItemIds']);
        }  
      }
    }
    
    $machineUtilityArray=$productionMovementData;
    //pr($machineUtilityArray);
    return $machineUtilityArray;
  }
  
  public $validate = [
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];

  public $belongsTo = [
		'Plant' => [
			'className' => 'Plant',
			'foreignKey' => 'plant_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'ProductionRun' => [
			'className' => 'ProductionRun',
			'foreignKey' => 'machine_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
		'MachineProduct' => [
			'className' => 'MachineProduct',
			'foreignKey' => 'machine_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
	];

}
