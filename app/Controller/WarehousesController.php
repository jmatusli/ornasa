<?php
App::uses('AppController', 'Controller');

class WarehousesController extends AppController {


	public $components = array('Paginator');

	public function index() {
		$this->Warehouse->recursive = -1;
		$this->set('warehouses', $this->Paginator->paginate());
	}

	public function view($id = null) {
		if (!$this->Warehouse->exists($id)) {
			throw new NotFoundException(__('Invalid warehouse'));
		}
		
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
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
				
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$options = array(
			'conditions' => array(
				'Warehouse.id'=> $id,
			),
			'contain'=>array(

			)
		);
		$warehouse=$this->Warehouse->find('first', $options);
		
		$this->set(compact('warehouse'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->Warehouse->create();
			if ($this->Warehouse->save($this->request->data)) {
				$this->recordUserAction($this->Warehouse->id,null,null);
				$this->Session->setFlash(__('The warehouse has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The warehouse could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
	}

	public function edit($id = null) {
		if (!$this->Warehouse->exists($id)) {
			throw new NotFoundException(__('Invalid warehouse'));
		}
		$this->Warehouse->recursive=-1;
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Warehouse->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The warehouse has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The warehouse could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('Warehouse.id' => $id));
			$this->request->data = $this->Warehouse->find('first', $options);
		}
	}

	public function delete($id = null) {
		$this->Warehouse->id = $id;
		if (!$this->Warehouse->exists()) {
			throw new NotFoundException(__('Invalid warehouse'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Warehouse->delete()) {
			$this->Session->setFlash(__('The warehouse has been deleted.'));
		} 
		else {
			$this->Session->setFlash(__('The warehouse could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
