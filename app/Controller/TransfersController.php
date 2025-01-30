<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class TransfersController extends AppController {

	public $components = array('Paginator');
	public $helpers = array('PhpExcel'); 

  public function resumenDepositos() {
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
    
    $this->set(compact('startDate','endDate'));
	
    $conditions=[
      'Transfer.transfer_date >='=> $startDate,
      'Transfer.transfer_date <'=> $endDatePlusOne,
      'Transfer.bool_deposit'=> true,
    ];
		$depositCount=	$this->Transfer->find('count', [
			'fields'=>['Transfer.id'],
			'conditions' => $conditions,
		]);
		
		$this->Paginator->settings = [
			'conditions' => $conditions,
			'order' => ['Transfer.transfer_date'=>'ASC','Transfer.transfer_code'=>'ASC'],
			'limit'=>($depositCount!=0?$depositCount:1),
		];

		$deposits = $this->Paginator->paginate('Transfer');
		$this->set(compact('deposits'));
		
    $aco_name="Transfers/crearDeposito";		
		$bool_deposit_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_deposit_add_permission'));
    
		$aco_name="AccountingCodes/index";		
		$bool_accountingcode_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_index_permission'));
		$aco_name="AccountingCodes/add";		
		$bool_accountingcode_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_add_permission'));
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function verDeposito($id = null) {
		if (!$this->Transfer->exists($id)) {
			throw new NotFoundException(__('Invalid deposit'));
		}
		$options = ['conditions' => [
      'Transfer.id' => $id,
      'Transfer.bool_deposit' => 1,
    ]];
		
		$transfer=$this->Transfer->find('first',[
      'conditions' => [
        'Transfer.id' => $id,
        'Transfer.bool_deposit' => 1,
      ],
      'contain'=>[
        'AccountingRegister'=>[
          'AccountingMovement'=>[
            'AccountingCode'
          ],
        ],
        'BankAccountingCode',
        'CashboxAccountingCode',
        'Currency',
        'PaymentDeposit'=>[
          'CashReceipt',
          'Invoice',
          'order'=>'invoice_id ASC, cash_receipt_id ASC'
        ],
      ]
    ]);
		$this->set(compact('transfer'));
		
    $aco_name="Transfers/crearDeposito";		
		$bool_deposit_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_deposit_add_permission'));
    
    $aco_name="Transfers/editarDeposito";		
		$bool_deposit_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_deposit_edit_permission'));
    
    $aco_name="Transfers/eliminarDeposito";		
		$bool_deposit_delete_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_deposit_delete_permission'));
    
		$aco_name="AccountingCodes/index";		
		$bool_accountingcode_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_index_permission'));
		$aco_name="AccountingCodes/add";		
		$bool_accountingcode_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_add_permission'));
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function crearDeposito() {
    $this->loadModel('AccountingCode');
    $this->loadModel('CashReceipt');
    $this->loadModel('Invoice');
    
    $this->CashReceipt->recursive=-1;
    $this->Invoice->recursive=-1;
  
    $undepositedInvoices=$this->Invoice->find('all',[
      'conditions'=>[
        'bool_credit'=>'0',
        'bool_deposited'=>'0',
        'sub_total_price >'=>0,
      ],
      'order'=>'invoice_code DESC'
    ]);
    for ($i=0;$i<count($undepositedInvoices);$i++){
      //pr($undepositedInvoices[$i]);
      
      $invoiceDate=$undepositedInvoices[$i]['Invoice']['invoice_date'];
      $exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoiceDate);
      //pr($exchangeRate);
      $undepositedInvoices[$i]['Invoice']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
      if ($undepositedInvoices[$i]['Invoice']['currency_id']==CURRENCY_CS){
        $undepositedInvoices[$i]['Invoice']['paid_amount_CS']=($undepositedInvoices[$i]['Invoice']['total_price']-$undepositedInvoices[$i]['Invoice']['retention_amount']);
        $undepositedInvoices[$i]['Invoice']['paid_amount_USD']=round(($undepositedInvoices[$i]['Invoice']['total_price']-$undepositedInvoices[$i]['Invoice']['retention_amount'])/$undepositedInvoices[$i]['Invoice']['exchange_rate'],2);
      }
      elseif ($undepositedInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
        
        $undepositedInvoices[$i]['Invoice']['paid_amount_CS']=round(($undepositedInvoices[$i]['Invoice']['total_price']-$undepositedInvoices[$i]['Invoice']['retention_amount'])*$undepositedInvoices[$i]['Invoice']['exchange_rate'],2);
        $undepositedInvoices[$i]['Invoice']['paid_amount_USD']=($undepositedInvoices[$i]['Invoice']['total_price']-$undepositedInvoices[$i]['Invoice']['retention_amount']);
      }
    }
    //pr($undepositedInvoices);
    $this->set(compact('undepositedInvoices'));
    
    $undepositedCashReceipts=$this->CashReceipt->find('all',[
      'conditions'=>[
        'bool_deposited'=>'0',
        'amount >'=>0
      ],
      'order'=>'receipt_code DESC'
    ]);
    
    for ($i=0;$i<count($undepositedCashReceipts);$i++){
      //pr($undepositedCashReceipts[$i]);
      $cashReceiptDate=$undepositedCashReceipts[$i]['CashReceipt']['receipt_date'];
      $exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptDate);
      //pr($exchangeRate);
      $undepositedCashReceipts[$i]['CashReceipt']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
      if ($undepositedCashReceipts[$i]['CashReceipt']['currency_id']==CURRENCY_CS){
        $undepositedCashReceipts[$i]['CashReceipt']['paid_amount_CS']=($undepositedCashReceipts[$i]['CashReceipt']['amount']);
        $undepositedCashReceipts[$i]['CashReceipt']['paid_amount_USD']=round(($undepositedCashReceipts[$i]['CashReceipt']['amount'])/$undepositedCashReceipts[$i]['CashReceipt']['exchange_rate'],2);
      }
      elseif ($undepositedCashReceipts[$i]['CashReceipt']['currency_id']==CURRENCY_USD){
        
        $undepositedCashReceipts[$i]['CashReceipt']['paid_amount_CS']=round(($undepositedCashReceipts[$i]['CashReceipt']['amount'])*$undepositedCashReceipts[$i]['CashReceipt']['exchange_rate'],2);
        $undepositedCashReceipts[$i]['CashReceipt']['paid_amount_USD']=($undepositedCashReceipts[$i]['CashReceipt']['amount']);
      }
    }
    //pr($undepositedInvoices);
    //pr($undepositedCashReceipts);
    $this->set(compact('undepositedCashReceipts'));
    
    
    $undepositedPayments=$undepositedInvoices;
    foreach ($undepositedCashReceipts as $undepositedCashReceipt){
      $undepositedPayments[]=$undepositedCashReceipt;
    }
    usort($undepositedPayments,[$this,'sortByDateDescending']);
    /*
    //pr($undepositedPayments);
    //echo "count Invoices is: ".(count($undepositedInvoices))."<br>";
    //echo "count CR is: ".(count($undepositedCashReceipts))."<br>";
    $undepositedPayments=[];
    $invoiceCounter=0;
    $receiptCounter=0;
    if (!empty($undepositedInvoices)&& !empty($undepositedCashReceipts)){
      for ($invoiceCounter=0;$invoiceCounter<count($undepositedInvoices);$invoiceCounter++){
        //echo "started for loop with invoice counter ".$invoiceCounter."<br/>";
        //pr($undepositedInvoices[$invoiceCounter]);
        $invoiceDate=$undepositedInvoices[$invoiceCounter]['Invoice']['invoice_date'];
        if ($receiptCounter< count($undepositedCashReceipts)){
          $cashReceiptDate=$undepositedCashReceipts[$receiptCounter]['CashReceipt']['receipt_date'];
        }
        //echo "invoiceCounter main loop is ".$invoiceCounter."<br/>";
        while ($invoiceCounter < count($undepositedInvoices) && $invoiceDate >= $cashReceiptDate){
          //$invoiceDateTime=new DateTime($invoiceDate);
          //$receiptDateTime=new DateTime($cashReceiptDate);
          //echo "invoice date time is ".($invoiceDateTime->format('d-m-Y'))." y receipt date time is ".($receiptDateTime->format('d-m-Y'))."<br>";
          $undepositedPayments[]=$undepositedInvoices[$invoiceCounter]['Invoice'];
          //echo "invoiceCounter small loop is ".$invoiceCounter."<br/>";  
          //echo "invoice ".$undepositedInvoices[$invoiceCounter]['Invoice']['invoice_code']."(".$undepositedInvoices[$invoiceCounter]['Invoice']['invoice_date'].") added<br>";
          $invoiceCounter++;
          if ($invoiceCounter< count($undepositedInvoices)){
            
            $invoiceDate=$undepositedInvoices[($invoiceCounter+1)]['Invoice']['invoice_date'];
            if ($receiptCounter>=count($undepositedCashReceipts) || $invoiceDate>$cashReceiptDate){
              
            }
          } 
        }
        //echo "receiptCounter smaller than count: ".($receiptCounter < count($undepositedCashReceipts))."<br>";
        //echo "date comparison: ".($invoiceDate < $cashReceiptDate)."<br>";
        //echo "invoiceCounter main loop is ".$invoiceCounter."<br/>";
        while ($receiptCounter < count($undepositedCashReceipts) && (($invoiceDate < $cashReceiptDate) || $invoiceCounter>=count($undepositedInvoices))){
          
          //$invoiceDateTime=new DateTime($invoiceDate);
          //$receiptDateTime=new DateTime($cashReceiptDate);
          //echo "invoice date time is ".($invoiceDateTime->format('d-m-Y'))." y receipt date time is ".($receiptDateTime->format('d-m-Y'))."<br>";
          $undepositedPayments[]=$undepositedCashReceipts[$receiptCounter]['CashReceipt'];
          //echo "receiptCounter small loop is ".$receiptCounter."<br/>";
          //echo "receipt ".$undepositedCashReceipts[$receiptCounter]['CashReceipt']['receipt_code']."(".$undepositedCashReceipts[$receiptCounter]['CashReceipt']['receipt_date'].") added<br>";
          $receiptCounter++;
          if ($receiptCounter< count($undepositedCashReceipts)){
            $cashReceiptDate=$undepositedCashReceipts[$receiptCounter]['CashReceipt']['receipt_date'];
          }
        } 
      }
    }
    elseif (!empty($undepositedInvoices)){
      for ($invoiceCounter=0;$invoiceCounter<count($undepositedInvoices);$invoiceCounter++){
        $invoiceDate=$undepositedInvoices[$invoiceCounter]['Invoice']['invoice_date'];
        while ($invoiceCounter < count($undepositedInvoices)){
          $undepositedPayments[]=$undepositedInvoices[$invoiceCounter]['Invoice'];
          $invoiceCounter++;
          if ($invoiceCounter< count($undepositedInvoices)){
            
            $invoiceDate=$undepositedInvoices[$invoiceCounter]['Invoice']['invoice_date'];
          } 
        }
      }
    }
    elseif (!empty($undepositedCashReceipts)){
      $cashReceiptDate=$undepositedCashReceipts[$receiptCounter]['CashReceipt']['receipt_date'];
      while ($receiptCounter < count($undepositedCashReceipts)){
        $undepositedPayments[]=$undepositedCashReceipts[$receiptCounter]['CashReceipt'];
        $receiptCounter++;
        if ($receiptCounter< count($undepositedCashReceipts)){
          $cashReceiptDate=$undepositedCashReceipts[$receiptCounter]['CashReceipt']['receipt_date'];
        }
      } 
    }
    //echo "receipt counter is ".$receiptCounter."<br>";
    //pr($undepositedPayments);
    */
    $this->set(compact('undepositedPayments'));
  
		if ($this->request->is('post')) {
      //pr($this->request->data);
			$this->loadModel('AccountingCode');
			$this->loadModel('AccountingRegister');
			$this->loadModel('ClosingDate');
      $this->loadModel('PaymentDeposit');
			
			$transfer_date=$this->request->data['Transfer']['transfer_date'];
			$transferDateAsString = $this->Transfer->deconstruct('transfer_date', $this->request->data['Transfer']['transfer_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
						
			$transferDateArray=[];
			$transferDateArray['year']=$transfer_date['year'];
			$transferDateArray['month']=$transfer_date['month'];
			$transferDateArray['day']=$transfer_date['day'];
					
			$deposit_code=$this->request->data['Transfer']['transfer_code'];
			$namedDeposits=$this->Transfer->find('all',array(
				'conditions'=>array(
					'transfer_code'=>$deposit_code,
				)
			));
			
			$currency_id=$this->request->data['Transfer']['currency_id'];
			/*
      $sumDebit=0;
			$sumCredit=0;
			$boolMissingDebitAccount='0';
			$boolMissingCreditAccount='0';
      //pr($this->request->data);
			
      foreach ($this->request->data['AccountingMovement'] as $accountingMovement){
				if (!empty($accountingMovement['debit_amount'])&&$accountingMovement['debit_amount']>0){
					$sumDebit+=$accountingMovement['debit_amount'];
					if ($accountingMovement['accounting_code_id']==0){
						$boolMissingDebitAccount=true;
					}
				}
				if (!empty($accountingMovement['credit_amount'])&&$accountingMovement['credit_amount']>0){
					$sumCredit+=$accountingMovement['credit_amount'];
					if ($accountingMovement['accounting_code_id']==0){
						$boolMissingCreditAccount=true;
					}
				}
			}
			
			if ($sumDebit!=$sumCredit){
				$this->Session->setFlash(__('La suma de DEBE y la suma de HABER no son iguales!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingDebitAccount){
				$this->Session->setFlash(__('Hay una cuenta de DEBE que no lleva la cuenta contable!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingCreditAccount){
				$this->Session->setFlash(__('Hay una cuenta de HABER que no lleva la cuenta contable!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			else
      */  
      if (count($namedDeposits)>0){
				$this->Session->setFlash(__('Ya existe una transferencia con el mismo código!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			else if ($transferDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de transferencia no puede estar en el futuro!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			elseif ($transferDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			
			/*
			else if ($this->request->data['Transfer']['bank_accounting_code_id']==0){
				//pr($this->request->data);
				$this->Session->setFlash(__('Se debe seleccionar la cuenta bancaria en una transferencia!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			*/
			else if ($this->request->data['Transfer']['cashbox_accounting_code_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar la caja de donde se transfiere el dinero!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			else {
        //pr($this->request->data);
				$datasource=$this->Transfer->getDataSource();
				$datasource->begin();
      
				try {
          
					$total_transfer=$this->request->data['Transfer']['amount_cs'];
					$currency_id=$this->request->data['Transfer']['currency_id'];
					
					// first prepare the accounting registers
					// debit = cost_accounting_code_id selected
					// credit = bank_accounting_code_id selected
					// amount = total
          // NO NEED BECAUSE VIEW ALREADY PROVIDES VALUES IN CS
					//if ($currency_id==CURRENCY_USD){
					//	$this->loadModel('ExchangeRate');
					//	$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($transferDateAsString);
					//	$total_CS=round($total_transfer*$applicableExchangeRate['ExchangeRate']['rate'],2);
					//}
					//else {
					//	$total_CS=$total_transfer;
					//}
					
          $accountingMovementData=[];
          $accountingMovementData[0]['accounting_code_id']=ACCOUNTING_CODE_BANK_CS;
          $accountingMovementData[0]['concept']=("Transfer ".$deposit_code);
          $accountingMovementData[0]['debit_amount']=$total_transfer;
          $accountingMovementData[1]['accounting_code_id']=ACCOUNTING_CODE_CASHBOX_MAIN;
          $accountingMovementData[1]['concept']=("Transfer ".$deposit_code);
          $accountingMovementData[1]['credit_amount']=$total_transfer;
          
          
					$accountingRegisterData['AccountingRegister']['register_date']=$transferDateArray;
					$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
					$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
					$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
					$accountingRegisterData['AccountingRegister']['amount']=$total_transfer;
					$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
					$accountingRegisterData['AccountingRegister']['concept']="Transfer ".$deposit_code;
					$accountingRegisterData['AccountingRegister']['observation']="Transfer ".$deposit_code;
				
					//$accountingRegisterData['AccountingMovement']=$this->request->data['AccountingMovement'];
          $accountingRegisterData['AccountingMovement']=$accountingMovementData;
          // no need as deposit already converts to amount cs in view
					//if ($currency_id==CURRENCY_USD){
					//	for ($a=0;$a<count($accountingRegisterData['AccountingMovement']);$a++){
					//		if (!empty($accountingRegisterData['AccountingMovement'][$a]['debit_amount'])){
					//			$accountingRegisterData['AccountingMovement'][$a]['debit_amount']=round($accountingRegisterData['AccountingMovement'][$a]['debit_amount']*$applicableExchangeRate['ExchangeRate']['rate'],2);
					//		}
					//		if (!empty($accountingRegisterData['AccountingMovement'][$a]['credit_amount'])){
					//			$accountingRegisterData['AccountingMovement'][$a]['credit_amount']=round($accountingRegisterData['AccountingMovement'][$a]['credit_amount']*$applicableExchangeRate['ExchangeRate']['rate'],2);
					//		}
					//	}
					//}
					
					//pr($accountingRegisterData);
					$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
					$this->recordUserAction($this->AccountingRegister->id,"add",null);
					
					$this->request->data['Transfer']['accounting_register_id']=$accounting_register_id;
					
					$this->Transfer->create();
					if (!$this->Transfer->save($this->request->data)) {
						echo "Problema guardando la transferencia";
						pr($this->validateErrors($this->Transfer));
						throw new Exception();
					}
					$transferId=$this->Transfer->id;
					
          if (!empty($this->request->data['Payment']['Invoice'])){
            foreach ($this->request->data['Payment']['Invoice'] as $invoiceId => $depositAmount){
              // 20190103 added because somehow last element in row contains a selector
              if (is_array($depositAmount)){
                $depositAmount=$depositAmount['selector'];
              }
              
              //echo "printing the deposit amount...";
              //pr($depositAmount);
               //echo "invoice id ".$invoiceId."  has an amount of ".$depositAmount."<br>";
               if ($depositAmount>0){
                  if (is_array($depositAmount)){
                    //pr($invoiceId);
                    //pr($depositAmount);
                  }
                  $boolDeposited = $this->Invoice->setDepositedStatus($invoiceId,$depositAmount,$currency_id);
                  //echo "bool deposited is ".$boolDeposited."<br/>";
                  
                  $paymentDepositArray=[];
                  $paymentDepositArray['PaymentDeposit']['invoice_id']=$invoiceId;
                  $paymentDepositArray['PaymentDeposit']['transfer_id']=$transferId;
                  $paymentDepositArray['PaymentDeposit']['amount']=$depositAmount;
                  $paymentDepositArray['PaymentDeposit']['currency_id']=$currency_id;
                  $paymentDepositArray['PaymentDeposit']['bool_deposit_complete']=$boolDeposited;
                  
                  $this->PaymentDeposit->create();
                  if (!$this->PaymentDeposit->save($paymentDepositArray)) {
                    echo "Problema guardando el registro que entrelaza el depósito y la factura";
                    pr($this->validateErrors($this->PaymentDeposit));
                    throw new Exception();
                  }  
                  if ($boolDeposited){
                    //echo  "setting deposited to true";  
                    $this->Invoice->id=$invoiceId;
                    $invoiceArray=[];
                    $invoiceArray['Invoice']['id']=$invoiceId;
                    $invoiceArray['Invoice']['bool_deposited']=true;
                    
                    if (!$this->Invoice->save($invoiceArray)) {
                      echo "Problema guardando el estado (depositado) de la factura";
                      pr($this->validateErrors($this->Invoice));
                      throw new Exception();
                    }
                  }
               }
            }
            
          }
          
          if (!empty($this->request->data['Payment']['CashReceipt'])){
            foreach ($this->request->data['Payment']['CashReceipt'] as $cashReceiptId => $depositAmount){
              //echo "cash receipt id ".$cashReceiptId."  has an amount of ".$depositAmount."<br>";
              if ($depositAmount>0){
                  $boolDeposited = $this->CashReceipt->setDepositedStatus($cashReceiptId,$depositAmount,$currency_id);
               
                  $paymentDepositArray=[];
                  $paymentDepositArray['PaymentDeposit']['cash_receipt_id']=$cashReceiptId;
                  $paymentDepositArray['PaymentDeposit']['transfer_id']=$transferId;
                  $paymentDepositArray['PaymentDeposit']['amount']=$depositAmount;
                  $paymentDepositArray['PaymentDeposit']['currency_id']=$currency_id;
                  $paymentDepositArray['PaymentDeposit']['bool_deposit_complete']=$boolDeposited;
                  
                  $this->PaymentDeposit->create();
                  if (!$this->PaymentDeposit->save($paymentDepositArray)) {
                    echo "Problema guardando el registro que entrelaza el depósito y el recibo de caja";
                    pr($this->validateErrors($this->PaymentDeposit));
                    throw new Exception();
                  }
                  if ($boolDeposited){
                    $this->CashReceipt->id=$cashReceiptId;
                    $cashReceiptArray=[];
                    $cashReceiptArray['CashReceipt']['id']=$cashReceiptId;
                    $cashReceiptArray['CashReceipt']['bool_deposited']=true;
                    
                    if (!$this->CashReceipt->save($cashReceiptArray)) {
                      echo "Problema guardando el estado (depositado) de la factura";
                      pr($this->validateErrors($this->CashReceipt));
                      throw new Exception();
                    }
                  }
               }
            }
          }
          
          $this->recordUserAction($this->Transfer->id,null,null);
          
					$datasource->commit();
					
					$this->recordUserAction();
					// SAVE THE USERLOG FOR THE CHEQUE
					$this->recordUserActivity($this->Session->read('User.username'),"Depósito registrado con concepto ".$this->request->data['Transfer']['concept']);
					$this->Session->setFlash(__('Se guardó el depósito.'),'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'resumenDepositos'));
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar el depósito.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
				}
			}	
		}
		$currencies = $this->Transfer->Currency->find('list');
		
		$bankAccountingCode=$this->Transfer->BankAccountingCode->find('first',array(
			'fields'=>array('BankAccountingCode.lft','BankAccountingCode.rght'),
			'conditions'=>array(
				'BankAccountingCode.id'=>ACCOUNTING_CODE_BANKS,
			),
			
		));
		$bankAccountingCodes = $this->Transfer->BankAccountingCode->find('list',array(
			'fields'=>'BankAccountingCode.description',
			'conditions'=>array(
				'BankAccountingCode.lft >'=>$bankAccountingCode['BankAccountingCode']['lft'],
				'BankAccountingCode.rght <'=>$bankAccountingCode['BankAccountingCode']['rght'],
				'BankAccountingCode.bool_main'=>'0',
			)
		));
		$cashboxAccountingCode=$this->Transfer->CashboxAccountingCode->find('first',array(
			'fields'=>array('CashboxAccountingCode.lft','CashboxAccountingCode.rght'),
			'conditions'=>array(
				'CashboxAccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
		));
		$cashboxAccountingCodes = $this->Transfer->CashboxAccountingCode->find('list',array(
			'fields'=>'CashboxAccountingCode.fullname',
			'conditions'=>array(
				'CashboxAccountingCode.lft >'=>$cashboxAccountingCode['CashboxAccountingCode']['lft'],
				'CashboxAccountingCode.rght <'=>$cashboxAccountingCode['CashboxAccountingCode']['rght'],
				'CashboxAccountingCode.bool_main'=>'0',
			)
		));
	
		$accountingRegisters = $this->Transfer->AccountingRegister->find('list');
		$this->set(compact('currencies', 'bankAccountingCodes', 'cashboxAccountingCodes', 'accountingRegisters'));
		
    
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>array('AccountingCode.id','AccountingCode.shortfullname'),
			'conditions'=>array('AccountingCode.bool_main'=>false),
			'order'=>'AccountingCode.lft ASC'
		));
		$this->set(compact('accountingCodes'));
		
    $newTransferCode=$this->Transfer->getNewTransferCode();
		$this->set(compact('newTransferCode'));
    
		$aco_name="AccountingCodes/index";		
		$bool_accountingcode_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_index_permission'));
		$aco_name="AccountingCodes/add";		
		$bool_accountingcode_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_add_permission'));
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function sortByDateDescending($a,$b ){ 
    $orderDateA="";
    $isInvoiceA="";
    $codeA="";
    
    $orderDateB="";
    $isInvoiceB="";
    $codeB="";
    
    if (!empty($a['Invoice'])){
      $orderDateA=new DateTime($a['Invoice']['invoice_date']);
      $isInvoiceA=true;
      $codeA=$a['Invoice']['invoice_code'];
    }
    else {
      $orderDateA=new DateTime($a['CashReceipt']['receipt_date']);
      $isInvoiceA='0';
      $codeA=$a['CashReceipt']['receipt_code'];
    }
    if (!empty($b['Invoice'])){
      $orderDateB=new DateTime($b['Invoice']['invoice_date']);
      $isInvoiceB=true;
      $codeB=$b['Invoice']['invoice_code'];
    }
    else {
      $orderDateB=new DateTime($b['CashReceipt']['receipt_date']);
      $isInvoiceB='0';
      $codeB=$b['CashReceipt']['receipt_code'];
    }
    if ($orderDateA==$orderDateB){
      if ($isInvoiceA && $isInvoiceB){
        return ($codeA < $codeB) ? 1 : -1;
      }
      elseif ($isInvoiceA) {
          return -1;
      }
      elseif ($isInvoiceB) {
         return 1;
      }
      else {
        return ($codeA < $codeB) ? 1 : -1;
      } 
    } 
    else {
      return ($orderDateA < $orderDateB) ? 1 : -1;
    }
	  //if( $a['totalForClient'] == $b['totalForClient'] ){ return 0 ; } 
	  //return ($s['totalForClient'] < $b['totalForClient']) ? 1 : -1;
	} 
  
	public function editarDeposito($id = null) {
    if (!$this->Transfer->exists($id)) {
			throw new NotFoundException(__('Depósito Inválido'));
		}
		
    $this->loadModel('AccountingCode');
    $this->loadModel('CashReceipt');
    $this->loadModel('Invoice');
    
    $this->CashReceipt->recursive=-1;
    $this->Invoice->recursive=-1;
    
    $originalTransfer=$this->Transfer->find('first',[
      'conditions'=>['Transfer.id'=>$id],
      'contain'=>[
        'AccountingRegister'=>['AccountingMovement'],
        'PaymentDeposit'=>['CashReceipt','Invoice'],
      ],
    ]);
    //pr($originalTransfer);  
    $requestInvoicePaymentIds=[];
    $requestCashReceiptPaymentIds=[];
  
		if ($this->request->is(['post','put'])) {
      //pr($this->request->data);
			$this->loadModel('AccountingRegister');
			$this->loadModel('ClosingDate');
      $this->loadModel('PaymentDeposit');
      
      foreach ($this->request->data['Payment']['Invoice'] as $invoiceId=>$invoicePayment){
        if ($invoicePayment>0){
          $requestInvoicePaymentIds[]=$invoiceId;
        }
      }
      foreach ($this->request->data['Payment']['CashReceipt'] as $cashReceiptId=>$cashReceiptPayment){
        if ($cashReceiptPayment>0){
          $requestCashReceiptPaymentIds[]=$cashReceiptId;
        }
      }
			
			$transfer_date=$this->request->data['Transfer']['transfer_date'];
			$transferDateAsString = $this->Transfer->deconstruct('transfer_date', $this->request->data['Transfer']['transfer_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
						
			$transferDateArray=[];
			$transferDateArray['year']=$transfer_date['year'];
			$transferDateArray['month']=$transfer_date['month'];
			$transferDateArray['day']=$transfer_date['day'];
					
			$deposit_code=$this->request->data['Transfer']['transfer_code'];
			$currency_id=$this->request->data['Transfer']['currency_id'];
			if ($transferDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de transferencia no puede estar en el futuro!  No se guardó la transferencia.'), 'default',['class' => 'error-message']);
			}
			elseif ($transferDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',['class' => 'error-message']);
			}
			else if ($this->request->data['Transfer']['cashbox_accounting_code_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar la caja de donde se transfiere el dinero!  No se guardó la transferencia.'), 'default',['class' => 'error-message']);
			}
			else {
        $datasource=$this->Transfer->getDataSource();
				$datasource->begin();
        
        $originalDepositRemoved='0';
        //echo  "starting to remove the original deposit <br/>";
        try {
        	if (!empty($originalTransfer['AccountingRegister'])){
            foreach ($originalTransfer['AccountingRegister']['AccountingMovement'] as $accountingMovement){
              //$this->AccountingRegister->AccountingMovement->id=$accountingMovement['id'];
							if (!$this->AccountingRegister->AccountingMovement->delete($accountingMovement['id'])){
                echo "Problema al eliminar el movimiento contable original";
                pr($this->validateErrors($this->AccountingRegister->AccountingMovement));
                throw new Exception();
              }
            }
            //$this->AccountingRegister->id=$originalTransfer['AccountingRegister']['id'];
						if (!$this->AccountingRegister->delete($originalTransfer['AccountingRegister']['id'])){
              echo "Problema al eliminar el registro contable original";
              pr($this->validateErrors($this->AccountingRegister));
              throw new Exception();
            }
          }
          
          if (!empty($originalTransfer['PaymentDeposit'])){
            foreach ($originalTransfer['PaymentDeposit'] as $paymentDeposit){
              $relatedInvoiceCashReceipt="";
              if(!empty($paymentDeposit['Invoice']) && $paymentDeposit['amount'] > 0){
                $this->Invoice->id=$paymentDeposit['Invoice']['id'];
                $invoiceArray=[];
                $invoiceArray['id']=$paymentDeposit['Invoice']['id'];
                $invoiceArray['bool_deposited']='0';
                if (!$this->Invoice->save($invoiceArray)){
                  echo "Problema revirtiendo el estado (depositado) de la factura ".$paymentDeposit['Invoice']['invoice_code'];
                  pr($this->validateErrors($this->Invoice));
                  throw new Exception();
                }
                $relatedInvoiceCashReceipt="factura ".$paymentDeposit['Invoice']['invoice_code'];  
              }
              if(!empty($paymentDeposit['CashReceipt']) && $paymentDeposit['amount'] > 0){
                $this->CashReceipt->id=$paymentDeposit['CashReceipt']['id'];
                $cashReceiptArray=[];
                $cashReceiptArray['id']=$paymentDeposit['CashReceipt']['id'];
                $cashReceiptArray['bool_deposited']='0';
                if (!$this->CashReceipt->save($cashReceiptArray)){
                  echo "Problema revirtiendo el estado (depositado) del recibo de caja ".$paymentDeposit['CashReceipt']['receipt_code'];
                  pr($this->validateErrors($this->CashReceipt));
                  throw new Exception();
                }
                $relatedInvoiceCashReceipt="recibo de caja ".$paymentDeposit['CashReceipt']['receipt_code'];  
              }
              if (!$this->PaymentDeposit->delete($paymentDeposit['id'])){
                echo "Problema al eliminar el depósito para ".$relatedInvoiceCashReceipt;
                pr($this->validateErrors($this->PaymentDeposit));
                throw new Exception();
              }
            }  
					}
					$datasource->commit();
          $originalDepositRemoved=true;
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía remover el depósito original.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
				}
        if ($originalDepositRemoved)  {
          //echo  "starting to save the new deposit <br/>";
          // now register new deposit
          $datasource=$this->Transfer->getDataSource();
          $datasource->begin();
          try {
            $total_transfer=$this->request->data['Transfer']['amount_cs'];
            $currency_id=$this->request->data['Transfer']['currency_id'];
            
            // first prepare the accounting registers
            // debit = cost_accounting_code_id selected
            // credit = bank_accounting_code_id selected
            // amount = total
            // NO NEED BECAUSE VIEW ALREADY PROVIDES VALUES IN CS
            
            $accountingMovementData=[];
            $accountingMovementData[0]['accounting_code_id']=ACCOUNTING_CODE_BANK_CS;
            $accountingMovementData[0]['concept']=("Transfer ".$deposit_code);
            $accountingMovementData[0]['debit_amount']=$total_transfer;
            $accountingMovementData[1]['accounting_code_id']=ACCOUNTING_CODE_CASHBOX_MAIN;
            $accountingMovementData[1]['concept']=("Transfer ".$deposit_code);
            $accountingMovementData[1]['credit_amount']=$total_transfer;
            
            
            $accountingRegisterData['AccountingRegister']['register_date']=$transferDateArray;
            $accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
            $registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
            $accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
            $accountingRegisterData['AccountingRegister']['amount']=$total_transfer;
            $accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
            $accountingRegisterData['AccountingRegister']['concept']="Transfer ".$deposit_code;
            $accountingRegisterData['AccountingRegister']['observation']="Transfer ".$deposit_code;
          
            //$accountingRegisterData['AccountingMovement']=$this->request->data['AccountingMovement'];
            $accountingRegisterData['AccountingMovement']=$accountingMovementData;
            // no need as deposit already converts to amount cs in view
            
            //pr($accountingRegisterData);
            $accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
            $this->recordUserAction($this->AccountingRegister->id,"add",null);
            
            $this->request->data['Transfer']['accounting_register_id']=$accounting_register_id;
            
            $this->Transfer->id=$id;
            if (!$this->Transfer->save($this->request->data)) {
              echo "Problema guardando la transferencia";
              pr($this->validateErrors($this->Transfer));
              throw new Exception();
            }
            $transferId=$this->Transfer->id;
            
            if (!empty($this->request->data['Payment']['Invoice'])){
              foreach ($this->request->data['Payment']['Invoice'] as $invoiceId => $depositAmount){
                // 20190103 added because somehow last element in row contains a selector
                if (is_array($depositAmount)){
                  $depositAmount=$depositAmount['selector'];
                }
                
                //echo "printing the deposit amount...";
                //pr($depositAmount);
                 //echo "invoice id ".$invoiceId."  has an amount of ".$depositAmount."<br>";
                 if ($depositAmount>0){
                    if (is_array($depositAmount)){
                      //pr($invoiceId);
                      //pr($depositAmount);
                    }
                    $boolDeposited = $this->Invoice->setDepositedStatus($invoiceId,$depositAmount,$currency_id);
                    //echo "bool deposited is ".$boolDeposited."<br/>";
                    
                    $paymentDepositArray=[];
                    $paymentDepositArray['PaymentDeposit']['invoice_id']=$invoiceId;
                    $paymentDepositArray['PaymentDeposit']['transfer_id']=$transferId;
                    $paymentDepositArray['PaymentDeposit']['amount']=$depositAmount;
                    $paymentDepositArray['PaymentDeposit']['currency_id']=$currency_id;
                    $paymentDepositArray['PaymentDeposit']['bool_deposit_complete']=$boolDeposited;
                    
                    $this->PaymentDeposit->create();
                    if (!$this->PaymentDeposit->save($paymentDepositArray)) {
                      echo "Problema guardando el registro que entrelaza el depósito y la factura";
                      pr($this->validateErrors($this->PaymentDeposit));
                      throw new Exception();
                    }  
                    if ($boolDeposited){
                      //echo  "setting deposited to true";  
                      $this->Invoice->id=$invoiceId;
                      $invoiceArray=[];
                      $invoiceArray['Invoice']['id']=$invoiceId;
                      $invoiceArray['Invoice']['bool_deposited']=true;
                      
                      if (!$this->Invoice->save($invoiceArray)) {
                        echo "Problema guardando el estado (depositado) de la factura";
                        pr($this->validateErrors($this->Invoice));
                        throw new Exception();
                      }
                    }
                 }
              }
            }
            
            if (!empty($this->request->data['Payment']['CashReceipt'])){
              foreach ($this->request->data['Payment']['CashReceipt'] as $cashReceiptId => $depositAmount){
                //echo "cash receipt id ".$cashReceiptId."  has an amount of ".$depositAmount."<br>";
                if ($depositAmount>0){
                    $boolDeposited = $this->CashReceipt->setDepositedStatus($cashReceiptId,$depositAmount,$currency_id);
                 
                    $paymentDepositArray=[];
                    $paymentDepositArray['PaymentDeposit']['cash_receipt_id']=$cashReceiptId;
                    $paymentDepositArray['PaymentDeposit']['transfer_id']=$transferId;
                    $paymentDepositArray['PaymentDeposit']['amount']=$depositAmount;
                    $paymentDepositArray['PaymentDeposit']['currency_id']=$currency_id;
                    $paymentDepositArray['PaymentDeposit']['bool_deposit_complete']=$boolDeposited;
                    
                    $this->PaymentDeposit->create();
                    if (!$this->PaymentDeposit->save($paymentDepositArray)) {
                      echo "Problema guardando el registro que entrelaza el depósito y el recibo de caja";
                      pr($this->validateErrors($this->PaymentDeposit));
                      throw new Exception();
                    }
                    if ($boolDeposited){
                      $this->CashReceipt->id=$cashReceiptId;
                      $cashReceiptArray=[];
                      $cashReceiptArray['CashReceipt']['id']=$cashReceiptId;
                      $cashReceiptArray['CashReceipt']['bool_deposited']=true;
                      
                      if (!$this->CashReceipt->save($cashReceiptArray)) {
                        echo "Problema guardando el estado (depositado) de la factura";
                        pr($this->validateErrors($this->CashReceipt));
                        throw new Exception();
                      }
                    }
                 }
              }
            }
            
            $this->recordUserAction($this->Transfer->id,null,null);
            
            $datasource->commit();
            
            $this->recordUserAction();
            // SAVE THE USERLOG FOR THE CHEQUE
            $this->recordUserActivity($this->Session->read('User.username'),"Depósito registrado con concepto ".$this->request->data['Transfer']['concept']);
            $this->Session->setFlash(__('Se guardó el depósito.'),'default',['class' => 'success']);
            return $this->redirect(['action' => 'resumenDepositos']);
          }
          catch(Exception $e){
            $datasource->rollback();
            pr($e);
            $this->Session->setFlash(__('No se podía guardar el depósito.  Por favor intente de nuevo.'), 'default',['class' => 'error-message']);
          }
        
        }
      }	
		}
		else {
      $this->request->data=$originalTransfer;
      
      foreach ($this->request->data['PaymentDeposit'] as $paymentDeposit){
				if (!empty($paymentDeposit['invoice_id'])){
          $requestInvoicePaymentIds[]=$paymentDeposit['invoice_id'];
        }
        elseif (!empty($paymentDeposit['cash_receipt_id'])){
          $requestCashReceiptPaymentIds[]=$paymentDeposit['cash_receipt_id'];
        }
			}
    }
    
    $this->set(compact('requestInvoicePaymentIds','requestCashReceiptPaymentIds'));
    
    $undepositedInvoices=$this->Invoice->find('all',[
      'conditions'=>[
        'OR'=>[
          [
            'bool_credit'=>'0',
            'bool_deposited'=>'0',
            'sub_total_price >'=>0,
          ],
          ['Invoice.id'=>$requestInvoicePaymentIds]
        ]        
      ],
      'order'=>'invoice_code DESC'
    ]);
    for ($i=0;$i<count($undepositedInvoices);$i++){
      //pr($undepositedInvoices[$i]);
      
      $invoiceDate=$undepositedInvoices[$i]['Invoice']['invoice_date'];
      $exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($invoiceDate);
      //pr($exchangeRate);
      $undepositedInvoices[$i]['Invoice']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
      if ($undepositedInvoices[$i]['Invoice']['currency_id']==CURRENCY_CS){
        $undepositedInvoices[$i]['Invoice']['paid_amount_CS']=($undepositedInvoices[$i]['Invoice']['total_price']-$undepositedInvoices[$i]['Invoice']['retention_amount']);
        $undepositedInvoices[$i]['Invoice']['paid_amount_USD']=round(($undepositedInvoices[$i]['Invoice']['total_price']-$undepositedInvoices[$i]['Invoice']['retention_amount'])/$undepositedInvoices[$i]['Invoice']['exchange_rate'],2);
      }
      elseif ($undepositedInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
        
        $undepositedInvoices[$i]['Invoice']['paid_amount_CS']=round(($undepositedInvoices[$i]['Invoice']['total_price']-$undepositedInvoices[$i]['Invoice']['retention_amount'])*$undepositedInvoices[$i]['Invoice']['exchange_rate'],2);
        $undepositedInvoices[$i]['Invoice']['paid_amount_USD']=($undepositedInvoices[$i]['Invoice']['total_price']-$undepositedInvoices[$i]['Invoice']['retention_amount']);
      }
    }
    //pr($undepositedInvoices);
    $this->set(compact('undepositedInvoices'));
    
    $undepositedCashReceipts=$this->CashReceipt->find('all',[
      'conditions'=>[
        'OR'=>[
          [
            'bool_deposited'=>'0',
            'amount >'=>0
          ],
          ['CashReceipt.id'=>$requestCashReceiptPaymentIds]
        ]    
        
      ],
      'order'=>'receipt_code DESC'
    ]);
    
    for ($i=0;$i<count($undepositedCashReceipts);$i++){
      //pr($undepositedCashReceipts[$i]);
      $cashReceiptDate=$undepositedCashReceipts[$i]['CashReceipt']['receipt_date'];
      $exchangeRate=$this->ExchangeRate->getApplicableExchangeRate($cashReceiptDate);
      //pr($exchangeRate);
      $undepositedCashReceipts[$i]['CashReceipt']['exchange_rate']=$exchangeRate['ExchangeRate']['rate'];
      if ($undepositedCashReceipts[$i]['CashReceipt']['currency_id']==CURRENCY_CS){
        $undepositedCashReceipts[$i]['CashReceipt']['paid_amount_CS']=($undepositedCashReceipts[$i]['CashReceipt']['amount']);
        $undepositedCashReceipts[$i]['CashReceipt']['paid_amount_USD']=round(($undepositedCashReceipts[$i]['CashReceipt']['amount'])/$undepositedCashReceipts[$i]['CashReceipt']['exchange_rate'],2);
      }
      elseif ($undepositedCashReceipts[$i]['CashReceipt']['currency_id']==CURRENCY_USD){
        
        $undepositedCashReceipts[$i]['CashReceipt']['paid_amount_CS']=round(($undepositedCashReceipts[$i]['CashReceipt']['amount'])*$undepositedCashReceipts[$i]['CashReceipt']['exchange_rate'],2);
        $undepositedCashReceipts[$i]['CashReceipt']['paid_amount_USD']=($undepositedCashReceipts[$i]['CashReceipt']['amount']);
      }
    }
    //pr($undepositedInvoices);
    //pr($undepositedCashReceipts);
    $this->set(compact('undepositedCashReceipts'));
    
    $undepositedPayments=$undepositedInvoices;
    foreach ($undepositedCashReceipts as $undepositedCashReceipt){
      $undepositedPayments[]=$undepositedCashReceipt;
    }
    usort($undepositedPayments,[$this,'sortByDateDescending']);
    $this->set(compact('undepositedPayments'));
  
    $currencies = $this->Transfer->Currency->find('list');
		$bankAccountingCode=$this->Transfer->BankAccountingCode->find('first',array(
			'fields'=>array('BankAccountingCode.lft','BankAccountingCode.rght'),
			'conditions'=>array(
				'BankAccountingCode.id'=>ACCOUNTING_CODE_BANKS,
			),
			
		));
		$bankAccountingCodes = $this->Transfer->BankAccountingCode->find('list',array(
			'fields'=>'BankAccountingCode.description',
			'conditions'=>array(
				'BankAccountingCode.lft >'=>$bankAccountingCode['BankAccountingCode']['lft'],
				'BankAccountingCode.rght <'=>$bankAccountingCode['BankAccountingCode']['rght'],
				'BankAccountingCode.bool_main'=>'0',
			)
		));
		$cashboxAccountingCode=$this->Transfer->CashboxAccountingCode->find('first',array(
			'fields'=>array('CashboxAccountingCode.lft','CashboxAccountingCode.rght'),
			'conditions'=>array(
				'CashboxAccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
		));
		$cashboxAccountingCodes = $this->Transfer->CashboxAccountingCode->find('list',array(
			'fields'=>'CashboxAccountingCode.fullname',
			'conditions'=>array(
				'CashboxAccountingCode.lft >'=>$cashboxAccountingCode['CashboxAccountingCode']['lft'],
				'CashboxAccountingCode.rght <'=>$cashboxAccountingCode['CashboxAccountingCode']['rght'],
				'CashboxAccountingCode.bool_main'=>'0',
			)
		));
	
		$accountingRegisters = $this->Transfer->AccountingRegister->find('list');
		$this->set(compact('currencies', 'bankAccountingCodes', 'cashboxAccountingCodes', 'accountingRegisters'));
		
    
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>array('AccountingCode.id','AccountingCode.shortfullname'),
			'conditions'=>array('AccountingCode.bool_main'=>false),
			'order'=>'AccountingCode.lft ASC'
		));
		$this->set(compact('accountingCodes'));
		
    $aco_name="AccountingCodes/index";		
		$bool_accountingcode_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_index_permission'));
		$aco_name="AccountingCodes/add";		
		$bool_accountingcode_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_add_permission'));
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function eliminarDeposito($id = null) {
		$this->Transfer->id = $id;
		if (!$this->Transfer->exists()) {
			throw new NotFoundException(__('Invalid transfer'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('AccountingCode');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
    $this->loadModel('PaymentDeposit');
    		
		$datasource=$this->Transfer->getDataSource();				
		try {
			$datasource->begin();

			// first bring back everything to original state
			$oldTransfer=$this->Transfer->find('first',array(
				'conditions'=>array(
					'Transfer.id'=>$id,
				)
			));
			$oldAccountingRegister=$this->AccountingRegister->find('first',array(
				'conditions'=>array('AccountingRegister.id'=>$oldTransfer['Transfer']['accounting_register_id'])
			));
			
			if (!empty($oldAccountingRegister)){
				// first remove the movement
				$oldAccountingMovements=$this->AccountingMovement->find('all',array(
					'fields'=>array('AccountingMovement.id'),
					'conditions'=>array(
						'accounting_register_id'=>$oldAccountingRegister['AccountingRegister']['id'],
					)
				));
				if (!empty($oldAccountingMovements)){
					foreach ($oldAccountingMovements as $oldAccountingMovement){
						$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
					}
				}
        
				// then remove the register
				$this->AccountingRegister->delete($oldAccountingRegister['AccountingRegister']['id']);
				// then remove the register cash receipt link
			}
      
      $oldPaymentDeposits=$this->PaymentDeposit->find('all',[
        'fields'=>['PaymentDeposit.id', 'PaymentDeposit.cash_receipt_id','PaymentDeposit.invoice_id', 'PaymentDeposit.amount'],
        'conditions'=>['transfer_id'=>$id,]
      ]);
      if (!empty($oldPaymentDeposits)){
        foreach ($oldPaymentDeposits as $oldPaymentDeposit){
          if ($oldPaymentDeposit['PaymentDeposit']['amount']>0 && $oldPaymentDeposit['PaymentDeposit']['cash_receipt_id']>0){
            $this->loadModel('CashReceipt');
            $this->CashReceipt->id=$oldPaymentDeposit['PaymentDeposit']['cash_receipt_id'];
            $cashReceipt=[];
            $cashReceipt['CashReceipt']['bool_deposited']='0';
            if (!$this->CashReceipt->save($cashReceipt)){
              echo "Problema restableciendo el estado  depositado del recibo de caja";
              pr($this->validateErrors($this->CashReceipt));
              throw new Exception();
            }
          }
          if ($oldPaymentDeposit['PaymentDeposit']['amount']>0 && $oldPaymentDeposit['PaymentDeposit']['invoice_id']>0){
            $this->loadModel('Invoice');
            $this->Invoice->id=$oldPaymentDeposit['PaymentDeposit']['invoice_id'];
            $invoice=[];
            $invoice['Invoice']['bool_deposited']='0';
            if (!$this->Invoice->save($invoice)){
              echo "Problema restableciendo el estado  depositado de la factura";
              pr($this->validateErrors($this->Invoice));
              throw new Exception();
            }
          }
          
          $this->PaymentDeposit->delete($oldPaymentDeposit['PaymentDeposit']['id']);
        }
      }
      
			// then remove the cash receipt
			$this->Transfer->delete($oldTransfer['Transfer']['id']);						
			
			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el depósito con código ".$oldTransfer['Transfer']['transfer_code']);
      $this->Session->setFlash(__('Se eliminó el depósito #'.$oldTransfer['Transfer']['transfer_code'].'.'),'default',array('class' => 'success'));	
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('Los datos del depósito no se podían remover.'), 'default',['class' => 'error-message']);
		}				
			
		return $this->redirect(array('action' => 'resumenDepositos'));
	}

  
	public function index() {
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
	
	
		$transferCount=	$this->Transfer->find('count', array(
			'fields'=>array('Transfer.id'),
			'conditions' => array(
				'Transfer.transfer_date >='=> $startDate,
				'Transfer.transfer_date <'=> $endDatePlusOne,
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(
				'Transfer.transfer_date >='=> $startDate,
				'Transfer.transfer_date <'=> $endDatePlusOne,
			),
			'order' => array('Transfer.transfer_date'=>'ASC','Transfer.transfer_code'=>'ASC'),
			'limit'=>($transferCount!=0?$transferCount:1),
		);

		$transfers = $this->Paginator->paginate('Transfer');
		$this->set(compact('transfers'));
		
		$this->set(compact('startDate','endDate'));
		
		$aco_name="AccountingCodes/index";		
		$bool_accountingcode_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_index_permission'));
		$aco_name="AccountingCodes/add";		
		$bool_accountingcode_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_add_permission'));
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function view($id = null) {
		if (!$this->Transfer->exists($id)) {
			throw new NotFoundException(__('Invalid transfer'));
		}
		$options = array('conditions' => array('Transfer.' . $this->Transfer->primaryKey => $id));
		
		$transfer=$this->Transfer->find('first', $options);
		$this->set(compact('transfer'));
		
		$this->loadModel('AccountingRegister');
		$accountingRegister=$this->AccountingRegister->find('first',array(
			'conditions'=>array(
				'AccountingRegister.id'=>$transfer['Transfer']['accounting_register_id'],
			),
			'contain'=>array(
				'AccountingMovement'=>array(
					'AccountingCode',
				),
			)
		));
		$this->set(compact('accountingRegister'));
		
		$aco_name="AccountingCodes/index";		
		$bool_accountingcode_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_index_permission'));
		$aco_name="AccountingCodes/add";		
		$bool_accountingcode_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_add_permission'));
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->loadModel('AccountingCode');
			$this->loadModel('AccountingRegister');
			$this->loadModel('ClosingDate');
			
			$transfer_date=$this->request->data['Transfer']['transfer_date'];
			$transferDateAsString = $this->Transfer->deconstruct('transfer_date', $this->request->data['Transfer']['transfer_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
						
			$transferDateArray=array();
			$transferDateArray['year']=$transfer_date['year'];
			$transferDateArray['month']=$transfer_date['month'];
			$transferDateArray['day']=$transfer_date['day'];
					
			$transfer_code=$this->request->data['Transfer']['transfer_code'];
			$namedTransfers=$this->Transfer->find('all',array(
				'conditions'=>array(
					'transfer_code'=>$transfer_code,
				)
			));
			
			$currency_id=$this->request->data['Transfer']['currency_id'];
			$sumDebit=0;
			$sumCredit=0;
			$boolMissingDebitAccount='0';
			$boolMissingCreditAccount='0';
			foreach ($this->request->data['AccountingMovement'] as $accountingMovement){
				if (!empty($accountingMovement['debit_amount'])&&$accountingMovement['debit_amount']>0){
					$sumDebit+=$accountingMovement['debit_amount'];
					if ($accountingMovement['accounting_code_id']==0){
						$boolMissingDebitAccount=true;
					}
				}
				if (!empty($accountingMovement['credit_amount'])&&$accountingMovement['credit_amount']>0){
					$sumCredit+=$accountingMovement['credit_amount'];
					if ($accountingMovement['accounting_code_id']==0){
						$boolMissingCreditAccount=true;
					}
				}
			}
			
			if ($sumDebit!=$sumCredit){
				$this->Session->setFlash(__('La suma de DEBE y la suma de HABER no son iguales!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingDebitAccount){
				$this->Session->setFlash(__('Hay una cuenta de DEBE que no lleva la cuenta contable!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingCreditAccount){
				$this->Session->setFlash(__('Hay una cuenta de HABER que no lleva la cuenta contable!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif (count($namedTransfers)>0){
				$this->Session->setFlash(__('Ya existe una transferencia con el mismo código!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			else if ($transferDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de transferencia no puede estar en el futuro!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			elseif ($transferDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			//else if ($this->request->data['Transfer']['bool_annulled']){
			//}
			/*
			else if ($this->request->data['Transfer']['bank_accounting_code_id']==0){
				//pr($this->request->data);
				$this->Session->setFlash(__('Se debe seleccionar la cuenta bancaria en una transferencia!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			*/
			else if ($this->request->data['Transfer']['cashbox_accounting_code_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar la caja de donde se transfiere el dinero!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			else {
				$datasource=$this->Transfer->getDataSource();
				//pr($this->request->data);
				
				try {
					$datasource->begin();
					$total_transfer=$this->request->data['Transfer']['amount'];
					$currency_id=$this->request->data['Transfer']['currency_id'];
					
					// first prepare the accounting registers
					// debit = cost_accounting_code_id selected
					// credit = bank_accounting_code_id selected
					// amount = total
					if ($currency_id==CURRENCY_USD){
						$this->loadModel('ExchangeRate');
						$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($transferDateAsString);
						$total_CS=round($total_transfer*$applicableExchangeRate['ExchangeRate']['rate'],2);
					}
					else {
						$total_CS=$total_transfer;
					}
					
					$accountingRegisterData['AccountingRegister']['register_date']=$transferDateArray;
					$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
					$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
					$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
					$accountingRegisterData['AccountingRegister']['amount']=$total_CS;
					$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
					$accountingRegisterData['AccountingRegister']['concept']="Transfer ".$transfer_code;
					$accountingRegisterData['AccountingRegister']['observation']="Transfer ".$transfer_code;
				
					$accountingRegisterData['AccountingMovement']=$this->request->data['AccountingMovement'];
					if ($currency_id==CURRENCY_USD){
						for ($a=0;$a<count($accountingRegisterData['AccountingMovement']);$a++){
							if (!empty($accountingRegisterData['AccountingMovement'][$a]['debit_amount'])){
								$accountingRegisterData['AccountingMovement'][$a]['debit_amount']=round($accountingRegisterData['AccountingMovement'][$a]['debit_amount']*$applicableExchangeRate['ExchangeRate']['rate'],2);
							}
							if (!empty($accountingRegisterData['AccountingMovement'][$a]['credit_amount'])){
								$accountingRegisterData['AccountingMovement'][$a]['credit_amount']=round($accountingRegisterData['AccountingMovement'][$a]['credit_amount']*$applicableExchangeRate['ExchangeRate']['rate'],2);
							}
						}
					}
					
					//pr($accountingRegisterData);
					$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
					$this->recordUserAction($this->AccountingRegister->id,"add",null);
					
					$this->request->data['Transfer']['accounting_register_id']=$accounting_register_id;
					
					$this->Transfer->create();
					if (!$this->Transfer->save($this->request->data)) {
						echo "Problema guardando la transferencia";
						pr($this->validateErrors($this->Transfer));
						throw new Exception();
					}
					$transfer_id=$this->Transfer->id;
					$this->recordUserAction($this->Transfer->id,null,null);
					$datasource->commit();
					
					$this->recordUserAction();
					// SAVE THE USERLOG FOR THE CHEQUE
					$this->recordUserActivity($this->Session->read('User.username'),"Transfer registrado con número ".$this->request->data['Transfer']['transfer_code']);
					$this->Session->setFlash(__('Se guardó la transferencia.'),'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'index'));
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar la transferencia.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
				}
			}	
		}
		$currencies = $this->Transfer->Currency->find('list');
		
		$bankAccountingCode=$this->Transfer->BankAccountingCode->find('first',array(
			'fields'=>array('BankAccountingCode.lft','BankAccountingCode.rght'),
			'conditions'=>array(
				'BankAccountingCode.id'=>ACCOUNTING_CODE_BANKS,
			),
			
		));
		$bankAccountingCodes = $this->Transfer->BankAccountingCode->find('list',array(
			'fields'=>'BankAccountingCode.fullname',
			'conditions'=>array(
				'BankAccountingCode.lft >'=>$bankAccountingCode['BankAccountingCode']['lft'],
				'BankAccountingCode.rght <'=>$bankAccountingCode['BankAccountingCode']['rght'],
				'BankAccountingCode.bool_main'=>'0',
			)
		));
		$cashboxAccountingCode=$this->Transfer->CashboxAccountingCode->find('first',array(
			'fields'=>array('CashboxAccountingCode.lft','CashboxAccountingCode.rght'),
			'conditions'=>array(
				'CashboxAccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
		));
		$cashboxAccountingCodes = $this->Transfer->CashboxAccountingCode->find('list',array(
			'fields'=>'CashboxAccountingCode.fullname',
			'conditions'=>array(
				'CashboxAccountingCode.lft >'=>$cashboxAccountingCode['CashboxAccountingCode']['lft'],
				'CashboxAccountingCode.rght <'=>$cashboxAccountingCode['CashboxAccountingCode']['rght'],
				'CashboxAccountingCode.bool_main'=>'0',
			)
		));
	
		$accountingRegisters = $this->Transfer->AccountingRegister->find('list');
		$this->set(compact('currencies', 'bankAccountingCodes', 'cashboxAccountingCodes', 'accountingRegisters'));
		$this->loadModel('AccountingCode');
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>array('AccountingCode.id','AccountingCode.shortfullname'),
			'conditions'=>array('AccountingCode.bool_main'=>false),
			'order'=>'AccountingCode.lft ASC'
		));
		$this->set(compact('accountingCodes'));
		
		$newTransferCode=$this->Transfer->getNewTransferCode();
		$this->set(compact('newTransferCode'));
		
		$aco_name="AccountingCodes/index";		
		$bool_accountingcode_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_index_permission'));
		$aco_name="AccountingCodes/add";		
		$bool_accountingcode_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_add_permission'));
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->Transfer->exists($id)) {
			throw new NotFoundException(__('Invalid transfer'));
		}
		
		$thisTransfer=$this->Transfer->read(null,$id);
		//pr($thisTransfer);
		$this->loadModel('AccountingRegister');
		$debitMovementsAlreadyInAccountingRegister=$this->AccountingRegister->AccountingMovement->find('all',array(
			'fields'=>array('AccountingMovement.id','AccountingMovement.accounting_code_id','AccountingMovement.amount','AccountingMovement.concept'),
			'conditions'=>array(
				'AccountingMovement.accounting_register_id'=>$thisTransfer['AccountingRegister']['id'],
				'AccountingMovement.amount >'=>'0',
				'AccountingMovement.bool_debit'=>true,
			),
		));
		
		$creditMovementsAlreadyInAccountingRegister=$this->AccountingRegister->AccountingMovement->find('all',array(
			'fields'=>array('AccountingMovement.id','AccountingMovement.accounting_code_id','AccountingMovement.amount','AccountingMovement.concept'),
			'conditions'=>array(
				'AccountingMovement.accounting_register_id'=>$thisTransfer['AccountingRegister']['id'],
				'AccountingMovement.amount >'=>'0',
				'AccountingMovement.bool_debit'=>'0',
			),
		));
		$this->set(compact('debitMovementsAlreadyInAccountingRegister','creditMovementsAlreadyInAccountingRegister'));
		
		$this->loadModel('ExchangeRate');
		$appliedExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($thisTransfer['Transfer']['transfer_date']);
		$this->set(compact('appliedExchangeRate'));
		
		$bool_first_load=true;
		if ($this->request->is(array('post', 'put'))) {
			$bool_first_load='0';
			$this->loadModel('AccountingCode');
			$this->loadModel('AccountingRegister');
			$this->loadModel('AccountingMovement');
			$this->loadModel('ClosingDate');
			
			$transfer_date=$this->request->data['Transfer']['transfer_date'];
			$transferDateAsString = $this->Transfer->deconstruct('transfer_date', $this->request->data['Transfer']['transfer_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
						
			$transferDateArray=array();
			$transferDateArray['year']=$transfer_date['year'];
			$transferDateArray['month']=$transfer_date['month'];
			$transferDateArray['day']=$transfer_date['day'];
					
			$transfer_code=$this->request->data['Transfer']['transfer_code'];
			$namedTransfers=$this->Transfer->find('all',array(
				'conditions'=>array(
					'transfer_code'=>$transfer_code,
					'Transfer.id !='=>$id,
				)
			));
			$total_transfer=$this->request->data['Transfer']['amount'];
			$currency_id=$this->request->data['Transfer']['currency_id'];
			
			$sumDebit=0;
			$sumCredit=0;
			$boolMissingDebitAccount='0';
			$boolMissingCreditAccount='0';
			foreach ($this->request->data['AccountingMovement'] as $accountingMovement){
				if (!empty($accountingMovement['debit_amount'])&&$accountingMovement['debit_amount']>0){
					$sumDebit+=$accountingMovement['debit_amount'];
					if ($accountingMovement['accounting_code_id']==0){
						$boolMissingDebitAccount=true;
					}
				}
				if (!empty($accountingMovement['credit_amount'])&&$accountingMovement['credit_amount']>0){
					$sumCredit+=$accountingMovement['credit_amount'];
					if ($accountingMovement['accounting_code_id']==0){
						$boolMissingCreditAccount=true;
					}
				}
			}
			if ($sumDebit!=$sumCredit){
				$this->Session->setFlash(__('La suma de DEBE y la suma de HABER no son iguales!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingDebitAccount){
				$this->Session->setFlash(__('Hay una cuenta de DEBE que no lleva la cuenta contable!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingCreditAccount){
				$this->Session->setFlash(__('Hay una cuenta de HABER que no lleva la cuenta contable!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif (count($namedTransfers)>0){
				$this->Session->setFlash(__('Ya existe una transferencia con el mismo código!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			else if ($transferDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de transferencia no puede estar en el futuro!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			elseif ($transferDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			//else if ($this->request->data['Transfer']['bool_annulled']){
			//}
			/*
			else if ($this->request->data['Transfer']['bank_accounting_code_id']==0){
				//pr($this->request->data);
				$this->Session->setFlash(__('Se debe seleccionar la cuenta bancaria en una transferencia!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			*/
			else if ($this->request->data['Transfer']['cashbox_accounting_code_id']==0){
				$this->Session->setFlash(__('Se debe seleccionar la cuenta contable para la caja en una transferencia!  No se guardó la transferencia.'), 'default',array('class' => 'error-message'));
			}
			else {
				$datasource=$this->Transfer->getDataSource();
				//pr($this->request->data);
				
				try {
					$datasource->begin();
					$removedOK='0';
					$oldAccountingRegisterCode="";
					// first bring back everything to original state
					$oldTransfer=$this->Transfer->find('first',array(
						'conditions'=>array(
							'Transfer.id'=>$id,
						)
					));
					//MODIFIED 20160310 ONLY ONE ACCOUNTING REGISTER PRESENT
					$oldAccountingRegister=$this->AccountingRegister->find('first',array(
						'conditions'=>array('AccountingRegister.id'=>$oldTransfer['Transfer']['accounting_register_id'])
					));
					//pr($oldAccountingRegister);
					if (!empty($oldAccountingRegister)){
						$oldAccountingRegisterCode=$oldAccountingRegister['AccountingRegister']['register_code'];						
						// first remove the movement
						$oldAccountingMovements=$this->AccountingMovement->find('all',array(
							'fields'=>array('AccountingMovement.id'),
							'conditions'=>array(
								'accounting_register_id'=>$oldAccountingRegister['AccountingRegister']['id'],
							)
						));
						if (!empty($oldAccountingMovements)){
							foreach ($oldAccountingMovements as $oldAccountingMovement){
								$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
							}
						}
						// then remove the register
						$this->AccountingRegister->delete($oldAccountingRegister['AccountingRegister']['id']);
					}
					// MODIFIED 20160310 TRANSFER ITSELF SHOULD NOT BE REMOVED
					// then remove the transfer
					// $this->Transfer->delete($oldTransfer['Transfer']['id']);						
					
					$removedOK=true;
					
					$datasource->commit();
					$this->recordUserAction();
					$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la transferencia con código ".$this->request->data['Transfer']['transfer_code']);
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('Los datos de la remisión no se podían remover.'), 'default',array('class' => 'error-message'));
				}				
				//echo "everything back to original state";
				if ($removedOK){
					try {
						$datasource->begin();					
						// first prepare the accounting registers
						// debit = cost_accounting_code_id selected
						// credit = bank_accounting_code_id selected
						// amount = total
						if ($currency_id==CURRENCY_USD){
							$this->loadModel('ExchangeRate');
							$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($transferDateAsString);
							$total_CS=round($total_transfer*$applicableExchangeRate['ExchangeRate']['rate'],2);
						}
						else {
							$total_CS=$total_transfer;
						}
						
						$accountingRegisterData['AccountingRegister']['register_date']=$transferDateArray;
						$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CD;
						if (!empty($oldAccountingRegisterCode)){
							$registerCode=$oldAccountingRegisterCode;
						}
						else {
							$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CD);
						}
						$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
						$accountingRegisterData['AccountingRegister']['amount']=$total_CS;
						$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
						$accountingRegisterData['AccountingRegister']['concept']="Transfer ".$transfer_code;
						$accountingRegisterData['AccountingRegister']['observation']="Transfer ".$transfer_code;
					
						$accountingRegisterData['AccountingMovement']=$this->request->data['AccountingMovement'];
						if ($currency_id==CURRENCY_USD){
							for ($a=0;$a<count($accountingRegisterData['AccountingMovement']);$a++){
								if (!empty($accountingRegisterData['AccountingMovement'][$a]['debit_amount'])){
									$accountingRegisterData['AccountingMovement'][$a]['debit_amount']=round($accountingRegisterData['AccountingMovement'][$a]['debit_amount']*$applicableExchangeRate['ExchangeRate']['rate'],2);
								}
								if (!empty($accountingRegisterData['AccountingMovement'][$a]['credit_amount'])){
									$accountingRegisterData['AccountingMovement'][$a]['credit_amount']=round($accountingRegisterData['AccountingMovement'][$a]['credit_amount']*$applicableExchangeRate['ExchangeRate']['rate'],2);
								}
							}
						}
						
						//pr($accountingRegisterData);
						$accounting_register_id=$this->saveAccountingRegisterData($accountingRegisterData,true);
						$this->recordUserAction($this->AccountingRegister->id,"add",null);
						
						$this->request->data['Transfer']['accounting_register_id']=$accounting_register_id;
						
						$this->Transfer->id=$id;
						if (!$this->Transfer->save($this->request->data)) {
							echo "Problema guardando la transferencia";
							pr($this->validateErrors($this->Transfer));
							throw new Exception();
						}
						$transfer_id=$this->Transfer->id;
						
						$datasource->commit();
						
						// SAVE THE USERLOG FOR THE CHEQUE
						$this->recordUserActivity($this->Session->read('User.username'),"Transfer registrado con número ".$this->request->data['Transfer']['transfer_code']);
						$this->Session->setFlash(__('Se guardó la transferencia.'),'default',array('class' => 'success'));
						return $this->redirect(array('action' => 'index'));
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('No se podía guardar la transferencia.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
					}
				}			
			}
		} 
		
		else {
			$options = array('conditions' => array('Transfer.' . $this->Transfer->primaryKey => $id));
			$this->request->data = $this->Transfer->find('first', $options);
		}
		
		
		$currencies = $this->Transfer->Currency->find('list');
		$bankAccountingCode=$this->Transfer->BankAccountingCode->find('first',array(
			'fields'=>array('BankAccountingCode.lft','BankAccountingCode.rght'),
			'conditions'=>array(
				'BankAccountingCode.id'=>ACCOUNTING_CODE_BANKS,
			),
		));
		$bankAccountingCodes = $this->Transfer->BankAccountingCode->find('list',array(
			'fields'=>'BankAccountingCode.fullname',
			'conditions'=>array(
				'BankAccountingCode.lft >'=>$bankAccountingCode['BankAccountingCode']['lft'],
				'BankAccountingCode.rght <'=>$bankAccountingCode['BankAccountingCode']['rght'],
				'BankAccountingCode.bool_main'=>'0',
			)
		));
		$cashboxAccountingCode=$this->Transfer->CashboxAccountingCode->find('first',array(
			'fields'=>array('CashboxAccountingCode.lft','CashboxAccountingCode.rght'),
			'conditions'=>array(
				'CashboxAccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			),
		));
		$cashboxAccountingCodes = $this->Transfer->CashboxAccountingCode->find('list',array(
			'fields'=>'CashboxAccountingCode.fullname',
			'conditions'=>array(
				'CashboxAccountingCode.lft >'=>$cashboxAccountingCode['CashboxAccountingCode']['lft'],
				'CashboxAccountingCode.rght <'=>$cashboxAccountingCode['CashboxAccountingCode']['rght'],
				'CashboxAccountingCode.bool_main'=>'0',
			)
		));
		$accountingRegisters = $this->Transfer->AccountingRegister->find('list');
		$this->set(compact('currencies', 'bankAccountingCodes', 'cashboxAccountingCodes', 'accountingRegisters'));
		$this->loadModel('AccountingCode');
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>array('AccountingCode.id','AccountingCode.shortfullname'),
			'conditions'=>array('AccountingCode.bool_main'=>false),
			'order'=>'AccountingCode.lft ASC'
		));
		$this->set(compact('accountingCodes'));
		
		$aco_name="AccountingCodes/index";		
		$bool_accountingcode_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_index_permission'));
		$aco_name="AccountingCodes/add";		
		$bool_accountingcode_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingcode_add_permission'));
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Transfer->id = $id;
		if (!$this->Transfer->exists()) {
			throw new NotFoundException(__('Invalid transfer'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('AccountingCode');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		
		$datasource=$this->Transfer->getDataSource();				
		try {
			$datasource->begin();

			// first bring back everything to original state
			$oldTransfer=$this->Transfer->find('first',array(
				'conditions'=>array(
					'Transfer.id'=>$id,
				)
			));
			$oldAccountingRegister=$this->AccountingRegister->find('first',array(
				'conditions'=>array('AccountingRegister.id'=>$oldTransfer['Transfer']['accounting_register_id'])
			));
			
			if (!empty($oldAccountingRegister)){
				// first remove the movement
				$oldAccountingMovements=$this->AccountingMovement->find('all',array(
					'fields'=>array('AccountingMovement.id'),
					'conditions'=>array(
						'accounting_register_id'=>$oldAccountingRegister['AccountingRegister']['id'],
					)
				));
				if (!empty($oldAccountingMovements)){
					foreach ($oldAccountingMovements as $oldAccountingMovement){
						$this->AccountingMovement->delete($oldAccountingMovement['AccountingMovement']['id']);
					}
				}
				// then remove the register
				$this->AccountingRegister->delete($oldAccountingRegister['AccountingRegister']['id']);
				// then remove the register cash receipt link
			}
			// then remove the cash receipt
			$this->Transfer->delete($oldTransfer['Transfer']['id']);						
			
			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la transferencia con código ".$oldTransfer['Transfer']['transfer_code']);
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('Los datos de la remisión no se podían remover.'), 'default',array('class' => 'error-message'));
		}				
				
		return $this->redirect(array('action' => 'index'));
	}

}
