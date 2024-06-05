<?php
App::uses('AppController', 'Controller');

class ShiftsController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->Shift->recursive = 0;
		$this->set('shifts', $this->Paginator->paginate());
		
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
		if (!$this->Shift->exists($id)) {
			throw new NotFoundException(__('Invalid shift'));
		}

		$this->loadModel('Product');
    $this->loadModel('ProductType');
    $this->loadModel('ProductionResultCode');
		$this->loadModel('ProductionRun');
		$this->loadModel('ProductionMovement');
    
    $this->loadModel('Machine');
		$this->loadModel('Operator');
		
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
      'ProductionRun.shift_id'=>$id,
      'ProductionRun.production_run_date >='=> $startDate,
      'ProductionRun.production_run_date <'=> $endDatePlusOne
    ];
  
		$options = [
			'conditions' => ['Shift.id' => $id],
			'contain'=>[
				'ProductionRun'=>[
					'ProductionMovement',
					'RawMaterial',
					'FinishedProduct',
					'Machine',
					'Operator',
					'conditions' => $productionRunConditions,
					'order'=>'production_run_date DESC',
				]
			]
		];
		$shift=$this->Shift->find('first', $options);
		
		$energyConsumption=[];
		foreach ($shift['ProductionRun'] as $productionRun){
			//pr($productionRun);
			$energyConsumption[$productionRun['id']]=$this->ProductionRun->getEnergyUseForRun($productionRun['id']);
		}
		
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
    
		$productionResultCodes=$this->ProductionResultCode->find('all',['fields'=>['ProductionResultCode.id', 'ProductionResultCode.code']]);
		
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
		
		
		$producedProductsPerMachine=array();
		$machines=$this->Machine->find('all',['fields'=>['Machine.id','Machine.name']]);
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
						$arrayForProduct=[];
            
            $machineProductionRunConditions=$productionRunConditions;
            $machineProductionRunConditions['ProductionRun.finished_product_id']=$finishedProduct['Product']['id'];
            $machineProductionRunConditions['ProductionRun.raw_material_id']=$rawMaterial['Product']['id'];
            $machineProductionRunConditions['ProductionRun.machine_id']=$machine['Machine']['id'];
            $productionRunIds=$this->ProductionRun->find('list',[
              'fields'=>['id'],
              'conditions'=>$machineProductionRunConditions,
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
		$producedProductsPerOperator=[];
		$operators=$this->Operator->find('all',['fields'=>['Operator.id','Operator.name']]);
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
            //if ($operator['Operator']['id']==29){
            //  pr($productionRunConditions);
            //  pr($productionRunIds);
            //}
            
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
		//pr($producedProductsPerShift);
		
		$this->set(compact('operator','producedProducts','startDate','endDate','productionResultCodes','producedProductsPerMachine','producedProductsPerOperator','visibleArray','rawMaterialsUse','energyConsumption'));
		
		$this->set(compact('shift'));
		
		
		$otherShifts=$this->Shift->find('all',[
			'fields'=>['Shift.id','Shift.name'],
			'conditions'=>['Shift.id !='=>$id],
		]);
		$this->set(compact('otherShifts'));
		
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

	public function add() {
		if ($this->request->is('post')) {
			$this->Shift->create();
			if ($this->Shift->save($this->request->data)) {
				$this->recordUserAction($this->Shift->id,null,null);
				$this->Session->setFlash(__('The shift has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The shift could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
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
		if (!$this->Shift->exists($id)) {
			throw new NotFoundException(__('Invalid shift'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Shift->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The shift has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The shift could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('Shift.' . $this->Shift->primaryKey => $id));
			$this->request->data = $this->Shift->find('first', $options);
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
		$this->Shift->id = $id;
		if (!$this->Shift->exists()) {
			throw new NotFoundException(__('Invalid shift'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Shift->delete()) {
			$this->Session->setFlash(__('The shift has been deleted.'));
		} else {
			$this->Session->setFlash(__('The shift could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
