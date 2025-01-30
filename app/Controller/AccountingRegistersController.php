<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class AccountingRegistersController extends AppController {


	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel'); 
	
	public function getaccountingregistercode(){
		$this->layout="ajax";
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		
		$accountingregistertypeid=trim($_POST['accountingregistertypeid']);
		
		$newAccountingRegisterCode=$this->AccountingRegister->getregistercode($accountingregistertypeid);
		//REPLACED BY MODEL FUNCTION 20160309	
		/*
		$this->loadModel('AccountingRegisterType');
		$accountingRegisterType=$this->AccountingRegisterType->find('first',array(
			'conditions'=>array('AccountingRegisterType.id'=>$accountingregistertypeid),
		));
		$typePrefix=$accountingRegisterType['AccountingRegisterType']['abbreviation']."_";
	
		$prefixLength=strlen($typePrefix);
		
		$newAccountingRegisterCode="";
		$lastAccountingRegister = $this->AccountingRegister->find('first',array(
			'conditions'=>array(
				'AccountingRegister.accounting_register_type_id'=>$accountingregistertypeid,
			),
			'order' => array('AccountingRegister.register_code' => 'desc'),
		));
		//pr($lastAccountingRegister);
		if (!empty($lastAccountingRegister) && $lastAccountingRegister['AccountingRegister']['register_code']!=0){
			$newAccountingRegisterCode = substr($lastAccountingRegister['AccountingRegister']['register_code'],$prefixLength)+1;
			//pr($newAccountingRegisterCode);
			$newAccountingRegisterCode=$typePrefix.str_pad($newAccountingRegisterCode,5,'0',STR_PAD_LEFT);
		}
		else {
			$newAccountingRegisterCode=$typePrefix."00001";
		}
		*/
		return $newAccountingRegisterCode;
	}
 
	public function cuadrarAccountingRegisters(){
		$accountingRegisters=$this->AccountingRegister->find('all',array(
			'fields'=>array(
				//'AccountingRegister.id','AccountingRegister.register_date','AccountingRegister.register_code','AccountingRegister.concept','AccountingRegister.amount','AccountingRegister.bool_annulled',
				'AccountingRegister.id','AccountingRegister.register_date','AccountingRegister.register_code','AccountingRegister.concept','AccountingRegister.amount',
			),
			'contain'=>array(
				'AccountingMovement'
			)
		));
		$this->set(compact('accountingRegisters'));
	}
	
	public function guardarResumenAsientosContablesProblemas() {
		$exportData=$_SESSION['resumenAsientosContablesProblemas'];
		$this->set(compact('exportData'));
	}
	
	public function index() {
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
		
		$this->AccountingRegister->recursive = 0;
		
		$accountingRegisterCount=$this->AccountingRegister->find('count', array(
			'conditions' => array(
				'AccountingRegister.register_date >='=> $startDate,
				'AccountingRegister.register_date <'=> $endDatePlusOne,
			),
		));
		
		$this->Paginator->settings=array(
			'conditions' => array(
				'AccountingRegister.register_date >='=> $startDate,
				'AccountingRegister.register_date <'=> $endDatePlusOne,
			),
			'order'=>'register_date DESC, register_code DESC',
			'limit'=>($accountingRegisterCount!=0?$accountingRegisterCount:1)
		);
		$accountingRegisters=$this->Paginator->paginate('AccountingRegister');
		$this->set(compact('accountingRegisters','startDate','endDate'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->AccountingRegister->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register'));
		}
		$this->AccountingRegister->recursive = 2;
		
		$options = array('conditions' => array('AccountingRegister.' . $this->AccountingRegister->primaryKey => $id));
		$accountingRegister=$this->AccountingRegister->find('first', $options);
		$filename='Asiento Contable_'.$accountingRegister['AccountingRegister']['concept'];
		$this->set(compact('accountingRegister','filename'));
	}
	
	public function viewPdf($id = null) {
		if (!$this->AccountingRegister->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register'));
		}
		
		$options = array('conditions' => array('AccountingRegister.' . $this->AccountingRegister->primaryKey => $id));
		$this->AccountingRegister->recursive = 2;
		$accountingRegister=$this->AccountingRegister->find('first', $options);
		$filename='Asiento Contable_'.$accountingRegister['AccountingRegister']['concept'];
	
		$this->set(compact('accountingRegister','filename'));
	}

	public function getaccountingcodedescription($id){
	    $this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->loadModel('AccountingCode');
		if (!$id){
			throw new NotFoundException(__('Accounting Code id not present'));
		}
		if (!$this->AccountingCode->exists($id)) {
			throw new NotFoundException(__('Invalid accounting code'));
		}
		
		$accountingCode=$this->AccountingCode->find('first',array('conditions'=>array('AccountingCode.id'=>$id)));
		return $accountingCode['AccountingCode']['description'];
	}
	
/**
 * add method
 *
 * @return void
 */
	public function add() {
		$this->loadModel('Currency');
		$this->loadModel('ClosingDate');
		
		if ($this->request->is('post')) {
			// sum all the debe and haber
			//pr($this->request->data);
			
			$registerDateAsString=$this->AccountingRegister->deconstruct('register_date',$this->request->data['AccountingRegister']['register_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
			
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
			elseif ($registerDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			else {
				if ($this->saveAccountingRegisterData($this->request->data,true)){
					$this->recordUserAction($this->AccountingRegister->id,null,null);
					return $this->redirect(array('action' => 'index'));
				}
				else {
					$this->Session->setFlash(__('No se podía guardar el asiento contable. Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
				}
			}
			
			/*
			try {
				$datasource=$this->AccountingRegister->getDataSource();
				$datasource->begin();
				$this->AccountingRegister->create();
				
				if (!$this->AccountingRegister->save($this->request->data)) {
					pr($this->validateErrors($this->AccountingRegister));
					echo "Error al guardar el registro contable";
					throw new Exception();
				}
				$accounting_register_id=$this->AccountingRegister->id;
				$accounting_register_concept=$this->request->data['AccountingRegister']['concept'];
				$accounting_register_date=$this->request->data['AccountingRegister']['register_date'];
				
				$accounting_register_currency_id=$this->request->data['AccountingRegister']['currency_id'];
				$linkedCurrency=$this->Currency->read(null,$accounting_register_currency_id);
				$currency_abbreviation=$linkedCurrency['Currency']['abbreviation'];
				
				foreach ($this->request->data['AccountingMovement'] as $accountingMovement){
					//pr($accountingMovement);
					$accounting_movement_amount=0;
					$bool_debit=true;
					
					if (!empty($accountingMovement['debit_amount'])){
						$accounting_movement_amount = round($accountingMovement['debit_amount'],2);
						$bool_debit=true;
					}
					else if (!empty($accountingMovement['credit_amount'])){
						$accounting_movement_amount = round($accountingMovement['credit_amount'],2);
						$bool_debit='0';
					}
					
					$accounting_movement_code_id = $accountingMovement['accounting_code_id'];
					
					if ($accounting_movement_code_id>0 && $accounting_movement_amount>0){
						$accounting_movement_code_description = $accountingMovement['accounting_code_description'];
						
						$logmessage="Registro de cuenta contable ".$accounting_movement_code_description." (Monto:".$accounting_movement_amount." ".$currency_abbreviation.") para Registro Contable ".$accounting_register_concept;
						
						// SAVE PURCHASE ORDER PRODUCT
						$AccountingMovementItemData['accounting_register_id']=$accounting_register_id;
						$AccountingMovementItemData['accounting_code_id']=$accounting_movement_code_id;
						$AccountingMovementItemData['amount']=$accounting_movement_amount;
						$AccountingMovementItemData['currency_id']=$accounting_register_currency_id;
						
						$AccountingMovementItemData['bool_debit']=$bool_debit;
						//echo "saved item data";
						//pr($AccountingMovementItemData);
						$this->AccountingRegister->AccountingMovement->create();
						if (!$this->AccountingRegister->AccountingMovement->save($AccountingMovementItemData)) {
							pr($this->validateErrors($this->AccountingMovement));
							echo "problema al guardar el movimiento contable";
							throw new Exception();
						}
						
						// SAVE THE USERLOG FOR THE PURCHASE ORDER ITEM
						$this->recordUserActivity($this->Session->read('User.username'),$logmessage);
					}
				}			
				$datasource->commit();
				$this->Session->setFlash(__('The accounting register has been saved.'),'default',array('class' => 'success'));
				//return $this->redirect(array('action' => 'index'));
			}
			catch(Exception $e){
				$datasource->rollback();
				$this->Session->setFlash(__('The accounting register could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
			}
			*/
		}
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		$this->loadModel('AccountingRegisterType');
		$this->loadModel('Order');
		
		$currencies=$this->Currency->find('list');
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>array('AccountingCode.id','AccountingCode.shortfullname'),
			'conditions'=>array('AccountingCode.bool_main'=>false),
			'order'=>'AccountingCode.lft ASC'
		));
		
		$accountingRegisterTypes=$this->AccountingRegisterType->find('list');
		
		$sales=$this->Order->find('list');
		
		$this->set(compact('currencies','accountingCodes','accountingRegisterTypes','sales'));
		
		$this->loadModel('Invoice');
		$invoices=$this->Invoice->find('list');
		$this->set(compact('invoices'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->AccountingRegister->exists($id)) {
			throw new NotFoundException(__('Invalid accounting register'));
		}
		
		$debitMovementsAlreadyInAccountingRegister=$this->AccountingRegister->AccountingMovement->find('all',array(
			//'fields'=>array('AccountingMovement.id','AccountingMovement.accounting_code_id','AccountingMovement.amount','AccountingMovement.invoice_id','AccountingMovement.fuel_order_id','AccountingMovement.section_biweekly_id'),
			'fields'=>array('AccountingMovement.id','AccountingMovement.accounting_code_id','AccountingMovement.amount','AccountingMovement.concept'),
			'conditions'=>array(
				'AccountingMovement.accounting_register_id'=>$id,
				'AccountingMovement.amount >'=>'0',
				'AccountingMovement.bool_debit'=>true,
			),
		));
		
		$creditMovementsAlreadyInAccountingRegister=$this->AccountingRegister->AccountingMovement->find('all',array(
			//'fields'=>array('AccountingMovement.id','AccountingMovement.accounting_code_id','AccountingMovement.amount','AccountingMovement.invoice_id','AccountingMovement.fuel_order_id','AccountingMovement.section_biweekly_id'),
			'fields'=>array('AccountingMovement.id','AccountingMovement.accounting_code_id','AccountingMovement.amount','AccountingMovement.concept'),
			'conditions'=>array(
				'AccountingMovement.accounting_register_id'=>$id,
				'AccountingMovement.amount >'=>'0',
				'AccountingMovement.bool_debit'=>'0',
			),
		));
		
		if ($this->request->is(array('post', 'put'))) {
			// sum all the debe and haber
			//pr($this->request->data);
			
			
			$this->loadModel('ClosingDate');
			$registerDateAsString=$this->AccountingRegister->deconstruct('register_date',$this->request->data['AccountingRegister']['register_date']);
			$latestClosingDate=$this->ClosingDate->getLatestClosingDate();
			$latestClosingDatePlusOne=date("Y-m-d",strtotime($latestClosingDate."+1 days"));
			$closingDate=new DateTime($latestClosingDate);
			
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
			//echo $sumDebit."<br/>";
			//echo $sumCredit."<br/>";
			//echo ($sumDebit-$sumCredit)."<br/>";
			if (($sumDebit-$sumCredit)>0.009){
				$this->Session->setFlash(__('La suma de DEBE y la suma de HABER no son iguales!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif ($registerDateAsString<$latestClosingDatePlusOne){
				$this->Session->setFlash(__('La última fecha de cierre es '.$closingDate->format('d-m-Y').'!  No se pueden realizar cambios.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingDebitAccount){
				$this->Session->setFlash(__('Hay una cuenta de DEBE que no lleva la cuenta contable!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			elseif ($boolMissingCreditAccount){
				$this->Session->setFlash(__('Hay una cuenta de HABER que no lleva la cuenta contable!  No se guardó el asiento contable.'), 'default',array('class' => 'error-message'));
			}
			else {
				foreach ($debitMovementsAlreadyInAccountingRegister as $debitMovement){
					$this->AccountingRegister->AccountingMovement->delete($debitMovement['AccountingMovement']['id']);
				}
				foreach ($creditMovementsAlreadyInAccountingRegister as $creditMovement){
					$this->AccountingRegister->AccountingMovement->delete($creditMovement['AccountingMovement']['id']);
				}
				if ($this->saveAccountingRegisterData($this->request->data,false)){
					$this->recordUserAction();
					return $this->redirect(array('action' => 'index'));
				}
				else {
					$this->Session->setFlash(__('No se podía guardar el asiento contable. Por favor intente de nuevo.'), 'default',array('class' => 'error-message'));
				}
			}
		} 
		else {
			$options = array('conditions' => array('AccountingRegister.' . $this->AccountingRegister->primaryKey => $id));
			$this->request->data = $this->AccountingRegister->find('first', $options);
		}
		
		$this->loadModel('Currency');
		$this->loadModel('AccountingCode');
		
		$this->loadModel('Order');
		
		$this->loadModel('AccountingRegisterType');
		
		$currencies=$this->Currency->find('list');
		
		$accountingCodes=$this->AccountingCode->find('list',array(
			'fields'=>array('AccountingCode.id','AccountingCode.shortfullname'),
			'conditions'=>array('AccountingCode.bool_main'=>false),
			'order'=>'AccountingCode.lft ASC'
		));
		
		$sales=$this->Order->find('list');
		
		$accountingRegisterTypes=$this->AccountingRegisterType->find('list');
		
		$this->set(compact('currencies','accountingCodes','debitMovementsAlreadyInAccountingRegister','creditMovementsAlreadyInAccountingRegister','sales','id','accountingRegisterTypes'));
		
		$this->loadModel('Invoice');
		$invoices=$this->Invoice->find('list');
		$this->set(compact('invoices'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->AccountingRegister->id = $id;
		if (!$this->AccountingRegister->exists()) {
			throw new NotFoundException(__('Invalid accounting register'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->AccountingRegister->delete()) {
			$this->Session->setFlash(__('The accounting register has been deleted.'));
		} else {
			$this->Session->setFlash(__('The accounting register could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
	
	public function verEstadoResultados(){
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
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$results=$this->calculateResultState($startDate,$endDatePlusOne,true);
		//pr($results);
		
		// get list of all accounting codes for selector
		$accountingCodes=$this->AccountingCode->find('list');
		
		$this->set(compact('startDate','endDate','accountingCodes','statusFlows','results'));	
	}
	
	
	public function guardarEstadoResultados() {
		$exportData=$_SESSION['reporteEstadoResultados'];
		$this->set(compact('exportData'));
	}
	
	public function calculateResultState($startDate,$endDatePlusOne,$bool_detail){
		//first get costos de venta from orders
		$this->loadModel('ProductType');
		$this->loadModel('Product');
		$this->loadModel('StockItem');
		$this->ProductType->recursive=-1;
		$this->Product->recursive=-1;
		
		$costosVentaInitial=0;
		$costosVentaPeriod=0;
		$producedProductTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array('ProductType.product_category_id'=> CATEGORY_PRODUCED),
		));
		//pr($producedProductTypes);
		$allProducedMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id',
			'conditions' => array('Product.product_type_id'=> $producedProductTypes),
			'contain'=>array('StockMovement'),
		));
		
		foreach ($allProducedMaterials as $producedMaterial){
			$productTotalValueCostInitial=0;
			$productTotalValueCostPeriod=0;
			
			foreach ($producedMaterial['StockMovement'] as $stockMovement){
				//pr($stockMovement);
				if (!$stockMovement['bool_input']){
					if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							$linkedStockItem=$this->StockItem->read(null,$stockMovement['stockitem_id']);
							$productTotalValueCostPeriod+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
						}
					}
					elseif ($stockMovement['movement_date'] < $startDate){
						if (!$stockMovement['bool_reclassification']){
							$linkedStockItem=$this->StockItem->read(null,$stockMovement['stockitem_id']);
							$productTotalValueCostInitial+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
						}
					}
				} 
			}
			$costosVentaInitial+=$productTotalValueCostInitial;
			$costosVentaPeriod+=$productTotalValueCostPeriod;
		}
		
		$otherProductTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array(
				'ProductType.product_category_id'=> CATEGORY_OTHER,
			),
		));
		//pr($otherProductTypes);
		$allOtherMaterials=$this->Product->find('all',array(
			'fields'=>'Product.id',
			'conditions' => array('Product.product_type_id'=> $otherProductTypes),
			'contain'=>array('StockMovement'),
		));
		
		$i=0;
		foreach ($allOtherMaterials as $otherMaterial){
			$productTotalValueCostInitial=0;
			$productTotalValueCostPeriod=0;
			
			foreach ($otherMaterial['StockMovement'] as $stockMovement){
				if (!$stockMovement['bool_input']){
					if ($stockMovement['movement_date'] >= $startDate && $stockMovement['movement_date'] <= $endDatePlusOne){
						if (!$stockMovement['bool_reclassification']){
							$linkedStockItem=$this->StockItem->read(null,$stockMovement['stockitem_id']);
							$productTotalValueCostPeriod+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
						}
					}
					elseif ($stockMovement['movement_date'] < $startDate){
						if (!$stockMovement['bool_reclassification']){
							$linkedStockItem=$this->StockItem->read(null,$stockMovement['stockitem_id']);
							$productTotalValueCostInitial+=$stockMovement['product_quantity']*$linkedStockItem['StockItem']['product_unit_price'];
						}
					}
				} 
			}
			$costosVentaInitial+=$productTotalValueCostInitial;
			$costosVentaPeriod+=$productTotalValueCostPeriod;
		}
		//echo $costosVentaInitial."<br/>";
		//echo $costosVentaPeriod."<br/>";
		
		$this->loadModel('AccountingRegister');
		$this->loadModel('AccountingCode');
		$this->AccountingCode->recursive=-1;
		$this->AccountingRegister->AccountingMovement->virtualFields['total']=0;
		$statusAccountingCodes=array(
			ACCOUNTING_CODE_INGRESOS_VENTA_MAYOR,
			ACCOUNTING_CODE_INGRESOS_DESCUENTOS,
			ACCOUNTING_CODE_INGRESOS_OTROS,
			
			ACCOUNTING_CODE_COSTOS_VENTA,
			
			ACCOUNTING_CODE_GASTOS_ADMIN,
			ACCOUNTING_CODE_GASTOS_VENTA,
			ACCOUNTING_CODE_GASTOS_FINANCIEROS,
			ACCOUNTING_CODE_GASTOS_PRODUCCION,
			ACCOUNTING_CODE_GASTOS_OTROS
		);
		
		$statusFlows=array();
		$results=array();
		
		for ($i=0;$i<count($statusAccountingCodes);$i++){
			$accountingCode=$this->AccountingCode->read(null,$statusAccountingCodes[$i]);
			$statusFlows[$i]['accounting_code_id']=$statusAccountingCodes[$i];
			$statusFlows[$i]['accounting_code_code']=$accountingCode['AccountingCode']['code'];
			$statusFlows[$i]['accounting_code_description']=$accountingCode['AccountingCode']['description'];
			
			$statusFlows[$i]['current_total']=0;
			
			if ($statusAccountingCodes[$i]==ACCOUNTING_CODE_COSTOS_VENTA){
				$statusFlows[$i]['current_total']=$costosVentaPeriod;							
			}
			$descendentcodeids=$this->AccountingCode->find('list',array(
				'fields' => array('AccountingCode.id'),
				'conditions' => array(
					'AccountingCode.lft BETWEEN ? AND ?' => array($accountingCode['AccountingCode']['lft']+1, $accountingCode['AccountingCode']['rght']-1),
				)
			));
			$currentAccountingRegisters=$this->AccountingRegister->AccountingMovement->find('all',array(
				'fields'=>array('SUM(AccountingMovement.amount) as AccountingMovement__total','AccountingMovement.currency_id','AccountingMovement.bool_debit'),
				'conditions'=>array(
					'AccountingMovement.accounting_code_id'=>$descendentcodeids,
					'AccountingRegister.register_date >='=>$startDate,
					'AccountingRegister.register_date <'=>$endDatePlusOne,
				),
				'group'=>'bool_debit',
			));
			if (!empty($currentAccountingRegisters)){
				foreach ($currentAccountingRegisters as $currentAccountingRegister){
					if ($accountingCode['AccountingCode']['bool_creditor']){
						if ($currentAccountingRegister['AccountingMovement']['bool_debit']){
							$statusFlows[$i]['current_total']-=$currentAccountingRegister['AccountingMovement']['total'];
						}
						else {
							$statusFlows[$i]['current_total']+=$currentAccountingRegister['AccountingMovement']['total'];
						}
					}
					else{
						if ($currentAccountingRegister['AccountingMovement']['bool_debit']){
							$statusFlows[$i]['current_total']+=$currentAccountingRegister['AccountingMovement']['total'];
						}
						else {
							$statusFlows[$i]['current_total']-=$currentAccountingRegister['AccountingMovement']['total'];
						}
					}
				}
			}
			
			$secondaryFlows=array();
			if ($bool_detail){
				$secondaryAccountingCodes=$this->AccountingCode->find('all',array(
					'conditions'=>array(
						'AccountingCode.parent_id'=>$accountingCode['AccountingCode']['id'],
					),
					'order'=>'AccountingCode.lft ASC',
				));
				//pr($secondaryAccountingCodes);
				if (!empty($secondaryAccountingCodes)){
					for ($j=0;$j<count($secondaryAccountingCodes);$j++){
						$accountingCode=$secondaryAccountingCodes[$j];
						$secondaryFlows[$j]['accounting_code_id']=$secondaryAccountingCodes[$j]['AccountingCode']['id'];
						$secondaryFlows[$j]['accounting_code_code']=$secondaryAccountingCodes[$j]['AccountingCode']['code'];
						$secondaryFlows[$j]['accounting_code_description']=$secondaryAccountingCodes[$j]['AccountingCode']['description'];
					
						$secondaryFlows[$j]['current_total']=0;
						$descendentcodeids=$this->AccountingCode->find('list',array(
							'fields' => array('AccountingCode.id'),
							'conditions' => array(
								'AccountingCode.lft BETWEEN ? AND ?' => array($secondaryAccountingCodes[$j]['AccountingCode']['lft']+1, $secondaryAccountingCodes[$j]['AccountingCode']['rght']-1),
							)
						));
						$conditions=array(
							'AccountingRegister.register_date >='=>$startDate,
							'AccountingRegister.register_date <'=>$endDatePlusOne,
						);
						if (!empty($descendentcodeids)){
							$conditions[]=array('AccountingMovement.accounting_code_id'=>$descendentcodeids);
						}
						else {
							$conditions[]=array('AccountingMovement.accounting_code_id'=>$secondaryAccountingCodes[$j]['AccountingCode']['id']);
						}

						$currentAccountingRegisters=$this->AccountingRegister->AccountingMovement->find('all',array(
							'fields'=>array('SUM(AccountingMovement.amount) as AccountingMovement__total','AccountingMovement.currency_id','AccountingMovement.bool_debit'),
							'conditions'=>$conditions,
							'group'=>'bool_debit',
						));
						
						
						if (!empty($currentAccountingRegisters)){
							foreach ($currentAccountingRegisters as $currentAccountingRegister){
								if ($accountingCode['AccountingCode']['bool_creditor']){
									if ($currentAccountingRegister['AccountingMovement']['bool_debit']){
										$secondaryFlows[$j]['current_total']-=$currentAccountingRegister['AccountingMovement']['total'];
									}
									else {
										$secondaryFlows[$j]['current_total']+=$currentAccountingRegister['AccountingMovement']['total'];
									}
								}
								else{
									if ($currentAccountingRegister['AccountingMovement']['bool_debit']){
										$secondaryFlows[$j]['current_total']+=$currentAccountingRegister['AccountingMovement']['total'];
									}
									else {
										$secondaryFlows[$j]['current_total']-=$currentAccountingRegister['AccountingMovement']['total'];
									}
								}
							}
						}
						//if ($secondaryAccountingCodes[$j]['AccountingCode']['id']==89){
							//pr($secondaryFlows[$j]);
						//}
						
						$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['code']=$secondaryAccountingCodes[$j]['AccountingCode']['code'];
						$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['description']=$secondaryAccountingCodes[$j]['AccountingCode']['description'];
						$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['current_total']=$secondaryFlows[$j]['current_total'];
						
						$initialSaldo=$this->AccountingCode->getTotalSaldo($secondaryAccountingCodes[$j]['AccountingCode']['id'],$startDate);	
						$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['initial_saldo']=$initialSaldo;
						$finalSaldo=$this->AccountingCode->getTotalSaldo($secondaryAccountingCodes[$j]['AccountingCode']['id'],$endDatePlusOne);
						$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['final_saldo']=$finalSaldo;
						
						$tertiaryFlows=array();
						$tertiaryAccountingCodes=$this->AccountingCode->find('all',array(
							'conditions'=>array(
								'AccountingCode.parent_id'=>$accountingCode['AccountingCode']['id'],
							),
							'order'=>'AccountingCode.lft ASC',
						));
						//pr($tertiaryAccountingCodes);
						if (!empty($tertiaryAccountingCodes)){
							for ($k=0;$k<count($tertiaryAccountingCodes);$k++){
								$accountingCode=$tertiaryAccountingCodes[$k];
								$tertiaryFlows[$k]['accounting_code_id']=$accountingCode['AccountingCode']['id'];
								$tertiaryFlows[$k]['accounting_code_code']=$accountingCode['AccountingCode']['code'];
								$tertiaryFlows[$k]['accounting_code_description']=$accountingCode['AccountingCode']['description'];
								
								$tertiaryFlows[$k]['current_total']=0;
								$descendentcodeids=$this->AccountingCode->find('list',array(
									'fields' => array('AccountingCode.id'),
									'conditions' => array(
										'AccountingCode.lft BETWEEN ? AND ?' => array($accountingCode['AccountingCode']['lft']+1, $accountingCode['AccountingCode']['rght']-1),
									)
								));
								$currentAccountingRegisters=$this->AccountingRegister->AccountingMovement->find('all',array(
									'fields'=>array('SUM(AccountingMovement.amount) as AccountingMovement__total','AccountingMovement.currency_id','AccountingMovement.bool_debit'),
									'conditions'=>array(
										'AccountingMovement.accounting_code_id'=>$descendentcodeids,
										'AccountingRegister.register_date >='=>$startDate,
										'AccountingRegister.register_date <'=>$endDatePlusOne,
									),
									'group'=>'bool_debit',
								));
								if (!empty($currentAccountingRegisters)){
									foreach ($currentAccountingRegisters as $currentAccountingRegister){
										if ($accountingCode['AccountingCode']['bool_creditor']){
											if ($currentAccountingRegister['AccountingMovement']['bool_debit']){
												$tertiaryFlows[$k]['current_total']-=$currentAccountingRegister['AccountingMovement']['total'];
											}
											else {
												$tertiaryFlows[$k]['current_total']+=$currentAccountingRegister['AccountingMovement']['total'];
											}
										}
										else{
											if ($currentAccountingRegister['AccountingMovement']['bool_debit']){
												$tertiaryFlows[$k]['current_total']+=$currentAccountingRegister['AccountingMovement']['total'];
											}
											else {
												$tertiaryFlows[$k]['current_total']-=$currentAccountingRegister['AccountingMovement']['total'];
											}
										}
									}
								}
								//pr($secondaryAccountingCodes[$j]);
								//pr($tertiaryAccountingCodes[$k]);
								$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['tertiary'][$tertiaryAccountingCodes[$k]['AccountingCode']['id']]['code']=$tertiaryAccountingCodes[$k]['AccountingCode']['code'];
								$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['tertiary'][$tertiaryAccountingCodes[$k]['AccountingCode']['id']]['description']=$tertiaryAccountingCodes[$k]['AccountingCode']['description'];
								$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['tertiary'][$tertiaryAccountingCodes[$k]['AccountingCode']['id']]['current_total']=$tertiaryFlows[$k]['current_total'];
									
								$initialSaldo=$this->AccountingCode->getTotalSaldo($tertiaryAccountingCodes[$k]['AccountingCode']['id'],$startDate);	
								$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['tertiary'][$tertiaryAccountingCodes[$k]['AccountingCode']['id']]['initial_saldo']=$initialSaldo;
								$finalSaldo=$this->AccountingCode->getTotalSaldo($tertiaryAccountingCodes[$k]['AccountingCode']['id'],$endDatePlusOne);
								$statusFlows[$i]['secondary'][$secondaryAccountingCodes[$j]['AccountingCode']['id']]['tertiary'][$tertiaryAccountingCodes[$k]['AccountingCode']['id']]['final_saldo']=$finalSaldo;
							}
						}
						
					}
				}
			}
			
			$results[$statusFlows[$i]['accounting_code_id']]['code']=$statusFlows[$i]['accounting_code_code'];
			$results[$statusFlows[$i]['accounting_code_id']]['description']=$statusFlows[$i]['accounting_code_description'];
			$results[$statusFlows[$i]['accounting_code_id']]['current_total']=$statusFlows[$i]['current_total'];
			if (!empty($statusFlows[$i]['secondary'])){
				$results[$statusFlows[$i]['accounting_code_id']]['secondary']=$statusFlows[$i]['secondary'];
			}
			else {
				$results[$statusFlows[$i]['accounting_code_id']]['secondary']=array();
			}
			
			if ($statusAccountingCodes[$i]==ACCOUNTING_CODE_COSTOS_VENTA){
				$results[$statusFlows[$i]['accounting_code_id']]['initial_saldo']=$costosVentaInitial;
				$results[$statusFlows[$i]['accounting_code_id']]['final_saldo']=$costosVentaInitial+$costosVentaPeriod;
			}
			else {
				$initialSaldo=$this->AccountingCode->getTotalSaldo($statusAccountingCodes[$i],$startDate);	
				$results[$statusFlows[$i]['accounting_code_id']]['initial_saldo']=$initialSaldo;
				$finalSaldo=$this->AccountingCode->getTotalSaldo($statusAccountingCodes[$i],$endDatePlusOne);
				$results[$statusFlows[$i]['accounting_code_id']]['final_saldo']=$finalSaldo;
			}
		}
		//pr($results);
		return $results;
	}
	
	public function verBalanceGeneral(){
		if ($this->request->is('post')) {
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}

		$_SESSION['endDate']=$endDate;
		
		$this->loadModel('AccountingCode');		
				
		$this->loadModel('ProductType');	
		$this->loadModel('Product');	
		$this->loadModel('StockItem');	
		$this->loadModel('StockItemLog');	
		$this->AccountingCode->recursive=-1;
		$this->Product->recursive=-1;
		$this->StockItem->recursive=-1;
		$statusAccountingCodes=array(
			ACCOUNTING_CODE_ACTIVOS,
			ACCOUNTING_CODE_PASIVOS,
			//ACCOUNTING_CODE_PATRIMONIO,
		);
		
		// calculate the totals for raw material, finished product and other material
		$productTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_RAW,
			),
		));
		$products=$this->Product->find('list',array(
			'fields'=>'Product.id',
			'conditions'=>array(
				'Product.product_type_id'=>$productTypes,
			),
		));
		$allStockItems=$this->StockItem->find('all',array(
			'conditions'=>array('StockItem.product_id'=>$products),
		));
		$rawMaterialTotal=0;
		if (count($allStockItems)>0){
			$lastStockItemLog=array();
			foreach ($allStockItems as $stockitem){				
				$lastStockItemLog=$this->StockItemLog->find('first',array(
					'conditions'=>array(
						'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id'],
						'StockItemLog.stockitem_date <='=>$endDatePlusOne,
					),
					'order'=>'StockItemLog.id DESC',
				));
				if (count($lastStockItemLog)>0){
					$rawMaterialTotal+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
				}
			}
		}
		$productTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_PRODUCED,
			),
		));
		$products=$this->Product->find('list',array(
			'fields'=>'Product.id',
			'conditions'=>array(
				'Product.product_type_id'=>$productTypes,
			),
		));
		$allStockItems=$this->StockItem->find('all',array(
			'conditions'=>array('StockItem.product_id'=>$products),
		));
		$finishedProductTotal=0;
		if (count($allStockItems)>0){
			$lastStockItemLog=array();
			foreach ($allStockItems as $stockitem){				
				$lastStockItemLog=$this->StockItemLog->find('first',array(
					'conditions'=>array(
						'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id'],
						'StockItemLog.stockitem_date <='=>$endDatePlusOne,
					),
					'order'=>'StockItemLog.id DESC',
				));
				if (count($lastStockItemLog)>0){
					$finishedProductTotal+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
				}
			}
		}
		$productTypes=$this->ProductType->find('list',array(
			'fields'=>'ProductType.id',
			'conditions'=>array(
				'ProductType.product_category_id'=>CATEGORY_OTHER,
			),
		));
		$products=$this->Product->find('list',array(
			'fields'=>'Product.id',
			'conditions'=>array(
				'Product.product_type_id'=>$productTypes,
			),
		));
		$allStockItems=$this->StockItem->find('all',array(
			'conditions'=>array('StockItem.product_id'=>$products),
		));
		$otherMaterialTotal=0;
		if (count($allStockItems)>0){
			$lastStockItemLog=array();
			foreach ($allStockItems as $stockitem){				
				$lastStockItemLog=$this->StockItemLog->find('first',array(
					'conditions'=>array(
						'StockItemLog.stockitem_id'=>$stockitem['StockItem']['id'],
						'StockItemLog.stockitem_date <='=>$endDatePlusOne,
					),
					'order'=>'StockItemLog.id DESC',
				));
				if (count($lastStockItemLog)>0){
					$otherMaterialTotal+=$lastStockItemLog['StockItemLog']['product_quantity']*$lastStockItemLog['StockItemLog']['product_unit_price'];
				}
			}
		}
		
	
		$results=array();
		for ($i=0;$i<count($statusAccountingCodes);$i++){
			$accountingCode=$this->AccountingCode->read(null,$statusAccountingCodes[$i]);
			$results[$i]['accounting_code_id']=$statusAccountingCodes[$i];
			$results[$i]['accounting_code_code']=$accountingCode['AccountingCode']['code'];
			$results[$i]['accounting_code_description']=$accountingCode['AccountingCode']['description'];
			//echo "getting saldo of major account<br/>";
			$saldo=$this->AccountingCode->getTotalSaldo($statusAccountingCodes[$i],$endDatePlusOne);
			//echo "returned saldo of major account<br/>";
			//pr($saldo);
			$results[$i]['saldo']=$saldo;
			
			
			$childrenAccountingCodes=$this->AccountingCode->find('all',array(
				'fields'=>array('id','code','description'),
				'conditions'=>array('AccountingCode.parent_id'=>$statusAccountingCodes[$i]),
			));
			if (!empty($childrenAccountingCodes)){
				for ($c=0;$c<count($childrenAccountingCodes);$c++){
					$childrenAccountingCodes[$c]['accounting_code_id']=$childrenAccountingCodes[$c]['AccountingCode']['id'];
					$childrenAccountingCodes[$c]['accounting_code_code']=$childrenAccountingCodes[$c]['AccountingCode']['code'];
					$childrenAccountingCodes[$c]['accounting_code_description']=$childrenAccountingCodes[$c]['AccountingCode']['description'];
					
					//echo "getting saldo of child account<br/>";
					$childsaldo=$this->AccountingCode->getTotalSaldo($childrenAccountingCodes[$c]['AccountingCode']['id'],$endDatePlusOne);
					$childrenAccountingCodes[$c]['saldo']=$childsaldo;
					
					
					$grandChildrenAccountingCodes=$this->AccountingCode->find('all',array(
						'fields'=>array('id','code','description'),
						'conditions'=>array('AccountingCode.parent_id'=>$childrenAccountingCodes[$c]['AccountingCode']['id']),
					));
					if (!empty($grandChildrenAccountingCodes)){
						for ($g=0;$g<count($grandChildrenAccountingCodes);$g++){
							$grandChildrenAccountingCodes[$g]['accounting_code_id']=$grandChildrenAccountingCodes[$g]['AccountingCode']['id'];
							$grandChildrenAccountingCodes[$g]['accounting_code_code']=$grandChildrenAccountingCodes[$g]['AccountingCode']['code'];
							$grandChildrenAccountingCodes[$g]['accounting_code_description']=$grandChildrenAccountingCodes[$g]['AccountingCode']['description'];
							
							//echo "getting saldo of grandchild account<br/>";
							//echo "grand child accounting code id is ".$grandChildrenAccountingCodes[$g]['AccountingCode']['id']."<br/>";
							// at this level an exception should be made for ACCOUNTING_CODE_INVENTORY (id 29)
							if ($grandChildrenAccountingCodes[$g]['AccountingCode']['id']!=ACCOUNTING_CODE_INVENTORY){
								$grandchildsaldo=$this->AccountingCode->getTotalSaldo($grandChildrenAccountingCodes[$g]['AccountingCode']['id'],$endDatePlusOne);
								$grandChildrenAccountingCodes[$g]['saldo']=$grandchildsaldo;
							}
							else {
								$grandChildrenAccountingCodes[$g]['saldo']=$rawMaterialTotal+$finishedProductTotal+$otherMaterialTotal;
							}
							
							$greatGrandChildrenAccountingCodes=$this->AccountingCode->find('all',array(
								'fields'=>array('id','code','description','parent_id'),
								'conditions'=>array('AccountingCode.parent_id'=>$grandChildrenAccountingCodes[$g]['AccountingCode']['id']),
							));
							if (!empty($greatGrandChildrenAccountingCodes)){
								for ($gg=0;$gg<count($greatGrandChildrenAccountingCodes);$gg++){
									$greatGrandChildrenAccountingCodes[$gg]['accounting_code_id']=$greatGrandChildrenAccountingCodes[$gg]['AccountingCode']['id'];
									$greatGrandChildrenAccountingCodes[$gg]['accounting_code_code']=$greatGrandChildrenAccountingCodes[$gg]['AccountingCode']['code'];
									$greatGrandChildrenAccountingCodes[$gg]['accounting_code_description']=$greatGrandChildrenAccountingCodes[$gg]['AccountingCode']['description'];
									
									//echo "getting saldo of grandchild account<br/>";
									// at this level an exception should be made if parent id for the accounting code is ACCOUNTING_CODE_INVENTORY
									if ($greatGrandChildrenAccountingCodes[$gg]['AccountingCode']['parent_id']!=ACCOUNTING_CODE_INVENTORY){
										$greatgrandchildsaldo=$this->AccountingCode->getTotalSaldo($greatGrandChildrenAccountingCodes[$gg]['AccountingCode']['id'],$endDatePlusOne);
										$greatGrandChildrenAccountingCodes[$gg]['saldo']=$greatgrandchildsaldo;
									}
									else {
										switch ($greatGrandChildrenAccountingCodes[$gg]['AccountingCode']['id']){
											case ACCOUNTING_CODE_INVENTORY_RAW_MATERIAL:
												$greatGrandChildrenAccountingCodes[$gg]['saldo']=$rawMaterialTotal;
												break;
											case ACCOUNTING_CODE_INVENTORY_FINISHED_PRODUCT:
												$greatGrandChildrenAccountingCodes[$gg]['saldo']=$finishedProductTotal;
												break;
											case ACCOUNTING_CODE_INVENTORY_OTHER_MATERIAL:
												$greatGrandChildrenAccountingCodes[$gg]['saldo']=$otherMaterialTotal;
												break;
										}
									}
								}
								$grandChildrenAccountingCodes[$g]['greatgrandchildren']=$greatGrandChildrenAccountingCodes;
							}
						}
						$childrenAccountingCodes[$c]['grandchildren']=$grandChildrenAccountingCodes;
						
						
					}
				}
			}
			$results[$i]['children']=$childrenAccountingCodes;
		}
		//pr($results);
		
		// get list of all accounting codes for selector
		$accountingCodes=$this->AccountingCode->find('list',array(
			'order'=>array('AccountingCode.lft'),
		));
		
		$this->set(compact('endDate','accountingCodes','statusFlows','results'));	
		/*
		$patrimonyAccountingCodes=array(
			ACCOUNTING_CODE_CAPITAL_SOCIAL,
			ACCOUNTING_CODE_LOSSES_GAINS_PREVIOUS_PERIODS,
			ACCOUNTING_CODE_LOSSES_GAINS_EXERCISE,
		);
		
		$patrimonyResults=array();
		for ($i=0;$i<count($patrimonyAccountingCodes);$i++){
			$accountingCode=$this->AccountingCode->read(null,$patrimonyAccountingCodes[$i]);
			$patrimonyResults[$i]['accounting_code_id']=$patrimonyAccountingCodes[$i];
			$patrimonyResults[$i]['accounting_code_code']=$accountingCode['AccountingCode']['code'];
			$patrimonyResults[$i]['accounting_code_description']=$accountingCode['AccountingCode']['description'];
			//echo "getting saldo of major account<br/>";
			$saldo=$this->AccountingCode->getTotalSaldo($patrimonyAccountingCodes[$i],$endDatePlusOne);
			//echo "returned saldo of major account<br/>";
			//pr($saldo);
			$patrimonyResults[$i]['saldo']=$saldo;
		}
		$this->set(compact('patrimonyResults'));
		
		
		$this->loadModel('ClosingDate');
		$previousClosingDate=$this->ClosingDate->find('first',array('order'=>'closing_date DESC'));
		$latestPreviousClosingDate=$previousClosingDate['ClosingDate']['closing_date'];
		$latestPreviousClosingDatePlusOne=date("Y-m-d",strtotime($latestPreviousClosingDate."+1 days"));
		$latestPreviousClosingDateTime=new DateTime($latestPreviousClosingDate);
			
		$utilityresults=$this->calculateResultState($latestPreviousClosingDatePlusOne,$endDatePlusOne,false);
		$utility=$this->getUtility($utilityresults);
		
		$utilityAmount=$utility['current'];
		$this->set(compact('utilityAmount'));
		*/
	}
	
	public function guardarBalanceGeneral() {
		$exportData=$_SESSION['reporteBalanceGeneral'];
		$this->set(compact('exportData'));
	}
	
}

