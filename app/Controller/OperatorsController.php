<?php
App::uses('AppController', 'Controller');

class OperatorsController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$operatorCount=$this->Operator->find('count');
		$this->Paginator->settings = [
      'contain'=>'Plant',
			'order'=>'Operator.bool_active DESC, Operator.name ASC',
			'limit'=>($operatorCount!=0?$operatorCount:1)
		];
		$this->set('operators', $this->Paginator->paginate());
		
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

	public function view($id = null) {
		if (!$this->Operator->exists($id)) {
			throw new NotFoundException(__('Invalid operator'));
		}
		
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('ProductionMovement');
    
		$this->loadModel('Machine');
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
	
    $productionRunConditions=[
      'ProductionRun.operator_id'=>$id,
      'ProductionRun.production_run_date >='=> $startDate,
      'ProductionRun.production_run_date <'=> $endDatePlusOne
    ];
  
		$options = [
			'conditions' => ['Operator.id'  => $id],
			'contain'=>[
				'Plant', 
				'ProductionRun'=>[
					'ProductionMovement',
					'RawMaterial',
					'FinishedProduct',
					'Machine',
					'Shift',
					'conditions' => $productionRunConditions,
					'order'=>'production_run_date DESC',
				]
			]
		];
		
		$operator=$this->Operator->find('first', $options);
		//pr($operator);
		$energyConsumption=array();
		foreach ($operator['ProductionRun'] as $productionRun){
			//pr($productionRun);
			$energyConsumption[$productionRun['id']]=$this->ProductionRun->getEnergyUseForRun($productionRun['id']);
		}
		//pr($energyConsumption);
		
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
		]);
		$rawMaterials=$this->Product->find('all',[
			'fields'=>['Product.id','Product.name'],
			'conditions'=>['Product.product_type_id'=>$rawProductTypeIds],
		]);
    
		$productionResultCodes=$this->ProductionResultCode->find('all',array('fields'=>array('ProductionResultCode.id', 'ProductionResultCode.code')));
		
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
					$quantityForProductInMonth=$this->ProductionMovement->find('first',array(
						'fields'=>array('ProductionMovement.product_id', 'SUM(ProductionMovement.product_quantity) AS product_total','SUM(ProductionMovement.product_quantity*ProductionMovement.product_unit_price) AS total_value'),
						'conditions'=>[
							'ProductionMovement.production_run_id'=>$productionRunIds,
              'ProductionMovement.production_result_code_id'=>$productionResultCode['ProductionResultCode']['id'],
						],
						'group'=>'ProductionMovement.product_id',
					));
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
		
		$producedProductsPerMachine=array();
		$this->Machine->recursive=-1;
		$machines=$this->Machine->find('all',array('fields'=>array('Machine.id','Machine.name')));
		$machineCounter=0;
		foreach ($machines as $machine){
			$rawMaterialCounter=0;
			
			foreach ($rawMaterials as $rawMaterial){
				if ($rawMaterialsUse[$rawMaterial['Product']['id']]>0){
					$producedProductsPerMachine[$machineCounter]['machine_id']=$machine['Machine']['id'];
					$producedProductsPerMachine[$machineCounter]['machine_name']=$machine['Machine']['name'];
					$producedProductsPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_id']=$rawMaterial['Product']['id'];
					$producedProductsPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['raw_material_name']=$rawMaterial['Product']['name'];
					$productCounter=0;
					foreach ($finishedProducts as $finishedProduct){
						$arrayForProduct=array();
            
            $machineProductionRunConditions=$productionRunConditions;
            $machineProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
            $machineProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
            $machineProductionRunConditions['ProductionRun.machine_id']=$machine['Machine']['id'];
            $productionRunIds=$this->ProductionRun->find('list',[
              'fields'=>['id'],
              'conditions'=>$machineProductionRunConditions,
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
								$producedProductsPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=$quantityForProductInMonth[0]['product_total'];
								//$producedProductPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['total_value']=$quantityForProductInMonth[0]['total_value'];
							}
							else {
								$producedProductsPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_id']=$finishedProduct['Product']['id'];
								$producedProductsPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['finished_product_name']=$finishedProduct['Product']['name'];
								$producedProductsPerMachine[$machineCounter]['rawmaterial'][$rawMaterialCounter]['products'][$productCounter]['product_quantity'][$productionResultCode['ProductionResultCode']['id']]=0;
							}
						}
						$productCounter++;
					}
				}
				$rawMaterialCounter++;
			}
			$machineCounter++;
		}
		
		//pr($producedProductsPerMachine);
		
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
		foreach ($producedProductsPerMachine as $producedProductPerMachine){
			foreach ($producedProductPerMachine['rawmaterial'] as $producedProductPerMachineAndRawMaterial){
				foreach ($producedProductPerMachineAndRawMaterial['products'] as $finishedProduct){
					foreach ($finishedProduct['product_quantity'] as $quantity){
						if ($quantity>0){
							$visibleArray[$producedProductPerMachineAndRawMaterial['raw_material_id']][$finishedProduct['finished_product_id']]['visible']=1;
						}
					}
				}
			}
		}
		
		//pr($visibleArray);
		
		
		$producedProductsPerShift=array();
		$this->Shift->recursive=-1;
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
		
		$this->set(compact('operator','producedProducts','startDate','endDate','productionResultCodes','producedProductsPerMachine','producedProductsPerShift','visibleArray','rawMaterialsUse','energyConsumption'));
		
		$this->Operator->recursive=-1;
		$otherOperators=$this->Operator->find('all',array(
			'fields'=>array('Operator.id','Operator.name'),
			'conditions'=>array(
				'Operator.id !='=>$id,
				'Operator.bool_active'=>true,
			),
			'order'=>'Operator.name ASC',
		));
		$this->set(compact('otherOperators'));
		
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

	public function reporteProduccionTotal() {
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
		elseif (!empty($this->params['named']['sort'])){
			$startDate=$_SESSION['startDateReporteProduccionTotal'];
			$endDate=$_SESSION['endDateReporteProduccionTotal'];
			$endDatePlusOne=date( "Y-m-d", strtotime( $endDate."+1 days" ) );
		}

		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
				
		$_SESSION['startDateReporteProduccionTotal']=$startDate;
		$_SESSION['endDateReporteProduccionTotal']=$endDate;
		$this->set(compact('startDate','endDate'));

		$this->loadModel('ProductionResultCode');
		$this->ProductionResultCode->recursive=-1;
		
		$this->loadModel('Machine');
		$this->loadModel('Shift');
		$this->Operator->recursive=-1;
		$this->Machine->recursive=-1;
		$this->Shift->recursive=-1;
		
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionRun');
		$this->ProductionMovement->recursive=-1;
		$this->ProductionRun->recursive=-1;
				
		$productionResultCodes=$this->ProductionResultCode->find('all',array(
			'fields'=>array('ProductionResultCode.id', 'ProductionResultCode.code'),
			'order'=>'ProductionResultCode.code ASC',
		));
		$this->set(compact('productionResultCodes'));
		
		$operators=$this->Operator->find('all',array(
			'fields'=>array('Operator.id','Operator.name'),
			'order'=>'Operator.name',
		));
		
		for ($i=0;$i<count($operators);$i++){
			$selectedProductionRuns=$this->ProductionRun->find('list',array(
				'fields'=>'ProductionRun.id',
				'conditions'=>array(
					'ProductionRun.production_run_date >='=> $startDate,
					'ProductionRun.production_run_date <'=> $endDatePlusOne,
					'ProductionRun.operator_id'=>$operators[$i]['Operator']['id'],
				),
			));
			for ($j=0;$j<count($productionResultCodes);$j++){
				$this->ProductionMovement->virtualFields['product_total']=0;
				$productsForOperator=$this->ProductionMovement->find('first',array(
					'fields'=>array(
						'SUM(ProductionMovement.product_quantity) AS ProductionMovement__product_total',
					),	
					'conditions'=>array(
						'ProductionMovement.production_run_id'=> $selectedProductionRuns,
						'ProductionMovement.bool_input'=>'0',
						'ProductionMovement.production_result_code_id'=>$productionResultCodes[$j]['ProductionResultCode']['id'],
					),
				));
				if (!empty($productsForOperator['ProductionMovement']['product_total'])){
					$productionResultCodes[$j]['total_produced']=$productsForOperator['ProductionMovement']['product_total'];
				}
				else {
					$productionResultCodes[$j]['total_produced']=0;
				}
			}
			$operators[$i]['productionresultcodes']=$productionResultCodes;
		}
		//pr($operators);
		$this->set(compact('operators'));
		
		$machines=$this->Machine->find('all',array(
			'fields'=>array('Machine.id','Machine.name'),
			'order'=>'Machine.name',
		));
		
		for ($i=0;$i<count($machines);$i++){
			$selectedProductionRuns=$this->ProductionRun->find('list',array(
				'fields'=>'ProductionRun.id',
				'conditions'=>array(
					'ProductionRun.production_run_date >='=> $startDate,
					'ProductionRun.production_run_date <'=> $endDatePlusOne,
					'ProductionRun.machine_id'=>$machines[$i]['Machine']['id'],
				),
			));
			for ($j=0;$j<count($productionResultCodes);$j++){
				$this->ProductionMovement->virtualFields['product_total']=0;
				$productsForMachine=$this->ProductionMovement->find('first',array(
					'fields'=>array(
						'SUM(ProductionMovement.product_quantity) AS ProductionMovement__product_total',
					),	
					'conditions'=>array(
						'ProductionMovement.production_run_id'=> $selectedProductionRuns,
						'ProductionMovement.bool_input'=>'0',
						'ProductionMovement.production_result_code_id'=>$productionResultCodes[$j]['ProductionResultCode']['id'],
					),
				));
				if (!empty($productsForMachine['ProductionMovement']['product_total'])){
					$productionResultCodes[$j]['total_produced']=$productsForMachine['ProductionMovement']['product_total'];
				}
				else {
					$productionResultCodes[$j]['total_produced']=0;
				}
			}
			$machines[$i]['productionresultcodes']=$productionResultCodes;
		}
		//pr($machines);
		$this->set(compact('machines'));
		
		$shifts=$this->Shift->find('all',array(
			'fields'=>array('Shift.id','Shift.name'),
			'order'=>'Shift.name',
		));
		
		for ($i=0;$i<count($shifts);$i++){
			$selectedProductionRuns=$this->ProductionRun->find('list',array(
				'fields'=>'ProductionRun.id',
				'conditions'=>array(
					'ProductionRun.production_run_date >='=> $startDate,
					'ProductionRun.production_run_date <'=> $endDatePlusOne,
					'ProductionRun.shift_id'=>$shifts[$i]['Shift']['id'],
				),
			));
			for ($j=0;$j<count($productionResultCodes);$j++){
				$this->ProductionMovement->virtualFields['product_total']=0;
				$productsForShift=$this->ProductionMovement->find('first',array(
					'fields'=>array(
						'SUM(ProductionMovement.product_quantity) AS ProductionMovement__product_total',
					),	
					'conditions'=>array(
						'ProductionMovement.production_run_id'=> $selectedProductionRuns,
						'ProductionMovement.bool_input'=>'0',
						'ProductionMovement.production_result_code_id'=>$productionResultCodes[$j]['ProductionResultCode']['id'],
					),
				));
				if (!empty($productsForShift['ProductionMovement']['product_total'])){
					$productionResultCodes[$j]['total_produced']=$productsForShift['ProductionMovement']['product_total'];
				}
				else {
					$productionResultCodes[$j]['total_produced']=0;
				}
			}
			$shifts[$i]['productionresultcodes']=$productionResultCodes;
		}
		//pr($shifts);
		$this->set(compact('shifts'));
		
	}


	public function add() {
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
		if ($this->request->is('post')) {
			$this->Operator->create();
			if ($this->Operator->save($this->request->data)) {
				$this->recordUserAction($this->Operator->id,null,null);
				$this->Session->setFlash(__('The operator has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The operator could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
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

	public function edit($id = null) {
		if (!$this->Operator->exists($id)) {
			throw new NotFoundException(__('Invalid operator'));
		}
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Operator->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The operator has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The operator could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('Operator.' . $this->Operator->primaryKey => $id));
			$this->request->data = $this->Operator->find('first', $options);
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

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Operator->id = $id;
		if (!$this->Operator->exists()) {
			throw new NotFoundException(__('Invalid operator'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Operator->delete()) {
			$this->Session->setFlash(__('The operator has been deleted.'));
		} else {
			$this->Session->setFlash(__('The operator could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
