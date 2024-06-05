<?php
App::uses('AppController', 'Controller');
/**
 * DeliveryRemarks Controller
 *
 * @property DeliveryRemark $DeliveryRemark
 * @property PaginatorComponent $Paginator
 */
class DeliveryRemarksController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

public function resumen() {
		$this->DeliveryRemark->recursive = -1;
		
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
		
		$deliveryRemarkCount=	$this->DeliveryRemark->find('count', [
			'fields'=>['DeliveryRemark.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($deliveryRemarkCount!=0?$deliveryRemarkCount:1),
		] ;

		$deliveryRemarks = $this->Paginator->paginate('DeliveryRemark');
		$this->set(compact('deliveryRemarks'));
	}

	public function detalle($id = null) {
		if (!$this->DeliveryRemark->exists($id)) {
			throw new NotFoundException(__('Invalid delivery remark'));
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
		$options = [
      'conditions' => [
        'DeliveryRemark.id' => $id,
      ],
    ];
		$this->set('deliveryRemark', $this->DeliveryRemark->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->DeliveryRemark->create();
			if ($this->DeliveryRemark->save($this->request->data)) {
				$this->Session->setFlash(__('The delivery remark has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash(__('The delivery remark could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
		$deliveries = $this->DeliveryRemark->Delivery->find('list');
		$registeringUsers = $this->DeliveryRemark->RegisteringUser->find('list');
		$this->set(compact('deliveries', 'registeringUsers'));
	}


	public function edit($id = null) {
		if (!$this->DeliveryRemark->exists($id)) {
			throw new NotFoundException(__('Invalid delivery remark'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->DeliveryRemark->save($this->request->data)) {
				$this->Session->setFlash(__('The delivery remark has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The delivery remark could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('DeliveryRemark.id' => $id));
			$this->request->data = $this->DeliveryRemark->find('first', $options);
		}
		$deliveries = $this->DeliveryRemark->Delivery->find('list');
		$registeringUsers = $this->DeliveryRemark->RegisteringUser->find('list');
		$this->set(compact('deliveries', 'registeringUsers'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->DeliveryRemark->id = $id;
		if (!$this->DeliveryRemark->exists()) {
			throw new NotFoundException(__('Invalid delivery remark'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->DeliveryRemark->delete()) {
			$this->Session->setFlash(__('The delivery remark has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The delivery remark could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
