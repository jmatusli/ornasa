<?php
App::uses('AppController', 'Controller');
/**
 * ExchangeRates Controller
 *
 * @property ExchangeRate $ExchangeRate
 * @property PaginatorComponent $Paginator
 */
class ExchangeRatesController extends AppController {

	public $components = ['Paginator'];

  public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow('getexchangerate','getquotationexchangerate');		
	}

	public function index() {
		$this->ExchangeRate->recursive = -1;
		
		$exchangeRateCount=$this->ExchangeRate->find('count', array(
			'fields' => 'ExchangeRate.id'
		));
		
		$this->Paginator->settings = array(
			'contain'=>array(
				'ConversionCurrency',
				'BaseCurrency'
			),
			'order'=>'application_date DESC',
			'limit'=>($exchangeRateCount!=0?$exchangeRateCount:1)
		);
		$this->set('exchangeRates', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->ExchangeRate->exists($id)) {
			throw new NotFoundException(__('Invalid exchange rate'));
		}
		$options = array('conditions' => array('ExchangeRate.' . $this->ExchangeRate->primaryKey => $id));
		$this->set('exchangeRate', $this->ExchangeRate->find('first', $options));
		
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->ExchangeRate->create();
			if ($this->ExchangeRate->save($this->request->data)) {
				$this->recordUserAction($this->ExchangeRate->id,null,null);
				$this->Session->setFlash(__('The exchange rate has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The exchange rate could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$conversionCurrencies = $this->ExchangeRate->ConversionCurrency->find('list');
		$baseCurrencies = $this->ExchangeRate->BaseCurrency->find('list');
		$this->set(compact('conversionCurrencies', 'baseCurrencies'));
	}

	public function edit($id = null) {
		if (!$this->ExchangeRate->exists($id)) {
			throw new NotFoundException(__('Invalid exchange rate'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ExchangeRate->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The exchange rate has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The exchange rate could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('ExchangeRate.' . $this->ExchangeRate->primaryKey => $id));
			$this->request->data = $this->ExchangeRate->find('first', $options);
		}
		$conversionCurrencies = $this->ExchangeRate->ConversionCurrency->find('list');
		$baseCurrencies = $this->ExchangeRate->BaseCurrency->find('list');
		$this->set(compact('conversionCurrencies', 'baseCurrencies','id'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ExchangeRate->id = $id;
		if (!$this->ExchangeRate->exists()) {
			throw new NotFoundException(__('Invalid exchange rate'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ExchangeRate->delete()) {
			$this->Session->setFlash(__('The exchange rate has been deleted.'));
		} else {
			$this->Session->setFlash(__('The exchange rate could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

	public function getexchangerate(){
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
		$receiptday=trim($_POST['receiptday']);
		$receiptmonth=trim($_POST['receiptmonth']);
		$receiptyear=trim($_POST['receiptyear']);
		
		$receiptDateString=$receiptyear.'-'.$receiptmonth.'-'.$receiptday;
		$receiptDate=date( "Y-m-d", strtotime($receiptDateString));
		$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($receiptDate);
		return $cashReceiptExchangeRate['ExchangeRate']['rate'];
	}
  
  public function getquotationexchangerate(){
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		//$this->layout('ajax');
		
		$selectedday=trim($_POST['selectedday']);
		$selectedmonth=trim($_POST['selectedmonth']);
		$selectedyear=trim($_POST['selectedyear']);
		
		$selectedDateString=$selectedyear.'-'.$selectedmonth.'-'.$selectedday;
		$selectedDate=date( "Y-m-d", strtotime($selectedDateString));
		$exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($selectedDate);
		//pr($exchangeRate);
		return $exchangeRate['ExchangeRate']['rate'];
	}
}
