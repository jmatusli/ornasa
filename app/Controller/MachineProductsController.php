<?php
App::uses('AppController', 'Controller');
/**
 * MachineProducts Controller
 *
 * @property MachineProduct $MachineProduct
 * @property PaginatorComponent $Paginator
 */
class MachineProductsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->MachineProduct->recursive = -1;
		
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
		
		$machineProductCount=	$this->MachineProduct->find('count', array(
			'fields'=>array('MachineProduct.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($machineProductCount!=0?$machineProductCount:1),
		);

		$machineProducts = $this->Paginator->paginate('MachineProduct');
		$this->set(compact('machineProducts'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->MachineProduct->exists($id)) {
			throw new NotFoundException(__('Invalid machine product'));
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
		$options = array('conditions' => array('MachineProduct.' . $this->MachineProduct->primaryKey => $id));
		$this->set('machineProduct', $this->MachineProduct->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->MachineProduct->create();
			if ($this->MachineProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The machine product has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The machine product could not be saved. Please, try again.'));
			}
		}
		$machines = $this->MachineProduct->Machine->find('list');
		$products = $this->MachineProduct->Product->find('list');
		$this->set(compact('machines', 'products'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->MachineProduct->exists($id)) {
			throw new NotFoundException(__('Invalid machine product'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->MachineProduct->save($this->request->data)) {
				$this->Session->setFlash(__('The machine product has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The machine product could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('MachineProduct.' . $this->MachineProduct->primaryKey => $id));
			$this->request->data = $this->MachineProduct->find('first', $options);
		}
		$machines = $this->MachineProduct->Machine->find('list');
		$products = $this->MachineProduct->Product->find('list');
		$this->set(compact('machines', 'products'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->MachineProduct->id = $id;
		if (!$this->MachineProduct->exists()) {
			throw new NotFoundException(__('Invalid machine product'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->MachineProduct->delete()) {
			$this->Session->setFlash(__('The machine product has been deleted.'));
		} else {
			$this->Session->setFlash(__('The machine product could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
