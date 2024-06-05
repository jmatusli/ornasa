<?php
App::uses('AppController', 'Controller');
/**
 * PaymentModes Controller
 *
 * @property PaymentMode $PaymentMode
 * @property PaginatorComponent $Paginator
 */
class PaymentModesController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->PaymentMode->recursive = -1;
		
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
		
		$paymentModeCount=	$this->PaymentMode->find('count', array(
			'fields'=>array('PaymentMode.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($paymentModeCount!=0?$paymentModeCount:1),
		);

		$paymentModes = $this->Paginator->paginate('PaymentMode');
		$this->set(compact('paymentModes'));
	}

	public function view($id = null) {
		if (!$this->PaymentMode->exists($id)) {
			throw new NotFoundException(__('Invalid payment mode'));
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
		$options = array('conditions' => array('PaymentMode.' . $this->PaymentMode->primaryKey => $id));
		$this->set('paymentMode', $this->PaymentMode->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->PaymentMode->create();
			if ($this->PaymentMode->save($this->request->data)) {
				$this->Session->setFlash(__('The payment mode has been saved.'), 'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The payment mode could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
	}

	public function edit($id = null) {
		if (!$this->PaymentMode->exists($id)) {
			throw new NotFoundException(__('Invalid payment mode'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->PaymentMode->save($this->request->data)) {
				$this->Session->setFlash(__('The payment mode has been saved.'), 'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The payment mode could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('PaymentMode.' . $this->PaymentMode->primaryKey => $id));
			$this->request->data = $this->PaymentMode->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->PaymentMode->id = $id;
		if (!$this->PaymentMode->exists()) {
			throw new NotFoundException(__('Invalid payment mode'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$paymentMode=$this->PaymentMode->find('first',array(
			'conditions'=>array(
				'PaymentMode.id'=>$id,
			),
		));
		
		if ($this->PaymentMode->delete()) {
			$this->loadModel('Deletion');
			$this->Deletion->create();
			$deletionArray=array();
			$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
			$deletionArray['Deletion']['reference_id']=$paymentMode['PaymentMode']['id'];
			$deletionArray['Deletion']['reference']=$paymentMode['PaymentMode']['name'];
			$deletionArray['Deletion']['type']='PaymentMode';
			$this->Deletion->save($deletionArray);

			$this->Session->setFlash(__('The payment mode has been deleted.'));
		} 
		else {
			$this->Session->setFlash(__('The payment mode could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
