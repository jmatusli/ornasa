<?php
App::uses('AppController', 'Controller');

class ZonesController extends AppController {

	public $components = array('Paginator');

  public function resumen() {
		$this->Zone->recursive = -1;
		
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
		
		$zoneCount=	$this->Zone->find('count', [
			'fields'=>['Zone.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
      'order'=>'Zone.list_order ASC,Zone.name ASC',
			'limit'=>($zoneCount!=0?$zoneCount:1),
		] ;

		$zones = $this->Paginator->paginate('Zone');
		$this->set(compact('zones'));
    //pr($zones);
	}

	public function detalle($id = null) {
		if (!$this->Zone->exists($id)) {
			throw new NotFoundException(__('Invalid zone'));
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
      'Order.zone_id'=>$id,
      'Order.order_date >='=> $startDate,
      'Order.order_date <'=> $endDatePlusOne
    ];
    $quotationConditions=[
      'Quotation.zone_id'=>$id,
      'Quotation.quotation_date >='=> $startDate,
      'Quotation.quotation_date <'=> $endDatePlusOne
    ];
    $salesOrderConditions=[
      'SalesOrder.zone_id'=>$id,
      'SalesOrder.sales_order_date >='=> $startDate,
      'SalesOrder.sales_order_date <'=> $endDatePlusOne
    ];

		$options = [
      'conditions' => [
        'Zone.id' => $id,
      ],
      'contain'=>[
        'ThirdParty',
        'Order'=>[
          'conditions'=>$orderConditions,
          'order'=>['Order.order_date DESC'],
          'Invoice',
          'ThirdParty',
          'User',
        ],
        'Quotation'=>[
          'conditions'=>$quotationConditions,
          'order'=>['Quotation.quotation_date DESC'],
          'Client',
          'User',
        ],
        'SalesOrder'=>[
          'conditions'=>$salesOrderConditions,
          'order'=>['SalesOrder.sales_order_date DESC'],
          'Client',
          'User',
        ],
      ],
    ];
    $zone=$this->Zone->find('first', $options);
    //pr($zone);
		if (!empty($zone['Order'])){
      for ($i=0;$i<count($zone['Order']);$i++){
        //pr($zone['Order'][$i]);
        if ($zone['Order'][$i]['Invoice'][0]['currency_id']==CURRENCY_USD){
          $zone['Order'][$i]['price_subtotal_usd']=$zone['Order'][$i]['Invoice'][0]['sub_total_price'];
        }
        else {
          $exchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($zone['Order'][$i]['order_date']);
          $zone['Order'][$i]['price_subtotal_usd']=$zone['Order'][$i]['Invoice'][0]['sub_total_price']/$exchangeRate;
        }
      }
    }
    if (!empty($zone['SalesOrder'])){
      for ($i=0;$i<count($zone['SalesOrder']);$i++){
        //pr($zone['SalesOrder'][$i]);
        if ($zone['SalesOrder'][$i]['currency_id']==CURRENCY_USD){
          $zone['SalesOrder'][$i]['price_subtotal_usd']=$zone['SalesOrder'][$i]['price_subtotal'];
        }
        else {
          $exchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($zone['SalesOrder'][$i]['sales_order_date']);
          $zone['SalesOrder'][$i]['price_subtotal_usd']=$zone['SalesOrder'][$i]['price_subtotal']/$exchangeRate;
        }
      }
    }
    if (!empty($zone['Quotation'])){
      for ($i=0;$i<count($zone['Quotation']);$i++){
        //pr($zone['Quotation'][$i]);
        if ($zone['Quotation'][$i]['currency_id']==CURRENCY_USD){
          $zone['Quotation'][$i]['price_subtotal_usd']=$zone['Quotation'][$i]['price_subtotal'];
        }
        else {
          $exchangeRate=$this->ExchangeRate->getApplicableExchangeRateValue($zone['Quotation'][$i]['quotation_date']);
          $zone['Quotation'][$i]['price_subtotal_usd']=$zone['Quotation'][$i]['price_subtotal']/$exchangeRate;
        }
      }
    }
		$this->set(compact('zone'));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->Zone->create();
			if ($this->Zone->save($this->request->data)) {
				$this->Session->setFlash(__('The zone has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash(__('The zone could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
	}

	public function editar($id = null) {
		if (!$this->Zone->exists($id)) {
			throw new NotFoundException(__('Invalid zone'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->Zone->save($this->request->data)) {
				$this->Session->setFlash(__('The zone has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The zone could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('Zone.id' => $id));
			$this->request->data = $this->Zone->find('first', $options);
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
		$this->Zone->id = $id;
		if (!$this->Zone->exists()) {
			throw new NotFoundException(__('Invalid zone'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Zone->delete()) {
			$this->Session->setFlash(__('The zone has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The zone could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
