<?php
App::uses('AppController', 'Controller');

class HolidayTypesController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->HolidayType->recursive = -1;
		
		$holidayTypeCount=	$this->HolidayType->find('count', array(
			'fields'=>array('HolidayType.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($holidayTypeCount!=0?$holidayTypeCount:1),
		);

		$holidayTypes = $this->Paginator->paginate('HolidayType');
		$this->set(compact('holidayTypes'));
		
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
	}

	public function view($id = null) {
		if (!$this->HolidayType->exists($id)) {
			throw new NotFoundException(__('Invalid holiday type'));
		}
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
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		$options = array('conditions' => array('HolidayType.' . $this->HolidayType->primaryKey => $id));
		$this->set('holidayType', $this->HolidayType->find('first', $options));
		
		$aco_name="EmployeeHolidays/index";		
		$bool_employeeholiday_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_index_permission'));
		$aco_name="EmployeeHolidays/add";		
		$bool_employeeholiday_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_add_permission'));
		
		$aco_name="EmployeeHolidays/delete";		
		$bool_employeeholiday_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_employeeholiday_delete_permission'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->HolidayType->create();
			if ($this->HolidayType->save($this->request->data)) {
				$this->recordUserAction($this->HolidayType->id,null,null);
				$this->Session->setFlash(__('The holiday type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The holiday type could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
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
		if (!$this->HolidayType->exists($id)) {
			throw new NotFoundException(__('Invalid holiday type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->HolidayType->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The holiday type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The holiday type could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('HolidayType.' . $this->HolidayType->primaryKey => $id));
			$this->request->data = $this->HolidayType->find('first', $options);
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
		$this->HolidayType->id = $id;
		if (!$this->HolidayType->exists()) {
			throw new NotFoundException(__('Invalid holiday type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->HolidayType->delete()) {
			$this->Session->setFlash(__('The holiday type has been deleted.'));
		} else {
			$this->Session->setFlash(__('The holiday type could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
