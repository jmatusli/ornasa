<?php
App::uses('AppModel', 'Model');

class ProviderNature extends AppModel {


	public $displayField = 'name';

  function getProviderNatureList(){
    return $this->find('list',[
      'fields'=>['ProviderNature.id','ProviderNature.abbreviation',]
    ]);
  }

	public $hasMany = [
		'ThirdParty' => [
			'className' => 'ThirdParty',
			'foreignKey' => 'provider_nature_id',
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
	] ;

}
