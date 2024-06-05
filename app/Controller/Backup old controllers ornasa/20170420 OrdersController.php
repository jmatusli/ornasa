<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class OrdersController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('setduedate');		
	}
	
	public function setduedate(){
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
		$clientid=trim($_POST['clientid']);
		$emissionday=trim($_POST['emissionday']);
		$emissionmonth=trim($_POST['emissionmonth']);
		$emissionyear=trim($_POST['emissionyear']);
	
		$this->loadModel('ThirdParty');
		if (!$clientid){
			throw new NotFoundException(__('Cliente no está presente'));
		}
		if (!$this->ThirdParty->exists($clientid)) {
			throw new NotFoundException(__('Cliente inválido'));
		}
		
		$client=$this->ThirdParty->find('first',array('conditions'=>array('ThirdParty.id'=>$clientid)));
		
		$creditperiod=0;
		if (!empty($client)){
			$creditperiod=$client['ThirdParty']['credit_days'];
		}
		$emissionDateString=$emissionyear.'-'.$emissionmonth.'-'.$emissionday;
		$emissionDate=date( "Y-m-d", strtotime($emissionDateString));
		
		$dueDate=$emissionDate;
		if($creditperiod>0){
			$dueDate=date("Y-m-d",strtotime($emissionDate."+".$creditperiod." days"));
		}
		
		$this->set(compact('dueDate'));
	}
	
	public function resumenEntradas($lastMonth=0) {
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
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
	
		$this->Order->recursive = -1;
		
		$purchaseConditions=array(
			'Order.stock_movement_type_id'=> MOVEMENT_PURCHASE,
			'Order.order_date >='=> $startDate,
			'Order.order_date <'=> $endDatePlusOne,
		);
		
		$purchaseCount=$this->Order->find('count', array(
			'conditions' => $purchaseConditions,
		));
		
		$purchases=array();
		$this->Paginator->settings = array(
			'conditions' => $purchaseConditions,
			'contain'=>array(
				'StockMovement'=>array(
					'fields'=>array('product_id','product_quantity'),
					'Product'=>array(
						'fields'=>array('product_type_id'),
						'ProductType'=>array(
							'fields'=>array('product_category_id'),
						),
					),
				),
				'ThirdParty'=>array(
					'fields'=>array('id','company_name'),
				),
			),
			'order'=>'order_date DESC,order_code DESC',
			'limit'=>($purchaseCount!=0?$purchaseCount:1)
		);
		$purchases = $this->Paginator->paginate('Order');
		$this->set(compact('purchases', 'startDate','endDate'));
		
		$orderIdsForPeriod=$this->Order->find('list',array(
			'fields'=>array('Order.id'),
			'conditions' => $purchaseConditions,
		));
		$this->loadModel('ProductType');
		$unfinishedProductTypes=$this->ProductType->find('all',array(
			'conditions'=>array(
				'ProductType.product_category_id !='=>CATEGORY_PRODUCED,
			),
			'contain'=>array(
				'Product'=>array(
					'StockMovement'=>array(
						'conditions'=>array(
							'StockMovement.order_id'=>$orderIdsForPeriod,
						),
					),
				),
			),
			'order'=>'ProductType.product_category_id ASC, ProductType.name ASC',
		));
		//pr($unfinishedProductTypes);
		for ($pt=0;$pt<count($unfinishedProductTypes);$pt++){
			for ($p=0;$p<count($unfinishedProductTypes[$pt]['Product']);$p++){
				$packagingUnitProduct=$unfinishedProductTypes[$pt]['Product'][$p]['packaging_unit'];
				$totalQuantityProduct=0;
				$totalCostProduct=0;
				foreach ($unfinishedProductTypes[$pt]['Product'][$p]['StockMovement'] as $stockMovement){
					$totalQuantityProduct+=$stockMovement['product_quantity'];
					$totalCostProduct+=$stockMovement['product_total_price'];
				}
				if ($packagingUnitProduct>0){
					$unfinishedProductTypes[$pt]['Product'][$p]['total_packages']=round($totalQuantityProduct/$packagingUnitProduct);
				}
				else {
					$unfinishedProductTypes[$pt]['Product'][$p]['total_packages']=-1;
				}
				
				$unfinishedProductTypes[$pt]['Product'][$p]['total_quantity_product']=$totalQuantityProduct;
				$unfinishedProductTypes[$pt]['Product'][$p]['total_cost_product']=$totalCostProduct;
			}
		}
		//pr($unfinishedProductTypes);
		$this->set(compact('unfinishedProductTypes'));
		
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}

	public function resumenVentasRemisiones() {
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
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
	
		$this->Order->recursive = 0;
		
		$salesForPeriod=$this->Order->find('all',array(
			'fields'=>array(),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('id','company_name')),
				'StockMovement'=>array(
					'fields'=>array('StockMovement.product_quantity','StockMovement.production_result_code_id'),
					'StockItem'=>array(
						'fields'=>array('product_unit_price'),
					),
					'Product'=>array(
						'ProductType'=>array(
							'fields'=>array('product_category_id')
						)
					)
				),
				'Invoice'=>array(
					'fields'=>array(
						'Invoice.id','Invoice.invoice_code','Invoice.bool_annulled',
						'Invoice.currency_id','Invoice.total_price',
					),
					'Currency'
				),
				'CashReceipt'=>array(
					'fields'=>array(
						'CashReceipt.id','CashReceipt.receipt_code','CashReceipt.bool_annulled',
						'CashReceipt.currency_id','CashReceipt.amount',
					),
					'Currency'
				),
			),
			'conditions' => array(
				'Order.stock_movement_type_id'=> MOVEMENT_SALE,
				'Order.order_date >='=> $startDate,
				'Order.order_date <'=> $endDatePlusOne,
			),
			'order'=>'order_date DESC,order_code DESC',
		));
		//pr($salesForPeriod);
		
		$quantitySales=0;
		// loop to determine quantity
		foreach ($salesForPeriod as $sale){
			//pr($sale);
			if (!empty($sale['Invoice'])){
				if ($sale['Invoice'][0]['bool_annulled']){
					$quantitySales+=1;
				}
			}
			else {
				foreach ($sale['StockMovement'] as $stockMovement){
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
						$quantitySales+=1;
					}
					elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
						$quantitySales+=1;
					}
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantitySales!=0?$quantitySales:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProduced=0;
		$totalQuantityOther=0;
		
		$sales=array();
		// loop to get extended information
		foreach ($salesForPeriod as $sale){
			$quantityProduced=0;
			$quantityOther=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				//pr ($stockMovement);
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_A){
					$quantityProduced+=$stockMovement['product_quantity'];
					$totalQuantityProduced+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_OTHER) {
					$quantityOther+=$stockMovement['product_quantity'];
					$totalQuantityOther+=$stockMovement['product_quantity'];
				}
				
				$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProduced+$quantityOther)>0)||(!empty($sale['Invoice'])&&$sale['Invoice'][0]['bool_annulled'])){
				$sales[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$sales[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$sales[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$sales[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$sales[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$sales[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$sales[$rowCounter]['Order']['total_cost']=$totalCost;
				$sales[$rowCounter]['Order']['quantity_other']=$quantityOther;
				$sales[$rowCounter]['Order']['quantity_produced']=$quantityProduced;
				$sales[$rowCounter]['Order']['total_quantity_other']=$totalQuantityOther;
				$sales[$rowCounter]['Order']['total_quantity_produced']=$totalQuantityProduced;
				if (!empty($sale['Invoice'])){
					//pr($sale);
					$sales[$rowCounter]['Invoice']=$sale['Invoice'][0];
					if($sale['Invoice'][0]['bool_annulled']){
						$sales[$rowCounter]['Invoice']['bool_annulled']=true;
					}
					else {
						$sales[$rowCounter]['Invoice']['bool_annulled']=false;
					}
				}
				else {
					$sales[$rowCounter]['Invoice']['bool_annulled']=false;
				}
				$rowCounter++;
			}
		}
	
		$quantityRemissions=0;
		// loop to determine quantity remissions
		foreach ($salesForPeriod as $sale){
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityRemissions+=1;
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityRemissions+=1;
				}
			}
		}
		
		$this->Paginator->settings = array(
			'limit'=>($quantityRemissions!=0?$quantityRemissions:1)
		);
		$salesShown = $this->Paginator->paginate('Order');
		$rowCounter=0;
		
		$totalQuantityProducedB=0;
		$totalQuantityProducedC=0;
		
		$remissions=array();
		foreach ($salesForPeriod as $sale){
			$quantityProducedB=0;
			$quantityProducedC=0;
			$totalCost=0;
			foreach ($sale['StockMovement'] as $stockMovement){
				if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED  && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_B){
					$quantityProducedB+=$stockMovement['product_quantity'];
					$totalQuantityProducedB+=$stockMovement['product_quantity'];
				}
				elseif ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED && $stockMovement['production_result_code_id']==PRODUCTION_RESULT_CODE_C){
					$quantityProducedC+=$stockMovement['product_quantity'];
					$totalQuantityProducedC+=$stockMovement['product_quantity'];
				}
				
				$totalCost+=$stockMovement['product_quantity']*$stockMovement['StockItem']['product_unit_price'];
			}
			if ((($quantityProducedB+$quantityProducedC)>0)||(!empty($sale['CashReceipt'])&&$sale['CashReceipt'][0]['bool_annulled'])){
				$remissions[$rowCounter]['Order']['id']=$sale['Order']['id'];
				$remissions[$rowCounter]['Order']['order_date']=$sale['Order']['order_date'];
				$remissions[$rowCounter]['Order']['order_code']=$sale['Order']['order_code'];
				$remissions[$rowCounter]['ThirdParty']['company_name']=$sale['ThirdParty']['company_name'];
				$remissions[$rowCounter]['ThirdParty']['id']=$sale['ThirdParty']['id'];
				$remissions[$rowCounter]['Order']['total_price']=$sale['Order']['total_price'];
				$remissions[$rowCounter]['Order']['total_cost']=$totalCost;
				$remissions[$rowCounter]['Order']['quantity_produced_B']=$quantityProducedB;
				$remissions[$rowCounter]['Order']['quantity_produced_C']=$quantityProducedC;
				$remissions[$rowCounter]['Order']['total_quantity_produced_B']=$totalQuantityProducedB;
				$remissions[$rowCounter]['Order']['total_quantity_produced_C']=$totalQuantityProducedC;
				if (!empty($sale['CashReceipt'])){
					$remissions[$rowCounter]['CashReceipt']=$sale['CashReceipt'][0];
					if ($sale['CashReceipt'][0]['bool_annulled']){
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=true;
						//pr($remissions[$rowCounter]);
					}
					else {
						$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
					}
				}
				else {
					$remissions[$rowCounter]['CashReceipt']['bool_annulled']=false;
				}
				$rowCounter++;
			}
		}
		
		$this->set(compact('sales','remissions','startDate','endDate'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function guardarResumenVentasRemisiones() {
		$exportData=$_SESSION['resumenVentasRemisiones'];
		$this->set(compact('exportData'));
	}	
	
	public function verEntrada($id = null) {
		$this->Order->recursive=2;
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid purchase'));
		}
		$options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
		$this->set('order', $this->Order->find('first', $options));
		
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
	public function verPdfEntrada($id = null) {
		$this->Order->recursive=2;
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid purchase'));
		}
		$options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
		$this->set('order', $this->Order->find('first', $options));
	}
	
	public function verVenta($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid sale'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('Invoice');
		$this->loadModel('ExchangeRate');
		
		$this->Product->recursive=0;
		$this->Invoice->recursive=0;
		
		$options = array(
			'conditions' => array('Order.' . $this->Order->primaryKey => $id),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('ThirdParty.id, ThirdParty.company_name')),
			),
		);
		$order=$this->Order->find('first', $options);
		
		$invoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.order_id'=>$id,
			),
			'contain'=>array(
				'AccountingRegisterInvoice'=>array(
					'AccountingRegister'=>array(
						'AccountingMovement'=>array(
							'AccountingCode',
						),
					),
				),
				'CashboxAccountingCode',
				'Currency'=>array('fields'=>array('Currency.id, Currency.abbreviation')),
			)
		));
		if (!empty($invoice)){
			$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
			$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
			
			$invoice_total_price_CS=$invoice['Invoice']['total_price'];
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$invoice_total_price_CS*=$exchangeRateCurrent;
			}
			$invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($invoice['Invoice']['id']);
			$invoice['Invoice']['total_price_CS']=$invoice_total_price_CS;
			$invoice['Invoice']['pendingCS']=$invoice_total_price_CS-$invoice_paid_already_CS;
		}
		$summedMovements=$this->StockMovement->find('all',array(
			'fields'=>array('SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'),
			'conditions'=>array('StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'),
			'group'=>array('Product.id, StockItem.raw_material_id, ProductionResultCode.code'),
		));
		
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName="";
			if (!empty($summedMovements[$i]['StockItem']['raw_material_id'])){
				$linkedRawMaterial=$this->Product->read(null,$summedMovements[$i]['StockItem']['raw_material_id']);
				//pr ($linkedRawMaterial);
				$rawMaterialName=$linkedRawMaterial['Product']['name'];
			}
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		
		$cashReceiptsForInvoice=array();
		if (!empty($invoice)){
			if ($invoice['Invoice']['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('all',array(
					'fields'=>array(
						'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.currency_id',
						'Currency.abbreviation','Currency.id',
						'CashReceipt.id','CashReceipt.receipt_date','CashReceipt.receipt_code',
					),
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$invoice['Invoice']['id'],
					),
				));
			}
		}
		//pr($cashReceiptsForInvoice);
		
		$this->set(compact('order','summedMovements','invoice','cashReceiptsForInvoice','exchangeRateCurrent'));
		
		//if (!empty($invoice)){
		//	$creditDays=$this->Invoice->getCreditDays($invoice['Invoice']['id']);
		//}
		//$this->set(compact('creditDays'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}
	
	public function verPdfVenta($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid sale'));
		}
		
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('Invoice');
		$this->loadModel('ExchangeRate');
		
		
		$this->Product->recursive=0;
		$this->Invoice->recursive=0;
		
		$options = array(
			'conditions' => array('Order.' . $this->Order->primaryKey => $id),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('ThirdParty.id, ThirdParty.company_name')),
			),
		);
		$order=$this->Order->find('first', $options);
		
		$invoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.order_id'=>$id,
			),
			'contain'=>array(
				'CashboxAccountingCode',
				'Currency'=>array('fields'=>array('Currency.id, Currency.abbreviation')),
			)
		));
		if (!empty($invoice)){
			$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
			$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
			
			$invoice_total_price_CS=$invoice['Invoice']['total_price'];
			if ($invoice['Invoice']['currency_id']==CURRENCY_USD){
				$invoice_total_price_CS*=$exchangeRateCurrent;
			}
			$invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($invoice['Invoice']['id']);
			$invoice['Invoice']['total_price_CS']=$invoice_total_price_CS;
			$invoice['Invoice']['pendingCS']=$invoice_total_price_CS-$invoice_paid_already_CS;
		}
		$summedMovements=$this->StockMovement->find('all',array(
			'fields'=>array('SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'),
			'conditions'=>array('StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'),
			'group'=>array('Product.id, StockItem.raw_material_id, ProductionResultCode.code'),
		));
		
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName="";
			if (!empty($summedMovements[$i]['StockItem']['raw_material_id'])){
				$linkedRawMaterial=$this->Product->read(null,$summedMovements[$i]['StockItem']['raw_material_id']);
				//pr ($linkedRawMaterial);
				$rawMaterialName=$linkedRawMaterial['Product']['name'];
			}
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		
		$cashReceiptsForInvoice=array();
		if (!empty($invoice)){
			if ($invoice['Invoice']['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('all',array(
					'fields'=>array(
						'CashReceiptInvoice.cash_receipt_id','CashReceiptInvoice.amount','CashReceiptInvoice.currency_id',
						'Currency.abbreviation','Currency.id',
						'CashReceipt.id','CashReceipt.receipt_date','CashReceipt.receipt_code',
					),
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$invoice['Invoice']['id'],
					),
				));
			}
		}
		//pr($cashReceiptsForInvoice);
		
		$this->set(compact('order','summedMovements','invoice','cashReceiptsForInvoice','exchangeRateCurrent'));
		
		//if (!empty($invoice)){
		//	$creditDays=$this->Invoice->getCreditDays($invoice['Invoice']['id']);
		//}
		//$this->set(compact('creditDays'));
	}
	
	public function verRemision($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Remisión inválido'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('CashReceipt');
		
		$this->Product->recursive=-1;
		$this->CashReceipt->recursive=-1;
		
		$options = array(
			'conditions' => array('Order.' . $this->Order->primaryKey => $id),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('ThirdParty.id, ThirdParty.company_name')),
			),
		);
		$order=$this->Order->find('first', $options);
		
		
		$cashReceipt=$this->CashReceipt->find('first',array(
			'conditions'=>array(
				'CashReceipt.order_id'=>$id,
			),
			'contain'=>array(
				'AccountingRegisterCashReceipt'=>array(
					'AccountingRegister'=>array(
						'AccountingMovement'=>array(
							'AccountingCode',
						),
					),
				),
				'CashboxAccountingCode',
			)
		));
		
		$summedMovements=$this->StockMovement->find('all',array(
			'fields'=>array('SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, Product.packaging_unit, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'),
			'conditions'=>array(
				'StockMovement.order_id'=>$id,
				'StockMovement.product_quantity >'=>0,
			),
			'group'=>array('Product.id, StockItem.raw_material_id, ProductionResultCode.code'),
		));
		
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName="";
			if (!empty($summedMovements[$i]['StockItem']['raw_material_id'])){
				$linkedRawMaterial=$this->Product->read(null,$summedMovements[$i]['StockItem']['raw_material_id']);
				//pr ($linkedRawMaterial);
				$rawMaterialName=$linkedRawMaterial['Product']['name'];
			}
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		$this->set(compact('order','summedMovements','cashReceipt'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function verPdfRemision($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Remisión inválido'));
		}
		
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('CashReceipt');
		
		$this->Product->recursive=-1;
		$this->CashReceipt->recursive=-1;
		
		$options = array(
			'conditions' => array('Order.' . $this->Order->primaryKey => $id),
			'contain'=>array(
				'ThirdParty'=>array('fields'=>array('ThirdParty.id, ThirdParty.company_name')),
			),
		);
		$order=$this->Order->find('first', $options);
		
		
		$cashReceipt=$this->CashReceipt->find('first',array(
			'conditions'=>array(
				'CashReceipt.order_id'=>$id,
			),
			'contain'=>array(
				'CashboxAccountingCode'
			)
		));
		
		$summedMovements=$this->StockMovement->find('all',array(
			'fields'=>array('SUM(StockMovement.product_quantity) AS total_product_quantity, StockMovement.product_unit_price, Product.name, Product.packaging_unit, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'),
			'conditions'=>array(
				'StockMovement.order_id'=>$id,
				'StockMovement.product_quantity >'=>0,
			),
			'group'=>array('Product.id, StockItem.raw_material_id, ProductionResultCode.code'),
		));
		
		
		for ($i=0;$i<count($summedMovements); $i++){
			$rawMaterialName="";
			if (!empty($summedMovements[$i]['StockItem']['raw_material_id'])){
				$linkedRawMaterial=$this->Product->read(null,$summedMovements[$i]['StockItem']['raw_material_id']);
				//pr ($linkedRawMaterial);
				$rawMaterialName=$linkedRawMaterial['Product']['name'];
			}
			$summedMovements[$i]['StockItem']['raw_material_name']=$rawMaterialName;
		}
		//pr($summedMovements);
		$this->set(compact('order','summedMovements','cashReceipt'));
	}
	
	public function crearEntrada() {
		$this->loadModel('Product');
		$this->loadModel('StockMovement');
		$this->loadModel('ThirdParty');
		$this->loadModel('ClosingDate');
		
		if ($this->request->is('post')) {
			$purchase_date=$this->request->data['Order']['order_date'];
			$purchaseDateAsString=$this->Order->deconstruct('order_date',$this->request->data['Order']['order_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
			
			$previousPurchasesWithThisCode=array();
			$previousPurchasesWithThisCode=$this->Order->find('all',array(
				'conditions'=>array(
					'Order.order_code'=>$this->request->data['Order']['order_code'],
					'Order.stock_movement_type_id'=>MOVEMENT_PURCHASE,
					'Order.third_party_id'=>$this->request->data['Order']['third_party_id'],
				),
			));
			
			if ($purchaseDateAsString>date('Y-m-d H:i')){
				$this->Session->setFlash(__('La fecha de entrada no puede estar en el futuro!  No se guardó la entrada.'), 'default',array('class' => 'error-message'));
			}
			elseif ($purchaseDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			elseif (count($previousPurchasesWithThisCode)>0){
				$this->Session->setFlash(__('Ya se introdujo una entrada con este código!  No se guardó la entrada.'), 'default',array('class' => 'error-message'));
			}
			else {
				$datasource=$this->Order->getDataSource();
				$datasource->begin();
				try {
					$this->Order->create();
					$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_PURCHASE;
					if (!$this->Order->save($this->request->data)) {
						echo "problema guardando la entrada";
						pr($this->validateErrors($this->Order));
						throw new Exception();
					}
					
					// get the relevant information of the purchase that was just saved
					$purchase_id=$this->Order->id;
					$order_code=$this->request->data['Order']['order_code'];
					$provider_id=$this->request->data['Order']['third_party_id'];
					// get the related provider data
					$linkedProvider=$this->ThirdParty->read(null,$provider_id);
					$provider_name=$linkedProvider['ThirdParty']['company_name'];
						
					foreach ($this->request->data['Product'] as $product){
						// four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
						
						// load the product request data into variables
						$product_id = $product['product_id'];
						$product_quantity = $product['product_quantity'];
						$product_price = $product['product_price'];
						
						if ($product_quantity>0 && $product_id>0){
							// calculate the unit price
							$product_unit_price=$product_price/$product_quantity;
							
							// get the related product data
							$linkedProduct=$this->Product->read(null,$product_id);
							$product_name=$linkedProduct['Product']['name'];
							$itemmovementname=$purchase_date['day']."_".$purchase_date['month']."_".$purchase_date['year']."_".$provider_name."_".$order_code."_".$product_name;
							$description="New stockitem ".$product_name." (Quantity:".$product_quantity.",Unit Price:".$product_unit_price.") from Purchase ".$provider_name."_".$order_code;
							
							// STEP 1: SAVE THE STOCK ITEM
							$this->loadModel('StockItem');
							$StockItemData['name']=$itemmovementname;
							$StockItemData['description']=$description;
							$StockItemData['stockitem_creation_date']=$purchase_date;
							$StockItemData['product_id']=$product_id;
							$StockItemData['product_unit_price']=$product_unit_price;
							$StockItemData['original_quantity']=$product_quantity;
							$StockItemData['remaining_quantity']=$product_quantity;
							
							$this->StockItem->clear();
							$this->StockItem->create();
							$logsuccess=$this->StockItem->save($StockItemData);
							if (!$logsuccess) {
								echo "problema guardando el lote";
								pr($this->validateErrors($this->StockItem));
								throw new Exception();
							}
							
							// STEP 2: SAVE THE STOCK MOVEMENT
							$stockitem_id=$this->StockItem->id;
							
							$StockMovementData['movement_date']=$purchase_date;
							$StockMovementData['bool_input']=true;
							$StockMovementData['name']=$itemmovementname;
							$StockMovementData['description']=$description;
							$StockMovementData['order_id']=$purchase_id;
							$StockMovementData['stockitem_id']=$stockitem_id;
							$StockMovementData['product_id']=$product_id;
							$StockMovementData['product_quantity']=$product_quantity;
							$StockMovementData['product_unit_price']=$product_unit_price;
							$StockMovementData['product_total_price']=$product_price;
							
							$this->StockMovement->clear();
							$this->StockMovement->create();
							if (!$this->StockMovement->save($StockMovementData)) {
								echo "problema guardando el movimiento de inventario";
								pr($this->validateErrors($this->StockMovement));
								throw new Exception();
							}
							
							// STEP 3: SAVE THE STOCK ITEM LOG
							$this->loadModel('StockItemLog');
							$stockmovement_id=$this->Order->StockMovement->id;
							
							$StockItemLogData['stockitem_id']=$stockitem_id;
							$StockItemLogData['stock_movement_id']=$stockmovement_id;
							$StockItemLogData['stockitem_date']=$purchase_date;
							$StockItemLogData['product_id']=$product_id;
							$StockItemLogData['product_unit_price']=$product_unit_price;
							$StockItemLogData['product_quantity']=$product_quantity;
							
							$this->StockItemLog->clear();
							$this->StockItemLog->create();
							$logsuccess=$this->StockItemLog->save($StockItemLogData);
							if (!$logsuccess) {
								echo "problema guardando el estado de lote";
								pr($this->validateErrors($this->StockItemLog));
								throw new Exception();
							}
							
							unset($StockItemData);
							unset($StockMovementData);
							unset($StockItemLogData);
							
							// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
							$this->recordUserActivity($this->Session->read('User.username'),$description);
						}
					}
					
					$datasource->commit();
					$this->recordUserAction($this->Order->id,"add",null);
					// SAVE THE USERLOG FOR THE PURCHASE
					$this->recordUserActivity($this->Session->read('User.username'),"Purchase registered with invoice code ".$this->request->data['Order']['order_code']);
					$this->Session->setFlash(__('The purchase has been saved.'),'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'resumenEntradas'));
				//	return $this->redirect(array('action' => 'verEntrada',$purchaseid));
				} 
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('The purchase could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
				}
			}
		}
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions' => array('ThirdParty.bool_provider'=> true)
		));
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		//MODIFIED 20160329 
		//$this->Product->recursive=2;
		//$productsAll = $this->Product->find('all',array('fields'=>'Product.id,Product.name','conditions' => array('ProductType.product_category_id !='=> CATEGORY_PRODUCED)));
		$this->Product->recursive=0;
		$productsAll = $this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array(
				'ProductType.product_category_id !='=> CATEGORY_PRODUCED
			),
		));
		$products = null;
		foreach ($productsAll as $product){
			$products[$product['Product']['id']]=$product['Product']['name'];
		}
		$this->set(compact('thirdParties', 'stockMovementTypes','products'));
		
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
		
	public function crearVenta() {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('Invoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterInvoice');
		
		//$this->loadModel('Invoice');
		$warehouseId=WAREHOUSE_DEFAULT;
		$requestProducts=array();
		if ($this->request->is('post')) {	
			foreach ($this->request->data['Product'] as $product){
				if (!empty($product['product_id'])){
					$requestProducts[]['Product']=$product;
				}
			}
			$warehouseId=$this->request->data['Order']['warehouse_id'];
			if (empty($this->request->data['refresh'])){
				$sale_date=$this->request->data['Order']['order_date'];
				$saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
				$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
				$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
				$closingDate=new DateTime($latestClosingDate);
							
				$saleDateArray=array();
				$saleDateArray['year']=$sale_date['year'];
				$saleDateArray['month']=$sale_date['month'];
				$saleDateArray['day']=$sale_date['day'];
						
				$order_code=$this->request->data['Order']['order_code'];
				$namedSales=$this->Order->find('all',array(
					'conditions'=>array(
						'order_code'=>$order_code,
						'stock_movement_type_id'=>MOVEMENT_SALE,
					)
				));
				if (count($namedSales)>0){
					$this->Session->setFlash(__('Ya existe una venta con el mismo código!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				else if ($saleDateAsString>date('Y-m-d 23:59:59')){
					$this->Session->setFlash(__('La fecha de salida no puede estar en el futuro!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				elseif ($saleDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				elseif ($this->request->data['Order']['third_party_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar el cliente para la venta!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				else if ($this->request->data['Invoice']['bool_annulled']){
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					try {
						//pr($this->request->data);					
						$this->Order->create();
						$OrderData=array();
						$OrderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						$OrderData['Order']['order_date']=$this->request->data['Order']['order_date'];
						$OrderData['Order']['order_code']=$this->request->data['Order']['order_code'];
						$OrderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
						$OrderData['Order']['total_price']=0;
				
						if (!$this->Order->save($OrderData)) {
							echo "Problema guardando el orden de salida";
							pr($this->validateErrors($this->Order));
							throw new Exception();
						}
						
						$order_id=$this->Order->id;
						
						$this->Invoice->create();
						$InvoiceData=array();
						$InvoiceData['Invoice']['order_id']=$order_id;
						$InvoiceData['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
						$InvoiceData['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
						$InvoiceData['Invoice']['bool_annulled']=true;
						$InvoiceData['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
						$InvoiceData['Invoice']['total_price']=0;
						$InvoiceData['Invoice']['currency_id']=CURRENCY_CS;
				
						if (!$this->Invoice->save($InvoiceData)) {
							echo "Problema guardando la factura";
							pr($this->validateErrors($this->Invoice));
							throw new Exception();
						}
						
						$datasource->commit();
						$this->recordUserAction();
						// SAVE THE USERLOG 
						$this->recordUserActivity($this->Session->read('User.username'),"Se registró una venta con número ".$this->request->data['Order']['order_code']);
						$this->Session->setFlash(__('Se guardó la venta.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
						return $this->redirect(array('action' => 'resumenVentasRemisiones'));
						
						//return $this->redirect(array('action' => 'verVenta',$order_id));
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
					}
				}					
				else if (!$this->request->data['Invoice']['bool_credit']&&$this->request->data['Invoice']['cashbox_accounting_code_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una factura de contado!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				else if ($this->request->data['Invoice']['bool_retention']&&strlen($this->request->data['Invoice']['retention_number'])==0){
					$this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}	
				else {
					// before moving into the selling part, perform the check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
					$saleItemsOK=true;
					$exceedingItems="";
					$productCount=0;
					$products=array();
					foreach ($this->request->data['Product'] as $product){
						//pr($product);
						// keep track of number of rows so that in case of an error jquery displays correct number of rows again
						if ($product['product_id']>0){
							$productCount++;
						}
						// only process lines where product_quantity and product id have been filled out
						if ($product['product_quantity']>0 && $product['product_id']>0){
							$products[]=$product;
							$quantityEntered=$product['product_quantity'];
							$productid = $product['product_id'];
							$productionresultcodeid = $product['production_result_code_id'];
							$rawmaterialid = $product['raw_material_id'];
							
							if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
								$stockItemConditions=array(
									'StockItem.product_id'=> $productid,
									'StockItem.production_result_code_id'=> $productionresultcodeid,
									'StockItem.raw_material_id'=> $rawmaterialid,
									'StockItem.stockitem_creation_date <'=> $saleDateAsString,
								);
								if (!empty($warehouseId)){
									$stockItemConditions[]=array('StockItem.warehouse_id'=>$warehouseId);
								}
								//echo "productid is".$productid." productionresultcodeid is ".$productionresultcodeid." rawmaterialid is ".$rawmaterialid; 
								$remainingStockForProduct = $this->StockItem->find('all',array(
									'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
									'conditions' => $stockItemConditions,
									'group' => array('Product.name')
								));
							}
							else {
								$stockItemConditions=array(
									'StockItem.product_id'=> $productid,
									'StockItem.stockitem_creation_date <'=> $saleDateAsString,
								);
								if (!empty($warehouseId)){
									$stockItemConditions[]=array('StockItem.warehouse_id'=>$warehouseId);
								}
								$remainingStockForProduct = $this->StockItem->find('all',
									array(
										'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
										'conditions' => $stockItemConditions,
										'group' => array('Product.name')
									)
								);
							}
											
							//pr($soldProducts);
							$quantityInStock=0;
							if (!empty($remainingStockForProduct)){
								$quantityInStock=$remainingStockForProduct[0][0]['remaining'];
							}
							
							//compare the quantity requested and the quantity in stock
							if ($quantityEntered>$quantityInStock){
								$saleItemsOK=false;
								$exceedingItems.=__("Para producto ".$remainingStockForProduct[0]['Product']['name']." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
							}
						}
					}
					if ($exceedingItems!=""){
						$exceedingItems.=__("Please correct and try again!");
					}					
					if (!$saleItemsOK){
						$this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',array('class' => 'error-message'));
					}
					else{
						$datasource=$this->Order->getDataSource();
						$datasource->begin();
						try {
							$currency_id=$this->request->data['Invoice']['currency_id'];
						
							$retention_invoice=$this->request->data['Invoice']['retention_amount'];
							$sub_total_invoice=$this->request->data['Invoice']['sub_total_price'];
							$IVA_invoice=$this->request->data['Invoice']['IVA_price'];
							$total_invoice=$this->request->data['Invoice']['total_price'];
					
							// if all products are in stock, proceed with the sale 
							$this->Order->create();
							$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
							// ORDER TOTAL PRICE SHOULD ALWAYS BE IN C$
							if ($currency_id==CURRENCY_USD){
								$this->request->data['Order']['total_price']=$sub_total_invoice*$this->request->data['Order']['exchange_rate'];
							}
							else {
								$this->request->data['Order']['total_price']=$sub_total_invoice;
							}
						
							if (!$this->Order->save($this->request->data)) {
								echo "Problema guardando la salida";
								pr($this->validateErrors($this->Order));
								throw new Exception();
							}
						
							$order_id=$this->Order->id;
							$order_code=$this->request->data['Order']['order_code'];
						
							$this->Invoice->create();
							$this->request->data['Invoice']['order_id']=$order_id;
							$this->request->data['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
							$this->request->data['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
							$this->request->data['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
							if ($this->request->data['Invoice']['bool_credit']){
								$this->request->data['Invoice']['bool_retention']=false;
								$this->request->data['Invoice']['retention_amount']=0;
								$this->request->data['Invoice']['retention_number']="";
							}
							else {
								$this->request->data['Invoice']['bool_paid']=true;
							}
					
							if (!$this->Invoice->save($this->request->data)) {
								echo "Problema guardando la factura";
								pr($this->validateErrors($this->Invoice));
								throw new Exception();
							}
							
							$invoice_id=$this->Invoice->id;
							
							// now prepare the accounting registers
							
							// if the invoice is with credit, save one accounting register; 
							// debit=cuentas por cobrar clientes 101-004-001, credit = ingresos por venta 401, amount = subtotal
							
							// if the invoice is paid with cash, save two or three accounting register; 
							// debit=caja selected by client, credit = ingresos por venta 401, amount = total
							// debit=?, credit = ?, amount = iva
							// if bool_retention is true
							// debit=?, credit = ?, amount = retention
							
							if ($currency_id==CURRENCY_USD){
								$this->loadModel('ExchangeRate');
								$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
								//pr($applicableExchangeRate);
								$retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
								$sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
								$IVA_CS=round($IVA_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
								$total_CS=round($total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
							}
							else {
								$retention_CS=$retention_invoice;
								$sub_total_CS=$sub_total_invoice;
								$IVA_CS=$IVA_invoice;
								$total_CS=$total_invoice;
							}
							
							if ($this->request->data['Invoice']['bool_credit']){
								$client_id=$this->request->data['Order']['third_party_id'];
								$this->loadModel('ThirdParty');
								$thisClient=$this->ThirdParty->read(null,$client_id);
							
								$accountingRegisterData['AccountingRegister']['concept']="Venta ".$order_code;
								$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
								$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
								$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
								$accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
								$accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
								$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
								
								if (empty($thisClient['ThirdParty']['accounting_code_id'])){
									$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
									$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES);
								}
								else {								
									$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
									$accountingCode=$this->AccountingCode->read(null,$thisClient['ThirdParty']['accounting_code_id']);
								}
								$accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$order_code;
								$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
								
								$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
								$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
								$accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$order_code;
								$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
								
								if ($this->request->data['Invoice']['bool_IVA']){
									$accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
									$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
									$accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$order_code;
									$accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
								}
								
								//pr($accountingRegisterData);
								$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
								$this->recordUserAction($this->AccountingRegister->id,"add",null);
						
								$AccountingRegisterInvoiceData=array();
								$AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
								$AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
								$this->AccountingRegisterInvoice->create();
								if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
									pr($this->validateErrors($this->AccountingRegisterInvoice));
									echo "problema al guardar el lazo entre asiento contable y factura";
									throw new Exception();
								}
								//echo "link accounting register sale saved<br/>";					
							}
							else {
								$accountingRegisterData['AccountingRegister']['concept']="Venta ".$order_code;
								$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
								$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
								$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
								$accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
								$accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
								$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
								
								if (!$this->request->data['Invoice']['bool_retention']){
									$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
									$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
									$accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$order_code;
									$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
								}
								else {
									// with retention
									$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
									$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
									$accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$order_code;
									$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS-$retention_CS;
								}
								
								$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
								$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
								$accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$order_code;
								$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
								$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
								
								if ($this->request->data['Invoice']['bool_IVA']){
									$accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
									$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
									$accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$order_code;
									$accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
								}
								if ($this->request->data['Invoice']['bool_retention']){
									$accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
									$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_RETENCIONES_POR_COBRAR);
									$accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$order_code;
									$accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
								}
								
								//pr($accountingRegisterData);
								$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
								$this->recordUserAction($this->AccountingRegister->id,"add",null);
								//echo "accounting register saved for cuentas cobrar clientes<br/>";
						
								$AccountingRegisterInvoiceData=array();
								$AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
								$AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
								$this->AccountingRegisterInvoice->create();
								if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
									pr($this->validateErrors($this->AccountingRegisterInvoice));
									echo "problema al guardar el lazo entre asiento contable y factura";
									throw new Exception();
								}
								//echo "link accounting register sale saved<br/>";	
							}
						
						
							foreach ($products as $product){
								// four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
								//pr($product);
								
								// load the product request data into variables
								$product_id = $product['product_id'];
								$product_category_id = $this->Product->getProductCategoryId($product_id);
								$production_result_code_id =0;
								$raw_material_id=0;
								
								if ($product_category_id==CATEGORY_PRODUCED){
									$production_result_code_id = $product['production_result_code_id'];
									$raw_material_id = $product['raw_material_id'];
								}
								
								$product_unit_price=$product['product_unit_price'];
								$product_quantity = $product['product_quantity'];
								
								if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
									$product_unit_price*=$this->request->data['Order']['exchange_rate'];
								}
								
								// get the related product data
								$linkedProduct=$this->Product->read(null,$product_id);
								$product_name=$linkedProduct['Product']['name'];
								
								// STEP 1: SAVE THE STOCK ITEM(S)
								// first prepare the materials that will be taken out of stock
								
								if ($product_category_id==CATEGORY_PRODUCED){
									$usedMaterials= $this->StockItem->getFinishedMaterialsForSale($product_id,$production_result_code_id,$product_quantity,$raw_material_id,$saleDateAsString);		
								}
								else {
									$usedMaterials= $this->StockItem->getOtherMaterialsForSale($product_id,$product_quantity,$saleDateAsString);		
								}
								//pr($usedMaterials);

								for ($k=0;$k<count($usedMaterials);$k++){
									$materialUsed=$usedMaterials[$k];
									$stockitem_id=$materialUsed['id'];
									$quantity_present=$materialUsed['quantity_present'];
									$quantity_used=$materialUsed['quantity_used'];
									$quantity_remaining=$materialUsed['quantity_remaining'];
									if (!$this->StockItem->exists($stockitem_id)) {
										throw new NotFoundException(__('Invalid StockItem'));
									}
									$linkedStockItem=$this->StockItem->read(null,$stockitem_id);
									$message="Se vendió lote ".$product_name." (Cantidad:".$quantity_used.") para Venta ".$order_code;
									
									$StockItemData=array();
									$StockItemData['id']=$stockitem_id;
									//$StockItemData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$order_code."_".$product_name;
									$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
									$StockItemData['remaining_quantity']=$quantity_remaining;
									// notice that no new stockitem is created because we are taking from an already existing one
									if (!$this->StockItem->save($StockItemData)) {
										echo "problema al guardar el lote";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									
									// STEP 2: SAVE THE STOCK MOVEMENT
									$message="Se vendió ".$product_name." (Cantidad:".$quantity_used.", total para venta:".$product_quantity.") para Venta ".$order_code;
									$StockMovementData=array();
									$StockMovementData['movement_date']=$sale_date;
									$StockMovementData['bool_input']=false;
									$StockMovementData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$order_code."_".$product_name;
									$StockMovementData['description']=$message;
									$StockMovementData['order_id']=$order_id;
									$StockMovementData['stockitem_id']=$stockitem_id;
									$StockMovementData['product_id']=$product_id;
									$StockMovementData['product_quantity']=$quantity_used;
									$StockMovementData['product_unit_price']=$product_unit_price;
									$StockMovementData['product_total_price']=$product_unit_price*$quantity_used;
									$StockMovementData['production_result_code_id']=$production_result_code_id;
									
									$this->StockMovement->create();
									if (!$this->StockMovement->save($StockMovementData)) {
										echo "problema al guardar el movimiento de lote";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
								
									// STEP 3: SAVE THE STOCK ITEM LOG
									$this->recreateStockItemLogs($stockitem_id);
											
									// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
									$this->recordUserActivity($this->Session->read('User.username'),$message);
								}
							}
											
							$datasource->commit();
							$this->recordUserAction($this->Order->id,"add",null);
							// SAVE THE USERLOG FOR THE PURCHASE
							$this->recordUserActivity($this->Session->read('User.username'),"Sale registered with invoice code ".$this->request->data['Order']['order_code']);
							$this->Session->setFlash(__('Se guardó la venta.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
							return $this->redirect(array('action' => 'resumenVentasRemisiones'));
							// on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
							//return $this->redirect(array('action' => 'verVenta',$order_id));
						}
						catch(Exception $e){
							$datasource->rollback();
							pr($e);
							$this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
						}
					}
				}
			
			}
		}
		
		$this->set(compact('warehouseId'));
		$this->set(compact('requestProducts'));
		
		$this->Product->recursive=-1;
		$this->ProductType->recursive=-1;
		
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions' => array(
				'ThirdParty.bool_provider'=> false,
				'ThirdParty.bool_active'=>true,
			),
			'order'=>'ThirdParty.company_name',			
		));
		
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		
		// Remove the raw products from the dropdown list
		// 20170419 RESTRICT STOCKITEMS DIRECTLY TO LIMIT RETRIEVED INFO
		$productsAll = $this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'contain'=>array(
				'ProductType',
				'StockItem'=>array(
					'fields'=> array('remaining_quantity','raw_material_id','warehouse_id'),
					'conditions'=>array(
						'StockItem.remaining_quantity >'=>0,
					),
				)
			),
			'order'=>'product_type_id DESC, name ASC',
			//'conditions' => array(
			//	'ProductType.product_category_id !='=> CATEGORY_RAW,
			//),
		));
		
		$products = array();
		$rawmaterialids=array();
		foreach ($productsAll as $product){
			// only show products that are in inventory at the current date
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
					// in this case the associative array just contains the product_id because otherwise the list would become very long
					// 20170419 CHECK REMOVED BECAUSE ALREADY PRESENT IN CONDITIONS OF LOOKUP
					//if ($stockitem['remaining_quantity']>0){
						if (!empty($warehouseId)){
							if ($stockitem['warehouse_id']==$warehouseId){
								$products[$product['Product']['id']]=$product['Product']['name'];
								$rawmaterialids[]=$stockitem['raw_material_id'];
							}
						}
						else {
							$products[$product['Product']['id']]=$product['Product']['name'];
							$rawmaterialids[]=$stockitem['raw_material_id'];
						}		
					//}
				}
			}
		}
		
		$this->loadModel('ProductionResultCode');
		$productionResultCodes=$this->ProductionResultCode->find('list');
		$preformasAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('remaining_quantity'),
					)
				),
				'conditions' => array(
					'ProductType.product_category_id ='=> CATEGORY_RAW,
					'Product.id'=>$rawmaterialids
				)
			)
		);
		//pr($preformasAll);
		$rawMaterials=array();
		foreach ($preformasAll as $preforma){
			//pr($preforma);
			// only show products that are in inventory
			//if ($preforma['StockItem']!=null){
				//if ($preforma['StockItem'][0]['remaining_quantity']>0){					
					// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
					// in this case the associative array just contains the product_id because otherwise the list would become very long
					$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
				//}
			//}
		}
		
		$productcategoryid=CATEGORY_PRODUCED;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$finishedMaterialsInventory =array();
		$finishedMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids,$warehouseId);
		
		//pr($finishedMaterialsInventory);
		$productcategoryid=CATEGORY_OTHER;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$otherMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids,$warehouseId);
		
		$productcategoryid=CATEGORY_RAW;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids,$warehouseId);
		//$rawMaterialsInventory = $this->StockItem->getInventoryTotals($categoryids,$producttypeids);
		//$finishedMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_PRODUCED);
		//$otherMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_OTHER);
		
		$currencies = $this->Currency->find('list');
		
		//$accountingCodes = $this->AccountingCode->find('list',array('fields'=>array('AccountingCode.id','AccountingCode.shortfullname')));
		$this->AccountingCode->recursive=-1;
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			)
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			)
		));
		// calculate the code for the new service sheet
		$newInvoiceCode="";
		/*
		$lastInvoice = $this->Invoice->find('first',array(
			'order' => array('Invoice.invoice_code' => 'desc'),
		));
		if ($lastInvoice!= null){
			$newInvoiceCode = intval($lastInvoice['Invoice']['order_code'])+1;
			$newInvoiceCode=str_pad($newInvoiceCode,5,'0',STR_PAD_LEFT);
		}
		else {
			$newInvoiceCode="00001";
		}
		*/
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','rawMaterials','currencies','accountingCodes','newInvoiceCode'));
		
		$this->loadModel('ExchangeRate');
		$orderDate=date( "Y-m-d");
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($orderDate);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
		$this->Invoice->recursive=-1;
		$lastInvoice = $this->Invoice->find('first',array(
			'fields'=>array('Invoice.invoice_code'),
			'order' => array('Invoice.invoice_code' => 'desc'),
		));
		//pr($lastInvoice);
		if ($lastInvoice!= null){
			$newInvoiceCode = $lastInvoice['Invoice']['invoice_code']+1;
		}
		else {
			$newInvoiceCode="1";
		}
		$this->set(compact('newInvoiceCode'));
		
		$this->loadModel('Warehouse');
		$warehouses=$this->Warehouse->find('list',array(
			'order'=>'Warehouse.name',
		));
		$this->set(compact('warehouses'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}
	
	public function crearRemision() {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('CashReceiptType');
		
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$productCount=1;
		if ($this->request->is('post')) {	
			$remission_date=$this->request->data['Order']['order_date'];
			$remissionDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
						
			$remissionDateArray=array();
			$remissionDateArray['year']=$remission_date['year'];
			$remissionDateArray['month']=$remission_date['month'];
			$remissionDateArray['day']=$remission_date['day'];
					
			$order_code=$this->request->data['Order']['order_code'];
			$namedRemissions=$this->Order->find('all',array(
				'conditions'=>array(
					'order_code'=>$order_code,
					'stock_movement_type_id'=>MOVEMENT_SALE,
				)
			));
			if (count($namedRemissions)>0){
				$this->Session->setFlash(__('Ya existe una remisión con el mismo código!  No se guardó la remisión.'), 'default',array('class' => 'error-message'));
			}
			else if ($remissionDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de remisión no puede estar en el futuro!  No se guardó la remisión.'), 'default',array('class' => 'error-message'));
			}
			elseif ($remissionDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			elseif ($this->request->data['Order']['third_party_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar el cliente para la remisión!  No se guardó la remisión.'), 'default',array('class' => 'error-message'));
			}
			else if ($this->request->data['CashReceipt']['bool_annulled']){
				$datasource=$this->Order->getDataSource();
				try {
					//pr($this->request->data);
					$datasource->begin();
					
					$this->Order->create();
					$OrderData=array();
					$OrderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
					$OrderData['Order']['order_date']=$this->request->data['Order']['order_date'];
					$OrderData['Order']['order_code']=$this->request->data['Order']['order_code'];
					$OrderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
					$OrderData['Order']['total_price']=0;
			
					if (!$this->Order->save($OrderData)) {
						echo "Problema guardando el orden de salida";
						pr($this->validateErrors($this->Order));
						throw new Exception();
					}
					
					$order_id=$this->Order->id;
					
					$this->CashReceipt->create();
					$CashReceiptData=array();
					$CashReceiptData['CashReceipt']['order_id']=$order_id;
					$CashReceiptData['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
					$CashReceiptData['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
					$CashReceiptData['CashReceipt']['bool_annulled']=true;
					$CashReceiptData['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
					$CashReceiptData['CashReceipt']['concept']=$this->request->data['CashReceipt']['concept'];
					$CashReceiptData['CashReceipt']['observation']=$this->request->data['CashReceipt']['observation'];
					$CashReceiptData['CashReceipt']['cash_receipt_type_id']=CASH_RECEIPT_TYPE_REMISSION;
					$CashReceiptData['CashReceipt']['amount']=0;
					$CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
			
					if (!$this->CashReceipt->save($CashReceiptData)) {
						echo "Problema guardando el recibo de caja";
						pr($this->validateErrors($this->CashReceipt));
						throw new Exception();
					}
					
					$datasource->commit();
						
					// SAVE THE USERLOG 
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró una remisión con número ".$this->request->data['Order']['order_code']);
					$this->Session->setFlash(__('Se guardó la remisión.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'resumenVentasRemisiones'));
					// on the view page the print button will be present; tt should display the invoice just as it has been made out, this is then sent to javascript
					//return $this->redirect(array('action' => 'verVenta',$order_id));
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar la remisión.  Por favor vuelva a intentar.'), 'default',array('class' => 'error-message'));
				}
			}					
			else if ($this->request->data['CashReceipt']['cashbox_accounting_code_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una remisión!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
			}
			else {
				// before moving into the selling part, perform the check if all materials that are selected, when summed up, do not exceed the quantity present in inventory
				$saleItemsOK=true;
				$exceedingItems="";
				$productCount=0;
				$products=array();
				foreach ($this->request->data['Product'] as $product){
					//pr($product);
					// keep track of number of rows so that in case of an error jquery displays correct number of rows again
					if ($product['product_id']>0){
						$productCount++;
					}
					// only process lines where product_quantity and product id have been filled out
					if ($product['product_quantity']>0 && $product['product_id']>0){
						$products[]=$product;
						$quantityEntered=$product['product_quantity'];
						$productid = $product['product_id'];
						$productionresultcodeid = $product['production_result_code_id'];
						$rawmaterialid = $product['raw_material_id'];
						
						if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
							//echo "productid is".$productid." productionresultcodeid is ".$productionresultcodeid." rawmaterialid is ".$rawmaterialid; 
							$remainingStockForProduct = $this->StockItem->find('all',array(
								'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
								'conditions' => array(
									'StockItem.product_id'=> $productid,
									'StockItem.production_result_code_id'=> $productionresultcodeid,
									'StockItem.raw_material_id'=> $rawmaterialid,
									'StockItem.stockitem_creation_date <'=> $remissionDateAsString,
								),
								'group' => array('Product.name')
							));
						}
						/*
						else {
							$remainingStockForProduct = $this->StockItem->find('all',
								array(
									'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
									'conditions' => array(
										'StockItem.product_id'=> $productid,
										'StockItem.stockitem_creation_date <'=> $remissionDateAsString,
									),
									'group' => array('Product.name')
								)
							);
						}
						*/			
						//pr($soldProducts);
						$quantityInStock=0;
						if (!empty($remainingStockForProduct)){
							$quantityInStock=$remainingStockForProduct[0][0]['remaining'];
						}
						
						//compare the quantity requested and the quantity in stock
						if ($quantityEntered>$quantityInStock){
							$saleItemsOK=false;
							$exceedingItems.=__("Para producto ".$remainingStockForProduct[0][0]['Product']['name']." la cantidad requerida (".$quantityEntered.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
						}
					}
				}
				if ($exceedingItems!=""){
					$exceedingItems.=__("Please correct and try again!");
				}					
				if (!$saleItemsOK){
					$this->Session->setFlash(__('La cantidad de bodega para los siguientes productos no es suficientes.')."<br/>".$exceedingItems, 'default',array('class' => 'error-message'));
				}
				else{
					$datasource=$this->Order->getDataSource();
					try {
						$datasource->begin();
				
						$currency_id=$this->request->data['CashReceipt']['currency_id'];
						$total_cash_receipt=$this->request->data['CashReceipt']['amount'];
				
						// if all products are in stock, proceed with the sale 
						$this->Order->create();
						$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						$this->request->data['Order']['total_price']=$total_cash_receipt;
						
						if ($currency_id==CURRENCY_USD){
							$this->request->data['Order']['total_price']=$total_cash_receipt*$this->request->data['Order']['exchange_rate'];
						}
					
						if (!$this->Order->save($this->request->data)) {
							echo "Problema guardando la salida";
							pr($this->validateErrors($this->Order));
							throw new Exception();
						}
					
						$order_id=$this->Order->id;
						$order_code=$this->request->data['Order']['order_code'];
					
						$this->CashReceipt->create();
						$this->request->data['CashReceipt']['order_id']=$order_id;
						$this->request->data['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
						$this->request->data['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
						$this->request->data['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
				
						if (!$this->CashReceipt->save($this->request->data)) {
							echo "Problema guardando la factura";
							pr($this->validateErrors($this->CashReceipt));
							throw new Exception();
						}
						$cash_receipt_id=$this->CashReceipt->id;
						// now prepare the accounting registers
						// if the invoice is paid with cash, save two or three accounting register; 
						// debit=caja selected by client, credit = ingresos por venta 401, amount = total
						if ($currency_id==CURRENCY_USD){
							$this->loadModel('ExchangeRate');
							$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($remissionDateAsString);
							//pr($applicableExchangeRate);
							$total_CS=round($total_cash_receipt*$applicableExchangeRate['ExchangeRate']['rate'],2);
						}
						else {
							$total_CS=$total_cash_receipt;
						}
						
						$accountingRegisterData['AccountingRegister']['concept']="Remisión Orden".$order_code;
						$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
						$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
						$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;		
						$accountingRegisterData['AccountingRegister']['register_date']=$remissionDateArray;
						$accountingRegisterData['AccountingRegister']['amount']=$total_CS;
						$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
						
						$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['CashReceipt']['cashbox_accounting_code_id'];
						$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['cashbox_accounting_code_id']);
						$accountingRegisterData['AccountingMovement'][0]['concept']="Remisión ".$order_code;
						$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
						$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$total_CS;
						
						$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
						$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
						$accountingRegisterData['AccountingMovement'][1]['concept']="Remisión Orden".$order_code;
						$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
						$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$total_CS;
						
						//pr($accountingRegisterData);
						$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
						$this->recordUserAction($this->AccountingRegister->id,"add",null);
						//echo "accounting register saved for cuentas cobrar clientes<br/>";
				
						$AccountingRegisterCashReceiptData=array();
						$AccountingRegisterCashReceiptData['accounting_register_id']=$accounting_register_id;
						$AccountingRegisterCashReceiptData['cash_receipt_id']=$cash_receipt_id;
						$this->AccountingRegisterCashReceipt->create();
						if (!$this->AccountingRegisterCashReceipt->save($AccountingRegisterCashReceiptData)) {
							pr($this->validateErrors($this->AccountingRegisterCashReceipt));
							echo "problema al guardar el lazo entre asiento contable y recibo de caja";
							throw new Exception();
						}
						//echo "link accounting register cash receipt saved<br/>";					
						
					
						foreach ($products as $product){
							// four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
							//pr($product);
							
							// load the product request data into variables
							$product_id = $product['product_id'];
							$product_category_id = $this->Product->getProductCategoryId($product_id);
							$production_result_code_id =0;
							$raw_material_id=0;
							
							if ($product_category_id==CATEGORY_PRODUCED){
								$production_result_code_id = $product['production_result_code_id'];
								$raw_material_id = $product['raw_material_id'];
							}
							
							$product_unit_price=$product['product_unit_price'];
							$product_quantity = $product['product_quantity'];
							
							if ($currency_id==CURRENCY_USD){
								$product_unit_price*=$this->request->data['Order']['exchange_rate'];
							}
							
							// get the related product data
							$linkedProduct=$this->Product->read(null,$product_id);
							$product_name=$linkedProduct['Product']['name'];
							
							// STEP 1: SAVE THE STOCK ITEM(S)
							// first prepare the materials that will be taken out of stock
							
							if ($product_category_id==CATEGORY_PRODUCED){
								$usedMaterials= $this->StockItem->getFinishedMaterialsForSale($product_id,$production_result_code_id,$product_quantity,$raw_material_id,$remissionDateAsString);		
							}
							else {
								$usedMaterials= $this->StockItem->getOtherMaterialsForSale($product_id,$product_quantity,$remissionDateAsString);		
							}
							//pr($usedMaterials);

							for ($k=0;$k<count($usedMaterials);$k++){
								$materialUsed=$usedMaterials[$k];
								$stockitem_id=$materialUsed['id'];
								$quantity_present=$materialUsed['quantity_present'];
								$quantity_used=$materialUsed['quantity_used'];
								$quantity_remaining=$materialUsed['quantity_remaining'];
								if (!$this->StockItem->exists($stockitem_id)) {
									throw new NotFoundException(__('Invalid StockItem'));
								}
								$linkedStockItem=$this->StockItem->read(null,$stockitem_id);
								$message="Se vendió lote ".$product_name." (Cantidad:".$quantity_used.") para Venta ".$order_code;
								
								$StockItemData['id']=$stockitem_id;
								//$StockItemData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$order_code."_".$product_name;
								$StockItemData['description']=$linkedStockItem['StockItem']['description']."|".$message;
								$StockItemData['remaining_quantity']=$quantity_remaining;
								
								$this->StockItem->clear();
								// notice that no new stockitem is created because we are taking from an already existing one
								$logsuccess=$this->StockItem->save($StockItemData);
								if (!$logsuccess) {
									echo "problema al guardar el lote";
									pr($this->validateErrors($this->StockItem));
									throw new Exception();
								}
								
								// STEP 2: SAVE THE STOCK MOVEMENT
								$message="Se remitió ".$product_name." (Cantidad:".$quantity_used.", total para remisión:".$product_quantity.") para Remisión ".$order_code;
								$StockMovementData['movement_date']=$remission_date;
								$StockMovementData['bool_input']=false;
								$StockMovementData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$order_code."_".$product_name;
								$StockMovementData['description']=$message;
								$StockMovementData['order_id']=$order_id;
								$StockMovementData['stockitem_id']=$stockitem_id;
								$StockMovementData['product_id']=$product_id;
								$StockMovementData['product_quantity']=$quantity_used;
								$StockMovementData['product_unit_price']=$product_unit_price;
								$StockMovementData['product_total_price']=$product_unit_price*$quantity_used;
								$StockMovementData['production_result_code_id']=$production_result_code_id;
								
								$this->StockMovement->clear();
								$this->StockMovement->create();
								$logsuccess=$this->StockMovement->save($StockMovementData);
								if (!$logsuccess) {
									echo "problema al guardar el movimiento de lote";
									pr($this->validateErrors($this->StockMovement));
									throw new Exception();
								}
							
								// STEP 3: SAVE THE STOCK ITEM LOG
								$this->recreateStockItemLogs($stockitem_id);
								unset($StockItemData);
								unset($StockMovementData);
								
								// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
								$this->recordUserActivity($this->Session->read('User.username'),$message);
							}
						}
						$datasource->commit();
						$this->recordUserAction($this->Order->id,"add",null);
						// SAVE THE USERLOG FOR THE REMISSION
						$this->recordUserActivity($this->Session->read('User.username'),"Remisión registrada con número ".$this->request->data['Order']['order_code']);
						$this->Session->setFlash(__('Se guardó la remisión.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
						return $this->redirect(array('action' => 'resumenVentasRemisiones'));
						// on the view page the print button will be present; it should display the invoice just as it has been made out, this is then sent to javascript
						//return $this->redirect(array('action' => 'verVenta',$order_id));
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
					}
				}
			}
		}
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions' => array(
				'ThirdParty.bool_provider'=> false,
				'ThirdParty.bool_active'=>true,
			),
			'order'=>'ThirdParty.company_name',			
		));
		
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		$this->Product->recursive=2;
		// Remove the raw products from the dropdown list
		$productsAll = $this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'contain'=>array(
				'ProductType',
				'StockItem'=>array(
					'fields'=> array('remaining_quantity','raw_material_id')
				)
			),
			'order'=>'product_type_id DESC, name ASC',
			'conditions' => array(
				'ProductType.product_category_id'=> CATEGORY_PRODUCED,
			)
		));
		
		$products = array();
		$rawmaterialids=array();
		foreach ($productsAll as $product){
			// only show products that are in inventory
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0){
						// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
						// in this case the associative array just contains the product_id because otherwise the list would become very long
						$products[$product['Product']['id']]=$product['Product']['name'];
						$rawmaterialids[]=$stockitem['raw_material_id'];
					}
				}
			}
		}
		
		$this->loadModel('ProductionResultCode');
		$productionResultCodes=$this->ProductionResultCode->find('list',array('conditions'=>array('id !='=>PRODUCTION_RESULT_CODE_A)));
		$preformasAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('remaining_quantity'),
					)
				),
				'conditions' => array(
					'ProductType.product_category_id ='=> CATEGORY_RAW,
					'Product.id'=>$rawmaterialids
				)
			)
		);
		//pr($preformasAll);
		$rawMaterials=array();
		foreach ($preformasAll as $preforma){
			//pr($preforma);
			// only show products that are in inventory
			//if ($preforma['StockItem']!=null){
				//if ($preforma['StockItem'][0]['remaining_quantity']>0){					
					// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
					// in this case the associative array just contains the product_id because otherwise the list would become very long
					$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
				//}
			//}
		}
		
		$this->ProductType->recursive=0;
		$productcategoryid=CATEGORY_PRODUCED;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$finishedMaterialsInventory =array();
		$finishedMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$productcategoryid=CATEGORY_OTHER;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$otherMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$productcategoryid=CATEGORY_RAW;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		//$rawMaterialsInventory = $this->StockItem->getInventoryTotals($categoryids,$producttypeids);
		//$finishedMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_PRODUCED);
		//$otherMaterialsInventory = $this->StockItem->getInventoryTotals(CATEGORY_OTHER);
		
		$currencies = $this->Currency->find('list');
		
		//$accountingCodes = $this->AccountingCode->find('list',array('fields'=>array('AccountingCode.id','AccountingCode.shortfullname')));
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			)
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			)
		));
		
		
		$cashReceiptTypes = $this->CashReceiptType->find('list');
		
		// calculate the code for the new service sheet
		$newCashReceiptCode="";
		/*$allCashReceipts = $this->CashReceipt->find('all',array(
			'fields'=>array('receipt_code'),
			'order' => array('CashReceipt.receipt_code' => 'desc'),
		));
		pr($allCashReceipts);
		*/
		$lastCashReceipt = $this->CashReceipt->find('first',array(
			'fields'=>array('receipt_code'),
			'order' => array('CAST(SUBSTR(CashReceipt.receipt_code,4,5) AS DEC)' => 'desc'),
		));
		//CAST(SUBSTR(receipt_code,4,5) AS DEC) DESC
		//SELECT * FROM `cash_receipts` WHERE 1 ORDER BY CAST(SUBSTR(receipt_code,4,5) AS DEC) DESC 
		//pr($lastCashReceipt);
		if ($lastCashReceipt!= null){
			$newCashReceiptCode = intval(substr($lastCashReceipt['CashReceipt']['receipt_code'],4))+1;
			$newCashReceiptCode="R/C ".$newCashReceiptCode;
		}
		else {
			$newCashReceiptCode="R/C 00001";
		}
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','rawMaterials','currencies','accountingCodes','newCashReceiptCode','cashReceiptTypes'));
		
		$this->loadModel('ExchangeRate');
		$orderDate=date( "Y-m-d");
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($orderDate);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
		$this->loadModel('Warehouse');
		$warehouses=$this->Warehouse->find('list',array(
			'order'=>'Warehouse.name',
		));
		$this->set(compact('warehouses'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}
	
	public function editarEntrada($id = null) {
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
		
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		$this->loadModel('Product');
		$this->loadModel('StockItemLog');
		$this->loadModel('ThirdParty');
		$this->loadModel('ClosingDate');
		$this->loadModel('ProductionMovement');
		$this->loadModel('ProductionRun');
		
		$productsPurchasedEarlier=array();
		$productsPurchasedEarlier = $this->Order->StockMovement->find('all',array(
			'fields'=>'StockMovement.product_id,Product.name,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.product_total_price,StockMovement.id',
			'conditions' => array(
				'StockMovement.order_id'=> $id,
				'StockMovement.product_quantity >'=> 0,
				'StockMovement.product_id >'=> 0,
			)
		));
		
		$boolEditable=true;
		
		if ($this->request->is(array('post', 'put'))) {
			$boolEditable=$this->request->data['Order']['bool_editable'];
			$reasonForNonEditable=$this->request->data['Order']['reason_for_non_editable'];
			
			$purchase_date=$this->request->data['Order']['order_date'];
			$purchaseDateAsString=$this->Order->deconstruct('order_date',$this->request->data['Order']['order_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
			
			$previousPurchasesWithThisCode=array();
			$previousPurchase=$this->Order->read(null,$id);
			if ($previousPurchase['Order']['order_code']!=$this->request->data['Order']['order_code']){
				$previousPurchasesWithThisCode=$this->Order->find('all',array(
					'conditions'=>array(
						'Order.order_code'=>$this->request->data['Order']['order_code'],
						'Order.stock_movement_type_id'=>MOVEMENT_PURCHASE,
						'Order.third_party_id'=>$this->request->data['Order']['third_party_id'],
					),
				));
			}
			
			// check purchase date is not in future
			if ($purchaseDateAsString>date('Y-m-d H:i')){
				$this->Session->setFlash(__('La fecha de entrada no puede estar en el futuro!  No se guardó la entrada.'), 'default',array('class' => 'error-message'));
			}
			// check if purchase date is not before closing date
			elseif ($purchaseDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			elseif (count($previousPurchasesWithThisCode)>0){
				$this->Session->setFlash(__('Ya se introdujo una entrada con este código!  No se guardó la entrada.'), 'default',array('class' => 'error-message'));
			}
			else {
				if ($boolEditable){
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					try {
						if (!$this->Order->save($this->request->data)) {
							echo "problema al guardar la entrada";
							pr($this->validateErrors($this->Order));
							throw new Exception();
						}
						
						$datasource->commit();
						$this->recordUserAction();
						$this->recordUserActivity($this->Session->read('User.username'),"Se editó entrada número ".$this->request->data['Order']['order_code']);
						
						$this->Session->setFlash(__('Se guardó la entrada.'),'default',array('class' => 'success'));
						return $this->redirect(array('action' => 'index'));
					}
					catch(Exception $e){
						$this->Session->setFlash(__('La entrada no se podía editar.'),'default',array('class' => 'error-message'));
					}
				}
				else {
					$goodtogo=true;
					// first check if there are products that have been removed (present in previous version but not anymore)
					foreach ($productsPurchasedEarlier as $productPurchasedEarlier){
						$productpresent = false;
						$warningIfProblem="No era posible editar la compra.<br/>";
						foreach ($this->request->data['Product'] as $product){
							if ($product['product_id']==$productPurchasedEarlier['StockMovement']['product_id']){
								$productpresent=true;
							}
						}
						if (!$productpresent){
							// the product in question $productPurchasedEarlier is no longer present
							$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$productPurchasedEarlier['StockMovement']['stockitem_id'])));
							if ($stockItem['StockItem']['remaining_quantity']!=$stockItem['StockItem']['original_quantity']){
								// the product has already been used in a production run or has been sold
								$goodtogo=false;
								$linkedProduct=$this->Product->read(null,$productPurchasedEarlier['StockMovement']['product_id']);
								$product_name=$linkedProduct['Product']['name'];
								$warningIfProblem.="Antes era presente el producto ".$product_name.", y este producto ya ha estado ocupado en un orden de producción o una salida.";
								$this->Session->setFlash($warningIfProblem, 'default',array('class' => 'error-message'));
							}				
						}
					}
					
					if ($goodtogo){
						// if alright check if there are products that were purchased earlier for which the quantity has changed and if this makes editing prohibitive
						foreach ($this->request->data['Product'] as $product){
							$product_id=$product['product_id'];
							if ($product_id>0){
								$product_quantity = $product['product_quantity'];
								// check the product_id against all productids already in the order
								$purchasedEarlier=false;
								foreach ($productsPurchasedEarlier as $productPurchasedEarlier){
									if ($product_id==$productPurchasedEarlier['StockMovement']['product_id']){
										$purchasedEarlier=true;
										// this is the case when the product was purchased earlier and edited in this instance
										$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$productPurchasedEarlier['StockMovement']['stockitem_id'])));
										// calculate the difference between the old quantity and the new quantity
										$productDifference = $productPurchasedEarlier['StockMovement']['product_quantity']-$product_quantity;
										$purchasedRemaining=$stockItem['StockItem']['remaining_quantity'];
										if ($productDifference > $purchasedRemaining){
											// if the difference is bigger than the remaining quantity set a flash message to mark an error
											$goodtogo=false;
											// get the related product data
											$linkedProduct=$this->Product->read(null,$product_id);
											$product_name=$linkedProduct['Product']['name'];
											$this->Session->setFlash(__('Los cambios no se podían guardar.  Para el producto '.$product_name.' ya se han ocupado más productos (en un orden de producción o una salida) que los que se restaron.'),'default',array('class' => 'error-message'));
										}
										// MODIFIED 20160310; IF THE PRODUCT HAS ALREADY BEEN USED, THE UNIT PRICE CANNOT BE EDITED
										if (abs(round($product['product_price']/$product['product_quantity'],2)-$stockItem['StockItem']['product_unit_price'])>=0.005){
											//echo "product price is ".$product['product_price']."<br/>";
											//pr($stockItem);
											// IF PRICES ARE DIFFERENT, CHECK IF THERE HAVE BEEN RELATED PRODUCTION OR STOCK MOVEMENTS
											$this->ProductionMovement->recursive=-1;
											$relatedProductionMovements=$this->ProductionMovement->find('all',array(
												'conditions'=>array(
													'ProductionMovement.bool_input'=>true,
													'ProductionMovement.product_quantity >'=>0,
													'ProductionMovement.stockitem_id'=>$stockItem['StockItem']['id'],
												),
											));
											$this->StockMovement->recursive=-1;
											$relatedStockMovements=$this->StockMovement->find('all',array(
												'conditions'=>array(
													'StockMovement.bool_input'=>false,
													'StockMovement.product_quantity >'=>0,
													'StockMovement.stockitem_id'=>$stockItem['StockItem']['id'],
												),
											));
											if (!empty($relatedProductionMovements)||!empty($relatedStockMovements)){
												$goodtogo=false;
												// get the related product data
												$linkedProduct=$this->Product->read(null,$product_id);
												$product_name=$linkedProduct['Product']['name'];
												$this->Session->setFlash(__('Los cambios no se podían guardar.  Para el producto '.$product_name.' ya existen ordenes de producción o salidas, y por tanto se prohibe cambiar el costo.'),'default',array('class' => 'error-message'));
											}
										}
									}
								}
							}
						}
					}
					
					// yet another check: check if this has forced STOCK MOVEMENTS taken from this stockitem to come before purchase date
					if ($goodtogo){
						$warning="Este lote ha estado utilizado en salidas antes de la nueva fecha de entrada.<br/>";					
						foreach ($productsPurchasedEarlier as $purchaseStockMovement){
							$stockMovementsForStockItemsInThisPurchase=$this->StockMovement->find('all',array(
								'conditions'=>array(
									'StockMovement.movement_date <'=>$purchaseDateAsString,
									'StockMovement.stockitem_id'=>$purchaseStockMovement['StockMovement']['stockitem_id'],
									'StockMovement.bool_input'=>false,
								),
								'order'=>'StockMovement.movement_date ASC'
							));
							if (count($stockMovementsForStockItemsInThisPurchase)>0){
								$goodtogo=false;
								foreach ($stockMovementsForStockItemsInThisPurchase as $stockmovement){
									$linkedOrder=$this->Order->read(null,$stockmovement['StockMovement']['order_id']);
									$movementdate=new DateTime($stockmovement['StockMovement']['movement_date']);
									$warning.="Tapon salió en salida ".$linkedOrder['Order']['order_code']." en ".$movementdate->format('d-m-Y')."<br/>";
								}
							}
						}
						if (!$goodtogo){
							$this->Session->setFlash($warning,'default',array('class' => 'error-message'));
						}
					}
					
					// yet another check: check if this has forced PRODUCTION MOVEMENTS taken from this stockitem to come before purchase date
					if ($goodtogo){
						$warning="Este lote ha estado utilizado en ordenes de producción antes de la nueva fecha de entrada.<br/>";
						foreach ($productsPurchasedEarlier as $purchaseStockMovement){
							$productionMovementsForStockItemsInThisPurchase=$this->ProductionMovement->find('all',array(
								'conditions'=>array(
									'ProductionMovement.movement_date <'=>$purchaseDateAsString,
									'ProductionMovement.stockitem_id'=>$purchaseStockMovement['StockMovement']['stockitem_id'],
								),
								'order'=>'ProductionMovement.movement_date ASC'
							));
							if (count($productionMovementsForStockItemsInThisPurchase)>0){
								$goodtogo=false;
								foreach ($productionMovementsForStockItemsInThisPurchase as $productionmovement){
									$linkedProductionRun=$this->ProductionRun->read(null,$productionmovement['ProductionMovement']['production_run_id']);
									$movementdate=new DateTime($productionmovement['ProductionMovement']['movement_date']);
									$warning.="Preforma ocupado en orden de producción ".$linkedProductionRun['ProductionRun']['production_run_code']." en ".$movementdate->format('d-m-Y')."<br/>";
								}
							}
						}
						if (!$goodtogo){
							$this->Session->setFlash($warning,'default',array('class' => 'error-message'));
						}
					}
					
					if ($goodtogo){
						// now we are sure that we can proceed without problems
						$datasource=$this->Order->getDataSource();
						$datasource->begin();
						try {
							$this->request->data['Order']['id']=$id;
							$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_PURCHASE;
					
							if (!$this->Order->save($this->request->data)) {
								echo "problema al guardar la entrada";
								pr($this->validateErrors($this->Order));
								throw new Exception();
							}
						
							// get the relevant information of the purchase that was just saved
							$purchase_id=$this->Order->id;
							$purchase_date=$this->request->data['Order']['order_date'];
							$order_code=$this->request->data['Order']['order_code'];		
							$provider_id=$this->request->data['Order']['third_party_id'];
							// get the related provider data
							$linkedProvider=$this->ThirdParty->read(null,$provider_id);
							$provider_name=$linkedProvider['ThirdParty']['company_name'];
							
							// IF A PRODUCT PRESENT PREVIOUSLY IS NO LONGER THERE AT ALL, SET IT TO 0
							foreach ($productsPurchasedEarlier as $productPurchasedEarlier){
								//echo "product purchased earlier";
								//pr($productPurchasedEarlier);
						
								$productpresent = false;
								foreach ($this->request->data['Product'] as $product){
									if ($product['product_id']==$productPurchasedEarlier['StockMovement']['product_id']){
										$productpresent=true;
									}
								}
								if (!$productpresent){
									//echo "starting to remove the previously registered product";
									//pr ($productPurchasedEarlier);
									// the product in question $productPurchasedEarlier is no longer present
									
									// we know already that the remaining quantity and original quantity of the related stockitem are the same because that is what we checked earlier
									$linkedProduct=$this->Product->read(null,$productPurchasedEarlier['StockMovement']['product_id']);
									$product_name=$linkedProduct['Product']['name'];
									
									$stockMovement=$this->StockMovement->find('first',array ('conditions'=>array('StockMovement.id'=>$productPurchasedEarlier['StockMovement']['id'])));
									// set the stockmovement quantity to 0
									$StockMovementData['id']=$stockMovement['StockMovement']['id'];
									$StockMovementData['product_quantity']=0;
									$StockMovementData['product_unit_price']=0;
									$StockMovementData['product_total_price']=0;
									$StockMovementData['description']=$stockMovement['StockMovement']['description']."|Product removed from order ".$order_code;
									
									$this->StockMovement->clear();
									$logsuccess=$this->StockMovement->save($StockMovementData);
									if (!$logsuccess) {
										echo "problema al guardar el movimiento de entrada";
										pr($this->validateErrors($this->StockMovement));
										throw new Exception();
									}
									
									// set the stockitem quantity to 0
									$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$productPurchasedEarlier['StockMovement']['stockitem_id'])));
									$StockItemData['id']=$stockItem['StockItem']['id'];
									$StockItemData['description']=$stockItem['StockItem']['id']."|"."Product removed from order ".$order_code;
									$StockItemData['stockitem_creation_date']=$purchase_date;
									$StockItemData['product_unit_price']=0;
									$StockItemData['original_quantity']=0;
									$StockItemData['remaining_quantity']=0;
									
									$this->StockItem->clear();
									$logsuccess=$this->StockItem->save($StockItemData);
									if (!$logsuccess) {
										echo "problema al guardar el lote";
										pr($this->validateErrors($this->StockItem));
										throw new Exception();
									}
									
									// set the stockitemlog quantity to 0
									$lastStockItemLog =$this->StockItemLog->find('first',
										array(
											'conditions'=>array('StockItemLog.stockitem_id'=>$stockItem['StockItem']['id']),
											'order'=>'StockItemLog.id DESC'
										)
									);
									$StockItemLogData['id']=$lastStockItemLog['StockItemLog']['id'];
									$StockItemLogData['stockitem_id']=$stockItem['StockItem']['id'];
									$StockItemLogData['product_unit_price']=0;
									$StockItemLogData['product_quantity']=0;
									
									$this->StockItemLog->clear();
									$logsuccess=$this->StockItemLog->save($StockItemLogData);
									if (!$logsuccess) {
										echo "problema al guardar el estado de lote";
										pr($this->validateErrors($this->StockItemLog));
										throw new Exception();
									}
									
									// remove the data to avoid overwriting
									unset($StockItemData);
									unset($StockMovementData);
									unset($StockItemLogData);
									
									// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
									$this->recordUserActivity($this->Session->read('User.username'),'removed product '.$product_name.' from purchase order '.$order_code);
								}
							}
							
							foreach ($this->request->data['Product'] as $product){
								// four records need to be kept: // one for stockmovement // one for stockitem // one for stockitemlog // one for userlog
								
								// load the product request data into variables
								$product_id = $product['product_id'];
								$product_quantity = $product['product_quantity'];
								$product_price = $product['product_price'];

								if ($product_id>0){
									//pr($product);
									// check the product_id against all productids already in the order
									$purchasedEarlier=false;
									// bool correction done comes into play when two rows with the same product were registered
									$boolCorrectionDone=false;
									foreach ($productsPurchasedEarlier as $productPurchasedEarlier){
										if ($product_id==$productPurchasedEarlier['StockMovement']['product_id']){
											if (!$boolCorrectionDone){
												$purchasedEarlier=true;
												// this is the case when the product was purchased earlier and edited in this instance
												$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$productPurchasedEarlier['StockMovement']['stockitem_id'])));
												// compare the new quantity with the old quantity
												$productDifference = $productPurchasedEarlier['StockMovement']['product_quantity']-$product_quantity;
												$purchasedRemaining=$stockItem['StockItem']['remaining_quantity'];
												// if the new product quantity is bigger than the original quantity
												// or when the new product quantity is smaller but the difference is smaller than the remaining quantity
												// then correct the difference everywhere 
												if ($productDifference <= $purchasedRemaining){
													// calculate the unit price
													$product_unit_price=$product_price/$product_quantity;
													
													$stockitem_id=$stockItem['StockItem']['id'];
													$itemmovementname=$purchase_date['day']."_".$purchase_date['month']."_".$purchase_date['year']."_".$provider_name."_".$order_code."_".$productPurchasedEarlier['Product']['name'];
													$description="Edited stockitem ".$stockItem['StockItem']['name']." for Product ".$productPurchasedEarlier['Product']['name']."(New Quantity:".$product_quantity.",Unit Price:".$product_unit_price.") from Purchase ".$order_code;
													//echo "updating the data for the previously purchased product";
													
													// STEP 1: SAVE THE STOCK ITEM
													//echo "step 1 stockitem id is ".$stockItem['StockItem']['id']."<br/>";
													$StockItemData['id']=$stockItem['StockItem']['id'];
													$StockItemData['name']=$itemmovementname;
													$StockItemData['stockitem_creation_date']=$purchase_date;
													$StockItemData['description']=$stockItem['StockItem']['description']."|".$description;
													//MODIFIED 20160310 ADDED PRODUCT UNIT PRICE UPDATE
													$StockItemData['product_unit_price']=$product_unit_price;
													
													$StockItemData['original_quantity']=$product_quantity;
													$StockItemData['remaining_quantity']=$stockItem['StockItem']['remaining_quantity']-$stockItem['StockItem']['original_quantity']+$product_quantity;

													$this->StockItem->clear();
													if (!$this->StockItem->save($StockItemData)) {
														echo "problema al guardar el lote";
														pr($this->validateErrors($this->StockItem));
														throw new Exception();
													}
													
													// MODIFIED 20160310 UPDATE UNIT PRICE FOR PRODUCTION MOVEMENTS AND STOCK MOVEMENTS
													// COMMENTED OUT AS IT WOULD LEAD TO FAR TO UPDATE PRICES THROUGHOUT THE SYSTEM, INSTEAD A CHECK IS ADDED TO FORBID THE PRICE CHANGE
													// STEP 1B UPDATE PRODUCTION MOVEMENT PRICES
													/*
													$this->ProductionMovement->recursive=-1;
													$productionMovementsForThisStockItem=$this->ProductionMovement->find('all',array(
														'conditions'=>array(
															'ProductionMovement.stockitem_id'=>$stockItem['StockItem']['id'],
															'ProductionMovement.product_quantity >'=>0,
														),
													));
													if (!empty($productionMovementsForThisStockItem)){
														foreach ($productionMovementsForThisStockItem as $inputProductionMovement){
															$ProductionMovementData=array();
															$ProductionMovementData['product_unit_price']=$product_unit_price;
															$this->ProductionMovement->id=$inputProductionMovement['ProdutionMovement']['id'];
															if (!$this->ProductionMovement->save($ProductionMovementData)) {
																echo "problema al guardar el precio unitario modificado para los productos fabricados";
																pr($this->validateErrors($this->ProductionMovement));
																throw new Exception();
															}
															
															//	it is however, not enough to just update the price for the used products; 
															// 	the same would have to be done for all produced items, both for the movements and for the stockitems created
															//  and then it should also cascade through to the sale of these items
														}
													}
													*/

													// STEP 2: SAVE THE STOCK MOVEMENT
													//echo "step 2 stockmovement id is ".$productPurchasedEarlier['StockMovement']['id']."<br/>";
													$stockMovement=$this->StockMovement->find('first',array ('conditions'=>array('StockMovement.id'=>$productPurchasedEarlier['StockMovement']['id'])));
													
													$StockMovementData['id']=$productPurchasedEarlier['StockMovement']['id'];
													$StockMovementData['name']=$itemmovementname;
													$StockMovementData['description']=$stockMovement['StockMovement']['description']."|".$description;
													$StockMovementData['movement_date']=$purchase_date;
													$StockMovementData['product_quantity']=$product_quantity;
													$StockMovementData['product_unit_price']=$product_unit_price;
													$StockMovementData['product_total_price']=$product_price;
													
													$this->StockMovement->clear();
													$logsuccess=$this->StockMovement->save($StockMovementData);
													if (!$logsuccess) {
														echo "problema al guardar el movimiento de entrada";
														pr($this->validateErrors($this->StockMovement));
														throw new Exception();
													}

													// recreate the stockitemlogs
													$this->recreateStockItemLogs($stockitem_id);
													
													
													// remove the data to avoid overwriting
													unset($StockItemData);
													unset($StockMovementData);
													unset($StockItemLogData);
													
													// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
													$this->recordUserActivity($this->Session->read('User.username'),$description);
												}
												$boolCorrectionDone=true;
											}
											else {
												// the correction has been made already, but there is a second product in $productsPurchasedEarlier that needs to be removed
												$linkedProduct=$this->Product->read(null,$productPurchasedEarlier['StockMovement']['product_id']);
												$product_name=$linkedProduct['Product']['name'];
												
												$stockMovement=$this->StockMovement->find('first',array ('conditions'=>array('StockMovement.id'=>$productPurchasedEarlier['StockMovement']['id'])));
												// set the stockmovement quantity to 0
												$StockMovementData['id']=$stockMovement['StockMovement']['id'];
												$StockMovementData['product_quantity']=0;
												$StockMovementData['product_unit_price']=0;
												$StockMovementData['product_total_price']=0;
												$StockMovementData['description']=$stockMovement['StockMovement']['description']."|Product removed from order ".$order_code;
												
												$this->StockMovement->clear();
												$logsuccess=$this->StockMovement->save($StockMovementData);
												if (!$logsuccess) {
													echo "problema al guardar el movimiento de entrada";
													pr($this->validateErrors($this->StockMovement));
													throw new Exception();
												}
												
												// set the stockitem quantity to 0
												$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$productPurchasedEarlier['StockMovement']['stockitem_id'])));
												$StockItemData['id']=$stockItem['StockItem']['id'];
												$StockItemData['description']=$stockItem['StockItem']['id']."|"."Product removed from order ".$order_code;
												$StockItemData['stockitem_creation_date']=$purchase_date;
												$StockItemData['product_unit_price']=0;
												$StockItemData['original_quantity']=0;
												$StockItemData['remaining_quantity']=0;
												
												$this->StockItem->clear();
												$logsuccess=$this->StockItem->save($StockItemData);
												if (!$logsuccess) {
													echo "problema al guardar el lote";
													pr($this->validateErrors($this->StockItem));
													throw new Exception();
												}
												
												// set the stockitemlog quantity to 0
												$lastStockItemLog =$this->StockItemLog->find('first',
													array(
														'conditions'=>array('StockItemLog.stockitem_id'=>$stockItem['StockItem']['id']),
														'order'=>'StockItemLog.id DESC'
													)
												);
												$StockItemLogData['id']=$lastStockItemLog['StockItemLog']['id'];
												$StockItemLogData['stockitem_id']=$stockItem['StockItem']['id'];
												$StockItemLogData['product_unit_price']=0;
												$StockItemLogData['product_quantity']=0;
												
												$this->StockItemLog->clear();
												$logsuccess=$this->StockItemLog->save($StockItemLogData);
												if (!$logsuccess) {
													echo "problema al guardar el estado de lote";
													pr($this->validateErrors($this->StockItemLog));
													throw new Exception();
												}
												
												// remove the data to avoid overwriting
												unset($StockItemData);
												unset($StockMovementData);
												unset($StockItemLogData);
												
												// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
												$this->recordUserActivity($this->Session->read('User.username'),'removed product '.$product_name.' from purchase order '.$order_code);
											}
										}
									}
									if (!$purchasedEarlier){
										//echo "inserting the new data";
										// calculate the unit price
										$product_unit_price=$product_price/$product_quantity;
										
										// get the related product data
										$linkedProduct=$this->Product->read(null,$product_id);
										$product_name=$linkedProduct['Product']['name'];
										
										$message="New stockitem ".$product_name." (Quantity:".$product_quantity.",Unit Price:".$product_unit_price.") from Purchase ".$order_code;
										
										// STEP 1: SAVE THE STOCK ITEM
										$this->loadModel('StockItem');
										$StockItemData['name']=$purchase_date['day']."_".$purchase_date['month']."_".$purchase_date['year']."_".$order_code."_".$product_name;
										$StockItemData['stockitem_creation_date']=$purchase_date;
										$StockItemData['description']=$message;
										$StockItemData['product_id']=$product_id;
										$StockItemData['product_unit_price']=$product_unit_price;
										$StockItemData['original_quantity']=$product_quantity;
										$StockItemData['remaining_quantity']=$product_quantity;
										
										$this->StockItem->clear();
										$this->StockItem->create();
										$logsuccess=$this->StockItem->save($StockItemData);
										if (!$logsuccess) {
											echo "problema al guardar el lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}
										
										// STEP 2: SAVE THE STOCK MOVEMENT
										$stockitem_id=$this->StockItem->id;
										$StockMovementData['movement_date']=$purchase_date;
										$StockMovementData['bool_input']=true;
										$StockMovementData['name']=$purchase_date['day']."_".$purchase_date['month']."_".$purchase_date['year']."_".$order_code."_".$product_name;
										$StockMovementData['description']=$message;
										$StockMovementData['order_id']=$purchase_id;
										$StockMovementData['stockitem_id']=$stockitem_id;
										$StockMovementData['product_id']=$product_id;
										$StockMovementData['product_quantity']=$product_quantity;
										$StockMovementData['product_unit_price']=$product_unit_price;
										$StockMovementData['product_total_price']=$product_price;
										
										$this->StockMovement->clear();
										$this->StockMovement->create();
										$logsuccess=$this->StockMovement->save($StockMovementData);
										if (!$logsuccess) {
											echo "problema al guardar el lote";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}

										// STEP 3: SAVE THE STOCK ITEM LOG
										$stockmovement_id=$this->Order->StockMovement->id;
										$StockItemLogData['stockitem_id']=$stockitem_id;
										$StockItemLogData['stock_movement_id']=$stockmovement_id;
										$StockItemLogData['stockitem_date']=$purchase_date;
										$StockItemLogData['product_id']=$product_id;
										$StockItemLogData['product_unit_price']=$product_unit_price;
										$StockItemLogData['product_quantity']=$product_quantity;
										
										$this->StockItemLog->clear();
										$this->StockItemLog->create();
										$logsuccess=$this->StockItemLog->save($StockItemLogData);
										if (!$logsuccess) {
											echo "problema al guardar el estado de lote";
											pr($this->validateErrors($this->StockItemLog));
											throw new Exception();
										}
										
										unset($StockItemData);
										unset($StockMovementData);
										unset($StockItemLogData);
										
										// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
										$this->recordUserActivity($this->Session->read('User.username'),$message);
									}
								}
							}
							
							$datasource->commit();
							$this->recordUserAction();
							$this->recordUserActivity($this->Session->read('User.username'),"Purchase edited with invoice code ".$this->request->data['Order']['order_code']);
							$this->Session->setFlash(__('The purchase has been saved.'),'default',array('class' => 'success'));
							return $this->redirect(array('action' => 'resumenEntradas'));
						}
						catch(Exception $e){
							$this->Session->setFlash(__('The purchase could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
						}
					} 
					
				}
			}	
		}
		else {
			$this->request->data = $this->Order->find('first', array(
				'conditions' => array(
					'Order.id' => $id,
				),
				'contain'=>array(
					'StockMovement'=>array(
						'StockItem'=>array(
							'ProductionMovement'=>array(
								'conditions'=>array(
									'ProductionMovement.product_quantity >'=>0,
								),
								'ProductionRun',
							),
							'StockMovement'=>array(
								'conditions'=>array(
									'StockMovement.product_quantity >'=>0,
									'StockMovement.order_id !='=>$id,
								),
								'Order',
							),
							
						)
					),
				),
			));
			$reasonForNonEditable="";
			foreach ($this->request->data['StockMovement'] as $stockMovement){
				if (!empty($stockMovement['StockItem']['ProductionMovement'])){
					$boolEditable=false;
				}
				if (!empty($stockMovement['StockItem']['StockMovement'])){
					$boolEditable=false;
				}
			}
			if (!$boolEditable){
				$reasonForNonEditable.="Los productos de la entrada no se pueden editar porque ya se han utilizado en ";
				foreach ($this->request->data['StockMovement'] as $stockMovement){
					foreach ($stockMovement['StockItem']['ProductionMovement'] as $productionMovement){						
						if (!empty($productionMovement['ProductionRun'])){
							//pr($productionMovement['ProductionRun']);
							$reasonForNonEditable.="orden de producción ".$productionMovement['ProductionRun']['production_run_code']." ";
						}
						else {
							//pr($productionMovement);
						}
						
					}
					foreach ($stockMovement['StockItem']['StockMovement'] as $stockMovement){
						//pr($productionMovement['StockItem']['StockMovement']);
						if (!$stockMovement['bool_transfer']){
							if (!empty($stockMovement['Order'])){
								//pr($productionMovement['StockItem']['StockMovement']['Order']);
								$reasonForNonEditable.=$stockMovement['Order']['order_code']." ";
							}
							else {
								//pr($productionMovement['StockItem']['StockMovement']);
							}
						}
						else {
							$reasonForNonEditable.=$stockMovement['transfer_code']." ";
						}
					}
				}
			}
		}	
		$this->set(compact('boolEditable'));
		$this->set(compact('reasonForNonEditable'));
			
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions' => array('ThirdParty.bool_provider'=> true)
		));
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
	
		$this->Product->recursive=0;
		$productsAll = $this->Product->find('all',array(
			'fields'=>'Product.id,Product.name',
			'conditions' => array('ProductType.product_category_id !='=> CATEGORY_PRODUCED),
		));
		$products = null;
		foreach ($productsAll as $product){
			$products[$product['Product']['id']]=$product['Product']['name'];
		}
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','productsPurchasedEarlier'));		
		
		$aco_name="Orders/crearEntrada";		
		$bool_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_add_permission'));
		$aco_name="Orders/editarEntrada";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		$aco_name="Orders/anularEntrada";		
		$bool_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_annul_permission'));
		
		$aco_name="ThirdParties/resumenProveedores";		
		$bool_provider_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_index_permission'));
		$aco_name="ThirdParties/crearProveedor";		
		$bool_provider_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_provider_add_permission'));
	}
	
	public function editarVenta($id = null) {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
				
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('Invoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterInvoice');
		$this->loadModel('AccountingMovement');
		
		$warehouseId=WAREHOUSE_DEFAULT;
		$requestProducts=array();
		
		$this->StockMovement->virtualFields['total_product_quantity']=0;
		$this->StockMovement->virtualFields['total_product_price']=0;
		$productsSold = $this->StockMovement->find('all',array(
			'fields'=>array(
				'product_unit_price',
				'SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity', 
				'SUM(product_total_price) AS StockMovement__total_product_price', 
				'StockMovement.product_unit_price', 
				'Product.id', 'Product.name', 
				'StockMovement.production_result_code_id', 'ProductionResultCode.code', 'StockItem.raw_material_id',
			),
			'conditions'=>array('StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'),
			'group'=>array('Product.id, StockItem.raw_material_id, ProductionResultCode.code'),
		));
		$stockItemsOfSoldProducts=$this->StockMovement->find('list',array(
			'fields'=>array(
				'StockMovement.stockitem_id',
			),
			'conditions'=>array(
				'StockMovement.order_id'=>$id,
				'StockMovement.product_quantity>0',
			),
		));
		
		$bool_first_load=true;
		$invoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.order_id'=>$id,
			)
		));
		//pr($productsSold);
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
		//pr($this->request->data);
		if ($this->request->is(array('post', 'put'))) {
			foreach ($this->request->data['Product'] as $product){
				if (!empty($product['product_id'])){
					$requestProducts[]['Product']=$product;
				}
			}
			$warehouseId=$this->request->data['Order']['warehouse_id'];
			if (empty($this->request->data['refresh'])){
				$bool_first_load=false;
				$sale_date=$this->request->data['Order']['order_date'];
				$saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
				$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
				$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
				$closingDate=new DateTime($latestClosingDate);
				
				$saleDateArray=array();
				$saleDateArray['year']=$sale_date['year'];
				$saleDateArray['month']=$sale_date['month'];
				$saleDateArray['day']=$sale_date['day'];
				
				$order_code=$this->request->data['Order']['order_code'];
				$namedSales=$this->Order->find('all',array(
					'conditions'=>array(
						'order_code'=>$order_code,
						'stock_movement_type_id'=>MOVEMENT_SALE,
						'Order.id !='=>$id,
					)
				));
				
				if (count($namedSales)>0){
					$this->Session->setFlash(__('Ya existe una salida con el mismo código!  No se guardó la salida.'), 'default',array('class' => 'error-message'));
				}
				elseif ($saleDateAsString>date('Y-m-d 23:59:59')){
					$this->Session->setFlash(__('La fecha de salida no puede estar en el futuro!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				elseif ($saleDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se puede realizar cambios.'), 'default',array('class' => 'error-message'));
				}
				elseif ($this->request->data['Order']['third_party_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar el cliente para la venta!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				elseif ($this->request->data['Invoice']['bool_annulled']){
					$datasource=$this->Order->getDataSource();
					$datasource->begin();
					$oldInvoice=$this->Invoice->find('first',array(
						'conditions'=>array(
							'Invoice.order_id'=>$id,
						)
					));
					try {
						// first remove existing data: invoice, accounting registers, accounting register invoice
						$oldAccountingRegisterInvoices=$this->AccountingRegisterInvoice->find('all',array(
							'fields'=>array('AccountingRegisterInvoice.id','AccountingRegisterInvoice.accounting_register_id'),
							'conditions'=>array(
								'invoice_id'=>$oldInvoice['Invoice']['id']
							)
						));
						
						if (!empty($oldAccountingRegisterInvoices)){
							foreach ($oldAccountingRegisterInvoices as $oldAccountingRegisterInvoice){
								// first remove the movement
								$oldAccountingMovements=$this->AccountingMovement->find('all',array(
									'fields'=>array('AccountingMovement.id'),
									'conditions'=>array(
										'accounting_register_id'=>$oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id'],
									)
								));
								if (!empty($oldAccountingMovements)){
									foreach ($oldAccountingMovements as $oldAccountingMovement){
										$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
									}
								}
								// then remove the register
								$this->AccountingRegister->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id']);
								// then remove the register invoice link
								$this->AccountingRegisterInvoice->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['id']);
							}
						}
						// then remove the invoice
						$this->Invoice->delete($oldInvoice['Invoice']['id']);
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',array('class' => 'error-message'));
					}
					// then save the minimum data for the annulled invoice/order				
					$datasource->begin();
					try {
						//pr($this->request->data);
						$OrderData=array();
						$OrderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
						$OrderData['Order']['order_date']=$this->request->data['Order']['order_date'];
						$OrderData['Order']['order_code']=$this->request->data['Order']['order_code'];
						$OrderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
						$OrderData['Order']['total_price']=0;
				
						if (!$this->Order->save($OrderData)) {
							echo "Problema guardando el orden de salida";
							pr($this->validateErrors($this->Order));
							throw new Exception();
						}
						
						$order_id=$this->Order->id;
						
						$InvoiceData=array();
						$InvoiceData['Invoice']['id']=$oldInvoice['Invoice']['id'];;
						$InvoiceData['Invoice']['order_id']=$order_id;
						$InvoiceData['Invoice']['order_code']=$this->request->data['Order']['order_code'];
						$InvoiceData['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
						$InvoiceData['Invoice']['bool_annulled']=true;
						$InvoiceData['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
						$InvoiceData['Invoice']['total_price']=0;
						$InvoiceData['Invoice']['currency_id']=CURRENCY_CS;
				
						if (!$this->Invoice->save($InvoiceData)) {
							echo "Problema guardando la factura";
							pr($this->validateErrors($this->Invoice));
							throw new Exception();
						}
						
						$datasource->commit();
							
						// SAVE THE USERLOG 
						$this->recordUserActivity($this->Session->read('User.username'),"Se registró una venta con número ".$this->request->data['Order']['order_code']);
						$this->Session->setFlash(__('Se guardó la venta.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
						return $this->redirect(array('action' => 'resumenVentasRemisiones'));

					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
					}
				}
				else if (!$this->request->data['Invoice']['bool_credit']&&$this->request->data['Invoice']['cashbox_accounting_code_id']==0){
					$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una factura de contado!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}
				else if ($this->request->data['Invoice']['bool_retention']&&strlen($this->request->data['Invoice']['retention_number'])==0){
					$this->Session->setFlash(__('Se debe especificar el número de retención!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
				}			
				else {
					$this->request->data['Order']['id']=$id;
					$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
					// get the relevant information of the purchase that was just saved
					$order_id=$id;
					$sale_date=$this->request->data['Order']['order_date'];
					$order_code=$this->request->data['Order']['order_code'];		
					
					$saleItemsOK=true;
					$exceedingItems="";
					$productCount=0;
					$productsAlreadySold=array();
					$productsNew=array();
					
					foreach ($this->request->data['Product'] as $product){
						//pr($product);
						$soldEarlier=false;	
						// keep track of number of rows so that in case of an error jquery displays correct number of rows again
						if ($product['product_id']>0){
							$productCount++;
						}
						// only process lines where product_quantity has been filled out
						if ($product['product_quantity']>0){
							
							$productid = $product['product_id'];
							
							$productquantity=$product['product_quantity'];
							$productunitprice = $product['product_unit_price'];
							$producttotalprice = $product['product_total_price'];
							$productionresultcodeid = $product['production_result_code_id'];
							$rawmaterialid = $product['raw_material_id'];
							$productsInStock=array();
							$this->StockItem->recursive=-1;
							if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
								//echo "for finishedproduct";
								$productsInStock = $this->StockItem->find('all',array(
									'fields'=>'SUM(StockItem.remaining_quantity) AS remaining',
									'conditions' => array(
										'StockItem.product_id'=> $productid,
										'StockItem.production_result_code_id'=> $productionresultcodeid,
										'StockItem.raw_material_id'=> $rawmaterialid,
										'StockItem.stockitem_creation_date <'=> $saleDateAsString,
									),
									'group' => array('StockItem.product_id'),
								));
							}
							else {
								//echo "for tapones";
								$productsInStock = $this->StockItem->find('all',array(
									//'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
									'fields'=>'SUM(StockItem.remaining_quantity) AS remaining',
									'conditions' => array(
										'StockItem.product_id'=> $productid,
										'StockItem.stockitem_creation_date <'=> $saleDateAsString,
									),
									'group' => array('StockItem.product_id')
								));
							}
							//pr($productsInStock);
							if (!empty($productsInStock)){
								//echo "let's check out what is in stock";
								//pr($productsInStock);
								$quantityInStock=$productsInStock[0][0]['remaining'];
							}
							else {
								$quantityInStock=0;
							}
							//echo "quantity in stock is ".$quantityInStock."<br/>";
							$quantitySoldAlready=0;
							$soldAlready=false;
							$currentstockmovementid=0;
							foreach ($productsSold as $productSold){
								if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
									if ($productid==$productSold['Product']['id'] && $productionresultcodeid==$productSold['StockMovement']['production_result_code_id'] && $rawmaterialid==$productSold['StockItem']['raw_material_id']){
										$soldAlready=true;
										$quantitySoldAlready=$productSold['StockMovement']['total_product_quantity'];
									}
								}
								else {
									if ($productid==$productSold['Product']['id']){
										$soldAlready=true;
										$quantitySoldAlready=$productSold['StockMovement']['total_product_quantity'];
									}
								}
							}
							// check how many more items would be needed
							$quantityNeeded=$productquantity-$quantitySoldAlready;
							$linkedProduct=$this->Product->read(null,$productid);
							$productname=$linkedProduct['Product']['name'];
							//compare the quantity requested and the quantity in stock
							if ($quantityNeeded>$quantityInStock){
								$saleItemsOK=false;
								
								$exceedingItems.=__("Para producto ".$productname." la cantidad requerida demás (".$quantityNeeded.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
							}
						}
					}
					if ($exceedingItems!=""){
						$exceedingItems.=__("Please correct and try again!");
					}
					if (!$saleItemsOK) {
						$this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',array('class' => 'error-message'));
					}				
					else{
						$datasource=$this->Order->getDataSource();
						$datasource->begin();
						// retrieve the original stockMovements that were involved in the sale
						$stockMovementsPreviousSale=$this->Order->StockMovement->find('all',
							array(
								'fields'=>'StockMovement.product_id,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
								'contain'=>array(
									'StockItem'=>array(
										'fields'=> array('remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'),
										'StockItemLog'=>array(
											'fields'=>array('StockItemLog.id,StockItemLog.stockitem_date'),
										)
									),
									'Product'=>array(
										'fields'=> array('id','name'),
									)
								),
								'conditions' => array(
									'StockMovement.order_id'=> $id
								)
							)
						);				
						
						// first bring back everything to original state
						$removedOK=false;
						$oldAccountingRegisterCode="";
						try {
							foreach ($stockMovementsPreviousSale as $previousStockMovement){						
								// set all stockmovements to 0
								$oldStockMovementData=array();
								$oldStockMovementData['id']=$previousStockMovement['StockMovement']['id'];
								$oldStockMovementData['description']=$previousStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
								$oldStockMovementData['product_quantity']=0;
								$oldStockMovementData['product_total_price']=0;
								
								$this->StockMovement->clear();
								$logsuccess=$this->StockMovement->save($oldStockMovementData);
								if (!$logsuccess) {
									echo "problema al guardar el movimiento de salida";
									pr($this->validateErrors($this->StockMovement));
									throw new Exception();
								}
								
								// restore the stockitems to their previous level
								$oldStockItemData=array();
								$oldStockItemData['id']=$previousStockMovement['StockItem']['id'];
								$oldStockItemData['description']=$previousStockMovement['StockItem']['description']." added back quantity ".$previousStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
								$oldStockItemData['remaining_quantity']=$previousStockMovement['StockItem']['remaining_quantity']+$previousStockMovement['StockMovement']['product_quantity'];
								
								$this->StockItem->clear();
								$logsuccess=$this->StockItem->save($oldStockItemData);
								if (!$logsuccess) {
									echo "problema al guardar el lote";
									pr($this->validateErrors($this->StockItem));
									throw new Exception();
								}
								
								unset($oldStockMovementData);
								unset($oldStockItemData);
								
								$this->recreateStockItemLogs($previousStockMovement['StockItem']['id']);
							}
												
							// first remove existing data: invoice, accounting registers, accounting register invoice
							$oldInvoice=$this->Invoice->find('first',array(
								'conditions'=>array(
									'Invoice.order_id'=>$id,
								)
							));
							if (!empty($oldInvoice)){
								//MODIFIED 20160310 TO INCLUDE ONLY ONE AND GET THE OLD REGISTER CODE
								//IN PRACTICE THERE IS ONLY ONE ACCOUNTING REGISTER FOR THE INVOICE
								$oldAccountingRegisterInvoice=$this->AccountingRegisterInvoice->find('first',array(
									'fields'=>array('AccountingRegisterInvoice.id','AccountingRegisterInvoice.accounting_register_id'),
									'conditions'=>array(
										'invoice_id'=>$oldInvoice['Invoice']['id']
									),
									'order'=>'id',
								));
								
								if (!empty($oldAccountingRegisterInvoice)){
									// first remove the movement
									$oldAccountingMovements=$this->AccountingMovement->find('all',array(
										'fields'=>array('AccountingMovement.id'),
										'conditions'=>array(
											'accounting_register_id'=>$oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id'],
										)
									));
									if (!empty($oldAccountingMovements)){
										foreach ($oldAccountingMovements as $oldAccountingMovement){
											$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
										}
									}
									$oldAccountingRegister=$this->AccountingRegister->find('first',array(
										'conditions'=>array(
											'AccountingRegister.id'=>$oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id'],
										),
									));
									if (!empty($oldAccountingRegister)){
										$oldAccountingRegisterCode=$oldAccountingRegister['AccountingRegister']['register_code'];						
									}
									// then remove the register
									$this->AccountingRegister->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id']);
									// then remove the register invoice link
									$this->AccountingRegisterInvoice->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['id']);
								}
								// then remove the invoice
							}						
							
							$removedOK=true;
							
							$datasource->commit();
							$this->recordUserActivity($this->Session->read('User.username'),"Old sale data removed for ".$this->request->data['Order']['order_code']);
						}
						catch(Exception $e){
							$datasource->rollback();
							pr($e);
							$this->Session->setFlash(__('Los datos de la salida no se podían remover.'), 'default',array('class' => 'error-message'));
						}				
						//echo "everything back to original state";
						if ($removedOK){
							$datasource->begin();
							try	{
								//pr($this->request->data);
								$currency_id=$this->request->data['Invoice']['currency_id'];
								
								$retention_invoice=$this->request->data['Invoice']['retention_amount'];
								$sub_total_invoice=$this->request->data['Invoice']['sub_total_price'];
								$IVA_invoice=$this->request->data['Invoice']['IVA_price'];
								$total_invoice=$this->request->data['Invoice']['total_price'];
								
								if ($currency_id==CURRENCY_USD){
									$this->request->data['Order']['total_price']=$sub_total_invoice*$this->request->data['Order']['exchange_rate'];
								}
								else {
									$this->request->data['Order']['total_price']=$sub_total_invoice;
								}
								if (!$this->Order->save($this->request->data)) {
									echo "problema al guardar la venta";
									pr($this->validateErrors($this->Order));
									throw new Exception();
								}
								
								$order_id=$this->Order->id;
								$order_code=$this->request->data['Order']['order_code'];
							
								$this->Invoice->id=$oldInvoice['Invoice']['id'];
								$this->request->data['Invoice']['order_id']=$order_id;
								$this->request->data['Invoice']['invoice_code']=$this->request->data['Order']['order_code'];
								$this->request->data['Invoice']['invoice_date']=$this->request->data['Order']['order_date'];
								$this->request->data['Invoice']['client_id']=$this->request->data['Order']['third_party_id'];
														
								if ($this->request->data['Invoice']['bool_credit']){
									$this->request->data['Invoice']['bool_retention']=false;
									$this->request->data['Invoice']['retention_amount']=0;
									$this->request->data['Invoice']['retention_number']="";
									$this->request->data['Invoice']['bool_paid']=false;
								}
								else {
									$this->request->data['Invoice']['bool_paid']=true;
								}
								
								if (!$this->Invoice->save($this->request->data)) {
									echo "Problema guardando la factura";
									pr($this->validateErrors($this->Invoice));
									throw new Exception();
								}
								
								$invoice_id=$this->Invoice->id;						
								// now prepare the accounting registers
								
								
								if ($currency_id==CURRENCY_USD){
									$this->loadModel('ExchangeRate');
									$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
									//pr($applicableExchangeRate);
									$retention_CS=round($retention_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
									$sub_total_CS=round($sub_total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
									$IVA_CS=round($IVA_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
									$total_CS=round($total_invoice*$applicableExchangeRate['ExchangeRate']['rate'],2);
								}
								else {
									$retention_CS=$retention_invoice;
									$sub_total_CS=$sub_total_invoice;
									$IVA_CS=$IVA_invoice;
									$total_CS=$total_invoice;
								}
								
								if ($this->request->data['Invoice']['bool_credit']){
									$client_id=$this->request->data['Order']['third_party_id'];
									$this->loadModel('ThirdParty');
									$thisClient=$this->ThirdParty->read(null,$client_id);
								
									$accountingRegisterData['AccountingRegister']['concept']="Venta ".$order_code;
									$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
									if (!empty($oldAccountingRegisterCode)){
										$registerCode=$oldAccountingRegisterCode;
									}
									else {
										$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
									}
									$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
									$accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
									$accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
									$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
									
									if (empty($thisClient['ThirdParty']['accounting_code_id'])){
										$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
										$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES);
									}
									else {								
										$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
										$accountingCode=$this->AccountingCode->read(null,$thisClient['ThirdParty']['accounting_code_id']);
									}							
									$accountingRegisterData['AccountingMovement'][0]['concept']="A cobrar Venta ".$order_code;
									$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
									
									$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
									$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
									$accountingRegisterData['AccountingMovement'][1]['concept']="Ingresos Venta ".$order_code;
									$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
									
									if ($this->request->data['Invoice']['bool_IVA']){
										$accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
										$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
										$accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$order_code;
										$accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
									}
									
									//pr($accountingRegisterData);
									$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
									$this->recordUserAction($this->AccountingRegister->id,"add",null);
							
									$AccountingRegisterInvoiceData=array();
									$AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
									$AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
									$this->AccountingRegisterInvoice->create();
									if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
										pr($this->validateErrors($this->AccountingRegisterInvoice));
										echo "problema al guardar el lazo entre asiento contable y factura";
										throw new Exception();
									}
									//echo "link accounting register sale saved<br/>";					
									
									//ADDED ON 20160310 THEN COMMENTED OUT
									/*
									$this->Invoice->id=$invoice_id;
									$invoiceData=array();
									$invoiceData['accounting_register_id']=$accounting_register_id;
									if (!$this->Invoice->save($invoiceData)) {
										pr($this->validateErrors($this->Invoice));
										echo "problema al guardar el comprobante para la factura";
										throw new Exception();
									}
									*/
									
								}
								else {
									$accountingRegisterData['AccountingRegister']['concept']="Venta ".$order_code;
									
									$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
									if (!empty($oldAccountingRegisterCode)){
										$registerCode=$oldAccountingRegisterCode;
									}
									else {
										$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
									}
									$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
									$accountingRegisterData['AccountingRegister']['register_date']=$saleDateArray;
									$accountingRegisterData['AccountingRegister']['amount']=$sub_total_CS+$IVA_CS;
									$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
									
									if (!$this->request->data['Invoice']['bool_retention']){
										$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
										$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
										$accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$order_code;
										$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS;
									}
									else {
										// with retention
										$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['Invoice']['cashbox_accounting_code_id'];
										$accountingCode=$this->AccountingCode->read(null,$this->request->data['Invoice']['cashbox_accounting_code_id']);
										$accountingRegisterData['AccountingMovement'][0]['concept']="Recibido Venta ".$order_code;
										$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$sub_total_CS+$IVA_CS-$retention_CS;
									}
									
									$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
									$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
									$accountingRegisterData['AccountingMovement'][1]['concept']="Subtotal Venta ".$order_code;
									$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
									$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$sub_total_CS;
									
									if ($this->request->data['Invoice']['bool_IVA']){
										$accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_IVA_POR_PAGAR;
										$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_IVA_POR_PAGAR);
										$accountingRegisterData['AccountingMovement'][2]['concept']="IVA Venta ".$order_code;
										$accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][2]['credit_amount']=$IVA_CS;
									}
									if ($this->request->data['Invoice']['bool_retention']){
										$accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
										$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_RETENCIONES_POR_COBRAR);
										$accountingRegisterData['AccountingMovement'][3]['concept']="Retención Venta ".$order_code;
										$accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
										$accountingRegisterData['AccountingMovement'][3]['debit_amount']=$retention_CS;
									}
									
									//pr($accountingRegisterData);
									$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
									$this->recordUserAction($this->AccountingRegister->id,"add",null);
									//echo "se guardó el comprobante para la venta<br/>";
							
									$AccountingRegisterInvoiceData=array();
									$AccountingRegisterInvoiceData['accounting_register_id']=$accounting_register_id;
									$AccountingRegisterInvoiceData['invoice_id']=$invoice_id;
									$this->AccountingRegisterInvoice->create();
									if (!$this->AccountingRegisterInvoice->save($AccountingRegisterInvoiceData)) {
										pr($this->validateErrors($this->AccountingRegisterInvoice));
										echo "problema al guardar el lazo entre asiento contable y factura";
										throw new Exception();
									}
									//echo "link accounting register sale saved<br/>";	
								}
						
								//echo "comenzando a guardar los productos";
								foreach ($this->request->data['Product'] as $product){
									if ($product['product_id']>0&&$product['product_quantity']>0){
										//pr($product);

										$product_id = $product['product_id'];
										
										$production_result_code_id = $product['production_result_code_id'];
										$raw_material_id = $product['raw_material_id'];
										
										$product_unit_price=$product['product_unit_price'];
										$product_quantity = $product['product_quantity'];
										
										if ($this->request->data['Invoice']['currency_id']==CURRENCY_USD){
											$product_unit_price*=$this->request->data['Order']['exchange_rate'];
										}
										// get the related product data
										$linkedProduct=$this->Product->read(null,$product_id);
										$product_name=$linkedProduct['Product']['name'];
										
										// STEP 1: SAVE THE STOCK ITEM(S)
										// first prepare the materials that will be taken out of stock
										if ($this->Product->getProductCategoryId($product_id)==CATEGORY_PRODUCED){
											$usedMaterials= $this->StockItem->getFinishedMaterialsForSale($product_id,$production_result_code_id,$product_quantity,$raw_material_id,$saleDateAsString);
										}
										else {
											$usedMaterials= $this->StockItem->getOtherMaterialsForSale($product_id,$product_quantity,$saleDateAsString);		
										}
										
										//echo "got sales materials";
										
										//pr($usedMaterials);
										for ($k=0;$k<count($usedMaterials);$k++){
											$materialUsed=$usedMaterials[$k];
											$stockitem_id=$materialUsed['id'];
											$quantity_present=$materialUsed['quantity_present'];
											$quantity_used=$materialUsed['quantity_used'];
											$quantity_remaining=$materialUsed['quantity_remaining'];
											
											if (!$this->StockItem->exists($stockitem_id)) {
												throw new NotFoundException(__('Invalid Purchase'));
											}
											
											$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$stockitem_id)));
											//pr($stockItem);
											$message="Sold stockitem ".$product_name." (Quantity:".$quantity_used.") for Sale ".$order_code;
											$newStockItemData=array();
											$newStockItemData['id']=$stockitem_id;
											$newStockItemData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$order_code."_".$product_name;
											$newStockItemData['description']=$stockItem['StockItem']['description']."|".$message;
											$newStockItemData['remaining_quantity']=$quantity_remaining;
											
											$this->StockItem->clear();
											$logsuccess=$this->StockItem->save($newStockItemData);
											if (!$logsuccess) {
												echo "problema al guardar el lote";
												pr($this->validateErrors($this->StockItem));
												throw new Exception();
											}

											// STEP 2: SAVE THE STOCK MOVEMENT
											$newStockMovementData=array();
											$message="Vendió lote ".$product_name." (Usado:".$quantity_used.", total para venta:".$product_quantity.") para Venta ".$order_code;
											
											$newStockMovementData['movement_date']=$sale_date;
											$newStockMovementData['bool_input']=false;
											$newStockMovementData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$order_code."_".$product_name;
											$newStockMovementData['description']=$message;
											$newStockMovementData['order_id']=$id;
											$newStockMovementData['stockitem_id']=$stockitem_id;
											$newStockMovementData['product_id']=$product_id;
											$newStockMovementData['product_quantity']=$quantity_used;
											$newStockMovementData['product_unit_price']=$product_unit_price;
											$newStockMovementData['product_total_price']=$product_unit_price*$quantity_used;
											$newStockMovementData['production_result_code_id']=$production_result_code_id;
											//pr($newStockMovementData);
											
											//$this->StockMovement->clear();
											$this->StockMovement->create();
											$logsuccess=$this->StockMovement->save($newStockMovementData);
											if (!$logsuccess) {
												echo "problema al guardar el movimiento de salida";
												pr($this->validateErrors($this->StockMovement));
												throw new Exception();
											}
											unset($newStockItemData);
											//unset($newStockMovementData);
											
											// STEP 3: SAVE THE STOCK ITEM LOG
											$this->recreateStockItemLogs($stockitem_id);
											
											// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
											$this->recordUserActivity($this->Session->read('User.username'),$message);
										}
									}
								}
								
								//echo "productos guardados";
								$datasource->commit();
								$this->recordUserAction();
								// SAVE THE USERLOG FOR THE PURCHASE
								//echo "committed";
								$this->recordUserActivity($this->Session->read('User.username'),"Venta número ".$this->request->data['Order']['order_code']." editada");
								//echo "userlog written away";
								$this->Session->setFlash(__('Se guardó la venta.'),'default',array('class' => 'success'));
								//echo "starting to redirect to action viewSale for order id ".$id;
								return $this->redirect(array('controller' => 'orders','action' => 'verVenta',$id));
							} 
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('No se podía guardar la venta.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
							}
						}
					}
				}
				
			}
		} 
		else {
			$options = array(
				'conditions' => array(
					'Order.id' => $id,
				),
				'contain'=>array(
					'StockMovement'=>array(
						'StockItem',
					),
				),
			);
			$this->request->data = $this->Order->find('first', $options);
			if (!empty($this->request->data['StockMovement'])){
				$warehouseId=$this->request->data['StockMovement'][0]['StockItem']['warehouse_id'];
				foreach ($this->request->data['StockMovement'] as $stockMovement){
					//pr($stockMovement);
					$productArray=array();
					$productArray['product_id']=$stockMovement['product_id'];
					$productArray['raw_material_id']=$stockMovement['StockItem']['raw_material_id'];
					$productArray['production_result_code_id']=$stockMovement['production_result_code_id'];
					$productArray['product_quantity']=$stockMovement['product_quantity'];
					$productArray['product_unit_price']=$stockMovement['product_unit_price'];
					$productArray['product_total_price']=$stockMovement['product_total_price'];
					$requestProducts[]['Product']=$productArray;
				}
			}
		}
		$this->set(compact('warehouseId'));
		$this->set(compact('requestProducts'));
		
		$subtotalNoInvoice=0;
		//pr($this->request->data);
		$bool_invoicetype_editable=true;
		if (!empty($this->request->data['Invoice'][0])){
			if ($this->request->data['Invoice'][0]['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('list',array(
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$this->request->data['Invoice'][0]['id'],
						'CashReceiptInvoice.amount >'=>0,
					),
				));
				if (count($cashReceiptsForInvoice)>0){
					$bool_invoicetype_editable=false;
				}
			}
		}
		elseif (!empty($this->request->data['Invoice'])){
			if ($this->request->data['Invoice']['bool_credit']){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptsForInvoice=$this->CashReceiptInvoice->find('list',array(
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$this->request->data['Invoice']['id'],
						'CashReceiptInvoice.amount >'=>0,
					),
				));
				if (count($cashReceiptsForInvoice)>0){
					$bool_invoicetype_editable=false;
				}
			}
		}
		else {
			foreach($productsSold as $productSold){
				$subtotalNoInvoice=$productSold['StockMovement']['total_product_price'];
			}
		}
		$this->set(compact('subtotalNoInvoice'));
		
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions'=>array(
				'OR'=>array(
					array(
						'ThirdParty.bool_provider'=>false,
						'ThirdParty.bool_active'=>true,
					),
					array(
						'ThirdParty.id'=>$this->request->data['Order']['third_party_id'],
					),
				),
			),
			'order'=>'ThirdParty.company_name',			
		));
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		$this->Product->recursive=-1;
		// Remove the raw products from the dropdown list
		// 20170419 RESTRICT STOCKITEMS DIRECTLY TO LIMIT RETRIEVED INFO
		if (is_array($this->request->data['Order']['order_date'])){
			$orderDateArray=$this->request->data['Order']['order_date'];
			$orderDateString=$orderDateArray['year'].'-'.$orderDateArray['month'].'-'.$orderDateArray['day'];
			$orderDate=date("Y-m-d",strtotime($orderDateString));
			$orderDatePlusOne=date("Y-m-d",strtotime($orderDateString."+1 days"));
		}
		else {
			$orderDate=date("Y-m-d",strtotime($this->request->data['Order']['order_date']));
			$orderDatePlusOne=date("Y-m-d",strtotime($this->request->data['Order']['order_date']."+1 days"));
		}
		$this->set(compact('orderDate'));
		
		$productsAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('id','remaining_quantity','raw_material_id'),
						'conditions'=>array(
							'StockItem.remaining_quantity >'=>0,
							'StockItem.stockitem_creation_date <'=>$orderDatePlusOne,
						),
					),
				),
				'order'=>'product_type_id DESC, name ASC',
			)
		);
		$products = array();
		$rawmaterialids=array();
		foreach ($productsAll as $product){
			// only show products that are in inventory
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0||in_array($stockitem['id'],$stockItemsOfSoldProducts)){
						// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
						// in this case the associative array just contains the product_id because otherwise the list would become very long
						$products[$product['Product']['id']]=$product['Product']['name'];
						$rawmaterialids[]=$stockitem['raw_material_id'];
					}
				}
			}
		}
		
		$this->loadModel('ProductionResultCode');
		$productionResultCodes=$this->ProductionResultCode->find('list');
		$preformasAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('remaining_quantity')
					)
				),
				'conditions' => array(
					'ProductType.product_category_id ='=> CATEGORY_RAW,
					'Product.id'=>$rawmaterialids
				)
			)
		);
		$rawMaterials=array();
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
		}
		
		//20170419 THIS SHOULD SHOW THE INVENTORY ON THE DAY OF THE ORDER 
		//20170419 THIS SHOULD SHOW THE INVENTORY FOR THE SELECTED WAREHOUSE 
		
		$this->loadModel('ProductType');
		$productcategoryid=CATEGORY_PRODUCED;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$finishedMaterialsInventory =array();
		//$finishedMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$finishedMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$warehouseId);
		
		$productcategoryid=CATEGORY_OTHER;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		//$otherMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$otherMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$warehouseId);
		
		$productcategoryid=CATEGORY_RAW;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		//$rawMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$rawMaterialsInventory = $this->StockItem->getInventoryTotalsByDate($productcategoryid,$producttypeids,$orderDate,$warehouseId);
		
		$currencies = $this->Currency->find('list');
		//$accountingCodes = $this->AccountingCode->find('list',array('fields'=>array('AccountingCode.id','AccountingCode.shortfullname')));
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			)
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			)
		));
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','rawMaterials','productsSold','currencies','accountingCodes','invoice','bool_first_load','bool_invoicetype_editable'));
		
		$this->loadModel('ExchangeRate');
		$saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
		$this->loadModel('Warehouse');
		$warehouses=$this->Warehouse->find('list',array(
			'order'=>'Warehouse.name',
		));
		$this->set(compact('warehouses'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function editarRemision($id = null) {
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
				
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('CashReceiptType');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterCashReceipt');
		$this->loadModel('AccountingMovement');
		
		$this->StockMovement->virtualFields['total_product_quantity']=0;
		$this->StockMovement->virtualFields['total_product_price']=0;
		$productsSold = $this->StockMovement->find('all',array(
			'fields'=>array('product_unit_price','SUM(StockMovement.product_quantity) AS StockMovement__total_product_quantity, SUM(product_total_price) AS StockMovement__total_product_price, StockMovement.product_unit_price, Product.id, Product.name, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'),
			'conditions'=>array('StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'),
			'group'=>array('Product.id, StockItem.raw_material_id, ProductionResultCode.code'),
		));
		
		$bool_first_load=true;
		$cashReceipt=$this->CashReceipt->find('first',array(
			'conditions'=>array(
				'CashReceipt.order_id'=>$id,
			)
		));
		//pr($productsSold);
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
		//pr($this->request->data);
		if ($this->request->is(array('post', 'put'))) {
			$bool_first_load=false;
			
			$remission_date=$this->request->data['Order']['order_date'];
			$remissionDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
			
			$remissionDateArray=array();
			$remissionDateArray['year']=$remission_date['year'];
			$remissionDateArray['month']=$remission_date['month'];
			$remissionDateArray['day']=$remission_date['day'];
			
			$order_code=$this->request->data['Order']['order_code'];
			$namedRemissions=$this->Order->find('all',array(
				'conditions'=>array(
					'order_code'=>$order_code,
					'stock_movement_type_id'=>MOVEMENT_SALE,
					'Order.id !='=>$id,
				)
			));
			
			if (count($namedRemissions)>0){
				$this->Session->setFlash(__('Ya existe una remisión con el mismo código!  No se guardó la remisión.'), 'default',array('class' => 'error-message'));
			}
			elseif ($remissionDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de remisión no puede estar en el futuro!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
			}
			elseif ($remissionDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se puede realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			elseif ($this->request->data['CashReceipt']['bool_annulled']){
				$datasource=$this->Order->getDataSource();
				$oldCashReceipt=$this->CashReceipt->find('first',array(
					'conditions'=>array(
						'CashReceipt.order_id'=>$id,
					)
				));
				try {
					// first remove existing data: cash receipt, accounting registers, accounting register cash receipt
					$oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
						'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
						'conditions'=>array(
							'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
						)
					));
					
					if (!empty($oldAccountingRegisterCashReceipts)){
						foreach ($oldAccountingRegisterCashReceipts as $oldAccountingRegisterCashReceipt){
							// first remove the movement
							$oldAccountingMovements=$this->AccountingMovement->find('all',array(
								'fields'=>array('AccountingMovement.id'),
								'conditions'=>array(
									'accounting_register_id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
								)
							));
							if (!empty($oldAccountingMovements)){
								foreach ($oldAccountingMovements as $oldAccountingMovement){
									$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
								}
							}
							// then remove the register
							$this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
							// then remove the register cash receipt link
							$this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
						}
					}
					// then remove the cash receipt
					$this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id']);
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',array('class' => 'error-message'));
				}
				// then save the minimum data for the annulled invoice/order				
				try {
					//pr($this->request->data);
					$datasource->begin();
					
					$OrderData=array();
					$OrderData['Order']['bool_annulled']=true;
					$OrderData['Order']['stock_movement_type_id']=MOVEMENT_SALE;
					$OrderData['Order']['order_date']=$this->request->data['Order']['order_date'];
					$OrderData['Order']['order_code']=$this->request->data['Order']['order_code'];
					$OrderData['Order']['third_party_id']=$this->request->data['Order']['third_party_id'];
					$OrderData['Order']['total_price']=0;
			
					if (!$this->Order->save($OrderData)) {
						echo "Problema guardando el orden de salida";
						pr($this->validateErrors($this->Order));
						throw new Exception();
					}
					
					$order_id=$this->Order->id;
					
					$this->CashReceipt->create();
					
					$CashReceiptData=array();
					$CashReceiptData['CashReceipt']['order_id']=$order_id;
					$CashReceiptData['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
					$CashReceiptData['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
					$CashReceiptData['CashReceipt']['bool_annulled']=true;
					$CashReceiptData['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
					$CashReceiptData['CashReceipt']['cash_receipt_type_id']=CASH_RECEIPT_TYPE_REMISSION;
					$CashReceiptData['CashReceipt']['amount']=0;
					$CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
			
					if (!$this->CashReceipt->save($CashReceiptData)) {
						echo "Problema guardando la factura";
						pr($this->validateErrors($this->CashReceipt));
						throw new Exception();
					}
					
					$datasource->commit();
						
					// SAVE THE USERLOG 
					$this->recordUserActivity($this->Session->read('User.username'),"Se anuló la remisión con número ".$this->request->data['Order']['order_code']);
					$this->Session->setFlash(__('Se anuló la remisión.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'indexVentasRemisiones'));

				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar la remisión.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
				}
			}
			else if ($this->request->data['CashReceipt']['cashbox_accounting_code_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una remisión!  No se guardó la venta.'), 'default',array('class' => 'error-message'));
			}
			else {
				$this->request->data['Order']['id']=$id;
				$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
				// get the relevant information of the purchase that was just saved
				$order_id=$id;
				$remission_date=$this->request->data['Order']['order_date'];
				$order_code=$this->request->data['Order']['order_code'];		
				
				$saleItemsOK=true;
				$exceedingItems="";
				$productCount=0;
				$productsAlreadySold=array();
				$productsNew=array();
				
				foreach ($this->request->data['Product'] as $product){
					//pr($product);
					$soldEarlier=false;	
					// keep track of number of rows so that in case of an error jquery displays correct number of rows again
					if ($product['product_id']>0){
						$productCount++;
					}
					// only process lines where product_quantity has been filled out
					if ($product['product_quantity']>0){
						
						$productid = $product['product_id'];
						
						$productquantity=$product['product_quantity'];
						$productunitprice = $product['product_unit_price'];
						$producttotalprice = $product['product_total_price'];
						$productionresultcodeid = $product['production_result_code_id'];
						$rawmaterialid = $product['raw_material_id'];
						$productsInStock=array();
						if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
							//echo "for finishedproduct";
							$productsInStock = $this->StockItem->find('all',array(
								'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
								'conditions' => array(
									'StockItem.product_id'=> $productid,
									'StockItem.production_result_code_id'=> $productionresultcodeid,
									'StockItem.raw_material_id'=> $rawmaterialid,
									'StockItem.stockitem_creation_date <'=> $remissionDateAsString,
								),
								'group' => array('Product.name')
							));
						}
						else {
							//echo "for tapones";
							$productsInStock = $this->StockItem->find('all',array(
								'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
								'conditions' => array(
									'StockItem.product_id'=> $productid,
									'StockItem.stockitem_creation_date <'=> $remissionDateAsString,
								),
								'group' => array('Product.name')
							));
						}
						//pr($productsInStock);
						if (!empty($productsInStock)){
							//echo "let's check out what is in stock";
							//pr($productsInStock);
							$quantityInStock=$productsInStock[0][0]['remaining'];
						}
						else {
							$quantityInStock=0;
						}
						$quantitySoldAlready=0;
						$soldAlready=false;
						$currentstockmovementid=0;
						foreach ($productsSold as $productSold){
							if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
								if ($productid==$productSold['Product']['id'] && $productionresultcodeid==$productSold['StockMovement']['production_result_code_id'] && $rawmaterialid==$productSold['StockItem']['raw_material_id']){
									$soldAlready=true;
									$quantitySoldAlready=$productSold['StockMovement']['total_product_quantity'];
								}
							}
							else {
								if ($productid==$productSold['Product']['id']){
									$soldAlready=true;
									$quantitySoldAlready=$productSold['StockMovement']['total_product_quantity'];
								}
							}
						}
						// check how many more items would be needed
						$quantityNeeded=$productquantity-$quantitySoldAlready;
						$linkedProduct=$this->Product->read(null,$productid);
						$productname=$linkedProduct['Product']['name'];
						//compare the quantity requested and the quantity in stock
						if ($quantityNeeded>$quantityInStock){
							$saleItemsOK=false;
							$exceedingItems.=__("Para producto ".$productname." la cantidad requerida (".$quantityNeeded.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
						}
					}
				}
				if ($exceedingItems!=""){
					$exceedingItems.=__("Please correct and try again!");
				}
				if (!$saleItemsOK) {
					$this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',array('class' => 'error-message'));
				}				
				else{
					$datasource=$this->Order->getDataSource();
					// retrieve the original stockMovements that were involved in the sale
					$stockMovementsPreviousSale=$this->Order->StockMovement->find('all',
						array(
							'fields'=>'StockMovement.product_id,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
							'contain'=>array(
								'StockItem'=>array(
									'fields'=> array('remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'),
									'StockItemLog'=>array(
										'fields'=>array('StockItemLog.id,StockItemLog.stockitem_date'),
									)
								),
								'Product'=>array(
									'fields'=> array('id','name'),
								)
							),
							'conditions' => array(
								'StockMovement.order_id'=> $id
							)
						)
					);				
					
					// first bring back everything to original state
					$removedOK=false;
					$oldAccountingRegisterCode="";
					$datasource->begin();
					try {
						foreach ($stockMovementsPreviousSale as $previousStockMovement){						
							// set all stockmovements to 0
							$oldStockMovementData=array();
							$oldStockMovementData['id']=$previousStockMovement['StockMovement']['id'];
							$oldStockMovementData['description']=$previousStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
							$oldStockMovementData['product_quantity']=0;
							$oldStockMovementData['product_total_price']=0;
							
							$this->StockMovement->clear();
							$logsuccess=$this->StockMovement->save($oldStockMovementData);
							if (!$logsuccess) {
								echo "problema al guardar el movimiento de remisión";
								pr($this->validateErrors($this->StockMovement));
								throw new Exception();
							}
							
							// restore the stockitems to their previous level
							$oldStockItemData=array();
							$oldStockItemData['id']=$previousStockMovement['StockItem']['id'];
							$oldStockItemData['description']=$previousStockMovement['StockItem']['description']." added back quantity ".$previousStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
							$oldStockItemData['remaining_quantity']=$previousStockMovement['StockItem']['remaining_quantity']+$previousStockMovement['StockMovement']['product_quantity'];
							
							$this->StockItem->clear();
							$logsuccess=$this->StockItem->save($oldStockItemData);
							if (!$logsuccess) {
								echo "problema al guardar el lote";
								pr($this->validateErrors($this->StockItem));
								throw new Exception();
							}
							
							unset($oldStockMovementData);
							unset($oldStockItemData);
							
							$this->recreateStockItemLogs($previousStockMovement['StockItem']['id']);
							// find all StockItemLogs for this stockitem which have a date greater than or equal to the movement date
							/*
							$allStockItemLogs=$this->StockItemLog->find('all',
								array(
									'conditions'=>array(
										'StockItemLog.stockitem_id'=>$previousStockMovement['StockItem']['id'],
										'StockItemLog.stockitem_date >='=>$previousStockMovement['StockMovement']['movement_date'],
									),
								)
							);
							
							$oldStockItemLogData=array();
							foreach ($allStockItemLogs as $stockItemLog){						
								$oldStockItemLogData['id']=$stockItemLog['StockItemLog']['id'];
								$oldStockItemLogData['product_quantity']=$oldStockItemData['remaining_quantity']+$previousStockMovement['StockMovement']['product_quantity'];
							
								$this->StockItemLog->clear();
								$logsuccess=$this->StockItemLog->save($oldStockItemLogData);
								if (!$logsuccess) {
									echo "problema al guardar el estado de lote";
									pr($this->validateErrors($this->StockItemLog));
									throw new Exception();
								}
							}
							unset($oldStockItemLogData);
							*/
						}
						
						// first remove existing data: invoice, accounting registers, accounting register invoice
						$oldCashReceipt=$this->CashReceipt->find('first',array(
							'conditions'=>array(
								'CashReceipt.order_id'=>$id,
							)
						));
						if (!empty($oldCashReceipt)){
							// MODIFIED 20160310 ONLY ONE ACCOUNTINGREGISTERCASHRECEIPT PER CASH RECEIPT
							$oldAccountingRegisterCashReceipt=$this->AccountingRegisterCashReceipt->find('first',array(
								'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
								'conditions'=>array(
									'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
								)
							));
							
							if (!empty($oldAccountingRegisterCashReceipt)){
								// first remove the movement
								$oldAccountingMovements=$this->AccountingMovement->find('all',array(
									'fields'=>array('AccountingMovement.id'),
									'conditions'=>array(
										'accounting_register_id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
									)
								));
								if (!empty($oldAccountingMovements)){
									foreach ($oldAccountingMovements as $oldAccountingMovement){
										$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
									}
								}
								$oldAccountingRegister=$this->AccountingRegister->find('first',array(
									'conditions'=>array(
										'AccountingRegister.id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
									),
								));
								if (!empty($oldAccountingRegister)){
									$oldAccountingRegisterCode=$oldAccountingRegister['AccountingRegister']['register_code'];						
								}
								
								// then remove the register
								$this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
								// then remove the register invoice link
								$this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
								
							}
							// then remove the invoice
							$this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id']);						
						}
						$removedOK=true;
						
						$datasource->commit();
						$this->recordUserActivity($this->Session->read('User.username'),"Old sale data removed for ".$this->request->data['Order']['order_code']);
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('Los datos de la remisión no se podían remover.'), 'default',array('class' => 'error-message'));
					}				
					//echo "everything back to original state";
					if ($removedOK){
						$datasource->begin();
						try	{
							$currency_id=$this->request->data['CashReceipt']['currency_id'];
							
							$total_cash_receipt=$this->request->data['CashReceipt']['amount'];
							
							if ($currency_id==CURRENCY_USD){
								$this->request->data['Order']['total_price']=$total_cash_receipt*$this->request->data['Order']['exchange_rate'];
							}
							else {
								$this->request->data['Order']['total_price']=$total_cash_receipt;
							}
							if (!$this->Order->save($this->request->data)) {
								echo "problema al guardar la venta";
								pr($this->validateErrors($this->Order));
								throw new Exception();
							}
							
							$order_id=$this->Order->id;
							$order_code=$this->request->data['Order']['order_code'];
						
							$this->CashReceipt->create();
							$this->request->data['CashReceipt']['order_id']=$order_id;
							$this->request->data['CashReceipt']['receipt_code']=$this->request->data['Order']['order_code'];
							$this->request->data['CashReceipt']['receipt_date']=$this->request->data['Order']['order_date'];
							$this->request->data['CashReceipt']['client_id']=$this->request->data['Order']['third_party_id'];
					
							if (!$this->CashReceipt->save($this->request->data)) {
								echo "Problema guardando la remisión";
								pr($this->validateErrors($this->CashReceipt));
								throw new Exception();
							}
							
							$cash_receipt_id=$this->CashReceipt->id;						
							// now prepare the accounting registers
							
							if ($currency_id==CURRENCY_USD){
								$this->loadModel('ExchangeRate');
								$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($remissionDateAsString);
								$total_CS=round($total_cash_receipt*$applicableExchangeRate['ExchangeRate']['rate'],2);
							}
							else {
								$total_CS=$total_cash_receipt;
							}
							
							$accountingRegisterData['AccountingRegister']['concept']="Remisión Orden".$order_code;
							$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
							if (!empty($oldAccountingRegisterCode)){
								$registerCode=$oldAccountingRegisterCode;
							}
							else {
								$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
							}
							$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;		
							$accountingRegisterData['AccountingRegister']['register_date']=$remissionDateArray;
							$accountingRegisterData['AccountingRegister']['amount']=$total_CS;
							$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
							
							$accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['CashReceipt']['cashbox_accounting_code_id'];
							$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['cashbox_accounting_code_id']);
							$accountingRegisterData['AccountingMovement'][0]['concept']="Remisión ".$order_code;
							$accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
							$accountingRegisterData['AccountingMovement'][0]['debit_amount']=$total_CS;
							
							$accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_VENTA;
							$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_VENTA);
							$accountingRegisterData['AccountingMovement'][1]['concept']="Remisión Orden".$order_code;
							$accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
							$accountingRegisterData['AccountingMovement'][1]['credit_amount']=$total_CS;
							
							//pr($accountingRegisterData);
							$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
							$this->recordUserAction($this->AccountingRegister->id,"add",null);
							//echo "accounting register saved for cuentas cobrar clientes<br/>";
					
							$AccountingRegisterCashReceiptData=array();
							$AccountingRegisterCashReceiptData['accounting_register_id']=$accounting_register_id;
							$AccountingRegisterCashReceiptData['cash_receipt_id']=$cash_receipt_id;
							$this->AccountingRegisterCashReceipt->create();
							if (!$this->AccountingRegisterCashReceipt->save($AccountingRegisterCashReceiptData)) {
								pr($this->validateErrors($this->AccountingRegisterCashReceipt));
								echo "problema al guardar el lazo entre asiento contable y factura";
								throw new Exception();
							}
							//echo "link accounting register sale saved<br/>";					
							
							foreach ($this->request->data['Product'] as $product){
								if ($product['product_id']>0&&$product['product_quantity']>0){
									//pr($product);

									$product_id = $product['product_id'];
									
									$production_result_code_id = $product['production_result_code_id'];
									$raw_material_id = $product['raw_material_id'];
									
									$product_unit_price=$product['product_unit_price'];
									$product_quantity = $product['product_quantity'];
									
									if ($currency_id==CURRENCY_USD){
										$product_unit_price*=$this->request->data['Order']['exchange_rate'];
									}
									
									// get the related product data
									$linkedProduct=$this->Product->read(null,$product_id);
									$product_name=$linkedProduct['Product']['name'];
									
									// STEP 1: SAVE THE STOCK ITEM(S)
									// first prepare the materials that will be taken out of stock
									if ($this->Product->getProductCategoryId($product_id)==CATEGORY_PRODUCED){
										$usedMaterials= $this->StockItem->getFinishedMaterialsForSale($product_id,$production_result_code_id,$product_quantity,$raw_material_id,$remissionDateAsString);
									}
									else {
										$usedMaterials= $this->StockItem->getOtherMaterialsForSale($product_id,$product_quantity,$remissionDateAsString);		
									}
									
									//echo "got sales materials";
									
									//pr($usedMaterials);
									for ($k=0;$k<count($usedMaterials);$k++){
										$materialUsed=$usedMaterials[$k];
										$stockitem_id=$materialUsed['id'];
										$quantity_present=$materialUsed['quantity_present'];
										$quantity_used=$materialUsed['quantity_used'];
										$quantity_remaining=$materialUsed['quantity_remaining'];
										
										if (!$this->StockItem->exists($stockitem_id)) {
											throw new NotFoundException(__('Invalid Purchase'));
										}
										
										$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$stockitem_id)));
										//pr($stockItem);
										$message="Sold stockitem ".$product_name." (Quantity:".$quantity_used.") for Sale ".$order_code;
										$newStockItemData=array();
										$newStockItemData['id']=$stockitem_id;
										$newStockItemData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$order_code."_".$product_name;
										$newStockItemData['description']=$stockItem['StockItem']['description']."|".$message;
										$newStockItemData['remaining_quantity']=$quantity_remaining;
										
										$this->StockItem->clear();
										$logsuccess=$this->StockItem->save($newStockItemData);
										if (!$logsuccess) {
											echo "problema al guardar el lote";
											pr($this->validateErrors($this->StockItem));
											throw new Exception();
										}

										// STEP 2: SAVE THE STOCK MOVEMENT
										$newStockMovementData=array();
										$message="Vendió lote ".$product_name." (Usado:".$quantity_used.", total para venta:".$product_quantity.") para Venta ".$order_code;
										
										$newStockMovementData['movement_date']=$remission_date;
										$newStockMovementData['bool_input']=false;
										$newStockMovementData['name']=$remission_date['day'].$remission_date['month'].$remission_date['year']."_".$order_code."_".$product_name;
										$newStockMovementData['description']=$message;
										$newStockMovementData['order_id']=$id;
										$newStockMovementData['stockitem_id']=$stockitem_id;
										$newStockMovementData['product_id']=$product_id;
										$newStockMovementData['product_quantity']=$quantity_used;
										$newStockMovementData['product_unit_price']=$product_unit_price;
										$newStockMovementData['product_total_price']=$product_unit_price*$quantity_used;
										$newStockMovementData['production_result_code_id']=$production_result_code_id;
										//pr($newStockMovementData);
										
										$this->StockMovement->clear();
										$this->StockMovement->create();
										$logsuccess=$this->StockMovement->save($newStockMovementData);
										if (!$logsuccess) {
											echo "problema al guardar el movimiento de remisión";
											pr($this->validateErrors($this->StockMovement));
											throw new Exception();
										}
										unset($newStockItemData);
										unset($newStockMovementData);
										
										// STEP 3: SAVE THE STOCK ITEM LOG
										$this->recreateStockItemLogs($stockitem_id);
										
										// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
										$this->recordUserActivity($this->Session->read('User.username'),$message);
									}
								}
							}
							
							$datasource->commit();
							$this->recordUserAction();
							// SAVE THE USERLOG FOR THE PURCHASE
							//echo "committed";
							$this->recordUserActivity($this->Session->read('User.username'),"Venta número ".$this->request->data['Order']['order_code']." editada");
							//echo "userlog written away";
							$this->Session->setFlash(__('Se guardó la venta.'),'default',array('class' => 'success'));
							//echo "starting to redirect to action viewSale for order id ".$id;
							return $this->redirect(array('action' => 'verRemision',$id));
						} 
						catch(Exception $e){
							$datasource->rollback();
							pr($e);
							$this->Session->setFlash(__('No se podía guardar la venta.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
						}
					}
				}
			}
		} 
		else {
			$options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
			$this->request->data = $this->Order->find('first', $options);
		}
		
		$thirdParties = $this->Order->ThirdParty->find('list',array(
			'conditions'=>array(
				'OR'=>array(
					array(
						'ThirdParty.bool_provider'=>false,
						'ThirdParty.bool_active'=>true,
					),
					array(
						'ThirdParty.id'=>$this->request->data['Order']['third_party_id'],
					),
				),
			),
			'order'=>'ThirdParty.company_name',		
		));
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		$this->Product->recursive=2;
		// Remove the raw products from the dropdown list
		$productsAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('remaining_quantity','raw_material_id')
					),
				),
				'order'=>'product_type_id DESC, name ASC',
			)
		);
		$products = array();
		$rawmaterialids=array();
		foreach ($productsAll as $product){
			// only show products that are in inventory
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0){
						// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
						// in this case the associative array just contains the product_id because otherwise the list would become very long
						$products[$product['Product']['id']]=$product['Product']['name'];
						$rawmaterialids[]=$stockitem['raw_material_id'];
					}
				}
			}
		}
		
		$this->loadModel('ProductionResultCode');
		$productionResultCodes=$this->ProductionResultCode->find('list');
		$preformasAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('remaining_quantity')
					)
				),
				'conditions' => array(
					'ProductType.product_category_id ='=> CATEGORY_RAW,
					'Product.id'=>$rawmaterialids
				)
			)
		);
		$rawMaterials=array();
		foreach ($preformasAll as $preforma){
			$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
		}
		$this->loadModel('ProductType');
		$productcategoryid=CATEGORY_PRODUCED;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$finishedMaterialsInventory =array();
		$finishedMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$productcategoryid=CATEGORY_OTHER;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$otherMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$productcategoryid=CATEGORY_RAW;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		
		$currencies = $this->Currency->find('list');
		$cashReceiptTypes = $this->CashReceiptType->find('list');
		
		//$accountingCodes = $this->AccountingCode->find('list',array('fields'=>array('AccountingCode.id','AccountingCode.shortfullname')));
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			)
		));
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			)
		));
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','rawMaterials','productsSold','currencies','accountingCodes','cashReceipt','bool_first_load','cashReceiptTypes'));
		
		$this->loadModel('ExchangeRate');
		//$orderDate=date( "Y-m-d");
		$saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
		$orderExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($saleDateAsString);
		$exchangeRateOrder=$orderExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateOrder'));
		
		$this->loadModel('Warehouse');
		$warehouses=$this->Warehouse->find('list',array(
			'order'=>'Warehouse.name',
		));
		$this->set(compact('warehouses'));
		
		$aco_name="Orders/crearVenta";		
		$bool_sale_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_add_permission'));
		$aco_name="Orders/editarVenta";		
		$bool_sale_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_edit_permission'));
		$aco_name="Orders/anularVenta";		
		$bool_sale_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_sale_annul_permission'));
		
		$aco_name="Orders/crearRemision";		
		$bool_remission_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_add_permission'));
		$aco_name="Orders/editarRemision";		
		$bool_remission_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_edit_permission'));
		$aco_name="Orders/anularRemision";		
		$bool_remission_annul_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_remission_annul_permission'));
		
		$aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}

	public function eliminarEntrada($id = null) {
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Entrada inválida'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('StockMovement');
		$this->loadModel('StockItem');
		$this->loadModel('StockItemLog');
		
		$entry=$this->Order->find('first', array(
			'conditions' => array(
				'Order.id' => $id,
			),
			'contain'=>array(
				'StockMovement'=>array(
					'StockItem'=>array(
						'ProductionMovement'=>array(
							'conditions'=>array(
								'ProductionMovement.product_quantity >'=>0,
							),
							'ProductionRun',
						),
						'StockMovement'=>array(
							'conditions'=>array(
								'StockMovement.product_quantity >'=>0,
								'StockMovement.order_id !='=>$id,
							),
							'Order',
						),	
					),
				),
			),
		));
		$flashMessage="";
		$boolDeletionAllowed=true;
		if (!empty($entry['StockMovement'])){
			foreach ($entry['StockMovement'] as $stockMovement){
				if (!empty($stockMovement['StockItem']['ProductionMovement'])){
					$boolDeletionAllowed=false;
				}
				if (!empty($stockMovement['StockItem']['StockMovement'])){
					$boolDeletionAllowed=false;
				}
			}
			if (!$boolDeletionAllowed){
				$flashMessage.="Los productos de la entrada no se pueden editar porque ya se han utilizado en ";
				foreach ($entry['StockMovement'] as $stockMovement){
					foreach ($stockMovement['StockItem']['ProductionMovement'] as $productionMovement){						
						if (!empty($productionMovement['ProductionRun'])){
							//pr($productionMovement['ProductionRun']);
							$flashMessage.="orden de producción ".$productionMovement['ProductionRun']['production_run_code']." ";
						}
						else {
							//pr($productionMovement);
						}
						
					}
					foreach ($stockMovement['StockItem']['StockMovement'] as $stockMovement){
						//pr($productionMovement['StockItem']['StockMovement']);
						if (!$stockMovement['bool_transfer']){
							if (!empty($stockMovement['Order'])){
								//pr($productionMovement['StockItem']['StockMovement']['Order']);
								$flashMessage.=$stockMovement['Order']['order_code']." ";
							}
							else {
								//pr($productionMovement['StockItem']['StockMovement']);
							}
						}
						else {
							$flashMessage.=$stockMovement['transfer_code']." ";
						}
					}
				}
			}
		}
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó la entrada.";
			$this->Session->setFlash($flashMessage, 'default',array('class' => 'error-message'));
			return $this->redirect(array('action' => 'verEntrada',$id));
		}
		else {
			$datasource=$this->Order->getDataSource();
			$datasource->begin();
			try {
				//delete all stockMovements, stockItems and stockItemLogs
				foreach ($entry['StockMovement'] as $stockMovement){
					//pr($stockMovement['StockItem']);
				
					if (!$this->StockMovement->delete($stockMovement['id'])) {
						echo "Problema al eliminar el movimiento de entrada en bodega";
						pr($this->validateErrors($this->StockMovement));
						throw new Exception();
					}
					
					if (!empty($stockMovement['StockItem']['StockItemLog'])){
						foreach ($stockMovement['StockItem']['StockItemLog'] as $stockItemLog){
							if (!$this->StockItemLog->delete($stockItemLog['id'])) {
								echo "Problema al eliminar el estado de lote";
								pr($this->validateErrors($this->StockItemLog));
								throw new Exception();
							}
						}
					}
					
					if (!empty($stockMovement['StockItem']['id'])){
						if (!$this->StockItem->delete($stockMovement['StockItem']['id'])) {
							echo "Problema al eliminar el lote de bodega";
							pr($this->validateErrors($this->StockItem));
							throw new Exception();
						}
					}
				}			
					
				if (!$this->Order->delete($id)) {
					echo "Problema al eliminar la entrada";
					pr($this->validateErrors($this->Order));
					throw new Exception();
				}
						
				$datasource->commit();
			/*
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=array();
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$entry['Order']['id'];
				$deletionArray['Deletion']['reference']=$entry['Order']['order_code'];
				$deletionArray['Deletion']['type']='Order';
				$this->Deletion->save($deletionArray);
			*/			
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la entrada número ".$entry['Order']['order_code']);
						
				$this->Session->setFlash(__('Se eliminó la entrada.'),'default',array('class' => 'success'));				
				return $this->redirect(array('action' => 'index'));
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la entrada.'), 'default',array('class' => 'error-message'));
				return $this->redirect(array('action' => 'verEntrada',$id));
			}
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
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
		$linkedSale=$this->Order->read(null,$id);
		$order_code=$linkedSale['Order']['order_code'];
		$this->loadModel('StockItem');
		
		$this->loadModel('Invoice');
		$this->loadModel('CashReceipt');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterInvoice');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$this->request->allowMethod('post', 'delete');
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			// find stock movements for order
			$stockMovements=$this->Order->StockMovement->find('all',array(
				'fields'=>array('stockitem_id','product_quantity','StockMovement.id'),
				'conditions'=>array('order_id'=>$id),
			));
			
			// reestablish stockitem quantity
			foreach ($stockMovements as $stockMovement){
				$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['StockMovement']['stockitem_id'])));
				
				$stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
				$stockItem['StockItem']['description'].="|eliminated sale ".$order_code;
				$success=$this->StockItem->save($stockItem);
				if (!$success) {
					echo "problema eliminando el estado de lote";
					pr($this->validateErrors($this->StockItem));
					throw new Exception();
				}
				
				// delete stockmovements
				$this->Order->StockMovement->id=$stockMovement['StockMovement']['id'];
				$success=$this->Order->StockMovement->delete();
				if (!$success) {
					echo "problema eliminando el movimiento de lote";
					pr($this->validateErrors($this->Order->StockMovement));
					throw new Exception();
				}
			}
			
			$oldInvoice=$this->Invoice->find('first',array(
				'conditions'=>array(
					'Invoice.order_id'=>$id,
				)
			));
			if (!empty($oldInvoice)){
				// first remove existing data: invoice, accounting registers, accounting register invoice				
				$oldAccountingRegisterInvoices=$this->AccountingRegisterInvoice->find('all',array(
					'fields'=>array('AccountingRegisterInvoice.id','AccountingRegisterInvoice.accounting_register_id'),
					'conditions'=>array(
						'invoice_id'=>$oldInvoice['Invoice']['id']
					)
				));
				
				if (!empty($oldAccountingRegisterInvoices)){
					foreach ($oldAccountingRegisterInvoices as $oldAccountingRegisterInvoice){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id']);
						// then remove the register invoice link
						$this->AccountingRegisterInvoice->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['id']);
					}
				}
				// then remove the invoice
				$success=$this->Invoice->delete($oldInvoice['Invoice']['id']);
				if (!$success) {
					echo "problema al eliminar la factura";
					pr($this->validateErrors($this->Invoice));
					throw new Exception();
				}			
			}
			
			$oldCashReceipt=$this->CashReceipt->find('first',array(
				'conditions'=>array(
					'CashReceipt.order_id'=>$id,
				)
			));
			if (!empty($oldCashReceipt)){
				// first remove existing data: cash receipt, accounting registers, accounting register cash receipt				
				$oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
					'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
					'conditions'=>array(
						'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
					)
				));
				
				if (!empty($oldAccountingRegisterCashReceipts)){
					foreach ($oldAccountingRegisterCashReceipts as $oldAccountingRegisterCashReceipt){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
						// then remove the register cash receipt link
						$this->AccountingRegisterInvoice->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
					}
				}
				// then remove the cash receipt
				$success=$this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id']);
				if (!$success) {
					echo "problema al eliminar el recibo de caja";
					pr($this->validateErrors($this->CashReceipt));
					throw new Exception();
				}			
			}
			
			
			// delete order
			$success=$this->Order->delete();
			if (!$success) {
				echo "problema al eliminar la venta";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
			
			//recreate stockitemlogs
			foreach ($stockMovements as $stockMovement){
				$this->recreateStockItemLogs($stockMovement['StockMovement']['stockitem_id']);
			}

			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Order removed with code ".$order_code);			
			$this->Session->setFlash(__('The sale has been deleted.'), 'default',array('class' => 'success'));
		} 		
		catch(Exception $e){
			$datasource->rollback();
			pr($e);					
			$this->Session->setFlash(__('The sale could not be deleted. Please, try again.'), 'default',array('class' => 'error-message'));
		}
		return $this->redirect(array('action' => 'resumenVentasRemisiones'));
	}
	
	
	
	public function anularVenta ($id=null){
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
		$linkedSale=$this->Order->read(null,$id);
		$order_code=$linkedSale['Order']['order_code'];
		$this->loadModel('StockItem');
		
		$this->loadModel('Invoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterInvoice');
		
		$this->request->allowMethod('post', 'delete');
		
		$oldInvoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.order_id'=>$id,
			),
		));
		
		$datasource=$this->Order->getDataSource();
		$datasource->begin();
		try {
			$stockMovements=$this->Order->StockMovement->find('all',array(
				'fields'=>array('stockitem_id','product_quantity','StockMovement.id'),
				'conditions'=>array('order_id'=>$id),
			));
			
			// reestablish stockitem quantity
			foreach ($stockMovements as $stockMovement){
				$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['StockMovement']['stockitem_id'])));
				
				$stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
				$stockItem['StockItem']['description'].="|eliminated sale ".$order_code;
				$success=$this->StockItem->save($stockItem);
				if (!$success) {
					echo "problema eliminando el estado de lote";
					pr($this->validateErrors($this->StockItem));
					throw new Exception();
				}
				
				// delete stockmovements
				$this->Order->StockMovement->id=$stockMovement['StockMovement']['id'];
				$success=$this->Order->StockMovement->delete();
				if (!$success) {
					echo "problema eliminando el movimiento de lote";
					pr($this->validateErrors($this->Order->StockMovement));
					throw new Exception();
				}
			}
		
			// first remove existing data: cash receipt, accounting registers, accounting register cash receipt
			$oldAccountingRegisterInvoices=array();
			if (!empty($oldInvoice)){
				$oldAccountingRegisterInvoices=$this->AccountingRegisterInvoice->find('all',array(
					'fields'=>array('AccountingRegisterInvoice.id','AccountingRegisterInvoice.accounting_register_id'),
					'conditions'=>array(
						'invoice_id'=>$oldInvoice['Invoice']['id']
					)
				));
			
				if (!empty($oldAccountingRegisterInvoices)){
					foreach ($oldAccountingRegisterInvoices as $oldAccountingRegisterInvoice){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['accounting_register_id']);
						// then remove the register invoice link
						$this->AccountingRegisterInvoice->delete($oldAccountingRegisterInvoice['AccountingRegisterInvoice']['id']);
					}
				}
				// then remove the invoice
				$this->Invoice->delete($oldInvoice['Invoice']['id']);
			}
			
			//recreate stockitemlogs
			foreach ($stockMovements as $stockMovement){
				$this->recreateStockItemLogs($stockMovement['StockMovement']['stockitem_id']);
			}
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',array('class' => 'error-message'));
		}
		// then save the minimum data for the annulled invoice/order				
		$datasource->begin();
		try {
			//pr($this->request->data);
			$OrderData=array();
			$OrderData['Order']['id']=$id;
			$OrderData['Order']['bool_annulled']=true;
			$OrderData['Order']['total_price']=0;
			$this->Order->id=$id;
	
			if (!$this->Order->save($OrderData)) {
				echo "Problema anulando la remisión";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
			
			$order_id=$this->Order->id;
			$this->Order->recursive=-1;
			$linkedOrder=$this->Order->find('first',array('conditions'=>array('Order.id'=>$order_id)));
			//pr($linkedOrder);		
			$this->Invoice->create();
			$InvoiceData=array();
			$InvoiceData['Invoice']['order_id']=$order_id;
			$InvoiceData['Invoice']['order_code']=$linkedOrder['Order']['order_code'];
			$InvoiceData['Invoice']['invoice_date']=date( 'Y-m-d', strtotime($linkedOrder['Order']['order_date']));
			$InvoiceData['Invoice']['bool_annulled']=true;
			$InvoiceData['Invoice']['client_id']=$linkedOrder['Order']['third_party_id'];
			$InvoiceData['Invoice']['total_price']=0;
			$InvoiceData['Invoice']['currency_id']=CURRENCY_CS;
	
			if (!$this->Invoice->save($InvoiceData)) {
				echo "Problema guardando la factura";
				pr($this->validateErrors($this->Invoice));
				throw new Exception();
			}
			$datasource->commit();
				
			// SAVE THE USERLOG 
			$this->recordUserActivity($this->Session->read('User.username'),"Se anuló la venta con número ".$linkedOrder['Order']['order_code']);
			$this->Session->setFlash(__('Se anuló la venta.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
			return $this->redirect(array('action' => 'resumenVentasRemisiones'));

		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('No se podía anular la venta.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
		}
	}
	
	public function anularRemision ($id=null){
		$this->Order->id = $id;
		if (!$this->Order->exists()) {
			throw new NotFoundException(__('Invalid order'));
		}
		$linkedRemission=$this->Order->read(null,$id);
		$order_code=$linkedRemission['Order']['order_code'];
		$this->loadModel('StockItem');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$this->request->allowMethod('post', 'delete');
		
		$datasource=$this->Order->getDataSource();
		$oldCashReceipt=$this->CashReceipt->find('first',array(
			'conditions'=>array(
				'CashReceipt.order_id'=>$id,
			)
		));
		try {
			$stockMovements=$this->Order->StockMovement->find('all',array(
				'fields'=>array('stockitem_id','product_quantity','StockMovement.id'),
				'conditions'=>array('order_id'=>$id),
			));
			
			// reestablish stockitem quantity
			foreach ($stockMovements as $stockMovement){
				$stockItem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['StockMovement']['stockitem_id'])));
				
				$stockItem['StockItem']['remaining_quantity']+=$stockMovement['StockMovement']['product_quantity'];
				$stockItem['StockItem']['description'].="|eliminated sale ".$order_code;
				$success=$this->StockItem->save($stockItem);
				if (!$success) {
					echo "problema eliminando el estado de lote";
					pr($this->validateErrors($this->StockItem));
					throw new Exception();
				}
				
				// delete stockmovements
				$this->Order->StockMovement->id=$stockMovement['StockMovement']['id'];
				$success=$this->Order->StockMovement->delete();
				if (!$success) {
					echo "problema eliminando el movimiento de lote";
					pr($this->validateErrors($this->Order->StockMovement));
					throw new Exception();
				}
			}
		
			// first remove existing data: cash receipt, accounting registers, accounting register cash receipt
			$oldAccountingRegisterCashReceipts=array();
			if (!empty($oldCashReceipt)){
				$oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
					'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
					'conditions'=>array(
						'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
					)
				));
			
				if (!empty($oldAccountingRegisterCashReceipts)){
					foreach ($oldAccountingRegisterCashReceipts as $oldAccountingRegisterCashReceipt){
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
						// then remove the register cash receipt link
						$this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
					}
				}
				// then remove the cash receipt
			
				$this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id']);
			}
			
			//recreate stockitemlogs
			foreach ($stockMovements as $stockMovement){
				$this->recreateStockItemLogs($stockMovement['StockMovement']['stockitem_id']);
			}
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',array('class' => 'error-message'));
		}
		// then save the minimum data for the annulled invoice/order				
		try {
			//pr($this->request->data);
			$datasource->begin();
			
			$OrderData=array();
			$OrderData['Order']['id']=$id;
			$OrderData['Order']['bool_annulled']=true;
			$OrderData['Order']['total_price']=0;
			$this->Order->id=$id;
	
			if (!$this->Order->save($OrderData)) {
				echo "Problema anulando la remisión";
				pr($this->validateErrors($this->Order));
				throw new Exception();
			}
			
			$order_id=$this->Order->id;
			$this->Order->recursive=-1;
			$linkedOrder=$this->Order->find('first',array('conditions'=>array('Order.id'=>$order_id)));
			//pr($linkedOrder);		
			$this->CashReceipt->create();
			
			$CashReceiptData=array();
			$CashReceiptData['CashReceipt']['order_id']=$order_id;
			$CashReceiptData['CashReceipt']['receipt_code']=$linkedOrder['Order']['order_code'];
			$CashReceiptData['CashReceipt']['receipt_date']=date( 'Y-m-d', strtotime($linkedOrder['Order']['order_date']));
			$CashReceiptData['CashReceipt']['bool_annulled']=true;
			$CashReceiptData['CashReceipt']['client_id']=$linkedOrder['Order']['third_party_id'];
			$CashReceiptData['CashReceipt']['cash_receipt_type_id']=CASH_RECEIPT_TYPE_REMISSION;
			$CashReceiptData['CashReceipt']['amount']=0;
			$CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
	
			if (!$this->CashReceipt->save($CashReceiptData)) {
				echo "Problema anulando el recibo de caja para la remisión anulada";
				pr($this->validateErrors($this->CashReceipt));
				throw new Exception();
			}
			
			$datasource->commit();
				
			// SAVE THE USERLOG 
			$this->recordUserActivity($this->Session->read('User.username'),"Se anuló la remisión con número ".$linkedOrder['Order']['order_code']);
			$this->Session->setFlash(__('Se anuló la remisión.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
			return $this->redirect(array('action' => 'resumenVentasRemisiones'));

		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('No se podía anular la remisión.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
		}
		
	}
	
	public function verReporteCierre($startDate = null,$endDate=null) {
		$bool_bottles=false;
	
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$bool_bottles=$this->request->data['Report']['report_type'];
		}
		else{
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->loadModel('StockMovement');
		$this->loadModel('ThirdParty');
		$clients=$this->ThirdParty->find('all',
			array(
				'fields'=>array('id','company_name'),
				'conditions'=>array(
					'bool_provider'=>false,
					'bool_active'=>true,
				),
			)
		);
		
		// get the relevant time period
		$startDateDay=date("d",strtotime($startDate));
		$startDateMonth=date("m",strtotime($startDate));
		$startDateYear=date("Y",strtotime($startDate));
		$endDateDay=date("d",strtotime($endDate));
		$endDateMonth=date("m",strtotime($endDate));
		$endDateYear=date("Y",strtotime($endDate));
		
		//echo $startDateDay."<br/>";
		//echo $startDateMonth."<br/>";
		//echo $startDateYear."<br/>";
		//echo $endDateDay."<br/>";
		//echo $endDateMonth."<br/>";
		//echo $endDateYear."<br/>";
		$monthArray=array();
		$counter=0;
		for ($yearCounter=$startDateYear;$yearCounter<=$endDateYear;$yearCounter++){
			if ($yearCounter==$startDateYear && $yearCounter==$endDateYear){
				// only 1 year in consideration
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=$endDateMonth;
			}
			else if($yearCounter==$startDateYear){
				// starting year (not the same as ending year)
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=12;
			}
			else if ($yearCounter==$endDateYear){
				// ending year (not the same as starting year)
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=$endDateMonth;
			}
			else {
				// in between year
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=12;
			}
			for ($monthCounter=$startingMonth;$monthCounter<=$endingMonth;$monthCounter++){
				$monthArray[$counter]['period']=$monthCounter.'_'.$yearCounter;
				if ($monthCounter==$startDateMonth && $yearCounter == $startDateYear){
					$monthArray[$counter]['start']=$startingDay;
				}
				else {
					$monthArray[$counter]['start']=1;
				}
				$monthArray[$counter]['month']=$monthCounter;
				$monthArray[$counter]['year']=$yearCounter;
				$counter++;
			}
		}
		
		$salesArray=array();
		$clientCounter=0;
		$totalSale=0;
		if (!$bool_bottles){
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$salesCounter=0;
				$totalForClient=0;
				$salesArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$salesArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
					$saleForMonthForClient=$this->Order->find('first',
						array(
							'fields'=>array('SUM(total_price) as totalSale'),
							'conditions'=>array(
								'stock_movement_type_id'=>MOVEMENT_SALE,
								'order_date >='=> $saleStartDate,
								'order_date <'=> $saleEndDate,
								'third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					$salesArray[$clientCounter]['sales'][$salesCounter]['period']=$period;
					
					$salesArray[$clientCounter]['sales'][$salesCounter]['total']=$saleForMonthForClient[0]['totalSale'];
					if (!empty($saleForMonthForClient)){
						$totalForClient+=$saleForMonthForClient[0]['totalSale'];
						$totalSale+=$saleForMonthForClient[0]['totalSale'];
					}
					$salesCounter++;
				}
				$salesArray[$clientCounter]['totalForClient']=$totalForClient;
			}
		}		
		//echo "totalSale is ".$totalSale."<br/>";
		usort($salesArray,array($this,'sortByTotalForClient'));
		//pr($salesArray);
		
		$bottlesAArray=array();
		$totalABottles=0;
		$bottlesBCArray=array();
		$totalBCBottles=0;
		if ($bool_bottles){
			$clientCounter=0;
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$bottlesCounter=0;
				$totalForClient=0;
				$bottlesAArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$bottlesAArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));

					$bottlesForMonthForClient=$this->StockMovement->find('first',
						array(
							'fields'=>array('SUM(product_quantity) as totalBottles'),
							'conditions'=>array(
								'StockMovement.production_result_code_id'=>1,
								'StockMovement.bool_reclassification'=>false,
								'StockMovement.product_quantity >'=>0,
								'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								'Order.order_date >='=> $saleStartDate,
								'Order.order_date <'=> $saleEndDate,
								'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					// if ($clients[$clientCounter]['ThirdParty']['id']==19){
						// $bottlesForProcasa=$this->StockMovement->find('all',array(
							// 'fields'=>array(
								// 'StockMovement.product_quantity',
							// ),
							// 'conditions'=>array(
								// 'StockMovement.production_result_code_id'=>1,
								// 'StockMovement.bool_reclassification'=>false,
								// 'StockMovement.product_quantity >'=>0,
								// 'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								// 'Order.order_date >='=> $saleStartDate,
								// 'Order.order_date <'=> $saleEndDate,
								// 'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							// ),
							// 'contain'=>array(
								// 'Order'=>array(
									// 'fields'=>array(
										// 'Order.order_code','Order.order_date',
									// ),
								// ),
							// ),
						// ));
						// //pr($bottlesForProcasa);
					// }
					$bottlesAArray[$clientCounter]['bottles'][$bottlesCounter]['period']=$period;					
					$bottlesAArray[$clientCounter]['bottles'][$bottlesCounter]['totalBottles']=$bottlesForMonthForClient[0]['totalBottles'];
					if (!empty($bottlesForMonthForClient)){
						$totalForClient+=$bottlesForMonthForClient[0]['totalBottles'];
						$totalABottles+=$bottlesForMonthForClient[0]['totalBottles'];
					}
					$bottlesCounter++;
				}
				$bottlesAArray[$clientCounter]['totalForClient']=$totalForClient;
			}
			$clientCounter=0;
			for ($clientCounter=0;$clientCounter<count($clients);$clientCounter++){
				$bottlesCounter=0;
				$totalForClient=0;
				$bottlesBCArray[$clientCounter]['clientid']=$clients[$clientCounter]['ThirdParty']['id'];
				$bottlesBCArray[$clientCounter]['clientname']=$clients[$clientCounter]['ThirdParty']['company_name'];
				for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
					$period=$monthArray[$salePeriod]['period'];
					$start=$monthArray[$salePeriod]['start'];
					$month=$monthArray[$salePeriod]['month'];
					$nextmonth=($month==12)?1:($month+1);
					$year=$monthArray[$salePeriod]['year'];
					$nextyear=($month==12)?($year+1):$year;
					$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
					$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));

					$bottlesForMonthForClient=$this->StockMovement->find('first',
						array(
							'fields'=>array('SUM(product_quantity) as totalBottles'),
							'conditions'=>array(
								'StockMovement.production_result_code_id >'=>1,
								'StockMovement.bool_reclassification'=>false,
								'Order.stock_movement_type_id'=>MOVEMENT_SALE,
								'Order.order_date >='=> $saleStartDate,
								'Order.order_date <'=> $saleEndDate,
								'Order.third_party_id'=>$clients[$clientCounter]['ThirdParty']['id']
							)
						)
					);
					
					$bottlesBCArray[$clientCounter]['bottles'][$bottlesCounter]['period']=$period;					
					$bottlesBCArray[$clientCounter]['bottles'][$bottlesCounter]['totalBottles']=$bottlesForMonthForClient[0]['totalBottles'];
					if (!empty($bottlesForMonthForClient)){
						$totalForClient+=$bottlesForMonthForClient[0]['totalBottles'];
						$totalBCBottles+=$bottlesForMonthForClient[0]['totalBottles'];
					}
					$bottlesCounter++;
				}
				$bottlesBCArray[$clientCounter]['totalForClient']=$totalForClient;
			}
		}
		
		usort($bottlesAArray,array($this,'sortByTotalForClient'));
		usort($bottlesBCArray,array($this,'sortByTotalForClient'));
		
		$this->set(compact('clients','monthArray','salesArray','bottlesAArray','bottlesBCArray','startDate','endDate','totalSale','totalABottles','totalBCBottles','bool_bottles'));
	}
	
	public function sortByTotalForClient($a,$b ){ 
	  if( $a['totalForClient'] == $b['totalForClient'] ){ return 0 ; } 
	  return ($a['totalForClient'] < $b['totalForClient']) ? 1 : -1;
	} 
	
	public function guardarReporteCierre() {
		$exportData=$_SESSION['reporteCierre'];
		$this->set(compact('exportData'));
	}
	
	public function verVentasPorCliente($id=0){
		$this->loadModel('StockItem');
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
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
		}
		else {
			//$startDate = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		// get the relevant time period
		$startDateDay=date("d",strtotime($startDate));
		$startDateMonth=date("m",strtotime($startDate));
		$startDateYear=date("Y",strtotime($startDate));
		$endDateDay=date("d",strtotime($endDate));
		$endDateMonth=date("m",strtotime($endDate));
		$endDateYear=date("Y",strtotime($endDate));
		
		$monthArray=array();
		$counter=0;
		for ($yearCounter=$startDateYear;$yearCounter<=$endDateYear;$yearCounter++){
			if ($yearCounter==$startDateYear && $yearCounter==$endDateYear){
				// only 1 year in consideration
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=$endDateMonth;
			}
			else if($yearCounter==$startDateYear){
				// starting year (not the same as ending year)
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=12;
			}
			else if ($yearCounter==$endDateYear){
				// ending year (not the same as starting year)
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=$endDateMonth;
			}
			else {
				// in between year
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=12;
			}
			for ($monthCounter=$startingMonth;$monthCounter<=$endingMonth;$monthCounter++){
				$monthArray[$counter]['period']=$monthCounter.'_'.$yearCounter;
				if ($monthCounter==$startDateMonth && $yearCounter == $startDateYear){
					$monthArray[$counter]['start']=$startingDay;
				}
				else {
					$monthArray[$counter]['start']=1;
				}
				$monthArray[$counter]['month']=$monthCounter;
				$monthArray[$counter]['year']=$yearCounter;
				$counter++;
			}
		}
		//pr($monthArray);
		$this->loadModel('ThirdParty');
		$client=$this->ThirdParty->find('first',array('conditions'=>array('ThirdParty.id'=>$id)));
		
		$salesArray=array();
		$salesCounter=0;
		$totalQuantityProduced=0;
		$totalQuantityOther=0;
		$totalSale=0;
		$totalCost=0;
		$totalProfit=0;
		
		for ($salePeriod=0;$salePeriod<count($monthArray);$salePeriod++){
			$period=$monthArray[$salePeriod]['period'];
			$start=$monthArray[$salePeriod]['start'];
			$month=$monthArray[$salePeriod]['month'];
			//echo "saleperiod is ".$salePeriod."<br/>";
			//echo "month is ".$month."<br/>";
			$nextmonth=($month==12)?1:($month+1);
			$year=$monthArray[$salePeriod]['year'];
			$nextyear=($month==12)?($year+1):$year;
			$saleStartDate=date('Y-m-d',strtotime($year.'-'.$month.'-'.$start));
			$saleEndDate=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$start));
			$salesForMonthForClient=$this->Order->find('all',
				array(
					'fields'=>array('total_price','order_date','order_code'),
					'conditions'=>array(
						'stock_movement_type_id'=>MOVEMENT_SALE,
						'order_date >='=> $saleStartDate,
						'order_date <'=> $saleEndDate,
						'third_party_id'=>$client['ThirdParty']['id']
					),
					'contain'=>array(
						'ThirdParty'=>array('fields'=>'company_name'),
						'StockMovement'=>array(
							'fields'=>array('id','movement_date','order_id','stockitem_id','product_quantity','product_unit_price','product_total_price'),
							'conditions'=>array('StockMovement.product_quantity >'=>0),
							'Product'=>array(
								'fields'=>array('id','packaging_unit'),	
								'ProductType'=>array('fields'=>'product_category_id'),
								
							)
						)
					),
					'order'=>'Order.order_date ASC, Order.id ASC'
				)
			);
			//pr($salesForMonthForClient);
			
			$totalQuantityProducedMonth=0;
			$totalQuantityOtherMonth=0;
			$totalSaleMonth=0;
			$totalCostMonth=0;
			$totalProfitMonth=0;
			
			$processedSales=array();
			for ($s=0;$s<count($salesForMonthForClient);$s++){
				//pr($salesForMonthForClient[$s]);
				$processedSales[$s]['order_date']=$salesForMonthForClient[$s]['Order']['order_date'];
				$processedSales[$s]['order_id']=$salesForMonthForClient[$s]['Order']['id'];
				$processedSales[$s]['order_code']=$salesForMonthForClient[$s]['Order']['order_code'];
				$amountBottles=0;
				$amountCaps=0;
				$productTotalPrice=0;
				$productTotalCost=0;
				$productTotalUtility=0;
				
				foreach ($salesForMonthForClient[$s]['StockMovement'] as $stockMovement){
					if ($stockMovement['product_quantity']>0){
						if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED){
							$amountBottles+=$stockMovement['product_quantity'];
						}
						else {
							$amountCaps+=$stockMovement['product_quantity'];
						}
						$productTotalPrice+=$stockMovement['product_total_price'];
						$stockItem=$this->StockItem->read(null,$stockMovement['stockitem_id']);
						$productTotalCost+=$stockMovement['product_quantity']*$stockItem['StockItem']['product_unit_price'];
						$productTotalUtility+=($stockMovement['product_total_price']-$stockMovement['product_quantity']*$stockItem['StockItem']['product_unit_price']);
					}
				}
				
				$processedSales[$s]['amount_bottles']=$amountBottles;
				$processedSales[$s]['amount_caps']=$amountCaps;
				$processedSales[$s]['product_total_price']=$productTotalPrice;
				$processedSales[$s]['product_total_cost']=$productTotalCost;
				$processedSales[$s]['product_total_utility']=$productTotalUtility;
			}
			
			foreach ($salesForMonthForClient as $saleForMonth){
				$totalSaleMonth+=$saleForMonth['Order']['total_price'];
				foreach ($saleForMonth['StockMovement'] as $stockMovement){
					//pr($stockMovement);
					if ($stockMovement['Product']['ProductType']['product_category_id']==CATEGORY_PRODUCED){
						$totalQuantityProducedMonth+=$stockMovement['product_quantity'];
					}
					else {
						$totalQuantityOtherMonth+=$stockMovement['product_quantity'];
					}
					$relatedStockitem=$this->StockItem->find('first',array('conditions'=>array('StockItem.id'=>$stockMovement['stockitem_id'])));
					$unitcost=$relatedStockitem['StockItem']['product_unit_price'];
					$totalCostMonth+=$stockMovement['product_quantity']*$unitcost;
				}
			}
			$totalProfitMonth=$totalSaleMonth-$totalCostMonth;
			
			$totalQuantityProduced+=$totalQuantityProducedMonth;
			$totalQuantityOther+=$totalQuantityOtherMonth;
			$totalSale+=$totalSaleMonth;
			$totalCost+=$totalCostMonth;
			$totalProfit+=$totalProfitMonth;
			
			//$salesArray[$salesCounter]['clientid']=$client['ThirdParty']['id'];
			$salesArray[$salesCounter]['period']=$period;
			
			$salesArray[$salesCounter]['sales']=$processedSales;
			
			$salesArray[$salesCounter]['totalSaleMonth']=$totalSaleMonth;
			$salesArray[$salesCounter]['totalQuantityProducedMonth']=$totalQuantityProducedMonth;
			$salesArray[$salesCounter]['totalQuantityOtherMonth']=$totalQuantityOtherMonth;
			$salesArray[$salesCounter]['totalCostMonth']=$totalCostMonth;
			$salesArray[$salesCounter]['totalProfitMonth']=$totalProfitMonth;
			
			$salesCounter++;
		}
		//echo "totalSale is ".$totalSale."<br/>";
		
		$totals=array();
		$totals['totalQuantityProduced']=$totalQuantityProduced;
		$totals['totalQuantityOther']=$totalQuantityOther;
		$totals['totalSale']=$totalSale;
		$totals['totalCost']=$totalCost;
		$totals['totalProfit']=$totalProfitMonth;
		
		$this->set(compact('client','monthArray','salesArray','startDate','endDate','totals'));
	}
	
	public function guardarReporteVentasCliente($clientname){
		$exportData=$_SESSION['reporteVentasPorCliente'];
		$this->set(compact('exportData','clientname'));
	}
	
	public function create_pdf($name=null){ 
		$outputcompra = $_SESSION['output_compra'];
		if (empty($name)){
			$name="compra.pdf";
		}
		$this->set(compact('outputcompra','name'));
		$this->layout = '/pdf/default';
		$this->render('/pdf/pdf_compra');
		//$this->redirect(array('action' => 'downloadPdf'),$name);
		//exit();
	}
	
	public function downloadPdf($name=null) { 
		$outputcompra = $_SESSION['output_compra'];
		
		App::import('Vendor','xtcpdf');
		$pdf = new XTCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
		//$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->AddPage();
		$html = $outputcompra;
		$pdf->writeHTML($html, true, false, true, false, '');
		$pdf->lastPage();
		//echo $pdf->Output(APP.'files/pdf'. DS . $name, 'F');
		echo $pdf->Output('E:/' . DS . $name, 'F');
		//echo "pdf 'E:/".DS.$name." generated";
	
		$this->viewClass = 'Media';
		$filename=substr($name,0,strpos($name,"."));
		$params = array(
			'id' => $name,
			'name' => $filename ,
			'download' => true,
			'extension' => 'pdf',
			'path' => 'E:/'
		);
		$this->set($params);
	}
