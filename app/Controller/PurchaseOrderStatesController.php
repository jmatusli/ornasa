<?php
App::uses('AppController', 'Controller');
/**
 * PurchaseOrderStates Controller
 *
 * @property PurchaseOrderState $PurchaseOrderState
 * @property PaginatorComponent $Paginator
 */
class PurchaseOrderStatesController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

public function resumen() {
		$this->PurchaseOrderState->recursive = -1;
		
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
		
		$purchaseOrderStateCount=	$this->PurchaseOrderState->find('count', [
			'fields'=>['PurchaseOrderState.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($purchaseOrderStateCount!=0?$purchaseOrderStateCount:1),
		] ;

		$purchaseOrderStates = $this->Paginator->paginate('PurchaseOrderState');
		$this->set(compact('purchaseOrderStates'));
	}

	public function detalle($id = null) {
		if (!$this->PurchaseOrderState->exists($id)) {
			throw new NotFoundException(__('Invalid purchase order state'));
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
        'PurchaseOrderState.id' => $id,
      ],
    ];
		$this->set('purchaseOrderState', $this->PurchaseOrderState->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->PurchaseOrderState->create();
			if ($this->PurchaseOrderState->save($this->request->data)) {
				$this->Session->setFlash(__('The purchase order state has been saved.'));
				return $this->redirect(['action' => 'resumen'], 'default',['class' => 'success'] );
			} else {
				$this->Session->setFlash(__('The purchase order state could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
	}


	public function edit($id = null) {
		if (!$this->PurchaseOrderState->exists($id)) {
			throw new NotFoundException(__('Invalid purchase order state'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->PurchaseOrderState->save($this->request->data)) {
				$this->Session->setFlash(__('The purchase order state has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The purchase order state could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} 
    else {
			$options = array('conditions' => array('PurchaseOrderState.id' => $id));
			$this->request->data = $this->PurchaseOrderState->find('first', $options);
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
		$this->PurchaseOrderState->id = $id;
		if (!$this->PurchaseOrderState->exists()) {
			throw new NotFoundException(__('Invalid purchase order state'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PurchaseOrderState->delete()) {
			$this->Session->setFlash(__('The purchase order state has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The purchase order state could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
