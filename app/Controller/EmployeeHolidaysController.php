<?php
App::uses('AppController', 'Controller');

class EmployeeHolidaysController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->EmployeeHoliday->recursive = -1;
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		
		if (!isset($startDate)){
			$startDate = date("Y-01-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		
		$employeeHolidayCount=	$this->EmployeeHoliday->find('count', array(
			'fields'=>array('EmployeeHoliday.id'),
			'conditions' => array(
				'EmployeeHoliday.holiday_date >='=>$startDate,
				'EmployeeHoliday.holiday_date <'=>$endDatePlusOne,
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
				'EmployeeHoliday.holiday_date >='=>$startDate,
				'EmployeeHoliday.holiday_date <'=>$endDatePlusOne,
			),
			'contain'=>array(
				'Employee',
				'HolidayType',
			),
      'order'=>'EmployeeHoliday.holiday_date',
			'limit'=>($employeeHolidayCount!=0?$employeeHolidayCount:1),
		);

		$employeeHolidays= $this->Paginator->paginate('EmployeeHoliday');
		$this->set(compact('employeeHolidays'));
		
		$aco_name="EmployeeHolidays/registrarFeriado";		
		$bool_holiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_holiday_add_permission'));
		
		
		$aco_name="Employees/index";		
		$bool_employee_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employee_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_add_permission'));
	}

	public function view($id = null) {
		if (!$this->EmployeeHoliday->exists($id)) {
			throw new NotFoundException(__('Invalid employee holiday'));
		}
		
		$options = array('conditions' => array('EmployeeHoliday.' . $this->EmployeeHoliday->primaryKey => $id));
		$this->set('employeeHoliday', $this->EmployeeHoliday->find('first', $options));
		
		$aco_name="EmployeeHolidays/registrarFeriado";		
		$bool_holiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_holiday_add_permission'));
		
		
		$aco_name="Employees/index";		
		$bool_employee_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employee_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_add_permission'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->EmployeeHoliday->create();
			if ($this->EmployeeHoliday->save($this->request->data)) {
				$this->recordUserAction($this->EmployeeHoliday->id,null,null);
				$this->Session->setFlash(__('The employee holiday has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(['action' => 'view',$this->EmployeeHoliday->id]);
			} 
			else {
				$this->Session->setFlash(__('The employee holiday could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
			}
		}
		$employees = $this->EmployeeHoliday->Employee->find('list',array(
			'conditions'=>array(
				'Employee.bool_active'=>true,
			)
		));
		$this->set(compact('employees'));
		$holidayTypes = $this->EmployeeHoliday->HolidayType->find('list');
		$this->set(compact('holidayTypes'));
		
		$aco_name="EmployeeHolidays/registrarFeriado";		
		$bool_holiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_holiday_add_permission'));
		
		
		$aco_name="Employees/index";		
		$bool_employee_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employee_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_add_permission'));
	}

	public function registrarFeriado() {
		if ($this->request->is('post')) {
			try{
				$datasource=$this->EmployeeHoliday->getDataSource();
				$datasource->begin();
				//pr($this->request->data);
				foreach ($this->request->data['Employee'] as $employee){
					//pr($employee);
					$checked=reset($employee['employee_id']);
					if ($checked){
						//echo "employee id is ".key($employee['employee_id'])."<br/>";
						$holidayData=array();
						$holidayData['EmployeeHoliday']['employee_id']=key($employee['employee_id']);
						$holidayData['EmployeeHoliday']['holiday_date']=$this->request->data['EmployeeHoliday']['holiday_date'];
						$holidayData['EmployeeHoliday']['days_taken']=$this->request->data['EmployeeHoliday']['days_taken'];
						$holidayData['EmployeeHoliday']['holiday_type_id']=$this->request->data['EmployeeHoliday']['holiday_type_id'];
						$holidayData['EmployeeHoliday']['observation']=$this->request->data['EmployeeHoliday']['observation'];
						$this->EmployeeHoliday->create();
						if (!$this->EmployeeHoliday->save($holidayData)) {
							echo "problema guardando las vacaciones";
							pr($this->validateErrors($this->EmployeeHoliday));
							throw new Exception();
						} 
						else {
							$this->recordUserAction($this->EmployeeHoliday->id,null,null);
						}
					}
				}	
				$datasource->commit();
				$this->recordUserAction();
				$this->recordUserActivity($this->Session->read('User.username'),"Registración de Días de Feriado ");
				$this->Session->setFlash(__('The employee holiday has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			}
			catch (Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('The employee holiday could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
			}
		}
		
		$employees = $this->EmployeeHoliday->Employee->find('list',array(
			'conditions'=>array(
				'Employee.bool_active'=>true,
			)
		));
		$this->set(compact('employees'));
		
		$holidayTypes = $this->EmployeeHoliday->HolidayType->find('list');
		$this->set(compact('holidayTypes'));
		
		$aco_name="EmployeeHolidays/registrarFeriado";		
		$bool_holiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_holiday_add_permission'));
		
		$aco_name="Employees/index";		
		$bool_employee_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employee_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->EmployeeHoliday->exists($id)) {
			throw new NotFoundException(__('Invalid employee holiday'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->EmployeeHoliday->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The employee holiday has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'view',$id));
			} 
			else {
				$this->Session->setFlash(__('The employee holiday could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('EmployeeHoliday.' . $this->EmployeeHoliday->primaryKey => $id));
			$this->request->data = $this->EmployeeHoliday->find('first', $options);
		}
		$employees = $this->EmployeeHoliday->Employee->find('list');
		$this->set(compact('employees'));
		
		$holidayTypes = $this->EmployeeHoliday->HolidayType->find('list');
		$this->set(compact('holidayTypes'));
		
		$aco_name="EmployeeHolidays/registrarFeriado";		
		$bool_holiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_holiday_add_permission'));
		
		$aco_name="Employees/index";		
		$bool_employee_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employee_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employee_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->EmployeeHoliday->id = $id;
		if (!$this->EmployeeHoliday->exists()) {
			throw new NotFoundException(__('Invalid employee holiday'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->EmployeeHoliday->delete()) {
			$this->Session->setFlash(__('The employee holiday has been deleted.'));
		} else {
			$this->Session->setFlash(__('The employee holiday could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
