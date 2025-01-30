<?php

App::uses('AppController', 'Controller');

class AccountingCodesController extends AppController {

	public $components = array('Paginator');
	 
	public function verReporteCaja() {
	
		$cashboxAccountingCodeId=ACCOUNTING_CODE_CASHBOX_MAIN;
		$cashBox=array();
		$initialSaldo=0;
		$finalSaldo=0;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$cashboxAccountingCodeId=$this->request->data['Report']['cashbox_accounting_code_id'];
		
			$initialSaldo=$this->AccountingCode->getTotalSaldo($cashboxAccountingCodeId,$startDate);
			$finalSaldo=$this->AccountingCode->getTotalSaldo($cashboxAccountingCodeId,$endDatePlusOne);
		}
		
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$cashboxMovements=array();
		if ($cashboxAccountingCodeId>0){
			$this->loadModel('AccountingCode');
			$this->loadModel('AccountingRegister');
			$this->loadModel('AccountingMovement');
			$this->loadModel('AccountingRegisterCashReceipt');
			$this->loadModel('AccountingRegisterInvoice');
			$this->loadModel('CashReceipt');
			$this->loadModel('Invoice');
			$this->loadModel('Transfer');
			$this->AccountingCode->recursive=-1;
			$this->CashReceipt->recursive=0;
			$this->Invoice->recursive=0;
			$this->Transfer->recursive=0;
			
			$cashbox=$this->AccountingCode->read(null,$cashboxAccountingCodeId);
			
			$accountingRegistersForPeriod=$this->AccountingRegister->find('list',array(
				'conditions'=>array(
					'AccountingRegister.register_date >='=>$startDate,
					'AccountingRegister.register_date <'=>$endDatePlusOne,
				),
			));
			//pr($accountingRegistersForPeriod);
			
			$cashboxMovements=$this->AccountingMovement->find('all',array(
				'conditions'=>array(
					'AccountingMovement.accounting_code_id'=>$cashboxAccountingCodeId,
					'AccountingMovement.accounting_register_id'=>$accountingRegistersForPeriod,
				),
				'order'=>'AccountingRegister.register_date ASC',
				'contain'=>array(
					'AccountingCode'=>array(
						'fields'=>'AccountingCode.fullname',
					),
					'AccountingRegister'
				),
			));
			//pr($cashboxMovements);
			for ($c=0;$c<count($cashboxMovements);$c++){
				
				$accountingRegisterId=$cashboxMovements[$c]['AccountingRegister']['id'];
				
				$accountingRegisterCashReceipt=$this->AccountingRegisterCashReceipt->find('first',array(
					'conditions'=>array(
						'AccountingRegisterCashReceipt.accounting_register_id'=>$accountingRegisterId,
					),
				));
				if (!empty($accountingRegisterCashReceipt)){
					$cashReceipt=$this->CashReceipt->find('first',[
						'conditions'=>[
							'CashReceipt.id'=>$accountingRegisterCashReceipt['AccountingRegisterCashReceipt']['cash_receipt_id'],
						],
            'contain'=>[
              'Client',
              'CashReceiptInvoice'=>['Invoice']
            ],
					]);
          for ($cri=0;$cri<count($cashReceipt['CashReceiptInvoice']);$cri++){
            if ($cashReceipt['CashReceiptInvoice'][$cri]['Invoice']['bool_paid']){
              $cashReceipt['CashReceiptInvoice'][$cri]['Invoice']['pending_saldo_cs']=0;
            }
            else {
              $cashReceipt['CashReceiptInvoice'][$cri]['Invoice']['pending_saldo_cs']=$this->Invoice->getPendingAmountCS($cashReceipt['CashReceiptInvoice'][$cri]['Invoice']['id']);
            }
          }
					$cashboxMovements[$c]['CashReceipt']=$cashReceipt;
				}
				else {
					$accountingRegisterInvoice=$this->AccountingRegisterInvoice->find('first',array(
						'conditions'=>array(
							'AccountingRegisterInvoice.accounting_register_id'=>$accountingRegisterId,
						),
					));
					if (!empty($accountingRegisterInvoice)){
						$invoice=$this->Invoice->find('first',[
							'conditions'=>[
								'Invoice.id'=>$accountingRegisterInvoice['AccountingRegisterInvoice']['invoice_id'],
							],
						]);
						$cashboxMovements[$c]['Invoice']=$invoice;
					}
					else {
						$transfer=$this->Transfer->find('first',array(
							'conditions'=>array(
								'Transfer.accounting_register_id'=>$accountingRegisterId,
							),
						));
						$cashboxMovements[$c]['Transfer']=$transfer;
					}
				}
				
			}	
			
			//pr($cashboxMovements);
		}
		
