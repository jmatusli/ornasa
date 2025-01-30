<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');
/**
 * ProductionMovements Controller
 *
 * @property ProductionMovement $ProductionMovement
 * @property PaginatorComponent $Paginator
 */
class ProductionMovementsController extends AppController {


	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 

	public function index() {
		$this->ProductionMovement->recursive = 0;
		$this->set('productionMovements', $this->Paginator->paginate());
	}

	public function view($id = null) {
		if (!$this->ProductionMovement->exists($id)) {
			throw new NotFoundException(__('Invalid production movement'));
		}
		$options = array('conditions' => array('ProductionMovement.' . $this->ProductionMovement->primaryKey => $id));
		$this->set('productionMovement', $this->ProductionMovement->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->ProductionMovement->create();
			if ($this->ProductionMovement->save($this->request->data)) {
				$this->Session->setFlash(__('The production movement has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The production movement could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$stockItems = $this->ProductionMovement->StockItem->find('list');
		$productionRuns = $this->ProductionMovement->ProductionRun->find('list');
		$productTypes = $this->ProductionMovement->ProductType->find('list');
		$this->set(compact('stockItems', 'productionRuns', 'productTypes'));
	}

	public function edit($id = null) {
		if (!$this->ProductionMovement->exists($id)) {
			throw new NotFoundException(__('Invalid production movement'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProductionMovement->save($this->request->data)) {
				$this->Session->setFlash(__('The production movement has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The production movement could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('ProductionMovement.' . $this->ProductionMovement->primaryKey => $id));
			$this->request->data = $this->ProductionMovement->find('first', $options);
		}
		$stockItems = $this->ProductionMovement->StockItem->find('list');
		$productionRuns = $this->ProductionMovement->ProductionRun->find('list');
		$productTypes = $this->ProductionMovement->ProductType->find('list');
		$this->set(compact('stockItems', 'productionRuns', 'productTypes'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ProductionMovement->id = $id;
		if (!$this->ProductionMovement->exists()) {
			throw new NotFoundException(__('Invalid production movement'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProductionMovement->delete()) {
			$this->Session->setFlash(__('The production movement has been deleted.'));
		} else {
			$this->Session->setFlash(__('The production movement could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	public function verReporteProduccionMeses($startDate = null,$endDate=null) {
		$report_type=0;
		$this->loadModel('ProductionRun');
    $this->loadModel('Shift');
    $this->loadModel('Machine');
    
    $this->Shift->recursive=-1;
    $this->Machine->recursive=-1;
	
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$report_type=$this->request->data['Report']['report_type'];
		}
		else{
			$startDate = date("Y-01-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		//$_SESSION['startDate']=$startDate;
		//$_SESSION['endDate']=$endDate;
		
		$this->set(compact('startDate','endDate','report_type'));
		
		// get the relevant time period
		$startDateDay=date("d",strtotime($startDate));
		$startDateMonth=date("m",strtotime($startDate));
		$startDateYear=date("Y",strtotime($startDate));
		$endDateDay=date("d",strtotime($endDate));
		$endDateMonth=date("m",strtotime($endDate));
		$endDateYear=date("Y",strtotime($endDate));
		
		$monthArray=[];
		$counter=0;
		for ($yearCounter=$startDateYear;$yearCounter<=$endDateYear;$yearCounter++){
			if ($yearCounter==$startDateYear && $yearCounter==$endDateYear){
				// only 1 year in consideration
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=$endDateMonth;
			}
			else if($yearCounter==$startDateYear){
				// starting year (not the same as ending year)
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=12;
			}
			else if ($yearCounter==$endDateYear){
				// ending year (not the same as starting year)
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=$endDateMonth;
			}
			else {
				// in between year
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=12;
			}
			for ($monthCounter=$startingMonth;$monthCounter<=$endingMonth;$monthCounter++){
				$monthArray[$counter]['period']=$monthCounter.'_'.$yearCounter;
				if ($monthCounter==$startDateMonth && $yearCounter == $startDateYear){
					$monthArray[$counter]['start']=$startingDay;
				}
				else {
					$monthArray[$counter]['start']=1;
				}
				$monthArray[$counter]['month']=$monthCounter;
				$monthArray[$counter]['year']=$yearCounter;
				$counter++;
			}
		}
		//pr($monthArray);
    
    $shiftMonthArray=$monthArray;
    $this->set(compact('shiftMonthArray'));
    
    $shifts=$this->Shift->find('all',[
      'fields'=>['Shift.id','Shift.name'],
      'order'=>'Shift.display_order ASC',
    ]);
    $machines=$this->Machine->find('all',[
      'fields'=>['Machine.id','Machine.name'],
      'conditions'=>['Machine.bool_active'=>true],
      'order'=>'Machine.name ASC',
    ]);
    
    
    for ($s=0;$s<count($shifts);$s++){
      $shifts[$s]['machines']=[];
      foreach ($machines as $machine){
        $machineArray=[];
        $machineArray['id']=$machine['Machine']['id'];
        $machineArray['name']=$machine['Machine']['name'];
        $machineArray['machine_periods']=[];
        $shifts[$s]['machines'][]=$machineArray;
      }         
    }
		
		for ($productionPeriod=0;$productionPeriod<count($monthArray);$productionPeriod++){
			$period=$monthArray[$productionPeriod]['period'];
			$start=$monthArray[$productionPeriod]['start'];
			$month=$monthArray[$productionPeriod]['month'];
			$nextmonth=($month==12)?1:($month+1);
			$year=$monthArray[$productionPeriod]['year'];
			$nextyear=($month==12)?($year+1):$year;
			$productionStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
			$productionEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
			$productionEndDatePlusOne=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
			
      $shiftMachineProductionRunConditions=$productionRunConditions=[
        'bool_annulled'=>'0',
        'production_run_date >='=> $productionStartDate,
        'production_run_date <'=> $productionEndDatePlusOne,
      ];
      
      for ($s=0;$s<count($shifts);$s++){
        for ($m=0;$m<count($shifts[$s]['machines']);$m++){
          $machinePeriodArray=[];
          $shiftMachineProductionRunConditions['shift_id']=$shifts[$s]['Shift']['id'];
          $shiftMachineProductionRunConditions['machine_id']=$shifts[$s]['machines'][$m]['id'];
          //pr($shiftMachineProductionRunConditions);
          $productionRunsForMonthShiftMachine=$this->ProductionRun->find('all',[
            'fields'=>['id','production_run_date',],
            'conditions'=>$shiftMachineProductionRunConditions,
          ]);
          $acceptableRunCounter=0;
          if (!empty($productionRunsForMonthShiftMachine)){            
            foreach ($productionRunsForMonthShiftMachine as $productionRun){
              $boolAcceptable=$this->ProductionRun->checkAcceptableProduction($productionRun['ProductionRun']['id'],$productionRun['ProductionRun']['production_run_date']);
              if ($boolAcceptable){
                $acceptableRunCounter++;
              }
            }
          }
          $machinePeriodArray['total_op']=count($productionRunsForMonthShiftMachine);
          $machinePeriodArray['acceptable_op']=$acceptableRunCounter;
          $shifts[$s]['machines'][$m]['machine_periods'][$period]=$machinePeriodArray;
        }
      }
      
			$productionRunCountForMonth=$this->ProductionRun->find('count',[
				'conditions'=>$productionRunConditions,
			]);
			//pr($productionRunCountForMonth);
			$monthArray[$productionPeriod]['production_run_count']=$productionRunCountForMonth;
			
			$this->ProductionMovement->virtualFields['total_A_cost']=0;
			$this->ProductionMovement->virtualFields['total_A_quantity']=0;
			$this->ProductionMovement->recursive=-1;
			$productionMovementAForMonth=$this->ProductionMovement->find('first',array(
				'fields'=>array('SUM(product_quantity*product_unit_price) as ProductionMovement__total_A_cost','SUM(product_quantity) as ProductionMovement__total_A_quantity'),
				'conditions'=>array(
					'bool_input'=>'0',
					'movement_date >='=> $productionStartDate,
					'movement_date <='=> $productionEndDate,
					'production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
				),
			));
			$this->ProductionMovement->virtualFields['total_B_cost']=0;
			$this->ProductionMovement->virtualFields['total_B_quantity']=0;
			$this->ProductionMovement->recursive=-1;
			$productionMovementBForMonth=$this->ProductionMovement->find('first',array(
				'fields'=>array('SUM(product_quantity*product_unit_price) as ProductionMovement__total_B_cost','SUM(product_quantity) as ProductionMovement__total_B_quantity'),
				'conditions'=>array(
					'bool_input'=>'0',
					'movement_date >='=> $productionStartDate,
					'movement_date <='=> $productionEndDate,
					'production_result_code_id'=>PRODUCTION_RESULT_CODE_B,
				),
			));
			$this->ProductionMovement->virtualFields['total_C_cost']=0;
			$this->ProductionMovement->virtualFields['total_C_quantity']=0;
			$this->ProductionMovement->recursive=-1;
			$productionMovementCForMonth=$this->ProductionMovement->find('first',array(
				'fields'=>array('SUM(product_quantity*product_unit_price) as ProductionMovement__total_C_cost','SUM(product_quantity) as ProductionMovement__total_C_quantity'),
				'conditions'=>array(
					'bool_input'=>'0',
					'movement_date >='=> $productionStartDate,
					'movement_date <='=> $productionEndDate,
					'production_result_code_id'=>PRODUCTION_RESULT_CODE_C,
				),
			));
			
			if (!empty($productionMovementAForMonth)){
				$monthArray[$productionPeriod]['total_A_cost']=$productionMovementAForMonth['ProductionMovement']['total_A_cost'];
				$monthArray[$productionPeriod]['total_A_quantity']=$productionMovementAForMonth['ProductionMovement']['total_A_quantity'];
			}
			else {
				$monthArray[$productionPeriod]['total_A_cost']=0;
				$monthArray[$productionPeriod]['total_A_quantity']=0;
			}
			if (!empty($productionMovementBForMonth)){
				$monthArray[$productionPeriod]['total_B_cost']=$productionMovementBForMonth['ProductionMovement']['total_B_cost'];
				$monthArray[$productionPeriod]['total_B_quantity']=$productionMovementBForMonth['ProductionMovement']['total_B_quantity'];
			}
			else {
				$monthArray[$productionPeriod]['total_B_cost']=0;
				$monthArray[$productionPeriod]['total_B_quantity']=0;
			}
			if (!empty($productionMovementCForMonth)){
				$monthArray[$productionPeriod]['total_C_cost']=$productionMovementCForMonth['ProductionMovement']['total_C_cost'];
				$monthArray[$productionPeriod]['total_C_quantity']=$productionMovementCForMonth['ProductionMovement']['total_C_quantity'];
			}
			else {
				$monthArray[$productionPeriod]['total_C_cost']=0;
				$monthArray[$productionPeriod]['total_C_quantity']=0;
			}
		}
		//pr($monthArray);
		//pr($shifts);
		$this->set(compact('monthArray'));
    $this->set(compact('shifts'));
	}

	public function guardarReporteProduccionMeses() {
		$exportData=$_SESSION['reporteProduccionMeses'];
		$this->set(compact('exportData'));
	}
}
