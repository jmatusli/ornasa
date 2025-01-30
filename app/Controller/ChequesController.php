<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ChequesController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 

	public function getchequenumber(){
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";
		$bank_account_id=trim($_POST['bank_account_id']);
		
		$cheque=$this->Cheque->find('first',array(
			'conditions'=>array(
				'Cheque.bank_accounting_code_id'=>$bank_account_id,
			),
			'order'=>'cheque_code DESC'
		));
		//pr ($cheque);
		$newChequeCode="";
		if (!empty($cheque)){
			$newChequeCode=$cheque['Cheque']['cheque_code']+1;
		}
		return $newChequeCode;
	}  
	

	/*
	public function index_original() {
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
	
		$this->Cheque->recursive = 0;
		
		$chequeCount=$this->Cheque->find('count', array(
			'conditions' => array(
				'Cheque.cheque_date >='=> $startDate,
				'Cheque.cheque_date <'=> $endDatePlusOne,
			),
		));
		
		$this->Paginator->settings=array(
			'conditions' => array(
				'Cheque.cheque_date >='=> $startDate,
				'Cheque.cheque_date <'=> $endDatePlusOne,
			),
			'order'=>'cheque_date DESC, cheque_code DESC',
			'limit'=>($chequeCount!=0?$chequeCount:1)
		);
		$cheques=$this->Paginator->paginate('Cheque');
		$this->set(compact('cheques','startDate','endDate'));
	}
	*/
	
	public function index() {
		$bank_accounting_code_id=0;		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
			$bank_accounting_code_id=$this->request->data['Report']['bank_accounting_code_id'];
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
		
		$this->set(compact('startDate','endDate','bank_accounting_code_id'));
		
		$this->loadModel('AccountingCode');
		$this->Cheque->recursive = -1;
		$this->AccountingCode->recursive = -1;
		
		$bankAccountingCode=$this->Cheque->BankAccountingCode->find('first',array(
			'fields'=>array('BankAccountingCode.lft','BankAccountingCode.rght'),
			'conditions'=>array(
				'BankAccountingCode.id'=>ACCOUNTING_CODE_BANKS,
			),
		));
		$bankAccountingCodes = $this->Cheque->BankAccountingCode->find('list',array(
			'fields'=>'BankAccountingCode.description',
			'conditions'=>array(
				'BankAccountingCode.lft >'=>$bankAccountingCode['BankAccountingCode']['lft'],
				'BankAccountingCode.rght <'=>$bankAccountingCode['BankAccountingCode']['rght'],
				'BankAccountingCode.bool_main'=>'0',
			)
		));
		$this->set(compact('bankAccountingCodes'));
		$conditions=array(
			'BankAccountingCode.lft >'=>$bankAccountingCode['BankAccountingCode']['lft'],
			'BankAccountingCode.rght <'=>$bankAccountingCode['BankAccountingCode']['rght'],
			'BankAccountingCode.bool_main'=>'0',
		);
		if ($bank_accounting_code_id>0){
			$conditions[]=array('BankAccountingCode.id'=> $bank_accounting_code_id);
		}
		$selectedBankAccountingCodes=$this->Cheque->BankAccountingCode->find('all',array(
			'fields'=>array('BankAccountingCode.id','BankAccountingCode.description'),
			'conditions'=>$conditions,
		));
		//pr($selectedBankAccountingCodes);
		
		for ($i=0;$i<count($selectedBankAccountingCodes);$i++){
			$conditions=array(
				'Cheque.cheque_date >='=> $startDate,
				'Cheque.cheque_date <'=> $endDatePlusOne,
				'Cheque.bank_accounting_code_id'=> $selectedBankAccountingCodes[$i]['BankAccountingCode']['id'],
			);
			$chequeCount=$this->Cheque->find('count', array(
				'conditions' =>$conditions, 
			));
			
			$this->Paginator->settings=array(
				'conditions' => $conditions,
				'contain'=>array(
					'Currency',
					'AccountingRegister'=>array(
						'AccountingMovement'=>array(
							'AccountingCode',
						),
					),
				),
				'order'=>'cheque_date DESC, cheque_code DESC',
				'limit'=>($chequeCount!=0?$chequeCount:1)
			);
			$cheques=$this->Paginator->paginate('Cheque');
			$selectedBankAccountingCodes[$i]['Cheques']=$cheques;
		}
		//pr($selectedBankAccountingCodes);
		$this->set(compact('selectedBankAccountingCodes'));
		
		$adminAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_GASTOS_ADMIN,
			),
		));
		$this->set(compact('adminAccountingCode'));
		$salariesAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_SPENDING_OPERATIONS,
			),
		));
		$this->set(compact('salariesAccountingCode'));
		$salesAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_GASTOS_VENTA,
			),
		));
		$this->set(compact('salesAccountingCode'));
		$inventoryAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_INVENTORY,
			),
		));
		$this->set(compact('inventoryAccountingCode'));
		$productionAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_GASTOS_PRODUCCION,
			),
		));
		$this->set(compact('productionAccountingCode'));
		$financeAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft','AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_GASTOS_FINANCIEROS,
			),
		));
		$this->set(compact('financeAccountingCode'));
		
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

	public function guardarResumenCheques() {
		$exportData=$_SESSION['resumenCheques'];
		$this->set(compact('exportData'));
	}

	public function view($id = null) {
		if (!$this->Cheque->exists($id)) {
			throw new NotFoundException(__('Invalid cheque'));
		}
		$options = array('conditions' => array('Cheque.' . $this->Cheque->primaryKey => $id));
		$cheque= $this->Cheque->find('first', $options);
		$this->set(compact('cheque'));
		
		$this->loadModel('AccountingRegister');
		$accountingRegister=$this->AccountingRegister->find('first',array(
			'conditions'=>array(
				'AccountingRegister.id'=>$cheque['Cheque']['accounting_register_id'],
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
	
	public function viewPdf($id = null) {
		if (!$this->Cheque->exists($id)) {
			throw new NotFoundException(__('Invalid cheque'));
		}
		$options = array('conditions' => array('Cheque.' . $this->Cheque->primaryKey => $id));
		$cheque= $this->Cheque->find('first', $options);
		$this->set(compact('cheque'));
		
		$this->loadModel('AccountingRegister');
		$accountingRegister=$this->AccountingRegister->find('first',array(
			'conditions'=>array(
				'AccountingRegister.id'=>$cheque['Cheque']['accounting_register_id'],
			),
			'contain'=>array(
				'AccountingMovement'=>array(
					'AccountingCode',
				),
			)
		));
		$this->set(compact('accountingRegister'));
	}

	public function add() {
		
		if ($this->request->is('post')) {
			$this->loadModel('AccountingCode');
			$this->loadModel('AccountingRegister');
			$this->loadModel('ClosingDate');
			
			$cheque_date=$this->request->data['Cheque']['cheque_date'];
			$chequeDateAsString = $this->Cheque->deconstruct('cheque_date', $this->request->data['Cheque']['cheque_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
						
			$chequeDateArray=array();
			$chequeDateArray['year']=$cheque_date['year'];
			$chequeDateArray['month']=$cheque_date['month'];
			$chequeDateArray['day']=$cheque_date['day'];
					
			$cheque_code=$this->request->data['Cheque']['cheque_code'];
			$cheque_concept=$this->request->data['Cheque']['concept'];
			$cheque_observation=$this->request->data['Cheque']['observation'];
			$namedCheques=$this->Cheque->find('all',array(
				'conditions'=>array(
					'cheque_code'=>$cheque_code,
				)
			));
			//pr($this->request->data);
			$currency_id=$this->request->data['Cheque']['currency_id'];
			
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
				$this->Session->setFlash(__('La suma de DEBE y la suma de HABER no son iguales!  No se guardó el comprobante.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingDebitAccount){
				$this->Session->setFlash(__('Hay una cuenta de DEBE que no lleva la cuenta contable!  No se guardó el comprobante.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingCreditAccount){
				$this->Session->setFlash(__('Hay una cuenta de HABER que no lleva la cuenta contable!  No se guardó el comprobante.'), 'default',array('class' => 'error-message'));
			}
			elseif (count($namedCheques)>0){
				$this->Session->setFlash(__('Ya existe un cheque con el mismo código!  No se guardó el cheque.'), 'default',array('class' => 'error-message'));
			}
			else if ($chequeDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de cheque no puede estar en el futuro!  No se guardó el cheque.'), 'default',array('class' => 'error-message'));
			}
			elseif ($chequeDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			//else if ($this->request->data['Cheque']['bool_annulled']){
			//}
			else if ($this->request->data['Cheque']['bank_accounting_code_id']==0){
				//pr($this->request->data);
				$this->Session->setFlash(__('Se debe seleccionar la cuenta bancaria en un cheque!  No se guardó el cheque.'), 'default',array('class' => 'error-message'));
			}
			else {
				$datasource=$this->Cheque->getDataSource();
				//pr($this->request->data);
				
				try {
					$datasource->begin();
					$total_cheque=$this->request->data['Cheque']['amount'];
					$currency_id=$this->request->data['Cheque']['currency_id'];
					
					// first prepare the accounting registers
					// debit = cost_accounting_code_id selected
					// credit = bank_accounting_code_id selected
					// amount = total
					if ($currency_id==CURRENCY_USD){
						$this->loadModel('ExchangeRate');
						$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($chequeDateAsString);
						$total_CS=round($total_cheque*$applicableExchangeRate['ExchangeRate']['rate'],2);
					}
					else {
						$total_CS=$total_cheque;
					}
					
					$accountingRegisterData['AccountingRegister']['register_date']=$chequeDateArray;
					$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
					$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
					$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
					$accountingRegisterData['AccountingRegister']['amount']=$total_CS;
					$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
					$accountingRegisterData['AccountingRegister']['concept']="Cheque ".$cheque_code."_".$cheque_concept;
					$accountingRegisterData['AccountingRegister']['observation']="Cheque ".$cheque_code."_".$cheque_observation;
					
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
					
					$this->request->data['Cheque']['accounting_register_id']=$accounting_register_id;
					$this->request->data['Cheque']['cheque_type_id']=2;
					$this->Cheque->create();
					if (!$this->Cheque->save($this->request->data)) {
						echo "Problema guardando el cheque";
						pr($this->validateErrors($this->Cheque));
						throw new Exception();
					}
					$cheque_id=$this->Cheque->id;
					
					$datasource->commit();
					$this->recordUserAction($this->Cheque->id,null,null);
					// SAVE THE USERLOG FOR THE CHEQUE
					$this->recordUserActivity($this->Session->read('User.username'),"Cheque registrado con número ".$this->request->data['Cheque']['cheque_code']);
					$this->Session->setFlash(__('Se guardó el cheque.'),'default',array('class' => 'success'));
					return $this->redirect(array('action' => 'index'));
				}
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podía guardar el cheque.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
				}
				
			}			
		}
		$currencies = $this->Cheque->Currency->find('list');
		$bankAccountingCode=$this->Cheque->BankAccountingCode->find('first',array(
			'fields'=>array('BankAccountingCode.lft','BankAccountingCode.rght'),
			'conditions'=>array(
				'BankAccountingCode.id'=>ACCOUNTING_CODE_BANKS,
			),
		));
		$bankAccountingCode=$this->Cheque->BankAccountingCode->find('first',array(
			'fields'=>array('BankAccountingCode.lft','BankAccountingCode.rght'),
			'conditions'=>array(
				'BankAccountingCode.id'=>ACCOUNTING_CODE_BANKS,
			),
		));
		$bankAccountingCodes = $this->Cheque->BankAccountingCode->find('list',array(
			'fields'=>'BankAccountingCode.fullname',
			'conditions'=>array(
				'BankAccountingCode.lft >'=>$bankAccountingCode['BankAccountingCode']['lft'],
				'BankAccountingCode.rght <'=>$bankAccountingCode['BankAccountingCode']['rght'],
				'BankAccountingCode.bool_main'=>'0',
			)
		));
		
		$accountingRegisters = $this->Cheque->AccountingRegister->find('list');
		
		//$chequeTypes = $this->Cheque->ChequeType->find('list');
		
		$this->set(compact('currencies', 'bankAccountingCodes', 'accountingRegisters'));
		$this->loadModel('AccountingCode');
		$costAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_COSTS,
			),
		));
		$spendingAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_SPENDING_OPERATIONS,
			),
		));
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>array('AccountingCode.id','AccountingCode.shortfullname'),
			'conditions'=>array(
				//'AccountingCode.lft >'=>$costAccountingCode['AccountingCode']['lft'],
				//'AccountingCode.rght <'=>$spendingAccountingCode['AccountingCode']['rght'],
				'AccountingCode.bool_main'=>false
			),
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

	public function edit($id = null) {
		if (!$this->Cheque->exists($id)) {
			throw new NotFoundException(__('Invalid cheque'));
		}
		
		$thisCheque=$this->Cheque->read(null,$id);
		//pr($thisCheque);
		$this->loadModel('AccountingRegister');
		$debitMovementsAlreadyInAccountingRegister=$this->AccountingRegister->AccountingMovement->find('all',array(
			'fields'=>array('AccountingMovement.id','AccountingMovement.accounting_code_id','AccountingMovement.amount','AccountingMovement.concept'),
			'conditions'=>array(
				'AccountingMovement.accounting_register_id'=>$thisCheque['AccountingRegister']['id'],
				'AccountingMovement.amount >'=>'0',
				'AccountingMovement.bool_debit'=>true,
			),
		));
		
		$creditMovementsAlreadyInAccountingRegister=$this->AccountingRegister->AccountingMovement->find('all',array(
			'fields'=>array('AccountingMovement.id','AccountingMovement.accounting_code_id','AccountingMovement.amount','AccountingMovement.concept'),
			'conditions'=>array(
				'AccountingMovement.accounting_register_id'=>$thisCheque['AccountingRegister']['id'],
				'AccountingMovement.amount >'=>'0',
				'AccountingMovement.bool_debit'=>'0',
			),
		));
		
		$this->loadModel('ExchangeRate');
		$appliedExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($thisCheque['Cheque']['cheque_date']);
		$this->set(compact('appliedExchangeRate'));
		
		$bool_first_load=true;
		if ($this->request->is(array('post', 'put'))) {
			$bool_first_load='0';
			$this->loadModel('AccountingCode');
			$this->loadModel('AccountingRegister');
			$this->loadModel('AccountingMovement');
			$this->loadModel('ClosingDate');
			
			$cheque_date=$this->request->data['Cheque']['cheque_date'];
			$chequeDateAsString = $this->Cheque->deconstruct('cheque_date', $this->request->data['Cheque']['cheque_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
						
			$chequeDateArray=array();
			$chequeDateArray['year']=$cheque_date['year'];
			$chequeDateArray['month']=$cheque_date['month'];
			$chequeDateArray['day']=$cheque_date['day'];
					
			$cheque_code=$this->request->data['Cheque']['cheque_code'];
			$cheque_concept=$this->request->data['Cheque']['concept'];
			$cheque_observation=$this->request->data['Cheque']['observation'];
			
			$namedCheques=$this->Cheque->find('all',array(
				'conditions'=>array(
					'cheque_code'=>$cheque_code,
					'Cheque.id !='=>$id,
				)
			));
			$total_cheque=$this->request->data['Cheque']['amount'];
			$currency_id=$this->request->data['Cheque']['currency_id'];
			
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
				$this->Session->setFlash(__('La suma de DEBE y la suma de HABER no son iguales!  No se guardó el comprobante.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingDebitAccount){
				$this->Session->setFlash(__('Hay una cuenta de DEBE que no lleva la cuenta contable!  No se guardó el comprobante.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingCreditAccount){
				$this->Session->setFlash(__('Hay una cuenta de HABER que no lleva la cuenta contable!  No se guardó el comprobante.'), 'default',array('class' => 'error-message'));
			}
			elseif (count($namedCheques)>0){
				$this->Session->setFlash(__('Ya existe un cheque con el mismo código!  No se guardó el cheque.'), 'default',array('class' => 'error-message'));
			}
			else if ($chequeDateAsString>date('Y-m-d 23:59:59')){
				$this->Session->setFlash(__('La fecha de cheque no puede estar en el futuro!  No se guardó el cheque.'), 'default',array('class' => 'error-message'));
			}
			elseif ($chequeDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			else {
				$datasource=$this->Cheque->getDataSource();
				//pr($this->request->data);
				
				try {
					$datasource->begin();
					$removedOK='0';
					$oldAccountingRegisterCode="";
					// first bring back everything to original state
					$oldCheque=$this->Cheque->find('first',array(
						'conditions'=>array(
							'Cheque.id'=>$id,
						)
					));
					
					//MODIFIED 20160310 , ONE CHEQUE ONLY HAS ONE ACCOUNTING REGISTER
					$oldAccountingRegister=$this->AccountingRegister->find('first',array(
						'conditions'=>array('AccountingRegister.id'=>$oldCheque['Cheque']['accounting_register_id'])
					));
					
					if (!empty($oldAccountingRegister)){	
						if (!empty($oldAccountingRegister)){
							$oldAccountingRegisterCode=$oldAccountingRegister['AccountingRegister']['register_code'];
						}
							
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
					// MODIFIED 201560310 CHEQUE SHOULD NOT BE REMOVED
					// then remove the cheque
					// $this->Cheque->delete($oldCheque['Cheque']['id']);						
					
					$removedOK=true;
					
					$datasource->commit();
					$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el cheque con código ".$this->request->data['Cheque']['cheque_code']);
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
							$applicableExchangeRate=$this->ExchangeRate->getApplicableExchangeRate($chequeDateAsString);
							$total_CS=round($total_cheque*$applicableExchangeRate['ExchangeRate']['rate'],2);
						}
						else {
							$total_CS=$total_cheque;
						}
						
						if (!empty($oldAccountingRegisterCode)){
							$registerCode=$oldAccountingRegisterCode;
						}
						else {
							$registerCode=$this->AccountingRegister->getregistercode(ACCOUNTING_REGISTER_TYPE_CP);
						}
						
						$accountingRegisterData['AccountingRegister']['register_date']=$chequeDateArray;
						$accountingRegisterData['AccountingRegister']['accounting_register_type_id']=ACCOUNTING_REGISTER_TYPE_CP;
						
						$accountingRegisterData['AccountingRegister']['register_code']=$registerCode;
						$accountingRegisterData['AccountingRegister']['amount']=$total_CS;
						$accountingRegisterData['AccountingRegister']['currency_id']=CURRENCY_CS;
						$accountingRegisterData['AccountingRegister']['concept']="Cheque ".$cheque_code."_".$cheque_concept;
						$accountingRegisterData['AccountingRegister']['observation']="Cheque ".$cheque_code."_".$cheque_observation;
						
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
						
						$this->request->data['Cheque']['accounting_register_id']=$accounting_register_id;
						$this->request->data['Cheque']['cheque_type_id']=2;
						$this->Cheque->id=$id;
						if (!$this->Cheque->save($this->request->data)) {
							echo "Problema guardando el cheque";
							pr($this->validateErrors($this->Cheque));
							throw new Exception();
						}
						$cheque_id=$this->Cheque->id;
						
						$datasource->commit();
						$this->recordUserAction();
						// SAVE THE USERLOG FOR THE CHEQUE
						$this->recordUserActivity($this->Session->read('User.username'),"Cheque registrado con número ".$this->request->data['Cheque']['cheque_code']);
						$this->Session->setFlash(__('Se guardó el cheque.'),'default',array('class' => 'success'));
						return $this->redirect(array('action' => 'index'));
					}
					catch(Exception $e){
						$datasource->rollback();
						pr($e);
						$this->Session->setFlash(__('No se podía guardar el cheque.  Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
					}
				}			
			}
			
		} 
		else {
			$options = array('conditions' => array('Cheque.' . $this->Cheque->primaryKey => $id));
			$this->request->data = $this->Cheque->find('first', $options);
		}
		
		
		$currencies = $this->Cheque->Currency->find('list');
		$bankAccountingCode=$this->Cheque->BankAccountingCode->find('first',array(
			'fields'=>array('BankAccountingCode.lft','BankAccountingCode.rght'),
			'conditions'=>array(
				'BankAccountingCode.id'=>ACCOUNTING_CODE_BANKS,
			),
		));
		$bankAccountingCodes = $this->Cheque->BankAccountingCode->find('list',array(
			'fields'=>'BankAccountingCode.fullname',
			'conditions'=>array(
				'BankAccountingCode.lft >'=>$bankAccountingCode['BankAccountingCode']['lft'],
				'BankAccountingCode.rght <'=>$bankAccountingCode['BankAccountingCode']['rght'],
			)
		));
		
		$accountingRegisters = $this->Cheque->AccountingRegister->find('list');
		//$chequeTypes = $this->Cheque->ChequeType->find('list');
		$this->set(compact('currencies', 'bankAccountingCodes','accountingRegisters'));
		$this->loadModel('AccountingCode');
		$costAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.lft'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_COSTS,
			),
		));
		$spendingAccountingCode=$this->AccountingCode->find('first',array(
			'fields'=>array('AccountingCode.rght'),
			'conditions'=>array(
				'AccountingCode.id'=>ACCOUNTING_CODE_SPENDING_OPERATIONS,
			),
		));
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>array('AccountingCode.id','AccountingCode.shortfullname'),
			'conditions'=>array(
				//'AccountingCode.lft >'=>$costAccountingCode['AccountingCode']['lft'],
				//'AccountingCode.rght <'=>$spendingAccountingCode['AccountingCode']['rght'],
				'AccountingCode.bool_main'=>false
			),
			'order'=>'AccountingCode.lft ASC'
		));
		$this->set(compact('accountingCodes','debitMovementsAlreadyInAccountingRegister','creditMovementsAlreadyInAccountingRegister'));
		
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
		$this->Cheque->id = $id;
		if (!$this->Cheque->exists()) {
			throw new NotFoundException(__('Invalid cheque'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$this->loadModel('AccountingCode');
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingMovement');
		
		try {
			$datasource=$this->Cheque->getDataSource();
			$datasource->begin();
			
			// first bring back everything to original state
			$oldCheque=$this->Cheque->find('first',array(
				'conditions'=>array(
					'Cheque.id'=>$id,
				)
			));
			$oldAccountingRegister=$this->AccountingRegister->find('first',array(
				'conditions'=>array('AccountingRegister.id'=>$oldCheque['Cheque']['accounting_register_id'])
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
			}
			// then remove the cash receipt
			$this->Cheque->delete($oldCheque['Cheque']['id']);						
			
			$datasource->commit();
			$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el cheque con código ".$oldCheque['Cheque']['cheque_code']);
			$this->Session->setFlash(__('The cheque has been deleted.'),'default',array('class' => 'success'));
		}
		catch(Exception $e){
			$datasource->rollback();
			pr($e);
			$this->Session->setFlash(__('The cheque could not be deleted. Please, try again.'), 'default',array('class' => 'error-message'));
		}				
				
		return $this->redirect(array('action' => 'index'));
	}
}