		$cashboxAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_CASHBOXES,
			)
		));
		
		$cashboxAccountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>'AccountingCode.fullname',
			'conditions'=>array(
				'AccountingCode.lft >'=>$cashboxAccountingCode['AccountingCode']['lft'],
				'AccountingCode.rght <'=>$cashboxAccountingCode['AccountingCode']['rght'],
			)
		));
		//pr($cashboxAccountingCodes);
		$this->set(compact('cashboxMovements','cashboxAccountingCodes','startDate','endDate','cashboxAccountingCodeId','cashbox','initialSaldo','finalSaldo'));
    
    $aco_name="Transfers/crearDeposito";		
		$bool_deposit_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_deposit_add_permission'));
		$aco_name="CashReceipts/add";		
		$bool_cash_receipt_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_cash_receipt_add_permission'));
    
    $aco_name="ThirdParties/resumenClientes";		
		$bool_client_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_index_permission'));
		$aco_name="ThirdParties/crearCliente";		
		$bool_client_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_client_add_permission'));
	}
	
	public function guardarReporteCaja() {
		$exportData=$_SESSION['reporteCaja'];
		$this->set(compact('exportData'));
	}
	
	public function getaccountsaldo(){
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
		$accounting_code_id=trim($_POST['accounting_code_id']);
		$accounting_code_day=trim($_POST['accounting_code_day']);
		$accounting_code_month=trim($_POST['accounting_code_month']);
		$accounting_code_year=trim($_POST['accounting_code_year']);

		$accountingDateString=$accounting_code_day.'-'.$accounting_code_month.'-'.$accounting_code_year;
		$accountingDate=date( "Y-m-d", strtotime($accountingDateString));
		
		
		$saldo=$this->AccountingCode->getTotalSaldo($accounting_code_id,$accountingDate);
		//echo "saldo is ".$saldo."<br/>";
		//pr ($saldo);
		return $saldo;
	}  
	 
	public function getaccountingcodenature($accountingcodeid){
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
		
		if (!$accountingcodeid){
			throw new NotFoundException(__('No está presente la cuenta contable'));
		}
		if (!$this->AccountingCode->exists($accountingcodeid)) {
			throw new NotFoundException(__('Cuenta Contable inválida'));
		}

		$accountingCode=$this->AccountingCode->find('first',array(
			'conditions'=>array('AccountingCode.id'=>$accountingcodeid),
		));
		//pr($accountingCode);
		
		return $accountingCode['AccountingCode']['bool_creditor'];
	} 
	 
	public function getaccountingcodeforparent(){
		$this->layout="ajax";
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
		$parentaccountingcodeid=trim($_POST['parentaccountingcodeid']);
		
		
		$lastAccountingCode=$this->AccountingCode->find('first',array(
			'conditions'=>array('AccountingCode.parent_id'=>$parentaccountingcodeid),
			'order'=>'AccountingCode.code DESC',
		));
		$newAccountingCode="";
		if (empty($lastAccountingCode)){
			$parentAccountingCode=$this->AccountingCode->find('first',array(
				'conditions'=>array('AccountingCode.id'=>$parentaccountingcodeid),
			));
			$newAccountingCode=$parentAccountingCode['AccountingCode']['code']."-001";
		}
		else {
			$positionLastHyphen=strrpos($lastAccountingCode['AccountingCode']['code'],"-",-1);
			$prefix=substr($lastAccountingCode['AccountingCode']['code'],0,$positionLastHyphen);
			$oldRankNumber=substr($lastAccountingCode['AccountingCode']['code'],$positionLastHyphen+1);
			$newRankNumber=$oldRankNumber+1;
			$newAccountingCode=$prefix."-".str_pad($newRankNumber,3,'0',STR_PAD_LEFT);
		}
		return $newAccountingCode;
	}
  
	public function indexOriginal() {
		
		$this->AccountingCode->recursive = 0;
		$this->set('accountingCodes', $this->Paginator->paginate());
		
	}
	
	public function index() {
        $accountingCodes = $this->AccountingCode->find('threaded', array( 
            //'order' => array('AccountingCode.lft')
			'order' => array('AccountingCode.code')
		)); 
		$this->set(compact('accountingCodes'));
		
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}
	
	public function viewOriginal($id = null) {
		if (!$this->AccountingCode->exists($id)) {
			throw new NotFoundException(__('Invalid accounting code'));
		}
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
		elseif (!empty($this->params['named']['sort'])){
			$startDate=$_SESSION['startDateAccountingCodes'];
			$endDate=$_SESSION['endDateAccountingCodes'];
			$endDatePlusOne=date( "Y-m-d", strtotime( $endDate."+1 days" ) );
		}
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$_SESSION['startDateAccountingCodes']=$startDate;
		$_SESSION['endDateAccountingCodes']=$endDate;
		
		$this->loadModel('AccountingMovement');
		$accountingCode=$this->AccountingCode->find('first', array(
			'conditions' => array(
				'AccountingCode.id' => $id
			),
			'contain'=>array(
				'ChildAccountingCode',
				'ParentAccountingCode',
			)
		));
		$accountingMovements=$this->AccountingMovement->find('all',array(
			'fields'=>array(
				'AccountingMovement.amount','AccountingMovement.currency_id','AccountingMovement.bool_debit',
				'AccountingRegister.id','AccountingRegister.register_date','AccountingRegister.concept',
				'Currency.abbreviation',
			),
			'conditions'=>array(
				'AccountingMovement.accounting_code_id'=>$id,
				'AccountingRegister.register_date >='=>$startDate,
				'AccountingRegister.register_date <'=>$endDatePlusOne,
			),
			'order'=>'AccountingRegister.register_date ASC,AccountingRegister.id ASC',
		));
		/*
		$boolBankAccount='0';
		$banksAccountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_BANKS);
		if (($accountingCode['AccountingCode']['lft']>$banksAccountingCode['AccountingCode']['lft'])&&($accountingCode['AccountingCode']['rght']<$banksAccountingCode['AccountingCode']['rght'])){
			$boolBankAccount=true;
			
		}
		*/
		$accountingCode['accountingMovements']=$accountingMovements;
		//pr($accountingCode);
		
		$saldoStartDate=$this->AccountingCode->getTotalSaldo($accountingCode['AccountingCode']['id'],$startDate); 
		$saldoEndDatePlusOne=$this->AccountingCode->getTotalSaldo($accountingCode['AccountingCode']['id'],$endDatePlusOne); 
		
		$saldo=$this->AccountingCode->getTotalSaldo($accountingCode['AccountingCode']['id'],date('Y-m-d')); 
		$registerdate=date('Y-m-d');
		$accountingcodeid=$id;
		
		$this->set(compact('accountingCode','saldo','startDate','endDate','saldoStartDate','saldoEndDatePlusOne'));
	}

	public function view($id = null) {
		if (!$this->AccountingCode->exists($id)) {
			throw new NotFoundException(__('Invalid accounting code'));
		}
		
		$startDate = null;
		$endDate = null;
		$saldoStartDate=array(); 
		$saldoEndDatePlusOne=array(); 
		
		$registerDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		$accountingCode=$this->AccountingCode->find('first', array(
			'conditions' => array(
				'AccountingCode.id' => $id
			),
			'contain'=>array(
				'ChildAccountingCode',
				'ParentAccountingCode',
			)
		));
		//echo "retrieving the saldo";
		$saldo=$this->AccountingCode->getTotalSaldo($accountingCode['AccountingCode']['id'],$registerDatePlusOne); 
		//echo "saldo received by controller is ".$saldo."<br/>";
		$accountingcodeid=$id;
		
		if ($accountingCode['AccountingCode']['bool_main']){
			for ($i=0; $i<count($accountingCode['ChildAccountingCode']); $i++){
				$childSaldo=$this->AccountingCode->getTotalSaldo($accountingCode['ChildAccountingCode'][$i]['id'],$registerDatePlusOne); 
				$accountingCode['ChildAccountingCode'][$i]['saldo']=$childSaldo;
			}
		}
		else {
			if ($this->request->is('post')) {
				$startDateArray=$this->request->data['Report']['startdate'];
				$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
				$startDate=date( "Y-m-d", strtotime($startDateString));
			
				$endDateArray=$this->request->data['Report']['enddate'];
				$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
				$endDate=date("Y-m-d",strtotime($endDateString));
				$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			}
			elseif (!empty($this->params['named']['sort'])){
				$startDate=$_SESSION['startDateAccountingCodes'];
				$endDate=$_SESSION['endDateAccountingCodes'];
				$endDatePlusOne=date( "Y-m-d", strtotime( $endDate."+1 days" ) );
			}
			if (!isset($startDate)){
				$startDate = date("Y-m-01");
			}
			if (!isset($endDate)){
				$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
				$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
			}
			$_SESSION['startDateAccountingCodes']=$startDate;
			$_SESSION['endDateAccountingCodes']=$endDate;
		
			$this->loadModel('AccountingMovement');
			$this->AccountingMovement->virtualFields['total_amount']=0;
			$this->AccountingMovement->virtualFields['count_movements']=0;
			$accountingMovements=$this->AccountingMovement->find('all',array(
				'fields'=>array(
					'SUM(AccountingMovement.amount) as AccountingMovement__total_amount','COUNT(AccountingMovement.amount) as AccountingMovement__count_movements',
					'AccountingMovement.currency_id','AccountingMovement.bool_debit','AccountingMovement.concept',
					'AccountingRegister.id','AccountingRegister.register_date','AccountingRegister.concept','AccountingRegister.accounting_register_type_id','AccountingRegister.register_code',
					'Currency.abbreviation',
				),
				'conditions'=>array(
					'AccountingMovement.accounting_code_id'=>$id,
					'AccountingRegister.register_date >='=>$startDate,
					'AccountingRegister.register_date <'=>$endDatePlusOne,
				),
				'order'=>'AccountingRegister.register_date ASC,AccountingRegister.id ASC',
				'group'=>'AccountingRegister.id,AccountingMovement.bool_debit,AccountingMovement.concept',
			));
			//pr($accountingMovements);
			
			$boolBankAccount='0';
			$accountingCode['bankaccount']='0';
			$banksAccountingCode=$this->AccountingCode->read(null,ACCOUNTING_CODE_BANKS);
			if (($accountingCode['AccountingCode']['lft']>$banksAccountingCode['AccountingCode']['lft'])&&($accountingCode['AccountingCode']['rght']<$banksAccountingCode['AccountingCode']['rght'])){
				$boolBankAccount=true;
				$accountingCode['bankaccount']=true;
				// echo "haha, the accounting code with id ".$accounting_code_id." is a bank account<br/>";
				/*
				for ($a=0;$a<count($accountingMovements);$a++){
					$paymentProofs=array();
					$paymentProofs=$this->PaymentProof->find('all',array(
						'conditions'=>array(
							'PaymentProof.accounting_register_id'=>$accountingMovements[$a]['AccountingRegister']['id'],
						),
					));
					if (!empty($paymentProofs)){
						$accountingMovements[$a]['PaymentProof']=$paymentProofs[0]['PaymentProof'];
					}
					else {
						$accountingMovements[$a]['PaymentProof']=array();
					}
				}
				*/
			}
			//pr($accountingMovements);
			/*
			if (($accountingCode['AccountingCode']['lft']>$banksAccountingCode['AccountingCode']['lft'])&&($accountingCode['AccountingCode']['rght']<$banksAccountingCode['AccountingCode']['rght'])){
				usort($accountingMovements,array($this,'sortByChequeCode'));
			}
			*/
			$accountingCode['accountingMovements']=$accountingMovements;
			//pr($accountingCode);
			$saldoStartDate=$this->AccountingCode->getTotalSaldo($accountingCode['AccountingCode']['id'],$startDate); 
			$saldoEndDatePlusOne=$this->AccountingCode->getTotalSaldo($accountingCode['AccountingCode']['id'],$endDatePlusOne); 
		}
		
		$this->loadModel('AccountingRegisterType');
		$accountingRegisterTypes=$this->AccountingRegisterType->find('list');
		
		$this->set(compact('accountingCode','saldo','startDate','endDate','saldoStartDate','saldoEndDatePlusOne','boolBankAccount','accountingRegisterTypes'));
		
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$previousAccountingCodesWithThisCode=array();
			$previousAccountingCodesWithThisCode=$this->AccountingCode->find('all',array(
				'conditions'=>array(
					'AccountingCode.code'=>$this->request->data['AccountingCode']['code'],
				),
			));
			if (count($previousAccountingCodesWithThisCode)>0){
				$this->Session->setFlash(__('Ya se introdujo una cuenta contable con este código!  No se guardó la cuenta contable.'), 'default',array('class' => 'error-message'));
			}
			else {			
				$this->AccountingCode->create();
				if ($this->request->data['AccountingCode']['parent_id']==0){
					$this->request->data['AccountingCode']['parent_id']="";
				}
				if ($this->AccountingCode->save($this->request->data)) {
					$this->recordUserAction($this->AccountingCode->id,null,null);
					$this->Session->setFlash(__('The accounting code has been saved.'), 'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'index'));
				}
				else {
					$this->Session->setFlash(__('The accounting code could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
				}
			}
		}
		$parentAccountingCodes = $this->AccountingCode->find('list',array(
			'fields'=>array('fullname'),
			'order' => array('AccountingCode.code'),
		));
		$this->set(compact('parentAccountingCodes'));
		
		$aco_name="AccountingRegisters/index";		
		$bool_accountingregister_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_index_permission'));
		$aco_name="AccountingRegisters/add";		
		$bool_accountingregister_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_accountingregister_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->AccountingCode->exists($id)) {
			throw new NotFoundException(__('Invalid accounting code'));
		}
		$this->AccountingCode->id=$id;
		if ($this->request->is(array('post', 'put'))) {
			$previousAccountingCodesWithThisCode=array();
			$previousAccountingCode=$this->AccountingCode->read(null,$id);
			if ($previousAccountingCode['AccountingCode']['code']!=$this->request->data['AccountingCode']['code']){
				$previousAccountingCodesWithThisCode=$this->AccountingCode->find('all',array(
					'conditions'=>array(
						'AccountingCode.code'=>$this->request->data['AccountingCode']['code'],
					),
				));
			}
			if (count($previousAccountingCodesWithThisCode)>0){
				$this->Session->setFlash(__('Ya se introdujo una cuenta contable con este código!  No se guardó la cuenta contable.'), 'default',array('class' => 'error-message'));
			}
			else {	
				//pr ($this->request->data);
				if ($this->request->data['AccountingCode']['parent_id']==0){
					$this->request->data['AccountingCode']['parent_id']="";
				}
				if ($this->AccountingCode->save($this->request->data)) {
					$this->recordUserAction();
					$this->Session->setFlash(__('The accounting code has been saved.'), 'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'index'));
				}
				else {
					pr($this->validateErrors($this->AccountingCode));
					$this->Session->setFlash(__('No se podía guardar la cuenta contable. Por favor intente nuevamente.'), 'default',array('class' => 'error-message'));
				}
			}
		} else {
			$options = array('conditions' => array('AccountingCode.' . $this->AccountingCode->primaryKey => $id));
			$this->request->data = $this->AccountingCode->find('first', $options);
		}
		$parentAccountingCodes = $this->AccountingCode->find('list',array(
			'fields'=>array('fullname'),'order' => array('AccountingCode.code'),
		));
		//pr($parentAccountingCodes);
		$this->set(compact('parentAccountingCodes','id'));
		
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
		$this->AccountingCode->id = $id;
		if (!$this->AccountingCode->exists()) {
			throw new NotFoundException(__('Invalid accounting code'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AccountingCode->delete()) {
			$this->Session->setFlash(__('The accounting code has been deleted.'));
		} else {
			$this->Session->setFlash(__('The accounting code could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	
	public function loadCodes() {

	/*
		$data=array();$data['code']='1';$data['description']='ACTIVO';$data['bool_creditor']=0;$data['parent_id']=null;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11';$data['description']='ACTIVO CIRCULANTE';$data['bool_creditor']=0;$data['parent_id']=1;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111';$data['description']='CAJA Y BANCO';$data['bool_creditor']=0;$data['parent_id']=2;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111-01';$data['description']='CAJA';$data['bool_creditor']=0;$data['parent_id']=3;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11101-1';$data['description']='CAJA CHICA';$data['bool_creditor']=0;$data['parent_id']=4;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111011-01';$data['description']='Silvia Elena Avendaño';$data['bool_creditor']=0;$data['parent_id']=5;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111011-02';$data['description']='Yanel Cosme Chevez';$data['bool_creditor']=0;$data['parent_id']=5;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111011-03';$data['description']='Javier Ibarra';$data['bool_creditor']=0;$data['parent_id']=5;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111011-04';$data['description']='Felix Ortiz';$data['bool_creditor']=0;$data['parent_id']=5;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111-02';$data['description']='BANCOS';$data['bool_creditor']=0;$data['parent_id']=3;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11102-1';$data['description']='CUENTA BANCO MONEDA NACIONAL C$';$data['bool_creditor']=0;$data['parent_id']=10;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111021-01';$data['description']='CTA. CTE Banpro N°1001-02-0-452880-2';$data['bool_creditor']=0;$data['parent_id']=11;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111021-02';$data['description']='CTA. CTE Banpro N°1001-02-0-579340-4';$data['bool_creditor']=0;$data['parent_id']=11;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111021-03';$data['description']='xxxx';$data['bool_creditor']=0;$data['parent_id']=11;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11102-2';$data['description']='CUENTA BANCO MONEDA EXTRANJERA  U$';$data['bool_creditor']=0;$data['parent_id']=10;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111022-01';$data['description']='CTA. Ahorro Banpro N° 1002-02-1-534051-8';$data['bool_creditor']=0;$data['parent_id']=15;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111022-02';$data['description']='CTA. CTE Banpro N° 1001-02-1-889632-8';$data['bool_creditor']=0;$data['parent_id']=15;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111022-03';$data['description']='CTA. CTE Bancentro N° 52110164-4';$data['bool_creditor']=0;$data['parent_id']=15;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='111022-04';$data['description']='CTA. CTE Bac 01502041-5';$data['bool_creditor']=0;$data['parent_id']=15;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112';$data['description']='CUENTAS POR COBRAR';$data['bool_creditor']=0;$data['parent_id']=2;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112-01';$data['description']='CUENTAS POR COBRAR A CLIENTES';$data['bool_creditor']=0;$data['parent_id']=20;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11201-1';$data['description']=' COMASA';$data['bool_creditor']=0;$data['parent_id']=21;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112011-01';$data['description']='Cosecha de invierno (Comasa)';$data['bool_creditor']=0;$data['parent_id']=22;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112011-02';$data['description']='Cosecha de Verano (Comasa)';$data['bool_creditor']=0;$data['parent_id']=22;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11201-2';$data['description']='ARROSISA';$data['bool_creditor']=0;$data['parent_id']=21;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112012-01';$data['description']='Cosecha de invierno (Arrosisa)';$data['bool_creditor']=0;$data['parent_id']=25;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112012-02';$data['description']='Cosecha de Verano (Arrosisa)';$data['bool_creditor']=0;$data['parent_id']=25;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112012-03';$data['description']='Cosecha de Canicula (Arrosisa)';$data['bool_creditor']=0;$data['parent_id']=25;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11201-3';$data['description']='INGENIO MONTE ROSA.';$data['bool_creditor']=0;$data['parent_id']=21;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112013-01';$data['description']='Cosecha de invierno (Ingenio)';$data['bool_creditor']=0;$data['parent_id']=29;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112013-02';$data['description']='Cosecha de Verano (Ingenio)';$data['bool_creditor']=0;$data['parent_id']=29;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112-02';$data['description']='CUENTAS POR COBRAR EMPLEADOS';$data['bool_creditor']=0;$data['parent_id']=20;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11202-1';$data['description']='PRESTAMOS  ';$data['bool_creditor']=0;$data['parent_id']=32;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-01';$data['description']='Juan Hermes Castillo Morales';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-02';$data['description']='Moises Pérez Martinez';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-03';$data['description']='Lesbia Barrera Ramos';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-04';$data['description']='Javier Ibarra';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-05';$data['description']='Felix Jarol Matamoros';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-06';$data['description']='Martire Esteban Sotelo';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-07';$data['description']='Aaron Hernandez';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-08';$data['description']='Yanel Chevez';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-09';$data['description']='William Bojorge Henandez';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-10';$data['description']='Darvin  Rodriguez Flores';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-11';$data['description']='Nerys Calderon';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-12';$data['description']='Luis Selva';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112021-13';$data['description']='silvio centeno';$data['bool_creditor']=0;$data['parent_id']=33;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11202-2';$data['description']='ADELANTOS SALARIALES';$data['bool_creditor']=0;$data['parent_id']=32;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112022-01';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=47;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112-03';$data['description']='CUENTAS POR COBRAR ENTRE COMPAÑIAS';$data['bool_creditor']=0;$data['parent_id']=20;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11203-1';$data['description']='CUENTAS POR COBRAR ENTRE COMPAÑIAS';$data['bool_creditor']=0;$data['parent_id']=49;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112031-01';$data['description']='Milpa Vieja (Juan  C. Gurdian T)';$data['bool_creditor']=0;$data['parent_id']=50;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112031-02';$data['description']='Agricola Lourdes Gurdian & Cia';$data['bool_creditor']=0;$data['parent_id']=50;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112031-03';$data['description']='Maria Lourdes Teran Icaza';$data['bool_creditor']=0;$data['parent_id']=50;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112-04';$data['description']='CUENTAS  POR COBRAR  A SOCIOS';$data['bool_creditor']=0;$data['parent_id']=20;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11204-1';$data['description']='CUENTAS  POR COBRAR  A SOCIOS';$data['bool_creditor']=0;$data['parent_id']=54;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112041-01';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=55;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112041-02';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=55;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112-05';$data['description']='OTRAS CTS X COB DIVERSAS';$data['bool_creditor']=0;$data['parent_id']=20;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11205-1';$data['description']='PROVEEDORES ';$data['bool_creditor']=0;$data['parent_id']=58;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112051-01';$data['description']='Nimac';$data['bool_creditor']=0;$data['parent_id']=59;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112051-02';$data['description']='Maquipos';$data['bool_creditor']=0;$data['parent_id']=59;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112051-03';$data['description']='Roberto Leiva';$data['bool_creditor']=0;$data['parent_id']=59;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112051-04';$data['description']='Uno San Benito';$data['bool_creditor']=0;$data['parent_id']=59;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11205-2';$data['description']='PERSONAL NATURALES';$data['bool_creditor']=0;$data['parent_id']=58;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112052-01';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=64;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112052-02';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=64;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11205-3';$data['description']='TRANSPORTISTAS';$data['bool_creditor']=0;$data['parent_id']=58;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112053-01';$data['description']='Rommy A. Rodriguez Cruz';$data['bool_creditor']=0;$data['parent_id']=67;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112053-02';$data['description']='Jairo Martinez Salmeron';$data['bool_creditor']=0;$data['parent_id']=67;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112053-03';$data['description']='Pastor Centeno';$data['bool_creditor']=0;$data['parent_id']=67;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='112053-04';$data['description']='Pedro Velasquez';$data['bool_creditor']=0;$data['parent_id']=67;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='113';$data['description']='FONDOS POR JUSTIFICAR';$data['bool_creditor']=0;$data['parent_id']=2;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='113-01';$data['description']='FONDOS POR JUSTIFICAR EMPLEADOS Y OTROS';$data['bool_creditor']=0;$data['parent_id']=72;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11301-1';$data['description']='FONDOS  POR JUSTIFICAR EMPLEADOS';$data['bool_creditor']=0;$data['parent_id']=73;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='113011-01';$data['description']='Gabriel Chevez';$data['bool_creditor']=0;$data['parent_id']=74;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='113011-02';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=74;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11301-2';$data['description']='FONDOS  POR JUSTIFICAR  OTROS';$data['bool_creditor']=0;$data['parent_id']=73;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='113012-01';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=77;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='113012-02';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=77;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114';$data['description']='RETENCIONES POR VENTA';$data['bool_creditor']=0;$data['parent_id']=2;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114-01';$data['description']='RETENCIONES POR VENTA';$data['bool_creditor']=0;$data['parent_id']=80;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11401-1';$data['description']='RETENCIONES AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=81;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114011-01';$data['description']='Retencion Mani';$data['bool_creditor']=0;$data['parent_id']=82;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114011-02';$data['description']='Retencion Arroz ';$data['bool_creditor']=0;$data['parent_id']=82;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114011-03';$data['description']='Retencion Caña';$data['bool_creditor']=0;$data['parent_id']=82;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114011-04';$data['description']='Retencion Soya';$data['bool_creditor']=0;$data['parent_id']=82;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114011-05';$data['description']='Retencion Sorgo';$data['bool_creditor']=0;$data['parent_id']=82;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114011-06';$data['description']='Retencion Platano';$data['bool_creditor']=0;$data['parent_id']=82;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114011-07';$data['description']='Retencion Maiz';$data['bool_creditor']=0;$data['parent_id']=82;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='11401-2';$data['description']='RETENCION GANADERA';$data['bool_creditor']=0;$data['parent_id']=81;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='114012-01';$data['description']='Retencion Ganado';$data['bool_creditor']=0;$data['parent_id']=90;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12';$data['description']='ACTIVO FIJO';$data['bool_creditor']=0;$data['parent_id']=1;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='121';$data['description']='TERRENOS Y EDIFICIOS ';$data['bool_creditor']=0;$data['parent_id']=92;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='121-01';$data['description']='TERRENOS Y EDIFICIOS ';$data['bool_creditor']=0;$data['parent_id']=93;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12101-1';$data['description']='EDIFICIO ';$data['bool_creditor']=0;$data['parent_id']=94;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='121011-01';$data['description']='Casa (Instalaciones  Oficinas)';$data['bool_creditor']=0;$data['parent_id']=95;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='121011-02';$data['description']='Casa Hacienda San Jose';$data['bool_creditor']=0;$data['parent_id']=95;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12101-2';$data['description']='TERRENOS ';$data['bool_creditor']=0;$data['parent_id']=94;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='121012-01';$data['description']='Terreno de casa (Instalaciones Oficinas)';$data['bool_creditor']=0;$data['parent_id']=98;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='121012-02';$data['description']='Finca San José';$data['bool_creditor']=0;$data['parent_id']=98;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122';$data['description']='INVENTARIO';$data['bool_creditor']=0;$data['parent_id']=92;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122-01';$data['description']='INVENTARIO DE MOB Y EQUIPO';$data['bool_creditor']=0;$data['parent_id']=101;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12201-1';$data['description']='MOB Y EQUIPO DE OFICINA';$data['bool_creditor']=0;$data['parent_id']=102;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122011-01';$data['description']='Equipo de Computo';$data['bool_creditor']=0;$data['parent_id']=103;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122011-02';$data['description']='Mobiliario de Oficina';$data['bool_creditor']=0;$data['parent_id']=103;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122011-03';$data['description']='Equipo de Oficina';$data['bool_creditor']=0;$data['parent_id']=103;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122011-04';$data['description']='Equipo de Seguridad';$data['bool_creditor']=0;$data['parent_id']=103;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122-02';$data['description']='VEHICULOS';$data['bool_creditor']=0;$data['parent_id']=101;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12202-1';$data['description']='VEHICULOS';$data['bool_creditor']=0;$data['parent_id']=108;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122021-01';$data['description']='Vehiculos Liviano de 2 ruedas';$data['bool_creditor']=0;$data['parent_id']=109;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122021-02';$data['description']='Vehiculo Liviano de 4 ruedas';$data['bool_creditor']=0;$data['parent_id']=109;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122021-03';$data['description']='Vehiculo pesasdo';$data['bool_creditor']=0;$data['parent_id']=109;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122-03';$data['description']='MAQUINARIA AGRICOLA, IMPLEMENTOS Y EQUIPOS DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=101;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12203-1';$data['description']='MAQUINARIA AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=113;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122031-01';$data['description']='Tractores';$data['bool_creditor']=0;$data['parent_id']=114;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122031-02';$data['description']='Combinas';$data['bool_creditor']=0;$data['parent_id']=114;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122031-03';$data['description']='Cosechadoras';$data['bool_creditor']=0;$data['parent_id']=114;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122031-04';$data['description']='Sembradora de Granos';$data['bool_creditor']=0;$data['parent_id']=114;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12203-2';$data['description']='IMPLEMENTOS AGRICOLAS';$data['bool_creditor']=0;$data['parent_id']=113;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-01';$data['description']='Gradas';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-02';$data['description']='Arados';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-03';$data['description']='Romplonas';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-04';$data['description']='Arrancadores';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-05';$data['description']='Surcador';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-06';$data['description']='Mureador';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-07';$data['description']='Rotocultor';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-08';$data['description']='Cultivadora de Caña';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-09';$data['description']='Set de Orugas Laverda';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-10';$data['description']='Cabezales de Cosechadora';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-11';$data['description']='Cortadores de Bejuco';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-12';$data['description']='Kazamba ';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-13';$data['description']='Niveladora  ';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-14';$data['description']='Aporreadora de AJONJOLI';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-15';$data['description']='Boleadora';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-16';$data['description']='Embaladora y Rastrillo';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-17';$data['description']='Fangueadores ';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-18';$data['description']='Bombas y Equipos de fumigacion';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-19';$data['description']='Pipas';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122032-20';$data['description']='Trailes';$data['bool_creditor']=0;$data['parent_id']=119;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12203-3';$data['description']='EQUIPO DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=113;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122033-01';$data['description']='Motores';$data['bool_creditor']=0;$data['parent_id']=140;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122033-02';$data['description']='Bombas';$data['bool_creditor']=0;$data['parent_id']=140;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122033-03';$data['description']='Tuberias y Accesorios de Riego';$data['bool_creditor']=0;$data['parent_id']=140;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122-04';$data['description']='MAQUINARIA Y EQUIPOS DE TALLER';$data['bool_creditor']=0;$data['parent_id']=101;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12204-1';$data['description']='MAQUINARIA DE TALLER';$data['bool_creditor']=0;$data['parent_id']=144;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122041-01';$data['description']='Soldadores';$data['bool_creditor']=0;$data['parent_id']=145;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122041-02';$data['description']='Generadores';$data['bool_creditor']=0;$data['parent_id']=145;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12204-2';$data['description']='EQUIPOS Y HERRAMIENTAS DE TALLER';$data['bool_creditor']=0;$data['parent_id']=144;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122042-01';$data['description']='Equipos y accesorios';$data['bool_creditor']=0;$data['parent_id']=148;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='122042-02';$data['description']='Herramientas';$data['bool_creditor']=0;$data['parent_id']=148;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123';$data['description']='INVENTARIO DE SEMOVIENTES';$data['bool_creditor']=0;$data['parent_id']=92;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123-01';$data['description']='GANADO VACUNO';$data['bool_creditor']=0;$data['parent_id']=151;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12301-1';$data['description']='VACAS';$data['bool_creditor']=0;$data['parent_id']=152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123011-01';$data['description']='vacas de 25-30 meses vacias';$data['bool_creditor']=0;$data['parent_id']=153;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123011-02';$data['description']='vacas de 30 meses gestadas';$data['bool_creditor']=0;$data['parent_id']=153;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12301-2';$data['description']='VAQUILLAS';$data['bool_creditor']=0;$data['parent_id']=152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123012-01';$data['description']='Vaquillas de  13-24 meses';$data['bool_creditor']=0;$data['parent_id']=156;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12301-3';$data['description']='TERNERAS';$data['bool_creditor']=0;$data['parent_id']=152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123013-01';$data['description']='Terneros Lactantes 0-12 Meses';$data['bool_creditor']=0;$data['parent_id']=158;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12301-4';$data['description']='MACHOS';$data['bool_creditor']=0;$data['parent_id']=152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123014-01';$data['description']='Toros';$data['bool_creditor']=0;$data['parent_id']=160;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123014-02';$data['description']='Novillos de 25-30 meses';$data['bool_creditor']=0;$data['parent_id']=160;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123014-03';$data['description']='Terneros de Destete 13-24 Meses';$data['bool_creditor']=0;$data['parent_id']=160;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123014-04';$data['description']='Terneros Lactantes 0-12 Meses';$data['bool_creditor']=0;$data['parent_id']=160;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12301-5';$data['description']='EQUINOS';$data['bool_creditor']=0;$data['parent_id']=152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123012-01';$data['description']='Caballos Nacionales';$data['bool_creditor']=0;$data['parent_id']=165;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='123012-02';$data['description']='Caballos Extranjeros';$data['bool_creditor']=0;$data['parent_id']=165;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124';$data['description']='DEPRECIACION  ACUMULADA ';$data['bool_creditor']=0;$data['parent_id']=92;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124-01';$data['description']='DEP EDIFICIO, MOB, EQUIP Y VEHICULOS';$data['bool_creditor']=0;$data['parent_id']=168;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12401-1';$data['description']='DEPRECIACION  ACUMULADA DE  EDIFICIO';$data['bool_creditor']=0;$data['parent_id']=169;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124011-01';$data['description']='Dep Acumulada Edificio';$data['bool_creditor']=0;$data['parent_id']=170;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12401-2';$data['description']='DEPRECICION  ACUMULADA DE  MOB. Y EQUIPO DE OFICINA';$data['bool_creditor']=0;$data['parent_id']=169;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124012-01';$data['description']='Equipo de computo';$data['bool_creditor']=0;$data['parent_id']=172;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124012-02';$data['description']='Mob de oficina';$data['bool_creditor']=0;$data['parent_id']=172;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124012-03';$data['description']='Equipo de Oficina';$data['bool_creditor']=0;$data['parent_id']=172;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124012-04';$data['description']='Equipo de seguridad';$data['bool_creditor']=0;$data['parent_id']=172;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12401-3';$data['description']='DEPRECIACION ACUMULADA DE  VEHICULOS';$data['bool_creditor']=0;$data['parent_id']=169;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124013-01';$data['description']='vehiculos Liviano de 2 ruedas';$data['bool_creditor']=0;$data['parent_id']=177;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124013-02';$data['description']='Vehiculo Liviano de 4 ruedas';$data['bool_creditor']=0;$data['parent_id']=177;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124013-03';$data['description']='Vehiculo pesasdo';$data['bool_creditor']=0;$data['parent_id']=177;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12401-4';$data['description']='DEPRECIACION  ACUMULADA DE  MAQ AGRICOLA IMPLEMENTOS Y EQU DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=169;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124014-01';$data['description']='Dep Maq Agricola';$data['bool_creditor']=0;$data['parent_id']=181;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124014-02';$data['description']='Dep Implementos Agricola';$data['bool_creditor']=0;$data['parent_id']=181;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124014-03';$data['description']='Dep Equip de riego';$data['bool_creditor']=0;$data['parent_id']=181;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='12401-5';$data['description']='DEP DE MAQUINARIA Y EQUIPO DE TALLER';$data['bool_creditor']=0;$data['parent_id']=169;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124015-01';$data['description']='Dep de Maquinaria de taller';$data['bool_creditor']=0;$data['parent_id']=185;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='124015-02';$data['description']='Dep de Equipos y Herramientas de taller';$data['bool_creditor']=0;$data['parent_id']=185;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13';$data['description']='OTROS ACTIVOS';$data['bool_creditor']=0;$data['parent_id']=1;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131';$data['description']='PRODUCCION EN PROCESO MANI';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=189;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13101-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=190;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=191;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=191;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131011-03';$data['description']='Inss patronal';$data['bool_creditor']=0;$data['parent_id']=191;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=191;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=191;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=191;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=191;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=191;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=189;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13102-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=200;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=201;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=189;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13103-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=204;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=189;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13104-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=206;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=207;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=207;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13104-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=206;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=210;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131042-02';$data['description']='Siembra';$data['bool_creditor']=0;$data['parent_id']=210;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131042-03';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=210;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131042-04';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=210;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13104-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=206;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=206;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131053-01';$data['description']='Mant y rep de Maq Agricola';$data['bool_creditor']=0;$data['parent_id']=215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131053-02';$data['description']='Mant y Rep de impl agricolas';$data['bool_creditor']=0;$data['parent_id']=215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131053-03';$data['description']='Mant Equipo de Riego';$data['bool_creditor']=0;$data['parent_id']=215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=189;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13105-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=220;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=221;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=221;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=189;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13106-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=224;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=225;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=189;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13107-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=227;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131071-01';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=228;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='131071-02';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=228;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132';$data['description']='PRODUCCION EN PROCESO ARROZ ';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=231;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13201-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=232;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=231;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13202-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=242;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=243;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=231;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13203-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=245;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=246;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=231;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13204-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=248;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=249;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=249;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13204-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=248;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=252;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132042-02';$data['description']='Siembra';$data['bool_creditor']=0;$data['parent_id']=252;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132042-03';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=252;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132042-04';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=252;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13204-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=248;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=257;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=231;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13205-1';$data['description']='ALQUILES';$data['bool_creditor']=0;$data['parent_id']=259;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=260;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=260;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=231;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13206-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=263;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=264;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=231;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13207-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=266;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132071-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=267;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132071-02';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=267;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='132071-03';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=267;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133';$data['description']='PRODUCCION EN PROCESO ARROZ CANICULA';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=271;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13301-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=272;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=273;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=273;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133011-03';$data['description']='Inss patronal';$data['bool_creditor']=0;$data['parent_id']=273;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=273;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=273;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=273;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=273;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=273;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=271;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13302-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=282;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=283;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=271;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13303-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=285;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=286;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133031-02';$data['description']='Aplicación Aerea';$data['bool_creditor']=0;$data['parent_id']=286;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=271;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13304-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=289;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=290;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=290;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13304-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=289;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=293;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133042-02';$data['description']='Siembra';$data['bool_creditor']=0;$data['parent_id']=293;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133042-03';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=293;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133042-04';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=293;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13304-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=289;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=298;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=271;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13305-1';$data['description']='ALQUILES';$data['bool_creditor']=0;$data['parent_id']=300;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=301;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=301;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=271;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13306-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=304;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='1330601-1';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=305;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=271;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13307-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=307;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133071-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=308;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133071-02';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=308;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='133071-03';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=308;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134';$data['description']='PRODUCCION EN PROCESO PLATANO';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=312;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13401-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=313;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=314;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=314;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=314;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=314;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=314;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=314;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=314;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=314;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134-02';$data['description']='VIVERO';$data['bool_creditor']=0;$data['parent_id']=312;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13402-1';$data['description']='INSUMOS PARA VIVERO';$data['bool_creditor']=0;$data['parent_id']=323;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134021-01';$data['description']='Tierra y Cascarilla';$data['bool_creditor']=0;$data['parent_id']=324;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134021-02';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=324;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134021-03';$data['description']='Bolsas para almacigo';$data['bool_creditor']=0;$data['parent_id']=324;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134021-04';$data['description']='Insumo';$data['bool_creditor']=0;$data['parent_id']=324;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134021-05';$data['description']='Energia electrica';$data['bool_creditor']=0;$data['parent_id']=324;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=312;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13403-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=330;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=331;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=312;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13404-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=333;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=334;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=334;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13404-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=333;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134042-02';$data['description']='Siembra';$data['bool_creditor']=0;$data['parent_id']=337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134042-03';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134042-04';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13404-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=333;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=342;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=312;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13405-1';$data['description']='ALQUILES';$data['bool_creditor']=0;$data['parent_id']=344;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=345;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=345;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=312;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13406-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=348;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=349;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=312;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13407-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=351;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134071-01';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=352;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='134071-02';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=352;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135';$data['description']='PRODUCCION EN PROCESO CAÑA';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13501-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=356;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=357;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=357;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=357;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=357;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=357;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=357;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=357;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=357;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13502-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=366;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=367;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13503-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=369;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=370;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13504-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=372;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=373;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=373;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13504-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=372;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=376;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=376;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135042-03';$data['description']='Cultivo-Fertilizacion';$data['bool_creditor']=0;$data['parent_id']=376;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=376;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=376;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13504-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=372;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=382;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13505-1';$data['description']='ALQUILES';$data['bool_creditor']=0;$data['parent_id']=384;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=385;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=385;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13506-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=388;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=389;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13507-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=391;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135071-01';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=392;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='135071-02';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=392;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136';$data['description']='PRODUCCION EN PROCESO SOYA';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=395;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13601-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=396;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=397;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=397;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=397;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=397;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=397;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=397;$this->AccountingCode->create();$this->AccountingCode->save($data);
		*/
		
		
		/*
		$data=array();$data['code']='136011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=397;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=397;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=395;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13602-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=406;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=407;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136021-02';$data['description']='Inoculante';$data['bool_creditor']=0;$data['parent_id']=407;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=395;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13603-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=410;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=411;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=395;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13604-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=413;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=414;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=414;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13604-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=413;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=417;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=417;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136042-03';$data['description']='Cultivo-Fertilizacion';$data['bool_creditor']=0;$data['parent_id']=417;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=417;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=417;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13604-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=413;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=423;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=395;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13605-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=425;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=426;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=426;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=395;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13606-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=429;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=430;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=395;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13607-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=432;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136071-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=433;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136071-02';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=433;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='136071-03';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=433;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137';$data['description']='PROD PROCESO SORGO';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=437;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13701-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=438;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=439;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=439;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=439;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=439;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=439;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=439;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=439;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=439;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=437;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13702-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=448;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=449;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=437;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13703-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=451;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=452;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=437;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13704-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=454;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=455;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=455;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13704-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=454;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=458;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=458;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137042-03';$data['description']='Cultivo-Fertilizacion';$data['bool_creditor']=0;$data['parent_id']=458;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=458;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=458;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13704-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=454;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=464;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=437;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13705-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=466;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=467;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=467;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=437;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13706-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=470;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=471;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=437;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13707-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=473;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137071-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=474;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137071-02';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=474;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='137071-03';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=474;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138';$data['description']='PROD PROCESO AJONOLI';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=478;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13801-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=479;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=480;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=480;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=480;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=480;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=480;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=480;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=480;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=480;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=478;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13802-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=489;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=490;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=478;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13803-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=492;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=493;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=478;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13804-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=495;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=496;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=496;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13804-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=495;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=499;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=499;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138042-03';$data['description']='Cultivo-Fertilizacion';$data['bool_creditor']=0;$data['parent_id']=499;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=499;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=499;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13804-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=495;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=505;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=478;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13805-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=507;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=508;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=508;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=478;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13806-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=511;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=512;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=478;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13807-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=514;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138071-01';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=515;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='138071-02';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=515;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139';$data['description']='PRODUCCION  PROCESO MAIZ';$data['bool_creditor']=0;$data['parent_id']=188;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=518;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13901-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=519;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=520;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=520;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=520;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=520;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=520;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=520;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=520;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=520;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=518;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13902-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=529;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=530;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=518;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13903-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=532;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=533;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=518;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13904-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=535;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=536;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=536;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13904-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=535;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=539;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=539;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139042-03';$data['description']='Cultivo-Fertilizacion';$data['bool_creditor']=0;$data['parent_id']=539;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=539;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=539;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13904-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=535;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=545;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=518;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13905-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=547;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=548;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=548;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=518;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13906-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=551;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=552;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139-07';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=518;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='13907-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=554;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139071-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=555;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139071-02';$data['description']='Costos de Taller';$data['bool_creditor']=0;$data['parent_id']=555;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='139071-03';$data['description']='Otros Egresos';$data['bool_creditor']=0;$data['parent_id']=555;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='2';$data['description']='PASIVOS';$data['bool_creditor']=0;$data['parent_id']=null;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21';$data['description']='PASIVO CIRCULANTE ';$data['bool_creditor']=0;$data['parent_id']=559;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211';$data['description']='PASIVO CIRCULANTE ';$data['bool_creditor']=0;$data['parent_id']=560;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211-01';$data['description']='PROVEEDORES';$data['bool_creditor']=0;$data['parent_id']=561;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21101-1';$data['description']='PROVEEDORES DE INSUMOS';$data['bool_creditor']=0;$data['parent_id']=562;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-01';$data['description']='Formunica';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-02';$data['description']='Sagsa';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-03';$data['description']='Agroalfa';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-04';$data['description']='Cisa Agro';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-05';$data['description']='Duweat';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-06';$data['description']='Agrovet de Nicaragua';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-07';$data['description']='Insecticida San Cristobal';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-08';$data['description']='Ramac';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-09';$data['description']='Plaza Agropecuaria';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-10';$data['description']='Punto Agicola S.A.';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-11';$data['description']='Agritade';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-12';$data['description']='Mansell Semilla';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-13';$data['description']='El Colono Agrop, S.A.';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211011-14';$data['description']='Marketing Nic.';$data['bool_creditor']=0;$data['parent_id']=563;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21101-2';$data['description']='PROVEEDORES DE REPUESTOS';$data['bool_creditor']=0;$data['parent_id']=562;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-01';$data['description']='Nimac';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-02';$data['description']='Maquipos';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-03';$data['description']='AgroIndustrial Mantica';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-04';$data['description']='La Casa del perno';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-05';$data['description']='Ragrisa';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-06';$data['description']='Comercial Insdustrial';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-07';$data['description']='Dianca';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-08';$data['description']='Casa Pellas';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-09';$data['description']='Petronio Montoya';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-10';$data['description']='Imprenta Igosa';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='211012-11';$data['description']='Formunica';$data['bool_creditor']=0;$data['parent_id']=578;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212';$data['description']='CUENTAS POR PAGAR';$data['bool_creditor']=0;$data['parent_id']=560;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212-01';$data['description']='CUENTAS POR PAGAR C/P';$data['bool_creditor']=0;$data['parent_id']=590;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21201-1';$data['description']='CUENTAS POR PAGAR ENTRE COMPAÑIAS';$data['bool_creditor']=0;$data['parent_id']=591;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212011-01';$data['description']='Agricola Lourdes';$data['bool_creditor']=0;$data['parent_id']=592;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212011-02';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=592;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212011-03';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=592;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212011-04';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=592;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212011-05';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=592;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212011-06';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=592;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212011-07';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=592;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212011-08';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=592;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21201-2';$data['description']='CUENTAS POR PAGAR A SOCIOS';$data['bool_creditor']=0;$data['parent_id']=591;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212012-01';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=601;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212012-02';$data['description']='XXXX';$data['bool_creditor']=0;$data['parent_id']=601;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212-02';$data['description']='OTRAS CTAS X PAG DIVERSAS';$data['bool_creditor']=0;$data['parent_id']=590;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21202-1 ';$data['description']='PERSONAS NATURALES';$data['bool_creditor']=0;$data['parent_id']=604;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212021-01';$data['description']='Willian A. Gurdian Teran';$data['bool_creditor']=0;$data['parent_id']=605;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212021-02';$data['description']='Juan C. Gurdian Teran';$data['bool_creditor']=0;$data['parent_id']=605;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21202-2 ';$data['description']=' A EX-EMPLEADOS (INDEMNIZACIONES)';$data['bool_creditor']=0;$data['parent_id']=604;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212022-01';$data['description']='Victor Calderon';$data['bool_creditor']=0;$data['parent_id']=608;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212022-02';$data['description']='Yanel Chevez';$data['bool_creditor']=0;$data['parent_id']=608;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21202-3 ';$data['description']='A TARJETAS DE CREDITO';$data['bool_creditor']=0;$data['parent_id']=604;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212023-01';$data['description']='Banpro Visa';$data['bool_creditor']=0;$data['parent_id']=611;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212023-02';$data['description']='Uno Fleet';$data['bool_creditor']=0;$data['parent_id']=611;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212023-03';$data['description']='Uno Banpro';$data['bool_creditor']=0;$data['parent_id']=611;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212023-04';$data['description']='Credomatic';$data['bool_creditor']=0;$data['parent_id']=611;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='212023-05';$data['description']='Presmart';$data['bool_creditor']=0;$data['parent_id']=611;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213';$data['description']='PRESTAMOS BANCARIOS  X PAG CORTO PLAZO';$data['bool_creditor']=0;$data['parent_id']=560;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213-01';$data['description']='BANPRO';$data['bool_creditor']=0;$data['parent_id']=617;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21301-1';$data['description']='HABILITACION DE MANI';$data['bool_creditor']=0;$data['parent_id']=618;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213011-01';$data['description']='Desembolsos de Mani,  Ptmo. 252817 (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=619;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213011-02';$data['description']='Desembolsos de Mani, Ptmo. 252817(Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=619;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213011-03';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.252817';$data['bool_creditor']=0;$data['parent_id']=619;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213011-04';$data['description']='Desemb. de Nueva Hab. Mani (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=619;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213011-05';$data['description']='Desemb. de Nueva Hab. Mani (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=619;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213011-06';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=619;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21301-2';$data['description']='HABILITACION DE ARROZ';$data['bool_creditor']=0;$data['parent_id']=618;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-01';$data['description']='Desembolsos de Arroz verano (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-02';$data['description']='Desembolsos de Arroz Verano (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-03';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-04';$data['description']='Desembolsos de Arroz Invierno (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-05';$data['description']='Desembolsos de Arroz Invierno (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-06';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-07';$data['description']='Desembolsos de Arroz Canicula (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-08';$data['description']='Desembolsos de Arroz Canicula (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213012-09';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=626;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21301-3';$data['description']='HABILITACION DE PLATANO';$data['bool_creditor']=0;$data['parent_id']=618;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213013-01';$data['description']='Desembolsos de Platano ptmo. (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=636;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213013-02';$data['description']='Desembolsos de Platano ptmo. (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=636;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213013-03';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=636;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21301-4';$data['description']='HABILITACION DE CAÑA';$data['bool_creditor']=0;$data['parent_id']=618;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-01';$data['description']='Desembolsos de Caña, Ptmo.252810 (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-02';$data['description']='Desembolsos de Caña, Ptmo.252810 (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-03';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.252810';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-04';$data['description']='Desembolsos de Caña, Ptmo.255014 (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-05';$data['description']='Desembolsos de Caña, Ptmo.255014 (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-06';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.255014';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-07';$data['description']='Des. Nueva Hab de Caña, Ptmo. (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-08';$data['description']='Des. Nueva Hab de Caña, Ptmo. (Garantia Bancarias)';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213014-09';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=640;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21301-5';$data['description']='HABILITACION DE SOYA';$data['bool_creditor']=0;$data['parent_id']=618;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213015-01';$data['description']='Desembolsos de Soya ptmo. (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=650;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213015-02';$data['description']='Desembolsos de Soya ptmo. (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=650;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213015-03';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=650;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21301-6';$data['description']='HABILITACION DE SORGO';$data['bool_creditor']=0;$data['parent_id']=618;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213016-01';$data['description']='Desembolsos de Sorgo Ptmo. 258677 (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=654;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213016-02';$data['description']='Desembolsos de Sorgo Ptmo.258677 (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=654;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213016-03';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.258677';$data['bool_creditor']=0;$data['parent_id']=654;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21301-7';$data['description']='HABILITACION DE AJONJOLI';$data['bool_creditor']=0;$data['parent_id']=618;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213017-01';$data['description']='Desembolsos de Ajonjoli Ptmo. (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=658;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213017-02';$data['description']='Desembolsos de Ajonjoli, Ptmo. (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=658;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213017-03';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=658;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21301-8';$data['description']='HABILITACION DE MAIZ';$data['bool_creditor']=0;$data['parent_id']=618;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213018-01';$data['description']='Desembolsos de Maiz Ptmo. (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=662;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213018-02';$data['description']='Desembolsos de Maiz Ptmo. (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=662;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213018-03';$data['description']='INT y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=662;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213-02';$data['description']='BAC';$data['bool_creditor']=0;$data['parent_id']=617;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21302-1';$data['description']='HABILITACION DE MANI';$data['bool_creditor']=0;$data['parent_id']=666;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213021-01';$data['description']='Desembolsos de Mani,  Ptmo. 3193351 (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=667;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213021-02';$data['description']='Desembolsos de Mani, Ptmo. 3193351(Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=667;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213021-03';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.3193351';$data['bool_creditor']=0;$data['parent_id']=667;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213021-04';$data['description']='Desemb. de Nueva Hab. Mani (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=667;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213021-05';$data['description']='Desemb. de Nueva Hab. Mani (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=667;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213021-06';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=667;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21302-2';$data['description']='HABILITACION DE SORGO';$data['bool_creditor']=0;$data['parent_id']=666;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213022-01';$data['description']='Desembolsos de Sorgo,  Ptmo. 3289751 (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=674;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213022-02';$data['description']='Desembolsos de Sorgo, Ptmo. 3289751(Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=674;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213022-03';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.3289751';$data['bool_creditor']=0;$data['parent_id']=674;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213022-04';$data['description']='Desemb. de Nueva Hab. Sorgo (Efectivo)';$data['bool_creditor']=0;$data['parent_id']=674;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213022-05';$data['description']='Desemb. de Nueva Hab. Sorgo (Garantia Bancaria)';$data['bool_creditor']=0;$data['parent_id']=674;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='213022-06';$data['description']='Int. y Mantenimiento al Valor por pagar';$data['bool_creditor']=0;$data['parent_id']=674;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214';$data['description']='RETENCIONES Y PROVICIONES ACUMULADAS  POR PAGAR';$data['bool_creditor']=0;$data['parent_id']=560;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214-01';$data['description']='IMPUESTOS      X PAGAR                                                  ';$data['bool_creditor']=0;$data['parent_id']=681;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21401-1';$data['description']='IR X PAGAR';$data['bool_creditor']=0;$data['parent_id']=682;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214011-01';$data['description']='Retenciones en la fuente x Pagar ';$data['bool_creditor']=0;$data['parent_id']=683;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214-02';$data['description']='INSS E INATEC';$data['bool_creditor']=0;$data['parent_id']=681;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21402-1';$data['description']='INSS E INATEC';$data['bool_creditor']=0;$data['parent_id']=685;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214021-01';$data['description']='Inss Laboral';$data['bool_creditor']=0;$data['parent_id']=686;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214021-02';$data['description']='Inss Patronal';$data['bool_creditor']=0;$data['parent_id']=686;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214021-03';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=686;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214-03';$data['description']='PROVISIONES X PAG';$data['bool_creditor']=0;$data['parent_id']=681;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='21403-1';$data['description']='PROVISIONES';$data['bool_creditor']=0;$data['parent_id']=690;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214031-01';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=691;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214031-02';$data['description']='Teceavos mes';$data['bool_creditor']=0;$data['parent_id']=691;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='214031-03';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=691;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='22';$data['description']='PASIVO FIJO';$data['bool_creditor']=0;$data['parent_id']=559;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221';$data['description']='CUENTAS POR PAG L/P';$data['bool_creditor']=0;$data['parent_id']=695;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221-01';$data['description']='FINANCIAMIENTOS AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=696;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='22101-1';$data['description']='PREST AMO BANPRO';$data['bool_creditor']=0;$data['parent_id']=697;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221011-01';$data['description']='Prestamo  N°220161. Finca San José';$data['bool_creditor']=0;$data['parent_id']=698;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221011-02';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.220161';$data['bool_creditor']=0;$data['parent_id']=698;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221011-03';$data['description']='Prestamo  N°227062. Camioneta Mahindra Sencilla 2012';$data['bool_creditor']=0;$data['parent_id']=698;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221011-04';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.227062';$data['bool_creditor']=0;$data['parent_id']=698;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='22101-2';$data['description']='PRESTAMO  BANCENTRO';$data['bool_creditor']=0;$data['parent_id']=696;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221012-01';$data['description']='Prestamo N°1020120381 (1 Amada y 1 Tractor)';$data['bool_creditor']=0;$data['parent_id']=703;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221012-02';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.1020120381';$data['bool_creditor']=0;$data['parent_id']=703;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221012-03';$data['description']='Prestamo N°101961539 (1Volquete, 1 Sembradora Y 1 mureador)';$data['bool_creditor']=0;$data['parent_id']=703;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221012-04';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.101961539';$data['bool_creditor']=0;$data['parent_id']=703;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221012-05';$data['description']='Prestamo N°102020381. Camioneta Mahindra Doble Cabina';$data['bool_creditor']=0;$data['parent_id']=703;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221012-06';$data['description']='Int. y Mantenimiento al Valor por pagar, Ptmo.102020381';$data['bool_creditor']=0;$data['parent_id']=703;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='22101-3';$data['description']='PREST BAC';$data['bool_creditor']=0;$data['parent_id']=696;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221013-01';$data['description']='Prestamo N°2384921 (1 Sembradora, 1 Mureador y 1 Arrancador)';$data['bool_creditor']=0;$data['parent_id']=710;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221013-02';$data['description']='Int. y Mantenimiento al Valor por pagar. Ptmo.2384921';$data['bool_creditor']=0;$data['parent_id']=710;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='22101-4';$data['description']='PREST BDF';$data['bool_creditor']=0;$data['parent_id']=696;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='221014-01';$data['description']='xxxxxxx';$data['bool_creditor']=0;$data['parent_id']=713;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='3';$data['description']='PATRIMONIO Y CAPITAL';$data['bool_creditor']=0;$data['parent_id']=null;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='31';$data['description']='PATRIMONIO Y CAPITAL';$data['bool_creditor']=0;$data['parent_id']=715;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='311';$data['description']='PATRIMONIO Y CAPITAL';$data['bool_creditor']=0;$data['parent_id']=716;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='311-01';$data['description']='CAPITAL SOCIAL';$data['bool_creditor']=0;$data['parent_id']=717;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='31101-1';$data['description']='CAPITAL SOCIAL';$data['bool_creditor']=0;$data['parent_id']=718;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='311011-01';$data['description']='Capital Social';$data['bool_creditor']=0;$data['parent_id']=719;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='311-02';$data['description']='RESERVA LEGAL';$data['bool_creditor']=0;$data['parent_id']=717;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='31102-1';$data['description']='RESERVA LEGAL';$data['bool_creditor']=0;$data['parent_id']=721;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='311021-01';$data['description']='Reserva Legal';$data['bool_creditor']=0;$data['parent_id']=722;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='311-03';$data['description']='UTILIDAD O PERDIDA';$data['bool_creditor']=0;$data['parent_id']=717;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='31103-1';$data['description']='UTILIDAD O PERDIDA';$data['bool_creditor']=0;$data['parent_id']=724;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='311031-01';$data['description']='Utilidad Y/ O Perdida del Ejercicio';$data['bool_creditor']=0;$data['parent_id']=725;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='311031-02';$data['description']='Utilidad Y/O Perdida de periodos Anteriores';$data['bool_creditor']=0;$data['parent_id']=725;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='4';$data['description']='INGRESOS';$data['bool_creditor']=0;$data['parent_id']=null;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='41';$data['description']='INGRESOS ';$data['bool_creditor']=0;$data['parent_id']=728;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411';$data['description']='INGRESOS AGRICOLAS';$data['bool_creditor']=0;$data['parent_id']=729;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411-01';$data['description']='INGRESOS AGRICOLAS';$data['bool_creditor']=0;$data['parent_id']=730;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='41101-1';$data['description']='INGRESOS AGRICOLAS';$data['bool_creditor']=0;$data['parent_id']=731;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411011-01';$data['description']='Ingresos x Ventas de Mani';$data['bool_creditor']=0;$data['parent_id']=732;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411011-02';$data['description']='Ingresos x Ventas de Arroz';$data['bool_creditor']=0;$data['parent_id']=732;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411011-03';$data['description']='Ingresos x Ventas de Platano';$data['bool_creditor']=0;$data['parent_id']=732;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411011-04';$data['description']='Ingresos x Ventas de Caña';$data['bool_creditor']=0;$data['parent_id']=732;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411011-05';$data['description']='Ingresos x Ventas de Soya';$data['bool_creditor']=0;$data['parent_id']=732;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411011-06';$data['description']='Ingresos x Ventas de Sorgo';$data['bool_creditor']=0;$data['parent_id']=732;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411011-07';$data['description']='Ingresos x Ventas de Ajonjoli';$data['bool_creditor']=0;$data['parent_id']=732;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='411011-08';$data['description']='Ingresos x Ventas de Maiz';$data['bool_creditor']=0;$data['parent_id']=732;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='412';$data['description']='INGRESOS AGROPECUARIOS ';$data['bool_creditor']=0;$data['parent_id']=729;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='412-01';$data['description']='INGRESOS AGROPECUARIOS ';$data['bool_creditor']=0;$data['parent_id']=741;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='41201-1';$data['description']='INGRESOS AGROPECUARIOS ';$data['bool_creditor']=0;$data['parent_id']=742;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='412011-01';$data['description']='Ingresos por  Venta de Ganado';$data['bool_creditor']=0;$data['parent_id']=743;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='412011-02';$data['description']='Ingresos por Venta de Leche';$data['bool_creditor']=0;$data['parent_id']=743;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='413';$data['description']='INGRESO POR SERVICIOS';$data['bool_creditor']=0;$data['parent_id']=729;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='413-01';$data['description']='INGRESO POR SERVICIOS';$data['bool_creditor']=0;$data['parent_id']=746;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='41301-1';$data['description']='INGRESO POR SERVICIOS';$data['bool_creditor']=0;$data['parent_id']=747;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='413011-01';$data['description']='Servicio de corte mecanizado';$data['bool_creditor']=0;$data['parent_id']=748;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='413011-02';$data['description']='Servicio de maquinaria';$data['bool_creditor']=0;$data['parent_id']=748;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='413011-03';$data['description']='Servicio de Transporte';$data['bool_creditor']=0;$data['parent_id']=748;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='414';$data['description']='INGRESOS  FINANCIEROS';$data['bool_creditor']=0;$data['parent_id']=729;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='414-01';$data['description']='INGRESOS  FINANCIEROS';$data['bool_creditor']=0;$data['parent_id']=752;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='41401-1';$data['description']='INGRESOS  FINANCIEROS';$data['bool_creditor']=0;$data['parent_id']=753;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='413011-01';$data['description']='Intereses en cuenta de ahorro';$data['bool_creditor']=0;$data['parent_id']=754;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='415';$data['description']='OTROS INGRESOS';$data['bool_creditor']=0;$data['parent_id']=729;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='415-01';$data['description']='OTROS INGRESOS';$data['bool_creditor']=0;$data['parent_id']=756;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='41501-1';$data['description']='OTROS INGRESOS';$data['bool_creditor']=0;$data['parent_id']=756;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='415011-01';$data['description']='Utilidad en Vta de Activo';$data['bool_creditor']=0;$data['parent_id']=758;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='415011-02';$data['description']='Reintegro Tributario';$data['bool_creditor']=0;$data['parent_id']=758;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='415011-03';$data['description']='Venta de pacas';$data['bool_creditor']=0;$data['parent_id']=758;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='415011-04';$data['description']='Otros Ingresos';$data['bool_creditor']=0;$data['parent_id']=758;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='5';$data['description']='GASTOS Y COSTOS';$data['bool_creditor']=0;$data['parent_id']=null;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51';$data['description']='GASTOS ';$data['bool_creditor']=0;$data['parent_id']=763;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511';$data['description']='GASTOS ADMINISTRATIVOS';$data['bool_creditor']=0;$data['parent_id']=764;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511-01';$data['description']='GASTOS ADMINISTRATIVOS';$data['bool_creditor']=0;$data['parent_id']=765;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51101-1';$data['description']='SALARIO Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=766;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-02';$data['description']='Incentivos de Produccion';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-03';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-04';$data['description']='Inss patronal';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-05';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-06';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-07';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-08';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511011-09';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=767;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51101-2';$data['description']='MANTENIMIENTO Y REPARACION DE ACTIVOS.';$data['bool_creditor']=0;$data['parent_id']=766;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511012-01';$data['description']='Mantenimiento Y Reparación de Activo';$data['bool_creditor']=0;$data['parent_id']=777;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511012-02';$data['description']='Mantenimiento Y Reparación de  Vehiculo';$data['bool_creditor']=0;$data['parent_id']=777;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511012-03';$data['description']='Mantenimiento Y Reparación de  Equipo de Computo';$data['bool_creditor']=0;$data['parent_id']=777;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511012-04';$data['description']='Mantenimiento Y Reparación de  Mobiliario de Oficina';$data['bool_creditor']=0;$data['parent_id']=777;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511012-05';$data['description']='Mant y rep de Equipo de oficina';$data['bool_creditor']=0;$data['parent_id']=777;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511012-06';$data['description']='Mant y Rep de equip de Seguridad';$data['bool_creditor']=0;$data['parent_id']=777;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51101-3';$data['description']='SERVICIOS ';$data['bool_creditor']=0;$data['parent_id']=766;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511013-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=784;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511013-02';$data['description']='Agua';$data['bool_creditor']=0;$data['parent_id']=784;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511013-03';$data['description']='Telefono e Internet';$data['bool_creditor']=0;$data['parent_id']=784;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511013-04';$data['description']='Cable';$data['bool_creditor']=0;$data['parent_id']=784;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511013-05';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=784;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511013-06';$data['description']='Alquiler de Local Oficina';$data['bool_creditor']=0;$data['parent_id']=784;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511013-07';$data['description']='Transporte';$data['bool_creditor']=0;$data['parent_id']=784;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511013-08';$data['description']='Otros Servicios';$data['bool_creditor']=0;$data['parent_id']=784;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51101-4';$data['description']='MATERIALES Y SUMINISTROS';$data['bool_creditor']=0;$data['parent_id']=766;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511014-01';$data['description']='Combustible y Lubricantes';$data['bool_creditor']=0;$data['parent_id']=793;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511014-02';$data['description']='Papeleria y utiles de Oficina';$data['bool_creditor']=0;$data['parent_id']=793;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511014-03';$data['description']='otros materiales';$data['bool_creditor']=0;$data['parent_id']=793;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511014-04';$data['description']='Materiales de Limpieza para uso local Oficina';$data['bool_creditor']=0;$data['parent_id']=793;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511014-05';$data['description']='Otros Materiales';$data['bool_creditor']=0;$data['parent_id']=793;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51101-5';$data['description']='POLIZAS DE SEGURO';$data['bool_creditor']=0;$data['parent_id']=766;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511015-01';$data['description']='Poliza de Vida Individual (Seguros Médicos)';$data['bool_creditor']=0;$data['parent_id']=799;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511015-02';$data['description']='Poliza de Vida Colectivo ';$data['bool_creditor']=0;$data['parent_id']=799;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511015-03';$data['description']='Poliza de Incendio';$data['bool_creditor']=0;$data['parent_id']=799;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511015-04';$data['description']='Poliza de Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=799;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511015-05';$data['description']='Poliza de Vehiculo de 2 ruedas';$data['bool_creditor']=0;$data['parent_id']=799;$this->AccountingCode->create();$this->AccountingCode->save($data);
		*/
		
		
		
		
		/*
		$data=array();$data['code']='51101-6';$data['description']='IMPUESTOS ';$data['bool_creditor']=0;$data['parent_id']=766;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511016-01';$data['description']='Impuestos de Bienes Inmuebles (IBI)';$data['bool_creditor']=0;$data['parent_id']=805;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511016-02';$data['description']='Rodamientos';$data['bool_creditor']=0;$data['parent_id']=805;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511016-03';$data['description']='Imp, Licencia, matricula , placa,multas y otros';$data['bool_creditor']=0;$data['parent_id']=805;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511016-04';$data['description']='Papel Sellado Y timbres  fiscales';$data['bool_creditor']=0;$data['parent_id']=805;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51101-7';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=766;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511017-01';$data['description']='Gastos legales';$data['bool_creditor']=0;$data['parent_id']=810;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51101-8';$data['description']='OTROS GASTOS';$data['bool_creditor']=0;$data['parent_id']=766;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-01';$data['description']='Viaticos de Aliementacion Y Transporte';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-02';$data['description']='Bono Escolar';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-03';$data['description']='Bono Navideño';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-04';$data['description']='Capacitacion y Seminarios personal';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-05';$data['description']='Afiliaciones y Suscripciones';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-06';$data['description']='Gastos Medicos';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-07';$data['description']='Convivio y Gratificaciones';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-08';$data['description']='cuentas incobrables                                       ';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='511018-09';$data['description']='Gastos Diversos';$data['bool_creditor']=0;$data['parent_id']=812;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512';$data['description']='GASTOS FINANCIEROS';$data['bool_creditor']=0;$data['parent_id']=764;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512-01';$data['description']='GASTOS FINANCIEROS';$data['bool_creditor']=0;$data['parent_id']=822;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51201-1';$data['description']='GASTOS FINANCIEROS';$data['bool_creditor']=0;$data['parent_id']=823;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512011-01';$data['description']='Intereses';$data['bool_creditor']=0;$data['parent_id']=824;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512011-02';$data['description']='Comisión x desembolso';$data['bool_creditor']=0;$data['parent_id']=824;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512011-03';$data['description']='Mantenimiento de Valor';$data['bool_creditor']=0;$data['parent_id']=824;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='513';$data['description']='GASTOS DE DEPRECIACION';$data['bool_creditor']=0;$data['parent_id']=764;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='513-01';$data['description']='DEPRECIACION  ACUMULADA';$data['bool_creditor']=0;$data['parent_id']=828;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51301-1';$data['description']='GASTO DE DEPRECIACION  ACUMULADA  EDIFICIO';$data['bool_creditor']=0;$data['parent_id']=829;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='513011-01';$data['description']='Dep Acumulada Edificio';$data['bool_creditor']=0;$data['parent_id']=830;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51301-2';$data['description']='DEPRECIACION MOBILIARIO  Y EQUP DE OFICINA';$data['bool_creditor']=0;$data['parent_id']=829;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512012-01';$data['description']='Equipo de computo';$data['bool_creditor']=0;$data['parent_id']=832;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512012-02';$data['description']='Mobiliario  de oficina';$data['bool_creditor']=0;$data['parent_id']=832;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512012-03';$data['description']='Equipo de oficina';$data['bool_creditor']=0;$data['parent_id']=832;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='512012-04';$data['description']='Equipo de seguridad';$data['bool_creditor']=0;$data['parent_id']=832;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='51301-3';$data['description']='DEPRECIACION VEHICULO ';$data['bool_creditor']=0;$data['parent_id']=829;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='513013-01';$data['description']='Vehiculos liviano de 2 ruedas';$data['bool_creditor']=0;$data['parent_id']=837;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='513013-02';$data['description']='Vehiculo liviano de 4 ruedas';$data['bool_creditor']=0;$data['parent_id']=837;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='513013-03';$data['description']='Vehiculo pesasdo';$data['bool_creditor']=0;$data['parent_id']=837;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52';$data['description']='COSTOS DE PRODUCCION';$data['bool_creditor']=0;$data['parent_id']=763;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521';$data['description']='COSTO DE PRODUCCION MANI';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52101-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=843;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=844;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=844;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521011-03';$data['description']='Inss patronal';$data['bool_creditor']=0;$data['parent_id']=844;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=844;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=844;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=844;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=844;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=844;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52102-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=853;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=854;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52103-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=856;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=857;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52104-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=859;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=860;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=860;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52104-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521042-01';$data['description']='Prep de sulo';$data['bool_creditor']=0;$data['parent_id']=863;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=863;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521042-03';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=863;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521042-04';$data['description']='Arranque ';$data['bool_creditor']=0;$data['parent_id']=863;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521042-05';$data['description']='combina';$data['bool_creditor']=0;$data['parent_id']=863;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521042-06';$data['description']='Corte';$data['bool_creditor']=0;$data['parent_id']=863;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521042-07';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=863;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52104-3';$data['description']='COMBUSTIBLES Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=871;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52105-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=873;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=874;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=874;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521051-03';$data['description']='Alquiler de Maquinaria';$data['bool_creditor']=0;$data['parent_id']=874;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52106-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=878;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=879;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52107-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=881;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=882;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=882;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52108-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=885;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=886;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52109-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=888;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521091-1';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=889;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521091-2';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=889;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521091-3';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=889;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=842;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52110-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=893;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-01';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-02';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-03';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-04';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-05';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-06';$data['description']='Mantenimiento y Reparacion Camino ';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-07';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-08';$data['description']='costos de taller';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='521001-09';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=894;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522';$data['description']='COSTO DE PRODUCCION ARROZ ';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52201-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=905;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=906;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=906;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522011-03';$data['description']='Inss patronal';$data['bool_creditor']=0;$data['parent_id']=906;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=906;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=906;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=906;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=906;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=906;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-02';$data['description']='SEMILLAS ';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52202-1';$data['description']='SEMILLAS PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=915;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522021-01';$data['description']='Semillas ';$data['bool_creditor']=0;$data['parent_id']=916;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52203-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=918;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=919;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52204-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=921;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=922;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=922;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52204-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522042-01';$data['description']='Preparacion de   suelo';$data['bool_creditor']=0;$data['parent_id']=925;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522042-02';$data['description']='Siembra';$data['bool_creditor']=0;$data['parent_id']=925;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522042-03';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=925;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522042-04';$data['description']='Corte Y Cosecha';$data['bool_creditor']=0;$data['parent_id']=925;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52204-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522043-01';$data['description']='Comb y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=930;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52205-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=932;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=933;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52205-2';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=933;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522052-01';$data['description']='Alquiler de Maquinaria';$data['bool_creditor']=0;$data['parent_id']=933;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52206-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=937;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=938;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52207-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=940;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=941;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=941;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52208-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=944;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=945;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52209-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=947;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522091-01';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=948;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522091-02';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=948;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522091-03';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=948;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522091-04';$data['description']='Servicio de almacenaje, secado y limpieza';$data['bool_creditor']=0;$data['parent_id']=948;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=904;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52210-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=953;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-02';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-03';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-04';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-05';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-06';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-07';$data['description']='Mantenimiento y repacaion camino';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-08';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-09';$data['description']='Costos de taller';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='522082-10';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=954;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523';$data['description']='COSTOS DE PRODUCCION  ARROZ CANICULA';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52301-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=966;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=967;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=967;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523011-03';$data['description']='Inss patronal';$data['bool_creditor']=0;$data['parent_id']=967;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=967;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=967;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=967;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=967;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=967;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-02';$data['description']='SEMILLAS ';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52302-1';$data['description']='SEMILLAS PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=976;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523021-01';$data['description']='Semillas ';$data['bool_creditor']=0;$data['parent_id']=977;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52303-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=979;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=980;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52304-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=982;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=983;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=983;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52304-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=982;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523042-01';$data['description']='Preparacion de   suelo';$data['bool_creditor']=0;$data['parent_id']=986;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523042-02';$data['description']='Siembra';$data['bool_creditor']=0;$data['parent_id']=986;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523042-03';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=986;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523042-04';$data['description']='Corte Y Cosecha';$data['bool_creditor']=0;$data['parent_id']=986;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52304-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=982;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523043-01';$data['description']='Comb y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=991;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52305-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=993;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=994;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=994;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523051-03';$data['description']='Alquiler de Maquinaria';$data['bool_creditor']=0;$data['parent_id']=994;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52306-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=998;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=999;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52307-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1001;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=1002;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=1002;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52308-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1005;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=1006;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52309-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1008;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523091-01';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=1009;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523091-02';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=1009;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523091-03';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=1009;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523091-04';$data['description']='Servicio de almacenaje, secado y limpieza';$data['bool_creditor']=0;$data['parent_id']=1009;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=965;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52310-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1014;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-02';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-03';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-04';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-05';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-06';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-07';$data['description']='Mantenimiento y repacaion camino';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-08';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-09';$data['description']='Costos de taller';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='523101-10';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=1015;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524';$data['description']='COSTO DE PRODUCCION PLATANO';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52401-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1027;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=1028;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524011-02';$data['description']='Alimentacion';$data['bool_creditor']=0;$data['parent_id']=1028;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=1028;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=1028;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=1028;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=1028;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524011-07';$data['description']='Indemnizacion';$data['bool_creditor']=0;$data['parent_id']=1028;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1028;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-02';$data['description']='VIVERO';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52402-1';$data['description']='INSUMOS PARA VIVERO';$data['bool_creditor']=0;$data['parent_id']=1037;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524021-01';$data['description']='Tierra y Cascarilla';$data['bool_creditor']=0;$data['parent_id']=1038;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524021-02';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=1038;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524021-03';$data['description']='Bolsas para almacigo';$data['bool_creditor']=0;$data['parent_id']=1038;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524021-04';$data['description']='Insumo';$data['bool_creditor']=0;$data['parent_id']=1038;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524021-05';$data['description']='Energia electrica';$data['bool_creditor']=0;$data['parent_id']=1038;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52403-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1044;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=1045;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524031-02';$data['description']='Aplicación Aerea';$data['bool_creditor']=0;$data['parent_id']=1045;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52404-1';$data['description']='COMBUSTIBLES Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1048;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524041-01';$data['description']='Vehiculo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1049;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524041-02';$data['description']='Vehiculo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1049;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52404-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=1048;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524042-01';$data['description']='Prep de sulo';$data['bool_creditor']=0;$data['parent_id']=1052;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=1052;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524042-03';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=1052;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524042-04';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1052;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52404-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=1048;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524043-01';$data['description']='Com y lub Equip de Riego';$data['bool_creditor']=0;$data['parent_id']=1057;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52405-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1059;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=1060;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524051-02';$data['description']='Alquiler de vehiculos';$data['bool_creditor']=0;$data['parent_id']=1060;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52406-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1063;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524061-01';$data['description']='Energia Electrica';$data['bool_creditor']=0;$data['parent_id']=1064;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52407-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1066;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=1067;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=1067;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52408-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1070;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=1071;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52409-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1073;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524091-01';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=1074;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524091-02';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=1074;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524091-03';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=1074;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524091-04';$data['description']='Servicio de almacenaje, secado y limpieza';$data['bool_creditor']=0;$data['parent_id']=1074;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1026;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52410-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1079;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-02';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-03';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-04';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-05';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-06';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-07';$data['description']='Mantenimiento y repacaion camino';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-08';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-09';$data['description']='Costos de taller';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='524101-10';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=1080;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525';$data['description']='COSTOS DE PRODUCCION CAÑA';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52501-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1092;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=1093;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525011-02';$data['description']='Alimentación';$data['bool_creditor']=0;$data['parent_id']=1093;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=1093;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=1093;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=1093;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=1093;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525011-07';$data['description']='Indemnización';$data['bool_creditor']=0;$data['parent_id']=1093;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1093;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-02';$data['description']='SEMILLAS ';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52502-2';$data['description']='SEMILLAS PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=1102;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525022-01';$data['description']='Semillas para siembra';$data['bool_creditor']=0;$data['parent_id']=1103;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52503-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1105;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=1106;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52504-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1108;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525041-01';$data['description']='Vehículo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1109;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525041-02';$data['description']='Vehículo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1109;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52504-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=1108;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=1112;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=1112;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525042-03';$data['description']='Cultivo-Fertilización';$data['bool_creditor']=0;$data['parent_id']=1112;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=1112;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1112;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52504-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=1108;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525043-01';$data['description']='Comb y lub Equipo de Riego';$data['bool_creditor']=0;$data['parent_id']=1118;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52505-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1120;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=1121;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525051-02';$data['description']='Alquiler de vehículos';$data['bool_creditor']=0;$data['parent_id']=1121;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52506-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1124;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525061-01';$data['description']='Energía Eléctrica';$data['bool_creditor']=0;$data['parent_id']=1125;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52507-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1127;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=1128;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=1128;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52508-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1131;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=1132;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52509-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1134;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525091-01';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=1135;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525091-02';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=1135;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525091-03';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=1135;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525091-04';$data['description']='Servicio de almacenaje, secado y limpieza';$data['bool_creditor']=0;$data['parent_id']=1135;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1091;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52510-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1140;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-02';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-03';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-04';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-05';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-06';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-07';$data['description']='Mantenimiento y repacaion camino';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-08';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-09';$data['description']='Costos de taller';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='525101-10';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=1141;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526';$data['description']='COSTO DE PRODUCCION SOYA';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52601-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1153;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=1154;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526011-02';$data['description']='Alimentación';$data['bool_creditor']=0;$data['parent_id']=1154;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=1154;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=1154;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=1154;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=1154;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526011-07';$data['description']='Indemnización';$data['bool_creditor']=0;$data['parent_id']=1154;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1154;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-02';$data['description']='SEMILLAS';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52602-1';$data['description']='SEMILLAS PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=1163;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526021-01';$data['description']='Semillas para siembra';$data['bool_creditor']=0;$data['parent_id']=1164;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526021-02';$data['description']='Inoculante';$data['bool_creditor']=0;$data['parent_id']=1164;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52603-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1167;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=1168;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52604-1';$data['description']='COMB Y LUB VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1170;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526041-01';$data['description']='Vehículo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1171;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526041-02';$data['description']='Vehículo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1171;$this->AccountingCode->create();$this->AccountingCode->save($data);
		*/
		/*
		$data=array();$data['code']='52604-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=1170;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=1174;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=1174;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526042-03';$data['description']='Cultivo-Fertilización';$data['bool_creditor']=0;$data['parent_id']=1174;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=1174;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1174;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52604-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=1170;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526043-01';$data['description']='Combustible y lub Equipo de Riego';$data['bool_creditor']=0;$data['parent_id']=1180;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52605-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1182;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=1183;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526051-02';$data['description']='Alquiler de vehículos';$data['bool_creditor']=0;$data['parent_id']=1183;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52606-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1186;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526061-01';$data['description']='Energía Eléctrica';$data['bool_creditor']=0;$data['parent_id']=1187;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52607-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1189;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=1190;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=1190;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52608-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1193;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=1194;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52609-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1196;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526091-1';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=1197;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526091-2';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=1197;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526091-3';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=1197;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='5260913-01';$data['description']='Servicio de almacenaje, secado y limpieza';$data['bool_creditor']=0;$data['parent_id']=1200;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1152;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52610-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1202;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1202;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-02';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-03';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-04';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-05';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-06';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-07';$data['description']='Mantenimiento y repacaion camino';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-08';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-09';$data['description']='Costos de taller';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='526101-10';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=1203;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527';$data['description']='COSTO DE PRODUCCION SORGO';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52701-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1216;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=1217;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527011-02';$data['description']='Alimentación';$data['bool_creditor']=0;$data['parent_id']=1217;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=1217;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=1217;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=1217;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=1217;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527011-07';$data['description']='Indemnización';$data['bool_creditor']=0;$data['parent_id']=1217;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1217;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-02';$data['description']='SEMILLAS ';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52702-1';$data['description']='SEMILLAS PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=1226;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527021-01';$data['description']='Semillas ';$data['bool_creditor']=0;$data['parent_id']=1227;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52703-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1229;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=1230;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52704-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1232;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527041-01';$data['description']='Vehículo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527041-02';$data['description']='Vehículo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1233;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52704-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=1232;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=1236;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527042-02';$data['description']='Siembra';$data['bool_creditor']=0;$data['parent_id']=1236;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527042-03';$data['description']='Cultivo-Fertilización';$data['bool_creditor']=0;$data['parent_id']=1236;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=1236;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1236;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52704-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=1232;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527042-01';$data['description']='Comb y lub Equipo de Riego';$data['bool_creditor']=0;$data['parent_id']=1242;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52705-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1244;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=1245;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527051-02';$data['description']='Alquiler de vehículos';$data['bool_creditor']=0;$data['parent_id']=1245;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52706-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1248;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527061-01';$data['description']='Energía Eléctrica';$data['bool_creditor']=0;$data['parent_id']=1249;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52707-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1251;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=1252;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=1252;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52708-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1255;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=1256;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52709-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1258;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527091-01';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=1259;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527091-02';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=1259;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527091-03';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=1259;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527091-04';$data['description']='Servicio de almacenaje, secado y limpieza';$data['bool_creditor']=0;$data['parent_id']=1259;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1215;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52710-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1264;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-02';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-03';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-04';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-05';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-06';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-07';$data['description']='Mantenimiento y repacaion camino';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-08';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-09';$data['description']='Costos de taller';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='527101-10';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=1265;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528';$data['description']='COSTO DE PRODUCCION AJONJOLI';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52801-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1277;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=1278;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528011-02';$data['description']='Alimentación';$data['bool_creditor']=0;$data['parent_id']=1278;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=1278;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=1278;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=1278;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=1278;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528011-07';$data['description']='Indemnización';$data['bool_creditor']=0;$data['parent_id']=1278;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1278;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-02';$data['description']='SEMILLAS ';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52802-1';$data['description']='SEMILLAS PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=1287;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528021-01';$data['description']='Semillas ';$data['bool_creditor']=0;$data['parent_id']=1288;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52803-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1290;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=1291;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52804-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1293;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528041-01';$data['description']='Vehículo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1294;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528041-02';$data['description']='Vehículo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1294;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52804-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=1293;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=1297;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528042-02';$data['description']='Siembra';$data['bool_creditor']=0;$data['parent_id']=1297;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528042-03';$data['description']='Cultivo-Fertilización';$data['bool_creditor']=0;$data['parent_id']=1297;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=1297;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1297;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52804-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=1293;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528043-01';$data['description']='COM y lub Equipo de Riego';$data['bool_creditor']=0;$data['parent_id']=1303;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52805-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1305;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=1306;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528051-02';$data['description']='Alquiler de vehículos';$data['bool_creditor']=0;$data['parent_id']=1306;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52806-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1309;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528061-01';$data['description']='Energía Eléctrica';$data['bool_creditor']=0;$data['parent_id']=1310;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52807-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1312;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=1313;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=1313;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52808-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1316;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=1317;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52809-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1319;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528091-01';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=1320;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528091-02';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=1320;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528091-03';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=1320;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528091-04';$data['description']='Servicio de almacenaje, secado y limpieza';$data['bool_creditor']=0;$data['parent_id']=1320;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1276;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52810-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1325;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-02';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-03';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-04';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-05';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-06';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-07';$data['description']='Mantenimiento y repacaion camino';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-08';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-09';$data['description']='Costos de taller';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='528101-10';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=1326;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529';$data['description']='COSTO DE PRODUCCION  MAIZ';$data['bool_creditor']=0;$data['parent_id']=841;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-01';$data['description']='LABOR';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52901-1';$data['description']='SALARIOS Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1338;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529011-01';$data['description']='Salarios';$data['bool_creditor']=0;$data['parent_id']=1339;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529011-02';$data['description']='Alimentación';$data['bool_creditor']=0;$data['parent_id']=1339;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529011-03';$data['description']='INSS patronal';$data['bool_creditor']=0;$data['parent_id']=1339;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=1339;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=1339;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=1339;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529011-07';$data['description']='Indemnización';$data['bool_creditor']=0;$data['parent_id']=1339;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1339;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-02';$data['description']='SEMILLA ';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52902-1';$data['description']='SEMILLA PARA SIEMBRA';$data['bool_creditor']=0;$data['parent_id']=1348;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529021-01';$data['description']='Semilla';$data['bool_creditor']=0;$data['parent_id']=1349;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-03';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52903-1';$data['description']='INSUMOS';$data['bool_creditor']=0;$data['parent_id']=1351;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529031-01';$data['description']='Insumos';$data['bool_creditor']=0;$data['parent_id']=1352;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-04';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52904-1';$data['description']='COMBUSTIBLE Y LUBRICANTES VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1354;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529041-01';$data['description']='Vehículo de 2 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529041-02';$data['description']='Vehículo de 4 Ruedas';$data['bool_creditor']=0;$data['parent_id']=1355;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52904-2';$data['description']='COMBUSTIBLE Y LUBRICANTES MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=1354;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529042-01';$data['description']='Prep de suelo';$data['bool_creditor']=0;$data['parent_id']=1358;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529042-02';$data['description']='siembra';$data['bool_creditor']=0;$data['parent_id']=1358;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529042-03';$data['description']='Cultivo-Fertilización';$data['bool_creditor']=0;$data['parent_id']=1358;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529042-04';$data['description']='Aplicación';$data['bool_creditor']=0;$data['parent_id']=1358;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529042-05';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1358;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52904-3';$data['description']='COMBUSTIBLES  Y LUBRICANTES EQUIP DE RIEGO';$data['bool_creditor']=0;$data['parent_id']=1354;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529043-01';$data['description']='COM y lub Equipo de Riego';$data['bool_creditor']=0;$data['parent_id']=1364;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-05';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52905-1';$data['description']='ALQUILERES';$data['bool_creditor']=0;$data['parent_id']=1366;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529051-01';$data['description']='Alquiler de tierras';$data['bool_creditor']=0;$data['parent_id']=1367;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529051-02';$data['description']='Alquiler de vehículos';$data['bool_creditor']=0;$data['parent_id']=1367;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-06';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52906-1';$data['description']='ENERGIA ELECTRICA';$data['bool_creditor']=0;$data['parent_id']=1370;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529061-01';$data['description']='Energía Eléctrica';$data['bool_creditor']=0;$data['parent_id']=1371;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-07';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52907-1';$data['description']='GASTOS LEGALES';$data['bool_creditor']=0;$data['parent_id']=1373;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529071-01';$data['description']='Honorarios';$data['bool_creditor']=0;$data['parent_id']=1374;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529071-02';$data['description']='Inscripciones Y liberacion de prendas';$data['bool_creditor']=0;$data['parent_id']=1374;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-08';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52908-1';$data['description']='FLETES Y ACARREO';$data['bool_creditor']=0;$data['parent_id']=1377;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529081-01';$data['description']='Pago de Transporte';$data['bool_creditor']=0;$data['parent_id']=1378;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-09';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52909-1';$data['description']='COSTOS DE COMERCIALIZACION';$data['bool_creditor']=0;$data['parent_id']=1380;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529091-01';$data['description']='Comision Bolsa Agricola';$data['bool_creditor']=0;$data['parent_id']=1381;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529091-02';$data['description']='Retencion Definitiva';$data['bool_creditor']=0;$data['parent_id']=1381;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529091-03';$data['description']='Aporte Asociacion';$data['bool_creditor']=0;$data['parent_id']=1381;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529091-04';$data['description']='Servicio de almacenaje, secado y limpieza';$data['bool_creditor']=0;$data['parent_id']=1381;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529-10';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1337;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='52910-1';$data['description']='OTROS COSTOS';$data['bool_creditor']=0;$data['parent_id']=1386;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-01';$data['description']='Servicios de Fumigacion Aereo';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-02';$data['description']='Capacitacion Tecnica';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-03';$data['description']='Incentivo de produccion';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-04';$data['description']='Comisión x acarreo de granos';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-05';$data['description']='Comisión x Alquiler Tierra';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-06';$data['description']='Viaticos de Alimentacion';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-07';$data['description']='Mantenimiento y repacaion camino';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-08';$data['description']='Sacos y otros empaques';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-09';$data['description']='Costos de taller';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='529101-10';$data['description']='Otros Costos';$data['bool_creditor']=0;$data['parent_id']=1387;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53';$data['description']='GATOS DEL TALLER';$data['bool_creditor']=0;$data['parent_id']=763;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531';$data['description']='GATOS DEL TALLER';$data['bool_creditor']=0;$data['parent_id']=1398;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531-01';$data['description']='SALARIO Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1399;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53101-1';$data['description']='SALARIO Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1400;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531011-01';$data['description']=' Salarios';$data['bool_creditor']=0;$data['parent_id']=1401;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531011-02';$data['description']='Alimentación';$data['bool_creditor']=0;$data['parent_id']=1401;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531011-03';$data['description']='Inss  Patronal';$data['bool_creditor']=0;$data['parent_id']=1401;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=1401;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=1401;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=1401;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531011-07';$data['description']='Indemnizaciones';$data['bool_creditor']=0;$data['parent_id']=1401;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1401;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531-02';$data['description']='MATERIALES Y SUMINISTRO';$data['bool_creditor']=0;$data['parent_id']=1399;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53102-1';$data['description']='MATERIALES Y SUMINISTRO';$data['bool_creditor']=0;$data['parent_id']=1410;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531021-01';$data['description']='Oxigeno';$data['bool_creditor']=0;$data['parent_id']=1411;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531021-02';$data['description']='Acetileno';$data['bool_creditor']=0;$data['parent_id']=1411;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531021-03';$data['description']='Materiales de Limpieza de taller';$data['bool_creditor']=0;$data['parent_id']=1411;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531021-04';$data['description']='Otros  Materiales';$data['bool_creditor']=0;$data['parent_id']=1411;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531-03';$data['description']='COMBUSTIBLE Y LUBRRICANTES';$data['bool_creditor']=0;$data['parent_id']=1399;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53103-1';$data['description']='COMBUSTIBLE Y LUBRRICANTES';$data['bool_creditor']=0;$data['parent_id']=1416;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531031-01';$data['description']='Combustible y Lub';$data['bool_creditor']=0;$data['parent_id']=1417;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531-04';$data['description']='MANTENIMIENTO Y REPARACION DE ACTIVOS';$data['bool_creditor']=0;$data['parent_id']=1399;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53104-1';$data['description']='MANTENIMIENTO Y REPARACION DE EDIFICIO ';$data['bool_creditor']=0;$data['parent_id']=1419;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531041-01';$data['description']='Mant y reparación de Edificio';$data['bool_creditor']=0;$data['parent_id']=1420;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53104-2';$data['description']='REPUESTOS ,MANTENIMIENTO Y REPARACION DE VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1419;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531042-01';$data['description']='Compra de Repuestos';$data['bool_creditor']=0;$data['parent_id']=1422;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531042-02';$data['description']='Compra de llantas';$data['bool_creditor']=0;$data['parent_id']=1422;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531042-03';$data['description']='Mantenimieto General';$data['bool_creditor']=0;$data['parent_id']=1422;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53104-3';$data['description']='MANT Y REP DE MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=1419;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531043-01';$data['description']='Mant y reparacion de Maq Agricola';$data['bool_creditor']=0;$data['parent_id']=1426;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531043-02';$data['description']='Mant y reparacion de impl agricolas';$data['bool_creditor']=0;$data['parent_id']=1426;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531043-03';$data['description']='Mant y reparacion de equipo de riego';$data['bool_creditor']=0;$data['parent_id']=1426;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53104-4';$data['description']='MANTENIMIENTO Y REPARACION DE MAQU Y EQUIPO DE TALLER';$data['bool_creditor']=0;$data['parent_id']=1419;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531044-01';$data['description']='Mantenimiento y reparacion  Maquinaria de taller';$data['bool_creditor']=0;$data['parent_id']=1430;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531044-02';$data['description']='Mantenimiento y reparacion de Equipos y Herramientas de taller';$data['bool_creditor']=0;$data['parent_id']=1430;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531-05';$data['description']='DEPRECIACION ACUMULADA';$data['bool_creditor']=0;$data['parent_id']=1399;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53105-1';$data['description']='DEPRECIACION EDIFICIO';$data['bool_creditor']=0;$data['parent_id']=1433;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531051-01';$data['description']='Depreciacion de Edificio';$data['bool_creditor']=0;$data['parent_id']=1434;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53105-2';$data['description']='DEPRECIACION DE VEHICULO PESADO';$data['bool_creditor']=0;$data['parent_id']=1433;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531052-01';$data['description']='Depreciacion de vehiculo';$data['bool_creditor']=0;$data['parent_id']=1436;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53105-3';$data['description']='DEP DE MAQ AGRICOLA';$data['bool_creditor']=0;$data['parent_id']=1433;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531053-01';$data['description']='Depreciacion de  Maquinaria Agricola';$data['bool_creditor']=0;$data['parent_id']=1438;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531053-02';$data['description']='Depreciacion de  Impl Agricola';$data['bool_creditor']=0;$data['parent_id']=1438;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531053-03';$data['description']='Depreciacion de Equipo de riego';$data['bool_creditor']=0;$data['parent_id']=1438;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531-06';$data['description']='SERVICIOS';$data['bool_creditor']=0;$data['parent_id']=1399;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53106-1';$data['description']='SERVICIOS BASICOS';$data['bool_creditor']=0;$data['parent_id']=1442;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531051-01';$data['description']='Energía Eléctrica';$data['bool_creditor']=0;$data['parent_id']=1443;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531051-02';$data['description']='Agua';$data['bool_creditor']=0;$data['parent_id']=1443;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531-06';$data['description']='OTROS GASTOS';$data['bool_creditor']=0;$data['parent_id']=1399;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='53106-1';$data['description']='OTROS GASTOS';$data['bool_creditor']=0;$data['parent_id']=1442;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531061-02';$data['description']='Herramientas y Accesorios';$data['bool_creditor']=0;$data['parent_id']=1443;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='531061-03';$data['description']='Otros Gastos';$data['bool_creditor']=0;$data['parent_id']=1443;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='54';$data['description']='GASTOS DE PRODUCCION  GANADERA';$data['bool_creditor']=0;$data['parent_id']=763;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541';$data['description']='GASTOS DE PRODUCCION  GANADERA';$data['bool_creditor']=0;$data['parent_id']=1450;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541-01';$data['description']='SALARIO Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1451;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='54101-1';$data['description']='SALARIO Y PRESTACIONES';$data['bool_creditor']=0;$data['parent_id']=1452;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541011-01';$data['description']=' Salarios';$data['bool_creditor']=0;$data['parent_id']=1453;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541011-02';$data['description']='Alimentación';$data['bool_creditor']=0;$data['parent_id']=1453;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541011-03';$data['description']='Inss patronal';$data['bool_creditor']=0;$data['parent_id']=1453;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541011-04';$data['description']='Inatec';$data['bool_creditor']=0;$data['parent_id']=1453;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541011-05';$data['description']='Vacaciones';$data['bool_creditor']=0;$data['parent_id']=1453;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541011-06';$data['description']='Treceavo mes';$data['bool_creditor']=0;$data['parent_id']=1453;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541011-07';$data['description']='indemnizaciones';$data['bool_creditor']=0;$data['parent_id']=1453;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541011-08';$data['description']='Otros';$data['bool_creditor']=0;$data['parent_id']=1453;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541-02';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=1451;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='54102-1';$data['description']='COMBUSTIBLE Y LUBRICANTES';$data['bool_creditor']=0;$data['parent_id']=1462;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541021-01';$data['description']='Combustible y Lubricantes';$data['bool_creditor']=0;$data['parent_id']=1463;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541-03';$data['description']='MANT Y REP DE EDIF Y VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1451;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='54103-1';$data['description']='MANT Y REP DE EDIF Y VEHICULO';$data['bool_creditor']=0;$data['parent_id']=1465;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541031-01';$data['description']='Mant y reparación de Edificio';$data['bool_creditor']=0;$data['parent_id']=1466;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541031-02';$data['description']='Mant y reparación  de Vehículo';$data['bool_creditor']=0;$data['parent_id']=1466;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541-04';$data['description']='OTROS GASTOS';$data['bool_creditor']=0;$data['parent_id']=1451;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='54104-1';$data['description']='OTROS GASTOS';$data['bool_creditor']=0;$data['parent_id']=1469;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541041-01';$data['description']='gastos diversos';$data['bool_creditor']=0;$data['parent_id']=1470;$this->AccountingCode->create();$this->AccountingCode->save($data);
		$data=array();$data['code']='541041-02';$data['description']='Transporte de Leche';$data['bool_creditor']=0;$data['parent_id']=1470;$this->AccountingCode->create();$this->AccountingCode->save($data);
		*/
	
	}

}
