<?php
App::uses('AppModel', 'Model');

class ThirdParty extends AppModel {
	var $displayField='company_name';
 
  public function getCreditDays($thirdPartyId){
    $this->recursive=-1;
    $thirdParty=$this->find('first',[
      'conditions'=>['ThirdParty.id'=>$thirdPartyId],
    ]);
    //pr($thirdParty);
		$creditperiod=(empty($thirdParty)?0:$thirdParty['ThirdParty']['credit_days']);
		return $creditperiod;
  }
	
  public function getPriceClientCategory($clientId){
    $client=$this->find('first', [
      'fields'=>'price_client_category_id',
			'conditions'=>[
        'id'=>$clientId,
        'bool_provider'=>false,
      ],
      'recursive'=>-1,
		]);
		//pr($client);
    if (empty($client)){
      return 1;
    }
    return $client['Client']['price_client_category_id'];
  }
  
  public function getClientCreditStatus($clientId){
    $this->recursive=-1;
    if ($clientId ==0){
      return [
        'ThirdParty'=>[
          'credit_days'=>0,
          'credit_amount'=>0,
          'pending_payment'=>0,
          'pending_invoices'=>[],
          'credit_saldo'=>0,
        ]
      ];
    }
    $clientCreditStatus=$this->find('first', [
			'conditions'=>['ThirdParty.id'=>$clientId]
		]);
		//pr($clientCreditStatus);
    $pendingPayments=$this->getPendingPayments($clientId);
		$clientCreditStatus['ThirdParty']['pending_payment']=$pendingPayments['total_pending'];
    $clientCreditStatus['ThirdParty']['pending_invoices']=$pendingPayments['pending_invoices'];
    $clientCreditStatus['ThirdParty']['credit_saldo']=$clientCreditStatus['ThirdParty']['credit_amount']-$clientCreditStatus['ThirdParty']['pending_payment'];
		return $clientCreditStatus;
  }
  
  public function getCurrentPendingPayment($clientId){
    $pendingPayments=$this->getPendingPayments($clientId);
    
    return $pendingPayments['total_pending'];
 }
	