/*
	public function editSaleOriginal($id = null) {
		$this->loadModel('StockItem');
		$this->loadModel('Product');
		$this->loadModel('StockMovement');
		$this->loadModel('StockItemLog');
		$this->loadModel('ClosingDate');
		
		$productsSold = $this->StockMovement->find('all',array(
			'fields'=>array('SUM(StockMovement.product_quantity) AS total_product_quantity, SUM(product_total_price) AS total_product_price, StockMovement.product_unit_price, Product.id, Product.name, StockMovement.production_result_code_id, ProductionResultCode.code, StockItem.raw_material_id'),
			'conditions'=>array('StockMovement.order_id'=>$id,'StockMovement.product_quantity>0'),
			'group'=>array('Product.id, StockItem.raw_material_id, ProductionResultCode.code'),
		));
		
		//pr($productsSold);
		if (!$this->Order->exists($id)) {
			throw new NotFoundException(__('Invalid order'));
		}
		//pr($this->request->data);
		if ($this->request->is(array('post', 'put'))) {
			
			$this->request->data['Order']['id']=$id;
			$this->request->data['Order']['stock_movement_type_id']=MOVEMENT_SALE;
			// get the relevant information of the purchase that was just saved
			$order_id=$id;
			$sale_date=$this->request->data['Order']['order_date'];
			$saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);									
			$order_code=$this->request->data['Order']['order_code'];		
				
			$saleItemsOK=true;
			$exceedingItems="";
			$productCount=0;
			$productsAlreadySold=array();
			$productsNew=array();
			
			$order_code=$this->request->data['Order']['order_code'];
			$namedSales=$this->Order->find('all',array(
				'conditions'=>array(
					'order_code'=>$order_code,
					'stock_movement_type_id'=>MOVEMENT_SALE,
					'Order.id !='=>$id,
				)
			));
			if (count($namedSales)>0){
				$this->Session->setFlash(__('Ya existe una salida con el mismo código!  No se guardó la salida.'), 'default',array('class' => 'error-message'));
			}
			else {
				$sale_date=$this->request->data['Order']['order_date'];
				$saleDateAsString = $this->Order->deconstruct('order_date', $this->request->data['Order']['order_date']);
				$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
				$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
				$closingDate=new DateTime($latestClosingDate);
				if ($saleDateAsString>date('Y-m-d H:i')){
					$this->Session->setFlash(__('La fecha de salida no puede estar en el futuro!  No se guardó la salida.'), 'default',array('class' => 'error-message'));
				}
				elseif ($saleDateAsString<$latestClosingDatePlusOne){
					$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
				}
				else {
					foreach ($this->request->data['Product'] as $product){
						//pr($product);
						$soldEarlier=false;	
						// keep track of number of rows so that in case of an error jquery displays correct number of rows again
						if ($product['product_id']>0){
							$productCount++;
						}
						// only process lines where product_quantity has been filled out
						if ($product['product_quantity']>0){
							$productprice = $product['product_price'];
							$productid = $product['product_id'];
							$productquantity=$product['product_quantity'];
							$productionresultcodeid = $product['production_result_code_id'];
							$rawmaterialid = $product['raw_material_id'];
							$productsInStock=array();
							if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
								//echo "for finishedproduct";
								$productsInStock = $this->StockItem->find('all',array(
									'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
									'conditions' => array(
										'StockItem.product_id'=> $productid,
										'StockItem.production_result_code_id'=> $productionresultcodeid,
										'StockItem.raw_material_id'=> $rawmaterialid,
										'StockItem.stockitem_creation_date <'=> $saleDateAsString,
									),
									'group' => array('Product.name')
								));
							}
							else {
								//echo "for tapones";
								$productsInStock = $this->StockItem->find('all',array(
									'fields'=>'SUM(StockItem.remaining_quantity) AS remaining,Product.name',
									'conditions' => array(
										'StockItem.product_id'=> $productid,
										'StockItem.stockitem_creation_date <'=> $saleDateAsString,
									),
									'group' => array('Product.name')
								));
							}
							//pr($productsInStock);
							if (!empty($productsInStock)){
								//echo "let's check out what is in stock";
								//pr($productsInStock);
								$quantityInStock=$productsInStock[0][0]['remaining'];
							}
							else {
								$quantityInStock=0;
							}
							$quantitySoldAlready=0;
							$soldAlready=false;
							$currentstockmovementid=0;
							foreach ($productsSold as $productSold){
								if ($this->Product->getProductCategoryId($productid)==CATEGORY_PRODUCED){
									if ($productid==$productSold['Product']['id'] && $productionresultcodeid==$productSold['StockMovement']['production_result_code_id'] && $rawmaterialid==$productSold['StockItem']['raw_material_id']){
										$soldAlready=true;
										$quantitySoldAlready=$productSold[0]['total_product_quantity'];
									}
								}
								else {
									if ($productid==$productSold['Product']['id']){
										$soldAlready=true;
										$quantitySoldAlready=$productSold[0]['total_product_quantity'];
									}
								}
							}
							// check how many more items would be needed
							$quantityNeeded=$productquantity-$quantitySoldAlready;
							$linkedProduct=$this->Product->read(null,$productid);
							$productname=$linkedProduct['Product']['name'];
							//compare the quantity requested and the quantity in stock
							if ($quantityNeeded>$quantityInStock){
								$saleItemsOK=false;
								$exceedingItems.=__("Para producto ".$productname." la cantidad requerida (".$quantityNeeded.") excede la cantidad en bodega (".$quantityInStock.")!")."<br/>";
							}
						}
					}
					if ($exceedingItems!=""){
						$exceedingItems.=__("Please correct and try again!");
					}
					if (!$saleItemsOK) {
						$this->Session->setFlash(__('The quantity in stock for the following items is not sufficient.')."<br/>".$exceedingItems, 'default',array('class' => 'error-message'));
					}				
					else{
						$datasource=$this->Order->getDataSource();
						
						// retrieve the original stockMovements that were involved in the sale
						$stockMovementsPreviousSale=$this->Order->StockMovement->find('all',
							array(
								'fields'=>'StockMovement.product_id,StockMovement.product_quantity,StockMovement.stockitem_id,StockMovement.product_total_price,StockMovement.id, StockMovement.description, StockMovement.movement_date',
								'contain'=>array(
									'StockItem'=>array(
										'fields'=> array('remaining_quantity','raw_material_id','production_result_code_id','remaining_quantity','description'),
										'StockItemLog'=>array(
											'fields'=>array('StockItemLog.id,StockItemLog.stockitem_date'),
										)
									),
									'Product'=>array(
										'fields'=> array('id','name'),
									)
								),
								'conditions' => array(
									'StockMovement.order_id'=> $id
								)
							)
						);				
						
						// first bring back everything to original state
						$removedOK=false;
						try {
							$datasource->begin();
							foreach ($stockMovementsPreviousSale as $previousStockMovement){						
								// set all stockmovements to 0
								$oldStockMovementData=array();
								$oldStockMovementData['id']=$previousStockMovement['StockMovement']['id'];
								$oldStockMovementData['description']=$previousStockMovement['StockMovement']['description']." cancelled through editing on ".date('Y-m-d');
								$oldStockMovementData['product_quantity']=0;
								$oldStockMovementData['product_total_price']=0;
								
								$this->StockMovement->clear();
								$logsuccess=$this->StockMovement->save($oldStockMovementData);
								if (!$logsuccess) {
									echo "problema al guardar el movimiento de salida";
									pr($this->validateErrors($this->StockMovement));
									throw new Exception();
								}
								
								// restore the stockitems to their previous level
								$oldStockItemData=array();
								$oldStockItemData['id']=$previousStockMovement['StockItem']['id'];
								$oldStockItemData['description']=$previousStockMovement['StockItem']['description']." added back quantity ".$previousStockMovement['StockMovement']['product_quantity']." through editing on ".date('Y-m-d')." for order ".$id;
								$oldStockItemData['remaining_quantity']=$previousStockMovement['StockItem']['remaining_quantity']+$previousStockMovement['StockMovement']['product_quantity'];
								
								$this->StockItem->clear();
								$logsuccess=$this->StockItem->save($oldStockItemData);
								if (!$logsuccess) {
									echo "problema al guardar el lote";
									pr($this->validateErrors($this->StockItem));
									throw new Exception();
								}
								
								unset($oldStockMovementData);
								unset($oldStockItemData);
								
								$this->recreateStockItemLogs($previousStockMovement['StockItem']['id']);
								// find all StockItemLogs for this stockitem which have a date greater than or equal to the movement date
								
								//$allStockItemLogs=$this->StockItemLog->find('all',
								//	array(
								//		'conditions'=>array(
								//			'StockItemLog.stockitem_id'=>$previousStockMovement['StockItem']['id'],
								//			'StockItemLog.stockitem_date >='=>$previousStockMovement['StockMovement']['movement_date'],
								//		),
								//	)
								//);
								
								//$oldStockItemLogData=array();
								//foreach ($allStockItemLogs as $stockItemLog){						
								//	$oldStockItemLogData['id']=$stockItemLog['StockItemLog']['id'];
								//	$oldStockItemLogData['product_quantity']=$oldStockItemData['remaining_quantity']+$previousStockMovement['StockMovement']['product_quantity'];
								//	
								//	$this->StockItemLog->clear();
								//	$logsuccess=$this->StockItemLog->save($oldStockItemLogData);
								//	if (!$logsuccess) {
								//		echo "problema al guardar el estado de lote";
								//		pr($this->validateErrors($this->StockItemLog));
								//		throw new Exception();
								//	}
								//}
								//unset($oldStockItemLogData);
							}
							$removedOK=true;
							$datasource->commit();
							$this->recordUserActivity($this->Session->read('User.username'),"Old sale data removed for ".$this->request->data['Order']['order_code']);
						}
						catch(Exception $e){
							$datasource->rollback();
							pr($e);
							$this->Session->setFlash(__('Los datos de la salida no se podían remover.'), 'default',array('class' => 'error-message'));
						}				
						//echo "everything back to original state";
						if ($removedOK){
							try	{
								$datasource->begin();
								$logsuccess=$this->Order->save($this->request->data);
								if (!$logsuccess) {
									echo "problema al guardar la salida";
									pr($this->validateErrors($this->Order));
									throw new Exception();
								}
								//echo "salida guardada ya";
								foreach ($this->request->data['Product'] as $product){
									if ($product['product_id']>0&&$product['product_quantity']>0){
										//pr($product);
										$product_price = $product['product_price'];
										$product_id = $product['product_id'];
										$product_quantity=$product['product_quantity'];
										$production_result_code_id = $product['production_result_code_id'];
										$raw_material_id = $product['raw_material_id'];
										
										// calculate the unit price
										$product_unit_price=$product_price/$product_quantity;
										
										// get the related product data
										$linkedProduct=$this->Product->read(null,$product_id);
										$product_name=$linkedProduct['Product']['name'];
										
										// STEP 1: SAVE THE STOCK ITEM(S)
										// first prepare the materials that will be taken out of stock
										if ($this->Product->getProductCategoryId($product_id)==CATEGORY_PRODUCED){
											$usedMaterials= $this->StockItem->getFinishedMaterialsForSale($product_id,$production_result_code_id,$product_quantity,$raw_material_id,$saleDateAsString);
										}
										else {
											$usedMaterials= $this->StockItem->getOtherMaterialsForSale($product_id,$product_quantity,$saleDateAsString);		
										}
										
										//echo "got sales materials";
										
										//pr($usedMaterials);
										for ($k=0;$k<count($usedMaterials);$k++){
											$materialUsed=$usedMaterials[$k];
											$stockitem_id=$materialUsed['id'];
											$quantity_present=$materialUsed['quantity_present'];
											$quantity_used=$materialUsed['quantity_used'];
											$quantity_remaining=$materialUsed['quantity_remaining'];
											
											if (!$this->StockItem->exists($stockitem_id)) {
												throw new NotFoundException(__('Invalid Purchase'));
											}
											
											$stockItem=$this->StockItem->find('first',array ('conditions'=>array('StockItem.id'=>$stockitem_id)));
											//pr($stockItem);
											$message="Sold stockitem ".$product_name." (Quantity:".$quantity_used.") for Sale ".$order_code;
											$newStockItemData=array();
											$newStockItemData['id']=$stockitem_id;
											$newStockItemData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$order_code."_".$product_name;
											$newStockItemData['description']=$stockItem['StockItem']['description']."|".$message;
											$newStockItemData['remaining_quantity']=$quantity_remaining;
											
											$this->StockItem->clear();
											$logsuccess=$this->StockItem->save($newStockItemData);
											if (!$logsuccess) {
												echo "problema al guardar el lote";
												pr($this->validateErrors($this->StockItem));
												throw new Exception();
											}

											// STEP 2: SAVE THE STOCK MOVEMENT
											$newStockMovementData=array();
											$message="Sold stockitem ".$product_name." (Used:".$quantity_used.", total for sale:".$product_quantity.") for Sale ".$order_code;
											
											$newStockMovementData['movement_date']=$sale_date;
											$newStockMovementData['bool_input']=false;
											$newStockMovementData['name']=$sale_date['day'].$sale_date['month'].$sale_date['year']."_".$order_code."_".$product_name;
											$newStockMovementData['description']=$message;
											$newStockMovementData['order_id']=$id;
											$newStockMovementData['stockitem_id']=$stockitem_id;
											$newStockMovementData['product_id']=$product_id;
											$newStockMovementData['product_quantity']=$quantity_used;
											$newStockMovementData['product_unit_price']=$product_unit_price;
											$newStockMovementData['product_total_price']=$product_unit_price*$quantity_used;
											$newStockMovementData['production_result_code_id']=$production_result_code_id;
											//pr($newStockMovementData);
											
											$this->StockMovement->clear();
											$this->StockMovement->create();
											$logsuccess=$this->StockMovement->save($newStockMovementData);
											if (!$logsuccess) {
												echo "problema al guardar el movimiento de salida";
												pr($this->validateErrors($this->StockMovement));
												throw new Exception();
											}
											unset($newStockItemData);
											unset($newStockMovementData);
											
											// STEP 3: SAVE THE STOCK ITEM LOG
											$this->recreateStockItemLogs($stockitem_id);
											
											//$stockmovement_id=$this->Order->StockMovement->id;
											//$this->loadModel('StockItemLog');
											//$newStockItemLogData=array();
											//$newStockItemLogData['stockitem_id']=$stockitem_id;
											//$newStockItemLogData['stock_movement_id']=$stockmovement_id;
											//$newStockItemLogData['stockitem_date']=$sale_date;
											//$newStockItemLogData['product_id']=$product_id;
											//$newStockItemLogData['product_unit_price']=$product_unit_price;
											//$newStockItemLogData['product_quantity']=$quantity_remaining;
											
											//$this->StockItemLog->clear();
											//$this->StockItemLog->create();
											//$logsuccess=$this->StockItemLog->save($newStockItemLogData);
											//if (!$logsuccess) {
											//	echo "problema al guardar el estado de lote";
											//	pr($this->validateErrors($this->StockItemLog));
											//	throw new Exception();
											//}
											//unset($newStockItemLogData);
											
											// STEP 4: SAVE THE USERLOG FOR THE STOCK MOVEMENT
											$this->recordUserActivity($this->Session->read('User.username'),$message);
										}
									}
								}
								
								$datasource->commit();
								// SAVE THE USERLOG FOR THE PURCHASE
								//echo "committed";
								$this->recordUserActivity($this->Session->read('User.username'),"Sale edited with invoice code ".$this->request->data['Order']['order_code']);
								//echo "userlog written away";
								$this->Session->setFlash(__('The sale has been saved.'),'default',array('class' => 'success'));
								//echo "starting to redirect to action viewSale for order id ".$id;
								return $this->redirect(array('action' => 'viewSale',$id));
							} 
							catch(Exception $e){
								$datasource->rollback();
								pr($e);
								$this->Session->setFlash(__('The sale could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
							}
						}
					}

				}
			
			}
		} 
		else {
			$options = array('conditions' => array('Order.' . $this->Order->primaryKey => $id));
			$this->request->data = $this->Order->find('first', $options);
		}
		$thirdParties = $this->Order->ThirdParty->find('list');
		$stockMovementTypes = $this->Order->StockMovementType->find('list');
		$this->Product->recursive=2;
		// Remove the raw products from the dropdown list
		$productsAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('remaining_quantity','raw_material_id')
					)
				),
				'order'=>'product_type_id DESC, name ASC'
				//'conditions' => array(
				//	'ProductType.product_category_id !='=> CATEGORY_RAW,
				//)
			)
		);
		$products = array();
		$rawmaterialids=array();
		foreach ($productsAll as $product){
			// only show products that are in inventory
			if ($product['StockItem']!=null){
				foreach ($product['StockItem'] as $stockitem){
					if ($stockitem['remaining_quantity']>0){
						// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
						// in this case the associative array just contains the product_id because otherwise the list would become very long
						$products[$product['Product']['id']]=$product['Product']['name'];
						$rawmaterialids[]=$stockitem['raw_material_id'];
					}
				}
			}
		}
		
		$this->loadModel('ProductionResultCode');
		$productionResultCodes=$this->ProductionResultCode->find('list');
		$preformasAll = $this->Product->find('all',
			array(
				'fields'=>'Product.id,Product.name',
				'contain'=>array(
					'ProductType',
					'StockItem'=>array(
						'fields'=> array('remaining_quantity')
					)
				),
				'conditions' => array(
					'ProductType.product_category_id ='=> CATEGORY_RAW,
					'Product.id'=>$rawmaterialids
				)
			)
		);
		$rawMaterials=array();
		foreach ($preformasAll as $preforma){
			// only show products that are in inventory
			//if ($preforma['StockItem']!=null){
				//if ($preforma['StockItem'][0]['remaining_quantity']>0){
					// create an associative array with product Id + category as index would be possible to avoid the problem of selecting separate categories
					// in this case the associative array just contains the product_id because otherwise the list would become very long
					$rawMaterials[$preforma['Product']['id']]=$preforma['Product']['name'];
				//}
			//}
		}
		$this->loadModel('ProductType');
		$productcategoryid=CATEGORY_PRODUCED;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$finishedMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$productcategoryid=CATEGORY_OTHER;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$otherMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		$productcategoryid=CATEGORY_RAW;
		$producttypeids=$this->ProductType->find('list',array(
			'fields'=>array('ProductType.id'),
			'conditions'=>array('ProductType.product_category_id'=>$productcategoryid)
		));
		$rawMaterialsInventory = $this->StockItem->getInventoryTotals($productcategoryid,$producttypeids);
		
		$this->set(compact('thirdParties', 'stockMovementTypes','products','finishedMaterialsInventory','otherMaterialsInventory','rawMaterialsInventory','productionResultCodes','productCount','rawMaterials','productsSold'));
	}
*/
	
}





