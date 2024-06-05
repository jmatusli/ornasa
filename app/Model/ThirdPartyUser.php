<?php
App::uses('AppModel', 'Model');
class ThirdPartyUser extends AppModel {

	public function hasThirdPartyUser($thirdPartyId,$userId){
		$thirdPartyUser=$this->find('first',[
			'conditions'=>[
				'ThirdPartyUser.third_party_id'=>$thirdPartyId,
				'ThirdPartyUser.user_id'=>$userId,
			],
      'recursive'=>-1,
			'order'=>'ThirdPartyUser.id DESC',
		]);
		if (empty($thirdPartyUser)){
      return false;
		}
		return $thirdPartyUser['ThirdPartyUser']['bool_assigned'];
	}
  
 public function getThirdPartiesForUser($userId){  
    $thirdParties=$this->ThirdParty->find('list');
    foreach ($thirdParties as $thirdPartyId=>$thirdPartyName){
      if (!$this->hasThirdPartyUser($thirdPartyId,$userId)){
        unset($thirdParties[$thirdPartyId]);
      }
    }
    return $thirdParties;
  }
  public function getClientsForUser($userId){
    return $this->getThirdPartiesForUser($userId);
  }
  public function getProvidersForUser($userId){
    return $this->getThirdPartiesForUser($userId);
  }  
  public function getThirdPartyIdsForUser($userId){
    return array_keys($this->getThirdPartiesForUser($userId));
  }
  
  public function getUsersForThirdParty($thirdPartyId){
    $users=$this->User->getUserList();
    foreach ($users as $userId=>$userName){
      if (!$this->hasThirdPartyUser($thirdPartyId,$userId)){
        unset($users[$userId]);
      }
    }
    return $users;
  }
  public function getUserIdsForThirdParty($thirdPartyId){
    return array_keys($this->getUsersForThirdParty($thirdPartyId));
  }

	public $validate = [
		'third_party_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
		'user_id' => [
			'numeric' => [
				'rule' => ['numeric'],
			],
		],
	];

	public $belongsTo = [
		'ThirdParty' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'third_party_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