  public function getPendingPayments($clientId){
    $totalPending=0;
    $invoiceModel=ClassRegistry::init('Invoice');
    $invoiceModel->recursive=0;
    $exchangeRateModel=ClassRegistry::init('ExchangeRate');
    $cashReceiptInvoiceModel=ClassRegistry::init('CashReceiptInvoice');
   	$pendingInvoices=$invoiceModel->find('all',[
      'fields'=>[
        'Invoice.id','Invoice.invoice_code',
        'Invoice.total_price','Invoice.currency_id',
        'Invoice.invoice_date','Invoice.due_date',
        'Invoice.client_id','Invoice.order_id',
        'Currency.abbreviation','Currency.id'
      ],
      'conditions'=>[
        'Invoice.bool_annulled'=>false,
        'Invoice.bool_paid'=>false,
        'Invoice.client_id'=>$clientId,					
      ],
    ]);
			
    for ($i=0;$i<count($pendingInvoices);$i++){
      $totalForInvoice=$pendingInvoices[$i]['Invoice']['total_price'];
      $totalForInvoiceCS=$totalForInvoice;
      if ($pendingInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
        $invoiceExchangeRate=$exchangeRateModel->getApplicableExchangeRate($pendingInvoices[$i]['Invoice']['invoice_date']);
        $exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
        $totalForInvoiceCS=$totalForInvoice*$exchangeRateInvoiceDate;
      }
      
      
      // get the amount already paid for this invoice
      $invoice_paid_already_CS=round($invoiceModel->getAmountPaidAlreadyCS($pendingInvoices[$i]['Invoice']['id']),2);
      $pendingForInvoice=$totalForInvoiceCS-$invoice_paid_already_CS;
      if ($pendingInvoices[$i]['Invoice']['currency_id']==CURRENCY_USD){
        
        $cashReceiptInvoices=$cashReceiptInvoiceModel->find('all',array(
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
        $invoiceExchangeRate=$exchangeRateModel->getApplicableExchangeRate($pendingInvoices[$i]['Invoice']['invoice_date']);
        $exchangeRateInvoiceDate=$invoiceExchangeRate['ExchangeRate']['rate'];
        // add the diferencia cambiaria on the total
        $currentExchangeRate=$exchangeRateModel->getApplicableExchangeRate(date('Y-m-d'));
        $exchangeRateNow=$currentExchangeRate['ExchangeRate']['rate'];
        $differenceExchangeRateNowInvoice=$exchangeRateNow-$exchangeRateInvoiceDate;
        $differenciaCambiariaTotal=$differenceExchangeRateNowInvoice*$totalForInvoice;
        $pendingForInvoice+=$differenciaCambiariaTotal;
        // add the diferencia cambiaria on the cashreceipts
        if (!empty($cashReceiptInvoices)){
          for ($cri=0;$cri<count($cashReceiptInvoices);$cri++){
            $cashReceiptExchangeRate=$exchangeRateModel->getApplicableExchangeRate($cashReceiptInvoices[$cri]['CashReceipt']['receipt_date']);
            $exchangeRateCashReceiptDate=$cashReceiptExchangeRate['ExchangeRate']['rate'];
            $differenceExchangeRateNowCashReceipt=$exchangeRateNow-$exchangeRateCashReceiptDate;
            $differenciaCambiariaPaid=$differenceExchangeRateNowCashReceipt*$cashReceiptInvoices[$cri]['CashReceiptInvoice']['payment_credit_CS']/$exchangeRateCashReceiptDate;
            $pendingForInvoice-=$differenciaCambiariaPaid;
          }
        }
      }
      $totalPending+=$pendingForInvoice;
    }
    
    $result=[
      'client_id'=>$clientId,
      'total_pending'=>$totalPending,
      'pending_invoices'=>$pendingInvoices
    ];
    return $result;
  }
	
  public function getClientList($maxChar=0){
    $fields=['ThirdParty.id','ThirdParty.company_name'];
    $clientConditions=[
      'ThirdParty.bool_provider'=>false,
    ];
    
    
    $clients=$this->find('list',[
			'fields'=>$fields,
			'conditions'=>$clientConditions,
      'order'=>'ThirdParty.company_name',
		]);
    if ($maxChar>0){
      foreach ($clients as $clientId=>$clientName){
        $clients[$clientId]=substr($clientName,0,$maxChar);
      }
    }
    return $clients;
  }
  
  public function getActiveClientList($maxChar=0,$clientId=0,$plantId=0){
    $fields=['ThirdParty.id','ThirdParty.company_name'];
    $conditions=[
      'ThirdParty.bool_active'=>true,
      'ThirdParty.bool_provider'=>false,
    ];
    if ($plantId > 0){
      $conditions['ThirdParty.plant_id']=$plantId;
    }
    if ($clientId == 0) {
      $clientConditions=$conditions;
    }
    else {    
      $clientConditions=[
        'OR'=>[
          $conditions,
          [
            'ThirdParty.id'=>$clientId,
          ],
        ],
      ];
    }
    
    $clients=$this->find('list',[
			'fields'=>$fields,
			'conditions'=>$clientConditions,
      'order'=>'ThirdParty.company_name',
		]);
    if ($maxChar>0){
      foreach ($clients as $clientId=>$clientName){
        $clients[$clientId]=substr($clientName,0,$maxChar);
      }
    }
    return $clients;
  }
  
  public function getNonGenericActiveClientList($plantId=0){
    $fields=['ThirdParty.id','ThirdParty.company_name'];
    $conditions=[
      'ThirdParty.bool_active'=>true,
      'ThirdParty.bool_provider'=>false,
      'ThirdParty.bool_generic'=>false,
    ];
    if ($plantId > 0){
      $conditions['ThirdParty.plant_id']=$plantId;
    }
    return $this->find('list',[
			'fields'=>$fields,
			'conditions'=>$conditions,
      'order'=>'ThirdParty.company_name',
		]);
  }
  
  public function getGenericClientIds($plantId=0){
    $fields=['ThirdParty.id'];
    $conditions=[
      'ThirdParty.bool_provider'=>false,
      'ThirdParty.bool_generic'=>true,
    ];
    if ($plantId > 0){
      $conditions['ThirdParty.plant_id']=$plantId;
    }
    return $this->find('list',[
			'fields'=>$fields,
			'conditions'=>$conditions,
      'order'=>'ThirdParty.id',
		]);
  }
  
  public function getActiveProviderList($plantId=0){
    $conditions=[
      'ThirdParty.bool_active'=>true,
      'ThirdParty.bool_provider'=>true,
    ];
    if ($plantId > 0){
      $conditions['ThirdParty.plant_id']=$plantId;
    }
    $providers=$this->find('list',[
			'fields'=>['ThirdParty.id','ThirdParty.company_name'],
			'conditions'=>$conditions,
      'recursive'=>-1,
			'order'=>'ThirdParty.company_name',
		]);
    return $providers;
  }
  
  public function getThirdPartyById($thirdPartyId){
    $thirdParty=$this->find('first',[
			'conditions'=>[
				'ThirdParty.id'=>$thirdPartyId,
			],
      'recursive'=>-1,
		]);
    return $thirdParty;
  }
  public function getClientById($clientId){
    return $this->getThirdPartyById($clientId);
  }
  public function getClientPhone($clientId){
    $client=$this->getClientById($clientId);
    return empty($client)?"":$client['ThirdParty']['phone'];
  }
  public function getProviderById($providerId){
    return $this->getThirdPartyById($providerId);
  }
  
  public function getRucNumbersPerClient(){
    $rucNumbers=$this->find('list',[
      'fields'=>['ThirdParty.id','ThirdParty.ruc_number'],
      'conditions'=>[
        'ThirdParty.bool_active'=>true,
        'ThirdParty.bool_provider'=>false,
      ],
    ]);
    foreach ($rucNumbers as $clientId=>$rucNumber){
      if (empty($rucNumber)){
        $rucNumbers[$clientId]=-1;
      }
    }
    return $rucNumbers;
  }
  
  public function getProviderNatureIdsByProviderId(){
    return $this->find('list',[
			'fields'=>['ThirdParty.id', 'ThirdParty.provider_nature_id'],
		]);
  }
  
  public function updateClientDataConditionally($modifiedClientData,$userRoleId){
    if (empty($modifiedClientData)){
      return [
        'success'=>false,
        'message'=>'no se especificó el cliente'
      ];
    }
    $clientId=$modifiedClientData['id'];
    if (empty($clientId)){
      return [
        'success'=>false,
        'message'=>'no se especificó el id del cliente'
      ];
    }
    
    if (empty($userRoleId)){
      return [
        'success'=>false,
        'message'=>'no se especificó el papel del usuario'
      ];
    }
    $originalClientData=$this->find('first',[
      'conditions'=>['ThirdParty.id'=>$modifiedClientData['id']],
      'recursive'=>-1,
    ]);
    if (empty( $originalClientData)){
      return [
        'success'=>false,
        'message'=>'no se eencontró el cliente'
      ];
    }
    $clientData=[
      'id'=>$clientId,
    ];
    
    if ($this->MarkClientPropertyForUpdate($originalClientData['ThirdParty'],$modifiedClientData,'phone',$userRoleId)){
      $clientData['phone']=$modifiedClientData['phone'];
    }
    if ($this->MarkClientPropertyForUpdate($originalClientData['ThirdParty'],$modifiedClientData,'email',$userRoleId)){
      $clientData['email']=$modifiedClientData['email'];
    };
    if ($this->MarkClientPropertyForUpdate($originalClientData['ThirdParty'],$modifiedClientData,'address',$userRoleId)){
      $clientData['address']=$modifiedClientData['address'];
    }
    if ($this->MarkClientPropertyForUpdate($originalClientData['ThirdParty'],$modifiedClientData,'ruc_number',$userRoleId)){
      $clientData['ruc_number']=$modifiedClientData['ruc_number'];
    }
    if ($this->MarkClientPropertyForUpdate($originalClientData['ThirdParty'],$modifiedClientData,'client_type_id',$userRoleId)){
      $clientData['client_type_id']=$modifiedClientData['client_type_id'];
    }
    if ($this->MarkClientPropertyForUpdate($originalClientData['ThirdParty'],$modifiedClientData,'zone_id',$userRoleId)){
      $clientData['zone_id']=$modifiedClientData['zone_id'];
    }    
    $this->id=$clientId;
    if (!$this->save($clientData)) {
      pr($this->validateErrors($this));
      return [
        'success'=>false,
        'message'=>'problema actualizando los datos del cliente'
      ];
      
    }
    return [
      'success'=>true,
      'message'=>'cliente actualizado'
    ];
  }
  private function markClientPropertyForUpdate($originalClient,$modifiedClient,$propertyName,$userRoleId){
    if (empty($modifiedClient[$propertyName])){
      return false;
    }
    if ($modifiedClient[$propertyName] == $originalClient[$propertyName]){
      return false;
    }
    if(!empty($originalClient[$propertyName]) && $userRoleId != ROLE_ADMIN && !in_array($propertyName,['client_type_id','zone_id'])){
      return false;
    }
    return true;
  }
  
  public function getRegisteredClientNames(){
    return $this->find('list',[
      'conditions'=>[
        'bool_active'=>true,
        'bool_generic'=>false,
      ],
    ]);
  }
  
  public function getSimilarClientNames($comparisonList,$clientName,$percent){
    $similarClients=[];
    foreach ($comparisonList as $comparisonClientId=>$comparisonClientName){
      similar_text($comparisonClientName,$clientName,$similarity);
      //echo $comparisonClientName.' has similarity '.$similarity.'<br/>';
      if ($similarity > $percent && $similarity != 100){
        $similarClients[$comparisonClientId]=$comparisonClientName;
      }
    }
    return $similarClients;
  }
  
  public function getClientCreatorList(){
    $fields=['ThirdParty.id','ThirdParty.creator_user_id'];
    $clientConditions=[
      'ThirdParty.bool_provider'=>false,
    ];
    return $this->find('list',[
			'fields'=>$fields,
			'conditions'=>$clientConditions,
		]);
  }
  public function getClientOwnerList(){
    $fields=['ThirdParty.id','ThirdParty.owner_user_id'];
    $clientConditions=[
      'ThirdParty.bool_provider'=>false,
    ];
    return $this->find('list',[
			'fields'=>$fields,
			'conditions'=>$clientConditions,
		]);
  }
  
 	public $validate = [
		'bool_provider' => [
			'boolean' => [
				'rule' => ['boolean'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'company_name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		/*
		'first_name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'last_name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		*/
	];

	public $belongsTo = [
		'AccountingCode' => [
			'className' => 'AccountingCode',
			'foreignKey' => 'accounting_code_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'ClientType' => [
			'className' => 'ClientType',
			'foreignKey' => 'client_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'CreditCurrency' => [
			'className' => 'Currency',
			'foreignKey' => 'credit_currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Plant' => [
			'className' => 'Plant',
			'foreignKey' => 'plant_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    
    'PriceClientCategory' => [
			'className' => 'PriceClientCategory',
			'foreignKey' => 'price_client_category_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'ProviderNature' => [
			'className' => 'ProviderNature',
			'foreignKey' => 'provider_nature_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'Zone' => [
			'className' => 'Zone',
			'foreignKey' => 'zone_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'CreatorUser' => [
			'className' => 'User',
			'foreignKey' => 'creator_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'OwnerUser' => [
			'className' => 'User',
			'foreignKey' => 'owner_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
	public $hasMany = [
		
    'Order' => [
			'className' => 'Order',
			'foreignKey' => 'third_party_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
		'Invoice' => [
			'className' => 'Invoice',
			'foreignKey' => 'client_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
    'PlantThirdParty' => [
			'className' => 'PlantThirdParty',
			'foreignKey' => 'third_party_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => '',
		],
    'PurchaseOrder' => [
			'className' => 'PurchaseOrder',
			'foreignKey' => 'provider_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
    'ThirdPartyUser' => [
			'className' => 'ThirdPartyUser',
			'foreignKey' => 'third_party_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
	];

}
