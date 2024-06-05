<?php
App::uses('AppController', 'Controller');

class MachinesController extends AppController {

	public $components = ['Paginator'];

	public function index() {
    $machineCount=$this->Machine->find('count');
		$this->Paginator->settings = [
			'contain'=>'Plant',
      'order'=>'Machine.bool_active DESC, Machine.name ASC',
			'limit'=>($machineCount!=0?$machineCount:1)
		];
		$machines=$this->Paginator->paginate();
		$this->set(compact('machines'));
    $userRoleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $startDate= date("2014-09-01");
    $endDate=date("Y-m-d",strtotime(date("Y-m-d")));
		
    for ($m=0;$m<count($machines);$m++){
      $machines[$m]['machineUtility']=$this->Machine->getMachineUtility($machines[$m]['Machine']['id'],$startDate,$endDate);
    }
    $this->set(compact('machines'));
    
    $this->loadModel('ProductType');
    $this->loadModel('Product');
    $this->loadModel('ProductionResultCode');
		$producedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_PRODUCED]
    ]);
    $rawProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW]
    ]);
		$finishedProductList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$producedProductTypeIds],
      'order'=>'Product.name'
		]);
    $this->set(compact('finishedProductList'));
		$rawMaterialList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$rawProductTypeIds],
      'order'=>'Product.name'
		]);
    $this->set(compact('rawMaterialList'));
		$productionResultCodeList=$this->ProductionResultCode->find('list',array(
			'fields'=>array(
				'ProductionResultCode.id', 'ProductionResultCode.code'
			),
		));
    $this->set(compact('productionResultCodeList'));
    
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
	}

	public function resumen() {
    $machineCount=$this->Machine->find('count');
		$this->Paginator->settings = [
			'contain'=>'Plant',
      'order'=>'Machine.bool_active DESC, Machine.name ASC',
			'limit'=>($machineCount!=0?$machineCount:1)
		];
		$machines=$this->Paginator->paginate();
		$this->set(compact('machines'));
    $userRoleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    $startDate= date("2014-09-01");
    $endDate=date("Y-m-d",strtotime(date("Y-m-d")));
		
    for ($m=0;$m<count($machines);$m++){
      $machines[$m]['machineUtility']=$this->Machine->getMachineUtility($machines[$m]['Machine']['id'],$startDate,$endDate);
    }
    $this->set(compact('machines'));
    //pr($machines);
    
    $this->loadModel('ProductType');
    $this->loadModel('Product');
    $this->loadModel('ProductionResultCode');
		$producedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_PRODUCED]
    ]);
    $rawProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW]
    ]);
		$finishedProductList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$producedProductTypeIds],
      'order'=>'Product.name'
		]);
    $this->set(compact('finishedProductList'));
    //pr($finishedProductList);
    $otherProductList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id !='=>$producedProductTypeIds],
      'order'=>'Product.name'
		]);
    //pr($otherProductList);
    $this->set(compact('otherProductList'));
		$rawMaterialList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$rawProductTypeIds],
      'order'=>'Product.name'
		]);
    $this->set(compact('rawMaterialList'));
		$productionResultCodeList=$this->ProductionResultCode->find('list',[
			'fields'=>[
				'ProductionResultCode.id', 'ProductionResultCode.code'
			],
		]);
    $this->set(compact('productionResultCodeList'));
    
		$aco_name="ProductionRuns/resumen";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/crear";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/editar";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
	}

	public function view($id = null) {
		if (!$this->Machine->exists($id)) {
			throw new NotFoundException(__('Invalid machine'));
		}

    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('ProductionMovement');
    
		$this->loadModel('Operator');
    $this->loadModel('Shift');
    
		$this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->ProductionResultCode->recursive=-1;
		$this->ProductionRun->recursive=-1;
    $this->ProductionMovement->recursive=-1;
   
    $this->Machine->recursive=-1;
    $this->Operator->recursive=-1;
    $this->Shift->recursive=-1;
				
		$startDate = null;
		$endDate = null;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate'));
    
    $machineUtility=$this->Machine->getMachineUtility($id,$startDate,$endDate);
    $this->set(compact('machineUtility'));
    
    $productionRunConditions=[
      'ProductionRun.machine_id'=>$id,
      'ProductionRun.production_run_date >='=> $startDate,
      'ProductionRun.production_run_date <'=> $endDatePlusOne
    ];
    
		$options = [
			'conditions' => ['Machine.id' => $id],
			'contain'=>[
        'Plant', 
				'ProductionRun'=>[
					'ProductionMovement',
					'RawMaterial',
					'FinishedProduct',
					'Operator',
					'Shift',
					'conditions' => $productionRunConditions,
					'order'=>'production_run_date DESC',
				]
			]
		];
		$machine=$this->Machine->find('first', $options);
		//pr($machine);
		
		$energyConsumption=array();
		foreach ($machine['ProductionRun'] as $productionRun){
			//pr($productionRun);
			$energyConsumption[$productionRun['id']]=$this->ProductionRun->getEnergyUseForRun($productionRun['id']);
		}
		$this->set(compact('machine','energyConsumption'));
		
		$producedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_PRODUCED]
    ]);
    $rawProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW]
    ]);
		$finishedProducts=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$producedProductTypeIds],
      'order'=>'Product.name'
		]);
    $finishedProductList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$producedProductTypeIds],
      'order'=>'Product.name'
		]);
    $this->set(compact('finishedProductList'));
		$rawMaterials=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$rawProductTypeIds],
      'order'=>'Product.name'
		]);
    $rawMaterialList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$rawProductTypeIds],
      'order'=>'Product.name'
		]);
    $this->set(compact('rawMaterialList'));
		$productionResultCodes=$this->ProductionResultCode->find('all',array(
			'fields'=>array(
				'ProductionResultCode.id', 'ProductionResultCode.code'
			),
		));
    $productionResultCodeList=$this->ProductionResultCode->find('list',array(
			'fields'=>array(
				'ProductionResultCode.id', 'ProductionResultCode.code'
			),
		));
    $this->set(compact('productionResultCodeList'));
		
		$producedProducts=array();
		$rawMaterialsUse=array();
		$producedProductCounter=0;
		foreach ($rawMaterials as $rawMaterial){
			$rawMaterialProductCounter=0;
			foreach ($finishedProducts as $finishedProduct){
				$productCounterForProduct=0;
				$valueCounterForProduct=0;
				$arrayForProduct=array();
        
        $productProductionRunConditions=$productionRunConditions;
        $productProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
        $productProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
        $productionRunIds=$this->ProductionRun->find('list',[
          'fields'=>['id'],
          'conditions'=>$productProductionRunConditions,
        ]);
        
				foreach ($productionResultCodes as $productionResultCode){
					$quantityForProductInMonth=$this->ProductionMovement->find('first',[
						'fields'=>array('ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'),
						'conditions'=>[
							'ProductionMovement.production_run_id'=>$productionRunIds,
							'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
						],
						'group'=>'ProductionMovement.product_id',
					]);
					if (!empty($quantityForProductInMonth)){
						$productCounterForProduct+=$quantityForProductInMonth[0]['product_total'];
						$valueCounterForProduct+=$quantityForProductInMonth[0]['total_value'];
						$arrayForProduct[$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
					}
				}
				if ($productCounterForProduct>0){
					$producedProducts[$producedProductCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProducts[$producedProductCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$producedProducts[$producedProductCounter]['finished_product_id']=$finishedProduct['Product']['id'];
					$producedProducts[$producedProductCounter]['finished_product_name']=$finishedProduct['Product']['name'];
					$producedProducts[$producedProductCounter]['produced_quantities']=$arrayForProduct;
					$producedProducts[$producedProductCounter]['total_value']=$valueCounterForProduct;
					$rawMaterialProductCounter+=3;
					$producedProductCounter++;
					
				}
			}
			if ($rawMaterialProductCounter>0){
				$rawMaterialsUse[$rawMaterial['Product']['id']]=$rawMaterialProductCounter;
			}
			else {
				$rawMaterialsUse[$rawMaterial['Product']['id']]=0;
			}
		}
		//pr($producedProducts);
		//pr($rawMaterialsUse);
		
		$producedProductsPerOperator=array();
		$operators=$this->Operator->find('all',array(
			'fields'=>array('Operator.id','Operator.name'),
		));
		$operatorCounter=0;
		foreach ($operators as $operator){
			$rawMaterialCounter=0;
			foreach ($rawMaterials as $rawMaterial){
				if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
					$producedProductsPerOperator[$operatorCounter]['operator_id']=$operator['Operator']['id'];
					$producedProductsPerOperator[$operatorCounter]['operator_name']=$operator['Operator']['name'];
					$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$productCounter=0;
					foreach ($finishedProducts as $finishedProduct){
						$arrayForProduct=array();
            
            $operatorProductionRunConditions=$productionRunConditions;
            $operatorProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
            $operatorProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
            $operatorProductionRunConditions['ProductionRun.operator_id']=$operator['Operator']['id'];
            $productionRunIds=$this->ProductionRun->find('list',[
              'fields'=>['id'],
              'conditions'=>$operatorProductionRunConditions,
            ]);
            
						foreach ($productionResultCodes as $productionResultCode){
							$quantityForProductInMonth=$this->ProductionMovement->find('first',array(
								'fields'=>array('ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'),
								'conditions'=>[
									'ProductionMovement.production_run_id'=>$productionRunIds,
                  'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
								],
								'group'=>'ProductionMovement.product_id',
							));
							if (!empty($quantityForProductInMonth)){
								//$valueCounterForProduct+=$quantityForProductInMonth[0]['total_value'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
								//$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['total_value']=$quantityForProductInMonth[0]['total_value'];
							}
							else {
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=0;
							}
						}
						$productCounter++;
					}
				}
				$rawMaterialCounter++;
			}
			$operatorCounter++;
		}
		
		//pr($producedProductsPerOperator);
		
		$visibleArray=array();
		
		//initialize		
		foreach ($rawMaterials as $rawMaterial){
			if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
				foreach ($finishedProducts as $finishedProduct){
					foreach ($productionResultCodes as $productionResultCode){
						$visibleArray[$rawMaterial['Product']['id']][$finishedProduct['Product']['id']]['visible']=0;
					}
				}
			}
		}
		// set visual products to 1
		
		foreach ($producedProductsPerOperator as $producedProductPerOperator){
			foreach ($producedProductPerOperator['rawmaterial'] as $producedProductPerOperatorAndRawMaterial){
				foreach ($producedProductPerOperatorAndRawMaterial['products'] as $finishedProduct){
					foreach ($finishedProduct['product_quantity'] as $quantity){
						if ($quantity>0){
							$visibleArray[$producedProductPerOperatorAndRawMaterial['raw_material_id']][$finishedProduct['finished_product_id']]['visible']=1;
						}
					}
				}
			}
		}
		//pr($visibleArray);
		
		$producedProductsPerShift=array();
		$shifts=$this->Shift->find('all',array('fields'=>array('Shift.id','Shift.name')));
		$shiftCounter=0;
		foreach ($shifts as $shift){
			$rawMaterialCounter=0;
			foreach ($rawMaterials as $rawMaterial){
				if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
					$producedProductsPerShift[$shiftCounter]['shift_id']=$shift['Shift']['id'];
					$producedProductsPerShift[$shiftCounter]['shift_name']=$shift['Shift']['name'];
					$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$productCounter=0;
					foreach ($finishedProducts as $finishedProduct){
						$arrayForProduct=array();
            
            $shiftProductionRunConditions=$productionRunConditions;
            $shiftProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
            $shiftProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
            $shiftProductionRunConditions['ProductionRun.shift_id']=$shift['Shift']['id'];
            $productionRunIds=$this->ProductionRun->find('list',[
              'fields'=>['id'],
              'conditions'=>$shiftProductionRunConditions,
            ]);
            
						foreach ($productionResultCodes as $productionResultCode){
							
							$quantityForProductInMonth=$this->ProductionMovement->find('first',array(
								'fields'=>array('ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'),
								'conditions'=>array(
									'ProductionMovement.production_run_id'=>$productionRunIds,
									'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
								),
								'group'=>'ProductionMovement.product_id',
							));
							if (!empty($quantityForProductInMonth)){
								//$valueCounterForProduct+=$quantityForProductInMonth[0]['total_value'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
								//$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['total_value']=$quantityForProductInMonth[0]['total_value'];
							}
							else {
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=0;
							}
						}
						$productCounter++;
					}
				}
				$rawMaterialCounter++;
			}
			$shiftCounter++;
		}
		
		//pr($producedProductsPerShift);
		
		$this->set(compact('producedProducts','productionResultCodes','producedProductsPerOperator','producedProductsPerShift','visibleArray','rawMaterialsUse'));
		
		$this->Machine->recursive=-1;
		$otherMachines=$this->Machine->find('all',array(
			'fields'=>array('Machine.id','Machine.name'),
			'conditions'=>array('Machine.id !='=>$id),
		));
		$this->set(compact('otherMachines'));
		
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
	}

  public function detalle($id = null) {
		if (!$this->Machine->exists($id)) {
			throw new NotFoundException(__('Invalid machine'));
		}

    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('ProductionMovement');
    
		$this->loadModel('Operator');
    $this->loadModel('Shift');
    
		$this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		$this->ProductionResultCode->recursive=-1;
		$this->ProductionRun->recursive=-1;
    $this->ProductionMovement->recursive=-1;
   
    $this->Machine->recursive=-1;
    $this->Operator->recursive=-1;
    $this->Shift->recursive=-1;
				
		$startDate = null;
		$endDate = null;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate'));
    
    $machineUtility=$this->Machine->getMachineUtility($id,$startDate,$endDate);
    $this->set(compact('machineUtility'));
    
    $productionRunConditions=[
      'ProductionRun.machine_id'=>$id,
      'ProductionRun.production_run_date >='=> $startDate,
      'ProductionRun.production_run_date <'=> $endDatePlusOne
    ];
    
		$options = [
			'conditions' => ['Machine.id' => $id],
			'contain'=>[
        'Plant', 
				'ProductionRun'=>[
					'ProductionMovement',
					'RawMaterial',
					'FinishedProduct',
					'Operator',
					'Shift',
					'conditions' => $productionRunConditions,
					'order'=>'production_run_date DESC',
				],
			],
		];
		$machine=$this->Machine->find('first', $options);
		//pr($machine);
		
		$energyConsumption=[];
		foreach ($machine['ProductionRun'] as $productionRun){
			//pr($productionRun);
			$energyConsumption[$productionRun['id']]=$this->ProductionRun->getEnergyUseForRun($productionRun['id']);
		}
		$this->set(compact('machine','energyConsumption'));
		
		$producedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_PRODUCED]
    ]);
    $rawProductTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW]
    ]);
		$finishedProducts=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$producedProductTypeIds],
      'order'=>'Product.name'
		]);
    $finishedProductList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$producedProductTypeIds],
      'order'=>'Product.name'
		]);
    $this->set(compact('finishedProductList'));
		$rawMaterials=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$rawProductTypeIds],
      'order'=>'Product.name'
		]);
    $rawMaterialList=$this->Product->find('list',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$rawProductTypeIds],
      'order'=>'Product.name'
		]);
    $this->set(compact('rawMaterialList'));
		$productionResultCodes=$this->ProductionResultCode->find('all',[
			'fields'=>[
				'ProductionResultCode.id', 'ProductionResultCode.code'
			],
		]);
    $productionResultCodeList=$this->ProductionResultCode->find('list',[
			'fields'=>[
				'ProductionResultCode.id', 'ProductionResultCode.code'
			],
		]);
    $this->set(compact('productionResultCodeList'));
		
		$producedProducts=[];
		$rawMaterialsUse=[];
		$producedProductCounter=0;
		foreach ($rawMaterials as $rawMaterial){
			$rawMaterialProductCounter=0;
			foreach ($finishedProducts as $finishedProduct){
				$productCounterForProduct=0;
				$valueCounterForProduct=0;
				$arrayForProduct=[];
        
        $productProductionRunConditions=$productionRunConditions;
        $productProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
        $productProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
        $productionRunIds=$this->ProductionRun->find('list',[
          'fields'=>['id'],
          'conditions'=>$productProductionRunConditions,
        ]);
        
				foreach ($productionResultCodes as $productionResultCode){
					$quantityForProductInMonth=$this->ProductionMovement->find('first',[
						'fields'=>['ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'],
						'conditions'=>[
							'ProductionMovement.production_run_id'=>$productionRunIds,
							'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
						],
						'group'=>'ProductionMovement.product_id',
					]);
					if (!empty($quantityForProductInMonth)){
						$productCounterForProduct+=$quantityForProductInMonth[0]['product_total'];
						$valueCounterForProduct+=$quantityForProductInMonth[0]['total_value'];
						$arrayForProduct[$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
					}
				}
				if ($productCounterForProduct>0){
					$producedProducts[$producedProductCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProducts[$producedProductCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$producedProducts[$producedProductCounter]['finished_product_id']=$finishedProduct['Product']['id'];
					$producedProducts[$producedProductCounter]['finished_product_name']=$finishedProduct['Product']['name'];
					$producedProducts[$producedProductCounter]['produced_quantities']=$arrayForProduct;
					$producedProducts[$producedProductCounter]['total_value']=$valueCounterForProduct;
					$rawMaterialProductCounter+=3;
					$producedProductCounter++;
					
				}
			}
			if ($rawMaterialProductCounter>0){
				$rawMaterialsUse[$rawMaterial['Product']['id']]=$rawMaterialProductCounter;
			}
			else {
				$rawMaterialsUse[$rawMaterial['Product']['id']]=0;
			}
		}
		//pr($producedProducts);
		//pr($rawMaterialsUse);
		
		$producedProductsPerOperator=[];
		$operators=$this->Operator->find('all',[
			'fields'=>['Operator.id','Operator.name'],
		]);
		$operatorCounter=0;
		foreach ($operators as $operator){
			$rawMaterialCounter=0;
			foreach ($rawMaterials as $rawMaterial){
				if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
					$producedProductsPerOperator[$operatorCounter]['operator_id']=$operator['Operator']['id'];
					$producedProductsPerOperator[$operatorCounter]['operator_name']=$operator['Operator']['name'];
					$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$productCounter=0;
					foreach ($finishedProducts as $finishedProduct){
						$arrayForProduct=[];
            
            $operatorProductionRunConditions=$productionRunConditions;
            $operatorProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
            $operatorProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
            $operatorProductionRunConditions['ProductionRun.operator_id']=$operator['Operator']['id'];
            $productionRunIds=$this->ProductionRun->find('list',[
              'fields'=>['id'],
              'conditions'=>$operatorProductionRunConditions,
            ]);
            
						foreach ($productionResultCodes as $productionResultCode){
							$quantityForProductInMonth=$this->ProductionMovement->find('first',[
								'fields'=>['ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'],
								'conditions'=>[
									'ProductionMovement.production_run_id'=>$productionRunIds,
                  'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
								],
								'group'=>'ProductionMovement.product_id',
							]);
							if (!empty($quantityForProductInMonth)){
								//$valueCounterForProduct+=$quantityForProductInMonth[0]['total_value'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
								//$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['total_value']=$quantityForProductInMonth[0]['total_value'];
							}
							else {
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerOperator[$operatorCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=0;
							}
						}
						$productCounter++;
					}
				}
				$rawMaterialCounter++;
			}
			$operatorCounter++;
		}
		
		//pr($producedProductsPerOperator);
		
		$visibleArray=[];
		
		//initialize		
		foreach ($rawMaterials as $rawMaterial){
			if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
				foreach ($finishedProducts as $finishedProduct){
					foreach ($productionResultCodes as $productionResultCode){
						$visibleArray[$rawMaterial['Product']['id']][$finishedProduct['Product']['id']]['visible']=0;
					}
				}
			}
		}
		// set visual products to 1
		
		foreach ($producedProductsPerOperator as $producedProductPerOperator){
			foreach ($producedProductPerOperator['rawmaterial'] as $producedProductPerOperatorAndRawMaterial){
				foreach ($producedProductPerOperatorAndRawMaterial['products'] as $finishedProduct){
					foreach ($finishedProduct['product_quantity'] as $quantity){
						if ($quantity>0){
							$visibleArray[$producedProductPerOperatorAndRawMaterial['raw_material_id']][$finishedProduct['finished_product_id']]['visible']=1;
						}
					}
				}
			}
		}
		//pr($visibleArray);
		
		$producedProductsPerShift=[];
		$shifts=$this->Shift->find('all',['fields'=>['Shift.id','Shift.name']]);
		$shiftCounter=0;
		foreach ($shifts as $shift){
			$rawMaterialCounter=0;
			foreach ($rawMaterials as $rawMaterial){
				if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
					$producedProductsPerShift[$shiftCounter]['shift_id']=$shift['Shift']['id'];
					$producedProductsPerShift[$shiftCounter]['shift_name']=$shift['Shift']['name'];
					$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$productCounter=0;
					foreach ($finishedProducts as $finishedProduct){
						$arrayForProduct=[];
            
            $shiftProductionRunConditions=$productionRunConditions;
            $shiftProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
            $shiftProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
            $shiftProductionRunConditions['ProductionRun.shift_id']=$shift['Shift']['id'];
            $productionRunIds=$this->ProductionRun->find('list',[
              'fields'=>['id'],
              'conditions'=>$shiftProductionRunConditions,
            ]);
            
						foreach ($productionResultCodes as $productionResultCode){
							
							$quantityForProductInMonth=$this->ProductionMovement->find('first',[
								'fields'=>['ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'],
								'conditions'=>[
									'ProductionMovement.production_run_id'=>$productionRunIds,
									'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
								],
								'group'=>'ProductionMovement.product_id',
							]);
							if (!empty($quantityForProductInMonth)){
								//$valueCounterForProduct+=$quantityForProductInMonth[0]['total_value'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
								//$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['total_value']=$quantityForProductInMonth[0]['total_value'];
							}
							else {
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerShift[$shiftCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=0;
							}
						}
						$productCounter++;
					}
				}
				$rawMaterialCounter++;
			}
			$shiftCounter++;
		}
		
		//pr($producedProductsPerShift);
		
		$this->set(compact('producedProducts','productionResultCodes','producedProductsPerOperator','producedProductsPerShift','visibleArray','rawMaterialsUse'));
		
		$this->Machine->recursive=-1;
		$otherMachines=$this->Machine->find('all',[
			'fields'=>['Machine.id','Machine.name'],
			'conditions'=>['Machine.id !='=>$id],
		]);
		$this->set(compact('otherMachines'));
		
		$aco_name="ProductionRuns/resumen";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/crear";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/editar";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
	}

  public function add() {
		$this->loadModel('ProductType');
		$this->loadModel('Product');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
		
		$this->ProductType->recursive=-1;
		$producedProductTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_PRODUCED,
			),
		));
		
		$this->Product->recursive=-1;
		$products = $this->Product->find('all',array(
			'fields'=>array('Product.id','Product.name'),
			'conditions'=>array(
				'Product.product_type_id'=>$producedProductTypes,
			),
			'order'=>'Product.name',
		));
		$this->set(compact('products'));
	
		if ($this->request->is('post')) {
			$datasource=$this->Machine->getDataSource();
			try {
				$datasource->begin();
				$product_id=0;
				
				$this->Machine->create();
				if (!$this->Machine->save($this->request->data)) {
					echo "Problema guardando la máquina";
					pr($this->validateErrors($this->Machine));
					throw new Exception();
				}
				$machine_id=$this->Machine->id;
				for ($pr=0;$pr<count($this->request->data['Product']);$pr++){
					if ($this->request->data['Product'][$pr]['product_id']){
						$machineProductArray=array();
						$this->Machine->MachineProduct->create();
						
						$machineProductArray['MachineProduct']['machine_id']=$machine_id;
						$machineProductArray['MachineProduct']['product_id']=$products[$pr]['Product']['id'];
						if (!$this->Machine->MachineProduct->save($machineProductArray)){
							pr($this->validateErrors($this->MachineProduct));
							echo "Problema guardando el producto para la máquina";
							throw new Exception();
						}
					}
				}
				$datasource->commit();
				$this->recordUserAction($this->Machine->id,null,null);
				$this->Session->setFlash(__('The machine has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('The machine could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
	}

	public function crear() {
		$this->loadModel('ProductType');
    $this->loadModel('ProductionType');
		$this->loadModel('Product');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plantId=0;
    
		if ($this->request->is('post')) {
      $plantId=$this->request->data['Machine']['plant_id'];  
			$datasource=$this->Machine->getDataSource();
      if (empty($this->request->data['plantSelection'])){  
        try {
          $datasource->begin();
          $product_id=0;
          
          $this->Machine->create();
          if (!$this->Machine->save($this->request->data)) {
            echo "Problema guardando la máquina";
            pr($this->validateErrors($this->Machine));
            throw new Exception();
          }
          $machineId=$this->Machine->id;
          for ($pr=0;$pr<count($this->request->data['Product']);$pr++){
            if ($this->request->data['Product'][$pr]['bool_product']){
              $machineProductArray=[];
              $this->Machine->MachineProduct->create();
              
              $machineProductArray['MachineProduct']['machine_id']=$machineId;
              $machineProductArray['MachineProduct']['product_id']=$this->request->data['Product'][$pr]['product_id'];
              if (!$this->Machine->MachineProduct->save($machineProductArray)){
                pr($this->validateErrors($this->MachineProduct));
                echo "Problema guardando el producto para la máquina";
                throw new Exception();
              }
            }
            
          }
          $datasource->commit();
          $this->recordUserAction($this->Machine->id,null,null);
          $this->Session->setFlash(__('The machine has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('The machine could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }        
      }
    }
		
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
		
    if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    elseif (count($plants) > 1 && $plantId == 0){
      if (!empty($_SESSION['plantId'])){
        $plantId = $_SESSION['plantId'];
      }
      else {
        $plantId=0;
      }
    }
    $_SESSION['plantId']=$plantId;
    $this->set(compact('plantId'));
    
    $productTypeConditions=[
      'ProductType.product_category_id'=>CATEGORY_PRODUCED,
    ];
		$producedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>$productTypeConditions,
		]);
		$productConditions=[
      'Product.product_type_id'=>$producedProductTypeIds,
    ];
    
    if ($plantId > 0){
      $productionTypes=$this->ProductionType->getProductionTypesForPlant($plantId);
      $productConditions['Product.production_type_id']=array_keys($productionTypes);
    }
    $products = $this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>$productConditions,
      'recursive'=>-1,
			'order'=>'Product.name',
	  ]);
		$this->set(compact('products'));
    
		$aco_name="ProductionRuns/resumen";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/crear";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/editar";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
	}

  public function edit($id = null) {
		if (!$this->Machine->exists($id)) {
			throw new NotFoundException(__('Invalid machine'));
		}
    
		$this->loadModel('ProductType');
		$this->loadModel('Product');
		
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
		$this->ProductType->recursive=-1;
		$producedProductTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_PRODUCED,
			),
		));
		
		$this->Product->recursive=-1;
		$products = $this->Product->find('all',array(
			'fields'=>array('Product.id','Product.name'),
			'conditions'=>array(
				'Product.product_type_id'=>$producedProductTypes,
			),
			'contain'=>array(
				'MachineProduct'=>array(
					'conditions'=>array(
						'MachineProduct.machine_id'=>$id,
					)
				),
			),
			'order'=>'Product.name',
		));
		//pr($products);
		$this->set(compact('products'));
		if ($this->request->is(array('post', 'put'))) {
			$datasource=$this->Machine->getDataSource();
			try {
				$datasource->begin();
				
				$this->Machine->MachineProduct->recursive=-1;
				$previousMachineProducts=$this->Machine->MachineProduct->find('all',array(
					'fields'=>array('MachineProduct.id'),
					'conditions'=>array(
						'MachineProduct.machine_id'=>$id,
					),
				));
				if (!empty($previousMachineProducts)){
					foreach ($previousMachineProducts as $previousMachineProduct){
						$this->Machine->MachineProduct->id=$previousMachineProduct['MachineProduct']['id'];
						$this->Machine->MachineProduct->delete($previousMachineProduct['MachineProduct']['id']);
					}
				}
				
				$this->Machine->id=$id;
				if (!$this->Machine->save($this->request->data)) {
					echo "Problema guardando la máquina";
					pr($this->validateErrors($this->Machine));
					throw new Exception();
				}
				$machine_id=$this->Machine->id;
				for ($pr=0;$pr<count($this->request->data['Product']);$pr++){
					if ($this->request->data['Product'][$pr]['product_id']){
						$machineProductArray=array();
						$this->Machine->MachineProduct->create();
						
						$machineProductArray['MachineProduct']['machine_id']=$machine_id;
						$machineProductArray['MachineProduct']['product_id']=$products[$pr]['Product']['id'];
						if (!$this->Machine->MachineProduct->save($machineProductArray)){
							pr($this->validateErrors($this->MachineProduct));
							echo "Problema guardando el producto para la máquina";
							throw new Exception();
						}
					}
				}
				$datasource->commit();
				$this->recordUserAction($this->Machine->id,null,null);
				$this->Session->setFlash(__('The machine has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('The machine could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('Machine.' . $this->Machine->primaryKey => $id));
			$this->request->data = $this->Machine->find('first', $options);
		}
		
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/edit";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
	}

	public function editar($id = null) {
		if (!$this->Machine->exists($id)) {
			throw new NotFoundException(__('Invalid machine'));
		}
    
		$this->loadModel('ProductType');
    $this->loadModel('ProductionType');
		$this->loadModel('Product');
		
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plantId=0;
    
		if ($this->request->is(['post', 'put'])) {
			$plantId=$this->request->data['Machine']['plant_id'];  
      if (empty($this->request->data['plantSelection'])){      
        $datasource=$this->Machine->getDataSource();
        try {
          $datasource->begin();
          $this->Machine->MachineProduct->recursive=-1;
          $previousMachineProducts=$this->Machine->MachineProduct->find('all',[
            'fields'=>['MachineProduct.id'],
            'conditions'=>[
              'MachineProduct.machine_id'=>$id,
            ],
          ]);
          if (!empty($previousMachineProducts)){
            foreach ($previousMachineProducts as $previousMachineProduct){
              $this->Machine->MachineProduct->id=$previousMachineProduct['MachineProduct']['id'];
            }
          }
          
          $this->Machine->id=$id;
          if (!$this->Machine->save($this->request->data)) {
            echo "Problema guardando la máquina";
            pr($this->validateErrors($this->Machine));
            throw new Exception();
          }
          $machineId=$this->Machine->id;
          for ($pr=0;$pr<count($this->request->data['Product']);$pr++){
            $this->Machine->MachineProduct->create();
            $machineProductArray=[
              'MachineProduct'=>[
                'assignment_datetime'=>date('Y-m-d H:i:s'),
                'bool_assigned'=>$this->request->data['Product'][$pr]['bool_product'],
                'machine_id'=>$machineId,
                'product_id'=>$this->request->data['Product'][$pr]['product_id'],
              ]
            ];
            
            if (!$this->Machine->MachineProduct->save($machineProductArray)){
              pr($this->validateErrors($this->MachineProduct));
              echo "Problema guardando el producto para la máquina";
              throw new Exception();
            }
          }
          $datasource->commit();
          $this->recordUserAction($this->Machine->id,null,null);
          $this->Session->setFlash(__('The machine has been saved.'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen']);
        } 
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('The machine could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
        }
        
      }
    } 
		else {
			$options = ['conditions' => ['Machine.id' => $id]];
			$this->request->data = $this->Machine->find('first', $options);
      $plantId=$this->request->data['Machine']['plant_id'];
		}
		
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
		if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    elseif (count($plants) > 1 && $plantId == 0){
      if (!empty($_SESSION['plantId'])){
        $plantId = $_SESSION['plantId'];
      }
      else {
        $plantId=0;
      }
    }
    $_SESSION['plantId']=$plantId;
    $this->set(compact('plantId'));
    //echo 'plant id is '.$plantId.'<br/>';
    $productTypeConditions=[
      'ProductType.product_category_id'=>CATEGORY_PRODUCED,
    ];
		$producedProductTypeIds=$this->ProductType->find('list',[
      'fields'=>'ProductType.id',
      'conditions'=>$productTypeConditions,
		]);
		$productConditions=[
      'Product.product_type_id'=>$producedProductTypeIds,
    ];
    
    if ($plantId > 0){
      $productionTypes=$this->ProductionType->getProductionTypesForPlant($plantId);
      $productConditions['Product.production_type_id']=array_keys($productionTypes);
    }
		$products = $this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>$productConditions,
			'contain'=>[
				'MachineProduct'=>[
          'fields'=>['MachineProduct.bool_assigned'],
					'conditions'=>[
						'MachineProduct.machine_id'=>$id,
					],
          'order'=>'MachineProduct.id DESC',
				],
			],
			'order'=>'Product.name',
		]);
		//pr($products);
		$this->set(compact('products'));
    
    
		$aco_name="ProductionRuns/resumen";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/crear";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
		$aco_name="ProductionRuns/editar";		
		$bool_productionrun_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_edit_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Machine->id = $id;
		if (!$this->Machine->exists()) {
			throw new NotFoundException(__('Invalid machine'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Machine->delete()) {
			$this->Session->setFlash(__('The machine has been deleted.'));
		} else {
			$this->Session->setFlash(__('The machine could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
