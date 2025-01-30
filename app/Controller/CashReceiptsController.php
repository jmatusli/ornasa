<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');


class CashReceiptsController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 


  public function beforeFilter(){
		parent::beforeFilter();
		// Allow users to register and logout.
		$this->Auth->allow('getCashReceiptCode');
	}
	
  public function getCashReceiptCode(){
    $this->autoRender = false; 
		$this->request->onlyAllow('ajax'); 
		$this->layout = "ajax";
		$plantId=trim($_POST['plantId']);
    
    $this->loadModel('Plant');
    return $this->CashReceipt->getCashReceiptCode($plantId,$this->Plant->getPlantShortName($plantId));
  }

	public function index() {
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    
    $plantId=0;
    
		$cash_receipt_type_id=0;
		
		if ($this->request->is('post')) {
      $plantId=$this->request->data['Report']['plant_id'];
      
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$cash_receipt_type_id=$this->request->data['Report']['cash_receipt_type_id'];
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
		
    if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    elseif (count($plants) > 1 && $plantId == 0){
      if (!empty($_SESSION['plantId'])){
        $plantId = $_SESSION['plantId'];
      }
      else {
        $plantId=0;
      }
    }
    $_SESSION['plantId']=$plantId;
    $this->set(compact('plantId'));
    
		$conditions=[];
		$conditions['CashReceipt.plant_id']= $plantId;
    $conditions['CashReceipt.receipt_date >=']= $startDate;
		$conditions['CashReceipt.receipt_date <']= $endDatePlusOne;
		if ($cash_receipt_type_id>0){
			$conditions['CashReceipt.cash_receipt_type_id']=$cash_receipt_type_id;
		}
	
		$this->CashReceipt->recursive = 0;
		
		$cashReceiptCount=$this->CashReceipt->find('count', [
			'conditions' => $conditions,
		]);
		
		$this->Paginator->settings=[
			'conditions' => $conditions,
			'order'=>'receipt_date DESC, receipt_code DESC',
			'limit'=>($cashReceiptCount!=0?$cashReceiptCount:1)
		];
		$cashReceipts=$this->Paginator->paginate('CashReceipt');
		$this->set('cashReceipts', $this->Paginator->paginate());
		
		$cashReceiptTypes=$this->CashReceipt->CashReceiptType->find('list');
		
		$this->set(compact('startDate','endDate','cashReceiptTypes'));
		
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

	public function view($id = null) {
		if (!$this->CashReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt'));
		}
		$options = [
			'conditions' => [
				'CashReceipt.id' => $id,
			],
			'contain'=>[
				'AccountingRegisterCashReceipt'=>[
					'AccountingRegister'
				],
				'CashboxAccountingCode',
				'CashReceiptType',
				'Client',
				'CreditAccountingCode',
				'Currency',
        'Plant',
			]
		];
		$cashReceipt=$this->CashReceipt->find('first', $options);
		
		$invoicesForCashReceipt=[];
		if ($cashReceipt['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_CREDIT){
			$this->loadModel('CashReceiptInvoice');
			$invoicesForCashReceipt=$this->CashReceiptInvoice->find('all',[
				'fields'=>[
					'CashReceiptInvoice.invoice_id',
					'CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.payment_retention','CashReceiptInvoice.payment_credit_CS',
					'CashReceiptInvoice.currency_id', 'Currency.id','Currency.abbreviation',
					'Invoice.invoice_code','Invoice.order_id',
				],
				'conditions'=>[
					'CashReceiptInvoice.cash_receipt_id'=>$id,
					'CashReceiptInvoice.amount >'=>0,
				],
				'order'=>'CashReceiptInvoice.invoice_id ASC'
			]);
		}
		
		$this->set(compact('cashReceipt','invoicesForCashReceipt'));
		
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

	public function viewPdf($id = null) {
		if (!$this->CashReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt'));
		}
		$options = array('conditions' => array('CashReceipt.' . $this->CashReceipt->primaryKey => $id));
		$cashReceipt=$this->CashReceipt->find('first', $options);
		$invoicesForCashReceipt=array();
		if ($cashReceipt['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_CREDIT){
			$this->loadModel('CashReceiptInvoice');
			$invoicesForCashReceipt=$this->CashReceiptInvoice->find('all',array(
				'fields'=>array(
					'CashReceiptInvoice.invoice_id',
					'CashReceiptInvoice.amount','CashReceiptInvoice.payment','CashReceiptInvoice.payment_retention','CashReceiptInvoice.payment_credit_CS',
					'CashReceiptInvoice.currency_id', 'Currency.id','Currency.abbreviation',
					'Invoice.invoice_code',
				),
				'conditions'=>array(
					'CashReceiptInvoice.cash_receipt_id'=>$id,
					'CashReceiptInvoice.amount >'=>0,
				),
				'order'=>'CashReceiptInvoice.invoice_id ASC'
			));
		}
		
		$this->set(compact('cashReceipt','invoicesForCashReceipt'));
	}

	public function add($cash_receipt_type_id) {
		$this->loadModel('Invoice');
		$this->loadModel('Order');
		
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('CashReceiptType');
		$this->loadModel('AccountingCode');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterCashReceipt');
		$this->loadModel('CashReceiptInvoice');
		
		$this->loadModel('ExchangeRate');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
		
		$plantId=0;
    
		$productCount=1;
		$postedInvoiceData=[];
		if ($this->request->is('post')) {
			//pr($this->request->data);
			
			$plantId=$this->request->data['CashReceipt']['plant_id'];
      
      if (empty($this->request->data['refresh'])){
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
          $postedInvoiceData=$this->request->data['Invoice'];
        }
        
        $receipt_date=$this->request->data['CashReceipt']['receipt_date'];
        $cashReceiptDateAsString = $this->CashReceipt->deconstruct('receipt_date', $this->request->data['CashReceipt']['receipt_date']);
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDate=new DateTime($latestClosingDate);
              
        $cashReceiptDateArray=[];
        $cashReceiptDateArray['year']=$receipt_date['year'];
        $cashReceiptDateArray['month']=$receipt_date['month'];
        $cashReceiptDateArray['day']=$receipt_date['day'];
            
        $receipt_code=$this->request->data['CashReceipt']['receipt_code'];
        $namedReceipts=$this->CashReceipt->find('all',array(
          'conditions'=>array(
            'receipt_code'=>$receipt_code,
          )
        ));
        
        $currency_id=$this->request->data['CashReceipt']['currency_id'];
        
        if (count($namedReceipts)>0){
          $this->Session->setFlash(__('Ya existe un recibo de caja con el mismo código!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        else if ($cashReceiptDateAsString>date('Y-m-d 23:59:59')){
          $this->Session->setFlash(__('La fecha de recibo de caja no puede estar en el futuro!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        elseif ($cashReceiptDateAsString<$latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
        }
        else if ($this->request->data['CashReceipt']['bool_annulled']){
          $datasource=$this->CashReceipt->getDataSource();
          $datasource->begin();
          try {
            //pr($this->request->data);
            $this->CashReceipt->create();
            $CashReceiptData=[];
            
            $CashReceiptData['CashReceipt']['receipt_code']=$this->request->data['CashReceipt']['receipt_code'];
            $CashReceiptData['CashReceipt']['receipt_date']=$this->request->data['CashReceipt']['receipt_date'];
            $CashReceiptData['CashReceipt']['bool_annulled']=true;
            $CashReceiptData['CashReceipt']['client_id']=$this->request->data['CashReceipt']['client_id'];
            $CashReceiptData['CashReceipt']['concept']=$this->request->data['CashReceipt']['concept'];
            $CashReceiptData['CashReceipt']['observation']=$this->request->data['CashReceipt']['observation'];
            $CashReceiptData['CashReceipt']['cash_receipt_type_id']=$cash_receipt_type_id;
            $CashReceiptData['CashReceipt']['amount']=0;
            $CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
        
            if (!$this->CashReceipt->save($CashReceiptData)) {
              echo "Problema guardando el recibo de caja";
              pr($this->validateErrors($this->CashReceipt));
              throw new Exception();
            }
            
            $datasource->commit();
              
            // SAVE THE USERLOG 
            $this->recordUserActivity($this->Session->read('User.username'),"Se registró el recibo de caja con número ".$this->request->data['CashReceipt']['receipt_code']);
            $this->Session->setFlash(__('Se guardó el recibo de caja.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
            return $this->redirect(array('action' => 'index'));
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('No se podía guardar la remisión.  Por favor vuelva a intentar.'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          }
        }					
        else if ($this->request->data['CashReceipt']['cashbox_accounting_code_id']==0){
          $this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en un recibo de caja!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        else if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER&&$this->request->data['CashReceipt']['credit_accounting_code_id']==0){
          $this->Session->setFlash(__('Se debe seleccionar la cuenta contable del HABER!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        else if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT&&$this->request->data['CashReceipt']['bool_retention']&&strlen($this->request->data['CashReceipt']['retention_number'])==0){
          $this->Session->setFlash(__('Se debe indicar el número de retención si se aplican retenciones!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        else {
            $datasource=$this->CashReceipt->getDataSource();
            $datasource->begin();
            try {
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                $this->request->data['CashReceipt']['amount']=$this->request->data['CashReceipt']['amount_total_payment'];
              }
              $this->CashReceipt->create();
              if (!$this->CashReceipt->save($this->request->data)) {
                echo "Problema guardando el recibo de caja";
                pr($this->validateErrors($this->CashReceipt));
                throw new Exception();
              }
              $cash_receipt_id=$this->CashReceipt->id;
              
              // now prepare the accounting registers
              // debit=caja selected by client
              // for otros ingresos, credit = code selected by client, 
              // for credit invoices, credit = facturas por cobrar 
              // amount = total
              
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                $total_cuentas_por_cobrar=$this->request->data['CashReceipt']['amount_cuentas_por_cobrar'];
                $total_increment=$this->request->data['CashReceipt']['amount_increment'];
                $total_discount=$this->request->data['CashReceipt']['amount_discount'];
                $total_erdiff=$this->request->data['CashReceipt']['amount_difference_exchange_rate'];
                $total_retention=$this->request->data['CashReceipt']['amount_retention_paid'];
                $total_cash_receipt=$this->request->data['CashReceipt']['amount_total_payment'];
              }
              else {
                $total_cash_receipt=$this->request->data['CashReceipt']['amount'];
                $total_retention=0;
              }
              
              if ($currency_id==CURRENCY_USD){
                $this->loadModel('ExchangeRate');
                $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptDateAsString);
                $total_CS=round($total_cash_receipt*$applicableExchangeRate['ExchangeRate']['rate'],2);
                $total_retention_CS=round($total_retention*$applicableExchangeRate['ExchangeRate']['rate'],2);
              }
              else {
                $total_CS=$total_cash_receipt;
                $total_retention_CS=$total_retention;
              }
              
              $accountingRegisterData['AccountingRegister']['register_date']=$cashReceiptDateArray;
              
              $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
              $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
              $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                //$accountingRegisterData['AccountingRegister']['amount']=$total_CS+$total_increment+$total_erdiff-$total_discount;
                $accountingRegisterData['AccountingRegister']['amount']=$total_CS+$total_retention_CS+$total_discount;
              }
              else {
                $accountingRegisterData['AccountingRegister']['amount']=$total_CS;
              }
              $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingRegister']['concept']="Recibo de Caja ".$receipt_code;
              $accountingRegisterData['AccountingRegister']['observation']="Recibo de Caja ".$receipt_code;
              
              $totalDebit=0;
              $totalCredit=0;
              
              // THIS GOES TO THE CASHBOX
              $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['CashReceipt']['cashbox_accounting_code_id'];
              //$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['cashbox_accounting_code_id']);
              $accountingRegisterData['AccountingMovement'][0]['concept']="Recibo de Caja ".$receipt_code;
              $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$total_CS;
              $totalDebit+=$total_CS;
              
              //pr($accountingRegisterData);
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
                $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=$this->request->data['CashReceipt']['credit_accounting_code_id'];
                //$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['credit_accounting_code_id']);
                $accountingRegisterData['AccountingMovement'][1]['concept']="Recibo de Caja ".$receipt_code;
                $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$total_CS;
                $totalCredit+=$total_CS;
              }
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                $client_id=$this->request->data['CashReceipt']['client_id'];
                $this->loadModel('ThirdParty');
                $thisClient=$this->ThirdParty->read(null,$client_id);
                
                if (empty($thisClient['ThirdParty']['accounting_code_id'])){
                  $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES);
                }
                else {
                  $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
                  //$accountingCode=$this->AccountingCode->read(null,$thisClient['ThirdParty']['accounting_code_id']);
                }
                $accountingRegisterData['AccountingMovement'][1]['concept']="Recibo de Caja ".$receipt_code;
                $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$total_cuentas_por_cobrar;
                $totalCredit+=$total_cuentas_por_cobrar;
                if ($total_increment>0){
                  $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_OTROS_INGRESOS;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_OTROS INGRESOS);
                  $accountingRegisterData['AccountingMovement'][2]['concept']="Recibo de Caja ".$receipt_code;
                  $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$total_increment;
                  $totalCredit+=$total_increment;
                }
                if ($total_discount>0){
                  $accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_DESCUENTO_SOBRE_VENTA;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_DESCUENTO_SOBRE_VENTA);
                  $accountingRegisterData['AccountingMovement'][3]['concept']="Recibo de Caja ".$receipt_code;
                  $accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][3]['debit_amount']=$total_discount;
                  $totalDebit+=$total_discount;
                }
                if ($total_erdiff>0){
                  $accountingRegisterData['AccountingMovement'][4]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_DIFERENCIA_CAMBIARIA;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_DIFERENCIA_CAMBIARIA);
                  $accountingRegisterData['AccountingMovement'][4]['concept']="Recibo de Caja ".$receipt_code;
                  $accountingRegisterData['AccountingMovement'][4]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][4]['credit_amount']=$total_erdiff;
                  $totalCredit+=$total_erdiff;
                }
                if ($this->request->data['CashReceipt']['bool_retention']){
                  $accountingRegisterData['AccountingMovement'][5]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_RETENCIONES_POR_COBRAR);
                  $accountingRegisterData['AccountingMovement'][5]['concept']="Retención Recibo de Caja ".$receipt_code;
                  $accountingRegisterData['AccountingMovement'][5]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][5]['debit_amount']=$total_retention_CS;
                  $totalDebit+=$total_retention_CS;
                }
                // ADDED 20160309 to deal with too unequal amounts (especially for receipts where too much was paid)
                if (abs($totalCredit-$totalDebit)>=0.005){
                  $accountingRegisterData['AccountingMovement'][6]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_OTROS_INGRESOS;
                  if ($totalCredit>$totalDebit){
                    $accountingRegisterData['AccountingMovement'][6]['concept']="Recibo de Caja ".$receipt_code;
                    $accountingRegisterData['AccountingMovement'][6]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][6]['debit_amount']=($totalCredit-$totalDebit);
                  }
                  else {
                    $accountingRegisterData['AccountingMovement'][6]['concept']="Recibo de Caja ".$receipt_code;
                    $accountingRegisterData['AccountingMovement'][6]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][6]['credit_amount']=($totalDebit-$totalCredit);
                  }
                }
              }
              
              //pr($accountingRegisterData);
              $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
              $this->recordUserAction($this->AccountingRegister->id,"add",null);
              //echo "accounting register saved for otros ingresos<br/>";
          
              $AccountingRegisterCashReceiptData=[];
              $AccountingRegisterCashReceiptData['accounting_register_id']=$accounting_register_id;
              $AccountingRegisterCashReceiptData['cash_receipt_id']=$cash_receipt_id;
              $this->AccountingRegisterCashReceipt->create();
              if (!$this->AccountingRegisterCashReceipt->save($AccountingRegisterCashReceiptData)) {
                pr($this->validateErrors($this->AccountingRegisterCashReceipt));
                echo "problema al guardar el lazo entre asiento contable y recibo de caja";
                throw new Exception();
              }
              //echo "link accounting register cash receipt saved<br/>";			
          
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                foreach ($this->request->data['Invoice'] as $invoice){
                  $this->Invoice->id=$invoice['invoice_id'];
                  $existingInvoice=[];
                  $existingInvoice=$this->Invoice->find('first',array(
                    'conditions'=>array(
                      'Invoice.id'=>$invoice['invoice_id'],
                    ),
                  ));
                  //pr($existingInvoice);
                  
                  $invoice_total_price_invoice_currency=$existingInvoice['Invoice']['total_price'];
                  $invoice_currency_id=$existingInvoice['Invoice']['currency_id'];
                  
                  if ($invoice_currency_id==CURRENCY_CS){
                    $invoice_total_price_CS=$invoice_total_price_invoice_currency;
                  }
                  elseif ($invoice_currency_id==CURRENCY_USD){
                    $invoiceDate=$existingInvoice['Invoice']['invoice_date'];
                    $invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoiceDate);
                    $exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
                    $invoice_total_price_CS=$invoice_total_price_invoice_currency*$exchangeRateInvoiceDate;
                  }
                  else {
                    $invoice_total_price_CS=$invoice_total_price_invoice_currency;
                  }
                  
                  $invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($existingInvoice['Invoice']['id']);
                  $invoice_pending_CS=$invoice_total_price_CS-$invoice_paid_already_CS;
                  $amount_paid_CS=0;
                  //$amount_paid_CS=$invoice['creditpayment']+$invoice['difpayment'];
                  // MODIFIED 20160122
                  //$amount_paid_CS=$invoice['creditpayment']+$invoice['difpayment']+$invoice['descpayment']-$invoice['incpayment'];
                  // MODIFIED 20160308
                  $amount_paid_CS=$invoice['creditpayment']+$invoice['difpayment']+$invoice['descpayment'];
                  
                  $InvoiceData=[];
                  // 20160122 NO TIENE SENTIDO AÑADIR A LO EXISTENTE EN INVOICE PORQUE ASI SE HARÍA MUY LARGA LA LISTA
                  // EN TEORIA Y PRACTICA SOLAMENTE DEBERIA HABER UNA SOLA RETENCION PARA UNA FACTURA
                  $InvoiceData['Invoice']['bool_retention']=$this->request->data['CashReceipt']['bool_retention'];
                  $InvoiceData['Invoice']['retention_number']=$this->request->data['CashReceipt']['retention_number'];
                  $InvoiceData['Invoice']['retention_amount']=$invoice['retentionpayment'];
                  
                  if (($invoice_pending_CS-$amount_paid_CS)<0.01){
                    $InvoiceData['Invoice']['bool_paid']=true;
                    //echo "paid set to true<br/>";
                  }
                  else {
                    $InvoiceData['Invoice']['bool_paid']='0';
                    //echo "paid set to false<br/>";
                  }
                  
                  $this->Invoice->id=$invoice['invoice_id'];
                  if (!$this->Invoice->save($InvoiceData)) {
                    pr($this->validateErrors($this->Invoice));
                    echo "problema al guardar la factura";
                    throw new Exception();
                  }
                  
                  if ($invoice['payment']==NULL){
                    $invoice['payment']=0;
                  }
                  if ($invoice['retentionpayment']==NULL){
                    $invoice['retentionpayment']=0;
                  }
                  if (($invoice['payment']>0)||($invoice['retentionpayment']>0)){
                    $CashReceiptInvoiceData=[];
                    $CashReceiptInvoiceData['cash_receipt_id']=$cash_receipt_id;
                    $CashReceiptInvoiceData['invoice_id']=$invoice['invoice_id'];
                    $CashReceiptInvoiceData['amount']=$invoice['payment']-$invoice['increment']+$invoice['discount']-$invoice['exchangeratedifference'];
                    $CashReceiptInvoiceData['increment']=$invoice['increment'];
                    $CashReceiptInvoiceData['discount']=$invoice['discount'];
                    $CashReceiptInvoiceData['erdiff']=$invoice['exchangeratedifference'];
                    $CashReceiptInvoiceData['payment']=$invoice['payment'];
                    $CashReceiptInvoiceData['payment_retention']=$invoice['retentionpayment'];
                    $CashReceiptInvoiceData['payment_credit_CS']=$invoice['creditpayment'];
                    $CashReceiptInvoiceData['payment_increment_CS']=$invoice['incpayment'];
                    $CashReceiptInvoiceData['payment_discount_CS']=$invoice['descpayment'];
                    $CashReceiptInvoiceData['payment_erdiff_CS']=$invoice['difpayment'];
                    $CashReceiptInvoiceData['currency_id']=$currency_id;
                    
                    
                    $this->CashReceiptInvoice->create();
                    if (!$this->CashReceiptInvoice->save($CashReceiptInvoiceData)) {
                      pr($this->validateErrors($this->CashReceiptInvoice));
                      echo "problema al guardar el lazo entre el recibo de caja y la factura";
                      throw new Exception();
                    }
                  }
                }
              }
              $datasource->commit();
              $this->recordUserAction($this->CashReceipt->id,null,null);
              // SAVE THE USERLOG FOR THE CASH RECEIPT
              $this->recordUserActivity($this->Session->read('User.username'),"Recibo de caja registrado con número ".$this->request->data['CashReceipt']['receipt_code']);
              $this->Session->setFlash(__('Se guardó el recibo de caja.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
              return $this->redirect(array('action' => 'index'));
            }
            catch(Exception $e){
              $datasource->rollback();
              pr($e);
              $this->Session->setFlash(__('No se podía guardar el recibo de caja.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
            }
          //}
        }
      
      }
    }
		
    if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    elseif (count($plants) > 1 && $plantId == 0){
      //if (!empty($_SESSION['plantId'])){
      //  $plantId = $_SESSION['plantId'];
      //}
      //else {
      //  $plantId=0;
      //}
    }
    $_SESSION['plantId']=$plantId;
    $this->set(compact('plantId'));
    
    $cashReceiptTypes = $this->CashReceipt->CashReceiptType->find('list');
		$currencies = $this->CashReceipt->Currency->find('list');
		$clients = $this->CashReceipt->Client->find('list',[
			'conditions'=>[
				'bool_provider'=>'0',
				'bool_active'=>true,
			],
			'order'=>'company_name',
		]);
		//pr($clients);
		$keys=array_keys($clients);
		//pr($keys);
		for ($c=0;$c<count($keys);$c++){
			//echo "key is ".$keys[$c]."<br/>";
			$pendingInvoices=$this->Invoice->find('all',[
				'fields'=>[
					'Invoice.id','Invoice.invoice_code',
				],
				'conditions'=>[
					'Invoice.bool_annulled'=>'0',
					'Invoice.bool_paid'=>'0',
					'Invoice.client_id'=>$keys[$c],
				],
				'order'=>'Invoice.invoice_date ASC',
			]);
			if (empty($pendingInvoices)){
				//echo "Client ".$keys[$c]." is removed<br/>";
				unset($clients[$keys[$c]]);
			}
			else {
				//echo "Invoices for client ".$keys[$c]."<br/>";
				//pr($pendingInvoices);
			}
		}
		//pr($clients);
		
		$accountingCodes = $this->AccountingCode->find('list',[
			'fields'=>['AccountingCode.id','AccountingCode.shortfullname'],
			'conditions'=>['AccountingCode.bool_main'=>false],
		]);
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',[
			'fields'=>['AccountingCode.lft','AccountingCode.rght'],
			'conditions'=>[
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			],
		]);
		$cashboxAccountingCodes = $this->AccountingCode->find('list',[
			'fields'=>'AccountingCode.fullname',
			'conditions'=>[
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			],
			'order'=>'AccountingCode.lft',
		]);
		
    $newCashReceiptCode=$this->CashReceipt->getCashReceiptCode($plantId,$this->Plant->getPlantShortName($plantId));
    
		$receiptDate=date( "Y-m-d");
		$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($receiptDate);
		$exchangeRateCashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
		
		//pr($clients);
		$this->set(compact('cashReceiptTypes', 'currencies', 'clients','cash_receipt_type_id','accountingCodes','newCashReceiptCode','postedInvoiceData','exchangeRateCashReceipt','cashboxAccountingCodes'));
		
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

	public function edit($id = null) {
		if (!$this->CashReceipt->exists($id)) {
			throw new NotFoundException(__('Invalid cash receipt'));
		}
		
		$cashReceipt=$this->CashReceipt->read(null,$id);
		
		$this->loadModel('Invoice');
		$this->loadModel('Order');
		
		$this->loadModel('Product');
		$this->loadModel('ProductType');
		$this->loadModel('StockItem');
		$this->loadModel('StockMovement');
		
		$this->loadModel('ClosingDate');
		
		$this->loadModel('Currency');
		
		$this->loadModel('CashReceipt');
		$this->loadModel('CashReceiptType');
		$this->loadModel('AccountingCode');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$this->loadModel('CashReceiptInvoice');
		$this->loadModel('ExchangeRate');
    
    $this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    $loggedUserId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
		
		$plantId=0;
    
		$productCount=1;
		$bool_first_load=true;
		
		$previousCashReceipt=$this->CashReceipt->read(null,$id);
		$cash_receipt_type_id=$previousCashReceipt['CashReceipt']['cash_receipt_type_id'];
		$this->set(compact('previousCashReceipt','cash_receipt_type_id'));
		
		$receiptdate=$previousCashReceipt['CashReceipt']['receipt_date'];
		$receiptDateTime=new DateTime($receiptdate);
		$receiptDateString=$receiptDateTime->format('Y').'-'.$receiptDateTime->format('m').'-'.$receiptDateTime->format('d');
		$receiptDate=date( "Y-m-d", strtotime($receiptDateString));
		
		$cashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($receiptDate);
		$exchangeRateCashReceipt=$cashReceiptExchangeRate['ExchangeRate']['rate'];
			
		if ($previousCashReceipt['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_CREDIT){
			$clientid=$previousCashReceipt['CashReceipt']['client_id'];
			
			$cashreceiptcurrencyid=trim($previousCashReceipt['CashReceipt']['currency_id']);
						
			$this->CashReceiptInvoice->recursive=-1;
			$cashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
				'conditions'=>array(
					'CashReceiptInvoice.cash_receipt_id'=>$id,
				),
				'contain'=>array(
					'CashReceipt'=>array(
						'fields'=>array(
							'CashReceipt.id','CashReceipt.receipt_code',
							'CashReceipt.receipt_date',
							'CashReceipt.bool_annulled',
						),
					),
					'Invoice'=>array(
						'Currency',
					),
					'Currency',
				),
			));
			//pr($cashReceiptInvoices);
			for ($i=0;$i<count($cashReceiptInvoices);$i++){
				$totalForInvoice=$cashReceiptInvoices[$i]['Invoice']['total_price'];
				$pendingForInvoice=$totalForInvoice;
				$invoiceCurrencyId=$cashReceiptInvoices[$i]['Invoice']['currency_id'];
				$invoiceDate=$cashReceiptInvoices[$i]['Invoice']['invoice_date'];
				
				// add the retention amount
				// IF STATEMENT ELIMINATED AS WE WANT THE RETENTION INTHE ORIGINAL CURRENCY
				//if ($invoiceCurrencyId==CURRENCY_CS){
					$cashReceiptInvoices[$i]['Invoice']['retention']=round($cashReceiptInvoices[$i]['Invoice']['sub_total_price']*0.02,2);
				//}
				//elseif ($invoiceCurrencyId==CURRENCY_USD){
				//	$cashReceiptInvoices[$i]['Invoice']['retention']=round($cashReceiptInvoices[$i]['Invoice']['sub_total_price']*0.02*$exchangeRateCashReceipt,2);
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
				$cashReceiptInvoices[$i]['Invoice']['invoice_exchange_rate']=$exchangeRateInvoiceDate;
				$cashReceiptInvoices[$i]['Invoice']['difference_exchange_rates']=$difference_exchange_rates;
				$cashReceiptInvoices[$i]['Invoice']['exchange_rate_difference']=$exchange_rate_difference;
				
				// get the amount already paid for this invoice
				$cashReceiptInvoices[$i]['Invoice']['paid_already_CS']=round($this->Invoice->getAmountPaidAlreadyWithoutErDiffCS($cashReceiptInvoices[$i]['Invoice']['id']),2);
				
				$diferenciaCambiariaPagado=0;
				if ($invoiceCurrencyId==CURRENCY_USD){
					$previousCashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
						'conditions'=>array(
							'CashReceiptInvoice.invoice_id'=>$cashReceiptInvoices[$i]['Invoice']['id'],
							'CashReceiptInvoice.cash_receipt_id !='=>$id,
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
					for ($cri=0;$cri<count($previousCashReceiptInvoices);$cri++){
						$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
						$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
						// add the diferencia cambiaria on the cashreceipts						
						$previousCashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($previousCashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
						$exchangeRatePreviousCashReceiptDate=$previousCashReceiptExchangeRate['ExchangeRate']['rate'];
						$differenceExchangeRateNowCashReceipt=$exchangeRateCashReceipt-$exchangeRatePreviousCashReceiptDate;
						$diferenciaCambiariaPagado+=$differenceExchangeRateNowCashReceipt*$previousCashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRatePreviousCashReceiptDate;
					}
				}
				//echo "diferencia cambiaria pagado is ".$diferenciaCambiariaPagado."<br/>";
				$cashReceiptInvoices[$i]['Invoice']['diferencia_cambiaria_pagado']=round($diferenciaCambiariaPagado,2);
				
				//this logic is unneeded; just rest the payment_credit_CS from the amountpaidalready in the view to obtain the pending
				/*
				$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment_CS']=$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment'];
				$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment_retention_CS']=$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment_retention'];
				if ($cashReceiptInvoices[$i]['CashReceiptInvoice']['currency_id']==CURRENCY_USD){
					$cashReceiptInvoice=$this->CashReceipt->read(null,$cashReceiptInvoices[$i]['CashReceiptInvoice']['cash_receipt_id']);
					//pr($cashReceiptInvoice);
					$cashreceiptinvoicedate=$cashReceiptInvoice['CashReceipt']['receipt_date'];
					$cashReceiptInvoiceDateTime=new DateTime($cashreceiptinvoicedate);
					$cashReceiptInvoiceString=$cashReceiptInvoiceDateTime->format('Y').'-'.$cashReceiptInvoiceDateTime->format('m').'-'.$cashReceiptInvoiceDateTime->format('d');
					$cashReceiptInvoiceDate=date( "Y-m-d", strtotime($cashReceiptInvoiceString));
					
					$cashReceiptInvoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoiceDate);
					$exchangeRateCashReceiptInvoice=$cashReceiptInvoiceExchangeRate['ExchangeRate']['rate'];
					
					$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment_CS']=$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment']*$exchangeRateCashReceiptInvoice;
					$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment_retention_CS']=$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment_retention']*$exchangeRateCashReceiptInvoice;
					
				}
				//echo "payment CS this cashreceipt is ".$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment_CS']."<br/>";
				//echo "retention CS this cashreceipt is ".$cashReceiptInvoices[$i]['CashReceiptInvoice']['payment_retention_CS']."<br/>";
				*/
				
			}
			//pr($cashReceiptInvoices);
			
			$invoicesPaidInCashReceipt=$this->CashReceiptInvoice->find('list',array(
				'fields'=>array('CashReceiptInvoice.invoice_id'),
				'conditions'=>array(
					'CashReceiptInvoice.cash_receipt_id'=>$id,
				),
			));
			//pr($invoicesPaidInCashReceipt);
			
			$otherPendingInvoicesForClient=$this->Invoice->find('all',array(
				'fields'=>array(
					'Invoice.id','Invoice.order_id','Invoice.invoice_code','Invoice.invoice_date',
					'Invoice.currency_id','Invoice.due_date',
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
					'Invoice.id !='=>$invoicesPaidInCashReceipt,
				),
				'order'=>'Invoice.invoice_date ASC'
			));
			//pr($otherPendingInvoicesForClient);
			for ($i=0;$i<count($otherPendingInvoicesForClient);$i++){
				$totalForInvoice=$otherPendingInvoicesForClient[$i]['Invoice']['total_price'];
				$pendingForInvoice=$totalForInvoice;
				$invoiceCurrencyId=$otherPendingInvoicesForClient[$i]['Invoice']['currency_id'];
				$invoiceDate=$otherPendingInvoicesForClient[$i]['Invoice']['invoice_date'];
				
				// add the retention amount
				// IF STATEMENT ELIMINATED AS WE WANT THE RETENTION INTHE ORIGINAL CURRENCY
				//if ($invoiceCurrencyId==CURRENCY_CS){
					$otherPendingInvoicesForClient[$i]['Invoice']['retention']=round($otherPendingInvoicesForClient[$i]['Invoice']['sub_total_price']*0.02,2);
				//}
				//elseif ($invoiceCurrencyId==CURRENCY_USD){
				//	$otherPendingInvoicesForClient[$i]['Invoice']['retention']=round($otherPendingInvoicesForClient[$i]['Invoice']['sub_total_price']*0.02*$exchangeRateCashReceipt,2);
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
				$otherPendingInvoicesForClient[$i]['Invoice']['invoice_exchange_rate']=$exchangeRateInvoiceDate;
				$otherPendingInvoicesForClient[$i]['Invoice']['difference_exchange_rates']=$difference_exchange_rates;
				$otherPendingInvoicesForClient[$i]['Invoice']['exchange_rate_difference']=$exchange_rate_difference;
				
				// get the amount already paid for this invoice
				$otherPendingInvoicesForClient[$i]['Invoice']['paid_already_CS']=round($this->Invoice->getAmountPaidAlreadyCS($otherPendingInvoicesForClient[$i]['Invoice']['id']),2);
				
				$diferenciaCambiariaPagado=0;
				if ($otherPendingInvoicesForClient[$i]['Invoice']['currency_id']==CURRENCY_USD){
					$this->loadModel('CashReceiptInvoice');
					$cashReceiptInvoicesForOther=$this->CashReceiptInvoice->find('all',array(
						'conditions'=>array(
							'CashReceiptInvoice.invoice_id'=>$otherPendingInvoicesForClient[$i]['Invoice']['id'],
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
					$invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($otherPendingInvoicesForClient[$i]['Invoice']['invoice_date']);
					$exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
					// add the diferencia cambiaria on the total
					$currentExchangeRate=$this->ExchangeRate->getApplicableExchangeRate(date('Y-m-d'));
					$exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
					// add the diferencia cambiaria on the cashreceipts
					if (!empty($cashReceiptInvoicesForOther)){
						for ($cri=0;$cri<count($cashReceiptInvoicesForOther);$cri++){
							$previousCashReceiptExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptInvoicesForOther[$cri]['CashReceipt']['receipt_date']);
							$exchangeRatePreviousCashReceiptDate=$previousCashReceiptExchangeRate['ExchangeRate']['rate'];
							$differenceExchangeRateNowCashReceipt=$exchangeRateCashReceipt-$exchangeRatePreviousCashReceiptDate;
							//echo "difference exchange rate between now and cashreceipt is".$differenceExchangeRateNowCashReceipt."<br/>";
							//echo "payment_credit_CS is".$cashReceiptInvoicesForOther[$cri]['CashReceiptInvoice']['payment_credit_CS']."<br/>";
							//echo "exchange rate previous cash receipts is".$exchangeRatePreviousCashReceiptDate."<br/>";
							//echo "diferenciaCambiariaPagado is".$diferenciaCambiariaPagado."<br/>";
							$diferenciaCambiariaPagado+=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoicesForOther[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRatePreviousCashReceiptDate;
							//echo "diferenciaCambiariaPagado is ".$diferenciaCambiariaPagado."<br/>";
						}
					}
				}
				$otherPendingInvoicesForClient[$i]['Invoice']['diferencia_cambiaria_pagado']=round($diferenciaCambiariaPagado,2);
				
				
				// get the pending amounts
				// COMMENTED OUT, getPendingAmount gives back the amount in the currency of the Invoice, and we want it in the cash receipt currency
				/*
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
			$this->set(compact('cashReceiptInvoices','otherPendingInvoicesForClient','id','cashreceiptcurrencyid','exchangeRateCashReceipt','previousCashReceipt'));
		}
		//pr($otherPendingInvoicesForClient);
		if ($this->request->is(['post', 'put'])) {
			$bool_first_load='0';
			
      $plantId=$this->request->data['CashReceipt']['plant_id'];
      
      if (empty($this->request->data['refresh'])){
      
        if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
          $postedInvoiceData=$this->request->data['Invoice'];
        }
        
        $receipt_date=$this->request->data['CashReceipt']['receipt_date'];
        $cashReceiptDateAsString = $this->CashReceipt->deconstruct('receipt_date', $this->request->data['CashReceipt']['receipt_date']);
        $latestClosingDate=$this->ClosingDate->getLatestClosingDate();
        $latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
        $closingDate=new DateTime($latestClosingDate);
              
        $cashReceiptDateArray=[];
        $cashReceiptDateArray['year']=$receipt_date['year'];
        $cashReceiptDateArray['month']=$receipt_date['month'];
        $cashReceiptDateArray['day']=$receipt_date['day'];
            
        $receipt_code=$this->request->data['CashReceipt']['receipt_code'];
        $namedReceipts=$this->CashReceipt->find('all',array(
          'conditions'=>array(
            'receipt_code'=>$receipt_code,
            'CashReceipt.id !='=>$id,
          )
        ));
        
        $currency_id=$this->request->data['CashReceipt']['currency_id'];
        
        if (count($namedReceipts)>0){
          $this->Session->setFlash(__('Ya existe un recibo de caja con el mismo código!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        else if ($cashReceiptDateAsString>date('Y-m-d 23:59:59')){
          $this->Session->setFlash(__('La fecha de recibo de caja no puede estar en el futuro!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        elseif ($cashReceiptDateAsString<$latestClosingDatePlusOne){
          $this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
        }
        else if ($this->request->data['CashReceipt']['bool_annulled']){
          $datasource=$this->CashReceipt->getDataSource();
          $oldCashReceipt=$this->CashReceipt->find('first',array(
            'conditions'=>array(
              'CashReceipt.id'=>$id,
            )
          ));
          $datasource->begin();
          try {
            // first remove existing data: cashreceipt_invoice, accounting register invoice, accounting registers
            $oldCashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
              'fields'=>array('CashReceiptInvoice.id','CashReceiptInvoice.invoice_id'),
              'conditions'=>array(
                'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
              ),				
            ));
            if (!empty($oldCashReceiptInvoices)){
              foreach ($oldCashReceiptInvoices as $oldCashReceiptInvoice){
                //pr($oldCashReceiptInvoice);
                //set bool_paid to false
                $invoiceArray=[];
                $invoiceArray['Invoice']['id']=$oldCashReceiptInvoice['CashReceiptInvoice']['invoice_id'];
                $invoiceArray['Invoice']['bool_paid']='0';
                $this->Invoice->id=$oldCashReceiptInvoice['CashReceiptInvoice']['invoice_id'];
                $this->Invoice->save($invoiceArray);
                // remove the CashReceiptInvoice
                $this->CashReceiptInvoice->delete($oldCashReceiptInvoice['CashReceiptInvoice']['id']);
              }
            }
                      
            $oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
              'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
              'conditions'=>array(
                'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
              ),				
            ));
            
            if (!empty($oldAccountingRegisterCashReceipts)){
              foreach ($oldAccountingRegisterCashReceipts as $oldAccountingRegisterCashReceipt){
                // first remove the movement
                $oldAccountingMovements=$this->AccountingMovement->find('all',array(
                  'fields'=>array('AccountingMovement.id'),
                  'conditions'=>array(
                    'accounting_register_id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
                  ),
                ));
                if (!empty($oldAccountingMovements)){
                  foreach ($oldAccountingMovements as $oldAccountingMovement){
                    $this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
                  }
                }
                // then remove the register
                $this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
                // then remove the register invoice link
                $this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
              }
            }
            // then remove the cash receipt
            $this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id']);
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('Problema al eliminar los datos viejos.'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          }
          $datasource->begin();
          try {
            //pr($this->request->data);
            $CashReceiptData=[];
            $CashReceiptData['CashReceipt']['id']=$id;
            $CashReceiptData['CashReceipt']['receipt_code']=$this->request->data['CashReceipt']['receipt_code'];
            $CashReceiptData['CashReceipt']['receipt_date']=$this->request->data['CashReceipt']['receipt_date'];
            $CashReceiptData['CashReceipt']['bool_annulled']=true;
            $CashReceiptData['CashReceipt']['client_id']=$this->request->data['CashReceipt']['client_id'];
            $CashReceiptData['CashReceipt']['concept']=$this->request->data['CashReceipt']['concept'];
            $CashReceiptData['CashReceipt']['observation']=$this->request->data['CashReceipt']['observation'];
            $CashReceiptData['CashReceipt']['cash_receipt_type_id']=$cash_receipt_type_id;
            $CashReceiptData['CashReceipt']['amount']=0;
            $CashReceiptData['CashReceipt']['currency_id']=CURRENCY_CS;
            $this->CashReceipt->id=$id;
            if (!$this->CashReceipt->save($CashReceiptData)) {
              echo "Problema guardando el recibo de caja";
              pr($this->validateErrors($this->CashReceipt));
              throw new Exception();
            }
            
            $datasource->commit();
            
            // SAVE THE USERLOG 
            $this->recordUserAction($id,"edit",null);	
            $this->recordUserActivity($this->Session->read('User.username'),"Se editó el recibo de caja con número ".$this->request->data['CashReceipt']['receipt_code']);
            $this->Session->setFlash(__('Se guardó el recibo de caja.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
            return $this->redirect(array('action' => 'index'));
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('No se podía guardar el recibo de caja.  Por favor vuelva a intentar.'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
          }
        }					
        else if ($this->request->data['CashReceipt']['cashbox_accounting_code_id']==0){
          $this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en un recibo de caja!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        else if ($this->request->data['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_OTHER&&$this->request->data['CashReceipt']['credit_accounting_code_id']==0){
          $this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en un recibo de caja!  No se guardó el recibo de caja.'), 'default',array('class' => 'error-message'));
        }
        else {				
          $datasource=$this->CashReceipt->getDataSource();
          $datasource->begin();
          // first bring back everything to original state
          $removedOK='0';
          $oldAccountingRegisterCode="";
          try {
            // first remove existing data: invoice, accounting registers, accounting register invoice
            $oldCashReceipt=$this->CashReceipt->find('first',array(
              'conditions'=>array(
                'CashReceipt.id'=>$id,
              )
            ));
            
            $oldCashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
              'fields'=>array('CashReceiptInvoice.id','CashReceiptInvoice.invoice_id'),
              'conditions'=>array(
                'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
              ),				
            ));
            if (!empty($oldCashReceiptInvoices)){
              foreach ($oldCashReceiptInvoices as $oldCashReceiptInvoice){
                //set bool_paid to false
                $invoiceArray=[];
                $invoiceArray['Invoice']['id']=$oldCashReceiptInvoice['CashReceiptInvoice']['invoice_id'];
                $invoiceArray['Invoice']['bool_paid']='0';
                $this->Invoice->id=$oldCashReceiptInvoice['CashReceiptInvoice']['invoice_id'];
                $this->Invoice->save($invoiceArray);
                // remove the CashReceiptInvoice
                $this->CashReceiptInvoice->delete($oldCashReceiptInvoice['CashReceiptInvoice']['id']);

              }
            }
            
            $oldAccountingRegisterCashReceipts=[];
            if (!empty($oldCashReceipt)){
            $oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
              'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
              'conditions'=>array(
                'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
              )
            ));
            }
            
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
                //first get the old cash register code
                $oldAccountingRegister=$this->AccountingRegister->find('first',array(
                  'conditions'=>array(
                    'AccountingRegister.id'=>$oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id'],
                  ),
                ));
                if (!empty($oldAccountingRegister)){
                  $oldAccountingRegisterCode=$oldAccountingRegister['AccountingRegister']['register_code'];
                }
                $this->AccountingRegister->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['accounting_register_id']);
                
                // then remove the register cash receipt link
                $this->AccountingRegisterCashReceipt->delete($oldAccountingRegisterCashReceipt['AccountingRegisterCashReceipt']['id']);
              }
            }
            // then remove the cash receipt
            $this->CashReceipt->delete($oldCashReceipt['CashReceipt']['id']);						
            
            $removedOK=true;
            
            $datasource->commit();
            $this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el recipo de caja # ".$this->request->data['CashReceipt']['receipt_code']);
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('Los datos del recibo de caja no se podían remover.'), 'default',array('class' => 'error-message'));
          }				
          //echo "everything back to original state";
          if ($removedOK){
            $datasource->begin();
            try {
              //$total_cash_receipt=$this->request->data['CashReceipt']['amount_total_payment'];
              $total_cash_receipt=0;
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                $total_cash_receipt=$this->request->data['CashReceipt']['amount_total_payment'];
                $this->request->data['CashReceipt']['amount']=$this->request->data['CashReceipt']['amount_total_payment'];
              }
              else {
                $total_cash_receipt=$this->request->data['CashReceipt']['amount'];
              }
              $this->CashReceipt->create();
              if (!$this->CashReceipt->save($this->request->data)) {
                echo "Problema guardando el recibo de caja";
                pr($this->validateErrors($this->CashReceipt));
                throw new Exception();
              }
              $cash_receipt_id=$this->CashReceipt->id;
              
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                $total_cuentas_por_cobrar=$this->request->data['CashReceipt']['amount_cuentas_por_cobrar'];
                $total_increment=$this->request->data['CashReceipt']['amount_increment'];
                $total_discount=$this->request->data['CashReceipt']['amount_discount'];
                $total_erdiff=$this->request->data['CashReceipt']['amount_difference_exchange_rate'];
                $total_retention=$this->request->data['CashReceipt']['amount_retention_paid'];
                $total_cash_receipt=$this->request->data['CashReceipt']['amount_total_payment'];
              }
              else {
                $total_cash_receipt=$this->request->data['CashReceipt']['amount'];
                $total_retention=0;
              }
              
              if ($currency_id==CURRENCY_USD){
                $this->loadModel('ExchangeRate');
                $applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptDateAsString);
                $total_CS=round($total_cash_receipt*$applicableExchangeRate['ExchangeRate']['rate'],2);
                $total_retention_CS=round($total_retention*$applicableExchangeRate['ExchangeRate']['rate'],2);
              }
              else {
                $total_CS=$total_cash_receipt;
                $total_retention_CS=$total_retention;
              }
              
              $accountingRegisterData['AccountingRegister']['register_date']=$cashReceiptDateArray;
              
              $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
              if (!empty($oldAccountingRegisterCode)){
                $registerCode=$oldAccountingRegisterCode;
              }
              else {
                $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
              }
              $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                //$accountingRegisterData['AccountingRegister']['amount']=$total_CS+$total_increment+$total_erdiff-$total_discount;
                $accountingRegisterData['AccountingRegister']['amount']=$total_CS+$total_retention_CS+$total_discount;
              }
              else {
                $accountingRegisterData['AccountingRegister']['amount']=$total_CS;
              }
              $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingRegister']['concept']="Recibo de Caja ".$receipt_code;
              $accountingRegisterData['AccountingRegister']['observation']="Recibo de Caja ".$receipt_code;
              
              $totalDebit=0;
              $totalCredit=0;
              
              // THIS GOES TO THE CASHBOX
              $accountingRegisterData['AccountingMovement'][0]['accounting_code_id']=$this->request->data['CashReceipt']['cashbox_accounting_code_id'];
              //$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['cashbox_accounting_code_id']);
              $accountingRegisterData['AccountingMovement'][0]['concept']="Recibo de Caja ".$receipt_code;
              $accountingRegisterData['AccountingMovement'][0]['currency_id']=CURRENCY_CS;
              $accountingRegisterData['AccountingMovement'][0]['debit_amount']=$total_CS;
              $totalDebit+=$total_CS;
              //pr($accountingRegisterData);
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_OTHER){
                $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=$this->request->data['CashReceipt']['credit_accounting_code_id'];
                //$accountingCode=$this->AccountingCode->read(null,$this->request->data['CashReceipt']['credit_accounting_code_id']);
                $accountingRegisterData['AccountingMovement'][1]['concept']="Recibo de Caja ".$receipt_code;
                $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$total_CS;
                $totalCredit+=$total_CS;
              }
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                $client_id=$this->request->data['CashReceipt']['client_id'];
                $this->loadModel('ThirdParty');
                $thisClient=$this->ThirdParty->read(null,$client_id);
                
                if (empty($thisClient['ThirdParty']['accounting_code_id'])){
                  $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_COBRAR_CLIENTES);
                }
                else {
                  $accountingRegisterData['AccountingMovement'][1]['accounting_code_id']=$thisClient['ThirdParty']['accounting_code_id'];
                  //$accountingCode=$this->AccountingCode->read(null,$thisClient['ThirdParty']['accounting_code_id']);
                }
                $accountingRegisterData['AccountingMovement'][1]['concept']="Recibo de Caja ".$receipt_code;
                $accountingRegisterData['AccountingMovement'][1]['currency_id']=CURRENCY_CS;
                $accountingRegisterData['AccountingMovement'][1]['credit_amount']=$total_cuentas_por_cobrar;
                $totalCredit+=$total_cuentas_por_cobrar;
                if ($total_increment>0){
                  $accountingRegisterData['AccountingMovement'][2]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_OTROS_INGRESOS;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_CUENTAS_OTROS INGRESOS);
                  $accountingRegisterData['AccountingMovement'][2]['concept']="Recibo de Caja ".$receipt_code;
                  $accountingRegisterData['AccountingMovement'][2]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][2]['credit_amount']=$total_increment;
                  $totalCredit+=$total_increment;
                }
                if ($total_discount>0){
                  $accountingRegisterData['AccountingMovement'][3]['accounting_code_id']=ACCOUNTING_CODE_DESCUENTO_SOBRE_VENTA;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_DESCUENTO_SOBRE_VENTA);
                  $accountingRegisterData['AccountingMovement'][3]['concept']="Recibo de Caja ".$receipt_code;
                  $accountingRegisterData['AccountingMovement'][3]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][3]['debit_amount']=$total_discount;
                  $totalDebit+=$total_discount;
                }
                if ($total_erdiff>0){
                  $accountingRegisterData['AccountingMovement'][4]['accounting_code_id']=ACCOUNTING_CODE_INGRESOS_DIFERENCIA_CAMBIARIA;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_INGRESOS_DIFERENCIA_CAMBIARIA);
                  $accountingRegisterData['AccountingMovement'][4]['concept']="Recibo de Caja ".$receipt_code;
                  $accountingRegisterData['AccountingMovement'][4]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][4]['credit_amount']=$total_erdiff;
                  $totalCredit+=$total_erdiff;
                }
                if ($this->request->data['CashReceipt']['bool_retention']){
                  $accountingRegisterData['AccountingMovement'][5]['accounting_code_id']=ACCOUNTING_CODE_RETENCIONES_POR_COBRAR;
                  //$accountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_RETENCIONES_POR_COBRAR);
                  $accountingRegisterData['AccountingMovement'][5]['concept']="Retención Recibo de Caja ".$receipt_code;
                  $accountingRegisterData['AccountingMovement'][5]['currency_id']=CURRENCY_CS;
                  $accountingRegisterData['AccountingMovement'][5]['debit_amount']=$total_retention_CS;
                  $totalDebit+=$total_retention_CS;
                }
                // ADDED 20160309 to deal with too unequal amounts (especially for receipts where too much was paid)
                if (abs($totalCredit-$totalDebit)>=0.005){
                  $accountingRegisterData['AccountingMovement'][6]['accounting_code_id']=ACCOUNTING_CODE_CUENTAS_OTROS_INGRESOS;
                  if ($totalCredit>$totalDebit){
                    $accountingRegisterData['AccountingMovement'][6]['concept']="Recibo de Caja ".$receipt_code;
                    $accountingRegisterData['AccountingMovement'][6]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][6]['debit_amount']=($totalCredit-$totalDebit);
                  }
                  else {
                    $accountingRegisterData['AccountingMovement'][6]['concept']="Recibo de Caja ".$receipt_code;
                    $accountingRegisterData['AccountingMovement'][6]['currency_id']=CURRENCY_CS;
                    $accountingRegisterData['AccountingMovement'][6]['credit_amount']=($totalDebit-$totalCredit);
                  }
                }
              }
              //pr($accountingRegisterData);
              
              $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
              $this->recordUserAction($this->AccountingRegister->id,"add",null);
              //echo "accounting register saved for otros ingresos<br/>";
          
              $AccountingRegisterCashReceiptData=[];
              $AccountingRegisterCashReceiptData['accounting_register_id']=$accounting_register_id;
              $AccountingRegisterCashReceiptData['cash_receipt_id']=$cash_receipt_id;
              $this->AccountingRegisterCashReceipt->create();
              if (!$this->AccountingRegisterCashReceipt->save($AccountingRegisterCashReceiptData)) {
                pr($this->validateErrors($this->AccountingRegisterCashReceipt));
                echo "problema al guardar el lazo entre asiento contable y recibo de caja";
                throw new Exception();
              }
              //echo "link accounting register cash receipt saved<br/>";			
          
              if ($cash_receipt_type_id==CASH_RECEIPT_TYPE_CREDIT){
                foreach ($this->request->data['Invoice'] as $invoice){
                  $existingInvoice=[];
                  $existingInvoice=$this->Invoice->find('first',array(
                    'conditions'=>array(
                      'Invoice.id'=>$invoice['invoice_id'],
                    ),
                  ));
                  //pr($existingInvoice);
                  
                  $invoice_total_price_invoice_currency=$existingInvoice['Invoice']['total_price'];
                  $invoice_currency_id=$existingInvoice['Invoice']['currency_id'];
                  
                  if ($invoice_currency_id==CURRENCY_CS){
                    $invoice_total_price_CS=$invoice_total_price_invoice_currency;
                  }
                  elseif ($invoice_currency_id==CURRENCY_USD){
                    $invoiceDate=$existingInvoice['Invoice']['invoice_date'];
                    $invoiceExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoiceDate);
                    $exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
                    $invoice_total_price_CS=$invoice_total_price_invoice_currency*$exchangeRateInvoiceDate;
                  }
                  else {
                    $invoice_total_price_CS=$invoice_total_price_invoice_currency;
                  }
                  
                  $invoice_applicable_exhange_rate_difference=$invoice['exchangeratedifference'];
                  //echo "applicacle exchange rate difference is ".$invoice_applicable_exhange_rate_difference."<br/>";
                  //echo "invoice id is ".$invoice['invoice_id']."<br>";
                  //echo "total price for invoice C$ is ".$invoice_total_price_CS."<br/>";
                  $invoice_paid_already_CS=$this->Invoice->getAmountPaidAlreadyCS($existingInvoice['Invoice']['id']);
                  //echo "total paid already C$ is ".$invoice_paid_already_CS."<br/>";
                  //$invoice_pending_CS=$invoice_total_price_CS-$invoice_paid_already_CS;
                  //MODIFIED 20160309
                  $invoice_pending_CS=$invoice_total_price_CS+$invoice_applicable_exhange_rate_difference-$invoice_paid_already_CS;
                  //echo "pending payment is ".$invoice_pending_CS."<br/>";
                  $amount_paid_CS=0;
                  //$amount_paid_CS=$invoice['creditpayment']+$invoice['difpayment'];
                  // MODIFIED 20160122
                  //$amount_paid_CS=$invoice['creditpayment']+$invoice['difpayment']+$invoice['descpayment']-$invoice['incpayment'];
                  // MODIFIED 20160308
                  $amount_paid_CS=$invoice['creditpayment']+$invoice['descpayment']+$invoice['difpayment'];
                  //echo "amount paid CS is ".$amount_paid_CS."<br/>";
                  $InvoiceData=[];
                  // 20160122 NO TIENE SENTIDO AÑADIR A LO EXISTENTE EN INVOICE PORQUE ASI SE HARÍA MUY LARGA LA LISTA
                  // EN TEORIA Y PRACTICA SOLAMENTE DEBERIA HABER UNA SOLA RETENCION PARA UNA FACTURA
                  $InvoiceData['Invoice']['bool_retention']=$this->request->data['CashReceipt']['bool_retention'];
                  $InvoiceData['Invoice']['retention_number']=$this->request->data['CashReceipt']['retention_number'];
                  $InvoiceData['Invoice']['retention_amount']=$invoice['retentionpayment'];
                  
                  if (($invoice_pending_CS-$amount_paid_CS)<0.01){
                    $InvoiceData['Invoice']['bool_paid']=true;
                    //echo "paid set to true<br/>";
                  }
                  else {
                    $InvoiceData['Invoice']['bool_paid']='0';
                    //echo "paid set to false<br/>";
                  }
                  $this->Invoice->id=$invoice['invoice_id'];
                  if (!$this->Invoice->save($InvoiceData)) {
                    pr($this->validateErrors($this->Invoice));
                    echo "problema al guardar la factura";
                    throw new Exception();
                  }
                  
                  if ($invoice['payment']==NULL){
                    $invoice['payment']=0;
                  }
                  if ($invoice['retentionpayment']==NULL){
                    $invoice['retentionpayment']=0;
                  }
                  if (($invoice['payment']>0)||($invoice['retentionpayment']>0)){
                    $CashReceiptInvoiceData=[];
                    $CashReceiptInvoiceData['cash_receipt_id']=$cash_receipt_id;
                    $CashReceiptInvoiceData['invoice_id']=$invoice['invoice_id'];
                    $CashReceiptInvoiceData['amount']=$invoice['payment']-$invoice['increment']+$invoice['discount']-$invoice['exchangeratedifference'];
                    $CashReceiptInvoiceData['increment']=$invoice['increment'];
                    $CashReceiptInvoiceData['discount']=$invoice['discount'];
                    $CashReceiptInvoiceData['erdiff']=$invoice['exchangeratedifference'];
                    $CashReceiptInvoiceData['payment']=$invoice['payment'];
                    $CashReceiptInvoiceData['payment_retention']=$invoice['retentionpayment'];
                    $CashReceiptInvoiceData['payment_credit_CS']=$invoice['creditpayment'];
                    $CashReceiptInvoiceData['payment_increment_CS']=$invoice['incpayment'];
                    $CashReceiptInvoiceData['payment_discount_CS']=$invoice['descpayment'];
                    $CashReceiptInvoiceData['payment_erdiff_CS']=$invoice['difpayment'];
                    $CashReceiptInvoiceData['currency_id']=$currency_id;
                    
                    
                    $this->CashReceiptInvoice->create();
                    if (!$this->CashReceiptInvoice->save($CashReceiptInvoiceData)) {
                      pr($this->validateErrors($this->CashReceiptInvoice));
                      echo "problema al guardar el lazo entre el recibo de caja y la factura";
                      throw new Exception();
                    }
                  }
                }
              }
              $datasource->commit();
              $this->recordUserAction($id,"edit",null);
              // SAVE THE USERLOG FOR THE REMISSION
              $this->recordUserActivity($this->Session->read('User.username'),"Recibo de caja registrado con número ".$this->request->data['CashReceipt']['receipt_code']);
              $this->Session->setFlash(__('Se guardó el recibo de caja.'),'default',array('class' => 'success'),'default',array('class' => 'success'));
              return $this->redirect(array('action' => 'index'));
            }
            catch(Exception $e){
              $datasource->rollback();
              pr($e);
              $this->Session->setFlash(__('No se podía guardar el recibo de caja.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'), 'default',array('class' => 'error-message'));
            }
          }
        }
      
      }
    }		 
		else {
			$options = [
        'conditions' => [
          'CashReceipt.id' => $id
        ]
      ];
			$this->request->data = $this->CashReceipt->find('first', $options);
      $plantId=$this->request->data['CashReceipt']['plant_id'];
		}
		
    if (count($plants) == 1){
      $plantId=array_keys($plants)[0];
    }
    elseif (count($plants) > 1 && $plantId == 0){
      //if (!empty($_SESSION['plantId'])){
      //  $plantId = $_SESSION['plantId'];
      //}
      //else {
      //  $plantId=0;
      //}
    }
    $_SESSION['plantId']=$plantId;
    $this->set(compact('plantId'));
    
    $cashReceiptTypes = $this->CashReceipt->CashReceiptType->find('list');
		$currencies = $this->CashReceipt->Currency->find('list');
		$clients = $this->CashReceipt->Client->find('list',array(
			'conditions'=>array(
				'bool_provider'=>'0',
				'bool_active'=>true,
			),
			'order'=>'company_name',
		));
		
		
		if ($previousCashReceipt['CashReceipt']['cash_receipt_type_id']==CASH_RECEIPT_TYPE_CREDIT){
			$keys=array_keys($clients);
			for ($c=0;$c<count($keys);$c++){
				$pendingInvoices=$this->Invoice->find('all',array(
					'fields'=>array(
						'Invoice.id','Invoice.invoice_code',
					),
					'conditions'=>array(
						'Invoice.bool_annulled'=>'0',
						'Invoice.bool_paid'=>'0',
						'Invoice.client_id'=>$keys[$c],
					),
				));
				if (empty($pendingInvoices)){
					if ($keys[$c]!=$previousCashReceipt['CashReceipt']['client_id']){
						unset($clients[$keys[$c]]);
					}
				}
			}
		}
		
		$accountingCodes = $this->AccountingCode->find('list',array('fields'=>array('AccountingCode.id','AccountingCode.shortfullname')));
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
		));
		$cashboxAccountingCodes = $this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			),
			'order'=>'AccountingCode.lft',
		));
		
		$this->set(compact('cashReceiptTypes', 'currencies', 'clients','cash_receipt_type_id','accountingCodes','newCashReceiptCode','postedInvoiceData','exchangeRateCashReceipt','cashboxAccountingCodes','bool_first_load','cashReceipt'));
		
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

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->CashReceipt->id = $id;
		if (!$this->CashReceipt->exists()) {
			throw new NotFoundException(__('Recibo de Caja no válido'));
		}
		
		$linkedCashReceipt=$this->CashReceipt->read(null,$id);
		$receipt_code=$linkedCashReceipt['CashReceipt']['receipt_code'];
		
		$this->loadModel('Invoice');
		$this->loadModel('CashReceipt');
		$this->loadModel('CashReceiptInvoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$this->request->allowMethod('post', 'delete');
		$datasource=$this->CashReceipt->getDataSource();
		$datasource->begin();
		try {
			$oldCashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
				'fields'=>array('CashReceiptInvoice.id','CashReceiptInvoice.invoice_id'),
				'conditions'=>array(
					'cash_receipt_id'=>$id,
				),				
			));
			if (!empty($oldCashReceiptInvoices)){
				foreach ($oldCashReceiptInvoices as $oldCashReceiptInvoice){
					//set bool_paid to false
					$invoiceArray=[];
					$invoiceArray['Invoice']['id']=$oldCashReceiptInvoice['CashReceiptInvoice']['invoice_id'];
					$invoiceArray['Invoice']['bool_paid']='0';
					$this->Invoice->id=$oldCashReceiptInvoice['CashReceiptInvoice']['invoice_id'];
					$this->Invoice->save($invoiceArray);
					// remove the CashReceiptInvoice
					$this->CashReceiptInvoice->delete($oldCashReceiptInvoice['CashReceiptInvoice']['id']);
				}
			}

			
			// first remove existing data: cash receipt, accounting registers, accounting register cash receipt				
			$oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
				'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
				'conditions'=>array(
					'cash_receipt_id'=>$id,
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
			$success=$this->CashReceipt->delete($id);
			if (!$success) {
				echo "problema al eliminar el recibo de caja";
				pr($this->validateErrors($this->CashReceipt));
				throw new Exception();
			}
			
			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Recibo con código ".$receipt_code." eliminado");			
			$this->Session->setFlash(__('Se eliminó el recibo de caja.'), 'default',array('class' => 'success'));
		} 		
		catch(Exception $e){
			$datasource->rollback();
			pr($e);					
			$this->Session->setFlash(__('No se podía eliminar el recibo de caja.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
		}
		
		return $this->redirect(array('action' => 'index'));
	}
	
	public function annul($id = null) {
		$this->CashReceipt->id = $id;
		if (!$this->CashReceipt->exists()) {
			throw new NotFoundException(__('Recibo de Caja no válido'));
		}
		
		$linkedCashReceipt=$this->CashReceipt->read(null,$id);
		$receipt_code=$linkedCashReceipt['CashReceipt']['receipt_code'];
		
		$this->loadModel('Invoice');
		$this->loadModel('CashReceipt');
		$this->loadModel('CashReceiptInvoice');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		$this->loadModel('AccountingRegisterCashReceipt');
		
		$this->request->allowMethod('post', 'delete');
		
		$oldCashReceipt=$this->CashReceipt->find('first',array(
			'conditions'=>array(
				'CashReceipt.id'=>$id,
			)
		));
		$datasource=$this->CashReceipt->getDataSource();
		$datasource->begin();
		try {
			$oldCashReceiptInvoices=$this->CashReceiptInvoice->find('all',array(
				'fields'=>array('CashReceiptInvoice.id','CashReceiptInvoice.invoice_id'),
				'conditions'=>array(
					'cash_receipt_id'=>$oldCashReceipt['CashReceipt']['id']
				),				
			));
			if (!empty($oldCashReceiptInvoices)){
				foreach ($oldCashReceiptInvoices as $oldCashReceiptInvoice){
					//set bool_paid to false
					$invoiceArray=[];
					$invoiceArray['Invoice']['id']=$oldCashReceiptInvoice['CashReceiptInvoice']['invoice_id'];
					$invoiceArray['Invoice']['bool_paid']='0';
					$this->Invoice->id=$oldCashReceiptInvoice['CashReceiptInvoice']['invoice_id'];
					$this->Invoice->save($invoiceArray);
					// remove the CashReceiptInvoice
					$this->CashReceiptInvoice->delete($oldCashReceiptInvoice['CashReceiptInvoice']['id']);
				}
			}
			
			// first remove existing data: cash receipt, accounting registers, accounting register cash receipt				
			$oldAccountingRegisterCashReceipts=$this->AccountingRegisterCashReceipt->find('all',array(
				'fields'=>array('AccountingRegisterCashReceipt.id','AccountingRegisterCashReceipt.accounting_register_id'),
				'conditions'=>array(
					'cash_receipt_id'=>$id
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
			// then annul the cash receipt
			$cashReceiptArray=[];
			$cashReceiptArray['CashReceipt']['id']=$id;
			$cashReceiptArray['CashReceipt']['bool_annulled']=true;
			$cashReceiptArray['CashReceipt']['amount']=0;
			$this->CashReceipt->id=$id;
			$success=$this->CashReceipt->save($cashReceiptArray);
			if (!$success) {
				echo "problema al anular el recibo de caja";
				pr($this->validateErrors($this->CashReceipt));
				throw new Exception();
			}
			
			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Recibo con código ".$receipt_code." eliminado");			
			$this->Session->setFlash(__('Se anuló el recibo de caja.'), 'default',array('class' => 'success'));
		} 		
		catch(Exception $e){
			$datasource->rollback();
			pr($e);					
			$this->Session->setFlash(__('No se podía anular el recibo de caja.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
		}
		
		return $this->redirect(array('action' => 'index'));
	}
}
