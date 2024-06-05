<?php
App::uses('AppModel', 'Model');
/**
 * ClientUser Model
 *
 * @property Client $Client
 * @property User $User
 */
class ClientUser extends AppModel {

	public function checkAssociationClientWithUser($clientId,$userId){
		$this->recursive=-1;
		$clientUserAssociation=$this->find('first',array(
			'conditions'=>array(
				'ClientUser.client_id'=>$clientId,
				'ClientUser.user_id'=>$userId,
			),
			'order'=>'ClientUser.id DESC',
		));
		if (!empty($clientUserAssociation)){
			return $clientUserAssociation['ClientUser']['bool_assigned'];
		}
		return false;
	}
  
  public function getAssociatedClientsForUser($userId){
    $this->recursive=-1;
		$clientIdsAssociatedWithUserAtOneTime=$this->find('list',[
      'fields'=>['ClientUser.client_id'],
			'conditions'=>[
        'ClientUser.user_id'=>$userId,
        'ClientUser.bool_assigned'=>true,
      ],
			'order'=>'ClientUser.assignment_datetime DESC',
		]);
    $clientIdsAssociatedWithUserAtOneTime=array_unique($clientIdsAssociatedWithUserAtOneTime);
    $this->Client->recursive=-1;
    $uniqueClients=$this->Client->find('all',[
      'conditions'=>[
        'Client.id'=>$clientIdsAssociatedWithUserAtOneTime,
      ],
      'contain'=>[					
        'ClientUser'=>[
          'conditions'=>[
            'ClientUser.user_id'=>$userId,
          ],
          'order'=>'ClientUser.assignment_datetime DESC,ClientUser.id DESC',
        ],
  		],
    ]);
    $clientIdsCurrentlyAssociated=[];
    foreach ($uniqueClients as $client){
      if ($client['ClientUser'][0]['bool_assigned']){
        $clientIdsCurrentlyAssociated[]=$client['Client']['id'];
      }
    }
		return $clientIdsCurrentlyAssociated;
	}

	public $validate = array(
		'client_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
