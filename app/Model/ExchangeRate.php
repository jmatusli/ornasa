<?php
App::uses('AppModel', 'Model');

class ExchangeRate extends AppModel {

	public function getApplicableExchangeRate($applicationDate){
		$applicableExchangeRate=$this->find('first',[
			'conditions'=>[
				'application_date <='=>$applicationDate,
			],
			'order'=>'application_date DESC',
		]);
    //pr($applicableExchangeRate);
		return $applicableExchangeRate;
	}
	
  public function getApplicableExchangeRateValue($applicationDate,$currencyId=CURRENCY_USD){
		$applicableExchangeRate=$this->find('first',[
			'conditions'=>[
				'application_date <='=>$applicationDate,
        'base_currency_id'=>$currencyId,
			],
			'order'=>'application_date DESC',
		]);
    //pr($applicableExchangeRate);
		return $applicableExchangeRate['ExchangeRate']['rate'];
	}
  
	function getLatestExchangeRateDuration(){
		$latestExchangeRate=$this->find('first',[
			'fields'=>['ExchangeRate.application_date'],
			'order'=>'ExchangeRate.application_date DESC',
		]);
		$duration=0;
		if (!empty($latestExchangeRate)){
			$applicationDate=new DateTime($latestExchangeRate['ExchangeRate']['application_date']);
			//pr($applicationDate);
			$currentDate= new DateTime(date('Y-m-d'));
			$daysPassed=$currentDate->diff($applicationDate);
			//pr($daysPassed);
			$duration=abs($daysPassed->format('%r%a'));
		}
		return $duration;
	}

  
	public $validate = array(
		'conversion_currency_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'base_currency_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'rate' => array(
			'decimal' => array(
				'rule' => array('decimal',4),
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
		'ConversionCurrency' => array(
			'className' => 'Currency',
			'foreignKey' => 'conversion_currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'BaseCurrency' => array(
			'className' => 'Currency',
			'foreignKey' => 'base_currency_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
