<?php
App::uses('AppController', 'Controller');

class EmployeesController extends AppController {


	public $components = array('Paginator','RequestHandler');

	public function index() {
		$this->Employee->recursive = -1;
		//$employees=$this->Paginator->paginate();
		
		$this->loadModel('EmployeeHoliday');
		
		$employeeCount=$this->Employee->find('count',array(
			'fields'=>array('Employee.id'),
			'conditions'=>array('Employee.bool_active'=>true),
		));
		
		$this->Paginator->settings = array(
			'conditions'=>array('Employee.bool_active'=>true),
			'order' => array('Employee.bool_active'=>'DESC','Employee.last_name'=>'ASC'),
			'limit'=>$employeeCount
		);
		$employees = $this->Paginator->paginate('Employee');
		
		$nowDate=new DateTime();
		//$newYearDate=new DateTime(date('Y-1-1'));
		for ($e=0;$e<count($employees);$e++){
			$workingDays=0;
			/*
			if ($employees[$e]['Employee']['starting_date']>date('Y-01-01')){
				$startingDate=new DateTime($employees[$e]['Employee']['starting_date']);
				$daysThisYear=$nowDate->diff($startingDate);
			}
			else {
				$daysThisYear=$nowDate->diff($newYearDate);
			}
			*/
			$startingDate=new DateTime($employees[$e]['Employee']['starting_date']);
			$endingDate=new DateTime($employees[$e]['Employee']['ending_date']);
      if ($endingDate<$nowDate){
        $daysWorked=$endingDate->diff($startingDate);
      }
      else {
        $daysWorked=$nowDate->diff($startingDate);
      }
			$workingDays=$daysWorked->days;
			$holidaysEarned=2.5*$workingDays/30;
			$employees[$e]['Employee']['holidays_earned']=$holidaysEarned;
			$this->EmployeeHoliday->virtualFields['total_holidays_taken']=0;
			$employeeHolidays=$this->EmployeeHoliday->find('all',array(
				'fields'=>array(
					'SUM(days_taken) AS EmployeeHoliday__total_holidays_taken', 
				),
				'conditions'=>array(
					'EmployeeHoliday.employee_id'=>$employees[$e]['Employee']['id'],
					//'EmployeeHoliday.holiday_date >='=>date('Y-01-01'),
				),
			));
			//pr($employeeHolidays);
			if (!empty($employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'])){
				$holidaysTaken=$employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'];
			}
			else {
				$holidaysTaken=0;
			}
			$employees[$e]['Employee']['holidays_taken']=$holidaysTaken;
		}
		//pr($employees);
		$this->set(compact('employees'));
		
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function resumenEmpleadosDesactivados() {
		$this->Employee->recursive = -1;
		//$employees=$this->Paginator->paginate();
		
		$this->loadModel('EmployeeHoliday');
		
		$employeeCount=$this->Employee->find('count',array(
			'fields'=>array('Employee.id'),
			'conditions'=>array('Employee.bool_active'=>false),
		));
		
		$this->Paginator->settings = array(
			'conditions'=>array('Employee.bool_active'=>false),
			'order' => array('Employee.bool_active'=>'DESC','Employee.last_name'=>'ASC'),
			'limit'=>$employeeCount,
		);
		$employees = $this->Paginator->paginate('Employee');
		
		$nowDate=new DateTime();
		//$newYearDate=new DateTime(date('Y-1-1'));
		for ($e=0;$e<count($employees);$e++){
			$workingDays=0;
			/*
			if ($employees[$e]['Employee']['starting_date']>date('Y-01-01')){
				$startingDate=new DateTime($employees[$e]['Employee']['starting_date']);
				$daysThisYear=$nowDate->diff($startingDate);
			}
			else {
				$daysThisYear=$nowDate->diff($newYearDate);
			}
			*/
			$startingDate=new DateTime($employees[$e]['Employee']['starting_date']);
			$endingDate=new DateTime($employees[$e]['Employee']['ending_date']);
      if ($endingDate<$nowDate){
        $daysWorked=$endingDate->diff($startingDate);
      }
      else {
        $daysWorked=$nowDate->diff($startingDate);
      }
			$workingDays=$daysWorked->days;
			$holidaysEarned=2.5*$workingDays/30;
			$employees[$e]['Employee']['holidays_earned']=$holidaysEarned;
			$this->EmployeeHoliday->virtualFields['total_holidays_taken']=0;
			$employeeHolidays=$this->EmployeeHoliday->find('all',array(
				'fields'=>array(
					'SUM(days_taken) AS EmployeeHoliday__total_holidays_taken', 
				),
				'conditions'=>array(
					'EmployeeHoliday.employee_id'=>$employees[$e]['Employee']['id'],
					//'EmployeeHoliday.holiday_date >='=>date('Y-01-01'),
				),
			));
			//pr($employeeHolidays);
			if (!empty($employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'])){
				$holidaysTaken=$employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'];
			}
			else {
				$holidaysTaken=0;
			}
			$employees[$e]['Employee']['holidays_taken']=$holidaysTaken;
		}
		//pr($employees);
		$this->set(compact('employees'));
		
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function view($id = null) {
		if (!$this->Employee->exists($id)) {
			throw new NotFoundException(__('Invalid employee'));
		}
    
    $this->loadModel('EmployeeHoliday');
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
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date( "Y-m-d", strtotime( $endDate."+1 days" ) );
		}

		if (!isset($startDate)){
			$startDate = date("Y-01-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
				
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate'));
		
		$options = [
			'conditions' => ['Employee.id' => $id],
			'contain'=>[
				'EmployeeHoliday'=>[
					'conditions' => [
						'EmployeeHoliday.employee_id'=> $id,
						'EmployeeHoliday.holiday_date >='=> $startDate,
						'EmployeeHoliday.holiday_date <'=> $endDatePlusOne,
					],
					'order'=>'holiday_date ASC',
					'HolidayType',
				]
			],
		];
		$employee=$this->Employee->find('first', $options);
		$this->set(compact('employee'));
		
    $startingDateTime=new DateTime($employee['Employee']['starting_date']);
    $endingDateTime=new DateTime($employee['Employee']['ending_date']);
    $yearArray=$this->EmployeeHoliday->getHolidayYearArray($employee['Employee']['id'],$startingDateTime,$endingDateTime);
    //pr($yearArray);
    $this->set(compact('yearArray'));
    
		$filename='Hoja_Vacaciones_'.$employee['Employee']['first_name']."_".$employee['Employee']['last_name']."_".date('d_m_Y');
		$this->set(compact('filename'));
		
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function verPdfHojaVacaciones($startDate=null,$endDate=null,$employee_id=null){
		if ($startDate==null){
			$startDateString=$_SESSION['startDate'];
		}
		else {
			$startDateString=$startDate;
		}
		$startDate=date( "Y-m-d", strtotime($startDateString));
		if ($endDate==null){
			$endDateString=$_SESSION['endDate'];
		}
		else {
			$endDateString=$endDate;
		}
		$endDate=date("Y-m-d",strtotime($endDateString."+1 days"));
		$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		
		$options = array(
			'conditions' => array('Employee.' . $this->Employee->primaryKey => $employee_id),
			'contain'=>array(
				'EmployeeHoliday'=>array(
					'conditions' => array(
						'EmployeeHoliday.employee_id'=> $employee_id,
						'EmployeeHoliday.holiday_date >='=> $startDate,
						'EmployeeHoliday.holiday_date <'=> $endDatePlusOne,
					),
					'order'=>'holiday_date ASC',
					'HolidayType',
				)
			),
		);
		$employee=$this->Employee->find('first', $options);
		$this->set(compact('employee'));
		
		$this->set(compact('startDate','endDate','endDatePlusOne','accountingCodes','statusFlows','results'));	
		
		$filename='Hoja_Vacaciones_'.$employee['Employee']['first_name']."_".$employee['Employee']['last_name']."_".date('d_m_Y');
		$this->set(compact('filename'));
	}
	
	public function add() {
		if ($this->request->is('post')) {
			$datasource=$this->Employee->getDataSource();
			try {
				$datasource->begin();
				
				$this->Employee->create();
				if (!$this->Employee->save($this->request->data)) {
					echo "Problema guardando el empleado";
					pr($this->validateErrors($this->Employee));
					throw new Exception();
				}
				$employee_id=$this->Employee->id;
				if (!empty($this->request->data['Document']['url_image'][0]['tmp_name'])){
					$imageOK=$this->uploadFiles('employeeimages/'.$employee_id,$this->request->data['Document']['url_image']);
					//echo "image OK<br/>";
					//pr($imageOK);
					if (array_key_exists('urls',$imageOK)){
						$this->request->data['Employee']['url_image']=$imageOK['urls'][0];
					}
				}
				if (!$this->Employee->save($this->request->data)) {
					echo "Problema guardando el empleado con su imagen";
					pr($this->validateErrors($this->Employee));
					throw new Exception();
				}
				$datasource->commit();
				$this->recordUserAction($this->Employee->id,null,null);
				$this->recordUserActivity($this->Session->read('User.username'),"Se creó el empleado ".$this->request->data['Employee']['first_name']." ".$this->request->data['Employee']['last_name']);
				$this->Session->setFlash(__('Se guardó el empleado.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			catch(Exception $e){
				$datasource->rollback();
				//pr($e);
				$this->Session->setFlash(__('No se guardó el empleado.'), 'default',array('class' => 'error-message'));
			}
		}
		
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->Employee->exists($id)) {
			throw new NotFoundException(__('Invalid employee'));
		}
		if ($this->request->is(array('post', 'put'))) {
			$datasource=$this->Employee->getDataSource();
			try {
				$datasource->begin();
				
				$this->Employee->id=$id;
				$employee_id=$id;
				if (!empty($this->request->data['Document']['url_image'][0]['tmp_name'])){
					$imageOK=$this->uploadFiles('employeeimages/'.$employee_id,$this->request->data['Document']['url_image']);
					//echo "image OK<br/>";
					//pr($imageOK);
					if (array_key_exists('urls',$imageOK)){
						$this->request->data['Employee']['url_image']=$imageOK['urls'][0];
					}
				}
				if (!$this->Employee->save($this->request->data)) {
					echo "Problema guardando el empleado";
					pr($this->validateErrors($this->Employee));
					throw new Exception();
				}
				$datasource->commit();
				$this->recordUserAction($this->Employee->id,null,null);
				$this->recordUserActivity($this->Session->read('User.username'),"Se creó el empleado ".$this->request->data['Employee']['first_name']." ".$this->request->data['Employee']['last_name']);
				$this->Session->setFlash(__('Se guardó el empleado.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			catch(Exception $e){
				$datasource->rollback();
				//pr($e);
				$this->Session->setFlash(__('No se guardó el empleado.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('Employee.' . $this->Employee->primaryKey => $id));
			$this->request->data = $this->Employee->find('first', $options);
		}
		
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Employee->id = $id;
		if (!$this->Employee->exists()) {
			throw new NotFoundException(__('Invalid employee'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Employee->delete()) {
			$this->Session->setFlash(__('The employee has been deleted.'));
		} else {
			$this->Session->setFlash(__('The employee could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
