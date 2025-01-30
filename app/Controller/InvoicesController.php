<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class InvoicesController extends AppController {

	public $components = array('Paginator');
	public $helpers = array('PhpExcel'); 
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('getpendinginvoicesforclient','changePaidStatus','getInvoiceCode');		
	}
	public function getpendinginvoicesforclient(){
		$clientid=trim($_POST['clientid']);
		$receiptday=trim($_POST['receiptday']);
		$receiptmonth=trim($_POST['receiptmonth']);
		$receiptyear=trim($_POST['receiptyear']);
		$cashReceiptCurrencyId=trim($_POST['currencyid']);
		$boolRetention=trim($_POST['boolretention']);
		if ($boolRetention=="true"){
			$boolRetention=1;
		}
		else {
			$boolRetention=0;
		}
		
		if (!$clientid){
			throw new NotFoundException(__('Identificación de cliente no es presente'));
		}
		
		$this->layout = "ajax";
		
		$this->loadModel('CashReceiptInvoice');
		$this->loadModel('ExchangeRate');
		
		$receiptDateString=$receiptyear.'-'.$receiptmonth.'-'.$receiptday;
		$receiptDate=date( "Y-m-d", strtotime($receiptDateString));
		$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($receiptDate);
		$exchangeRateCashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
		
		// $this->InvoiceProduct->virtualFields['total_product_quantity']=0;
		$invoicesForClient=$this->Invoice->find('all',array(
			'fields'=>array(
				//'SUM(product_quantity) AS InvoiceProduct__total_product_quantity', 
				'Invoice.id','Invoice.order_id','Invoice.invoice_code','Invoice.invoice_date',
				'Invoice.currency_id','Invoice.due_date',
				// when registering the sale, no cashbox accounting code nor retention should have been registered yet
				// 'Invoice.cashbox_accounting_code_id','Invoice.bool_retention','Invoice.retention_amount','Invoice.retention_number',
				'Invoice.sub_total_price',
				'Invoice.bool_IVA','Invoice.IVA_price',
				'Invoice.total_price',
				'Currency.id','Currency.abbreviation'
			),
			'conditions'=>array(
				'Invoice.client_id'=>$clientid,
				'Invoice.bool_credit'=>true,
				'Invoice.bool_annulled'=>'0',
				'Invoice.bool_paid'=>'0',
			),
			'order'=>'Invoice.invoice_date ASC'
		));
		//pr($invoicesForClient);
		
		for ($i=0;$i<count($invoicesForClient);$i++){
			$totalForInvoice=$invoicesForClient[$i]['Invoice']['total_price'];
			$pendingForInvoice=$totalForInvoice;
			$invoiceCurrencyId=$invoicesForClient[$i]['Invoice']['currency_id'];
			$invoiceDate=$invoicesForClient[$i]['Invoice']['invoice_date'];
			
			// add the retention amount
			// IF STATEMENT ELIMINATED AS WE WANT THE RETENTION INTHE ORIGINAL CURRENCY
			//if ($invoiceCurrencyId==CURRENCY_CS){
				$invoicesForClient[$i]['Invoice']['retention']=round($invoicesForClient[$i]['Invoice']['sub_total_price']*0.02,2);
			//}
			//elseif ($invoiceCurrencyId==CURRENCY_USD){
				// current exchange rate is used 
				//$invoicesForClient[$i]['Invoice']['retention']=round($invoicesForClient[$i]['Invoice']['sub_total_price']*0.02*$exchangeRateCashReceipt,2);
			//}
			// look up the exchange rate difference
			$difference_exchange_rates=0;
			$exchange_rate_difference=0;
			$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoiceDate);
			$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
			if ($invoiceCurrencyId==CURRENCY_USD){
				if (($exchangeRateCashReceipt-$exchangeRateInvoiceDate)>0.00001){
					$difference_exchange_rates=$exchangeRateCashReceipt-$exchangeRateInvoiceDate;
					$exchange_rate_difference=round($totalForInvoice*$difference_exchange_rates,2);
					if ($exchange_rate_difference<0){
						$exchange_rate_difference=0;
					}
				}
			}
			$invoicesForClient[$i]['Invoice']['invoice_exchange_rate']=$exchangeRateInvoiceDate;
			$invoicesForClient[$i]['Invoice']['difference_exchange_rates']=$difference_exchange_rates;
			$invoicesForClient[$i]['Invoice']['exchange_rate_difference']=$exchange_rate_difference;
			
			// get the amount already paid for this invoice
			// NOTICE THAT WE USE RATE OF CURRENT DATE FOR PENDING CALCULATION
			$invoicesForClient[$i]['Invoice']['paid_already_CS']=round($this->Invoice->getAmountPaidAlreadyCS($invoicesForClient[$i]['Invoice']['id']),2);
			
			
			
			
			$diferenciaCambiariaPagado=0;
			if ($invoicesForClient[$i]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$invoicesForClient[$i]['Invoice']['id'],
					),
					'contain'=>array(
						'CashReceipt'=>array(
							'fields'=>array(
								'CashReceipt.id','CashReceipt.receipt_code',
								'CashReceipt.receipt_date',
								'CashReceipt.bool_annulled',
							),
						),
						'Currency'=>array(
							'fields'=>array(
								'Currency.abbreviation','Currency.id',
							),
						),
					),
				));
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoicesForClient[$i]['Invoice']['invoice_date']);
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the total
				$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
				$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						$previousCashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$exchangeRatePreviousCashReceiptDate=$previousCashReceiptExchangeRate['ExchangeRate']['rate'];
						$differenceExchangeRateNowCashReceipt=$exchangeRateCashReceipt-$exchangeRatePreviousCashReceiptDate;
						//echo "difference exchange rate between now and cashreceipt is".$differenceExchangeRateNowCashReceipt."<br/>";
						//echo "payment_credit_CS is".$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']."<br/>";
						//echo "exchange rate previous cash receipts is".$exchangeRatePreviousCashReceiptDate."<br/>";
						//echo "diferenciaCambiariaPagado is".$diferenciaCambiariaPagado."<br/>";
						$diferenciaCambiariaPagado+=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRatePreviousCashReceiptDate;
						//echo "diferenciaCambiariaPagado is ".$diferenciaCambiariaPagado."<br/>";
					}
				}
			}
			$invoicesForClient[$i]['Invoice']['diferencia_cambiaria_pagado']=round($diferenciaCambiariaPagado,2);
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			/*
			// get the pending amounts
			// NOTICE THAT WE USE RATE OF CURRENT DATE FOR PENDING CALCULATION
			// COMMENTED OUT, getPendingAmount gives back the amount in the currency of the Invoice, and we want it in the cash receipt currency
			//$invoicesForClient[$i]['Invoice']['pending']=$this->Invoice->getPendingAmount($invoicesForClient[$i]['Invoice']['id'],$exchangeRateCashReceipt);
			if ($invoiceCurrencyId==$cashreceiptcurrencyid){
				$invoicesForClient[$i]['Invoice']['pending']=$this->Invoice->getPendingAmount($invoicesForClient[$i]['Invoice']['id'],$exchangeRateCashReceipt);
			}
			else {
				if ($invoiceCurrencyId==CURRENCY_CS){
					$invoicesForClient[$i]['Invoice']['pending']=round($this->Invoice->getPendingAmount($invoicesForClient[$i]['Invoice']['id'],$exchangeRateCashReceipt)/$exchangeRateCashReceipt,2);
				}
				else {
					$invoicesForClient[$i]['Invoice']['pending']=round($this->Invoice->getPendingAmount($invoicesForClient[$i]['Invoice']['id'],$exchangeRateCashReceipt)*$exchangeRateCashReceipt,2);
				}
			}
			
			$invoicesForClient[$i]['Invoice']['saldo']=$invoicesForClient[$i]['Invoice']['pending'];
			*/
		}
		
		//pr($invoicesForClient);
		$this->set(compact('invoicesForClient','id','cashReceiptCurrencyId','exchangeRateCashReceipt','boolRetention'));
	}

	public function changePaidStatus($id){
		$this->autoRender='0';
	
		if (!$this->Invoice->exists($id)) {
			throw new NotFoundException(__('Factura no válida'));
		}
		
		$invoice=$this->Invoice->find('first',array(
			'conditions'=>array(
				'Invoice.id'=>$id,
			),
		));
		if (!empty($invoice)){
			$this->Invoice->id=$id;
			$invoiceData['Invoice']['id']=$id;
			$invoiceData['Invoice']['bool_paid']=!$invoice['Invoice']['bool_paid'];
			if ($this->Invoice->save($invoiceData)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('Se cambió el estado de pago de factura '.$invoice['Invoice']['invoice_code'].'.'),'default',array('class' => 'success'));
				return $this->redirect(array('controller'=>'orders','action' => 'verVenta',$invoice['Invoice']['order_id']));
			} 
		}
		
		$this->Session->setFlash(__('No se podía modificar el estado de pagado de la factura.'), 'default',array('class' => 'error-message')); 
		return $this->redirect(Router::url( $this->referer(), true ));		
	}
	
  public function getInvoiceCode(){
    $this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
		$warehouseId=trim($_POST['warehouseId']);
    
    $this->loadModel('Warehouse');
    $warehouseSeries=$this->Warehouse->getWarehouseSeries($warehouseId);
    
    return $this->Invoice->getInvoiceCode($warehouseId,$warehouseSeries);
  }
  
	public function index() {
		$this->Invoice->recursive = 0;
		$this->set('invoices', $this->Paginator->paginate());
	}

	public function verClientesPorCobrar() {
		$this->Invoice->recursive = 0;
		
		$this->loadModel('ExchangeRate');
		
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
		
		$this->loadModel('ThirdParty');
		$this->ThirdParty->recursive=-1;
		$clients=$this->ThirdParty->find('all',[
			'fields'=>[
				'ThirdParty.company_name','ThirdParty.id',
        'ThirdParty.first_name','ThirdParty.last_name',
        'ThirdParty.phone',
			],
			'conditions'=>[
				'bool_provider'=>'0',
				'bool_active'=>true,
			],
			'order'=>'ThirdParty.company_name',
		]);
		//pr($clients);
		
		for ($c=0;$c<count($clients);$c++){
			$pendingInvoices=$this->Invoice->find('all',array(
				'fields'=>array(
					'Invoice.id','Invoice.invoice_code',
					'Invoice.total_price','Invoice.currency_id',
					'Invoice.invoice_date','Invoice.due_date',
					'Invoice.client_id',
					'Currency.abbreviation','Currency.id'
				),
				'conditions'=>array(
					'Invoice.bool_annulled'=>'0',
					'Invoice.bool_paid'=>'0',
					'Invoice.client_id'=>$clients[$c]['ThirdParty']['id'],
					
				),
				'order'=>'Invoice.invoice_date ASC',
			));
			
			$totalPending=0;
			$pendingUnder30=0;
			$pendingUnder45=0;
			$pendingUnder60=0;
			$pendingOver60=0;
			for ($i=0;$i<count($pendingInvoices);$i++){
				$totalForInvoice=$pendingInvoices[$i]['Invoice']['total_price'];
				$totalForInvoiceCS=$totalForInvoice;
				if ($pendingInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
					$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoices[$i]['Invoice']['invoice_date']);
					$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
					$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
				}
				// get the amount already paid for this invoice
				$invoice_paid_already_CS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoices[$i]['Invoice']['id']),2);
				$pendingForInvoice=$totalForInvoiceCS-$invoice_paid_already_CS;
				//if ($pendingInvoices[$i]['Invoice']['client_id']==34){
				//	echo "invoice paid already cs for invoice ".$pendingInvoices[$i]['Invoice']['invoice_code']." is ".$invoice_paid_already_CS."<br/>";
				//	echo "pending for invoice ".$pendingInvoices[$i]['Invoice']['invoice_code']." without diferencia cambiaria is ".$pendingForInvoice."<br/>";
				//}
				if ($pendingInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
					$this->loadModel('CashReceiptInvoice');
					$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
						'conditions'=>array(
							'CashReceiptInvoice.invoice_id'=>$pendingInvoices[$i]['Invoice']['id'],
						),
						'contain'=>array(
							'CashReceipt'=>array(
								'fields'=>array(
									'CashReceipt.id','CashReceipt.receipt_code',
									'CashReceipt.receipt_date',
									'CashReceipt.bool_annulled',
								),
							),
							'Currency'=>array(
								'fields'=>array(
									'Currency.abbreviation','Currency.id',
								),
							),
						),
					));
					$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoices[$i]['Invoice']['invoice_date']);
					$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
					// add the diferencia cambiaria on the total
					$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
					$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
					$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
					$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
					//if ($pendingInvoices[$i]['Invoice']['client_id']==34){
					//	echo "invoice paid already cs for invoice ".$pendingInvoices[$i]['Invoice']['invoice_code']." is ".$invoice_paid_already_CS."<br/>";
					//	echo "pending for invoice ".$pendingInvoices[$i]['Invoice']['invoice_code']." without diferencia cambiaria is ".$pendingForInvoice."<br/>";
					//}
					$pendingForInvoice+=$differenciaCambiariaTotal;
					// add the diferencia cambiaria on the cashreceipts
					if (!empty($cashReceiptInvoices)){
						for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
							$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
							$exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
							$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
							$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
							$pendingForInvoice-=$differenciaCambiariaPaid;
						}
					}
				}
				
				//if ($pendingInvoices[$i]['Invoice']['client_id']==34){
					//echo "pending for invoice ".$pendingInvoices[$i]['Invoice']['invoice_code']." with diferencia cambiaria is ".$pendingForInvoice."<br/>";
				//}
				$totalPending+=$pendingForInvoice;
				$invoiceDate=new DateTime($pendingInvoices[$i]['Invoice']['invoice_date']);
				$dueDate= new DateTime($pendingInvoices[$i]['Invoice']['due_date']);
				$nowDate= new DateTime();
				$daysLate=$nowDate->diff($invoiceDate);
				//echo "factura ".$pendingInvoices[$i]['Invoice']['invoice_code']." ".$daysLate->days."<br/>";
				if ($daysLate->days<31){
					
					$pendingUnder30+=$pendingForInvoice;
				}
				else if ($daysLate->days<46){
					$pendingUnder45+=$pendingForInvoice;
				}
				else if ($daysLate->days<61){
					$pendingUnder60+=$pendingForInvoice;
				}
				else{
					$pendingOver60+=$pendingForInvoice;
				}
			}
			$clients[$c]['saldo']=$totalPending;
			$clients[$c]['pendingUnder30']=$pendingUnder30;
			$clients[$c]['pendingUnder45']=$pendingUnder45;
			$clients[$c]['pendingUnder60']=$pendingUnder60;
			$clients[$c]['pendingOver60']=$pendingOver60;
			$clients[$c]['historicalCredit']=$this->Invoice->getHistoricalCreditForClient($clients[$c]['ThirdParty']['id']);
		}
		
		$this->set(compact('clients'));
	}
	
	public function guardarClientesPorCobrar() {
		$exportData=$_SESSION['clientesPorCobrar'];
		$this->set(compact('exportData'));
	}
	
	public function verHistorialPagos() {
		$client_id=0;
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$client_id=$this->request->data['Report']['client_id'];
		}
		//else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
		//	$startDate=$_SESSION['startDate'];
		//	$endDate=$_SESSION['endDate'];
		//	$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		//}
		else {
			$startDate = date("Y-m-d",strtotime(date("Y-m-01")."-3 months"));
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		//$_SESSION['startDate']=$startDate;
		//$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate','client_id'));
		
		$this->loadModel('ExchangeRate');
		$this->loadModel('ThirdParty');
		$this->loadModel('CashReceiptInvoice');
		
		$this->Invoice->recursive = -1;
		$this->ThirdParty->recursive=-1;
		$this->CashReceiptInvoice->recursive=-1;
		
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
		
		$conditions=array(
			'bool_provider'=>'0',
			'bool_active'=>true,
		);
		if ($client_id>0){
			$conditions[]=array('ThirdParty.id'=>$client_id);
		}
		$selectedClients=$this->ThirdParty->find('all',array(
			'fields'=>array(
				'ThirdParty.company_name','ThirdParty.id',
			),
			'conditions'=>$conditions,
		));
		//pr($clients);
		
		for ($c=0;$c<count($selectedClients);$c++){
			$invoices=$this->Invoice->find('all',array(
				'fields'=>array(
					'Invoice.id','Invoice.invoice_code',
					'Invoice.order_id',
					'Invoice.total_price','Invoice.currency_id',
					'Invoice.invoice_date','Invoice.due_date',
					'Invoice.bool_credit','Invoice.bool_paid',
					'Invoice.sub_total_price','Invoice.IVA_price','Invoice.total_price',
				),
				'conditions'=>array(
					'Invoice.bool_annulled'=>'0',
					'Invoice.client_id'=>$selectedClients[$c]['ThirdParty']['id'],
					'Invoice.invoice_date >='=>$startDate,
					'Invoice.invoice_date <'=>$endDatePlusOne,
				),
				'contain'=>array(
					'Currency'=>array(
						'fields'=>array(
							'Currency.abbreviation','Currency.id',
						),
					),
				),
				'order'=>'Invoice.invoice_date ASC',
			));
			if (!empty($invoices)){
				for ($i=0;$i<count($invoices);$i++){
					if ($invoices[$i]['Invoice']['bool_credit']){
						$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
							'conditions'=>array(
								'CashReceiptInvoice.invoice_id'=>$invoices[$i]['Invoice']['id'],
							),
							'contain'=>array(
								'CashReceipt'=>array(
									'fields'=>array(
										'CashReceipt.id','CashReceipt.receipt_code',
										'CashReceipt.receipt_date',
										'CashReceipt.bool_annulled',
									),
								),
								'Currency'=>array(
									'fields'=>array(
										'Currency.abbreviation','Currency.id',
									),
								),
							),
						));
						$invoices[$i]['cashreceiptpayments']=$cashReceiptInvoices;
						$totalForInvoice=$invoices[$i]['Invoice']['total_price'];
						$totalForInvoiceCS=$totalForInvoice;
						if ($invoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
							$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoices[$i]['Invoice']['invoice_date']);
							$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
							$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
						}
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "total price in C$ for invoice 968 is ".$totalForInvoiceCS."!<br/>";
						//}
						// get the amount already paid for this invoice
						$invoice_paid_already_CS=round($this->Invoice->getAmountPaidAlreadyCS($invoices[$i]['Invoice']['id']),2);
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "total payment in C$ for invoice 968 is ".$invoice_paid_already_CS."!<br/>";
						//}
						
						// this is the pending amount in C$ without taking into account the diff camb
						$pendingForInvoice=$totalForInvoiceCS-$invoice_paid_already_CS;
						if ($invoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
							$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoices[$i]['Invoice']['invoice_date']);
							$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
							// add the diferencia cambiaria on the total
							$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
							$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
							$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
							$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
							$pendingForInvoice+=$differenciaCambiariaTotal;
							// add the diferencia cambiaria on the cashreceipts
							if (!empty($cashReceiptInvoices)){
								for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
									$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
									$exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
									$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
									//if ($invoices[$i]['Invoice']['id']==159){
									//	echo "payment cash receipt dividing the amount paid by the exchange rate of the day".($cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate)."!<br/>";
									//	//pr($cashReceiptInvoices);
									//}
									$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
									$pendingForInvoice-=$differenciaCambiariaPaid;
								}
							}
						}
						
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "cashreceipts for invoice 968!<br/>";
						//	pr($cashReceiptInvoices);
						//}
						
						if ($pendingForInvoice<0){
							$pendingForInvoice=0;
						}
						$invoices[$i]['Invoice']['pendingCS']=$pendingForInvoice;
						
					}
					else {
						$invoices[$i]['Invoice']['pendingCS']=0;
					}
				}
			}
			$selectedClients[$c]['invoices']=$invoices;
			
		}
		$this->set(compact('selectedClients'));
		$clients=$this->ThirdParty->find('list',array(
			'conditions'=>array(
				'bool_provider'=>'0',
				'bool_active'=>true,
			)
		));
		$this->set(compact('clients'));
	}
	
	public function guardarHistorialPagos() {
		$exportData=$_SESSION['historialPagos'];
		$this->set(compact('exportData'));
	}
	
	public function verFacturasPorCobrar($client_id=0) {
		$this->Invoice->recursive = 0;
		
    if ($client_id==0){
      pr($this->Auth->User());
      $client_id=$this->Auth->User('client_id');
    }
    
		$this->loadModel('ExchangeRate');
		$exchangeRateCurrent=$this->ExchangeRate->getApplicableExchangeRateValue(date('Y-m-d'));
		
		$this->loadModel('ThirdParty');
		$client=$this->ThirdParty->find('first',[
      'conditions'=>['ThirdParty.id'=>$client_id],
      'contain'=>['CreditCurrency'],
    ]);
		
		$pendingInvoices=$this->Invoice->find('all',[
			'fields'=>[
				'Invoice.id','Invoice.invoice_code',
				'Order.id','Order.order_code',
				'Invoice.total_price',
				'Invoice.currency_id',
				'Invoice.invoice_date','Invoice.due_date',
				'Client.id','Client.company_name',
        'Currency.abbreviation','Currency.id'
			],
			'conditions'=>[
				'Invoice.bool_annulled'=>'0',
				'Invoice.bool_paid'=>'0',
				'Invoice.client_id'=>$client_id,
			],
			'order'=>'Invoice.invoice_date ASC',
		]);
		
		for ($c=0;$c<count($pendingInvoices);$c++){
			$totalForInvoice=$pendingInvoices[$c]['Invoice']['total_price'];
			$totalForInvoiceCS=$totalForInvoice;
			if ($pendingInvoices[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoices[$c]['Invoice']['invoice_date']);
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
			}
			
			// get the amount already paid for this invoice
			$paidForInvoiceCS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoices[$c]['Invoice']['id']),2);		
			$pendingInvoices[$c]['Invoice']['paidCS']=$paidForInvoiceCS;
			$pendingForInvoiceCS=$totalForInvoiceCS-$paidForInvoiceCS;
			if ($pendingInvoices[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$pendingInvoices[$c]['Invoice']['id'],
					),
					'contain'=>array(
						'CashReceipt'=>array(
							'fields'=>array(
								'CashReceipt.id','CashReceipt.receipt_code',
								'CashReceipt.receipt_date',
								'CashReceipt.bool_annulled',
							),
						),
						'Currency'=>array(
							'fields'=>array(
								'Currency.abbreviation','Currency.id',
							),
						),
					),
				));
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoices[$c]['Invoice']['invoice_date']);
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the total
				$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
				$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
				$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
				$pendingForInvoiceCS+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "payment cash receipt dividing the amount paid by the exchange rate of the day".($cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate)."!<br/>";
						//	//pr($cashReceiptInvoices);
						//}
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForInvoiceCS-=$differenciaCambiariaPaid;
					}
				}
			}
			$pendingInvoices[$c]['Invoice']['pendingCS']=$pendingForInvoiceCS;
			//echo "total for invoice in C$ is ".$totalForInvoiceCS."<br/>";
			//echo "paid for invoice in C$ is ".$paidForInvoiceCS."<br/>";
			//echo "pending for invoice in C$ is ".($totalForInvoiceCS-$paidForInvoiceCS)."<br/>";
			//echo "pending registered for invoice in C$ is ".$pendingInvoices[$c]['Invoice']['pendingCS']."<br/>";
		}
		
		$this->set(compact('pendingInvoices','client','exchangeRateCurrent'));
	}
	
	public function guardarFacturasPorCobrar($clientName="") {
		$exportData=$_SESSION['facturasPorCobrar'];
		$this->set(compact('exportData','clientName'));
	}
	
  public function verCuentasPorPagar($clientId=0) {
		$this->Invoice->recursive = 0;
		
    $roleId=$this->Auth->User('role_id');
    $this->set(compact('roleId'));
    
    if ($clientId==0){
      //pr($this->Auth->User());
      $clientId=$this->Auth->User('client_id');
    }
    
    if ($this->request->is('post')) {
			$clientId=$this->request->data['Report']['client_id'];
		}
		
    $this->set(compact('clientId'));
    //echo "client id is ".$clientId."<br/>";
		$this->loadModel('ExchangeRate');
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
		
		$this->loadModel('ThirdParty');
		$this->ThirdParty->recursive=-1;
    
		$client=$this->ThirdParty->find('first',[
      'conditions'=>['ThirdParty.id'=>$clientId],
    ]);
		//pr($client);
		$pendingInvoices=$this->Invoice->find('all',array(
			'fields'=>array(
				'Invoice.id','Invoice.invoice_code',
				'Order.id','Order.order_code',
				'Invoice.total_price',
				'Invoice.currency_id',
				'Invoice.invoice_date','Invoice.due_date',
				'Client.company_name','Client.id',
				'Currency.abbreviation','Currency.id'
			),
			'conditions'=>array(
				'Invoice.bool_annulled'=>'0',
				'Invoice.bool_paid'=>'0',
				'Invoice.client_id'=>$clientId,
			),
			'order'=>'Invoice.invoice_date ASC',
		));
		
		for ($c=0;$c<count($pendingInvoices);$c++){
			$totalForInvoice=$pendingInvoices[$c]['Invoice']['total_price'];
			$totalForInvoiceCS=$totalForInvoice;
			if ($pendingInvoices[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoices[$c]['Invoice']['invoice_date']);
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
			}
			
			// get the amount already paid for this invoice
			$paidForInvoiceCS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoices[$c]['Invoice']['id']),2);		
			$pendingInvoices[$c]['Invoice']['paidCS']=$paidForInvoiceCS;
			$pendingForInvoiceCS=$totalForInvoiceCS-$paidForInvoiceCS;
			if ($pendingInvoices[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$pendingInvoices[$c]['Invoice']['id'],
					),
					'contain'=>array(
						'CashReceipt'=>array(
							'fields'=>array(
								'CashReceipt.id','CashReceipt.receipt_code',
								'CashReceipt.receipt_date',
								'CashReceipt.bool_annulled',
							),
						),
						'Currency'=>array(
							'fields'=>array(
								'Currency.abbreviation','Currency.id',
							),
						),
					),
				));
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoices[$c]['Invoice']['invoice_date']);
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the total
				$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
				$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
				$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
				$pendingForInvoiceCS+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						//if ($invoices[$i]['Invoice']['id']==159){
						//	echo "payment cash receipt dividing the amount paid by the exchange rate of the day".($cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate)."!<br/>";
						//	//pr($cashReceiptInvoices);
						//}
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForInvoiceCS-=$differenciaCambiariaPaid;
					}
				}
			}
			$pendingInvoices[$c]['Invoice']['pendingCS']=$pendingForInvoiceCS;
			//echo "total for invoice in C$ is ".$totalForInvoiceCS."<br/>";
			//echo "paid for invoice in C$ is ".$paidForInvoiceCS."<br/>";
			//echo "pending for invoice in C$ is ".($totalForInvoiceCS-$paidForInvoiceCS)."<br/>";
			//echo "pending registered for invoice in C$ is ".$pendingInvoices[$c]['Invoice']['pendingCS']."<br/>";
		}
		
		$this->set(compact('pendingInvoices','client','exchangeRateCurrent'));
    
    $this->ThirdParty->recursive=-1;
    $clients=$this->ThirdParty->find('list',[
      'conditions'=>[
        'ThirdParty.bool_provider' => false,
        'ThirdParty.bool_active' => true,
      ],
      'order'=>'company_name ASC',
    ]);
    $this->set(compact('clients'));
	}
	
  public function guardarCuentasPorPagar() {
		$exportData=$_SESSION['cuentasPorPagar'];
		$this->set(compact('exportData'));
	}
  
	public function verCobrosSemana() {
		$this->Invoice->recursive = 0;
		
		$this->loadModel('ExchangeRate');
		$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
		$exchangeRateCurrent=$currentExchangeRate['ExchangeRate']['rate'];
		$this->set(compact('exchangeRateCurrent'));
		
		$finalDateThisWeek=date("Y-m-d",strtotime(date("Y-m-d")."+7 days"));
		
		$pendingInvoicesThisWeek=$this->Invoice->find('all',array(
			'fields'=>array(
				'Invoice.id','Invoice.invoice_code','Invoice.client_id',
				'Order.id','Order.order_code',
				'Invoice.total_price',
				'Invoice.currency_id',
				'Invoice.invoice_date','Invoice.due_date',
				'Client.company_name','Client.id',
				'Currency.abbreviation','Currency.id'
			),
			'conditions'=>array(
				'Invoice.bool_annulled'=>'0',
				'Invoice.bool_paid'=>'0',
				'Invoice.due_date >='=>date("Y-m-d"),
				'Invoice.due_date <='=>$finalDateThisWeek,
				
			),
			'order'=>'Client.company_name ASC, Invoice.due_date ASC',
		));
		//pr($pendingInvoicesThisWeek);
		
		for ($c=0;$c<count($pendingInvoicesThisWeek);$c++){
			$totalForInvoice=$pendingInvoicesThisWeek[$c]['Invoice']['total_price'];
			$totalForInvoiceCS=$totalForInvoice;
			if ($pendingInvoicesThisWeek[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoicesThisWeek[$c]['Invoice']['invoice_date']);
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
			}
			
			// get the amount already paid for this invoice
			$paidForInvoiceCS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoicesThisWeek[$c]['Invoice']['id']),2);		
			$pendingInvoicesThisWeek[$c]['Invoice']['paidCS']=$paidForInvoiceCS;
			$pendingForInvoice=($totalForInvoiceCS-$paidForInvoiceCS);
						
			if ($pendingInvoicesThisWeek[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$pendingInvoicesThisWeek[$c]['Invoice']['id'],
					),
					'contain'=>array(
						'CashReceipt'=>array(
							'fields'=>array(
								'CashReceipt.id','CashReceipt.receipt_code',
								'CashReceipt.receipt_date',
								'CashReceipt.bool_annulled',
							),
						),
						'Currency'=>array(
							'fields'=>array(
								'Currency.abbreviation','Currency.id',
							),
						),
					),
				));
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoicesThisWeek[$c]['Invoice']['invoice_date']);
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the total
				$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
				$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
				$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
				$pendingForInvoice+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForInvoice-=$differenciaCambiariaPaid;
					}
				}
			}
			$pendingInvoicesThisWeek[$c]['Invoice']['pendingCS']=$pendingForInvoice;
		}
		$this->set(compact('pendingInvoicesThisWeek'));
		
		$pendingInvoicesEarlier=$this->Invoice->find('all',array(
			'fields'=>array(
				'Invoice.id','Invoice.invoice_code','Invoice.client_id',
				'Order.id','Order.order_code',
				'Invoice.total_price',
				'Invoice.currency_id',
				'Invoice.invoice_date','Invoice.due_date',
				'Client.company_name','Client.id',
				'Currency.abbreviation','Currency.id'
			),
			'conditions'=>array(
				'Invoice.bool_annulled'=>'0',
				'Invoice.bool_paid'=>'0',
				'Invoice.due_date <'=>date("Y-m-d"),
			),
			'order'=>'Client.company_name ASC, Invoice.due_date DESC',
		));
		//pr($pendingInvoicesEarlier);
		
		for ($c=0;$c<count($pendingInvoicesEarlier);$c++){
			$totalForInvoice=$pendingInvoicesEarlier[$c]['Invoice']['total_price'];
			$totalForInvoiceCS=$totalForInvoice;
			if ($pendingInvoicesEarlier[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoicesEarlier[$c]['Invoice']['invoice_date']);
				//if ($pendingInvoicesEarlier[$c]['Invoice']['id']==88){
				//	pr($pendingInvoicesEarlier[$c]['Invoice']['invoice_date']);
				//}
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				//if ($pendingInvoicesEarlier[$c]['Invoice']['id']==88){
					//echo "exchange rate for invoice 893 on 15 july is ".$exchangeRateInvoiceDate."!<br/>";
				//}
				$totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
			}
			//if ($pendingInvoicesEarlier[$c]['Invoice']['id']==88){
				//echo "total price in C$ for invoice 893 is ".$totalForInvoiceCS."!<br/>";
			//}
						
			// get the amount already paid for this invoice
			$paidForInvoiceCS=round($this->Invoice->getAmountPaidAlreadyCS($pendingInvoicesEarlier[$c]['Invoice']['id']),2);		
			//if ($pendingInvoicesEarlier[$c]['Invoice']['id']==88){
			//	echo "total payment in C$ for invoice 893 is ".$paidForInvoiceCS."!<br/>";
			//}
			$pendingInvoicesEarlier[$c]['Invoice']['paidCS']=$paidForInvoiceCS;
			$pendingForInvoice=($totalForInvoiceCS-$paidForInvoiceCS);
						
			if ($pendingInvoicesEarlier[$c]['Invoice']['currency_id']==CURRENCY_USD){
				$this->loadModel('CashReceiptInvoice');
				$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
					'conditions'=>array(
						'CashReceiptInvoice.invoice_id'=>$pendingInvoicesEarlier[$c]['Invoice']['id'],
					),
					'contain'=>array(
						'CashReceipt'=>array(
							'fields'=>array(
								'CashReceipt.id','CashReceipt.receipt_code',
								'CashReceipt.receipt_date',
								'CashReceipt.bool_annulled',
							),
						),
						'Currency'=>array(
							'fields'=>array(
								'Currency.abbreviation','Currency.id',
							),
						),
					),
				));
				$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($pendingInvoicesEarlier[$c]['Invoice']['invoice_date']);
				$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
				// add the diferencia cambiaria on the total
				$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
				$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
				$differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
				$differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
				$pendingForInvoice+=$differenciaCambiariaTotal;
				// add the diferencia cambiaria on the cashreceipts
				if (!empty($cashReceiptInvoices)){
					for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
						$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
						$differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
						$differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
						$pendingForInvoice-=$differenciaCambiariaPaid;
					}
				}
			}
			$pendingInvoicesEarlier[$c]['Invoice']['pendingCS']=$pendingForInvoice;
		}
		$this->set(compact('pendingInvoicesEarlier'));
	}
	
	public function view($id = null) {
		if (!$this->Invoice->exists($id)) {
			throw new NotFoundException(__('Invalid invoice'));
		}
		$options = array('conditions' => array('Invoice.' . $this->Invoice->primaryKey => $id));
		$this->set('invoice', $this->Invoice->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->Invoice->create();
			if ($this->Invoice->save($this->request->data)) {
				$this->recordUserAction($this->Invoice->id,null,null);
				$this->Session->setFlash(__('The invoice has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The invoice could not be saved. Please, try again.'), 'default',array('class' => 'error-message')); 
			}
		}
		$orders = $this->Invoice->Order->find('list');
		$clients = $this->Invoice->Client->find('list',array(
			'conditions'=>array(
				'bool_active'=>true,
			),
		));
		$currencies = $this->Invoice->Currency->find('list');
		$cashboxAccountingCodes = $this->Invoice->CashboxAccountingCode->find('list');
		$this->set(compact('orders', 'clients', 'currencies', 'cashboxAccountingCodes'));
	}

	public function edit($id = null) {
		if (!$this->Invoice->exists($id)) {
			throw new NotFoundException(__('Invalid invoice'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Invoice->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The invoice has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The invoice could not be saved. Please, try again.'), 'default',array('class' => 'error-message')); 
			}
		} 
		else {
			$options = array('conditions' => array('Invoice.' . $this->Invoice->primaryKey => $id));
			$this->request->data = $this->Invoice->find('first', $options);
		}
		$orders = $this->Invoice->Order->find('list');
		$clients = $this->Invoice->Client->find('list',array(
			'conditions'=>array(
				'bool_active'=>true,
			),
		));
		$currencies = $this->Invoice->Currency->find('list');
		$cashboxAccountingCodes = $this->Invoice->CashboxAccountingCode->find('list');
		$this->set(compact('orders', 'clients', 'currencies', 'cashboxAccountingCodes'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Invoice->id = $id;
		if (!$this->Invoice->exists()) {
			throw new NotFoundException(__('Invalid invoice'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Invoice->delete()) {
			$this->Session->setFlash(__('The invoice has been deleted.'));
		} else {
			$this->Session->setFlash(__('The invoice could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
