<?php
App::uses('AppModel', 'Model');

class PageRight extends AppModel {

  public function getIdByCode($code){
    $pageRight=$this->find('first',[
      'conditions'=>[
        'PageRight.code'=>$code,
      ],
      'recursive'=>-1,
    ]);
    
    if (empty($pageRight)){
      return 0;
    }
    return $pageRight['PageRight']['id'];
    
  }

	public $validate = [
		'code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
			],
		],
    'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'list_order' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];
}
