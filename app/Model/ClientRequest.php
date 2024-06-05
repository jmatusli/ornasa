<?php
App::uses('AppModel', 'Model');
/**
 * Request Model
 *
 * @property Client $Client
 */
class ClientRequest extends AppModel {

  public function getNewClientRequestCode($clientId,$requestDateString=""){
    if (empty($requestDate)){
      $requestDay=date('d');
      $requestMonth=date('m');
      $requestYear=date('Y');
      $requestDateString=$requestDay.$requestMonth.$requestYear;
    }
    $newRequestCode="";
    $this->recursive=-1;
		$clientModel=ClassRegistry::init('ThirdParty');
		$client=$clientModel->find('first',[
			'fields'=>['ThirdParty.id','ThirdParty.company_name'],
			'conditions'=>[
				'ThirdParty.id'=>$clientId
      ],    
		]);
    
    if (!empty($client)){
      $newRequestCode=$client['ThirdParty']['company_name']."_".$requestDateString."_";
      $latestClientRequest=$this->find('first',[
        'conditions'=>[
          'ClientRequest.client_id'=>$clientId
        ],
        'order'=>'ClientRequest.client_request_code'
      ]);
      if (empty($latestClientRequest)){
        $newRequestCode.="00001";
      }
      else {
        $latestRequestCodeOrdinalNumber=ParseInt(substr($latestClientRequest['ClientRequest']['client_request_code'],(strlen($client['Client']['company_name'])+8)));
        $newRequestCode.=str_pad(($latestRequestCodeOrdinalNumber+1),5,"0",STR_PAD_LEFT);  
      }
    }
    
    return $newRequestCode;
  }
  
	public $validate = array(
		'bool_annulled' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Client' => array(
			'className' => 'ThirdParty',
			'foreignKey' => 'client_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
  
  public $hasMany = [
		'ClientRequestProduct' => [
			'className' => 'ClientRequestProduct',
			'foreignKey' => 'client_request_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		]
  ];  
}
