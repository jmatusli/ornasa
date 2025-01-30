<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ProductionRunsController extends AppController {

	public $components = ['Paginator','RequestHandler'];
	//public $helpers = array('PhpExcel','InventoryCountDisplay');
	public $helpers = ['PhpExcel'];

	public function beforeFilter(){
		parent::beforeFilter();
		// Allow users to register and logout.
		$this->Auth->allow('getProductionRunCode','checkacceptability','guardarResumenOrdenesDeProduccion','detallePdf');
	}
	
  public function getProductionRunCode(){
    $this->autoRender = false; 
		$this->request->onlyAllow('ajax'); 
		$this->layout = "ajax";
		$plantId=trim($_POST['plantId']);
    
    $this->loadModel('Plant');
    
    return $this->ProductionRun->getProductionRunCode($plantId,$this->Plant->getPlantShortName($plantId));
  }

  public function resumen() {
    $this->loadModel('Machine');
    $this->loadModel('ProductType');
    $this->loadModel('Product');
    $this->loadModel('PlantProductionResultCode');
		
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    $this->loadModel('UserPageRight');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $canSeeUtilityTables=$this->UserPageRight->hasUserPageRight('VER_RESUMEN_EJECUTIVO',$userRoleId,$loggedUserId,'ProductionRuns','resumen');
    $this->set(compact('canSeeUtilityTables'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $plantId=0;
    
		$startDate = null;
		$endDate = null;
		
    define('PRODUCTION_RUNS_ALL','0');
    define('PRODUCTION_RUNS_ACCEPTABLE','1');
    define('PRODUCTION_RUNS_UNACCEPTABLE','2');
    
    $acceptableOptions=[
      PRODUCTION_RUNS_ALL=>'Todas Ordenes de Producción',
      PRODUCTION_RUNS_ACCEPTABLE=>'Solo Ordenes aceptables',
      PRODUCTION_RUNS_UNACCEPTABLE=>'Solo Ordenes no aceptables',
    ];
    $this->set(compact('acceptableOptions'));
    
    $acceptableOptionId=0;
    $selectedProductId=0;
		$selectedShiftId=0;
    
		if ($this->request->is('post')) {
      $plantId=$this->request->data['Report']['plant_id'];
      
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
      $acceptableOptionId=$this->request->data['Report']['acceptable_option_id'];
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
    $this->set(compact('acceptableOptionId'));
		$this->set(compact('selectedProductId'));
		$this->set(compact('selectedShiftId'));
    
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
    
    
    $machineIds=array_keys($this->Machine->getMachineListForPlant($plantId));
    $machines=$this->Machine->getActiveMachines($machineIds);
    for ($m=0;$m<count($machines);$m++){
      $machines[$m]['machineUtility']=$this->Machine->getMachineUtility($machines[$m]['Machine']['id'],$startDate,$endDate);
    }
    $this->set(compact('machines'));
    
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
		$productionResultCodes=$this->PlantProductionResultCode->getProductionResultCodesForPlant($plantId);
    $this->set(compact('productionResultCodes'));
		
		$minimumConditions=[
      'ProductionRun.plant_id'=> $plantId,
			'ProductionRun.production_run_date >='=> $startDate,
			'ProductionRun.production_run_date <'=> $endDatePlusOne,
		];
    $productionRunConditions=$minimumConditions;
		if ($selectedProductId>0){
			$productionRunConditions['ProductionRun.finished_product_id']= $selectedProductId;
		}
		if ($selectedShiftId>0){
			$productionRunConditions['ProductionRun.shift_id']= $selectedShiftId;
		}
		
    $productionRunCount=$this->ProductionRun->find('count', [
			'conditions' =>$productionRunConditions,
		]);
		$this->Paginator->settings = [
			'conditions' => $productionRunConditions,
			'contain'=>[
				'Operator',
				'Shift',
				'Machine',
				'RawMaterial',
				'FinishedProduct'=>[
					'ProductProduction'=>[
						'conditions'=>[
							'ProductProduction.application_date <'=> $endDatePlusOne,
						],
					],
				],
				'ProductionMovement',
        'Incidence',
			],
			
			'order'=>'production_run_date DESC, production_run_code DESC',
			'limit'=>($productionRunCount!=0?$productionRunCount:1),
		];
		
		$allProductionRuns=$this->Paginator->paginate();
    //pr($allProductionRuns);
    $productionRuns=[];
    $millProductDefault=[
      'productName'=>'',
      'productQuantity'=>0,
      'productionRuns'=>[],
    ];
    $millProducts=[];
    
    for ($pr=0;$pr<count($allProductionRuns);$pr++){
      $boolAcceptable=$this->ProductionRun->checkAcceptableProduction($allProductionRuns[$pr]['ProductionRun']['id'],$allProductionRuns[$pr]['ProductionRun']['production_run_date']);
      if (($boolAcceptable && $acceptableOptionId != PRODUCTION_RUNS_UNACCEPTABLE)||(!$boolAcceptable && $acceptableOptionId!=PRODUCTION_RUNS_ACCEPTABLE)){
        $productionRuns[]=$allProductionRuns[$pr];
        if ($plantId == PLANT_COLINAS){
          foreach ($allProductionRuns[$pr]['ProductionMovement'] as $productionMovement){
            if($productionMovement['production_result_code_id'] == PRODUCTION_RESULT_CODE_MILL && $productionMovement['product_quantity'] > 0){
              //pr($productionMovement);  
              if (!array_key_exists($productionMovement['product_id'],$millProducts)){
                $millProducts[$productionMovement['product_id']]=$millProductDefault;
                $millProducts[$productionMovement['product_id']]['productName']=(array_key_exists($productionMovement['product_id'],$rawMaterialList)?$rawMaterialList[$productionMovement['product_id']]:"");
              }
              $millProducts[$productionMovement['product_id']]['productQuantity']+=$productionMovement['product_quantity'];
              $millProducts[$productionMovement['product_id']]['productionRuns'][$allProductionRuns[$pr]['ProductionRun']['id']]=$allProductionRuns[$pr]['ProductionRun']['production_run_code']." (".$productionMovement['product_quantity'].")";
            }
          }
        }        
      }
    }
    //pr($productionRuns);
    //pr($millProducts);
		usort($millProducts,[$this,'sortByProductName']);
    
    $this->set(compact('productionRuns'));
		$this->set(compact('millProducts'));
		$shiftConditions=$minimumConditions;
    $shiftConditions['ProductionRun.bool_annulled']= false;
		if ($selectedProductId>0){
			$shiftConditions['ProductionRun.finished_product_id']= $selectedProductId;
		}
		$this->ProductionRun->Shift->recursive=-1;
		$shiftTotals=$this->ProductionRun->Shift->find('all',[
			'fields'=>['Shift.id','Shift.name'],
			'contain'=>[
				'ProductionRun'=>[
					'fields'=>['ProductionRun.id','ProductionRun.production_run_date'],
					'conditions' => $shiftConditions,
				],
			],
		]);
		
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
    
    $machineConditions=$minimumConditions;
    $machineConditions['ProductionRun.bool_annulled']= false;
		if ($selectedProductId>0){
			$machineConditions['ProductionRun.finished_product_id']=$selectedProductId;
		}
		//pr($machineIds);
		$machineTotals=$this->ProductionRun->Machine->find('all',[
			'fields'=>['Machine.id','Machine.name'],
      'conditions'=>[
        'Machine.bool_active'=>true,
        'Machine.id'=>$machineIds,
      ],
			'contain'=>[
				'ProductionRun'=>[
					'fields'=>['ProductionRun.id','ProductionRun.production_run_date'],
					'conditions' => $machineConditions,
          'ProductionMovement'=>[
            'conditions'=>[
              'ProductionMovement.product_quantity >'=>0,
              'ProductionMovement.bool_input'=>'0',
            ]
          ],
				],
			],
      'order'=>'Machine.name ASC',
		]);
		
		for ($s=0;$s<count($machineTotals);$s++){
			$quantities=['total'=>0];
      foreach ($productionResultCodes as $productionResultCodeId=>$resultCode){
        $quantities[$productionResultCodeId] = 0;
      }
      $acceptableRunCounter=0;  
      if (!empty($machineTotals[$s]['ProductionRun'])){
        
				for ($p=0;$p<count($machineTotals[$s]['ProductionRun']);$p++){
          foreach ($machineTotals[$s]['ProductionRun'][$p]['ProductionMovement'] as $productionMovement){
             $quantities[$productionMovement['production_result_code_id']]+=$productionMovement['product_quantity'];
             $quantities['total']+=$productionMovement['product_quantity'];
             
          }
          $boolAcceptable=$this->ProductionRun->checkAcceptableProduction($machineTotals[$s]['ProductionRun'][$p]['id'],$machineTotals[$s]['ProductionRun'][$p]['production_run_date']);
					$machineTotals[$s]['ProductionRun'][$p]['bool_acceptable']=$boolAcceptable;
					if ($boolAcceptable){
						$acceptableRunCounter++;
					}
          //pr($machineTotals[$s]);
				}
			}
			$machineTotals[$s]['Machine']['quantities']=$quantities;
      $machineTotals[$s]['Machine']['acceptable_runs']=$acceptableRunCounter;
		}
		//pr($machineTotals);
		$this->set(compact('machineTotals'));
   
		$finishedProductsPresentInProductionRuns=$this->ProductionRun->find('list',[
			'fields'=>['ProductionRun.finished_product_id'],
			'conditions'=>$minimumConditions,
		]);
		$finishedProducts=$this->Product->find('list',[
			'conditions'=>[
				'Product.id'=>$finishedProductsPresentInProductionRuns,
			],
			'order'=>'Product.name ASC',
		]);
		$this->set(compact('finishedProducts'));
		
		$productionRunConditions=$minimumConditions;
    $productionRunConditions['ProductionRun.bool_annulled']= false;
		if ($selectedProductId>0){
			$productionRunConditions['ProductionRun.finished_product_id']= $selectedProductId;
		}
		$shiftIdsInProductionRuns=$this->ProductionRun->find('list',[
			'fields'=>['ProductionRun.shift_id'],
			'conditions' => $productionRunConditions,
		]);
		
		$shifts=$this->ProductionRun->Shift->find('list',[
			'fields'=>['Shift.id','Shift.name'],
			'conditions' => [
				'Shift.id'=>$shiftIdsInProductionRuns,
			],
		]);
		$this->set(compact('shifts'));
    
    if ($plantId == PLANT_COLINAS){
      $finishedProductTotals=[];
      foreach ($finishedProducts as $currentFinishedProductId=>$finishedProductName){   
        $finishedProductTotals[$currentFinishedProductId]=$this->Product->getProductProductionRunUtility($currentFinishedProductId,$startDate,$endDate);
      }
    }
		//pr($finishedProductTotals);
		$this->set(compact('finishedProductTotals'));
		
		$aco_name="Operators/reporteProduccionTotal";		
		$bool_operator_totalproductionreport_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_totalproductionreport_permission'));
		
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
	}
  
  public function sortByProductName($a,$b ){ 
	  if( $a['productName'] == $b['productName'] ){ return 0 ; } 
	  return ($a['productName'] < $b['productName']) ? -1 : 1;
	} 
/*
  public function index() {
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
		$startDate = null;
		$endDate = null;
		
    define('PRODUCTION_RUNS_ALL','0');
    define('PRODUCTION_RUNS_ACCEPTABLE','1');
    define('PRODUCTION_RUNS_UNACCEPTABLE','2');
    
    $acceptableOptions=[
      PRODUCTION_RUNS_ALL=>'Todas Ordenes de Producción',
      PRODUCTION_RUNS_ACCEPTABLE=>'Solo Ordenes aceptables',
      PRODUCTION_RUNS_UNACCEPTABLE=>'Solo Ordenes no aceptables',
    ];
    $this->set(compact('acceptableOptions'));
    
    $acceptableOptionId=0;
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
			
      $acceptableOptionId=$this->request->data['Report']['acceptable_option_id'];
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
    $this->set(compact('acceptableOptionId'));
		$this->set(compact('selectedProductId'));
		$this->set(compact('selectedShiftId'));
    
    $this->loadModel('Machine');
    $this->Machine->recursive=-1;
    $machines=$this->Machine->find('all',[
      'fields'=>['Machine.id,Machine.name'],
      'conditions'=>['Machine.bool_active'=>true],
      'order'=>'Machine.name',
    ]);
    
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
        'Incidence',
			),
			
			'order'=>'production_run_date DESC, production_run_code DESC',
			'limit'=>($productionRunCount!=0?$productionRunCount:1)
		);
		
		$this->loadModel('ProductionResultCode');
		$this->ProductionResultCode->recursive=-1;
		$resultCodes = $this->ProductionResultCode->find('all');
		
		$allProductionRuns=$this->Paginator->paginate();
    //pr($allProductionRuns);
    $productionRuns=[];
    
    for ($pr=0;$pr<count($allProductionRuns);$pr++){
      $boolAcceptable=$this->ProductionRun->checkAcceptableProduction($allProductionRuns[$pr]['ProductionRun']['id'],$allProductionRuns[$pr]['ProductionRun']['production_run_date']);
      if (($boolAcceptable && $acceptableOptionId!=PRODUCTION_RUNS_UNACCEPTABLE)||(!$boolAcceptable && $acceptableOptionId!=PRODUCTION_RUNS_ACCEPTABLE)){
          $productionRuns[]=$allProductionRuns[$pr];
      }
    }
    //pr($productionRuns);
    
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
              'ProductionMovement.bool_input'=>'0',
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
   
		$this->set(compact('resultCodes'));
		
		
		
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
		
		$aco_name="Products/index";		
		$bool_product_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_index_permission'));
		$aco_name="Products/add";		
		$bool_product_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_product_add_permission'));
	}
*/
	public function guardarResumenOrdenesDeProduccion() {
		$exportData=$_SESSION['resumenOrdenesProduccion'];
		$this->set(compact('exportData'));
	}
	
  public function detalle($id = null) {
		if (!$this->ProductionRun->exists($id)) {
			throw new NotFoundException(__('Invalid production run'));
		}
    
    $this->loadModel('PlantProductionResultCode');
    $this->loadModel('StockItem');
		$this->loadModel('ProductType');
		
		$options = [
			'conditions' => ['ProductionRun.id' => $id,],
			'contain'=>[
        'Plant',
				'Machine',
				'Operator',
				'Shift',
				'RawMaterial',
				'FinishedProduct',
				'ProductionMovement'=>[
					'StockItem'=>['Product'],
          'Unit',
				],
        'ProductionLoss'=>[
          'Unit'
        ],
        'Incidence',
        'Recipe'=>[
          'MillConversionProduct'
        ],
			],
		];
    
    $productionRun=$this->ProductionRun->find('first', $options);
		$this->set(compact('productionRun'));
    
    $productionResultCodes = $this->PlantProductionResultCode->getProductionResultCodesForPlant($productionRun['ProductionRun']['plant_id']);
		$this->set(compact('productionResultCodes'));
    
    //pr($productionRun);
		$productid=$productionRun['ProductionRun']['raw_material_id'];
		$quantityneeded=$productionRun['ProductionRun']['raw_material_quantity'];
		
		switch ($productionRun['ProductionRun']['production_type_id']){
      case PRODUCTION_TYPE_PET:
        $rawProductTypeIds=[PRODUCT_TYPE_PREFORMA];
        break;
      case PRODUCTION_TYPE_INJECTION:
        $rawProductTypeIds=[PRODUCT_TYPE_INJECTION_GRAIN];
        break;
      case PRODUCTION_TYPE_FILLING:
        break;
      default:
        $rawProductTypeIds=$this->ProductType->find('list',[
          'fields'=>['ProductType.id'],
          'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW],
        ]);
    }
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_RAW,$rawProductTypeIds);
		$energyConsumption=$this->ProductionRun->getEnergyUseForRun($id);
		$this->set(compact('rawMaterialsInventory','energyConsumption'));
    
    $totalUsed=0;
    $totalUsedConsumables=0;
    $usedConsumablesArray=[];
    foreach ($productionRun['ProductionMovement'] as $productionMovement){
			if ($productionMovement['bool_input'] && $productionMovement['product_quantity'] > 0){				
        //pr($productionMovement);
        if ($productionMovement['StockItem']['Product']['product_type_id']==PRODUCT_TYPE_PREFORMA){
          $totalUsed+=$productionMovement['product_quantity'];
        }
        else {
          $totalUsedConsumables+=$productionMovement['product_quantity'];
          $productId=$productionMovement['StockItem']['Product']['id'];
          if(!array_key_exists($productId,$usedConsumablesArray)){
            $usedConsumablesArray[$productId]=[
              'consumable_name'=>$productionMovement['StockItem']['Product']['name'],
              'product_quantity'=>0,
            ];
          }
          $usedConsumablesArray[$productId]['product_quantity']+=$productionMovement['product_quantity'];
        }
      }
    }
    //pr($usedConsumablesArray);
    $this->set(compact('usedConsumablesArray'));
    
    $editabilityData=$this->ProductionRun->getEditabilityData($id);
    $this->set(compact('editabilityData'));  
    
    $plantId=$productionRun['ProductionRun']['plant_id'];
    $this->set(compact('plantId'));  
		
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

	public function detallePdf($id = null) {
		if (!$this->ProductionRun->exists($id)) {
			throw new NotFoundException(__('Invalid production run'));
		}
    
    $this->loadModel('PlantProductionResultCode');
    $this->loadModel('StockItem');
		$this->loadModel('ProductType');
		
		$productionRun = $this->ProductionRun->find('first', [
      'conditions' => ['ProductionRun.id' => $id],
      'contain'=>[
				'Plant',
				'Machine',
				'Operator',
				'Shift',
				'RawMaterial',
				'FinishedProduct',
				'ProductionMovement'=>[
					'StockItem'=>['Product'],
          'Unit',
				],
        'ProductionLoss'=>[
          'Unit'
        ],
        'Incidence',
        'Recipe'=>[
          'MillConversionProduct'
        ],
			],
    ]);
		$this->set(compact('productionRun'));
    
        $productionResultCodes = $this->PlantProductionResultCode->getProductionResultCodesForPlant($productionRun['ProductionRun']['plant_id']);
		$this->set(compact('productionResultCodes'));
    
    //pr($productionRun);
		$productid=$productionRun['ProductionRun']['raw_material_id'];
		$quantityneeded=$productionRun['ProductionRun']['raw_material_quantity'];
		
		switch ($productionRun['ProductionRun']['production_type_id']){
      case PRODUCTION_TYPE_PET:
        $rawProductTypeIds=[PRODUCT_TYPE_PREFORMA];
        break;
      case PRODUCTION_TYPE_INJECTION:
        $rawProductTypeIds=[PRODUCT_TYPE_INJECTION_GRAIN];
        break;
      case PRODUCTION_TYPE_FILLING:
        break;
      default:
        $rawProductTypeIds=$this->ProductType->find('list',[
          'fields'=>['ProductType.id'],
          'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW],
        ]);
    }
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_RAW,$rawProductTypeIds);
		$energyConsumption=$this->ProductionRun->getEnergyUseForRun($id);
		$this->set(compact('rawMaterialsInventory','energyConsumption'));
    
    $totalUsed=0;
    $totalUsedConsumables=0;
    $usedConsumablesArray=[];
    foreach ($productionRun['ProductionMovement'] as $productionMovement){
			if ($productionMovement['bool_input'] && $productionMovement['product_quantity']>0){				
        //pr($productionMovement);
        if ($productionMovement['StockItem']['Product']['product_type_id']==PRODUCT_TYPE_PREFORMA){
          $totalUsed+=$productionMovement['product_quantity'];
        }
        else {
          $totalUsedConsumables+=$productionMovement['product_quantity'];
          $productId=$productionMovement['StockItem']['Product']['id'];
          if(!array_key_exists($productId,$usedConsumablesArray)){
            $usedConsumablesArray[$productId]=[
              'consumable_name'=>$productionMovement['StockItem']['Product']['name'],
              'product_quantity'=>0,
            ];
          }
          $usedConsumablesArray[$productId]['product_quantity']+=$productionMovement['product_quantity'];
        }
      }
    }
    $this->set(compact('usedConsumablesArray'));
    
    $this->loadModel('Warehouse');
    $warehouseIds=array_keys($this->Warehouse->getWarehousesForPlantId($productionRun['ProductionRun']['plant_id']));
		
    $remainingPreformaProductionRunDate= empty($productionRun['RawMaterial']['id'])?-1:$this->StockItem->getInventoryTotalPerProduct($productionRun['RawMaterial']['id'],$productionRun['ProductionRun']['production_run_date'],$warehouseIds);
    $bagId=(empty($productionRun['ProductionRun']['bag_product_id'])?PRODUCT_BAG:$productionRun['ProductionRun']['bag_product_id']);
    $remainingBagProductionRunDate= $this->StockItem->getInventoryTotalPerProduct($bagId,$productionRun['ProductionRun']['production_run_date'],$warehouseIds);
    $this->set(compact('remainingPreformaProductionRunDate','remainingBagProductionRunDate'));
    $nowDateTime= new DateTime(); 
    $remainingPreformaNow= empty($productionRun['RawMaterial']['id'])?-1:$this->StockItem->getInventoryTotalPerProduct($productionRun['RawMaterial']['id'],$nowDateTime->format('Y-m-d'));
    
    $this->set(compact('remainingPreformaNow'));
   
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
    
    $warehouseId=WAREHOUSE_DEFAULT;
		
		$relatedRawMaterial=$this->Product->find('first',array(
			'conditions'=>array(
				'Product.id'=>$requestData['ProductionRun']['raw_material_id'],
			),
		));
		$requestData['ProductionRun']['raw_material_name']=$relatedRawMaterial['Product']['name'];
		$relatedFinishedProduct=$this->Product->find('first',array('conditions'=>array('Product.id'=>$requestData['ProductionRun']['finished_product_id'])));
		$requestData['ProductionRun']['finished_product_name']=$relatedFinishedProduct['Product']['name'];
    
    //$relatedConsumableMaterial=$this->Product->find('first',[
		//	'conditions'=>['Product.id'=>$requestData['ProductionRun']['consumable_material_id'],],
		//]);
    //if (!empty($relatedConsumableMaterial)){
    //  $requestData['ProductionRun']['consumable_material_name']=$relatedConsumableMaterial['Product']['name'];
    //}
    //else {
    //  $requestData['ProductionRun']['consumable_material_name']='';
    //}
		
		
		//pr($requestData);
		
		$rawMaterialId=$requestData['ProductionRun']['raw_material_id'];
		$finishedProductId=$requestData['ProductionRun']['finished_product_id'];
		$productionRunDateAsString=$this->ProductionRun->deconstruct('production_run_date',$requestData['ProductionRun']['production_run_date']);
		
		
		$quantityRawMaterialInStock=$this->StockItemLog->getStockQuantityAtDateForProduct($rawMaterialId,$productionRunDateAsString,WAREHOUSE_DEFAULT,true);
		$quantityFinishedCInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($finishedProductId,$rawMaterialId,PRODUCTION_RESULT_CODE_C,$productionRunDateAsString,WAREHOUSE_DEFAULT);
		$quantityFinishedBInStock=$this->StockItemLog->getStockQuantityAtDateForFinishedProduct($finishedProductId,$rawMaterialId,PRODUCTION_RESULT_CODE_B,$productionRunDateAsString,WAREHOUSE_DEFAULT);
		
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
						$StockMovementData['bool_input']='0';
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
					$this->Session->setFlash(__('Reclasificación exitosa'),'default',['class' => 'success']);
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          $boolReclassificationSuccess='0';
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
						$StockMovementData['bool_input']='0';
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
					$this->Session->setFlash(__('Reclasificación exitosa'),'default',['class' => 'success']);
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          $boolReclassificationSuccess='0';
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
							$StockMovementData['bool_input']='0';
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
						
						$this->Session->setFlash(__('Reclasificación exitosa'),'default',['class' => 'success']);
					}
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Reclasificación falló'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          $boolReclassificationSuccess='0';
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
						$StockMovementData['bool_input']='0';
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
					$this->Session->setFlash(__('Reclasificación exitosa'),'default',['class' => 'success']);
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
          
          $productionRunId=$this->ProductionRun->id;
          $productionruncode=$this->request->data['ProductionRun']['production_run_code'] ;
          $productionRunDate = $this->request->data['ProductionRun']['production_run_date'];
          $productionRunDateAsString = $this->ProductionRun->deconstruct('production_run_date', $this->request->data['ProductionRun']['production_run_date']);
          //pr($productionRunDate);  
          // step 1: insert production movements for the raw materials and update the corresponding stock items
          $rawmaterialid=$this->request->data['ProductionRun']['raw_material_id'];
          $relatedRawMaterial=$this->Product->find('first',array(
            'conditions'=>array(
              'Product.id'=>$rawmaterialid,
            ),
          ));
          $rawMaterialName=$relatedRawMaterial['Product']['name'];
          $rawMaterialQuantity=$this->request->data['ProductionRun']['raw_material_quantity'];
          $usedRawMaterials= $this->StockItem->getRawMaterialsForProductionRun($rawmaterialid,$rawMaterialQuantity,$productionRunDateAsString,$warehouseId);
          
          //$bagId=PRODUCT_BAG;
          $bagId=$this->request->data['ProductionRun']['bag_product_id'];
          $bagQuantity=$this->request->data['ProductionRun']['bag_quantity'];
          $usedBags= $this->StockItem->getRawMaterialsForProductionRun($bagId,$bagQuantity,$productionRunDateAsString,$warehouseId);
          
          $consumableMaterialId=$this->request->data['ProductionRun']['consumable_material_id'];
          $consumableMaterialQuantity=$this->request->data['ProductionRun']['consumable_material_quantity'];
          $usedConsumableMaterials= $this->StockItem->getRawMaterialsForProductionRun($consumableMaterialId,$consumableMaterialQuantity,$productionRunDateAsString,$warehouseId);

          $rawUnitPrice=0;
          $finishedUnitPrice=0;
          $totalRawCost=0;
          $totalBagCost=0;
          $totalConsumableCost=0;
          $i=0;
          if (empty($usedRawMaterials)){
            echo "problema al buscar los materiales usados<br/>";
            throw new Exception();
          }
          
          else {
            foreach ($usedRawMaterials as $usedRawMaterial){
              $stockitemid= $usedRawMaterial['id'];
              $rawname= $usedRawMaterial['name'];
              
              $quantityPresent=$usedRawMaterial['quantity_present'];
              $quantityUsed= $usedRawMaterial['quantity_used'];
              $rawUnitPrice=$usedRawMaterial['unit_price'];
              $totalRawCost+=$rawUnitPrice*$quantityUsed;
              $quantityRemaining=$usedRawMaterial['quantity_remaining'];
              
              $message = "Used quantity ".$quantityUsed." of raw product ".$rawname." in product run ".$productionruncode." (prior to run: ".$quantityPresent."|remaining: ".$quantityRemaining.")";
              
              $StockItemData=[];
              $StockItemData['StockItem']['id']=$stockitemid;
              $StockItemData['StockItem']['remaining_quantity']=$quantityRemaining;
              
              if (!$this->StockItem->save($StockItemData)) {
                echo "problema guardando el lote";
                pr($this->validateErrors($this->StockItem));
                throw new Exception();
              }
              
              $RawProductionMovement['ProductionMovement']['name']=$reclassificationDateString."_".$productionruncode."_".$rawname;
              $RawProductionMovement['ProductionMovement']['description']=$message;
              $RawProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
              $RawProductionMovement['ProductionMovement']['bool_input']=true;
              $RawProductionMovement['ProductionMovement']['stockitem_id']=$stockitemid;
              $RawProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
              $RawProductionMovement['ProductionMovement']['product_id']=$rawmaterialid;
              $RawProductionMovement['ProductionMovement']['product_quantity']=$quantityUsed;
              $RawProductionMovement['ProductionMovement']['product_unit_price']=$rawUnitPrice;
              
              $this->ProductionMovement->create();
              if (!$this->ProductionMovement->save($RawProductionMovement['ProductionMovement'])) {
                echo "problema al guardar el movimiento de producción";
                pr($this->validateErrors($this->ProductionMovement));
                throw new Exception();
              }
                          
              $this->recordUserActivity($this->Session->read('User.username'),$message);
              $i++;
            }
            
            if (!empty($usedBags)){
              foreach ($usedBags as $usedBag){
                //pr ($usedBag);
                $stockItemId= $usedBag['id'];
                $bagName= $usedBag['name'];
                
                $quantityPresent=$usedBag['quantity_present'];
                $quantityUsed= $usedBag['quantity_used'];
                $bagUnitPrice=$usedBag['unit_price'];
                $totalBagCost+=$bagUnitPrice*$quantityUsed;
                $quantityRemaining=$usedBag['quantity_remaining'];
                
                $message = "Consumo de cantidad ".$quantityUsed." de producto consumable ".$bagName." en orden de producción ".$productionruncode." (antes de orden: ".$quantityPresent."|sobrante: ".$quantityRemaining.")";
                
                $StockItemData=[];
                $StockItemData['StockItem']['id']=$stockItemId;
                $StockItemData['StockItem']['remaining_quantity']=$quantityRemaining;
                
                if (!$this->StockItem->save($StockItemData)) {
                  echo "problema guardando el lote de bolsas";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                
                $bagProductionMovement=[];
                $bagProductionMovement['ProductionMovement']['name']=$reclassificationDateString."_".$productionruncode."_".$bagName;
                $bagProductionMovement['ProductionMovement']['description']=$message;
                $bagProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
                $bagProductionMovement['ProductionMovement']['bool_input']=true;
                $bagProductionMovement['ProductionMovement']['stockitem_id']=$stockItemId;
                $bagProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
                //$bagProductionMovement['ProductionMovement']['product_id']=PRODUCT_BAG;
                $bagProductionMovement['ProductionMovement']['product_id']=$bagId;
                $bagProductionMovement['ProductionMovement']['product_quantity']=$quantityUsed;
                $bagProductionMovement['ProductionMovement']['product_unit_price']=$bagUnitPrice;
                
                $this->ProductionMovement->create();
                if (!$this->ProductionMovement->save($bagProductionMovement['ProductionMovement'])) {
                  echo "problema guardando el movimiento de producción para bolsas";
                  pr($this->validateErrors($this->ProductionMovement));
                  throw new Exception();
                }
                
                $this->recordUserActivity($this->Session->read('User.username'),$message);
                $i++;
              }
            }
                               
            if (!empty($consumablesArray)){
              foreach ($consumablesArray as $consumableMaterialId=>$consumableMaterialQuantity){
                $usedConsumableMaterials= $this->StockItem->getRawMaterialsForProductionRun($consumableMaterialId,$consumableMaterialQuantity,$productionRunDateAsString,$warehouseId);
                $consumableCost=0;
                if (!empty($usedConsumableMaterials)){
                  foreach ($usedConsumableMaterials as $usedConsumableMaterial){
                    $consumableStockItemsArray[]=$usedConsumableMaterial['id'];
                    //pr ($usedConsumableMaterial);
                    $stockItemId= $usedConsumableMaterial['id'];
                    $consumableName= $usedConsumableMaterial['name'];
                    
                    $quantityPresent=$usedConsumableMaterial['quantity_present'];
                    $quantityUsed= $usedConsumableMaterial['quantity_used'];
                    $consumableUnitPrice=$usedConsumableMaterial['unit_price'];
                    $totalConsumableCost+=$consumableUnitPrice*$quantityUsed;
                    $quantityRemaining=$usedConsumableMaterial['quantity_remaining'];
                    
                    $message = "Consumo de cantidad ".$quantityUsed." de producto consumable ".$consumableName." en orden de producción ".$productionruncode." (antes de orden: ".$quantityPresent."|sobrante: ".$quantityRemaining.")";
                    
                    $StockItemData=[];
                    $StockItemData['StockItem']['id']=$stockItemId;
                    $StockItemData['StockItem']['remaining_quantity']=$quantityRemaining;
                    
                    if (!$this->StockItem->save($StockItemData)) {
                      echo "problema guardando el lote";
                      pr($this->validateErrors($this->StockItem));
                      throw new Exception();
                    }
                    
                    $consumableProductionMovement=[];
                    $consumableProductionMovement['ProductionMovement']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionruncode."_".$consumableName;
                    $consumableProductionMovement['ProductionMovement']['description']=$message;
                    $consumableProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
                    $consumableProductionMovement['ProductionMovement']['bool_input']=true;
                    $consumableProductionMovement['ProductionMovement']['stockitem_id']=$stockItemId;
                    $consumableProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
                    $consumableProductionMovement['ProductionMovement']['product_id']=$consumableMaterialId;
                    $consumableProductionMovement['ProductionMovement']['product_quantity']=$quantityUsed;
                    $consumableProductionMovement['ProductionMovement']['product_unit_price']=$consumableUnitPrice;
                    
                    $this->ProductionMovement->create();
                    if (!$this->ProductionMovement->save($consumableProductionMovement['ProductionMovement'])) {
                      echo "problema guardando el movimiento de producción para suministro adicional";
                      pr($this->validateErrors($this->ProductionMovement));
                      throw new Exception();
                    }
                    
                    $this->recordUserActivity($this->Session->read('User.username'),$message);
                    $i++;
                  }
                }
              }
            }
                    
            $finishedUnitPrice=$totalRawCost/$rawMaterialQuantity;
            
            // step 2: create new stock items for the produced products
            for ($c=0;$c<sizeof($resultCodes);$c++){
              $code=$resultCodes[$c]['ProductionResultCode']['code'];
              $quantityProduced=$this->request->data['Stockitems'][$resultCodes[$c]['ProductionResultCode']['code']];
              $finishedProductId=$this->request->data['ProductionRun']['finished_product_id'];
              $movementdate=$productionRunDate;
              $message = "Produced quantity ".$quantityProduced." of product type ".$finishedProductId." quality ".$code." in product run ".$productionruncode;
              $linkedProduct=$this->Product->find('first',array(
                'conditions'=>array(
                  'Product.id'=>$finishedProductId,
                ),
              ));
              $product_name=$linkedProduct['Product']['name'];
              
              $finishedItem=[];
              $finishedItem['StockItem']['name']=$reclassificationDateString."_".$productionruncode."_".$rawMaterialName." ".$product_name." ".$code;
              $finishedItem['StockItem']['stockitem_creation_date']=$productionRunDate;
              $finishedItem['StockItem']['product_id']=$finishedProductId;
              // no unit price is set yet until the time of purchase
              $finishedItem['StockItem']['product_unit_price']=$finishedUnitPrice;
              $finishedItem['StockItem']['original_quantity']=$quantityProduced;
              $finishedItem['StockItem']['remaining_quantity']=$quantityProduced;
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
              
              $finishedProductionMovement=[];
              $finishedProductionMovement['ProductionMovement']['name']=$productionruncode."_".$code;
              $finishedProductionMovement['ProductionMovement']['description']=$message;
              $finishedProductionMovement['ProductionMovement']['movement_date']=$movementdate;
              $finishedProductionMovement['ProductionMovement']['bool_input']='0';
              $finishedProductionMovement['ProductionMovement']['stockitem_id']=$lateststockitemid;
              $finishedProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
              $finishedProductionMovement['ProductionMovement']['product_id']=$finishedProductId;
              $finishedProductionMovement['ProductionMovement']['product_quantity']=$quantityProduced;
              $finishedProductionMovement['ProductionMovement']['production_result_code_id']=$resultCodes[$c]['ProductionResultCode']['id'];
              $finishedProductionMovement['ProductionMovement']['product_unit_price']=$finishedUnitPrice;
              
              $this->ProductionMovement->create();
              if (!$this->ProductionMovement->save($finishedProductionMovement['ProductionMovement'])) {
                echo "problema guardando el movimiento de producción";
                pr($this->validateErrors($this->ProductionMovement));
                throw new Exception();
              }
              
              $productionMovementId=$this->ProductionMovement->id;
              $StockItemLog=[];
              $StockItemLog['StockItemLog']['production_movement_id']=$productionMovementId;
              $StockItemLog['StockItemLog']['stockitem_id']=$lateststockitemid;
              $StockItemLog['StockItemLog']['stockitem_date']=$reclassificationDateTimeString;
              $StockItemLog['StockItemLog']['product_id']=$finishedProductId;
              $StockItemLog['StockItemLog']['product_quantity']=$quantityProduced;
              $StockItemLog['StockItemLog']['product_unit_price']=$finishedUnitPrice;
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
            foreach ($usedBags as $usedBag){
              $this->recreateStockItemLogs($usedBag['id']);
            }
            if (!empty($consumableStockItemsArray)){
              foreach ($consumableStockItemsArray as $consumableStockItemId){
                $this->recreateStockItemLogs($consumableStockItemId);
              }
            }
            $this->Session->setFlash(__('The production run has been saved.'),'default',['class' => 'success']);
            return $this->redirect(array('action' => 'view',$productionRunId));
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
		$machines = $this->ProductionRun->Machine->find('list',['order'=>'Machine.name ASC']);
		$operators = $this->ProductionRun->Operator->find('list',array('conditions'=>array('bool_active'=>true)));
		$shifts = $this->ProductionRun->Shift->find('list');
		
		$this->set(compact('requestData','quantityRawMaterialInStock','quantityFinishedCInStock','quantityFinishedBInStock','rawMaterials','finishedProducts','machines','operators','shifts','remainingFinishedBInStockForProduct'));
    
    $this->loadModel('Constant');
    $manipulationMaxValueConstant=$this->Constant->find('first',[
      'conditions'=>[
        'Constant.constant'=>'MAXIMO_RECLASIFICACION_PRODUCCION'
      ]
    ]);
    if (!defined('MANIPULATION_MAX_PRODUCCION')){
      define('MANIPULATION_MAX_PRODUCCION',$manipulationMaxValueConstant['Constant']['value']);
    }
	}

  public function crear() {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		
    $this->loadModel('ProductionType');
    $this->loadModel('ProductionResultCode');
    $this->loadModel('Recipe');
    
    $this->loadModel('ClosingDate');
    
    $this->loadModel('StockItem');
		$this->loadModel('ProductionMovement');
		$this->loadModel('StockItemLog');
    
    $this->loadModel('ProductionLoss');
		
    $this->loadModel('Machine');
    $this->loadModel('MachineProduct');
    
    $this->loadModel('PlantProductionType');
    $this->loadModel('PlantProductionResultCode');
    
    $this->loadModel('Warehouse');
    $this->loadModel('WarehouseProduct');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    //$this->loadModel('Warehouse');
    //$this->loadModel('UserWarehouse');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $plantId=0;
		
    $requestProducts=[];
    $productionRunDateAsString=$productionRunDate=date('Y-m-d');
    
    if ($this->request->is('post')) {
			$plantId=$this->request->data['ProductionRun']['plant_id'];
      
      $productionRunDate=$this->request->data['ProductionRun']['production_run_date'];
			$productionRunDateAsString=$this->ProductionRun->deconstruct('production_run_date',$this->request->data['ProductionRun']['production_run_date']);
		}
		
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
    //echo 'plantId is '.$plantId.'<br/>';
    $plantWarehouseIds=$this->Warehouse->getWarehouseIdsForPlantId($plantId);
    //pr($plantWarehouseIds);
    
    //20210412 wrong warehouse caused discharging of lotes Colinas in OR Sandino
    //$warehouseId=empty($plantWarehouseIds)?0:$plantWarehouseIds[0];
    if ($plantId == PLANT_COLINAS){
      $warehouseId=WAREHOUSE_INJECTION;
    }
    elseif ($plantId == PLANT_SANDINO){
      $warehouseId=WAREHOUSE_DEFAULT;
    }
    else {
      $warehouseId=0;
    }
    //echo 'warehouseId is '.$warehouseId.'<br/>';
    
    $productionTypes=$this->ProductionType->getProductionTypesForPlant($plantId);
    $this->set(compact('productionTypes'));
    
    $productionTypeId=0;
    if (count($productionTypes) == 1){
      $productionTypeId=array_keys($productionTypes)[0];
    }
    elseif ($this->request->is('post')) {
      $productionTypeId=$this->request->data['ProductionRun']['production_type_id'];
    }
    $this->set(compact('productionTypeId'));
    
    $finishedProducts = $this->Recipe->Product->getProductsByProductionType($productionTypeId,PRODUCT_NATURE_PRODUCED);
    $this->set(compact('finishedProducts'));
    //pr($finishedProducts);
    
    $productBags=$this->Product->getBagIdsForProducts(array_keys($finishedProducts));
    $this->set(compact('productBags'));
    
    $productPackagingUnits=$this->Product->getPackagingUnitsForProducts(array_keys($finishedProducts));
    $this->set(compact('productPackagingUnits'));
    
    $productPreferredRawMaterials=$this->Product->getPreferredRawMaterialsForProducts(array_keys($finishedProducts));
    $this->set(compact('productPreferredRawMaterials'));
    
    $recipes=$this->Recipe->getRecipeListForProducts(array_keys($finishedProducts));
    $this->set(compact('recipes'));
    //pr($recipes);
    
    $productMachines=$this->MachineProduct->getMachineIdsForProductIds(array_keys($finishedProducts));
    $this->set(compact('productMachines'));
    //pr($productMachines);
    
    $productRecipes=$this->Recipe->getProductRecipeList(array_keys($finishedProducts));
    $this->set(compact('productRecipes'));
    //pr($productRecipes);
    
		$productionResultCodes = $this->PlantProductionResultCode->getProductionResultCodesForPlant($plantId);
		$this->set(compact('productionResultCodes'));
    
		$rawProductTypeIds=[];
    switch ($productionTypeId){
      case PRODUCTION_TYPE_PET:
        $rawProductTypeIds=[PRODUCT_TYPE_PREFORMA];
        break;
      case PRODUCTION_TYPE_INJECTION:
        $rawProductTypeIds=[PRODUCT_TYPE_INJECTION_GRAIN,PRODUCT_TYPE_INJECTION_OUTPUT];
        break;
      case PRODUCTION_TYPE_FILLING:
        break;
      default:
        $rawProductTypeIds=$this->ProductType->find('list',[
          'fields'=>['ProductType.id'],
          'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW],
        ]);
    }
    //pr($rawProductTypeIds);
    $inventoryDate=date("Y-m-d", strtotime($productionRunDateAsString));
    $rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate(CATEGORY_RAW,$rawProductTypeIds,date("Y-m-d", strtotime($productionRunDateAsString)));
    //pr($rawMaterialsInventory);
    $rawMaterialStockItems = $this->StockItem->getStockItemsByProduct($rawProductTypeIds,$inventoryDate,$warehouseId);
    //pr($rawMaterialStockItems);
    $this->set(compact('rawMaterialStockItems'));
    
    $consumableMaterialTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>[
        'ProductType.product_category_id'=>CATEGORY_CONSUMIBLE,
        'ProductType.id !='=>PRODUCT_TYPE_BAGS,
      ],
    ]);
    //pr($consumableMaterialTypeIds);
    $consumableStockItems = $this->StockItem->getStockItemsByProduct($consumableMaterialTypeIds,$inventoryDate,$warehouseId);
    //pr($consumableStockItems);
    $this->set(compact('consumableStockItems'));
    
    $bagTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>[
        'ProductType.product_category_id'=>CATEGORY_CONSUMIBLE,
      ],
    ]);
    
    $requestConsumables=[];
		if ($this->request->is('post') && empty($this->request->data['refresh'])) {
      //pr($this->request->data);
      foreach ($this->request->data['Consumables'] as $consumable){
				if ($consumable['consumable_id']>0 && $consumable['consumable_quantity']>0){
					$requestConsumables['Consumables'][]=$consumable;
				}
			}
      
      $productionRunDateAsString = $this->ProductionRun->deconstruct('production_run_date', $this->data['ProductionRun']['production_run_date']);
      $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
      //pr($rawMaterialsInventory);
			
      $productionRunCode=$this->request->data['ProductionRun']['production_run_code'];
      $namedProductionRuns=$this->ProductionRun->find('first',['conditions'=>['production_run_code'=>$productionRunCode]]);
      
      $finishedProductId=$this->request->data['ProductionRun']['finished_product_id'];
      $finishedProduct=$this->Product->getProductById($finishedProductId);
      $bagId=empty($this->request->data['ProductionRun']['bag_product_id'])?$finishedProduct['Product']['bag_product_id']:$this->request->data['ProductionRun']['bag_product_id'];
      $this->request->data['ProductionRun']['bag_product_id']=$bagId;
      $productionTypeId=$this->request->data['ProductionRun']['production_type_id'];
      
      $productionResultCodeOutputQuantities;
      if ($productionTypeId == PRODUCTION_TYPE_PET){
        $productionResultCodeOutputQuantities=[
          PRODUCTION_RESULT_CODE_A=>$this->request->data['StockItems'][PRODUCTION_RESULT_CODE_A],
          PRODUCTION_RESULT_CODE_B=>$this->request->data['StockItems'][PRODUCTION_RESULT_CODE_B],
          PRODUCTION_RESULT_CODE_C=>$this->request->data['StockItems'][PRODUCTION_RESULT_CODE_C],
        ];
      }
      
      $boolAcceptableProduction=true;
      $acceptableProductionValue=$this->Product->getAcceptableProductionValue($finishedProductId,$productionRunDateAsString);
      $finishedProductQuantity=0;
      $finishedProductQuantityForBags=0;
      switch ($productionTypeId){
        case PRODUCTION_TYPE_PET:
          $boolAcceptableProduction=$this->ProductionRun->checkProduction($acceptableProductionValue,$this->request->data['StockItems'][PRODUCTION_RESULT_CODE_A],$productionRunDateAsString,$this->request->data['ProductionRun']['shift_id']);		  
          $finishedProductQuantity=$this->request->data['ProductionRun']['raw_material_quantity'];
          $finishedProductQuantityForBags=$productionResultCodeOutputQuantities[PRODUCTION_RESULT_CODE_A]+$productionResultCodeOutputQuantities[PRODUCTION_RESULT_CODE_B];
          break;
        case PRODUCTION_TYPE_INJECTION:
          $boolAcceptableProduction=$this->ProductionRun->checkProduction($acceptableProductionValue,$this->request->data['ProductionRun']['finished_product_quantity'],$productionRunDateAsString,$this->request->data['ProductionRun']['shift_id']);		  
          $finishedProductQuantity=$this->request->data['ProductionRun']['finished_product_quantity'];
          $finishedProductQuantityForBags=$finishedProductQuantity;
          break;
        default:  
      }
      
      if (count($namedProductionRuns)>0){
        $this->Session->setFlash(__('Ya existe un proceso de producción con el mismo código!  No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif ($productionRunDateAsString>date('Y-m-d H:i')){
        $this->Session->setFlash(__('La fecha del proceso de producción no puede estar en el futuro!  No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif ($productionRunDateAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['ProductionRun']['bool_annulled']){
        $datasource=$this->ProductionRun->getDataSource();
        $datasource->begin();
        try {
          $this->request->data['ProductionRun']['raw_material_id']=null;
          $this->request->data['ProductionRun']['raw_material_quantity']=0;
          $this->request->data['ProductionRun']['finished_product_id']=null;
          $this->request->data['ProductionRun']['finished_product_quantity']=0;
          $this->request->data['ProductionRun']['machine_id']=null;
          $this->request->data['ProductionRun']['operator_id']=null;
          $this->request->data['ProductionRun']['shift_id']=null;
          $this->request->data['ProductionRun']['bag_product_id']=0;
          
          $this->ProductionRun->create();
          if (!$this->ProductionRun->save($this->request->data)) {
            echo "problema al guardar el proceso de producción";
            pr($this->validateErrors($this->Order));
            throw new Exception();
          }
          
          $productionRunId=$this->ProductionRun->id;
          
          $datasource->commit();
          $this->recordUserAction($this->ProductionRun->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se guardó el proceso de producción de forma anulada con número ".$this->request->data['ProductionRun']['production_run_code']);
          
          $this->Session->setFlash(__('Se guardó el proceso de producción (anulada).'),'default',['class' => 'success']);
          return $this->redirect(['action' => 'detalle',$productionRunId]);
        }						 
        catch(Exception $e){
          $datasource->rollback();
          //pr($e);					
          $this->Session->setFlash(__('No se podía guardar el proceso de producción.'), 'default',['class' => 'error-message']);
        }
      }
      elseif (empty($this->request->data['ProductionRun']['raw_material_id']) && 
        $this->request->data['ProductionRun']['production_type_id'] == PRODUCTION_TYPE_PET
      ){
        $this->Session->setFlash(__('Se debe seleccionar la materia prima.No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
      } 
      elseif (empty($this->request->data['ProductionRun']['finished_product_id'])){
        $this->Session->setFlash(__('Se debe seleccionar el producto fabricado!  No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['ProductionRun']['machine_id'])){
        $this->Session->setFlash(__('Se debe seleccionar la máquina!  No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['ProductionRun']['operator_id'])){
        $this->Session->setFlash(__('Se debe seleccionar el operador!  No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['ProductionRun']['shift_id'])){
        $this->Session->setFlash(__('Se debe seleccionar el turno!  No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif ($finishedProductQuantity <= 0){
        $this->Session->setFlash('La cantidad producida debe ser mayor que cero!  No se guardó el proceso de producción.', 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['ProductionRun']['incidence_id'] && !$boolAcceptableProduction) {
        $this->Session->setFlash('La cantidad para el proceso de producción '.$finishedProductQuantity.' no es aceptable porque es menos que '.$acceptableProductionValue.'.  Por favor registrar la incidencia correspondiente.', 'default',['class' => 'error-message']);
      }
      elseif ($finishedProduct['Product']['packaging_unit'] > 0 && abs($this->request->data['ProductionRun']['bag_quantity'] -  ($finishedProductQuantityForBags/$finishedProduct['Product']['packaging_unit'])) > 2){
        $this->Session->setFlash('La cantidad de bolsas indicados es '.$this->request->data['ProductionRun']['bag_quantity'].' pero según la unidad de empaque '.$finishedProduct['Product']['packaging_unit'].' debería haber '.round($finishedProductQuantityForBags/$finishedProduct['Product']['packaging_unit'],1).' bolsas.', 'default',['class' => 'error-message']);
      }
      else {
        // before proceeding with the production run, check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
        //echo "checkpoint before checking materials present<br/>";
        $productionItemsOK=true;
        $exceedingItems="";
        
        $injectionMillRawMaterialsNeeded=[];
        
        switch ($productionTypeId){
          case PRODUCTION_TYPE_PET:
            $quantityPlanned=$this->request->data['ProductionRun']['raw_material_quantity'];
            $rawMaterialId=$this->request->data['ProductionRun']['raw_material_id'];	
            $rawMaterialName=$this->Product->getProductName($rawMaterialId);
            $quantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($rawMaterialId,$productionRunDateAsString,$warehouseId,true);
            
            if ($quantityPlanned>$quantityPresent){
              $productionItemsOK='0';
              $exceedingItems.=__("Para producto ".$rawMaterialName." la cantidad requerida (".$quantityPlanned.") excede la cantidad en bodega (".$quantityPresent.")!")."<br/>";						
            }                
            break;
          case PRODUCTION_TYPE_INJECTION:
            $injectionMillRawMaterialsNeeded=$this->ProductionRun->getInjectionRawMaterialsNeededForMill($this->request->data['RecipeItem'],$this->request->data['ProductionRun']['mill_conversion_product_quantity']);
          
            foreach($this->request->data['RecipeItem'] as $recipeItem){
              //pr($recipeItem);
              $ingredientQuantityPlanned=$recipeItem['quantity'];
              $ingredientProductId=$recipeItem['product_id'];

              $ingredientName=$this->Product->getProductName($ingredientProductId);
              $ingredientQuantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($ingredientProductId,$productionRunDateAsString,$warehouseId,true);
              
              if ($this->request->data['ProductionRun']['mill_conversion_product_quantity'] > 0){
                $ingredientQuantityPlanned+=$injectionMillRawMaterialsNeeded[$ingredientProductId];
              }
            
              if ($ingredientQuantityPlanned > $ingredientQuantityPresent){
                $productionItemsOK='0';
                $exceedingItems.=__("Para producto ".$ingredientName." la cantidad requerida (".$ingredientQuantityPlanned.") excede la cantidad en bodega (".$ingredientQuantityPresent.")!")."<br/>";						
              }                                  
            }
            break;
          default:
            $quantityPlanned=0;	
        }
        if ($exceedingItems!=""){
          $exceedingItems.=__("Please correct and try again!");
        }						
        
        $bagsOK=true;
        $exceedingBags="";
        $bagQuantityPlanned=$this->request->data['ProductionRun']['bag_quantity'];	
        //echo 'warehouse id is '.$warehouseId.'<br/>';
        $bagQuantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($bagId,$productionRunDateAsString,$warehouseId,true);
        
        $bagQuantity=min($bagQuantityPlanned,$bagQuantityPresent);
        $usedBags= $this->StockItem->getRawMaterialsForProductionRun($bagId,$bagQuantity,$productionRunDateAsString,$warehouseId);
        
        // 20211218 BAGS DO NOT PREVENT FROM SAVING
        // if ($bagQuantityPlanned>$bagQuantityPresent){
          // $bagsOK='0';
          // $exceedingBags.=__("Para las bolsas la cantidad requerida (".$bagQuantityPlanned.") excede la cantidad en bodega (".$bagQuantityPresent.")!")."<br/>";						
        // }
        // if ($exceedingBags!=""){
          // $exceedingBags.=__("Please correct and try again!");
        // }	
        
        $consumableProductionItemsOK=true;
        $exceedingConsumableItems="";
        
        $consumablesArray=[];
        $consumableStockItemsArray=[];

        if (array_key_exists('RecipeConsumable',$this->request->data) && !empty($this->request->data['RecipeConsumable'])){
          foreach ($this->request->data['RecipeConsumable'] as $recipeConsumable){
            if ($recipeConsumable['product_id']>0 && $recipeConsumable['quantity']>0){
              //first build the consumablesArray to ensure no double lines are present, ie with the same product_id
              if (array_key_exists($recipeConsumable['product_id'],$consumablesArray)){
                $consumablesArray[$recipeConsumable['product_id']]+=$recipeConsumable['quantity'];
              }
              else {
                $consumablesArray[$recipeConsumable['product_id']]=$recipeConsumable['quantity'];
              }
            }
          }
        }
        
        foreach ($this->request->data['Consumables'] as $consumable){
          if ($consumable['consumable_id']>0 && $consumable['consumable_quantity']>0){
            //first build the consumablesArray to ensure no double lines are present, ie with the same consumable_id
            if (array_key_exists($consumable['consumable_id'],$consumablesArray)){
              $consumablesArray[$consumable['consumable_id']]+=$consumable['consumable_quantity'];
            }
            else {
              $consumablesArray[$consumable['consumable_id']]=$consumable['consumable_quantity'];
            }
          }
        }
        //pr($consumablesArray);
        if (!empty($consumablesArray)){
          foreach ($consumablesArray as $consumableMaterialId=>$consumableQuantityPlanned){
            $linkedConsumableMaterial=$this->Product->getProductById($consumableMaterialId);
            $consumableMaterialName=$linkedConsumableMaterial['Product']['name'];
            $consumableQuantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($consumableMaterialId,$productionRunDateAsString,$warehouseId,true);
            
            if ($consumableQuantityPlanned > $consumableQuantityPresent){
              $consumablesArray[$consumableMaterialId]=$consumableQuantityPresent;
              $consumableProductionItemsOK='0';
              $exceedingConsumableItems.=__("Para producto ".$consumableMaterialName." la cantidad requerida (".$consumableQuantityPlanned.") excedió la cantidad en bodega (".$consumableQuantityPresent.")!  Por tal razón, se ha reducido la cantidad utilizado a .".$consumableQuantityPresent.".")."<br/>";
            }
          }
        }
        
        $rawMaterialQuantity=0;
        // GET usedRawMaterials
        $usedRawMaterialsForMill=[];
        switch ($productionTypeId){
          case PRODUCTION_TYPE_PET:
            $rawMaterialId=$this->request->data['ProductionRun']['raw_material_id'];
            $rawMaterialQuantity=$this->request->data['ProductionRun']['raw_material_quantity'];
            $usedRawMaterials= $this->StockItem->getRawMaterialsForProductionRun($rawMaterialId,$rawMaterialQuantity,$productionRunDateAsString,$warehouseId);		  
            break;
          case PRODUCTION_TYPE_INJECTION:
            $usedRawMaterials=[];
            foreach ($this->request->data['RecipeItem'] as $recipeItem){
              $ingredientsStock=[];  
              $recipeProductId=$recipeItem['product_id'];
              $recipeProductQuantity=$recipeItem['quantity'];
              $millProductQuantity=empty($injectionMillRawMaterialsNeeded)?0:$injectionMillRawMaterialsNeeded[$recipeProductId];
              
              $ingredientsStock= $this->StockItem->getRawMaterialsForInjectionProductionRun($recipeProductId,$recipeProductQuantity,$millProductQuantity,$productionRunDateAsString,$warehouseId);
              // echo 'for product id '.$recipeProductId.'<br/>';
              // pr($ingredientsStock);
              if (!empty($ingredientsStock)){
                foreach ($ingredientsStock as $ingredientStock){
                  if (array_key_exists('final_product',$ingredientStock)){
                    $usedRawMaterials[]=$ingredientStock['final_product'];
                  }
                  if (!empty($injectionMillRawMaterialsNeeded) && array_key_exists('mill',$ingredientStock)){
                    $usedRawMaterialsForMill[]=$ingredientStock['mill'];  
                  }
                }
              }
            }         
            break;
          default:  
        }
        // pr($usedRawMaterials);
        // pr($usedRawMaterialsForMill);
            
        if (!$bagsOK){
          $this->Session->setFlash(__('La cantidad de bolsas no está suficiente para los siguientes productos:')."<br/>".$exceedingBags, 'default',['class' => 'error-message']);
        }
        elseif (!$productionItemsOK){
          $_SESSION['productionRunRequestData']=$this->request->data;
          
          $aco_name="ProductionRuns/manipularProduccion";		
          $bool_productionrun_manipularproduccion_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
          
          if ($bool_productionrun_manipularproduccion_permission && $productionTypeId == PRODUCTION_TYPE_PET){
            //echo "checkpoint before manipulating production run<br/>";
            return $this->redirect(['action' => 'manipularProduccion']);
          }
          //$this->manipularProduccion();
          $this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
        }
        // 20190417 If there are not enough other consumables, just lower quantity and include in  the commit flash message
        //elseif (!$consumableProductionItemsOK){
        //  $this->Session->setFlash(__('La cantidad de suministros no está suficiente para los siguientes productos:')."<br/>".$exceedingConsumableItems, 'default',['class' => 'error-message']);
        //}
        elseif (empty($usedRawMaterials)){
          $this->Session->setFlash('problema al buscar los materiales usados', 'default',['class' => 'error-message']);  
        }
        else {              
          //echo "checkpoint before saving production run<br/>";
          $datasource=$this->ProductionRun->getDataSource();
          $datasource->begin();
          try {
            $this->ProductionRun->create();
            if ($productionTypeId == PRODUCTION_TYPE_PET){
              $this->request->data['ProductionRun']['finished_product_quantity']=$this->request->data['ProductionRun']['raw_material_quantity'];
            }
            if (!$this->ProductionRun->save($this->request->data)) {
              echo "problema al guardar el proceso de producción";
              pr($this->validateErrors($this->Order));
              throw new Exception();
            }
            $productionRunId=$this->ProductionRun->id;
              
            // step 1: insert production movements for the raw materials and update the corresponding stock items
            
            $rawUnitPrice=0;
            $finishedUnitPrice=0;
            $totalRawCost=0;
            $totalRawCostMill=0;
            
            $i=0;
            //pr($usedRawMaterials);
            foreach ($usedRawMaterials as $usedRawMaterial){
              //pr ($usedRawMaterial);
              $stockItemId= $usedRawMaterial['id'];
              $rawName= $usedRawMaterial['name'];
              
              $quantityPresent=$usedRawMaterial['quantity_present'];
              $quantityUsed= $usedRawMaterial['quantity_used'];
              $rawUnitPrice=$usedRawMaterial['unit_price'];
              $totalRawCost+=$rawUnitPrice*$quantityUsed;
              $quantityRemaining=$usedRawMaterial['quantity_remaining'];
              
              $message = "Used quantity ".$quantityUsed." of raw product ".$rawName." in product run ".$productionRunCode." (prior to run: ".$quantityPresent."|remaining: ".$quantityRemaining.")";
              
              $stockItem=$this->StockItem->getStockItemById($stockItemId);
              $stockItemData=[
                'StockItem'=>[
                  'id'=>$stockItemId,
                  'original_quantity'=>$stockItem['StockItem']['original_quantity'],
                  'remaining_quantity'=>$quantityRemaining,
                ],
              ];      
              //pr($stockItemData);
              $this->StockItem->id=$stockItemId;
              if (!$this->StockItem->save($stockItemData)) {
                echo "problema guardando el lote de preformas";
                pr($this->validateErrors($this->StockItem));
                throw new Exception();
              }
              $stockItemProductId=$stockItem['StockItem']['product_id'];
              $rawProductionMovement=[
                'ProductionMovement'=>[
                  'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$rawName,
                  'description'=>$message,
                  'movement_date'=>$productionRunDate,
                  'bool_input'=>true,
                  'stockitem_id'=>$stockItemId,
                  'production_run_id'=>$productionRunId,
                  'product_id'=>$stockItemProductId,
                  'unit_id'=>($productionTypeId == PRODUCTION_TYPE_INJECTION?UNIT_GRAM:UNIT_UNIT),
                  'product_quantity'=>$quantityUsed,
                  'product_unit_price'=>$rawUnitPrice,
                ],
              ];
              $this->ProductionMovement->create();
              if (!$this->ProductionMovement->save($rawProductionMovement['ProductionMovement'])) {
                echo "problema guardando el movimiento de producción";
                pr($this->validateErrors($this->ProductionMovement));
                throw new Exception();
              }
              
              $this->recordUserActivity($this->Session->read('User.username'),$message);
              $i++;
            }
            
            if (!empty($usedRawMaterialsForMill)){
              foreach ($usedRawMaterialsForMill as $usedRawMaterial){
                //pr ($usedRawMaterial);
                $stockItemId= $usedRawMaterial['id'];
                $rawName= $usedRawMaterial['name'];
                
                $quantityPresent=$usedRawMaterial['quantity_present'];
                $quantityUsed= $usedRawMaterial['quantity_used'];
                $rawUnitPrice=$usedRawMaterial['unit_price'];
                $totalRawCostMill+=$rawUnitPrice*$quantityUsed;
                $quantityRemaining=$usedRawMaterial['quantity_remaining'];
                
                $message = "Used quantity ".$quantityUsed." of raw product ".$rawName." in product run ".$productionRunCode." for mill (prior to run: ".$quantityPresent."|remaining: ".$quantityRemaining.")";
                
                $stockItem=$this->StockItem->getStockItemById($stockItemId);
                //pr($stockItem);
                $stockItemData=[
                  'StockItem'=>[
                    'id'=>$stockItemId,
                    'original_quantity'=>$stockItem['StockItem']['original_quantity'],
                    'remaining_quantity'=>$quantityRemaining,
                  ],
                ];      
                //pr($stockItemData);
                $this->StockItem->id=$stockItemId;
                if (!$this->StockItem->save($stockItemData)) {
                  echo "problema guardando el lote de preformas";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                $stockItemProductId=$stockItem['StockItem']['product_id'];
                $rawProductionMovementMill=[
                  'ProductionMovement'=>[
                    'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$rawName,
                    'description'=>$message,
                    'movement_date'=>$productionRunDate,
                    'bool_input'=>true,
                    'stockitem_id'=>$stockItemId,
                    'production_run_id'=>$productionRunId,
                    'product_id'=>$stockItemProductId,
                    'unit_id'=>($productionTypeId == PRODUCTION_TYPE_INJECTION?UNIT_GRAM:UNIT_UNIT),
                    'product_quantity'=>$quantityUsed,
                    'product_unit_price'=>$rawUnitPrice,
                    'bool_recycling'=>true,
                  ],
                ];
                $this->ProductionMovement->create();
                if (!$this->ProductionMovement->save($rawProductionMovementMill['ProductionMovement'])) {
                  echo "problema guardando el movimiento de producción para molina";
                  pr($this->validateErrors($this->ProductionMovement));
                  throw new Exception();
                }
                
                $this->recordUserActivity($this->Session->read('User.username'),$message);
                $i++;
              }
              
            }
            
            if (!empty($usedBags)){
              foreach ($usedBags as $usedBag){
                //pr ($usedBag);
                $stockItemId= $usedBag['id'];
                $bagName= $usedBag['name'];
                
                $quantityPresent=$usedBag['quantity_present'];
                $quantityUsed= $usedBag['quantity_used'];
                $bagUnitPrice=$usedBag['unit_price'];
                $quantityRemaining=$usedBag['quantity_remaining'];
                
                $message = "Consumo de cantidad ".$quantityUsed." de producto consumable ".$bagName." en orden de producción ".$productionRunCode." (antes de orden: ".$quantityPresent."|sobrante: ".$quantityRemaining.")";
                
                $StockItemData=[
                  'StockItem'=>[
                    'id'=>$stockItemId,
                    'remaining_quantity'=>$quantityRemaining,
                  ],
                ];
                if (!$this->StockItem->save($StockItemData)) {
                  echo "problema guardando el lote de bolsas";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                
                $bagProductionMovement=[];
                $bagProductionMovement['ProductionMovement']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$bagName;
                $bagProductionMovement['ProductionMovement']['description']=$message;
                $bagProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
                $bagProductionMovement['ProductionMovement']['bool_input']=true;
                $bagProductionMovement['ProductionMovement']['stockitem_id']=$stockItemId;
                $bagProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
                //$bagProductionMovement['ProductionMovement']['product_id']=PRODUCT_BAG;
                $bagProductionMovement['ProductionMovement']['product_id']=$bagId;
                $bagProductionMovement['ProductionMovement']['unit_id']=UNIT_UNIT;
                $bagProductionMovement['ProductionMovement']['product_quantity']=$quantityUsed;
                $bagProductionMovement['ProductionMovement']['product_unit_price']=$bagUnitPrice;
                
                $this->ProductionMovement->create();
                if (!$this->ProductionMovement->save($bagProductionMovement['ProductionMovement'])) {
                  echo "problema guardando el movimiento de producción para bolsas";
                  pr($this->validateErrors($this->ProductionMovement));
                  throw new Exception();
                }
                
                $this->recordUserActivity($this->Session->read('User.username'),$message);
                $i++;
              }
            }
            
            $totalModifiedConsumableCost=0;
            if (!empty($consumablesArray)){
              foreach ($consumablesArray as $consumableMaterialId=>$consumableMaterialQuantity){
                $usedConsumableMaterials= $this->StockItem->getRawMaterialsForProductionRun($consumableMaterialId,$consumableMaterialQuantity,$productionRunDateAsString,$warehouseId);
                
                if (!empty($usedConsumableMaterials)){
                  foreach ($usedConsumableMaterials as $usedConsumableMaterial){
                    $consumableStockItemsArray[]=$usedConsumableMaterial['id'];
                    //pr ($usedConsumableMaterial);
                    $stockItemId= $usedConsumableMaterial['id'];
                    $consumableName= $usedConsumableMaterial['name'];
                    
                    $quantityPresent=$usedConsumableMaterial['quantity_present'];
                    $quantityUsed= $usedConsumableMaterial['quantity_used'];
                    $consumableUnitPrice=$usedConsumableMaterial['unit_price'];
                    
                    $totalModifiedConsumableCost+=$consumableUnitPrice*$quantityUsed;
                    $quantityRemaining=$usedConsumableMaterial['quantity_remaining'];
                    
                    $message = "Consumo de cantidad ".$quantityUsed." de producto consumible ".$consumableName." en orden de producción ".$productionRunCode." (antes de orden: ".$quantityPresent."|sobrante: ".$quantityRemaining.")";
                    
                    $stockItemData=[
                      'StockItem'=>[
                        'id'=>$stockItemId,
                        'remaining_quantity'=>$quantityRemaining,
                      ],
                    ];
                    $this->StockItem->id=$stockItemId;
                    if (!$this->StockItem->save($stockItemData)) {
                      echo "problema guardando el lote de consumables";
                      pr($stockItemData);
                      pr($this->validateErrors($this->StockItem));
                      throw new Exception();
                    }
                    
                    $consumableProductionMovement=[];
                    $consumableProductionMovement['ProductionMovement']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$consumableName;
                    $consumableProductionMovement['ProductionMovement']['description']=$message;
                    $consumableProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
                    $consumableProductionMovement['ProductionMovement']['bool_input']=true;
                    $consumableProductionMovement['ProductionMovement']['stockitem_id']=$stockItemId;
                    $consumableProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
                    $consumableProductionMovement['ProductionMovement']['product_id']=$consumableMaterialId;
                    $consumableProductionMovement['ProductionMovement']['unit_id']=UNIT_UNIT;
                    $consumableProductionMovement['ProductionMovement']['product_quantity']=$quantityUsed;
                    $consumableProductionMovement['ProductionMovement']['product_unit_price']=$consumableUnitPrice;
                    
                    $this->ProductionMovement->create();
                    if (!$this->ProductionMovement->save($consumableProductionMovement['ProductionMovement'])) {
                      echo "problema guardando el movimiento de producción para suministro adicional";
                      pr($this->validateErrors($this->ProductionMovement));
                      throw new Exception();
                    }
                    
                    $this->recordUserActivity($this->Session->read('User.username'),$message);
                    $i++;
                  }
                }
              }
            }
            //echo "checkpoint before saving produced materials<br/>";
            
            // step 2: create new stock items for the produced products
            $productName=$this->Product->getProductName($finishedProductId);
            switch ($productionTypeId){
              case PRODUCTION_TYPE_PET:
                $finishedUnitPrice=$totalRawCost/$rawMaterialQuantity;
                  
                foreach ($productionResultCodes as $productionResultCodeId=>$resultCode){
                  $quantityProduced=$this->request->data['StockItems'][$productionResultCodeId];
                  $finishedProductId=$this->request->data['ProductionRun']['finished_product_id'];
                  $movementDate=$productionRunDate;
                  $message = "Se fabricaron ".$quantityProduced." de producto ".$finishedProductId." calidad ".$resultCode." en proceso producción ".$productionRunCode;
                  $finishedItemData=[
                    'StockItem'=>[
                      'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$rawMaterialName." ".$productName." ".$resultCode,
                      'stockitem_creation_date'=>$productionRunDate,
                      'product_id'=>$finishedProductId,
                      'unit_id'=>UNIT_UNIT,
                      // no unit price is set yet until the time of purchase
                      'product_unit_price'=>$finishedUnitPrice,
                      'original_quantity'=>$quantityProduced,
                      'remaining_quantity'=>$quantityProduced,
                      'production_result_code_id'=>$productionResultCodeId,
                      'raw_material_id'=>$rawMaterialId,
                    ],
                  ];    
                  //pr($finishedItem);
                  $this->StockItem->create();
                  if (!$this->StockItem->save($finishedItemData)) {
                    echo "problema guardando el lote de producto terminado";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  $latestStockItemId=$this->StockItem->id;
                  //echo "latest stock item id".$latestStockItemId."<br/>";
                  
                  $finishedProductionMovement=[
                    'ProductionMovement'=>[
                      'name'=>$productionRunCode."_".$resultCode,
                      'description'=>$message,
                      'movement_date'=>$movementDate,
                      'bool_input'=>'0',
                      'stockitem_id'=>$latestStockItemId,
                      'production_run_id'=>$productionRunId,
                      'product_id'=>$finishedProductId,
                      'unit_id'=>UNIT_UNIT,
                      'product_quantity'=>$quantityProduced,
                      'production_result_code_id'=>$productionResultCodeId,
                      'product_unit_price'=>$finishedUnitPrice,
                    ],
                  ];
                  //echo "checkpoint before saving produced material production movement<br/>";	
                  $this->ProductionMovement->create();
                  if (!$this->ProductionMovement->save($finishedProductionMovement['ProductionMovement'])) {
                    echo "problema guardando el movimiento de producción";
                    pr($this->validateErrors($this->ProductionMovement));
                    throw new Exception();
                  }
                  
                  $productionMovementId=$this->ProductionMovement->id;
                  $StockItemLog=[
                    'StockItemLog'=>[
                      'production_movement_id'=>$productionMovementId,
                      'stockitem_id'=>$latestStockItemId,
                      'stockitem_date'=>$productionRunDate,
                      'product_id'=>$finishedProductId,
                      'unit_id'=>UNIT_UNIT,
                      'product_quantity'=>$quantityProduced,
                      'product_unit_price'=>$finishedUnitPrice,
                      'production_result_code_id'=>$productionResultCodeId,
                    ],
                  ];                           
                  $this->StockItemLog->create();
                  if (!$this->StockItemLog->save($StockItemLog['StockItemLog'])) {
                    echo "problema guardando el estado de lote";
                    pr($this->validateErrors($this->StockItemLog));
                    throw new Exception();
                  }
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                }
                break;
              case PRODUCTION_TYPE_INJECTION:
                $movementDate=$productionRunDate;
                
                // keep finished product  
                $finishedProductId=$this->request->data['ProductionRun']['finished_product_id'];
                $finishedProductQuantity=$this->request->data['ProductionRun']['finished_product_quantity'];
                
                $millConversionProductId=$this->request->data['ProductionRun']['mill_conversion_product_id'];
                $millConversionProductQuantity=$this->request->data['ProductionRun']['mill_conversion_product_quantity'];
                
                $wasteQuantity=$this->request->data['ProductionRun']['waste_quantity'];
                
                //echo 'total raw cost is '.$totalRawCost.' and mill total cost is '.$this->request->data['UnitCost']['mill_total_cost'].'<br/>';
                //echo 'finished product quantity is '.$finishedProductQuantity.'<br/>';
                $finishedUnitPrice=($totalRawCost+$totalModifiedConsumableCost)/$finishedProductQuantity;
                //echo 'finished unit price is '.$finishedUnitPrice.'<br/>';
                // 20211218 AS WE CANNOT ENSURE ALL CONSUMABLES ARE THERE, WE ALSO CANNOT ENSURE THE FINAL PRICE OF THE CONSUMABLES
                //if (abs($finishedUnitPrice - $this->request->data['UnitCost']['unit_cost']) > 0.001){
                //  $this->Session->setFlash('error al calcular precio unitario, según página el precio unitario es '.$this->request->data['UnitCost']['unit_cost'].' pero el valor que se quiere guardar es '.$finishedUnitPrice, 'default',['class' => 'error-message']);  
                //  throw new Exception();
                //}
                $millGramPrice=($millConversionProductQuantity > 0?($totalRawCostMill/$millConversionProductQuantity):0);  
                
                $message = "Se fabricaron ".$finishedProductQuantity." de producto ".$productName." calidad ".PRODUCTION_RESULT_CODE_A." en proceso producción ".$productionRunCode;
                $finishedItemData=[
                  'StockItem'=>[
                    'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$productName."_".PRODUCTION_RESULT_CODE_A,
                    'stockitem_creation_date'=>$productionRunDate,
                    'product_id'=>$finishedProductId,
                    'unit_id'=>UNIT_UNIT,
                    'product_unit_price'=>$finishedUnitPrice,
                    'original_quantity'=>$finishedProductQuantity,
                    'remaining_quantity'=>$finishedProductQuantity,
                    'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
                    'raw_material_id'=>null,
                    'warehouse_id'=>$warehouseId,
                    //'raw_material_id'=>$rawMaterialId,
                  ],
                ];    
                //pr($finishedItem);
                $this->StockItem->create();
                if (!$this->StockItem->save($finishedItemData)) {
                  echo "problema guardando el lote de producto terminado";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                $latestStockItemId=$this->StockItem->id;
                  
                $finishedProductionMovement=[
                  'ProductionMovement'=>[
                    'name'=>$productionRunCode."_".PRODUCTION_RESULT_CODE_A,
                    'description'=>$message,
                    'movement_date'=>$movementDate,
                    'bool_input'=>'0',
                    'stockitem_id'=>$latestStockItemId,
                    'production_run_id'=>$productionRunId,
                    'product_id'=>$finishedProductId,
                    'unit_id'=>UNIT_UNIT,
                    'product_quantity'=>$finishedProductQuantity,
                    'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
                    'product_unit_price'=>$finishedUnitPrice,
                  ],
                ];
                $this->ProductionMovement->create();
                if (!$this->ProductionMovement->save($finishedProductionMovement['ProductionMovement'])) {
                  echo "problema guardando el movimiento de producción";
                  pr($this->validateErrors($this->ProductionMovement));
                  throw new Exception();
                }
                $productionMovementId=$this->ProductionMovement->id;
                $stockItemLog=[
                  'StockItemLog'=>[
                    'production_movement_id'=>$productionMovementId,
                    'stockitem_id'=>$latestStockItemId,
                    'stockitem_date'=>$productionRunDate,
                    'product_id'=>$finishedProductId,
                    'unit_id'=>UNIT_UNIT,
                    'product_quantity'=>$finishedProductQuantity,
                    'product_unit_price'=>$finishedUnitPrice,
                    'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
                    'warehouse_id'=>$warehouseId,
                  ],
                ];
                $this->StockItemLog->create();
                if (!$this->StockItemLog->save($stockItemLog['StockItemLog'])) {
                  echo "problema guardando el estado de lote";
                  pr($this->validateErrors($this->StockItemLog));
                  throw new Exception();
                }
                $this->recordUserActivity($this->Session->read('User.username'),$message);  
                if ($millConversionProductQuantity > 0){
                  // keep mill conversion product  
                  $millConversionProductName=$this->Product->getProductName($millConversionProductId);
                  $message = "Se fabricaron ".$millConversionProductQuantity." de producto ".$millConversionProductName." calidad ".PRODUCTION_RESULT_CODE_MILL." en proceso producción ".$productionRunCode;
                  $millConversionProductData=[
                    'StockItem'=>[
                      'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$productName."_".PRODUCTION_RESULT_CODE_MILL,
                      'stockitem_creation_date'=>$productionRunDate,
                      'product_id'=>$millConversionProductId,
                      'unit_id'=>UNIT_GRAM,
                      'product_unit_price'=>$millGramPrice,
                      'original_quantity'=>$millConversionProductQuantity,
                      'remaining_quantity'=>$millConversionProductQuantity,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_MILL,
                      'raw_material_id'=>null,
                      'warehouse_id'=>$warehouseId,
                      //'raw_material_id'=>$rawMaterialId,
                    ],
                  ];    
                  //pr($finishedItem);
                  $this->StockItem->create();
                  if (!$this->StockItem->save($millConversionProductData)) {
                    echo "problema guardando el lote de producto molino";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  $millStockItemId=$this->StockItem->id;
                  
                  $millConversionProductionMovement=[
                    'ProductionMovement'=>[
                      'name'=>$productionRunCode."_".PRODUCTION_RESULT_CODE_MILL,
                      'description'=>$message,
                      'movement_date'=>$movementDate,
                      'bool_input'=>'0',
                      'stockitem_id'=>$millStockItemId,
                      'production_run_id'=>$productionRunId,
                      'product_id'=>$millConversionProductId,
                      'unit_id'=>UNIT_GRAM,
                      'product_quantity'=>$millConversionProductQuantity,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_MILL,
                      'product_unit_price'=>$millGramPrice,
                      'bool_recycling'=>true,
                    ],
                  ];
                  $this->ProductionMovement->create();
                  if (!$this->ProductionMovement->save($millConversionProductionMovement['ProductionMovement'])) {
                    echo "problema guardando el movimiento de molino";
                    pr($this->validateErrors($this->ProductionMovement));
                    throw new Exception();
                  }
                  $productionMovementId=$this->ProductionMovement->id;
                  $stockItemLog=[
                    'StockItemLog'=>[
                      'production_movement_id'=>$productionMovementId,
                      'stockitem_id'=>$millStockItemId,
                      'stockitem_date'=>$productionRunDate,
                      'product_id'=>$millConversionProductId,
                      'unit_id'=>UNIT_GRAM,
                      'product_quantity'=>$millConversionProductQuantity,
                      'product_unit_price'=>$millGramPrice,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_MILL,
                      'warehouse_id'=>$warehouseId,
                    ],
                  ];
                  $this->StockItemLog->create();
                  if (!$this->StockItemLog->save($stockItemLog['StockItemLog'])) {
                    echo "problema guardando el estado de lote de molino";
                    pr($this->validateErrors($this->StockItemLog));
                    throw new Exception();
                  }
                  $this->recordUserActivity($this->Session->read('User.username'),$message);  
                }
                // keep the production loss
                $message = "Se perdieron ".$wasteQuantity." gramos de merma calidad ".PRODUCTION_RESULT_CODE_WASTE." en proceso producción ".$productionRunCode;
                $productionLossArray=[
                  'ProductionLoss'=>[
                    'name'=>$productionRunCode."_".PRODUCTION_RESULT_CODE_WASTE,
                    'description'=>$message,
                    'movement_date'=>$movementDate,
                    'production_run_id'=>$productionRunId,
                    'product_quantity'=>$wasteQuantity,
                    'unit_id'=>UNIT_GRAM,
                    'product_unit_price'=>$millGramPrice,
                    'production_result_code_id'=>PRODUCTION_RESULT_CODE_WASTE,
                  ],
                ];
                $this->ProductionLoss->create();
                if (!$this->ProductionLoss->save($productionLossArray['ProductionLoss'])) {
                  echo "problema guardando el movimiento de merma";
                  pr($this->validateErrors($this->ProductionLoss));
                  throw new Exception();
                }
                
                /*
                foreach ($productionResultCodes as $productionResultCodeId=>$resultCode){
                  $quantityProduced=$this->request->data['StockItems'][$productionResultCodeId];
                  $finishedProductId=$this->request->data['ProductionRun']['finished_product_id'];
                  $movementDate=$productionRunDate;
                  $message = "Se fabricaron ".$quantityProduced." de producto ".$productName." calidad ".$resultCode." en proceso producción ".$productionRunCode;
                  
                  $finishedItemData=[
                    'StockItem'=>[
                      'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$productName."_".$resultCode,
                      'stockitem_creation_date'=>$productionRunDate,
                      'product_id'=>$finishedProductId,
                      'product_unit_price'=>$finishedUnitPrice,
                      'original_quantity'=>$quantityProduced,
                      'remaining_quantity'=>$quantityProduced,
                      'production_result_code_id'=>$productionResultCodeId,
                      'raw_material_id'=>null,
                      //'raw_material_id'=>$rawMaterialId,
                    ],
                  ];    
                  //pr($finishedItem);
                  $this->StockItem->create();
                  if (!$this->StockItem->save($finishedItemData)) {
                    echo "problema guardando el lote de producto terminado";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  $latestStockItemId=$this->StockItem->id;
                  //echo "latest stock item id".$latestStockItemId."<br/>";
                  
                  $finishedProductionMovement=[
                    'ProductionMovement'=>[
                      'name'=>$productionRunCode."_".$resultCode,
                      'description'=>$message,
                      'movement_date'=>$movementDate,
                      'bool_input'=>'0',
                      'stockitem_id'=>$latestStockItemId,
                      'production_run_id'=>$productionRunId,
                      'product_id'=>$finishedProductId,
                      'product_quantity'=>$quantityProduced,
                      'production_result_code_id'=>$productionResultCodeId,
                      'product_unit_price'=>$finishedUnitPrice,
                    ],
                  ];
                  
                  //echo "checkpoint before saving produced material production movement<br/>";	
                  $this->ProductionMovement->create();
                  if (!$this->ProductionMovement->save($finishedProductionMovement['ProductionMovement'])) {
                    echo "problema guardando el movimiento de producción";
                    pr($this->validateErrors($this->ProductionMovement));
                    throw new Exception();
                  }
                  
                  $productionMovementId=$this->ProductionMovement->id;
                  $stockItemLog=[
                    'StockItemLog'=>[
                      'production_movement_id'=>$productionMovementId,
                      'stockitem_id'=>$latestStockItemId,
                      'stockitem_date'=>$productionRunDate,
                      'product_id'=>$finishedProductId,
                      'product_quantity'=>$quantityProduced,
                      'product_unit_price'=>$finishedUnitPrice,
                      'production_result_code_id'=>$productionResultCodeId,
                    ],
                  ];
                  $this->StockItemLog->create();
                  if (!$this->StockItemLog->save($stockItemLog['StockItemLog'])) {
                    echo "problema guardando el estado de lote";
                    pr($this->validateErrors($this->StockItemLog));
                    throw new Exception();
                  }
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                }
                */
                break;
              default:  
            }
            
            $datasource->commit();
            $this->recordUserAction($this->ProductionRun->id,null,null);
            $this->recordUserActivity($this->Session->read('User.username'),"Se ejecutó proceso de producción con numero ".$this->request->data['ProductionRun']['production_run_code']);
            
            //echo "checkpoint before creating raw material stock item log<br/>";	
            foreach ($usedRawMaterials as $usedRawMaterial){
              $this->recreateStockItemLogs($usedRawMaterial['id']);
            }
            foreach ($usedBags as $usedBag){
              $this->recreateStockItemLogs($usedBag['id']);
            }
            if (!empty($consumableStockItemsArray)){
              foreach ($consumableStockItemsArray as $consumableStockItemId){
                $this->recreateStockItemLogs($consumableStockItemId);
              }
            }
            $this->Session->setFlash(__('Se guardó el proceso de producción.'),'default',['class' => 'success']);
            return $this->redirect(['action' => 'detalle',$productionRunId]);
          }
          catch(Exception $e){
            $datasource->rollback();
            //echo substr(print_r($e,true),0,500);
            //var_dump($e);
            pr($e);					
            $this->Session->setFlash(__('No se podía guardar el proceso de producción.'), 'default',['class' => 'error-message']);
          }
        }
      }
		}
    
    //pr($requestConsumables);
    $this->set(compact('requestConsumables'));
		
		$this->Product->recursive=-1;
    $rawMaterialConditions=[
      'Product.product_type_id'=>$rawProductTypeIds,
      'Product.bool_active'=>true, 
      'Product.production_type_id'=>$productionTypeId,
    ];
		$rawMaterialsAll=$this->Product->find('all', [
			'fields'=>'Product.id,Product.name',
			'conditions' => $rawMaterialConditions,
			'order'=>'Product.name',
		]);
		$rawMaterials=null;
		foreach ($rawMaterialsAll as $rawMaterial){
			$rawMaterials[$rawMaterial['Product']['id']]=$rawMaterial['Product']['name'];
		}

    //pr($consumableMaterialTypeIds);
    $plantWarehouseIds=$this->Warehouse->getWarehouseIdsForPlantId($plantId);
    //pr($plantWarehouseIds);
    $warehouseProductIds=[];
    foreach ($plantWarehouseIds as $plantWarehouseId){
      $warehouseProductIds=array_merge($warehouseProductIds,$this->WarehouseProduct->getProductIdsForWarehouse($plantWarehouseId));
    }
    //pr($warehouseProductIds);
    $consumables = $this->Product->find('list', [
			'fields'=>'Product.id,Product.name',
			'conditions' => [
        'Product.product_type_id'=> $consumableMaterialTypeIds,
        'Product.bool_active'=>true,
        'Product.id'=> $warehouseProductIds,
        //'Product.id !='=>PRODUCT_BAG, // 20190529 bag cannot be determined beforehand
      ],
			'order'=>'Product.name',
		]);
    $bags = $this->Product->find('list', [
			'fields'=>'Product.id,Product.name',
			'conditions' => [
        'Product.product_type_id'=> PRODUCT_TYPE_BAGS,
        'Product.bool_active'=>true,
        'Product.id'=> $warehouseProductIds,
      ],
			'order'=>'Product.name',
		]);
		$this->set(compact('consumables','bags'));
    
    if ($plantId > 0){
      $machines = $this->ProductionRun->Machine->getMachineListForPlant($plantId);
      $operators = $this->ProductionRun->Operator->getOperatorListForPlant($plantId);
    }
		$shifts = $this->ProductionRun->Shift->find('list');
	
		// calculate the name for the next production run
		$newProductionRunCode=($plantId == 0?"":$this->ProductionRun->getProductionRunCode($plantId,$this->Plant->getPlantShortName($plantId)));
    
		$this->set(compact('rawMaterials','machines', 'operators', 'shifts','rawMaterialsInventory','newProductionRunCode'));
		
    $incidences=$this->ProductionRun->Incidence->find('list',[
			'order'=>'Incidence.list_order, Incidence.name',
		]);
		$this->set(compact('incidences'));
		
		$aco_name="Operators/reporteProduccionTotal";		
		$bool_operator_totalproductionreport_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_totalproductionreport_permission'));
    
    $injectionRawMaterials = $this->Product->getProductsByProductionType(PRODUCTION_TYPE_INJECTION,PRODUCT_NATURE_RAW);
		$this->set(compact('injectionRawMaterials'));
    //pr($rawMaterials);
    
    $rawMaterialUnits=$this->Product->getProductUnitList(array_keys($injectionRawMaterials));
    $this->set(compact('rawMaterialUnits'));
    
    $recipeMillConversionProducts=$this->Recipe->getMillConversionProductIdsForRecipes();
    $this->set(compact('recipeMillConversionProducts'));
		
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

	public function editar($id = null) {
		if (!$this->ProductionRun->exists($id)) {
			throw new NotFoundException(__('Invalid production run'));
		}
		
    $this->loadModel('Product');
		$this->loadModel('ProductType');
		
    $this->loadModel('ProductionType');
    $this->loadModel('ProductionResultCode');
    $this->loadModel('Recipe');
    
    $this->loadModel('ClosingDate');
    
    $this->loadModel('StockItem');
		$this->loadModel('ProductionMovement');
		$this->loadModel('StockItemLog');
    
    $this->loadModel('ProductionLoss');
		
    $this->loadModel('Machine');
    $this->loadModel('MachineProduct');
    
    $this->loadModel('PlantProductionType');
    $this->loadModel('PlantProductionResultCode');
    
    $this->loadModel('Warehouse');
    $this->loadModel('WarehouseProduct');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    //$this->loadModel('Warehouse');
    //$this->loadModel('UserWarehouse');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $plantId=$this->ProductionRun->getPlantId($id);;
		
    $requestProducts=[];
    
    if ($this->request->is(['post', 'put'])) {
			$plantId=$this->request->data['ProductionRun']['plant_id'];
      
      $productionRunDate=$this->request->data['ProductionRun']['production_run_date'];
			$productionRunDateAsString=$this->ProductionRun->deconstruct('production_run_date',$this->request->data['ProductionRun']['production_run_date']);
		}
    else {
      $productionRunDateAsString=$productionRunDate=$this->ProductionRun->getProductionRunDate($id);
    }
		
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
    //echo 'plantId is '.$plantId.'<br/>';
    $plantWarehouseIds=$this->Warehouse->getWarehouseIdsForPlantId($plantId);
    //pr($plantWarehouseIds);
    
    //20210412 wrong warehouse caused discharging of lotes Colinas in OR Sandino
    //$warehouseId=empty($plantWarehouseIds)?0:$plantWarehouseIds[0];
    if ($plantId == PLANT_COLINAS){
      $warehouseId=WAREHOUSE_INJECTION;
    }
    elseif ($plantId == PLANT_SANDINO){
      $warehouseId=WAREHOUSE_DEFAULT;
    }
    else {
      $warehouseId=0;
    }
    
    $productionTypes=$this->ProductionType->getProductionTypesForPlant($plantId);
    $this->set(compact('productionTypes'));
    
    $productionTypeId=0;
    if (count($productionTypes) == 1){
      $productionTypeId=array_keys($productionTypes)[0];
    }
    elseif ($this->request->is(['post', 'put'])) {
      $productionTypeId=$this->request->data['ProductionRun']['production_type_id'];
    }
    $this->set(compact('productionTypeId'));
    
    $finishedProducts = $this->Recipe->Product->getProductsByProductionType($productionTypeId,PRODUCT_NATURE_PRODUCED);
    $this->set(compact('finishedProducts'));
    //pr($finishedProducts);
    
    $productBags=$this->Product->getBagIdsForProducts(array_keys($finishedProducts));
    $this->set(compact('productBags'));
    
    $productPackagingUnits=$this->Product->getPackagingUnitsForProducts(array_keys($finishedProducts));
    $this->set(compact('productPackagingUnits'));
    
    $productPreferredRawMaterials=$this->Product->getPreferredRawMaterialsForProducts(array_keys($finishedProducts));
    $this->set(compact('productPreferredRawMaterials'));
    
    $recipes=$this->Recipe->getRecipeListForProducts(array_keys($finishedProducts));
    $this->set(compact('recipes'));
    //pr($recipes);
    
    $productMachines=$this->MachineProduct->getMachineIdsForProductIds(array_keys($finishedProducts));
    $this->set(compact('productMachines'));
    //pr($productMachines);
    
    $productRecipes=$this->Recipe->getProductRecipeList(array_keys($finishedProducts));
    $this->set(compact('productRecipes'));
    //pr($productRecipes);
    
		$productionResultCodes = $this->PlantProductionResultCode->getProductionResultCodesForPlant($plantId);
		$this->set(compact('productionResultCodes'));
		
    $rawProductTypeIds=[];
    switch ($productionTypeId){
      case PRODUCTION_TYPE_PET:
        $rawProductTypeIds=[PRODUCT_TYPE_PREFORMA];
        break;
      case PRODUCTION_TYPE_INJECTION:
        $rawProductTypeIds=[PRODUCT_TYPE_INJECTION_GRAIN];
        break;
      case PRODUCTION_TYPE_FILLING:
        break;
      default:
        $rawProductTypeIds=$this->ProductType->find('list',[
          'fields'=>['ProductType.id'],
          'conditions'=>['ProductType.product_category_id'=>CATEGORY_RAW],
        ]);
    }
    //pr($rawProductTypeIds);
    $inventoryDate=date("Y-m-d", strtotime($productionRunDateAsString));
    $rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate(CATEGORY_RAW,$rawProductTypeIds,date("Y-m-d", strtotime($productionRunDateAsString)));
    //pr($rawMaterialsInventory);
    $rawMaterialStockItems = $this->StockItem->getStockItemsByProduct($rawProductTypeIds,$inventoryDate,$warehouseId);
    
    $rawMaterialList=$this->Product->getActiveProductsByTypes($rawProductTypeIds);
    
    $consumableMaterialTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>[
        'ProductType.product_category_id'=>CATEGORY_CONSUMIBLE,
      ],
    ]);
    //pr($consumableMaterialTypeIds);
    $consumableStockItems = $this->StockItem->getStockItemsByProduct($consumableMaterialTypeIds,$inventoryDate,$warehouseId);
    //pr($consumableStockItems);
    $this->set(compact('consumableStockItems'));
    
    $bagTypeIds=$this->ProductType->find('list',[
      'fields'=>['ProductType.id'],
      'conditions'=>[
        'ProductType.product_category_id'=>CATEGORY_CONSUMIBLE,
      ],
    ]);
    
    $recipeId=$this->ProductionRun->getRecipeId($id);
    $recipeConsumableList=$this->Recipe->getRecipeConsumableList($recipeId);
    
    $requestConsumables=[];
    $productionResultCodeOutputQuantities=[];
    $requestIngredients=[];
    $requestRecipeConsumables=[];
    $millConversionProductId=0;
		if ($this->request->is(['post', 'put']) && empty($this->request->data['refresh'])) {
      if (!empty($this->request->data['Consumables'])){
        foreach ($this->request->data['Consumables'] as $consumable){
          if ($consumable['consumable_id']>0 && $consumable['consumable_quantity']>0){
            $requestConsumables['Consumables'][]=$consumable;
          }
        }  
      }
      if ($productionTypeId == PRODUCTION_TYPE_INJECTION){
        if (!empty($this->request->data['RecipeItem'])){
          foreach ($this->request->data['RecipeItem'] as $recipeItem){
            $raw=[];
            $raw['product_id']= $recipeItem['product_id'];
            $raw['quantity_for_one']= $recipeItem['quantity_for_one'];
            $raw['product_quantity']= $recipeItem['quantity'];
            $raw['unit_id']= $recipeItem['unit_id'];
            $requestIngredients['Products'][]=$raw;
          }
        }
        if (!empty($this->request->data['RecipeConsumable'])){
          foreach ($this->request->data['RecipeConsumable'] as $requestRecipeConsumable){
            $recipeConsumable=[];
            $recipeConsumable['product_id']= $requestRecipeConsumable['product_id'];
            $recipeConsumable['quantity_for_one']= $requestRecipeConsumable['quantity_for_one'];
            $recipeConsumable['product_quantity']= $requestRecipeConsumable['quantity'];
            $recipeConsumable['unit_id']= (empty($requestRecipeConsumable['unit_id'])?UNIT_UNIT:$requestRecipeConsumable['unit_id']);
            $requestRecipeConsumables['Products'][]=$recipeConsumable;
          }
        }
        $productionResultCodeOutputQuantities=[
          PRODUCTION_RESULT_CODE_A=>$this->request->data['ProductionRun']['finished_product_quantity'],
          PRODUCTION_RESULT_CODE_MILL=>$this->request->data['ProductionRun']['mill_conversion_product_quantity'],
          PRODUCTION_RESULT_CODE_WASTE=>$this->request->data['ProductionRun']['waste_quantity'],
        ];
      }
      if ($productionTypeId == PRODUCTION_TYPE_PET){
        $productionResultCodeOutputQuantities=[
          PRODUCTION_RESULT_CODE_A=>$this->request->data['StockItems'][PRODUCTION_RESULT_CODE_A],
          PRODUCTION_RESULT_CODE_B=>$this->request->data['StockItems'][PRODUCTION_RESULT_CODE_B],
          PRODUCTION_RESULT_CODE_C=>$this->request->data['StockItems'][PRODUCTION_RESULT_CODE_C],
        ];
      }
      $productionRunDate=$this->request->data['ProductionRun']['production_run_date'];
			//pr($productionRunDate);
			$productionRunDateAsString=$this->ProductionRun->deconstruct('production_run_date',$this->request->data['ProductionRun']['production_run_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
			
      $productionRunCode=$this->request->data['ProductionRun']['production_run_code'];
      $namedProductionRuns=$this->ProductionRun->find('first',[
        'conditions'=>[
          'ProductionRun.production_run_code'=>$productionRunCode,
          'ProductionRun.id !='=>$id,
        ]]
      );
      
      $finishedProductId=$this->request->data['ProductionRun']['finished_product_id'];
      $finishedProduct=$this->Product->getProductById($finishedProductId);
      $bagId=empty($this->request->data['ProductionRun']['bag_product_id'])?$finishedProduct['Product']['bag_product_id']:$this->request->data['ProductionRun']['bag_product_id'];
      $this->request->data['ProductionRun']['bag_product_id']=$bagId;
      $productionTypeId=$this->request->data['ProductionRun']['production_type_id'];
      
      $boolAcceptableProduction=true;
      $acceptableProductionValue=$this->Product->getAcceptableProductionValue($finishedProductId,$productionRunDateAsString);
      $finishedProductQuantity=0;
      $finishedProductQuantityForBags=0;
      switch ($productionTypeId){
        case PRODUCTION_TYPE_PET:
          $boolAcceptableProduction=$this->ProductionRun->checkProduction($acceptableProductionValue,$this->request->data['StockItems'][PRODUCTION_RESULT_CODE_A],$productionRunDateAsString,$this->request->data['ProductionRun']['shift_id']);		  
          $finishedProductQuantity=$this->request->data['ProductionRun']['raw_material_quantity'];
          $finishedProductQuantityForBags=$productionResultCodeOutputQuantities[PRODUCTION_RESULT_CODE_A]+$productionResultCodeOutputQuantities[PRODUCTION_RESULT_CODE_B];
          break;
        case PRODUCTION_TYPE_INJECTION:
          $boolAcceptableProduction=$this->ProductionRun->checkProduction($acceptableProductionValue,$this->request->data['ProductionRun']['finished_product_quantity'],$productionRunDateAsString,$this->request->data['ProductionRun']['shift_id']);		  
          $finishedProductQuantity=$this->request->data['ProductionRun']['finished_product_quantity'];
          $finishedProductQuantityForBags=$finishedProductQuantity;
          break;
        default:  
      }
      
      $originalProductionRun = $this->ProductionRun->find('first',[
        'conditions' => [
          'ProductionRun.id'=> $id,
        ],
        'contain'=>[
          'ProductionMovement'=>[
            'Product',
            'StockItem'=>[
              'StockItemLog',
            ],
          ],
          'ProductionLoss',
          'RawMaterial'=>[
            'ProductType',
          ],
        ],
      ]);
      
      if (count($namedProductionRuns)>0){
        $this->Session->setFlash(__('Ya existe un proceso de producción con el mismo código!  No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif ($productionRunDateAsString>date('Y-m-d H:i')){
          $this->Session->setFlash(__('La fecha del proceso de producción no puede estar en el futuro!  No se guardó el proceso de producción.'), 'default',['class' => 'error-message']);
        }
      elseif ($productionRunDateAsString<$latestClosingDatePlusOne){
        $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',['class' => 'error-message']);
      }
      elseif ($this->request->data['ProductionRun']['bool_annulled']){          
        $datasource=$this->ProductionRun->getDataSource();
        $datasource->begin();
        try {								
          $this->ProductionRun->id=$id;
          foreach ($originalProductionRun['ProductionMovement'] as $productionMovement){
            if ($productionMovement['bool_input']){
              $stockItem=[
                'StockItem'=>[
                  'id'=>$productionMovement['StockItem']['id'],
                  'remaining_quantity'=>$productionMovement['StockItem']['remaining_quantity']+$productionMovement['product_quantity'],
                  'description'=>$productionMovement['StockItem']['description']."|anulada orden de producción production run ".$originalProductionRun['ProductionRun']['production_run_code'],
                ],
              ];
              if (!$this->StockItem->save($stockItem)) {
                echo "problema actualizando el estado de lote";
                pr($this->validateErrors($this->StockItem));
                throw new Exception();
              }
              $this->recreateStockItemLogs($productionMovement['stockitem_id']);										
            }
            else {
              foreach ($productionMovement['StockItem']['StockItemLog'] as $stockItemLog){
                $this->StockItemLog->id=$stockItemLog['id'];
                if (!$this->StockItemLog->delete()) {
                  echo "problema eliminando el estado de lote";
                  pr($this->validateErrors($this->StockItemLog));
                  throw new Exception();
                }  
              }
              $this->StockItem->id=$productionMovement['StockItem']['id'];
              if (!$this->StockItem->delete()) {
                echo "problema eliminando el lote";
                pr($this->validateErrors($this->StockItem));
                throw new Exception();
              }	
            }
            $this->ProductionRun->ProductionMovement->id=$productionMovement['id'];
            if (!$this->ProductionRun->ProductionMovement->delete($productionMovement['id'])) {
              echo "problema eliminando el movimiento de lote";
              pr($this->validateErrors($this->ProductionRun->ProductionMovement));
              throw new Exception();
            }
          }
          foreach ($originalProductionRun['ProductionLoss'] as $productionLoss){
            $this->ProductionRun->ProductionLoss->id=$productionLoss['id'];
            if (!$this->ProductionRun->ProductionLoss->delete($productionLoss['id'])) {
              echo "problema eliminando el movimiento de lote";
              pr($this->validateErrors($this->ProductionRun->ProductionLoss));
              throw new Exception();
            }
          }
          $this->request->data['ProductionRun']['raw_material_id']=null;
          $this->request->data['ProductionRun']['raw_material_quantity']=0;
          $this->request->data['ProductionRun']['finished_product_id']=null;
          $this->request->data['ProductionRun']['finished_product_quantity']=0;
          $this->request->data['ProductionRun']['machine_id']=null;
          $this->request->data['ProductionRun']['operator_id']=null;
          $this->request->data['ProductionRun']['shift_id']=null;
          $this->request->data['ProductionRun']['bag_product_id']=0;
          
          if (!$this->ProductionRun->save($this->request->data)) {
            echo "problema al guardar el proceso de producción como anulada";
            pr($this->validateErrors($this->ProductionRun));
            throw new Exception();
          }
          
          $productionRunId=$id;
          
          $datasource->commit();
          
          $this->recordUserAction($this->ProductionRun->id,null,null);
          $this->recordUserActivity($this->Session->read('User.username'),"Se editó orden de produccion de forma anulada con numero ".$this->request->data['ProductionRun']['production_run_code']);
          
          $this->Session->setFlash(__('Se anuló el proceso de producción (por edición).'),'default',['class' => 'success']);
          return $this->redirect(array('action' => 'detalle',$productionRunId));
        }						 
        catch(Exception $e){
          $datasource->rollback();
          pr($e);					
          $this->Session->setFlash(__('No se podía guardar el proceso de producción como anulada.'), 'default',['class' => 'error-message']);
        }
      }
      elseif (empty($this->request->data['ProductionRun']['raw_material_id']) && 
        $this->request->data['ProductionRun']['production_type_id'] == PRODUCTION_TYPE_PET
      ){
        $this->Session->setFlash(__('Se debe seleccionar la materia prima.'), 'default',['class' => 'error-message']);
      } 
      elseif (empty($this->request->data['ProductionRun']['finished_product_id'])){
        $this->Session->setFlash(__('Se debe seleccionar el producto fabricado!  No se editó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['ProductionRun']['machine_id'])){
        $this->Session->setFlash(__('Se debe seleccionar la máquina!  No se editó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['ProductionRun']['operator_id'])){
        $this->Session->setFlash(__('Se debe seleccionar el operador!  No se editó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['ProductionRun']['shift_id'])){
        $this->Session->setFlash(__('Se debe seleccionar el turno!  No se editó el proceso de producción.'), 'default',['class' => 'error-message']);
      }
      elseif ($finishedProductQuantity <= 0){
        $this->Session->setFlash('La cantidad producida debe ser mayor que cero!  No se editó el proceso de producción.', 'default',['class' => 'error-message']);
      }
      elseif (!$this->request->data['ProductionRun']['incidence_id'] && !$boolAcceptableProduction) {
        $this->Session->setFlash('La cantidad para el proceso de producción '.$finishedProductQuantity.' no es aceptable porque es menos que '.$acceptableProductionValue.'.  Por favor registrar la incidencia correspondiente.', 'default',['class' => 'error-message']);
      }    
      elseif ($finishedProduct['Product']['packaging_unit'] > 0 && abs($this->request->data['ProductionRun']['bag_quantity'] -  ($finishedProductQuantityForBags/$finishedProduct['Product']['packaging_unit'])) > 2){
        $this->Session->setFlash('La cantidad de bolsas indicados es '.$this->request->data['ProductionRun']['bag_quantity'].' pero según la unidad de empaque '.$finishedProduct['Product']['packaging_unit'].' debería haber '.round($finishedProductQuantityForBags/$finishedProduct['Product']['packaging_unit'],1).' bolsas.', 'default',['class' => 'error-message']);
      }
      else {
        $originalFinishedProductId=$originalProductionRun['ProductionRun']['finished_product_id'] ;
        $originalBagId=$originalProductionRun['ProductionRun']['bag_product_id'] ;
        // original consumables are summed back about 100 lines further down in if (!empty($consumablesArray)){ ...
        //$originalConsumables=[];
        $originalBagQuantity=0;
        
        foreach ($originalProductionRun['ProductionMovement'] as $productionMovement){
          if ($productionMovement['product_id'] == $originalBagId){
            $originalBagQuantity+=$productionMovement['product_quantity'];
          }
          // original consumables are summed back about 100 lines further down in if (!empty($consumablesArray)){ ...
          // elseif ($productionMovement['bool_input']){
            // if (array_key_exists($productionMovement['product_id'],$consumableMaterialTypeIds)){
              // if (!array_key_exists($productionMovement['product_id'],$originalConsumables)){
                // $originalConsumables[$productionMovement['product_id']]=0;
              // }
              // $originalConsumables[$productionMovement['product_id']]+=$productionMovement['product_quantity'];
            // }
          // }  
        }
        
        $productionItemsOK=true;
        $exceedingItems="";
        
        switch ($productionTypeId){
          case PRODUCTION_TYPE_PET:
            $quantityPlanned=$this->request->data['ProductionRun']['raw_material_quantity'];
            $rawMaterialId=$this->request->data['ProductionRun']['raw_material_id'];	
            $rawMaterialName=$this->Product->getProductName($rawMaterialId);
            
            $quantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($rawMaterialId,$productionRunDateAsString,$warehouseId,true);
            
            if ($rawMaterialId == $originalProductionRun['ProductionRun']['raw_material_id']){
              $quantityPresent+=$originalProductionRun['ProductionRun']['raw_material_quantity'];
            }
            if ($quantityPlanned>$quantityPresent){
              $productionItemsOK='0';
              $exceedingItems.=__("Para producto ".$rawMaterialName." la cantidad requerida (".$quantityPlanned.") excede la cantidad en bodega (".$quantityPresent.")!")."<br/>";						
            }                
            break;
          case PRODUCTION_TYPE_INJECTION:
            $injectionMillRawMaterialsNeeded=$this->ProductionRun->getInjectionRawMaterialsNeededForMill($this->request->data['RecipeItem'],$this->request->data['ProductionRun']['mill_conversion_product_quantity']);
          
            foreach($this->request->data['RecipeItem'] as $recipeItem){
              $ingredientQuantityPlanned=$recipeItem['quantity'];
              $ingredientProductId=$recipeItem['product_id'];	                  
              
              $ingredientName=$this->Product->getProductName($ingredientProductId);
              $ingredientQuantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($ingredientProductId,$productionRunDateAsString,$warehouseId,true);
              
              if ($this->request->data['ProductionRun']['mill_conversion_product_quantity'] > 0){
                $ingredientQuantityPlanned+=$injectionMillRawMaterialsNeeded[$ingredientProductId];
              }
            
              foreach ($originalProductionRun['ProductionMovement'] as $productionMovement){
                if ($productionMovement['bool_input'] && $productionMovement['product_id'] == $ingredientProductId){
                  $ingredientQuantityPresent+=$productionMovement['product_quantity'];  
                }
              }
              if ($ingredientQuantityPlanned > $ingredientQuantityPresent){
                $productionItemsOK='0';
                $exceedingItems.=__("Para producto ".$ingredientName." la cantidad requerida (".$ingredientQuantityPlanned.") excede la cantidad en bodega (".$ingredientQuantityPresent.")!")."<br/>";						
              }                                  
            }
            break;
          default:
            $quantityPlanned=0;	
        }
        if ($exceedingItems!=""){
          $exceedingItems.=__("Please correct and try again!");
        }						
        
        $bagsOK=true;
        $exceedingBags="";
        $bagQuantityPlanned=$this->request->data['ProductionRun']['bag_quantity'];	
        $bagQuantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($bagId,$productionRunDateAsString,$warehouseId,true);
        if ($bagId == $originalBagId){
          $bagQuantityPresent+=$originalBagQuantity;
        }
        // 20211221 BAGS DO NOT PREVENT FROM SAVING
        // if ($bagQuantityPlanned>$bagQuantityPresent){
          // $bagsOK='0';
          // $exceedingBags.=__("Para las bolsas la cantidad requerida (".$bagQuantityPlanned.") excede la cantidad en bodega (".$bagQuantityPresent.")!")."<br/>";						
        // }
        // if ($exceedingBags!=""){
          // $exceedingBags.=__("Please correct and try again!");
        // }			
        
        $consumableProductionItemsOK=true;
        $exceedingConsumableItems="";
        
        $consumablesArray=[];
        $consumableStockItemsArray=[];
        if (array_key_exists('RecipeConsumable',$this->request->data) && !empty($this->request->data['RecipeConsumable'])){
          foreach ($this->request->data['RecipeConsumable'] as $recipeConsumable){
            if ($recipeConsumable['product_id']>0 && $recipeConsumable['quantity']>0){
              if (array_key_exists($recipeConsumable['product_id'],$consumablesArray)){
                $consumablesArray[$recipeConsumable['product_id']]+=$recipeConsumable['quantity'];
              }
              else {
                $consumablesArray[$recipeConsumable['product_id']]=$recipeConsumable['quantity'];
              }
            }
          }
        }
        foreach ($this->request->data['Consumables'] as $consumable){
          if ($consumable['consumable_id']>0 && $consumable['consumable_quantity']>0){
            if (array_key_exists($consumable['consumable_id'],$consumablesArray)){
              $consumablesArray[$consumable['consumable_id']]+=$consumable['consumable_quantity'];
            }
            else {
              $consumablesArray[$consumable['consumable_id']]=$consumable['consumable_quantity'];
            }
          }
        }
        if (!empty($consumablesArray)){
          foreach ($consumablesArray as $consumableMaterialId=>$consumableQuantityPlanned){
            $linkedConsumableMaterial=$this->Product->getProductById($consumableMaterialId);
            $consumableMaterialName=$linkedConsumableMaterial['Product']['name'];
            $consumableQuantityPresent=$this->StockItemLog->getStockQuantityAtDateForProduct($consumableMaterialId,$productionRunDateAsString,$warehouseId,true);
            
            foreach ($originalProductionRun['ProductionMovement'] as $productionMovement){
              if ($productionMovement['bool_input'] && $productionMovement['product_id'] == $consumableMaterialId){
                $consumableQuantityPresent+=$productionMovement['product_quantity'];  
              }
            }
            
            if ($consumableQuantityPlanned > $consumableQuantityPresent){
              $consumablesArray[$consumableMaterialId]=$consumableQuantityPresent;
              $consumableProductionItemsOK='0';
              $exceedingConsumableItems.=__("Para producto ".$consumableMaterialName." la cantidad requerida (".$consumableQuantityPlanned.") excedió la cantidad en bodega (".$consumableQuantityPresent.")!  Por tal razón, se ha reducido la cantidad utilizado a .".$consumableQuantityPresent.".")."<br/>";
            }
          }
        }
        
        if (!$bagsOK){
          $this->Session->setFlash(__('La cantidad de bolsas no está suficiente para los siguientes productos:')."<br/>".$exceedingBags, 'default',['class' => 'error-message']);
        }
        elseif (!$productionItemsOK){
          $_SESSION['productionRunRequestData']=$this->request->data;
          
          $aco_name="ProductionRuns/manipularProduccion";		
          $bool_productionrun_manipularproduccion_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
          
          if ($bool_productionrun_manipularproduccion_permission && $productionTypeId == PRODUCTION_TYPE_PET){
            return $this->redirect(['action' => 'manipularProduccion']);
          }
          $this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',['class' => 'error-message']);
        }
        // used materials can only be retrieved after deleting the previous movements
        //elseif (empty($usedRawMaterials)){
        //  $this->Session->setFlash('problema al buscar los materiales usados', 'default',['class' => 'error-message']);  
        //}
        else {
          $boolRemovalPreviousData=true;
          //echo 'starting to remove data<br/>';
          //pr($originalProductionRun);
          $datasource=$this->ProductionRun->getDataSource();
          $datasource->begin();
          try {								
            $this->ProductionRun->id=$id;
            if (!empty($originalProductionRun['ProductionMovement'])){
              foreach ($originalProductionRun['ProductionMovement'] as $productionMovement){
                if ($productionMovement['bool_input']){
                  $stockItem=[
                    'StockItem'=>[
                      'id'=>$productionMovement['StockItem']['id'],
                      'remaining_quantity'=>$productionMovement['StockItem']['remaining_quantity']+$productionMovement['product_quantity'],
                      'description'=>$productionMovement['StockItem']['description']."|editar producción ".$originalProductionRun['ProductionRun']['production_run_code'],
                    ],
                  ];
                  if (!$this->StockItem->save($stockItem)) {
                    echo "problema actualizando el estado de lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  $this->recreateStockItemLogs($productionMovement['stockitem_id']);										
                }
                else {
                  foreach ($productionMovement['StockItem']['StockItemLog'] as $stockItemLog){
                    $this->StockItemLog->id=$stockItemLog['id'];
                    if (!$this->StockItemLog->delete()) {
                      echo "problema eliminando el log de lote";
                      pr($this->validateErrors($this->StockItemLog));
                      throw new Exception();
                    }  
                  }
                  $this->StockItem->id=$productionMovement['StockItem']['id'];
                  if (!$this->StockItem->delete()) {
                    echo "problema eliminando el lote";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }	
                }
                $this->ProductionRun->ProductionMovement->id=$productionMovement['id'];
                if (!$this->ProductionRun->ProductionMovement->delete($productionMovement['id'])) {
                  echo "problema eliminando el movimiento de lote";
                  pr($this->validateErrors($this->ProductionRun->ProductionMovement));
                  throw new Exception();
                }
              }
            }
            if (!empty($originalProductionRun['ProductionLoss'])){
              foreach ($originalProductionRun['ProductionLoss'] as $productionLoss){
                $this->ProductionRun->ProductionLoss->id=$productionLoss['id'];
                if (!$this->ProductionRun->ProductionLoss->delete($productionLoss['id'])) {
                  echo "problema eliminando el movimiento de lote";
                  pr($this->validateErrors($this->ProductionRun->ProductionLoss));
                  throw new Exception();
                }
              }
            }  
              
            $datasource->commit();
            
            $this->recordUserAction($this->ProductionRun->id,null,null);
            $this->recordUserActivity($this->Session->read('User.username'),"Eliminado movimientos viejos (en edición) para producción número ".$this->request->data['ProductionRun']['production_run_code']);
          }						 
          catch(Exception $e){
            $datasource->rollback();
            pr($e);					
            $boolRemovalPreviousData='0';
            $this->Session->setFlash(__('No se podían eliminar los movimientos viejos'), 'default',['class' => 'error-message']);
          }
          if ($boolRemovalPreviousData){
            //echo 'starting to save data<br/>';
            
            $rawMaterialQuantity=0;
            // GET usedRawMaterials
            $usedRawMaterialsForMill=[];
            switch ($productionTypeId){
              case PRODUCTION_TYPE_PET:
                $rawMaterialId=$this->request->data['ProductionRun']['raw_material_id'];
                $rawMaterialQuantity=$this->request->data['ProductionRun']['raw_material_quantity'];
                $usedRawMaterials= $this->StockItem->getRawMaterialsForProductionRun($rawMaterialId,$rawMaterialQuantity,$productionRunDateAsString,$warehouseId);		  
                break;
              case PRODUCTION_TYPE_INJECTION:
                $usedRawMaterials=[];
                foreach ($this->request->data['RecipeItem'] as $recipeItem){
                  $ingredientsStock=[];  
                  $recipeProductId=$recipeItem['product_id'];
                  $recipeProductQuantity=$recipeItem['quantity'];
                  $millProductQuantity=empty($injectionMillRawMaterialsNeeded)?0:$injectionMillRawMaterialsNeeded[$recipeProductId];
                  
                  $rawMaterialQuantity+=$recipeItem['quantity'];
                  $ingredientsStock= $this->StockItem->getRawMaterialsForInjectionProductionRun($recipeProductId,$recipeProductQuantity,$millProductQuantity,$productionRunDateAsString,$warehouseId);
                  if (!empty($ingredientsStock)){
                    foreach ($ingredientsStock as $ingredientStock){
                      if (array_key_exists('final_product',$ingredientStock)){
                        $usedRawMaterials[]=$ingredientStock['final_product'];
                      }
                      if (!empty($injectionMillRawMaterialsNeeded) && array_key_exists('mill',$ingredientStock)){
                        $usedRawMaterialsForMill[]=$ingredientStock['mill'];  
                      }
                    }
                  }
                }  
                break;
              default:  
            }
            //pr($usedRawMaterials);    
            // pr($usedRawMaterialsForMill);
        
            $bagQuantity=$this->request->data['ProductionRun']['bag_quantity'];
            $usedBags= $this->StockItem->getRawMaterialsForProductionRun($bagId,$bagQuantity,$productionRunDateAsString,$warehouseId);
           
            $datasource=$this->ProductionRun->getDataSource();
            $datasource->begin();
            try {
              $this->ProductionRun->id=$id;
              if ($productionTypeId == PRODUCTION_TYPE_PET){
                $this->request->data['ProductionRun']['finished_product_quantity']=$this->request->data['ProductionRun']['raw_material_quantity'];
              }
              if (!$this->ProductionRun->save($this->request->data)) {
                echo "problema al guardar el proceso de producción";
                pr($this->validateErrors($this->Order));
                throw new Exception();
              }
              $productionRunId=$id;
                
              // step 1: insert production movements for the raw materials and update the corresponding stock items
              
              $rawUnitPrice=0;
              $finishedUnitPrice=0;
              $totalRawCost=0;
              $totalRawCostMill=0;
              
              $i=0;
              //pr($usedRawMaterials);
              foreach ($usedRawMaterials as $usedRawMaterial){
                //pr ($usedRawMaterial);
                $stockItemId= $usedRawMaterial['id'];
                $rawName= $usedRawMaterial['name'];
                
                $quantityPresent=$usedRawMaterial['quantity_present'];
                $quantityUsed= $usedRawMaterial['quantity_used'];
                $rawUnitPrice=$usedRawMaterial['unit_price'];
                $totalRawCost+=$rawUnitPrice*$quantityUsed;
                $quantityRemaining=$usedRawMaterial['quantity_remaining'];
                
                $message = "Used quantity ".$quantityUsed." of raw product ".$rawName." in product run ".$productionRunCode." (prior to run: ".$quantityPresent."|remaining: ".$quantityRemaining.")";
                
                $stockItem=$this->StockItem->getStockItemById($stockItemId);
                //pr($stockItem);
                $stockItemData=[
                  'StockItem'=>[
                    'id'=>$stockItemId,
                    'original_quantity'=>$stockItem['StockItem']['original_quantity'],
                    'remaining_quantity'=>$quantityRemaining,
                  ],
                ];      
                //pr($stockItemData);
                $this->StockItem->id=$stockItemId;
                if (!$this->StockItem->save($stockItemData)) {
                  echo "problema guardando el lote de preformas";
                  pr($this->validateErrors($this->StockItem));
                  throw new Exception();
                }
                $stockItemProductId=$stockItem['StockItem']['product_id'];
                $rawProductionMovement=[
                  'ProductionMovement'=>[
                    'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$rawName,
                    'description'=>$message,
                    'movement_date'=>$productionRunDate,
                    'bool_input'=>true,
                    'stockitem_id'=>$stockItemId,
                    'production_run_id'=>$productionRunId,
                    'product_id'=>$stockItemProductId,
                    'unit_id'=>($productionTypeId == PRODUCTION_TYPE_INJECTION?UNIT_GRAM:UNIT_UNIT),
                    'product_quantity'=>$quantityUsed,
                    'product_unit_price'=>$rawUnitPrice,
                  ],
                ];
                $this->ProductionMovement->create();
                if (!$this->ProductionMovement->save($rawProductionMovement['ProductionMovement'])) {
                  echo "problema guardando el movimiento de producción";
                  pr($this->validateErrors($this->ProductionMovement));
                  throw new Exception();
                }
                
                $this->recordUserActivity($this->Session->read('User.username'),$message);
                $i++;
              }
              
              if (!empty($usedRawMaterialsForMill)){
                foreach ($usedRawMaterialsForMill as $usedRawMaterial){
                  //pr ($usedRawMaterial);
                  $stockItemId= $usedRawMaterial['id'];
                  $rawName= $usedRawMaterial['name'];
                  
                  $quantityPresent=$usedRawMaterial['quantity_present'];
                  $quantityUsed= $usedRawMaterial['quantity_used'];
                  $rawUnitPrice=$usedRawMaterial['unit_price'];
                  $totalRawCostMill+=$rawUnitPrice*$quantityUsed;
                  $quantityRemaining=$usedRawMaterial['quantity_remaining'];
                  
                  $message = "Used quantity ".$quantityUsed." of raw product ".$rawName." in product run ".$productionRunCode." for mill (prior to run: ".$quantityPresent."|remaining: ".$quantityRemaining.")";
                  
                  $stockItem=$this->StockItem->getStockItemById($stockItemId);
                  //pr($stockItem);
                  $stockItemData=[
                    'StockItem'=>[
                      'id'=>$stockItemId,
                      'original_quantity'=>$stockItem['StockItem']['original_quantity'],
                      'remaining_quantity'=>$quantityRemaining,
                    ],
                  ];      
                  //pr($stockItemData);
                  $this->StockItem->id=$stockItemId;
                  if (!$this->StockItem->save($stockItemData)) {
                    echo "problema guardando el lote de preformas";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  $stockItemProductId=$stockItem['StockItem']['product_id'];
                  $rawProductionMovementMill=[
                    'ProductionMovement'=>[
                      'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$rawName,
                      'description'=>$message,
                      'movement_date'=>$productionRunDate,
                      'bool_input'=>true,
                      'stockitem_id'=>$stockItemId,
                      'production_run_id'=>$productionRunId,
                      'product_id'=>$stockItemProductId,
                      'unit_id'=>($productionTypeId == PRODUCTION_TYPE_INJECTION?UNIT_GRAM:UNIT_UNIT),
                      'product_quantity'=>$quantityUsed,
                      'product_unit_price'=>$rawUnitPrice,
                      'bool_recycling'=>true,
                    ],
                  ];
                  $this->ProductionMovement->create();
                  if (!$this->ProductionMovement->save($rawProductionMovementMill['ProductionMovement'])) {
                    echo "problema guardando el movimiento de producción para molina";
                    pr($this->validateErrors($this->ProductionMovement));
                    throw new Exception();
                  }
                  
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                  $i++;
                }
              }
            
              if (!empty($usedBags)){
                foreach ($usedBags as $usedBag){
                  //pr ($usedBag);
                  $stockItemId= $usedBag['id'];
                  $bagName= $usedBag['name'];
                  
                  $quantityPresent=$usedBag['quantity_present'];
                  $quantityUsed= $usedBag['quantity_used'];
                  $bagUnitPrice=$usedBag['unit_price'];
                  $quantityRemaining=$usedBag['quantity_remaining'];
                  
                  $message = "Consumo de cantidad ".$quantityUsed." de producto consumable ".$bagName." en orden de producción ".$productionRunCode." (antes de orden: ".$quantityPresent."|sobrante: ".$quantityRemaining.")";
                  
                  $StockItemData=[
                    'StockItem'=>[
                      'id'=>$stockItemId,
                      'remaining_quantity'=>$quantityRemaining,
                    ],
                  ];
                  if (!$this->StockItem->save($StockItemData)) {
                    echo "problema guardando el lote de bolsas";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  
                  $bagProductionMovement=[];
                  $bagProductionMovement['ProductionMovement']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$bagName;
                  $bagProductionMovement['ProductionMovement']['description']=$message;
                  $bagProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
                  $bagProductionMovement['ProductionMovement']['bool_input']=true;
                  $bagProductionMovement['ProductionMovement']['stockitem_id']=$stockItemId;
                  $bagProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
                  //$bagProductionMovement['ProductionMovement']['product_id']=PRODUCT_BAG;
                  $bagProductionMovement['ProductionMovement']['product_id']=$bagId;
                  $bagProductionMovement['ProductionMovement']['unit_id']=UNIT_UNIT;
                  $bagProductionMovement['ProductionMovement']['product_quantity']=$quantityUsed;
                  $bagProductionMovement['ProductionMovement']['product_unit_price']=$bagUnitPrice;
                  
                  $this->ProductionMovement->create();
                  if (!$this->ProductionMovement->save($bagProductionMovement['ProductionMovement'])) {
                    echo "problema guardando el movimiento de producción para bolsas";
                    pr($this->validateErrors($this->ProductionMovement));
                    throw new Exception();
                  }
                  
                  $this->recordUserActivity($this->Session->read('User.username'),$message);
                  $i++;
                }
              }
              
              $totalModifiedConsumableCost=0;
              if (!empty($consumablesArray)){
                foreach ($consumablesArray as $consumableMaterialId=>$consumableMaterialQuantity){
                  $usedConsumableMaterials= $this->StockItem->getRawMaterialsForProductionRun($consumableMaterialId,$consumableMaterialQuantity,$productionRunDateAsString,$warehouseId);
                  
                  if (!empty($usedConsumableMaterials)){
                    foreach ($usedConsumableMaterials as $usedConsumableMaterial){
                      $consumableStockItemsArray[]=$usedConsumableMaterial['id'];
                      //pr ($usedConsumableMaterial);
                      $stockItemId= $usedConsumableMaterial['id'];
                      $consumableName= $usedConsumableMaterial['name'];
                      
                      $quantityPresent=$usedConsumableMaterial['quantity_present'];
                      $quantityUsed= $usedConsumableMaterial['quantity_used'];
                      $consumableUnitPrice=$usedConsumableMaterial['unit_price'];
                      
                      $totalModifiedConsumableCost+=$consumableUnitPrice*$quantityUsed;
                      $quantityRemaining=$usedConsumableMaterial['quantity_remaining'];
                      
                      $message = "Consumo de cantidad ".$quantityUsed." de producto consumible ".$consumableName." en orden de producción ".$productionRunCode." (antes de orden: ".$quantityPresent."|sobrante: ".$quantityRemaining.")";
                      
                      $stockItemData=[
                        'StockItem'=>[
                          'id'=>$stockItemId,
                          'remaining_quantity'=>$quantityRemaining,
                        ],
                      ];
                      $this->StockItem->id=$stockItemId;
                      if (!$this->StockItem->save($stockItemData)) {
                        echo "problema guardando el lote de consumables";
                        pr($stockItemData);
                        pr($this->validateErrors($this->StockItem));
                        throw new Exception();
                      }
                      
                      $consumableProductionMovement=[];
                      $consumableProductionMovement['ProductionMovement']['name']=$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$consumableName;
                      $consumableProductionMovement['ProductionMovement']['description']=$message;
                      $consumableProductionMovement['ProductionMovement']['movement_date']=$productionRunDate;
                      $consumableProductionMovement['ProductionMovement']['bool_input']=true;
                      $consumableProductionMovement['ProductionMovement']['stockitem_id']=$stockItemId;
                      $consumableProductionMovement['ProductionMovement']['production_run_id']=$productionRunId;
                      $consumableProductionMovement['ProductionMovement']['product_id']=$consumableMaterialId;
                      $consumableProductionMovement['ProductionMovement']['unit_id']=UNIT_UNIT;
                      $consumableProductionMovement['ProductionMovement']['product_quantity']=$quantityUsed;
                      $consumableProductionMovement['ProductionMovement']['product_unit_price']=$consumableUnitPrice;
                      
                      $this->ProductionMovement->create();
                      if (!$this->ProductionMovement->save($consumableProductionMovement['ProductionMovement'])) {
                        echo "problema guardando el movimiento de producción para suministro adicional";
                        pr($this->validateErrors($this->ProductionMovement));
                        throw new Exception();
                      }
                      
                      $this->recordUserActivity($this->Session->read('User.username'),$message);
                      $i++;
                    }
                  }
                }
              }
              //echo "checkpoint before saving produced materials<br/>";
              
              // step 2: create new stock items for the produced products
              $productName=$this->Product->getProductName($finishedProductId);
              switch ($productionTypeId){
                case PRODUCTION_TYPE_PET:
                  $finishedUnitPrice=$totalRawCost/$rawMaterialQuantity;
                    
                  foreach ($productionResultCodes as $productionResultCodeId=>$resultCode){
                    $quantityProduced=$this->request->data['StockItems'][$productionResultCodeId];
                    $finishedProductId=$this->request->data['ProductionRun']['finished_product_id'];
                    $movementDate=$productionRunDate;
                    $message = "Se fabricaron ".$quantityProduced." de producto ".$finishedProductId." calidad ".$resultCode." en proceso producción ".$productionRunCode;
                    $finishedItemData=[
                      'StockItem'=>[
                        'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$rawMaterialName." ".$productName." ".$resultCode,
                        'stockitem_creation_date'=>$productionRunDate,
                        'product_id'=>$finishedProductId,
                        'unit_id'=>UNIT_UNIT,
                        // no unit price is set yet until the time of purchase
                        'product_unit_price'=>$finishedUnitPrice,
                        'original_quantity'=>$quantityProduced,
                        'remaining_quantity'=>$quantityProduced,
                        'production_result_code_id'=>$productionResultCodeId,
                        'raw_material_id'=>$rawMaterialId,
                      ],
                    ];    
                    //pr($finishedItem);
                    $this->StockItem->create();
                    if (!$this->StockItem->save($finishedItemData)) {
                      echo "problema guardando el lote de producto terminado";
                      pr($this->validateErrors($this->StockItem));
                      throw new Exception();
                    }
                    
                    $latestStockItemId=$this->StockItem->id;
                    //echo "latest stock item id".$latestStockItemId."<br/>";
                    
                    $finishedProductionMovement=[
                      'ProductionMovement'=>[
                        'name'=>$productionRunCode."_".$resultCode,
                        'description'=>$message,
                        'movement_date'=>$movementDate,
                        'bool_input'=>'0',
                        'stockitem_id'=>$latestStockItemId,
                        'production_run_id'=>$productionRunId,
                        'product_id'=>$finishedProductId,
                        'unit_id'=>UNIT_UNIT,
                        'product_quantity'=>$quantityProduced,
                        'production_result_code_id'=>$productionResultCodeId,
                        'product_unit_price'=>$finishedUnitPrice,
                      ],
                    ];
                    //echo "checkpoint before saving produced material production movement<br/>";	
                    $this->ProductionMovement->create();
                    if (!$this->ProductionMovement->save($finishedProductionMovement['ProductionMovement'])) {
                      echo "problema guardando el movimiento de producción";
                      pr($this->validateErrors($this->ProductionMovement));
                      throw new Exception();
                    }
                    
                    $productionMovementId=$this->ProductionMovement->id;
                    $StockItemLog=[
                      'StockItemLog'=>[
                        'production_movement_id'=>$productionMovementId,
                        'stockitem_id'=>$latestStockItemId,
                        'stockitem_date'=>$productionRunDate,
                        'product_id'=>$finishedProductId,
                        'unit_id'=>UNIT_UNIT,
                        'product_quantity'=>$quantityProduced,
                        'product_unit_price'=>$finishedUnitPrice,
                        'production_result_code_id'=>$productionResultCodeId,
                      ],
                    ];                           
                    $this->StockItemLog->create();
                    if (!$this->StockItemLog->save($StockItemLog['StockItemLog'])) {
                      echo "problema guardando el estado de lote";
                      pr($this->validateErrors($this->StockItemLog));
                      throw new Exception();
                    }
                    $this->recordUserActivity($this->Session->read('User.username'),$message);
                  }
                  break;
                case PRODUCTION_TYPE_INJECTION:
                  $movementDate=$productionRunDate;
                  
                  // keep finished product  
                  $finishedProductId=$this->request->data['ProductionRun']['finished_product_id'];
                  $finishedProductQuantity=$this->request->data['ProductionRun']['finished_product_quantity'];
                  
                  $millConversionProductId=$this->request->data['ProductionRun']['mill_conversion_product_id'];
                  $millConversionProductQuantity=$this->request->data['ProductionRun']['mill_conversion_product_quantity'];
                  
                  $wasteQuantity=$this->request->data['ProductionRun']['waste_quantity'];
                  
                  $finishedUnitPrice=($totalRawCost+$totalModifiedConsumableCost)/$finishedProductQuantity;
                  //echo 'finished unit price is '.$finishedUnitPrice.'<br/>';
                  // 20211218 AS WE CANNOT ENSURE ALL CONSUMABLES ARE THERE, WE ALSO CANNOT ENSURE THE FINAL PRICE OF THE CONSUMABLES
                  // if (abs($finishedUnitPrice - $this->request->data['UnitCost']['unit_cost']) > 0.001){
                    // $this->Session->setFlash('error al calcular precio unitario, según página el precio unitario es '.$this->request->data['UnitCost']['unit_cost'].' pero el valor que se quiere guardar es '.$finishedUnitPrice, 'default',['class' => 'error-message']);  
                    // throw new Exception();
                  // }
                  $millGramPrice=($millConversionProductQuantity > 0?($totalRawCostMill/$millConversionProductQuantity):0);  
                  
                  $message = "Se fabricaron ".$finishedProductQuantity." de producto ".$productName." calidad ".PRODUCTION_RESULT_CODE_A." en proceso producción ".$productionRunCode;
                  $finishedItemData=[
                    'StockItem'=>[
                      'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$productName."_".PRODUCTION_RESULT_CODE_A,
                      'stockitem_creation_date'=>$productionRunDate,
                      'product_id'=>$finishedProductId,
                      'unit_id'=>UNIT_UNIT,
                      'product_unit_price'=>$finishedUnitPrice,
                      'original_quantity'=>$finishedProductQuantity,
                      'remaining_quantity'=>$finishedProductQuantity,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
                      'raw_material_id'=>null,
                      'warehouse_id'=>$warehouseId,
                      //'raw_material_id'=>$rawMaterialId,
                    ],
                  ];    
                  //pr($finishedItem);
                  $this->StockItem->create();
                  if (!$this->StockItem->save($finishedItemData)) {
                    echo "problema guardando el lote de producto terminado";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  $latestStockItemId=$this->StockItem->id;
                    
                  $finishedProductionMovement=[
                    'ProductionMovement'=>[
                      'name'=>$productionRunCode."_".PRODUCTION_RESULT_CODE_A,
                      'description'=>$message,
                      'movement_date'=>$movementDate,
                      'bool_input'=>'0',
                      'stockitem_id'=>$latestStockItemId,
                      'production_run_id'=>$productionRunId,
                      'product_id'=>$finishedProductId,
                      'unit_id'=>UNIT_UNIT,
                      'product_quantity'=>$finishedProductQuantity,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
                      'product_unit_price'=>$finishedUnitPrice,
                    ],
                  ];
                  $this->ProductionMovement->create();
                  if (!$this->ProductionMovement->save($finishedProductionMovement['ProductionMovement'])) {
                    echo "problema guardando el movimiento de producción";
                    pr($this->validateErrors($this->ProductionMovement));
                    throw new Exception();
                  }
                  $productionMovementId=$this->ProductionMovement->id;
                  $stockItemLog=[
                    'StockItemLog'=>[
                      'production_movement_id'=>$productionMovementId,
                      'stockitem_id'=>$latestStockItemId,
                      'stockitem_date'=>$productionRunDate,
                      'product_id'=>$finishedProductId,
                      'unit_id'=>UNIT_UNIT,
                      'product_quantity'=>$finishedProductQuantity,
                      'product_unit_price'=>$finishedUnitPrice,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
                      'warehouse_id'=>$warehouseId,
                    ],
                  ];
                  $this->StockItemLog->create();
                  if (!$this->StockItemLog->save($stockItemLog['StockItemLog'])) {
                    echo "problema guardando el estado de lote";
                    pr($this->validateErrors($this->StockItemLog));
                    throw new Exception();
                  }
                  $this->recordUserActivity($this->Session->read('User.username'),$message);  
                  
                  // keep mill conversion product  
                  $millConversionProductName=$this->Product->getProductName($millConversionProductId);
                  $message = "Se fabricaron ".$millConversionProductQuantity." de producto ".$millConversionProductName." calidad ".PRODUCTION_RESULT_CODE_MILL." en proceso producción ".$productionRunCode;
                  $millConversionProductData=[
                    'StockItem'=>[
                      'name'=>$productionRunDate['day']."_".$productionRunDate['month']."_".$productionRunDate['year']."_".$productionRunCode."_".$productName."_".PRODUCTION_RESULT_CODE_MILL,
                      'stockitem_creation_date'=>$productionRunDate,
                      'product_id'=>$millConversionProductId,
                      'unit_id'=>UNIT_GRAM,
                      'product_unit_price'=>$millGramPrice,
                      'original_quantity'=>$millConversionProductQuantity,
                      'remaining_quantity'=>$millConversionProductQuantity,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_MILL,
                      'raw_material_id'=>null,
                      'warehouse_id'=>$warehouseId,
                      //'raw_material_id'=>$rawMaterialId,
                    ],
                  ];    
                  //pr($finishedItem);
                  $this->StockItem->create();
                  if (!$this->StockItem->save($millConversionProductData)) {
                    echo "problema guardando el lote de producto molino";
                    pr($this->validateErrors($this->StockItem));
                    throw new Exception();
                  }
                  $latestStockItemId=$this->StockItem->id;
                    
                  $millConversionProductionMovement=[
                    'ProductionMovement'=>[
                      'name'=>$productionRunCode."_".PRODUCTION_RESULT_CODE_MILL,
                      'description'=>$message,
                      'movement_date'=>$movementDate,
                      'bool_input'=>'0',
                      'stockitem_id'=>$latestStockItemId,
                      'production_run_id'=>$productionRunId,
                      'product_id'=>$millConversionProductId,
                      'unit_id'=>UNIT_GRAM,
                      'product_quantity'=>$millConversionProductQuantity,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_MILL,
                      'product_unit_price'=>$millGramPrice,
                      'bool_recycling'=>true,
                    ],
                  ];
                  $this->ProductionMovement->create();
                  if (!$this->ProductionMovement->save($millConversionProductionMovement['ProductionMovement'])) {
                    echo "problema guardando el movimiento de molino";
                    pr($this->validateErrors($this->ProductionMovement));
                    throw new Exception();
                  }
                  $productionMovementId=$this->ProductionMovement->id;
                  $stockItemLog=[
                    'StockItemLog'=>[
                      'production_movement_id'=>$productionMovementId,
                      'stockitem_id'=>$latestStockItemId,
                      'stockitem_date'=>$productionRunDate,
                      'product_id'=>$millConversionProductId,
                      'unit_id'=>UNIT_GRAM,
                      'product_quantity'=>$millConversionProductQuantity,
                      'product_unit_price'=>$millGramPrice,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_MILL,
                      'warehouse_id'=>$warehouseId,
                    ],
                  ];
                  $this->StockItemLog->create();
                  if (!$this->StockItemLog->save($stockItemLog['StockItemLog'])) {
                    echo "problema guardando el estado de lote de molino";
                    pr($this->validateErrors($this->StockItemLog));
                    throw new Exception();
                  }
                  $this->recordUserActivity($this->Session->read('User.username'),$message);  
                  
                  // keep the production loss
                  $message = "Se perdieron ".$wasteQuantity." gramos de merma calidad ".PRODUCTION_RESULT_CODE_WASTE." en proceso producción ".$productionRunCode;
                  $productionLossArray=[
                    'ProductionLoss'=>[
                      'name'=>$productionRunCode."_".PRODUCTION_RESULT_CODE_WASTE,
                      'description'=>$message,
                      'movement_date'=>$movementDate,
                      'production_run_id'=>$productionRunId,
                      'product_quantity'=>$wasteQuantity,
                      'unit_id'=>UNIT_GRAM,
                      'product_unit_price'=>$millGramPrice,
                      'production_result_code_id'=>PRODUCTION_RESULT_CODE_WASTE,
                    ],
                  ];
                  $this->ProductionLoss->create();
                  if (!$this->ProductionLoss->save($productionLossArray['ProductionLoss'])) {
                    echo "problema guardando el movimiento de merma";
                    pr($this->validateErrors($this->ProductionLoss));
                    throw new Exception();
                  }
                  
                  break;
                default:  
              }
              
              $datasource->commit();
              $this->recordUserAction($this->ProductionRun->id,null,null);
              $this->recordUserActivity($this->Session->read('User.username'),"Se editó proceso de producción con numero ".$this->request->data['ProductionRun']['production_run_code']);
              
              //echo "checkpoint before creating raw material stock item log<br/>";	
              foreach ($usedRawMaterials as $usedRawMaterial){
                $this->recreateStockItemLogs($usedRawMaterial['id']);
              }
              foreach ($usedBags as $usedBag){
                $this->recreateStockItemLogs($usedBag['id']);
              }
              if (!empty($consumableStockItemsArray)){
                foreach ($consumableStockItemsArray as $consumableStockItemId){
                  $this->recreateStockItemLogs($consumableStockItemId);
                }
              }
              $this->Session->setFlash(__('Se editó el proceso de producción.'),'default',['class' => 'success']);
              return $this->redirect(['action' => 'detalle',$id]);
            }
            catch(Exception $e){
              $datasource->rollback();
              pr($e);					
              $this->Session->setFlash(__('No se podía editar el proceso de producción.'), 'default',['class' => 'error-message']);
            }
          }
        }
      } 			
    }
		else {
			$this->request->data = $this->ProductionRun->find('first', [
				'conditions' => [
					'ProductionRun.id' => $id,
				],
				'contain'=>[
					'RawMaterial'=>[
						'ProductType',
					],
          'ProductionLoss',
					'ProductionMovement'=>[
						'StockItem'=>[
							'StockMovement'=>[
                'conditions'=>['StockMovement.product_quantity >'=>0],
								'Order',
							],
						]
					],
				],
			]);
			//$reasonForNonEditable="";
      $bagQuantity=0;
      $consumableMaterials=[];
      $consumableMaterialUnits=[];
      //pr($this->request->data);
      $consumableMaterialsNotBagIds=$this->Product->find('list',[
        'fields'=>'Product.id',
        'conditions'=>[
          'Product.product_type_id'=>$consumableMaterialTypeIds,
         ],
      ]);
      foreach ($productionResultCodes as $productionResultCodeId=>$productionResultCodeCode){
        $productionResultCodeOutputQuantities[$productionResultCodeId]=0;
      }
      //pr($consumableMaterialsNotBagIds);
      
      $requestMillIngredients=[];
      
      if (!empty($this->request->data['ProductionMovement'])){
        foreach ($this->request->data['ProductionMovement'] as $productionMovement){
          if (!$productionMovement['bool_input']){
            $productionResultCodeOutputQuantities[$productionMovement['production_result_code_id']]+=$productionMovement['product_quantity'];
            if ($productionMovement['production_result_code_id'] == PRODUCTION_RESULT_CODE_MILL){
              $millConversionProductId=$productionMovement['product_id'];
            }
            //if (!empty($productionMovement['StockItem']['StockMovement'])){
            //  $boolEditable='0';
            //}
          }
          else {
            //pr($productionMovement);
            if ($productionMovement['product_id'] == $this->request->data['ProductionRun']['bag_product_id']){
              $bagQuantity+=$productionMovement['product_quantity'];
            }
            else{
              //pr($consumableMaterialsNotBagIds);
              if (in_array($productionMovement['product_id'],$consumableMaterialsNotBagIds) || $productionTypeId == PRODUCTION_TYPE_INJECTION){
                //if (array_key_exists($productionMovement['product_id'],$consumableMaterials)){
                //  $consumableMaterials[$productionMovement['product_id']]+=$productionMovement['product_quantity'];
                //}
                //else {
                  $consumableMaterials[$productionMovement['product_id']]=$productionMovement['product_quantity'];
                  $consumableMaterialUnits[$productionMovement['product_id']]=$productionMovement['unit_id'];
                //}
              }
              elseif (array_key_exists($productionMovement['product_id'],$rawMaterialList) && !$productionMovement['bool_recycling'] && $productionTypeId == PRODUCTION_TYPE_INJECTION){
                $raw=[];
                $raw['product_id']= $productionMovement['product_id'];
                $raw['product_quantity']= $productionMovement['product_quantity'];
                $raw['unit_id']= UNIT_GRAM;
                $requestIngredients['Products'][]=$raw;
              }
            }
          
            // update the array for unit price calculations in order to take into account already used ingredients  
            //pr($productionMovement);
            $boolStockItemAdjusted='0';
            if (array_key_exists($productionMovement['product_id'],$rawMaterialStockItems)){
              for ($rmsi=0;$rmsi<count($rawMaterialStockItems[$productionMovement['product_id']]['StockItems']);$rmsi++){
                if ($rawMaterialStockItems[$productionMovement['product_id']]['StockItems'][$rmsi]['StockItem']['id'] == $productionMovement['stockitem_id']){
                  $rawMaterialStockItems[$productionMovement['product_id']]['StockItems'][$rmsi]['StockItem']['remaining_quantity']+=$productionMovement['product_quantity'];
                  $boolStockItemAdjusted=true;
                }
              }    
            }
            else {
              $rawMaterialStockItems[$productionMovement['product_id']]['StockItems']=[];
            }
            if (!$boolStockItemAdjusted){
              $reinsertedStockItem=$this->StockItem->getStockItemById($productionMovement['stockitem_id']);
              if (!empty($reinsertedStockItem)){
                  array_unshift($rawMaterialStockItems[$productionMovement['product_id']]['StockItems'],
                    ['StockItem'=>[
                      'id'=>$productionMovement['stockitem_id'],
                      'product_unit_price'=>$reinsertedStockItem['StockItem']['product_unit_price'],
                      'remaining_quantity'=>$productionMovement['product_quantity'],
                      'stockitem_creation_date'=>$reinsertedStockItem['StockItem']['stockitem_creation_date'],
                    ],
                  ]
                );  
              }              
            }
          }
        }
      }
      if (!empty($this->request->data['ProductionLoss'])){
        foreach ($this->request->data['ProductionLoss'] as $productionLoss){
          $productionResultCodeOutputQuantities[$productionLoss['production_result_code_id']]+=$productionLoss['product_quantity'];
        }
      }
      $requestConsumables=[];
      $requestMillIngredients=[];
      $requestRecipeConsumables=[];
      //pr($rawMaterialList);
      //pr($consumableMaterials);
      //pr($recipeConsumableList);
      if (!empty($consumableMaterials)){
        if ($productionTypeId == PRODUCTION_TYPE_PET){
          foreach ($consumableMaterials as $consumableMaterialId =>$consumableMaterialQuantity){            
            $consumable=[];
            $consumable['consumable_id']= $consumableMaterialId;
            $consumable['consumable_quantity']= $consumableMaterialQuantity;
            $requestConsumables['Consumables'][]=$consumable;
          }
        }
        elseif ($productionTypeId == PRODUCTION_TYPE_INJECTION){
          foreach ($consumableMaterials as $consumableMaterialId =>$consumableMaterialQuantity){            
            //echo $consumableMaterialId.'<br/>';
            if (array_key_exists($consumableMaterialId,$rawMaterialList)){
              $raw=[];
              $raw['product_id']= $consumableMaterialId;
              $raw['product_quantity']= $consumableMaterialQuantity;
              $raw['unit_id']= $consumableMaterialUnits[$consumableMaterialId];
              $requestMillIngredients['Products'][]=$raw;
            }
            elseif (in_array($consumableMaterialId,$recipeConsumableList)){
              $recipeConsumable=[];
              $recipeConsumable['product_id']= $consumableMaterialId;
              $recipeConsumable['product_quantity']= $consumableMaterialQuantity;
              $recipeConsumable['unit_id']= (array_key_exists($consumableMaterialId,$consumableMaterialUnits)?$consumableMaterialUnits[$consumableMaterialId]:UNIT_UNIT);
              $requestRecipeConsumables['Products'][]=$recipeConsumable;
            }
            else {
              //echo $consumableMaterialId.'<br/>';
              $consumable=[];
              $consumable['consumable_id']= $consumableMaterialId;
              $consumable['consumable_quantity']= $consumableMaterialQuantity;
              $consumable['unit_id']= $consumableMaterialUnits[$consumableMaterialId];
              $requestConsumables['Consumables'][]=$consumable;
            }
          }
        }
      }
      $this->request->data['ProductionRun']['bag_quantity']=$bagQuantity;
    }
		$this->set(compact('productionResultCodeOutputQuantities'));
    $this->set(compact('millConversionProductId'));
    $this->set(compact('requestConsumables'));
    $this->set(compact('requestRecipeConsumables'));
    $this->set(compact('requestIngredients'));
    $editabilityData=$this->ProductionRun->getEditabilityData($id);
    $this->set(compact('editabilityData'));  
      
    //pr($rawMaterialStockItems);
    $this->set(compact('rawMaterialStockItems'));
      
		$this->Product->recursive=-1;
    $rawMaterialConditions=[
      'Product.product_type_id'=>$rawProductTypeIds,
      'Product.bool_active'=>true, 
      'Product.production_type_id'=>$productionTypeId,
    ];
		$rawMaterialsAll=$this->Product->find('all', [
			'fields'=>'Product.id,Product.name',
			'conditions' => $rawMaterialConditions,
			'order'=>'Product.name',
		]);
		$rawMaterials=null;
		foreach ($rawMaterialsAll as $rawMaterial){
			$rawMaterials[$rawMaterial['Product']['id']]=$rawMaterial['Product']['name'];
		}

    $plantWarehouseIds=$this->Warehouse->getWarehouseIdsForPlantId($plantId);
    $warehouseProductIds=[];
    foreach ($plantWarehouseIds as $plantWarehouseId){
      $warehouseProductIds=array_merge($warehouseProductIds,$this->WarehouseProduct->getProductIdsForWarehouse($plantWarehouseId));
    }
    $consumables = $this->Product->find('list', [
			'fields'=>'Product.id,Product.name',
			'conditions' => [
        'Product.product_type_id'=> $consumableMaterialTypeIds,
        'Product.bool_active'=>true,
        'Product.id'=> $warehouseProductIds,
        //'Product.id !='=>PRODUCT_BAG, // 20190529 bag cannot be determined beforehand
      ],
			'order'=>'Product.name',
		]);
		$bags = $this->Product->find('list', [
			'fields'=>'Product.id,Product.name',
			'conditions' => [
        'Product.product_type_id'=> PRODUCT_TYPE_BAGS,
        'Product.bool_active'=>true,
        'Product.id'=> $warehouseProductIds,
      ],
			'order'=>'Product.name',
		]);
		$this->set(compact('consumables','bags'));
    
    if ($plantId > 0){
      $machines = $this->ProductionRun->Machine->getMachineListForPlant($plantId);
      $operators = $this->ProductionRun->Operator->getOperatorListForPlant($plantId);
    }
		$shifts = $this->ProductionRun->Shift->find('list');
	
		$this->set(compact('rawMaterials','machines', 'operators', 'shifts','rawMaterialsInventory'));
		
    $incidences=$this->ProductionRun->Incidence->find('list',[
			'order'=>'Incidence.list_order, Incidence.name',
		]);
		$this->set(compact('incidences'));
		
		$aco_name="Operators/reporteProduccionTotal";		
		$bool_operator_totalproductionreport_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_operator_totalproductionreport_permission'));
    
    $injectionRawMaterials = $this->Product->getProductsByProductionType(PRODUCTION_TYPE_INJECTION,PRODUCT_NATURE_RAW);
		$this->set(compact('injectionRawMaterials'));
    //pr($rawMaterials);
    
    $rawMaterialUnits=$this->Product->getProductUnitList(array_keys($injectionRawMaterials));
    $this->set(compact('rawMaterialUnits'));
    
    $recipeMillConversionProducts=$this->Recipe->getMillConversionProductIdsForRecipes();
    $this->set(compact('recipeMillConversionProducts'));
		
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
    
    $this->loadModel('StockItem');
		
		$linkedProductionRun=$this->ProductionRun->find('first',[
      'conditions'=>[
        'ProductionRun.id'=>$id,
      ],
    ]);
		$production_run_code=$linkedProductionRun['ProductionRun']['production_run_code'];
		
		$this->request->allowMethod('post', 'delete');
		
		// find produced stock for production run
		$producedMovements=$this->ProductionRun->ProductionMovement->find('all',array(
			'fields'=>array('stockitem_id','product_quantity','ProductionMovement.id'),
			'conditions'=>array(
				'production_run_id'=>$id,
				'bool_input'=>'0',
			),
		));
		$producedStockUntouched=true;
		$salesInvolved=[];
		// now check if the stockitems have not been sold yet
		foreach ($producedMovements as $producedMovement){
			$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$producedMovement['ProductionMovement']['stockitem_id'])));
			if ($stockItem['StockItem']['original_quantity']!=$stockItem['StockItem']['remaining_quantity']){
				$producedStockUntouched='0';
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
			$flashmessage="No se puede eliminar el proceso de producción porque los productos fabricados eran vendidos en salidas con código ";
			for ($i=0;$i<count($salesInvolved);$i++){
				if ($i!=count($salesInvolved)-1){
					$flashmessage.=$salesInvolved[$i].", ";
				}
				else {
					$flashmessage.=$salesInvolved[$i]. "!";
				}
			}
			$this->Session->setFlash($flashmessage, 'default',['class' => 'error-message']);
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
					$stockItem=[];
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
					if (!$this->ProductionRun->ProductionMovement->delete($usedMovement['ProductionMovement']['id'])) {
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
					if (!$this->ProductionRun->ProductionMovement->delete($producedMovement['ProductionMovement']['id'])) {
						echo "problema eliminando el movimiento de producción";
						pr($this->validateErrors($this->ProductionRun->ProductionMovement));
						throw new Exception();
					}
				}
        
        $productionLosses=$this->ProductionRun->ProductionLoss->find('all',[
          'fields'=>['ProductionLoss.product_quantity','ProductionLoss.id'],
          'conditions'=>[
            'production_run_id'=>$id,
          ],
        ]);
        if (!empty($productionLosses)){
          foreach ($productionLosses as $productionLoss){
            $this->ProductionRun->ProductionLoss->id=$productionLoss['ProductionLoss']['id'];
            if (!$this->ProductionRun->ProductionLoss->delete($productionLoss['ProductionLoss']['id'])) {
              echo "problema eliminando la pérdida de producción";
              pr($this->validateErrors($this->ProductionRun->ProductionLoss));
              throw new Exception();
            }
          }
        }
			
				// delete productionrun
				$this->ProductionRun->id = $id;
				if (!$this->ProductionRun->delete()) {
					echo "problema eliminando el proceso de producción";
					pr($this->validateErrors($this->ProductionRun));
					throw new Exception();
				}
				
				// recreate stockitemlogs
				foreach ($usedMovements as $usedMovement){
					$this->recreateStockItemLogs($usedMovement['ProductionMovement']['stockitem_id']);
				}
		
				$datasource->commit();
				$this->recordUserActivity($this->Session->read('User.username'),"Production run removed with code ".$production_run_code);			
				$this->Session->setFlash(__('The production run has been deleted.'), 'default',['class' => 'success']);
			} 		
			catch(Exception $e){
				$datasource->rollback();
				pr($e);					
				$this->Session->setFlash(__('The production run could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
			}
		}
		return $this->redirect(['action' => 'resumen']);
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
				'bool_input'=>'0',
			),
		));
		$producedStockUntouched=true;
		$salesInvolved=[];
		// now check if the stockitems have not been sold yet
		foreach ($producedMovements as $producedMovement){
			$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$producedMovement['ProductionMovement']['stockitem_id'])));
			if ($stockItem['StockItem']['original_quantity']!=$stockItem['StockItem']['remaining_quantity']){
				$producedStockUntouched='0';
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
			$flashmessage="No se puede eliminar el proceso de producción porque los productos fabricados eran vendidos en salidas con código ";
			for ($i=0;$i<count($salesInvolved);$i++){
				if ($i!=count($salesInvolved)-1){
					$flashmessage.=$salesInvolved[$i].", ";
				}
				else {
					$flashmessage.=$salesInvolved[$i]. "!";
				}
			}
			$this->Session->setFlash($flashmessage, 'default',['class' => 'error-message']);
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
					$success=$this->ProductionRun->ProductionMovement->delete($usedMovement['ProductionMovement']['id']);
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
					$success=$this->ProductionRun->ProductionMovement->delete($producedMovement['ProductionMovement']['id']);
					if (!$success) {
						echo "problema eliminando el movimiento de producción";
						pr($this->validateErrors($this->ProductionRun->ProductionMovement));
						throw new Exception();
					}
				}
			
				// delete productionrun
				$success=$this->ProductionRun->delete();
				if (!$success) {
					echo "problema eliminando el proceso de producción";
					pr($this->validateErrors($this->ProductionRun));
					throw new Exception();
				}
				
				// recreate stockitemlogs
				foreach ($usedMovements as $usedMovement){
					$this->recreateStockItemLogs($usedMovement['ProductionMovement']['stockitem_id']);
				}
		
				$datasource->commit();
				$this->recordUserActivity($this->Session->read('User.username'),"Production run removed with code ".$production_run_code);			
				$this->Session->setFlash(__('The production run has been deleted.'), 'default',['class' => 'success']);
			} 		
			catch(Exception $e){
				$datasource->rollback();
				pr($e);					
				$this->Session->setFlash(__('The production run could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
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

