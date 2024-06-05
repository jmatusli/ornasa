<?php
App::uses('AppController', 'Controller');
/**
 * ProductProductions Controller
 *
 * @property ProductProduction $ProductProduction
 * @property PaginatorComponent $Paginator
 */
class ProductProductionsController extends AppController {


	public $components = array('Paginator');

	public function index() {
		$this->ProductProduction->recursive = -1;
		
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
		
		$productProductionCount=	$this->ProductProduction->find('count', array(
			'fields'=>array('ProductProduction.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($productProductionCount!=0?$productProductionCount:1),
		);

		$productProductions = $this->Paginator->paginate('ProductProduction');
		$this->set(compact('productProductions'));
	}

	public function view($id = null) {
		if (!$this->ProductProduction->exists($id)) {
			throw new NotFoundException(__('Invalid product production'));
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
		$options = array('conditions' => array('ProductProduction.' . $this->ProductProduction->primaryKey => $id));
		$this->set('productProduction', $this->ProductProduction->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->ProductProduction->create();
			if ($this->ProductProduction->save($this->request->data)) {
				$this->Session->setFlash(__('The product production has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The product production could not be saved. Please, try again.'));
			}
		}
		$products = $this->ProductProduction->Product->find('list');
		$this->set(compact('products'));
	}

	public function edit($id = null) {
		if (!$this->ProductProduction->exists($id)) {
			throw new NotFoundException(__('Invalid product production'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProductProduction->save($this->request->data)) {
				$this->Session->setFlash(__('Se guardó la producción aceptable.'), 'default',array('class' => 'success'));
				return $this->redirect(array('controller'=>'products','action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('No se podía guardar la producción aceptable.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('ProductProduction.' . $this->ProductProduction->primaryKey => $id));
			$this->request->data = $this->ProductProduction->find('first', $options);
		}
		$products = $this->ProductProduction->Product->find('list');
		$this->set(compact('products'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ProductProduction->id = $id;
		if (!$this->ProductProduction->exists()) {
			throw new NotFoundException(__('Invalid product production'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProductProduction->delete()) {
			$this->Session->setFlash(__('The product production has been deleted.'));
		} else {
			$this->Session->setFlash(__('The product production could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
