<?php
App::uses('AppController', 'Controller');

class ClientTypesController extends AppController {

	public $components = array('Paginator');

  public function resumen() {
		$this->ClientType->recursive = -1;
		/*
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
		*/
		$clientTypeCount=	$this->ClientType->find('count', [
			'fields'=>['ClientType.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($clientTypeCount!=0?$clientTypeCount:1),
		] ;

		$clientTypes = $this->Paginator->paginate('ClientType');
		$this->set(compact('clientTypes'));
	}

	public function detalle($id = null) {
		if (!$this->ClientType->exists($id)) {
			throw new NotFoundException(__('Invalid client type'));
		}
    
    $this->loadModel('ExchangeRate');
    
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
    
    $orderConditions=[
      'Order.client_type_id'=>$id,
      'Order.order_date >='=> $startDate,
      'Order.order_date <'=> $endDatePlusOne
    ];
    $quotationConditions=[
      'Quotation.client_type_id'=>$id,
      'Quotation.quotation_date >='=> $startDate,
      'Quotation.quotation_date <'=> $endDatePlusOne
    ];
    $salesOrderConditions=[
      'SalesOrder.client_type_id'=>$id,
      'SalesOrder.sales_order_date >='=> $startDate,
      'SalesOrder.sales_order_date <'=> $endDatePlusOne
    ];

    $options = [
      'conditions' => [
        'ClientType.id' => $id,
      ],
      'contain'=>[
        'Client',
        'Order'=>[
          'conditions'=>$orderConditions,
          'order'=>['Order.order_date DESC'],
          'Invoice',
          'ThirdParty',
          'VendorUser',
        ],
        'Quotation'=>[
          'conditions'=>$quotationConditions,
          'order'=>['Quotation.quotation_date DESC'],
          'Client',
          'VendorUser',
        ],
        'SalesOrder'=>[
          'conditions'=>$salesOrderConditions,
          'order'=>['SalesOrder.sales_order_date DESC'],
          'Client',
          'VendorUser',
        ],
      ],
    ];
    $clientType=$this->ClientType->find('first', $options);
    if (!empty($clientType['Order'])){
      for ($i=0;$i<count($clientType['Order']);$i++){
        //pr($clientType['Order'][$i]['Invoice'][0]);
        if ($clientType['Order'][$i]['Invoice'][0]['currency_id']==CURRENCY_USD){
          $clientType['Order'][$i]['price_subtotal_usd']=$clientType['Order'][$i]['Invoice'][0]['sub_total_price'];
        }
        else {
          $exchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($clientType['Order'][$i]['order_date']);
          $clientType['Order'][$i]['price_subtotal_usd']=$clientType['Order'][$i]['Invoice'][0]['sub_total_price']/$exchangeRate;
        }
      }
    }
    if (!empty($clientType['SalesOrder'])){
      for ($i=0;$i<count($clientType['SalesOrder']);$i++){
        //pr($clientType['SalesOrder'][$i]);
        if ($clientType['SalesOrder'][$i]['currency_id']==CURRENCY_USD){
          $clientType['SalesOrder'][$i]['price_subtotal_usd']=$clientType['SalesOrder'][$i]['price_subtotal'];
        }
        else {
          $exchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($clientType['SalesOrder'][$i]['sales_order_date']);
          $clientType['SalesOrder'][$i]['price_subtotal_usd']=$clientType['SalesOrder'][$i]['price_subtotal']/$exchangeRate;
        }
      }
    }
    if (!empty($clientType['Quotation'])){
      for ($i=0;$i<count($clientType['Quotation']);$i++){
        //pr($clientType['Quotation'][$i]);
        if ($clientType['Quotation'][$i]['currency_id']==CURRENCY_USD){
          $clientType['Quotation'][$i]['price_subtotal_usd']=$clientType['Quotation'][$i]['price_subtotal'];
        }
        else {
          $exchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($clientType['Quotation'][$i]['quotation_date']);
          $clientType['Quotation'][$i]['price_subtotal_usd']=$clientType['Quotation'][$i]['price_subtotal']/$exchangeRate;
        }
      }
    }
		$this->set(compact('clientType'));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->ClientType->create();
			if ($this->ClientType->save($this->request->data)) {
				$this->Session->setFlash(__('The client type has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash(__('The client type could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
	}

	public function editar($id = null) {
		if (!$this->ClientType->exists($id)) {
			throw new NotFoundException(__('Invalid client type'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->ClientType->save($this->request->data)) {
				$this->Session->setFlash(__('The client type has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The client type could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('ClientType.id' => $id));
			$this->request->data = $this->ClientType->find('first', $options);
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
		$this->ClientType->id = $id;
		if (!$this->ClientType->exists()) {
			throw new NotFoundException(__('Invalid client type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ClientType->delete()) {
			$this->Session->setFlash(__('The client type has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The client type could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
