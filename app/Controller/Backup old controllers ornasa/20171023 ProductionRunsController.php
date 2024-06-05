<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ProductionRunsController extends AppController {

	public $components = array('Paginator','RequestHandler');
	//public $helpers = array('PhpExcel','InventoryCountDisplay');
	public $helpers = array('PhpExcel');

	public function beforeFilter(){
		parent::beforeFilter();
		// Allow users to register and logout.
		$this->Auth->allow('getrawmaterialid');
	}
	
	public function index() {
		$startDate = null;
		$endDate = null;
		$selectedProductId=0;
		$selectedShiftId=0;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$selectedProductId=$this->request->data['Report']['finished_product_id'];
			$selectedShiftId=$this->request->data['Report']['shift_id'];
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
		$this->set(compact('selectedProductId'));
		$this->set(compact('selectedShiftId'));
		
		$productionRunConditions=array(
			'ProductionRun.production_run_date >='=> $startDate,
			'ProductionRun.production_run_date <'=> $endDatePlusOne,
		);
		if ($selectedProductId>0){
			$productionRunConditions[]=array('ProductionRun.finished_product_id'=> $selectedProductId);
		}
		if ($selectedShiftId>0){
			$productionRunConditions[]=array('ProductionRun.shift_id'=> $selectedShiftId);
		}
		
		$productionRunCount=$this->ProductionRun->find('count', array(
			'conditions' =>$productionRunConditions,
		));
		
		$this->Paginator->settings = array(
			'conditions' => $productionRunConditions,
			'contain'=>array(
				'Operator',
				'Shift',
				'Machine',
				'RawMaterial',
				'FinishedProduct'=>array(
					'ProductProduction'=>array(
						'conditions'=>array(
							'ProductProduction.application_date <'=> $endDatePlusOne,
						),
					),
				),
				'ProductionMovement',
			),
			
			'order'=>'production_run_date DESC, production_run_code DESC',
			'limit'=>($productionRunCount!=0?$productionRunCount:1)
		);
		
		$this->loadModel('ProductionResultCode');
		$this->ProductionResultCode->recursive=-1;
		$resultCodes = $this->ProductionResultCode->find('all');
		
		$productionRuns=$this->Paginator->paginate();
		$this->set(compact('productionRuns'));
		
		$shiftConditions=array(
			'ProductionRun.production_run_date >='=> $startDate,
			'ProductionRun.production_run_date <'=> $endDatePlusOne,
			'ProductionRun.bool_annulled'=> false,
		);
		if ($selectedProductId>0){
			$shiftConditions[]=array('ProductionRun.finished_product_id'=> $selectedProductId);
		}
		$this->ProductionRun->Shift->recursive=-1;
		$shiftTotals=$this->ProductionRun->Shift->find('all',array(
			'fields'=>array('Shift.id','Shift.name'),
			'contain'=>array(
				'ProductionRun'=>array(
					'fields'=>array('ProductionRun.id','ProductionRun.production_run_date'),
					'conditions' => $shiftConditions,
				),
			),
		));
		
		for ($s=0;$s<count($shiftTotals);$s++){
			if (!empty($shiftTotals[$s]['ProductionRun'])){
				$acceptableRunCounter=0;
				for ($p=0;$p<count($shiftTotals[$s]['ProductionRun']);$p++){
					$boolAcceptable=$this->ProductionRun->checkAcceptableProduction($shiftTotals[$s]['ProductionRun'][$p]['id'],$shiftTotals[$s]['ProductionRun'][$p]['production_run_date']);
					$shiftTotals[$s]['ProductionRun'][$p]['bool_acceptable']=$boolAcceptable;
					if ($boolAcceptable){
						$acceptableRunCounter++;
					}
				}
				$shiftTotals[$s]['Shift']['acceptable_runs']=$acceptableRunCounter;
			}
			else {
				$shiftTotals[$s]['Shift']['acceptable_runs']=0;
			}
		}
		//pr($shiftTotals);
		$this->set(compact('shiftTotals'));
    
    $machineConditions=array(
			'ProductionRun.production_run_date >='=> $startDate,
			'ProductionRun.production_run_date <'=> $endDatePlusOne,
			'ProductionRun.bool_annulled'=> false,
		);
		if ($selectedProductId>0){
			$machineConditions[]=array('ProductionRun.finished_product_id'=> $selectedProductId);
		}
		$this->ProductionRun->Machine->recursive=-1;
		$machineTotals=$this->ProductionRun->Machine->find('all',array(
			'fields'=>array('Machine.id','Machine.name'),
      'conditions'=>array(
        'Machine.bool_active'=>true,
      ),
			'contain'=>array(
				'ProductionRun'=>array(
					'fields'=>array('ProductionRun.id','ProductionRun.production_run_date'),
					'conditions' => $machineConditions,
          'ProductionMovement'=>array(
            'conditions'=>array(
              'ProductionMovement.product_quantity >'=>0,
              'ProductionMovement.bool_input'=>false,
            )
          )
				),
			),
      'order'=>'Machine.name ASC',
		));
		
		for ($s=0;$s<count($machineTotals);$s++){
			if (!empty($machineTotals[$s]['ProductionRun'])){
        $quantityA=0;
        $quantityB=0;
        $quantityC=0;
				$acceptableRunCounter=0;
				for ($p=0;$p<count($machineTotals[$s]['ProductionRun']);$p++){
          foreach ($machineTotals[$s]['ProductionRun'][$p]['ProductionMovement'] as $productionMovement){
            //pr($productionMovement);
            switch ($productionMovement['production_result_code_id']){
              case 1:
                $quantityA+=$productionMovement['product_quantity'];
                break;
              case 2:
                $quantityB+=$productionMovement['product_quantity'];
                break;
              case 3:
                $quantityC+=$productionMovement['product_quantity'];
                break;
            }
          }
          $boolAcceptable=$this->ProductionRun->checkAcceptableProduction($machineTotals[$s]['ProductionRun'][$p]['id'],$machineTotals[$s]['ProductionRun'][$p]['production_run_date']);
					$machineTotals[$s]['ProductionRun'][$p]['bool_acceptable']=$boolAcceptable;
					if ($boolAcceptable){
						$acceptableRunCounter++;
					}
          //pr($machineTotals[$s]);
				}
				$machineTotals[$s]['Machine']['quantity_A']=$quantityA;
        $machineTotals[$s]['Machine']['quantity_B']=$quantityB;
        $machineTotals[$s]['Machine']['quantity_C']=$quantityC;
        $machineTotals[$s]['Machine']['acceptable_runs']=$acceptableRunCounter;
			}
			else {
        $machineTotals[$s]['Machine']['quantity_A']=0;
        $machineTotals[$s]['Machine']['quantity_B']=0;
        $machineTotals[$s]['Machine']['quantity_C']=0;
				$machineTotals[$s]['Machine']['acceptable_runs']=0;
			}
		}
		//pr($machineTotals);
		$this->set(compact('machineTotals'));
   
    
		$productionRunIDs=$this->ProductionRun->find('all', array(
			'fields'=>array('ProductionRun.id'),
			'conditions' => $productionRunConditions,
		));
		$energyUsePerProductionRun=array();
		foreach ($productionRunIDs as $productionRunID){
			$energyUsePerProductionRun[$productionRunID['ProductionRun']['id']]=$this->ProductionRun->getEnergyUseForRun($productionRunID['ProductionRun']['id']);
		}
		$this->set(compact('resultCodes','energyUsePerProductionRun'));
		
		
		
		//$this->loadModel('ProductType');
		//$finishedProductTypes=$this->ProductType->find('list',array(
		//	'fields'=>array('ProductType.id'),
		//	'conditions'=>array(
		//		'ProductType.product_category_id'=>CATEGORY_PRODUCED,
		//	),
		//));
		$finishedProductsPresentInProductionRuns=$this->ProductionRun->find('list',array(
			'fields'=>array('ProductionRun.finished_product_id'),
			'conditions'=>array(
				'ProductionRun.production_run_date >='=> $startDate,
				'ProductionRun.production_run_date <'=> $endDatePlusOne,
			),
		));
		$this->loadModel('Product');
		$finishedProducts=$this->Product->find('list',array(
			'conditions'=>array(
				//'Product.product_type_id'=>$finishedProductTypes,
				'Product.id'=>$finishedProductsPresentInProductionRuns,
			),
			'order'=>'Product.name ASC',
		));
		$this->set(compact('finishedProducts'));
		
		$productionRunConditions=array(
			'ProductionRun.production_run_date >='=> $startDate,
			'ProductionRun.production_run_date <'=> $endDatePlusOne,
			'ProductionRun.bool_annulled'=> false,
		);
		if ($selectedProductId>0){
			$productionRunConditions[]=array('ProductionRun.finished_product_id'=> $selectedProductId);
		}
		$shiftIdsInProductionRuns=$this->ProductionRun->find('list',array(
			'fields'=>array('ProductionRun.shift_id'),
			'conditions' => $productionRunConditions,
		));
		$this->ProductionRun->Shift->recursive=-1;
		$shifts=$this->ProductionRun->Shift->find('list',array(
			'fields'=>array('Shift.id','Shift.name'),
			'conditions' => array(
				'Shift.id'=>$shiftIdsInProductionRuns,
			),
		));
		$this->set(compact('shifts'));
		
		$aco_name="Operators/reporteProduccionTotal";		
		$bool_operator_totalproductionreport_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_totalproductionreport_permission'));
		
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
		$aco_name="Machines/index";		
		$bool_machine_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_machine_index_permission'));
		$aco_name="Machines/add";		
		$bool_machine_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_machine_add_permission'));
		$aco_name="Operators/index";		
		$bool_operator_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_index_permission'));
		$aco_name="Operators/add";		
		$bool_operator_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_add_permission'));
		$aco_name="Shifts/index";		
		$bool_shift_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_shift_index_permission'));
		$aco_name="Shifts/add";		
		$bool_shift_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_shift_add_permission'));
	}

	public function guardarResumenOrdenesDeProduccion() {
		$exportData=$_SESSION['resumenOrdenesProduccion'];
		$this->set(compact('exportData'));
	}
	public function view($id = null) {
		if (!$this->ProductionRun->exists($id)) {
			throw new NotFoundException(__('Invalid production run'));
		}
		
		$this->ProductionRun->recursive=-1;
		$options = array(
			'conditions' => array(
				'ProductionRun.id' => $id,
			),
			'contain'=>array(
				'Machine',
				'Operator',
				'Shift',
				'RawMaterial',
				'FinishedProduct',
				'ProductionMovement'=>array(
					'StockItem',
				),
			),
		);
		$productionRun=$this->ProductionRun->find('first', $options);
		$this->set(compact('productionRun'));
		$productid=$productionRun['ProductionRun']['raw_material_id'];
		$quantityneeded=$productionRun['ProductionRun']['raw_material_quantity'];
		
		$this->loadModel('StockItem');
		$this->loadModel('ProductType');
		$productcategoryid=CATEGORY_RAW;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		//$usedRawMaterials= $this->StockItem->getRawMaterialsForProductionRun($productid,$quantityneeded);
		$energyConsumption=$this->ProductionRun->getEnergyUseForRun($id);
		//$this->set(compact('rawMaterialsInventory','usedRawMaterials'));
		$this->set(compact('rawMaterialsInventory','energyConsumption'));
		
		$aco_name="Operators/reporteProduccionTotal";		
		$bool_operator_totalproductionreport_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_totalproductionreport_permission'));
		
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
		$aco_name="Machines/index";		
		$bool_machine_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_machine_index_permission'));
		$aco_name="Machines/add";		
		$bool_machine_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_machine_add_permission'));
		$aco_name="Operators/index";		
		$bool_operator_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_index_permission'));
		$aco_name="Operators/add";		
		$bool_operator_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_add_permission'));
		$aco_name="Shifts/index";		
		$bool_shift_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_shift_index_permission'));
		$aco_name="Shifts/add";		
		$bool_shift_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_shift_add_permission'));
	}

	public function viewPdf($id = null) {
		$this->ProductionRun->recursive=2;
		if (!$this->ProductionRun->exists($id)) {
			throw new NotFoundException(__('Invalid production run'));
		}
		$options = array('conditions' => array('ProductionRun.' . $this->ProductionRun->primaryKey => $id));
		$this->set('productionRun',$this->ProductionRun->find('first', $options));
		$energyConsumption=$this->ProductionRun->getEnergyUseForRun($id);
		$this->set(compact('energyConsumption'));
		//header("Content-type: application/pdf");
	}

	public function getrawmaterialid($id){
	    $this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->loadModel('Product');
		if (!$id){
			throw new NotFoundException(__('Product id not present'));
		}
		if (!$this->Product->exists($id)) {
			throw new NotFoundException(__('Invalid product'));
		}
		
		$product=$this->Product->find('first',array('conditions'=>array('Product.id'=>$id)));
		return $product['Product']['preferred_raw_material_id'];
	}
	
	public function manipularProduccion(){
		$requestData=$_SESSION['productionRunRequestData'];
		
		$this->LoadModel('Machine');
		$this->LoadModel('Operator');
		$this->LoadModel('Shift');
		
		$this->LoadModel('Product');
		$this->LoadModel('StockItem');
		$this->LoadModel('ProductionResultCode');
		
		$this->loadModel('ProductType');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		$this->loadModel('ProductionMovement');
		
		$this->Machine->recursive=-1;
		$this->Operator->recursive=-1;
		$this->Shift->recursive=-1;
		$this->Product->recursive=-1;
		
		$relatedMachine=$this->Machine->find('first',array('conditions'=>array('Machine.id'=>$requestData['ProductionRun']['machine_id'])));
		$requestData['ProductionRun']['machine_name']=$relatedMachine['Machine']['name'];
		$relatedOperator=$this->Operator->find('first',array('conditions'=>array('Operator.id'=>$requestData['ProductionRun']['operator_id'])));
		$requestData['ProductionRun']['operator_name']=$relatedOperator['Operator']['name'];
		$relatedShift=$this->Shift->find('first',array('conditions'=>array('Shift.id'=>$requestData['ProductionRun']['shift_id'])));
		$requestData['ProductionRun']['shift_name']=$relatedShift['Shift']['name'];
		
		$relatedRawMaterial=$this->Product->find('first',array(
			'conditions'=>array(
				'Product.id'=>$requestData['ProductionRun']['raw_material_id'],
			),
		));
		$requestData['ProductionRun']['raw_material_name']=$relatedRawMaterial['Product']['name'];
		$relatedFinishedProduct=$this->Product->find('first',array('conditions'=>array('Product.id'=>$requestData['ProductionRun']['finished_product_id'])));
		$requestData['ProductionRun']['finished_product_name']=$relatedFinishedProduct['Product']['name'];
		
		//pr($requestData);
		
		$rawMaterialId=$requestData['ProductionRun']['raw_material_id'];
		$finishedProductId=$requestData['ProductionRun']['finished_product_id'];
		$productionRunDateAsString=$this->ProductionRun->deconstruct('production_run_date',$requestData['ProductionRun']['production_run_date']);
		
		
		$quantityRawMaterialInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($rawMaterialId,$productionRunDateAsString);
		$quantityFinishedCInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($finishedProductId,$rawMaterialId,PRODUCTION_RESULT_CODE_C,$productionRunDateAsString);
		$quantityFinishedBInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($finishedProductId,$rawMaterialId,PRODUCTION_RESULT_CODE_B,$productionRunDateAsString);
		
		$resultCodes = $this->ProductionResultCode->find('all');
		
		if ($this->request->is('post')) {	
			//pr($this->request->data);
			//reclassify C
      //$reclassificationDate=$this->request->data['ProductionRun']['production_run_date'];
      //$reclassificationDateString=$reclassificationDate['year'].'-'.$reclassificationDate['month'].'-'.$reclassificationDate['day'];
      $reclassificationDate=$reclassificationDateString=$this->request->data['ProductionRun']['production_run_date'];
      $reclassificationDateTimeString=$this->request->data['ProductionRun']['production_run_date']." 08:00:00";
      $reclassificationDatePlusOne=date("Y-m-d",strtotime($reclassificationDateString."+1 days"));
      //pr($this->request->data); 
      $this->request->data['ProductionRun']['comment']= str_replace("[carriagereturn]","\r",$this->request->data['ProductionRun']['comment']);
      $this->request->data['ProductionRun']['comment']= str_replace("[newline]","\n",$this->request->data['ProductionRun']['comment']);
      $reclassificationComment=$this->request->data['ProductionRun']['reclassification_comment'];
      //pr($this->request->data);
      $boolReclassificationSuccess=true;
			if ($this->request->data['ProductionRun']['manipulation_action']==2){
				$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
				$allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));				
				$productionResultCodes=$this->ProductionResultCode->find('list',array('conditions'=>array('id !='=>PRODUCTION_RESULT_CODE_A)));
				
				$bottle_id=$this->request->data['ProductionRun']['finished_product_id'];
				$preforma_id=$this->request->data['ProductionRun']['raw_material_id'];
				$original_production_result_code_id=PRODUCTION_RESULT_CODE_C;
				$target_preforma_id=$this->request->data['ProductionRun']['raw_material_id'];
				$quantity_bottles=$this->request->data['ProductionRun']['reclassified_C'];
        
        //echo "quantity bottles is ".$quantity_bottles."<br/>";
					
				$lastReclassification=$this->StockMovement->find('first',array(
					'fields'=>array('StockMovement.reclassification_code'),
					'conditions'=>array(
						'bool_reclassification'=>true,
					),
					'order'=>array('StockMovement.reclassification_code' => 'desc'),
				));
				$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
				$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_AUTO_".$this->Session->read('User.username');
					
				$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
				$newlyCreatedStockItems=array();
				//pr($usedBottleStockItems);			
				$datasource=$this->StockItem->getDataSource();
				$datasource->begin();
				try{
					foreach ($usedBottleStockItems as $usedBottleStockItem){
						$stockitem_id=$usedBottleStockItem['id'];
						$quantity_present=$usedBottleStockItem['quantity_present'];
						$quantity_used=$usedBottleStockItem['quantity_used'];
						$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
						$unit_price=$usedBottleStockItem['unit_price'];
						if (!$this->StockItem->exists($stockitem_id)) {
							throw new NotFoundException(__('Invalid StockItem'));
						}
						$linkedStockItem=$this->StockItem->find('first',array(
              'conditions'=>array(
                'StockItem.id'=>$stockitem_id,
              ),
            ));
						$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$allPreformas[$target_preforma_id]." on ".date("d")."-".date("m")."-".date("Y");
						
						// STEP 1: EDIT THE STOCKITEM OF ORIGIN
						$StockItemData=array();
						$StockItemData['id']=$stockitem_id;
						$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
						$StockItemData['remaining_quantity']=$quantity_remaining;
						
						if (!$this->StockItem->save($StockItemData)) {
							echo "problema al editor el lote de origen";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
						
									
						// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
						$StockMovementData=array();
						$StockMovementData['movement_date']=$reclassificationDateTimeString;
						$StockMovementData['bool_input']=false;
						$StockMovementData['name']=$message;
						$StockMovementData['description']=$message;
						$StockMovementData['order_id']=0;
						$StockMovementData['stockitem_id']=$stockitem_id;
						$StockMovementData['product_id']=$bottle_id;
						$StockMovementData['product_quantity']=$quantity_used;
						$StockMovementData['product_unit_price']=$unit_price;
						$StockMovementData['product_total_price']=$unit_price*$quantity_used;
						$StockMovementData['production_result_code_id']=$original_production_result_code_id;
						$StockMovementData['bool_reclassification']=true;
						$StockMovementData['reclassification_code']=$reclassificationCode;
            $StockMovementData['comment']=$reclassificationComment;
						
						$this->StockMovement->create();
						if (!$this->StockMovement->save($StockMovementData)) {
							echo "problema al guardar el movimiento de lote";
							pr($this->validateErrors($this->StockMovement));
							throw new Exception();
						}
						
						// STEP 3: SAVE THE TARGET STOCKITEM
						$StockItemData=array();
						$StockItemData['name']=$message;
						$StockItemData['description']=$message;
						$StockItemData['stockitem_creation_date']=$reclassificationDateString;
						$StockItemData['product_id']=$target_preforma_id;
						$StockItemData['product_unit_price']=$unit_price;
						$StockItemData['original_quantity']=$quantity_used;
						$StockItemData['remaining_quantity']=$quantity_used;
						$StockItemData['production_result_code_id']=0;
						$StockItemData['raw_material_id']=0;
						
						$this->StockItem->create();
						// notice that no new stockitem is created because we are taking from an already existing one
						if (!$this->StockItem->save($StockItemData)) {
							echo "problema al guardar el lote de destino";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
						
						
						// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
						$new_stockitem_id=$this->StockItem->id;
						$newlyCreatedStockItems[]=$new_stockitem_id;
						
						$origin_stock_movement_id=$this->StockMovement->id;
						
						$StockMovementData=array();
						$StockMovementData['movement_date']=$reclassificationDateTimeString;
						$StockMovementData['bool_input']=true;
						$StockMovementData['name']=$message;
						$StockMovementData['description']=$message;
						$StockMovementData['order_id']=0;
						$StockMovementData['stockitem_id']=$new_stockitem_id;
						$StockMovementData['product_id']=$target_preforma_id;
						$StockMovementData['product_quantity']=$quantity_used;
						$StockMovementData['product_unit_price']=$unit_price;
						$StockMovementData['product_total_price']=$unit_price*$quantity_used;
						$StockMovementData['production_result_code_id']=0;
						$StockMovementData['bool_reclassification']=true;
						$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
						$StockMovementData['reclassification_code']=$reclassificationCode;
            $StockMovementData['comment']=$reclassificationComment;
						
						$this->StockMovement->create();
						if (!$this->StockMovement->save($StockMovementData)) {
							echo "problema al guardar el movimiento de lote";
							pr($this->validateErrors($this->StockMovement));
							throw new Exception();
						}
								
						// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
						$this->recordUserActivity($this->Session->read('User.username'),$message);
					}
					$datasource->commit();
					
					foreach ($usedBottleStockItems as $usedBottleStockItem){
						$this->recreateStockItemLogs($usedBottleStockItem['id']);
					}
					for ($i=0;$i<count($newlyCreatedStockItems);$i++){
						$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
					}
					$this->Session->setFlash(__('Reclasificación exitosa'),'default',array('class' => 'success'));
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          $boolReclassificationSuccess=false;
				}
			}
		
			if ($this->request->data['ProductionRun']['manipulation_action']==4){
				$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
				$allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));				
				$productionResultCodes=$this->ProductionResultCode->find('list',array('conditions'=>array('id !='=>PRODUCTION_RESULT_CODE_A)));
				// reclassify C
				
				$bottle_id=$this->request->data['ProductionRun']['finished_product_id'];
				$preforma_id=$this->request->data['ProductionRun']['raw_material_id'];
				$original_production_result_code_id=PRODUCTION_RESULT_CODE_C;
				$target_preforma_id=$this->request->data['ProductionRun']['raw_material_id'];
				$quantity_bottles=$this->request->data['ProductionRun']['reclassified_C'];
					
				$lastReclassification=$this->StockMovement->find('first',array(
					'fields'=>array('StockMovement.reclassification_code'),
					'conditions'=>array(
						'bool_reclassification'=>true,
					),
					'order'=>array('StockMovement.reclassification_code' => 'desc'),
				));
				$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
				$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_AUTO_".$this->Session->read('User.username');
					
				$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
				$newlyCreatedStockItems=array();
							
				$datasource=$this->StockItem->getDataSource();
				$datasource->begin();
				try{
					foreach ($usedBottleStockItems as $usedBottleStockItem){
						$stockitem_id=$usedBottleStockItem['id'];
						$quantity_present=$usedBottleStockItem['quantity_present'];
						$quantity_used=$usedBottleStockItem['quantity_used'];
						$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
						$unit_price=$usedBottleStockItem['unit_price'];
						if (!$this->StockItem->exists($stockitem_id)) {
							throw new NotFoundException(__('Invalid StockItem'));
						}
						$linkedStockItem=$this->StockItem->find('first',array(
              'conditions'=>array(
                'StockItem.id'=>$stockitem_id,
              ),
            ));
						$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$allPreformas[$target_preforma_id]." on ".date("d")."-".date("m")."-".date("Y");
						
						// STEP 1: EDIT THE STOCKITEM OF ORIGIN
						$StockItemData=array();
						$StockItemData['id']=$stockitem_id;
						$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
						$StockItemData['remaining_quantity']=$quantity_remaining;
						
						$this->StockItem->clear();
						if (!$this->StockItem->save($StockItemData)) {
							echo "problema al editor el lote de origen";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
									
						// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
						$StockMovementData=array();
						$StockMovementData['movement_date']=$reclassificationDateTimeString;
						$StockMovementData['bool_input']=false;
						$StockMovementData['name']=$message;
						$StockMovementData['description']=$message;
						$StockMovementData['order_id']=0;
						$StockMovementData['stockitem_id']=$stockitem_id;
						$StockMovementData['product_id']=$bottle_id;
						$StockMovementData['product_quantity']=$quantity_used;
						$StockMovementData['product_unit_price']=$unit_price;
						$StockMovementData['product_total_price']=$unit_price*$quantity_used;
						$StockMovementData['production_result_code_id']=$original_production_result_code_id;
						$StockMovementData['bool_reclassification']=true;
						$StockMovementData['reclassification_code']=$reclassificationCode;
            $StockMovementData['comment']=$reclassificationComment;
						
						$this->StockMovement->create();
						if (!$this->StockMovement->save($StockMovementData)) {
							echo "problema al guardar el movimiento de lote";
							pr($this->validateErrors($this->StockMovement));
							throw new Exception();
						}
						
						// STEP 3: SAVE THE TARGET STOCKITEM
						$StockItemData=array();
						$StockItemData['name']=$message;
						$StockItemData['description']=$message;
						$StockItemData['stockitem_creation_date']=$reclassificationDate;
						$StockItemData['product_id']=$target_preforma_id;
						$StockItemData['product_unit_price']=$unit_price;
						$StockItemData['original_quantity']=$quantity_used;
						$StockItemData['remaining_quantity']=$quantity_used;
						$StockItemData['production_result_code_id']=0;
						$StockItemData['raw_material_id']=0;
						
						$this->StockItem->create();
						// notice that no new stockitem is created because we are taking from an already existing one
						if (!$this->StockItem->save($StockItemData)) {
							echo "problema al guardar el lote de destino";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
						
						// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
						$new_stockitem_id=$this->StockItem->id;
						$newlyCreatedStockItems[]=$new_stockitem_id;
						
						$origin_stock_movement_id=$this->StockMovement->id;
						
						$StockMovementData=array();
						$StockMovementData['movement_date']=$reclassificationDateTimeString;
						$StockMovementData['bool_input']=true;
						$StockMovementData['name']=$message;
						$StockMovementData['description']=$message;
						$StockMovementData['order_id']=0;
						$StockMovementData['stockitem_id']=$new_stockitem_id;
						$StockMovementData['product_id']=$target_preforma_id;
						$StockMovementData['product_quantity']=$quantity_used;
						$StockMovementData['product_unit_price']=$unit_price;
						$StockMovementData['product_total_price']=$unit_price*$quantity_used;
						$StockMovementData['production_result_code_id']=0;
						$StockMovementData['bool_reclassification']=true;
						$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
						$StockMovementData['reclassification_code']=$reclassificationCode;
            $StockMovementData['comment']=$reclassificationComment;
						
						$this->StockMovement->create();
						if (!$this->StockMovement->save($StockMovementData)) {
							echo "problema al guardar el movimiento de lote";
							pr($this->validateErrors($this->StockMovement));
							throw new Exception();
						}
								
						// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
						$this->recordUserActivity($this->Session->read('User.username'),$message);
					}
					$datasource->commit();
					
					foreach ($usedBottleStockItems as $usedBottleStockItem){
						$this->recreateStockItemLogs($usedBottleStockItem['id']);
					}
					for ($i=0;$i<count($newlyCreatedStockItems);$i++){
						$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
					}
					$this->Session->setFlash(__('Reclasificación exitosa'),'default',array('class' => 'success'));
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          $boolReclassificationSuccess=false;
				}
			}
		
			if ($this->request->data['ProductionRun']['manipulation_action']==5){
				$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
				$allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));				
				$productionResultCodes=$this->ProductionResultCode->find('list',array('conditions'=>array('id !='=>PRODUCTION_RESULT_CODE_A)));
				// reclassify C
				
				$bottle_id=$this->request->data['ProductionRun']['finished_product_id'];
				$preforma_id=$this->request->data['ProductionRun']['raw_material_id'];
				$original_production_result_code_id=PRODUCTION_RESULT_CODE_C;
				$target_preforma_id=$this->request->data['ProductionRun']['raw_material_id'];
				$quantity_bottles=$this->request->data['ProductionRun']['reclassified_C'];
					
				$lastReclassification=$this->StockMovement->find('first',array(
					'fields'=>array('StockMovement.reclassification_code'),
					'conditions'=>array(
						'bool_reclassification'=>true,
					),
					'order'=>array('StockMovement.reclassification_code' => 'desc'),
				));
				$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
				$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_AUTO_".$this->Session->read('User.username');
					
				$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
				$newlyCreatedStockItems=array();
							
				$datasource=$this->StockItem->getDataSource();
				$datasource->begin();
				try{
					if (!empty($usedBottleStockItems)){
						foreach ($usedBottleStockItems as $usedBottleStockItem){
							$stockitem_id=$usedBottleStockItem['id'];
							$quantity_present=$usedBottleStockItem['quantity_present'];
							$quantity_used=$usedBottleStockItem['quantity_used'];
							$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
							$unit_price=$usedBottleStockItem['unit_price'];
							if (!$this->StockItem->exists($stockitem_id)) {
								throw new NotFoundException(__('Invalid StockItem'));
							}
							$linkedStockItem=$this->StockItem->find('first',array(
                'conditions'=>array(
                  'StockItem.id'=>$stockitem_id,
                ),
              ));
							$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$allPreformas[$target_preforma_id]." on ".date("d")."-".date("m")."-".date("Y");
							
							// STEP 1: EDIT THE STOCKITEM OF ORIGIN
							$StockItemData=array();
							$StockItemData['id']=$stockitem_id;
							$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
							$StockItemData['remaining_quantity']=$quantity_remaining;
							
							if (!$this->StockItem->save($StockItemData)) {
								echo "problema al editor el lote de origen";
								pr($this->validateErrors($this->StockItem));
								throw new Exception();
							}
										
							// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
							$StockMovementData=array();
							$StockMovementData['movement_date']=$reclassificationDateTimeString;
							$StockMovementData['bool_input']=false;
							$StockMovementData['name']=$message;
							$StockMovementData['description']=$message;
							$StockMovementData['order_id']=0;
							$StockMovementData['stockitem_id']=$stockitem_id;
							$StockMovementData['product_id']=$bottle_id;
							$StockMovementData['product_quantity']=$quantity_used;
							$StockMovementData['product_unit_price']=$unit_price;
							$StockMovementData['product_total_price']=$unit_price*$quantity_used;
							$StockMovementData['production_result_code_id']=$original_production_result_code_id;
							$StockMovementData['bool_reclassification']=true;
							$StockMovementData['reclassification_code']=$reclassificationCode;
              $StockMovementData['comment']=$reclassificationComment;
							
							$this->StockMovement->create();
							if (!$this->StockMovement->save($StockMovementData)) {
								echo "problema al guardar el movimiento de lote";
								pr($this->validateErrors($this->StockMovement));
								throw new Exception();
							}
							
							// STEP 3: SAVE THE TARGET STOCKITEM
							$StockItemData=array();
							$StockItemData['name']=$message;
							$StockItemData['description']=$message;
							$StockItemData['stockitem_creation_date']=$reclassificationDate;
							$StockItemData['product_id']=$target_preforma_id;
							$StockItemData['product_unit_price']=$unit_price;
							$StockItemData['original_quantity']=$quantity_used;
							$StockItemData['remaining_quantity']=$quantity_used;
							$StockItemData['production_result_code_id']=0;
							$StockItemData['raw_material_id']=0;
							
							$this->StockItem->create();
							// notice that no new stockitem is created because we are taking from an already existing one
							if (!$this->StockItem->save($StockItemData)) {
								echo "problema al guardar el lote de destino";
								pr($this->validateErrors($this->StockItem));
								throw new Exception();
							}
							
							// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
							$new_stockitem_id=$this->StockItem->id;
							$newlyCreatedStockItems[]=$new_stockitem_id;
							
							$origin_stock_movement_id=$this->StockMovement->id;
							
							$StockMovementData=array();
							$StockMovementData['movement_date']=$reclassificationDateTimeString;
							$StockMovementData['bool_input']=true;
							$StockMovementData['name']=$message;
							$StockMovementData['description']=$message;
							$StockMovementData['order_id']=0;
							$StockMovementData['stockitem_id']=$new_stockitem_id;
							$StockMovementData['product_id']=$target_preforma_id;
							$StockMovementData['product_quantity']=$quantity_used;
							$StockMovementData['product_unit_price']=$unit_price;
							$StockMovementData['product_total_price']=$unit_price*$quantity_used;
							$StockMovementData['production_result_code_id']=0;
							$StockMovementData['bool_reclassification']=true;
							$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
							$StockMovementData['reclassification_code']=$reclassificationCode;
              $StockMovementData['comment']=$reclassificationComment;
							
							$this->StockMovement->create();
							if (!$this->StockMovement->save($StockMovementData)) {
								echo "problema al guardar el movimiento de lote";
								pr($this->validateErrors($this->StockMovement));
								throw new Exception();
							}
									
							// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
							$this->recordUserActivity($this->Session->read('User.username'),$message);
						}
											
						$datasource->commit();
						
						
						foreach ($usedBottleStockItems as $usedBottleStockItem){
							$this->recreateStockItemLogs($usedBottleStockItem['id']);
						}
						for ($i=0;$i<count($newlyCreatedStockItems);$i++){
							$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
						}
						
						$this->Session->setFlash(__('Reclasificación exitosa'),'default',array('class' => 'success'));
					}
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          $boolReclassificationSuccess=false;
				}
				
				$allPreformas=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_PREFORMA)));
				$allBottles=$this->Product->find('list',array('conditions'=>array('Product.product_type_id'=>PRODUCT_TYPE_BOTTLE)));				
				$productionResultCodes=$this->ProductionResultCode->find('list',array('conditions'=>array('id !='=>PRODUCTION_RESULT_CODE_A)));
				// reclassify B
				
				$bottle_id=$this->request->data['ProductionRun']['finished_product_id'];
				$preforma_id=$this->request->data['ProductionRun']['raw_material_id'];
				$original_production_result_code_id=PRODUCTION_RESULT_CODE_B;
				$target_preforma_id=$this->request->data['ProductionRun']['raw_material_id'];
				$quantity_bottles=$this->request->data['ProductionRun']['reclassified_B'];
				
				$lastReclassification=$this->StockMovement->find('first',array(
					'fields'=>array('StockMovement.reclassification_code'),
					'conditions'=>array(
						'bool_reclassification'=>true,
					),
					'order'=>array('StockMovement.reclassification_code' => 'desc'),
				));
				$reclassificationNumber=substr($lastReclassification['StockMovement']['reclassification_code'],6,6)+1;
				$reclassificationCode="RECLA_".str_pad($reclassificationNumber,6,"0",STR_PAD_LEFT)."_AUTO_".$this->Session->read('User.username');
				
				$usedBottleStockItems=$this->StockItem->getFinishedMaterialsForSale($bottle_id,$original_production_result_code_id,$quantity_bottles,$preforma_id,$reclassificationDatePlusOne,0,"DESC");
				$newlyCreatedStockItems=array();
							
				$datasource=$this->StockItem->getDataSource();
				$datasource->begin();
				try{
					foreach ($usedBottleStockItems as $usedBottleStockItem){
						$stockitem_id=$usedBottleStockItem['id'];
						$quantity_present=$usedBottleStockItem['quantity_present'];
						$quantity_used=$usedBottleStockItem['quantity_used'];
						$quantity_remaining=$usedBottleStockItem['quantity_remaining'];
						$unit_price=$usedBottleStockItem['unit_price'];
						if (!$this->StockItem->exists($stockitem_id)) {
							throw new NotFoundException(__('Invalid StockItem'));
						}
						$linkedStockItem=$this->StockItem->find('first',array(
              'conditions'=>array(
                'StockItem.id'=>$stockitem_id,
              ),
            ));
						$message="Reclassified ".$quantity_used." of ".$allPreformas[$preforma_id]." ".$allBottles[$bottle_id]." from ".$productionResultCodes[$original_production_result_code_id]." to ".$allPreformas[$target_preforma_id]." on ".date("d")."-".date("m")."-".date("Y");
						
						// STEP 1: EDIT THE STOCKITEM OF ORIGIN
						$StockItemData=array();
						$StockItemData['id']=$stockitem_id;
						$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
						$StockItemData['remaining_quantity']=$quantity_remaining;
						
						if (!$this->StockItem->save($StockItemData)) {
							echo "problema al editor el lote de origen";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
									
						// STEP 2: SAVE THE STOCK MOVEMENT FOR THE STOCKITEM OF ORIGIN
						$StockMovementData=array();
						$StockMovementData['movement_date']=$reclassificationDateTimeString;
						$StockMovementData['bool_input']=false;
						$StockMovementData['name']=$message;
						$StockMovementData['description']=$message;
						$StockMovementData['order_id']=0;
						$StockMovementData['stockitem_id']=$stockitem_id;
						$StockMovementData['product_id']=$bottle_id;
						$StockMovementData['product_quantity']=$quantity_used;
						$StockMovementData['product_unit_price']=$unit_price;
						$StockMovementData['product_total_price']=$unit_price*$quantity_used;
						$StockMovementData['production_result_code_id']=$original_production_result_code_id;
						$StockMovementData['bool_reclassification']=true;
						$StockMovementData['reclassification_code']=$reclassificationCode;
            $StockMovementData['comment']=$reclassificationComment;
						
						$this->StockMovement->create();
						if (!$this->StockMovement->save($StockMovementData)) {
							echo "problema al guardar el movimiento de lote";
							pr($this->validateErrors($this->StockMovement));
							throw new Exception();
						}
						
						// STEP 3: SAVE THE TARGET STOCKITEM
						$StockItemData=array();
						$StockItemData['name']=$message;
						$StockItemData['description']=$message;
						$StockItemData['stockitem_creation_date']=$reclassificationDate;
						$StockItemData['product_id']=$target_preforma_id;
						$StockItemData['product_unit_price']=$unit_price;
						$StockItemData['original_quantity']=$quantity_used;
						$StockItemData['remaining_quantity']=$quantity_used;
						$StockItemData['production_result_code_id']=0;
						$StockItemData['raw_material_id']=0;
						
						$this->StockItem->create();
						// notice that no new stockitem is created because we are taking from an already existing one
						if (!$this->StockItem->save($StockItemData)) {
							echo "problema al guardar el lote de destino";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
						
						// STEP 4: SAVE THE STOCK MOVEMENT FOR THE TARGET STOCKITEM
						$new_stockitem_id=$this->StockItem->id;
						$newlyCreatedStockItems[]=$new_stockitem_id;
						
						$origin_stock_movement_id=$this->StockMovement->id;
						
						$StockMovementData=array();
						$StockMovementData['movement_date']=$reclassificationDateTimeString;
						$StockMovementData['bool_input']=true;
						$StockMovementData['name']=$message;
						$StockMovementData['description']=$message;
						$StockMovementData['order_id']=0;
						$StockMovementData['stockitem_id']=$new_stockitem_id;
						$StockMovementData['product_id']=$target_preforma_id;
						$StockMovementData['product_quantity']=$quantity_used;
						$StockMovementData['product_unit_price']=$unit_price;
						$StockMovementData['product_total_price']=$unit_price*$quantity_used;
						$StockMovementData['production_result_code_id']=0;
						$StockMovementData['bool_reclassification']=true;
						$StockMovementData['origin_stock_movement_id']=$origin_stock_movement_id;
						$StockMovementData['reclassification_code']=$reclassificationCode;
            $StockMovementData['comment']=$reclassificationComment;
						
						$this->StockMovement->create();
						if (!$this->StockMovement->save($StockMovementData)) {
							echo "problema al guardar el movimiento de lote";
							pr($this->validateErrors($this->StockMovement));
							throw new Exception();
						}
								
						// STEP 5: SAVE THE USERLOG FOR THE STOCK MOVEMENT
						$this->recordUserActivity($this->Session->read('User.username'),$message);
					}
					$datasource->commit();
					
					foreach ($usedBottleStockItems as $usedBottleStockItem){
						$this->recreateStockItemLogs($usedBottleStockItem['id']);
					}
					for ($i=0;$i<count($newlyCreatedStockItems);$i++){
						$this->recreateStockItemLogs($newlyCreatedStockItems[$i]);
					}
					$this->Session->setFlash(__('Reclasificación exitosa'),'default',array('class' => 'success'));
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
				}
			}
					
      if ($boolReclassificationSuccess){    
        $datasource=$this->ProductionRun->getDataSource();
        $datasource->begin();
        try {
          $this->ProductionRun->create();
          if (!$this->ProductionRun->save($this->request->data)) {
            echo "problema al guardar el orden de producción";
            pr($this->validateErrors($this->Order));
            throw new Exception();
          }
          
          $productionrunid=$this->ProductionRun->id;
          $productionruncode=$this->request->data['ProductionRun']['production_run_code'] ;
          $productionrundate = $this->request->data['ProductionRun']['production_run_date'];
          $productionrundateAsString = $this->ProductionRun->deconstruct('production_run_date', $this->request->data['ProductionRun']['production_run_date']);
            
          // step 1: insert production movements for the raw materials and update the corresponding stock items
          $rawmaterialid=$this->request->data['ProductionRun']['raw_material_id'];
          $relatedRawMaterial=$this->Product->find('first',array(
            'conditions'=>array(
              'Product.id'=>$rawmaterialid,
            ),
          ));
          $rawMaterialName=$relatedRawMaterial['Product']['name'];
          $rawmaterialquantity=$this->request->data['ProductionRun']['raw_material_quantity'];
          $usedRawMaterials= $this->StockItem->getRawMaterialsForProductionRun($rawmaterialid,$rawmaterialquantity,$productionrundateAsString);

          $rawunitprice=0;
          $finishedunitprice=0;
          $totalrawcost=0;
          $i=0;
          if (empty($usedRawMaterials)){
            echo "problema al buscar los materiales usados<br/>";
            throw new Exception();
          }
          
          else {
            foreach ($usedRawMaterials as $usedRawMaterial){
              $stockitemid= $usedRawMaterial['id'];
              $rawname= $usedRawMaterial['name'];
              
              $quantitypresent=$usedRawMaterial['quantity_present'];
              $quantityused= $usedRawMaterial['quantity_used'];
              $rawunitprice=$usedRawMaterial['unit_price'];
              $totalrawcost+=$rawunitprice*$quantityused;
              $quantityremaining=$usedRawMaterial['quantity_remaining'];
              
              $message = "Used quantity ".$quantityused." of raw product ".$rawname." in product run ".$productionruncode." (prior to run: ".$quantitypresent."|remaining: ".$quantityremaining.")";
              
              $StockItemData=array();
              $StockItemData['StockItem']['id']=$stockitemid;
              $StockItemData['StockItem']['remaining_quantity']=$quantityremaining;
              
              if (!$this->StockItem->save($StockItemData)) {
                echo "problema guardando el lote";
                pr($this->validateErrors($this->StockItem));
                throw new Exception();
              }
              
              $RawProductionMovement['ProductionMovement']['name']=$reclassificationDateString."_".$productionruncode."_".$rawname;
              $RawProductionMovement['ProductionMovement']['description']=$message;
              $RawProductionMovement['ProductionMovement']['movement_date']=$productionrundate;
              $RawProductionMovement['ProductionMovement']['bool_input']=true;
              $RawProductionMovement['ProductionMovement']['stockitem_id']=$stockitemid;
              $RawProductionMovement['ProductionMovement']['production_run_id']=$productionrunid;
              $RawProductionMovement['ProductionMovement']['product_id']=$rawmaterialid;
              $RawProductionMovement['ProductionMovement']['product_quantity']=$quantityused;
              $RawProductionMovement['ProductionMovement']['product_unit_price']=$rawunitprice;
              
              $this->ProductionMovement->create();
              if (!$this->ProductionMovement->save($RawProductionMovement['ProductionMovement'])) {
                echo "problema al guardar el movimiento de producción";
                pr($this->validateErrors($this->ProductionMovement));
                throw new Exception();
              }
                          
              $this->recordUserActivity($this->Session->read('User.username'),$message);
              $i++;
            }
            
            $finishedunitprice=$totalrawcost/$rawmaterialquantity;
            
            // step 2: create new stock items for the produced products
            for ($c=0;$c<sizeof($resultCodes);$c++){
              $code=$resultCodes[$c]['ProductionResultCode']['code'];
              $quantityproduced=$this->request->data['Stockitems'][$resultCodes[$c]['ProductionResultCode']['code']];
              $finishedproductid=$this->request->data['ProductionRun']['finished_product_id'];
              $movementdate=$productionrundate;
              $message = "Produced quantity ".$quantityproduced." of product type ".$finishedproductid." quality ".$code." in product run ".$productionruncode;
              $linkedProduct=$this->Product->find('first',array(
                'conditions'=>array(
                  'Product.id'=>$finishedproductid,
                ),
              ));
              $product_name=$linkedProduct['Product']['name'];
              
              $finishedItem=array();
              $finishedItem['StockItem']['name']=$reclassificationDateString."_".$productionruncode."_".$rawMaterialName." ".$product_name." ".$code;
              $finishedItem['StockItem']['stockitem_creation_date']=$productionrundate;
              $finishedItem['StockItem']['product_id']=$finishedproductid;
              // no unit price is set yet until the time of purchase
              $finishedItem['StockItem']['product_unit_price']=$finishedunitprice;
              $finishedItem['StockItem']['original_quantity']=$quantityproduced;
              $finishedItem['StockItem']['remaining_quantity']=$quantityproduced;
              $finishedItem['StockItem']['production_result_code_id']=$resultCodes[$c]['ProductionResultCode']['id'];
              $finishedItem['StockItem']['raw_material_id']=$rawmaterialid;
              
              $this->StockItem->create();
              if (!$this->StockItem->save($finishedItem)) {
                echo "problema guardando el lote";
                pr($this->validateErrors($this->StockItem));
                throw new Exception();
              }
              
              $lateststockitemid=$this->StockItem->id;
              //echo "latest stock item id".$lateststockitemid."<br/>";
              
              $finishedProductionMovement=array();
              $finishedProductionMovement['ProductionMovement']['name']=$productionruncode."_".$code;
              $finishedProductionMovement['ProductionMovement']['description']=$message;
              $finishedProductionMovement['ProductionMovement']['movement_date']=$movementdate;
              $finishedProductionMovement['ProductionMovement']['bool_input']=false;
              $finishedProductionMovement['ProductionMovement']['stockitem_id']=$lateststockitemid;
              $finishedProductionMovement['ProductionMovement']['production_run_id']=$productionrunid;
              $finishedProductionMovement['ProductionMovement']['product_id']=$finishedproductid;
              $finishedProductionMovement['ProductionMovement']['product_quantity']=$quantityproduced;
              $finishedProductionMovement['ProductionMovement']['production_result_code_id']=$resultCodes[$c]['ProductionResultCode']['id'];
              $finishedProductionMovement['ProductionMovement']['product_unit_price']=$finishedunitprice;
              
              $this->ProductionMovement->create();
              if (!$this->ProductionMovement->save($finishedProductionMovement['ProductionMovement'])) {
                echo "problema guardando el movimiento de producción";
                pr($this->validateErrors($this->ProductionMovement));
                throw new Exception();
              }
              
              $productionMovementId=$this->ProductionMovement->id;
              $StockItemLog=array();
              $StockItemLog['StockItemLog']['production_movement_id']=$productionMovementId;
              $StockItemLog['StockItemLog']['stockitem_id']=$lateststockitemid;
              $StockItemLog['StockItemLog']['stockitem_date']=$reclassificationDateTimeString;
              $StockItemLog['StockItemLog']['product_id']=$finishedproductid;
              $StockItemLog['StockItemLog']['product_quantity']=$quantityproduced;
              $StockItemLog['StockItemLog']['product_unit_price']=$finishedunitprice;
              $StockItemLog['StockItemLog']['production_result_code_id']=$resultCodes[$c]['ProductionResultCode']['id'];
              
              $this->StockItemLog->create();
              if (!$this->StockItemLog->save($StockItemLog['StockItemLog'])) {
                echo "problema guardando el estado de lote";
                pr($this->validateErrors($this->StockItemLog));
                throw new Exception();
              }
              
              $this->recordUserActivity($this->Session->read('User.username'),$message);
            }
            
            $datasource->commit();
            
            $this->recordUserActivity($this->Session->read('User.username'),"Production Run executed with code ".$this->request->data['ProductionRun']['production_run_code']);
            
            foreach ($usedRawMaterials as $usedRawMaterial){
              $this->recreateStockItemLogs($usedRawMaterial['id']);
            }
            
            $this->Session->setFlash(__('The production run has been saved.'),'default',array('class' => 'success'));
            return $this->redirect(array('action' => 'view',$productionrunid));
          }
        } 
        catch(Exception $e){
          $datasource->rollback();
          pr($e);					
          $this->Session->setFlash(__('The production run could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
        }
      }
		}
		
		$this->request->data=$requestData;
		//pr($requestData);
		$rawProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_RAW,
			),
		));
		$producedProductTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_PRODUCED,
			),
		));
		$rawMaterialsAll=$this->Product->find('all', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=>$rawProductTypeIds,
			),
			'order'=>'Product.name',
		));
		$rawMaterials=null;
		foreach ($rawMaterialsAll as $rawMaterial){
			$rawMaterials[$rawMaterial['Product']['id']]=$rawMaterial['Product']['name'];
		}

		$finishedProductsAll = $this->Product->find('all', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'Product.product_type_id'=> $producedProductTypeIds,
			),
			'order'=>'Product.name',
		));
		$finishedProducts = null;
		foreach ($finishedProductsAll as $finishedProduct){
			$finishedProducts[$finishedProduct['Product']['id']]=$finishedProduct['Product']['name'];
		}
		$machines = $this->ProductionRun->Machine->find('list');
		$operators = $this->ProductionRun->Operator->find('list',array('conditions'=>array('bool_active'=>true)));
		$shifts = $this->ProductionRun->Shift->find('list');
		
		$this->set(compact('requestData','quantityRawMaterialInStock','quantityFinishedCInStock','quantityFinishedBInStock','rawMaterials','finishedProducts','machines','operators','shifts','remainingFinishedBInStockForProduct'));
	}
	
	public function add() {
		$this->loadModel('ProductionResultCode');
		$this->loadModel('StockItem');
		$this->loadModel('ProductionMovement');
		$this->loadModel('StockItemLog');
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('ClosingDate');
		
		$this->ProductionResultCode->recursive=-1;
		$resultCodes = $this->ProductionResultCode->find('all');
		
		$roleId=$this->Auth->User['role_id'];
		$this->set(compact('roleId'));
		
		$this->loadModel('ProductType');
		$this->ProductType->recursive=0;
		$productCategoryId=CATEGORY_RAW;
		$productTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productCategoryId)
		));
		//pr($productTypeIds);
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals($productCategoryId,$productTypeIds);
	
		if ($this->request->is('post')) {
			//echo "this is a request<br/>";
			$productionRunDate=$this->request->data['ProductionRun']['production_run_date'];
			//pr($productionRunDate);
			$productionRunDateAsString=$this->ProductionRun->deconstruct('production_run_date',$this->request->data['ProductionRun']['production_run_date']);
			//echo "production run date as string is ".$productionRunDateAsString."<br/>";
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
			
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,date("Y-m-d", strtotime($productionRunDateAsString)));
			//pr($rawMaterialsInventory);
			
			if (empty($this->request->data['refresh'])){
				$productionruncode=$this->request->data['ProductionRun']['production_run_code'];
				$namedProductionRuns=$this->ProductionRun->find('first',array('conditions'=>array('production_run_code'=>$productionruncode)));
				if (count($namedProductionRuns)>0){
					$this->Session->setFlash(__('Ya existe un orden de producción con el mismo código!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif (!$this->request->data['ProductionRun']['bool_annulled']&&empty($this->request->data['ProductionRun']['machine_id'])){
					$this->Session->setFlash(__('Se debe seleccionar la máquina!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif (!$this->request->data['ProductionRun']['bool_annulled']&&empty($this->request->data['ProductionRun']['operator_id'])){
					$this->Session->setFlash(__('Se debe seleccionar la máquina!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif (!$this->request->data['ProductionRun']['bool_annulled']&&empty($this->request->data['ProductionRun']['shift_id'])){
					$this->Session->setFlash(__('Se debe seleccionar la máquina!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif ($productionRunDateAsString>date('Y-m-d H:i:s')){
					$this->Session->setFlash(__('La fecha del orden de producción no puede estar en el futuro!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif ($productionRunDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
				}
				else {
					if ($this->request->data['ProductionRun']['bool_annulled']){
						$datasource=$this->ProductionRun->getDataSource();
						$datasource->begin();
						try {
							$this->ProductionRun->create();
							if (!$this->ProductionRun->save($this->request->data)) {
								echo "problema al guardar el orden de producción";
								pr($this->validateErrors($this->Order));
								throw new Exception();
							}
							
							$productionRunId=$this->ProductionRun->id;
							
							$datasource->commit();
							$this->recordUserAction($this->ProductionRun->id,null,null);
							$this->recordUserActivity($this->Session->read('User.username'),"Se guardo orden de produccion de forma anulada con numero ".$this->request->data['ProductionRun']['production_run_code']);
							
							$this->Session->setFlash(__('Se guardó la orden de producción (anulada).'),'default',array('class' => 'success'));
							return $this->redirect(array('action' => 'view',$productionRunId));
						}						 
						catch(Exception $e){
							$datasource->rollback();
							pr($e);					
							$this->Session->setFlash(__('No se podía guardar la orden de producción.'), 'default',array('class' => 'error-message'));
						}
					}
					else {
						// before proceeding with the production run, check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
						//echo "checkpoint before checking materials present<br/>";
						$productionItemsOK=true;
						$exceedingItems="";
						$quantityPlanned=$this->request->data['ProductionRun']['raw_material_quantity'];	
						
						$rawMaterialId=$this->request->data['ProductionRun']['raw_material_id'];	
						$linkedRawMaterial=$this->Product->find('first',array(
              'conditions'=>array(
                'Product.id'=>$rawMaterialId,
              ),
            ));
						$rawMaterialName=$linkedRawMaterial['Product']['name'];
						$quantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($rawMaterialId,$productionRunDateAsString);
						
						if ($quantityPlanned>$quantityPresent){
							$productionItemsOK=false;
							$exceedingItems.=__("Para producto ".$rawMaterialName." la cantidad requerida (".$quantityPlanned.") excede la cantidad en bodega (".$quantityPresent.")!")."<br/>";						
						}
						if ($exceedingItems!=""){
							$exceedingItems.=__("Please correct and try again!");
						}						
						if (!$productionItemsOK){
							$_SESSION['productionRunRequestData']=$this->request->data;
							
							$aco_name="ProductionRuns/manipularProduccion";		
							$bool_productionrun_manipularproduccion_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
							
							if ($bool_productionrun_manipularproduccion_permission){
								//echo "checkpoint before manipulating production run<br/>";
								return $this->redirect(array('action' => 'manipularProduccion'));
							}
							//$this->manipularProduccion();
							$this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',array('class' => 'error-message'));
						}
						else {
							//echo "checkpoint before saving production run<br/>";
							$datasource=$this->ProductionRun->getDataSource();
							$datasource->begin();
							try {
								$this->ProductionRun->create();
								if (!$this->ProductionRun->save($this->request->data)) {
									echo "problema al guardar el orden de producción";
									pr($this->validateErrors($this->Order));
									throw new Exception();
								}
								
								$productionRunId=$this->ProductionRun->id;
								$productionruncode=$this->request->data['ProductionRun']['production_run_code'] ;
								$productionRunDateAsString = $this->ProductionRun->deconstruct('production_run_date', $this->data['ProductionRun']['production_run_date']);
									
								// step 1: insert production movements for the raw materials and update the corresponding stock items
								$rawmaterialid=$this->request->data['ProductionRun']['raw_material_id'];
								$rawmaterialquantity=$this->request->data['ProductionRun']['raw_material_quantity'];
								$usedRawMaterials= $this->StockItem->getRawMaterialsForProductionRun($rawmaterialid,$rawmaterialquantity,$productionRunDateAsString);

								$rawunitprice=0;
								$finishedunitprice=0;
								$totalrawcost=0;
								$i=0;
								if (empty($usedRawMaterials)){
									echo "problema al buscar los materiales usados<br/>";
									throw new Exception();
								}
								else {
									//echo "checkpoint before saving raw materials<br/>";
									//pr($usedRawMaterials);
									foreach ($usedRawMaterials as $usedRawMaterial){
										//pr ($usedRawMaterial);
										$stockitemid= $usedRawMaterial['id'];
										$rawname= $usedRawMaterial['name'];
										
										$quantitypresent=$usedRawMaterial['quantity_present'];
										$quantityused= $usedRawMaterial['quantity_used'];
										$rawunitprice=$usedRawMaterial['unit_price'];
										$totalrawcost+=$rawunitprice*$quantityused;
										$quantityremaining=$usedRawMaterial['quantity_remaining'];
										
										$message = "Used quantity ".$quantityused." of raw product ".$rawname." in product run ".$productionruncode." (prior to run: ".$quantitypresent."|remaining: ".$quantityremaining.")";
										
										$StockItemData=array();
										$StockItemData['StockItem']['id']=$stockitemid;
										$StockItemData['StockItem']['remaining_quantity']=$quantityremaining;
										
										if (!$this->StockItem->save($StockItemData)) {
											echo "problema guardando el lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										$RawProductionMovement=array();
										$RawProductionMovement['ProductionMovement']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionruncode."_".$rawname;
										$RawProductionMovement['ProductionMovement']['description']=$message;
										$RawProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
										$RawProductionMovement['ProductionMovement']['bool_input']=true;
										$RawProductionMovement['ProductionMovement']['stockitem_id']=$stockitemid;
										$RawProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
										$RawProductionMovement['ProductionMovement']['product_id']=$rawmaterialid;
										$RawProductionMovement['ProductionMovement']['product_quantity']=$quantityused;
										$RawProductionMovement['ProductionMovement']['product_unit_price']=$rawunitprice;
										
										$this->ProductionMovement->create();
										if (!$this->ProductionMovement->save($RawProductionMovement['ProductionMovement'])) {
											echo "problema guardando el movimiento de producción";
											pr($this->validateErrors($this->ProductionMovement));
											throw new Exception();
										}
										
										$this->recordUserActivity($this->Session->read('User.username'),$message);
										$i++;
									}
									
									//echo "checkpoint before saving produced materials<br/>";
									$finishedunitprice=$totalrawcost/$rawmaterialquantity;
									// step 2: create new stock items for the produced products
									for ($c=0;$c<sizeof($resultCodes);$c++){
										
										$code=$resultCodes[$c]['ProductionResultCode']['code'];
										//echo "checkpoint starting to save produced material result code ".$code."<br/>";
										$quantityproduced=$this->request->data['Stockitems'][$resultCodes[$c]['ProductionResultCode']['code']];
										$finishedproductid=$this->request->data['ProductionRun']['finished_product_id'];
										$movementdate=$productionRunDate;
										$message = "Produced quantity ".$quantityproduced." of product type ".$finishedproductid." quality ".$code." in product run ".$productionruncode;
										// get the related product data
										$this->Product->recursive=-1;
										$linkedProduct=$this->Product->find('first',array(
											'conditions'=>array(
												'Product.id'=>$finishedproductid,
											),
										));
										$product_name=$linkedProduct['Product']['name'];
										
										$finishedItem=array();
										$finishedItem['StockItem']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionruncode."_".$rawMaterialName." ".$product_name." ".$code;
										$finishedItem['StockItem']['stockitem_creation_date']=$productionRunDate;
										// productid for the finished product is the same as for the raw material
										// only when packed for sale it becomes a new product
										$finishedItem['StockItem']['product_id']=$finishedproductid;
										// no unit price is set yet until the time of purchase
										$finishedItem['StockItem']['product_unit_price']=$finishedunitprice;
										$finishedItem['StockItem']['original_quantity']=$quantityproduced;
										$finishedItem['StockItem']['remaining_quantity']=$quantityproduced;
										$finishedItem['StockItem']['production_result_code_id']=$resultCodes[$c]['ProductionResultCode']['id'];
										$finishedItem['StockItem']['raw_material_id']=$rawmaterialid;
										//echo "printing stock item data ...<br/>";	
										//pr($finishedItem);
										//echo "checkpoint before saving produced material stock item<br/>";	
										$this->StockItem->create();
										if (!$this->StockItem->save($finishedItem)) {
											echo "problema guardando el lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										$lateststockitemid=$this->StockItem->id;
										//echo "latest stock item id".$lateststockitemid."<br/>";
										
										$finishedProductionMovement=array();
										$finishedProductionMovement['ProductionMovement']['name']=$productionruncode."_".$code;
										$finishedProductionMovement['ProductionMovement']['description']=$message;
										$finishedProductionMovement['ProductionMovement']['movement_date']=$movementdate;
										$finishedProductionMovement['ProductionMovement']['bool_input']=false;
										$finishedProductionMovement['ProductionMovement']['stockitem_id']=$lateststockitemid;
										$finishedProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
										$finishedProductionMovement['ProductionMovement']['product_id']=$finishedproductid;
										$finishedProductionMovement['ProductionMovement']['product_quantity']=$quantityproduced;
										$finishedProductionMovement['ProductionMovement']['production_result_code_id']=$resultCodes[$c]['ProductionResultCode']['id'];
										$finishedProductionMovement['ProductionMovement']['product_unit_price']=$finishedunitprice;
										
										//echo "checkpoint before saving produced material production movement<br/>";	
										$this->ProductionMovement->create();
										if (!$this->ProductionMovement->save($finishedProductionMovement['ProductionMovement'])) {
											echo "problema guardando el movimiento de producción";
											pr($this->validateErrors($this->ProductionMovement));
											throw new Exception();
										}
										
										$productionMovementId=$this->ProductionMovement->id;
										$StockItemLog=array();
										$StockItemLog['StockItemLog']['production_movement_id']=$productionMovementId;
										$StockItemLog['StockItemLog']['stockitem_id']=$lateststockitemid;
										$StockItemLog['StockItemLog']['stockitem_date']=$productionRunDate;
										$StockItemLog['StockItemLog']['product_id']=$finishedproductid;
										$StockItemLog['StockItemLog']['product_quantity']=$quantityproduced;
										$StockItemLog['StockItemLog']['product_unit_price']=$finishedunitprice;
										$StockItemLog['StockItemLog']['production_result_code_id']=$resultCodes[$c]['ProductionResultCode']['id'];
										
										//echo "checkpoint before saving produced material stock item log<br/>";	
										//echo "printing stock item log...<br/>";	
										//pr($StockItemLog['StockItemLog']);
										$this->StockItemLog->create();
										if (!$this->StockItemLog->save($StockItemLog['StockItemLog'])) {
											echo "problema guardando el estado de lote";
											pr($this->validateErrors($this->StockItemLog));
											throw new Exception();
										}
										$this->recordUserActivity($this->Session->read('User.username'),$message);
									}
									
									//echo "checkpoint before committing<br/>";	
									$datasource->commit();
									$this->recordUserAction($this->ProductionRun->id,null,null);
									$this->recordUserActivity($this->Session->read('User.username'),"Se ejecuto orden de produccion con numero ".$this->request->data['ProductionRun']['production_run_code']);
									
									//echo "checkpoint before creating raw material stock item log<br/>";	
									foreach ($usedRawMaterials as $usedRawMaterial){
										$this->recreateStockItemLogs($usedRawMaterial['id']);
									}
									
									$this->Session->setFlash(__('Se guardó la orden de producción.'),'default',array('class' => 'success'));
									return $this->redirect(array('action' => 'view',$productionRunId));
								}
							} 
							catch(Exception $e){
								$datasource->rollback();
								echo substr(print_r($e,true),0,500);
								//var_dump($e);
								//pr($e);					
								$this->Session->setFlash(__('No se podía guardar la orden de producción.'), 'default',array('class' => 'error-message'));
							}
						}
					}
				}				
			}
		}

		$this->loadModel('Product');
		$this->Product->recursive=-1;
    
    //echo "load raw materials<br/>";
    $rawProductTypeIds=$this->ProductType->find('list',array(
      'fields'=>array('ProductType.id'),
      'conditions'=>array(
        'ProductType.product_category_id'=>CATEGORY_RAW,
      ),
    ));
		$rawMaterialsAll=$this->Product->find('all', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array('Product.product_type_id'=>$rawProductTypeIds),
			'order'=>'Product.name',
		));
		$rawMaterials=null;
		foreach ($rawMaterialsAll as $rawMaterial){
			$rawMaterials[$rawMaterial['Product']['id']]=$rawMaterial['Product']['name'];
		}

		 $finishedProductTypeIds=$this->ProductType->find('list',array(
      'fields'=>array('ProductType.id'),
      'conditions'=>array(
        'ProductType.product_category_id'=>CATEGORY_PRODUCED,
      ),
    ));
		$finishedProductsAll = $this->Product->find('all', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array('Product.product_type_id'=> $finishedProductTypeIds),
			'order'=>'Product.name',
		));
		$finishedProducts = null;
		foreach ($finishedProductsAll as $finishedProduct){
			$finishedProducts[$finishedProduct['Product']['id']]=$finishedProduct['Product']['name'];
		}
		$machines = $this->ProductionRun->Machine->find('list',array(
			'conditions'=>array(
				'Machine.bool_active'=>true,
			),
			'order'=>'Machine.name',
		));
		$operators = $this->ProductionRun->Operator->find('list',array(
			'conditions'=>array(
				'bool_active'=>true,
			),
			'order'=>'Operator.name',
		));
		$shifts = $this->ProductionRun->Shift->find('list');
	
		// as raw materials are not input, they are not visualized, although it will still be useful to calculate them
		// $usedRawMaterials= $this->StockItem->getRawMaterialsForProductionRun($productid,$quantityneeded);
		
		// calculate the name for the next production run
		$newProductionRunCode="";
    $this->ProductionRun->recursive=-1;
		$lastProductionRun = $this->ProductionRun->find('first',array(
			'order' => array('ProductionRun.created' => 'desc')
		));
		//pr($lastProductionRun);
		if ($lastProductionRun!= null){
			$newProductionRunCode = "OR".(substr($lastProductionRun['ProductionRun']['production_run_code'],2)+1);
		}
		else {
			$newProductionRunCode="OR001";
		}
		$this->set(compact('rawMaterials','finishedProducts','machines', 'operators', 'shifts','rawMaterialsInventory','resultCodes','newProductionRunCode'));
		
		$productionRunTypes=$this->ProductionRun->ProductionRunType->find('list',array(
			'order'=>'ProductionRunType.name',
		));
		$this->set(compact('productionRunTypes'));
		
		$aco_name="Operators/reporteProduccionTotal";		
		$bool_operator_totalproductionreport_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_totalproductionreport_permission'));
		
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
		$aco_name="Machines/index";		
		$bool_machine_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_machine_index_permission'));
		$aco_name="Machines/add";		
		$bool_machine_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_machine_add_permission'));
		$aco_name="Operators/index";		
		$bool_operator_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_index_permission'));
		$aco_name="Operators/add";		
		$bool_operator_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_add_permission'));
		$aco_name="Shifts/index";		
		$bool_shift_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_shift_index_permission'));
		$aco_name="Shifts/add";		
		$bool_shift_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_shift_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->ProductionRun->exists($id)) {
			throw new NotFoundException(__('Invalid production run'));
		}
		$this->ProductionRun->recursive=2;
		
		$this->loadModel('ProductionResultCode');
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		$this->loadModel('ProductionMovement');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('ClosingDate');
		
    $this->ProductionRun->recursive=-1;
		$this->ProductionResultCode->recursive=-1;
    $this->ProductType->recursive=-1;
    $this->StockItem->recursive=-1;
    $this->StockMovement->recursive=-1;
    
		$resultCodes = $this->ProductionResultCode->find('all');
		
		$roleId=$this->Auth->User['role_id'];
		$this->set(compact('roleId'));
		
		
		$productCategoryId=CATEGORY_RAW;
		$productTypeIds=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productCategoryId)
		));
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals($productCategoryId,$productTypeIds);
		
		$boolEditable=true;
		
		if ($this->request->is(array('post', 'put'))) {
			// check if meter_finish is bigger than meter_start
			//if ($this->request->data['ProductionRun']['meter_finish']<=$this->request->data['ProductionRun']['meter_start']){
			//	$this->Session->setFlash(__('El medidor final tiene que estar más grande que el medidor inicial!  Corriga los medidores...'), 'default',array('class' => 'error-message'));
			//}
			//else {
			
			$boolEditable=$this->request->data['ProductionRun']['bool_editable'];
			$reasonForNonEditable=$this->request->data['ProductionRun']['reason_for_non_editable'];
			
			$productionRunDate=$this->request->data['ProductionRun']['production_run_date'];
			//pr($productionRunDate);
			$productionRunDateAsString=$this->ProductionRun->deconstruct('production_run_date',$this->request->data['ProductionRun']['production_run_date']);
			//echo "production run date as string is ".$productionRunDateAsString."<br/>";
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
			
			$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productCategoryId,$productTypeIds,date("Y-m-d", strtotime($productionRunDateAsString)));
			//pr($rawMaterialsInventory);
			
			if (empty($this->request->data['refresh'])){
				$productionruncode=$this->request->data['ProductionRun']['production_run_code'];
				$namedProductionRuns=$this->ProductionRun->find('first',array(
					'conditions'=>array(
						'production_run_code'=>$productionruncode,
						'ProductionRun.id !='=>$id,
					),
				));
        //pr($namedProductionRuns);
				if (count($namedProductionRuns)>0){
					$this->Session->setFlash(__('Ya existe un orden de producción con el mismo código!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif (!$this->request->data['ProductionRun']['bool_annulled']&&empty($this->request->data['ProductionRun']['machine_id'])){
					$this->Session->setFlash(__('Se debe seleccionar la máquina!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif (!$this->request->data['ProductionRun']['bool_annulled']&&empty($this->request->data['ProductionRun']['operator_id'])){
					$this->Session->setFlash(__('Se debe seleccionar la máquina!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif (!$this->request->data['ProductionRun']['bool_annulled']&&empty($this->request->data['ProductionRun']['shift_id'])){
					$this->Session->setFlash(__('Se debe seleccionar la máquina!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
				}
				elseif ($productionRunDateAsString>date('Y-m-d H:i')){
						$this->Session->setFlash(__('La fecha del orden de producción no puede estar en el futuro!  No se guardó el orden de producción.'), 'default',array('class' => 'error-message'));
					}
				elseif ($productionRunDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
				}
				else {
					if ($this->request->data['ProductionRun']['bool_annulled']){
						if (!$boolEditable){
							$this->Session->setFlash($reasonForNonEditable.".  No se puede anular la orden de producción.", 'default',array('class' => 'error-message'));
						}
						else {
							$datasource=$this->ProductionRun->getDataSource();
							$datasource->begin();
							try {								
								$this->ProductionRun->id=$id;
								$productionRun=$this->ProductionRun->find('first',array(
									'conditions'=>array(
										'ProductionRun.id'=>$id
									),
									'contain'=>array(
										'RawMaterial'=>array(
											'ProductType',
										),
										'ProductionMovement'=>array(
											'StockItem'=>array(
												'StockMovement',
											)
										),
									),
								));
								foreach ($productionRun['ProductionMovement'] as $productionMovement){
									if ($productionMovement['bool_input']){
										$stockItem=array();
										$stockItem['StockItem']['id']=$productionMovement['StockItem']['id'];
										$stockItem['StockItem']['remaining_quantity']=$productionMovement['StockItem']['remaining_quantity']+$productionMovement['product_quantity'];
										$stockItem['StockItem']['description']=$productionMovement['StockItem']['description']."|anulada orden de producción production run ".$productionRun['ProductionRun']['production_run_code'];
										if (!$this->StockItem->save($stockItem)) {
											echo "problema actualizando el estado de lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										$this->recreateStockItemLogs($productionMovement['stockitem_id']);										
									}
									else {
										$this->StockItem->id=$productionMovement['StockItem']['id'];
										if (!$this->StockItem->delete()) {
											echo "problema eliminando el estado de lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}	
									}
									$this->ProductionRun->ProductionMovement->id=$productionMovement['id'];
									if (!$this->ProductionRun->ProductionMovement->delete()) {
										echo "problema eliminando el movimiento de lote";
										pr($this->validateErrors($this->ProductionRun->ProductionMovement));
										throw new Exception();
									}
								}
								
								$this->request->data['ProductionRun']['raw_material_quantity']=0;
								if (!$this->ProductionRun->save($this->request->data)) {
									echo "problema al guardar el orden de producción como anulada";
									pr($this->validateErrors($this->Order));
									throw new Exception();
								}
								
								$productionRunId=$this->ProductionRun->id;
								
								$datasource->commit();
								
								$this->recordUserAction($this->ProductionRun->id,null,null);
								$this->recordUserActivity($this->Session->read('User.username'),"Se editó orden de produccion de forma anulada con numero ".$this->request->data['ProductionRun']['production_run_code']);
								
								$this->Session->setFlash(__('Se anuló la orden de producción (por edición).'),'default',array('class' => 'success'));
								return $this->redirect(array('action' => 'view',$productionRunId));
							}						 
							catch(Exception $e){
								$datasource->rollback();
								pr($e);					
								$this->Session->setFlash(__('No se podía guardar la orden de producción como anulada.'), 'default',array('class' => 'error-message'));
							}
						}
					}
					else {
						if (!$boolEditable){
							$datasource=$this->ProductionRun->getDataSource();
							$datasource->begin();
							try {
                //echo "production run not editable<br/>";
								if (!$this->ProductionRun->save($this->request->data)) {
									echo "problema al guardar el orden de producción";
									pr($this->validateErrors($this->ProductionRun));
									throw new Exception();
								}
														
								$productionRunId=$this->request->data['ProductionRun']['id'];
								$productionruncode=$this->request->data['ProductionRun']['production_run_code'] ;
								$productionRunDate=$this->request->data['ProductionRun']['production_run_date'] ;
								$productionRunDateAsString = $this->ProductionRun->deconstruct('production_run_date', $this->data['ProductionRun']['production_run_date']);
							
								$datasource->commit();
								$this->recordUserAction();
								$this->recordUserActivity($this->Session->read('User.username'),"Se editó orden de producción con número ".$this->request->data['ProductionRun']['production_run_code']);
								
								$this->Session->setFlash(__('The production run has been saved.'),'default',array('class' => 'success'));
								return $this->redirect(array('action' => 'index'));
							}
							catch(Exception $e){
								$this->Session->setFlash(__('La orden de producción no se podía editar.'),'default',array('class' => 'error-message'));
							}
						}
						else {
              //echo "entering the editable part<br/>";
							// first check the changes of the contained materials
							$originalProductionRun = $this->ProductionRun->find('first', array(
								'conditions' => array(
									'ProductionRun.id'=> $id,
								),
                'contain'=>array(
                  'ProductionMovement',
                )
							));
              //pr($originalProductionRun);
							$originalrawmaterialid=$originalProductionRun['ProductionRun']['raw_material_id'] ;
							$originalrawmaterialquantity=$originalProductionRun['ProductionRun']['raw_material_quantity'] ;
							$originalfinishedproductid=$originalProductionRun['ProductionRun']['finished_product_id'] ;
							
							$newrawmaterialid=$this->request->data['ProductionRun']['raw_material_id'] ;
							$newrawmaterialquantity=$this->request->data['ProductionRun']['raw_material_quantity'] ;
							$newfinishedproductid=$this->request->data['ProductionRun']['finished_product_id'] ;
							$thisfinishedproduct=$this->Product->find('first',array('conditions'=>array('Product.id'=>$newfinishedproductid)));
							$newfinishedproductname=$thisfinishedproduct['Product']['name'];
							
							$rawmaterialidchanged=($newrawmaterialid!=$originalrawmaterialid);
							$rawmaterialquantitychanged=($newrawmaterialquantity!=$originalrawmaterialquantity);
							//echo "rawmaterial quantity changed is ".($rawmaterialquantitychanged?"true":"false");
							$finishedproductidchanged=($newfinishedproductid!=$originalfinishedproductid);
							$goodtogo=true;
							$savesuccess=false;
							$originalRawStockItems=array();
							$originalProducedStockItems=array();
							
							$rawStockCounter=0;
							$producedStockCounter=0;
							// load the original stock items into arrays $originalRawStockItems and $originalProducedStockItems
							foreach ($originalProductionRun['ProductionMovement'] as $productionMovement){
                //pr ($productionMovement);
								if ($productionMovement['bool_input']){
									// raw materials
									$originalRawStockItems[$rawStockCounter]['stockitemid']=$productionMovement['stockitem_id'];
									$originalRawStockItems[$rawStockCounter]['quantityused']=$productionMovement['product_quantity'];
									$originalRawStockItems[$rawStockCounter]['productionmovementid']=$productionMovement['id'];
									$originalRawStockItems[$rawStockCounter]['productunitprice']=$productionMovement['product_unit_price'];
									$rawStockCounter++;
								}
								else {
									// produced materials
									$originalProducedStockItems[$producedStockCounter]['stockitemid']=$productionMovement['stockitem_id'];
									$originalProducedStockItems[$producedStockCounter]['quantityproduced']=$productionMovement['product_quantity'];
									$originalProducedStockItems[$producedStockCounter]['productionmovementid']=$productionMovement['id'];
									$originalProducedStockItems[$producedStockCounter]['productunitprice']=$productionMovement['product_unit_price'];							
									$originalProducedStockItems[$producedStockCounter]['productionresultcodeid']=$productionMovement['production_result_code_id'];
									$producedStockCounter++;
								}
							}
							
							// if any products were sold already, editing is a no go
							
							$salesCodes=array();
							foreach ($originalProducedStockItems as $producedStock){
								$sales=$this->StockMovement->find('all', array(
									'fields'=>array('StockMovement.order_id'),
									'conditions'=>array(
										'StockMovement.stockitem_id'=>$producedStock['stockitemid'],
										'StockMovement.product_quantity >'=>0,
									),
									'contain'=>array(
										'Order'=>array(
											'fields'=>array('Order.order_code'),
										),
									),
								));
								if (!empty($sales)){
									$goodtogo=false;
									foreach ($sales as $sale){
										$salesCodes[]=$sale['Order']['order_code'];
									}
									
								}
							}
							if (!$goodtogo){
								$this->Session->setFlash(__('Los productos fabricados en la orden de producción original ya se han vendido '.(empty($salesCodes)?"":"en salidas ".implode("'",$salesCodes)).' y no se pueden editar.  No se guardaron los cambios.'), 'default',array('class' => 'error-message'));
							}
							// yet another check: check if this has forced stockmovements taken from this stockitem to come before purchase date
							if ($goodtogo){
                //echo "check if date moved stockmovements before purchase date<br/>";
								$warning="Este lote ha estado utilizado en salidas antes de la nueva fecha de entrada.<br/>";
								foreach ($originalProducedStockItems as $originalProducedStockItem){
									//pr($originalProducedStockItems);
									$stockMovementsForStockItemsInThisPurchase=$this->StockMovement->find('all',array(
										'conditions'=>array(
											'StockMovement.movement_date <'=>$productionRunDateAsString,
											'StockMovement.stockitem_id'=>$originalProducedStockItem['stockitemid'],
											'StockMovement.bool_input'=>false,
										),
										'order'=>'StockMovement.movement_date ASC'
									));
									if (count($stockMovementsForStockItemsInThisPurchase)>0){
										$goodtogo=false;
										foreach ($stockMovementsForStockItemsInThisPurchase as $stockmovement){
											$linkedOrder=$this->Order->find('first',array(
                        'conditions'=>array(
                          'Order.id'=>$stockmovement['StockMovement']['order_id'],
                        ),
                      ));
											$movementdate=new DateTime($stockmovement['StockMovement']['movement_date']);
											$warning.="Botella salió en salida ".$linkedOrder['Order']['invoice_code']." en ".$movementdate->format('d-m-Y')."<br/>";
										}
									}
								}
								if (!$goodtogo){
									$this->Session->setFlash($warning,'default',array('class' => 'error-message'));
								}
							}
							// if none of the produced items were sold yet 
							// check if the raw material id and/or quantity has changed, 
							// then check if there are enough materials in the inventory to make the change
							if ($goodtogo){
                //echo "checking if rawmaterial id or quantity has changed<br/>";
								$totalrawprice=0;
								if ($rawmaterialidchanged||$rawmaterialquantitychanged){
									// check if there are enough materials present in inventory to support the change
									$quantityNeededOfRaw=0;
									if ($rawmaterialidchanged){
										// only check if there are enough additional materials
										$quantityNeededOfRaw=$newrawmaterialquantity;
									}
									else {
										// just check if there are enough materials for the new material id
										$quantityNeededOfRaw=$newrawmaterialquantity-$originalrawmaterialquantity;
									}
									//echo "calling getStockQuantityAtDateForProduct<br/>";
									$quantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($this->request->data['ProductionRun']['raw_material_id'],$productionRunDateAsString);
                  //echo "look up linked raw material<br/>";
                  $this->Product->recursive=-1;
									$linkedRawMaterial=$this->Product->find('first',array(
                    'conditions'=>array(
                      'Product.id'=>$this->request->data['ProductionRun']['raw_material_id'],
                    ),
                  ));							
									$exceedingItems="";
									if ($quantityNeededOfRaw>$quantityPresent){
										$goodtogo=false;
										$exceedingItems.=__("Para producto ".$$linkedRawMaterial['Product']['name']." la cantidad requerida (".$quantityNeededOfRaw.") excede la cantidad en bodega (".$quantityPresent.")!")."<br/>";						
										$productionItemsOK=false;
									}
									if ($exceedingItems!=""){
										$exceedingItems.=__("Please correct and try again!");
									}
									
									if (!$goodtogo){
										$this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',array('class' => 'error-message'));
									}
								}
							}
							
							// if all checks have been passed, the saving can proceed
							if ($goodtogo){
                //echo "Starting to save<br/>";
								$datasource=$this->ProductionRun->getDataSource();
								$datasource->begin();
                $this->StockItem->recursive=-1;
								try {
									if (!$this->ProductionRun->save($this->request->data)) {
										echo "problema al guardar el orden de producción";
										pr($this->validateErrors($this->ProductionRun));
										throw new Exception();
									}
									
									//echo "entering the materials loop<br/>";
									$productionRunId=$this->request->data['ProductionRun']['id'];
									$productionruncode=$this->request->data['ProductionRun']['production_run_code'] ;
									$productionRunDate=$this->request->data['ProductionRun']['production_run_date'] ;
									$productionRunDateAsString = $this->ProductionRun->deconstruct('production_run_date', $this->data['ProductionRun']['production_run_date']);
									
									$usedRawMaterials=array();
									$rawMaterialName="";
									if ($rawmaterialidchanged||$rawmaterialquantitychanged){
										// first undo the use of the formerly used raw materials
										$numberOfRawReturned=$originalrawmaterialquantity;
                    //echo "looping the original raw stock items <br/>";  
										foreach ($originalRawStockItems as $rawItem){
											// restore the original quantities to the stockitems from which they were taken
											// simultaneously set the corresponding stockitemlog to the original quantity
											$stockItem=array();
											$stockItem['StockItem']['id']=$rawItem['stockitemid'];
											$thisStockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$rawItem['stockitemid'])));

											if ($numberOfRawReturned>=$thisStockItem['StockItem']['original_quantity']-$thisStockItem['StockItem']['remaining_quantity']){
												$stockItem['StockItem']['remaining_quantity']=$thisStockItem['StockItem']['original_quantity'];
												$numberOfRawReturned=$numberOfRawReturned-$thisStockItem['StockItem']['original_quantity']+$thisStockItem['StockItem']['remaining_quantity'];
											}
											else {
												$stockItem['StockItem']['remaining_quantity']=$numberOfRawReturned+$thisStockItem['StockItem']['remaining_quantity'];
												$numberOfRawReturned=0;
											}
											if (!$this->StockItem->save($stockItem['StockItem'])) {
												echo "problema al guardar el lote";
												pr($this->validateErrors($this->StockItem));
												throw new Exception();
											}
											
											$productionMove=array();
											$productionMove['ProductionMovement']['id']=$rawItem['productionmovementid'];
											$productionMove['ProductionMovement']['product_quantity']=0;
											$productionMove['ProductionMovement']['movement_date']=$productionRunDate;
											
											$this->ProductionMovement->id=$rawItem['productionmovementid'];
											if (!$this->ProductionMovement->save($productionMove['ProductionMovement'])) {
												echo "problema al guardar el movimiento de producción";
												pr($this->validateErrors($this->ProductionMovement));
												throw new Exception();
											}
										}

                    //echo "registering the use of the new raw material <br/>";  
										// then register the use of the new raw material
										$usedRawMaterials= $this->StockItem->getRawMaterialsForProductionRun($newrawmaterialid,$newrawmaterialquantity,$productionRunDateAsString);		
										$rawunitprice=0;
										$finishedunitprice=0;
										$totalrawcost=0;
									
										$i=0;
										//pr($usedRawMaterials);	
                    //echo "looping the used raw materials <br/>";  
										foreach ($usedRawMaterials as $usedRawMaterial){
											$stockitemid= $usedRawMaterial['id'];
											$rawname= $usedRawMaterial['name'];
										
											$rawunitprice=$usedRawMaterial['unit_price'];
											$quantitypresent=$usedRawMaterial['quantity_present'];
											
											$quantityused= $usedRawMaterial['quantity_used'];
											$quantityremaining=$usedRawMaterial['quantity_remaining'];
											$totalrawprice+=$rawunitprice*$quantityused;
											$rawMaterialId=$this->request->data['ProductionRun']['raw_material_id'];	
											$linkedRawMaterial=$this->Product->find('first',array(
                        'conditions'=>array(
                          'Product.id'=>$rawMaterialId,
                        ),
                      ));
											$rawMaterialName=$linkedRawMaterial['Product']['name'];
											
											$StockItemData=array();
											$StockItemData['StockItem']['id']=$stockitemid;
											$StockItemData['StockItem']['remaining_quantity']=$quantityremaining;
											
											if (!$this->StockItem->save($StockItemData)) {
												echo "problema al guardar el lote";
												pr($this->validateErrors($this->StockItem));
												throw new Exception();
											}
											
											$message = "Used quantity ".$quantityused." of raw product ".$rawname." in product run ".$productionruncode." (prior to run: ".$quantitypresent."|remaining: ".$quantityremaining.")";
											$RawProductionMovement=array();
											$RawProductionMovement['ProductionMovement']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionruncode."_".$rawname;
											$RawProductionMovement['ProductionMovement']['description']=$message;
											$RawProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
											$RawProductionMovement['ProductionMovement']['bool_input']=true;
											$RawProductionMovement['ProductionMovement']['stockitem_id']=$stockitemid;
											$RawProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
											$RawProductionMovement['ProductionMovement']['product_id']=$newrawmaterialid;
											$RawProductionMovement['ProductionMovement']['product_quantity']=$quantityused;
											$RawProductionMovement['ProductionMovement']['product_unit_price']=$rawunitprice;
											$this->ProductionMovement->create();
											if (!$this->ProductionMovement->save($RawProductionMovement['ProductionMovement'])) {
												echo "problema al guardar el movimiento de uso de materia prima";
												pr($this->validateErrors($this->ProductionMovement));
												throw new Exception();
											}
											
											$this->recordUserActivity($this->Session->read('User.username'),$message);
											$i++;
										}
										$finishedunitprice=$totalrawcost/$newrawmaterialquantity;
									}
									else {
                    //echo "id and material have not changed<br/>";
										foreach ($originalRawStockItems as $rawItem){
                      //pr($rawItem);
											// update the production run date
                      
											$stockItem['StockItem']['id']=$rawItem['stockitemid'];
                      //echo "looking up stock item<br/>";
											$thisStockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$rawItem['stockitemid'])));
                      //pr($thisStockItem);
											$stockitemname=$thisStockItem['StockItem']['name'];
											//$rawMaterialId=$this->request->data['ProductionRun']['raw_material_id'];	
											//$linkedRawMaterial=$this->Product->find('first',array(
                      //  'conditions'=>array(
                      //    'Product.id'=>$rawMaterialId,
                      //  ),
                      //));
                      //pr($linkedRawMaterial);
											//$rawMaterialName=$linkedRawMaterial['Product']['name'];
											
											$totalrawprice+=$rawItem['productunitprice']*$rawItem['quantityused'];
											
											// set the corresponding production movements to 0 quantity
											$productionMove=array();
											$productionMove['ProductionMovement']['id']=$rawItem['productionmovementid'];
											$productionMove['ProductionMovement']['movement_date']=$productionRunDate;
											$productionMove['ProductionMovement']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionruncode."_".$stockitemname;
											//pr($productionMove);
											if (!$this->ProductionMovement->save($productionMove['ProductionMovement'])) {
												echo "problema al guardar el movimiento de producción";
												pr($this->validateErrors($this->ProductionMovement));
												throw new Exception();
											}
										}
									}						
									// now all that remains is to edit the finished materials
									foreach ($originalProducedStockItems as $producedItem){
										//echo "entering the finished products loop";
										$finishedquantity=0;
										switch ($producedItem['productionresultcodeid']){
											case 1:
												$finishedquantity=$this->request->data['Stockitems']['A'];
												$code="A";
												break;
											case 2:
												$finishedquantity=$this->request->data['Stockitems']['B'];
												$code="B";
												break;
											case 3:
												$finishedquantity=$this->request->data['Stockitems']['C'];
												$code="C";
												break;
										}
										$name=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionruncode."_".$rawMaterialName." ".$newfinishedproductname." ".$code;
										$stockItem=array();
										$stockItem['StockItem']['id']=$producedItem['stockitemid'];
										$stockItem['StockItem']['name']=$name;
										$stockItem['StockItem']['stockitem_creation_date']=$productionRunDate;
										$stockItem['StockItem']['product_id']=$newfinishedproductid;
										$stockItem['StockItem']['original_quantity']=$finishedquantity;
										$stockItem['StockItem']['remaining_quantity']=$finishedquantity;
										$stockItem['StockItem']['raw_material_id']=$newrawmaterialid;
										if ($rawmaterialidchanged||$rawmaterialquantitychanged){
											$stockItem['StockItem']['product_unit_price']=$totalrawprice/$newrawmaterialquantity;
										}
										
										$this->StockItem->id=$producedItem['stockitemid'];
										if (!$this->StockItem->save($stockItem['StockItem'])) {
											echo "problema al guardar el lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										$productionMove=array();
										$productionMove['ProductionMovement']['id']=$producedItem['productionmovementid'];
										$productionMove['ProductionMovement']['product_id']=$newfinishedproductid;
										$productionMove['ProductionMovement']['product_quantity']=$finishedquantity;
										$productionMove['ProductionMovement']['movement_date']=$productionRunDate;
										$productionMove['ProductionMovement']['name']=$name;
										if ($rawmaterialidchanged||$rawmaterialquantitychanged){
											$productionMove['ProductionMovement']['product_unit_price']=$totalrawprice/$newrawmaterialquantity;
										}
										
										if (!$this->ProductionMovement->save($productionMove['ProductionMovement'])) {
											echo "problema al guardar el movimiento de producción";
											pr($this->validateErrors($this->ProductionMovement));
											throw new Exception();
										}
									}
								
									$datasource->commit();
									$this->recordUserAction();
									$this->recordUserActivity($this->Session->read('User.username'),"Se editó orden de producción con número ".$this->request->data['ProductionRun']['production_run_code']);
									
									foreach ($originalProductionRun['ProductionMovement'] as $productionMovement){
										$this->recreateStockItemLogs($productionMovement['stockitem_id']);
									}
									foreach ($usedRawMaterials as $usedRawMaterial){
										$this->recreateStockItemLogs($usedRawMaterial['id']);
									}
									
									$this->Session->setFlash(__('The production run has been saved.'),'default',array('class' => 'success'));
									return $this->redirect(array('action' => 'index'));
								}
								catch(Exception $e){
									$this->Session->setFlash(__('La orden de producción no se podía guardar.'),'default',array('class' => 'error-message'));
								}
							}
						}
					}
				} 			
			}
      //echo "through with the post event<br/>";
		}
		else {
			$this->request->data = $this->ProductionRun->find('first', array(
				'conditions' => array(
					'ProductionRun.id' => $id,
				),
				'contain'=>array(
					'RawMaterial'=>array(
						'ProductType',
					),
					'ProductionMovement'=>array(
						'StockItem'=>array(
							'StockMovement'=>array(
								'Order',
							),
						)
					),
				),
			));
			$reasonForNonEditable="";
			foreach ($this->request->data['ProductionMovement'] as $productionMovement){
				if (!$productionMovement['bool_input']){
					if (!empty($productionMovement['StockItem']['StockMovement'])){
						$boolEditable=false;
					}
				}
			}
			if (!$boolEditable){
				$reasonForNonEditable.="Los productos de la orden de producción no se pueden editar porque ya se remitieron los productos fabricados en las salidas ";
				foreach ($this->request->data['ProductionMovement'] as $productionMovement){
					if (!$productionMovement['bool_input']){
						foreach ($productionMovement['StockItem']['StockMovement'] as $stockMovement){
							//pr($productionMovement['StockItem']['StockMovement']);
							if (!$stockMovement['bool_transfer']){
								if (!empty($stockMovement['Order'])){
									//pr($productionMovement['StockItem']['StockMovement']['Order']);
									$reasonForNonEditable.=$stockMovement['Order']['order_code']." ";
								}
								else {
									//pr($productionMovement['StockItem']['StockMovement']);
								}
							}
							else {
								$reasonForNonEditable.=$stockMovement['transfer_code']." ";
							}
						}
					}
				}
			}
		}
		$this->set(compact('boolEditable'));
		$this->set(compact('reasonForNonEditable'));
		//pr($this->request->data);
		//echo "load additional data<br/>";
    
    
		$this->Product->recursive=-1;
    
    //echo "load raw materials<br/>";
    $rawProductTypeIds=$this->ProductType->find('list',array(
      'fields'=>array('ProductType.id'),
      'conditions'=>array(
        'ProductType.product_category_id'=>CATEGORY_RAW,
      ),
    ));
		$rawMaterialsAll=$this->Product->find('all', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array('Product.product_type_id'=>$rawProductTypeIds),
			'order'=>'Product.name',
		));
		$rawMaterials=null;
		foreach ($rawMaterialsAll as $rawMaterial){
			$rawMaterials[$rawMaterial['Product']['id']]=$rawMaterial['Product']['name'];
		}
    
    //echo "load finished products<br/>";
    $finishedProductTypeIds=$this->ProductType->find('list',array(
      'fields'=>array('ProductType.id'),
      'conditions'=>array(
        'ProductType.product_category_id'=>CATEGORY_PRODUCED,
      ),
    ));
		$finishedProductsAll = $this->Product->find('all', array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array('Product.product_type_id'=> $finishedProductTypeIds),
			'order'=>'Product.name',
		));
		$finishedProducts = null;
		foreach ($finishedProductsAll as $finishedProduct){
			$finishedProducts[$finishedProduct['Product']['id']]=$finishedProduct['Product']['name'];
		}
		
    //echo "load machines<br/>";
		$machines = $this->ProductionRun->Machine->find('list',array(
			'conditions'=>array(
				'Machine.bool_active'=>true,
			),
			'order'=>'Machine.name',
		));
    
    $this->ProductionRun->recursive=-1;
    //echo "load original production run<br/>";
		$originalProductionRun=$this->ProductionRun->find('first',array(
      'fields'=>'ProductionRun.operator_id',
      'conditions'=>array(
        'ProductionRun.id'=>$id,
       )
     ));
    
    //echo "load operators<br/>";
		$operators = $this->ProductionRun->Operator->find('list',array(
			'conditions'=>array(
				'OR'=>array(
					array('Operator.bool_active'=>true,),
					array('Operator.id'=>$originalProductionRun['ProductionRun']['operator_id'],),
				),
			),
			'order'=>'Operator.name',
		));
    
    //echo "load shifts<br/>";
		$shifts = $this->ProductionRun->Shift->find('list');
		
		$this->set(compact('rawMaterials','finishedProducts', 'machines', 'operators', 'shifts','rawMaterialsInventory','resultCodes','usedRawMaterials'));
		
    //echo "load production run types<br/>";
		$productionRunTypes=$this->ProductionRun->ProductionRunType->find('list',array(
			'order'=>'ProductionRunType.name',
		));
		$this->set(compact('productionRunTypes'));
		
		//echo "everything ready except the permissions<br/>";
    //echo "looking up permissions for user ".$this->Auth->User('id')."<br/>";
    
		$aco_name="Operators/reporteProduccionTotal";		
		$bool_operator_totalproductionreport_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_totalproductionreport_permission'));
		
		$aco_name="ProductTypes/index";		
		$bool_producttype_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_index_permission'));
		$aco_name="ProductTypes/add";		
		$bool_producttype_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_producttype_add_permission'));
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
		$aco_name="Machines/index";		
		$bool_machine_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_machine_index_permission'));
		$aco_name="Machines/add";		
		$bool_machine_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_machine_add_permission'));
		$aco_name="Operators/index";		
		$bool_operator_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_index_permission'));
		$aco_name="Operators/add";		
		$bool_operator_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_add_permission'));
		$aco_name="Shifts/index";		
		$bool_shift_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_shift_index_permission'));
		$aco_name="Shifts/add";		
		$bool_shift_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_shift_add_permission'));
	}

	/**
	 * delete method
	 *
	 * @throws NotFoundException
	 * @param string $id
	 * @return void
	 */
	public function delete($id = null) {
		$this->ProductionRun->id = $id;
		if (!$this->ProductionRun->exists()) {
			throw new NotFoundException(__('Invalid production run'));
		}
		$linkedProductionRun=$this->ProductionRun->find('first',array(
      'conditions'=>array(
        'ProductionRun.id'=>$id,
      ),
    ));
		$production_run_code=$linkedProductionRun['ProductionRun']['production_run_code'];
		$this->loadModel('StockItem');
		
		$this->request->allowMethod('post', 'delete');
		
		// find produced stock for production run
		$producedMovements=$this->ProductionRun->ProductionMovement->find('all',array(
			'fields'=>array('stockitem_id','product_quantity','ProductionMovement.id'),
			'conditions'=>array(
				'production_run_id'=>$id,
				'bool_input'=>false,
			),
		));
		$producedStockUntouched=true;
		$salesInvolved=array();
		// now check if the stockitems have not been sold yet
		foreach ($producedMovements as $producedMovement){
			$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$producedMovement['ProductionMovement']['stockitem_id'])));
			if ($stockItem['StockItem']['original_quantity']!=$stockItem['StockItem']['remaining_quantity']){
				$producedStockUntouched=false;
				$this->loadModel('StockMovement');
				$this->loadModel('Order');
				$saleMovementsForStockItem=$this->Order->StockMovement->find('all',array('conditions'=>array('stockitem_id'=>$stockItem['StockItem']['id'])));
				foreach ($saleMovementsForStockItem as $saleMovementForStockItem){
					$saleForMovement=$this->Order->find('first',array('conditions'=>array('Order.id'=>$saleMovementForStockItem['StockMovement']['order_id'])));
					$salesInvolved[]=$saleForMovement['Order']['invoice_code'];
				}
			}
		}
		if (!$producedStockUntouched){
			// produced stock has been sold already
			$flashmessage="No se puede eliminar el orden de producción porque los productos fabricados eran vendidos en salidas con código ";
			for ($i=0;$i<count($salesInvolved);$i++){
				if ($i!=count($salesInvolved)-1){
					$flashmessage.=$salesInvolved[$i].", ";
				}
				else {
					$flashmessage.=$salesInvolved[$i]. "!";
				}
			}
			$this->Session->setFlash($flashmessage, 'default',array('class' => 'error-message'));
		}
		else {
			$datasource=$this->ProductionRun->getDataSource();
			$datasource->begin();
			try {
				// find used stock movements
				$usedMovements=$this->ProductionRun->ProductionMovement->find('all',array(
					'fields'=>array('stockitem_id','product_quantity','ProductionMovement.id'),
					'conditions'=>array(
						'production_run_id'=>$id,
						'bool_input'=>true,
					),
				));
				
				// reestablish stock item quantity & used productionmovements
				foreach ($usedMovements as $usedMovement){
					$stockItem=array();
					$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$usedMovement['ProductionMovement']['stockitem_id'])));
					$stockItem['StockItem']['remaining_quantity']+=$usedMovement['ProductionMovement']['product_quantity'];
					$stockItem['StockItem']['description'].="|eliminated production run ".$production_run_code;

					if (!$this->StockItem->save($stockItem)) {
						echo "problema eliminando el estado de lote";
						pr($this->validateErrors($this->StockItem));
						throw new Exception();
					}
					
					// delete productionmovements
					$this->ProductionRun->ProductionMovement->id=$usedMovement['ProductionMovement']['id'];
					if (!$this->ProductionRun->ProductionMovement->delete()) {
						echo "problema eliminando el movimiento de lote";
						pr($this->validateErrors($this->ProductionRun->ProductionMovement));
						throw new Exception();
					}
				}
				
				// delete produced stockitems & productionmovements
				foreach ($producedMovements as $producedMovement){
					
					$this->StockItem->id=$producedMovement['ProductionMovement']['stockitem_id'];
					if (!$this->StockItem->delete()) {
						echo "problema eliminando el lote fabricado";
						pr($this->validateErrors($this->StockItem));
						throw new Exception();
					}
					
					$this->ProductionRun->ProductionMovement->id=$producedMovement['ProductionMovement']['id'];
					if (!$this->ProductionRun->ProductionMovement->delete()) {
						echo "problema eliminando el movimiento de producción";
						pr($this->validateErrors($this->ProductionRun->ProductionMovement));
						throw new Exception();
					}
				}
			
				// delete productionrun
				$this->ProductionRun->id = $id;
				if (!$this->ProductionRun->delete()) {
					echo "problema eliminando el orden de producción";
					pr($this->validateErrors($this->ProductionRun));
					throw new Exception();
				}
				
				// recreate stockitemlogs
				foreach ($usedMovements as $usedMovement){
					$this->recreateStockItemLogs($usedMovement['ProductionMovement']['stockitem_id']);
				}
		
				$datasource->commit();
				$this->recordUserActivity($this->Session->read('User.username'),"Production run removed with code ".$production_run_code);			
				$this->Session->setFlash(__('The production run has been deleted.'), 'default',array('class' => 'success'));
			} 		
			catch(Exception $e){
				$datasource->rollback();
				pr($e);					
				$this->Session->setFlash(__('The production run could not be deleted. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	public function annul($id = null) {
		$this->ProductionRun->id = $id;
		if (!$this->ProductionRun->exists()) {
			throw new NotFoundException(__('Invalid production run'));
		}
		$linkedProductionRun=$this->ProductionRun->find('first',array(
      'conditions'=>array(
        'ProductionRun.id'=>$id,
      ),
    ));
		$production_run_code=$linkedProductionRun['ProductionRun']['production_run_code'];
		$this->loadModel('StockItem');
		
		$this->request->allowMethod('post', 'delete');
		
		// find produced stock for production run
		$producedMovements=$this->ProductionRun->ProductionMovement->find('all',array(
			'fields'=>array('stockitem_id','product_quantity','ProductionMovement.id'),
			'conditions'=>array(
				'production_run_id'=>$id,
				'bool_input'=>false,
			),
		));
		$producedStockUntouched=true;
		$salesInvolved=array();
		// now check if the stockitems have not been sold yet
		foreach ($producedMovements as $producedMovement){
			$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$producedMovement['ProductionMovement']['stockitem_id'])));
			if ($stockItem['StockItem']['original_quantity']!=$stockItem['StockItem']['remaining_quantity']){
				$producedStockUntouched=false;
				$this->loadModel('StockMovement');
				$this->loadModel('Order');
				$saleMovementsForStockItem=$this->Order->StockMovement->find('all',array('conditions'=>array('stockitem_id'=>$stockItem['StockItem']['id'])));
				foreach ($saleMovementsForStockItem as $saleMovementForStockItem){
					$saleForMovement=$this->Order->find('first',array(
            'conditions'=>array(
              'Order.id'=>$saleMovementForStockItem['StockMovement']['order_id'],
            ),
          ));
					$salesInvolved[]=$saleForMovement['Order']['invoice_code'];
				}
			}
		}
		if (!$producedStockUntouched){
			// produced stock has been sold already
			$flashmessage="No se puede eliminar el orden de producción porque los productos fabricados eran vendidos en salidas con código ";
			for ($i=0;$i<count($salesInvolved);$i++){
				if ($i!=count($salesInvolved)-1){
					$flashmessage.=$salesInvolved[$i].", ";
				}
				else {
					$flashmessage.=$salesInvolved[$i]. "!";
				}
			}
			$this->Session->setFlash($flashmessage, 'default',array('class' => 'error-message'));
		}
		else {
			$datasource=$this->ProductionRun->getDataSource();
			$datasource->begin();
			try {
				// find used stock movements
				$usedMovements=$this->ProductionRun->ProductionMovement->find('all',array(
					'fields'=>array('stockitem_id','product_quantity','ProductionMovement.id'),
					'conditions'=>array(
						'production_run_id'=>$id,
						'bool_input'=>true,
					),
				));
				
				// reestablish stock item quantity & used productionmovements
				foreach ($usedMovements as $usedMovement){
					$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$usedMovement['ProductionMovement']['stockitem_id'])));
					
					$stockItem['StockItem']['remaining_quantity']+=$usedMovement['ProductionMovement']['product_quantity'];
					$stockItem['StockItem']['description'].="|eliminated production run ".$production_run_code;
					$success=$this->StockItem->save($stockItem);
					if (!$success) {
						echo "problema eliminando el estado de lote";
						pr($this->validateErrors($this->StockItem));
						throw new Exception();
					}
					
					// delete productionmovements
					$this->ProductionRun->ProductionMovement->id=$usedMovement['ProductionMovement']['id'];
					$success=$this->ProductionRun->ProductionMovement->delete();
					if (!$success) {
						echo "problema eliminando el movimiento de lote";
						pr($this->validateErrors($this->ProductionRun->ProductionMovement));
						throw new Exception();
					}
				}
				
				// delete produced stockitems & productionmovements
				foreach ($producedMovements as $producedMovement){
					
					$this->StockItem->id=$producedMovement['ProductionMovement']['stockitem_id'];
					$success=$this->StockItem->delete();
					if (!$success) {
						echo "problema eliminando el lote fabricado";
						pr($this->validateErrors($this->StockItem));
						throw new Exception();
					}
					
					$this->ProductionRun->ProductionMovement->id=$producedMovement['ProductionMovement']['id'];
					$success=$this->ProductionRun->ProductionMovement->delete();
					if (!$success) {
						echo "problema eliminando el movimiento de producción";
						pr($this->validateErrors($this->ProductionRun->ProductionMovement));
						throw new Exception();
					}
				}
			
				// delete productionrun
				$success=$this->ProductionRun->delete();
				if (!$success) {
					echo "problema eliminando el orden de producción";
					pr($this->validateErrors($this->ProductionRun));
					throw new Exception();
				}
				
				// recreate stockitemlogs
				foreach ($usedMovements as $usedMovement){
					$this->recreateStockItemLogs($usedMovement['ProductionMovement']['stockitem_id']);
				}
		
				$datasource->commit();
				$this->recordUserActivity($this->Session->read('User.username'),"Production run removed with code ".$production_run_code);			
				$this->Session->setFlash(__('The production run has been deleted.'), 'default',array('class' => 'success'));
			} 		
			catch(Exception $e){
				$datasource->rollback();
				pr($e);					
				$this->Session->setFlash(__('The production run could not be deleted. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	
	public function downloadPdf($name=null) { 
		$outputproduction = $_SESSION['output_produccion'];
		
		App::import('Vendor','xtcpdf');
		$pdf = new XTCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
		//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->AddPage();
		$html = $outputproduction;
		$pdf->SetFontSize(6,true);
		$pdf->writeHTML($html, true, false, true, false, '');
		$pdf->lastPage();
		
		//echo $pdf->Output(APP.'files/pdf'. DS . $name, 'F');
		echo $pdf->Output('E:/' . DS . $name, 'F');
		//echo "pdf 'E:/".DS.$name." generated";
	
		$this->viewClass = 'Media';
		$filename=substr($name,0,strpos($name,"."));
		$params = array(
			'id' => $name,
			'name' => $filename ,
			'download' => true,
			'extension' => 'pdf',
			'path' => 'E:/'
		);
		$this->set($params);
	}
	
}

