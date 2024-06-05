<?php
App::uses('AppModel', 'Model');

class ProductionRun extends AppModel {

	public function getEnergyUseForRun($productionrunid){
		$energyConsumption=0;
		$thisProductionRun=$this->find('first',array('conditions'=>array('ProductionRun.id'=>$productionrunid)));
		//echo "year of OR is ".date('Y',strtotime($thisProductionRun['ProductionRun']['production_run_date']))."<br/>";
		//echo "month of OR is ".date('m',strtotime($thisProductionRun['ProductionRun']['production_run_date']))."<br/>";
		//echo "day of OR is ".date('d',strtotime($thisProductionRun['ProductionRun']['production_run_date']))."<br/>";
		$countOfRunsInShift=$this->find('count',array(
			'conditions'=>array(
				'YEAR(ProductionRun.production_run_date)'=>date('Y',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
				'MONTH(ProductionRun.production_run_date)'=>date('m',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
				'DAY(ProductionRun.production_run_date)'=>date('d',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
				'ProductionRun.shift_id'=>$thisProductionRun['ProductionRun']['shift_id'],
			),
		));
		
		if ($countOfRunsInShift==1){
			$lastMeterReading=$this->find('first',array(
				'fields'=>array('MAX(ProductionRun.meter_finish) AS max_meter'),
				'conditions'=>array('ProductionRun.meter_finish <'=>$thisProductionRun['ProductionRun']['meter_finish']),
			));
			$energyConsumption=$thisProductionRun['ProductionRun']['meter_finish']-$lastMeterReading[0]['max_meter'];
		}
		else{
			//echo "calculating weighted average";
			$allProductionRunsInShift=$this->find('all',array(
				'fields'=>array('ProductionRun.raw_material_quantity','ProductionRun.meter_finish'),
				'conditions'=>array(
					'YEAR(ProductionRun.production_run_date)'=>date('Y',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
					'MONTH(ProductionRun.production_run_date)'=>date('m',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
					'DAY(ProductionRun.production_run_date)'=>date('d',strtotime($thisProductionRun['ProductionRun']['production_run_date'])),
					'ProductionRun.shift_id'=>$thisProductionRun['ProductionRun']['shift_id'],
				),
			));
			$minimalMeterReading=1000000;
			$maximalMeterReading=0;
			$totalProductsProduced=0;
			foreach ($allProductionRunsInShift as $productionrun){
				if ($productionrun['ProductionRun']['meter_finish']<$minimalMeterReading&&$productionrun['ProductionRun']['meter_finish']>0){
					$minimalMeterReading=$productionrun['ProductionRun']['meter_finish'];
				}
				if ($productionrun['ProductionRun']['meter_finish']>$maximalMeterReading){
					$maximalMeterReading=$productionrun['ProductionRun']['meter_finish'];
				}
				$totalProductsProduced+=$productionrun['ProductionRun']['raw_material_quantity'];
			}
			if ($maximalMeterReading==0){
				$energyConsumption=0;
			}
			else {
				$lastMeterReading=$this->find('first',array(
					'fields'=>array('MAX(ProductionRun.meter_finish) AS max_meter'),
					'conditions'=>array('ProductionRun.meter_finish <'=>$minimalMeterReading),
				));
				
				$energyConsumption=($maximalMeterReading-$lastMeterReading[0]['max_meter'])*$thisProductionRun['ProductionRun']['raw_material_quantity']/$totalProductsProduced;
			}
		}
		return $energyConsumption;
	}

	public function checkAcceptableProduction($productionRunId,$productionRunDate){
		$productionRunData=$this->find('first',array(
			'fields'=>array(),
			'conditions'=>array(
				'ProductionRun.id'=>$productionRunId,
			),
			'contain'=>array(
				'FinishedProduct'=>array(
					'ProductProduction'=>array(
						'fields'=>array('ProductProduction.application_date','ProductProduction.acceptable_production'),
						'conditions'=>array(
							'ProductProduction.application_date <='=> $productionRunDate,
						),
						'order'=>'ProductProduction.application_date DESC,ProductProduction.id DESC'
					),
				),
				'ProductionMovement'=>array(
					'fields'=>array('ProductionMovement.product_quantity'),
					'conditions'=>array(
						'ProductionMovement.product_quantity >'=>0,
						'ProductionMovement.production_result_code_id'=>PRODUCTION_RESULT_CODE_A,
					),
				),
			),
		));
		//pr($productionRunData);
		if (!empty($productionRunData)){
			$acceptableProductionValue=0;
			if (!empty($productionRunData['FinishedProduct']['ProductProduction'])){						
				$acceptableProductionValue=$productionRunData['FinishedProduct']['ProductProduction'][0]['acceptable_production'];
			}
			//echo "production run date is ".$productionRunDate."<br/>";
			//echo "day of the week for production run date is ".date('w', strtotime($productionRunDate))."<br/>";
			if(date('w', strtotime($productionRunDate)) == 6){
				//echo "acceptable production value is ".$acceptableProductionValue."<br/>";
				$acceptableProductionValue=$acceptableProductionValue/2;
				//echo "acceptable production value is ".$acceptableProductionValue."<br/>";
			}
			if ($acceptableProductionValue>0){
				$quantityA=0;
				if (!empty($productionRunData['ProductionMovement'])){
					$quantityA=$productionRunData['ProductionMovement'][0]['product_quantity'];
				}			
				//echo "quantity is ".$quantityA."<br/>";
				//echo "acceptable production value is ".$acceptableProductionValue."<br/>";
				if ($quantityA>=$acceptableProductionValue){
					//echo "returning true<br/>";
					return true;
				}
			}
		}
		return false;
	}
	
	public $validate = array(
		'production_run_code' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		/*
		'meter_start' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Registre el valor inicial del medidor',
				'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		*/
		'meter_finish' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				'message' => 'Registre el valor inicial del medidor',
				'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = array(
		'RawMaterial' => array(
			'className' => 'Product',
			'foreignKey' => 'raw_material_id',
			//'conditions' => array('ProductType.product_category_id'=>CATEGORY_RAW),
			'fields' => '',
			'order' => ''
		),
		'FinishedProduct' => array(
			'className' => 'Product',
			'foreignKey' => 'finished_product_id',
			//'conditions' => array('ProductType.product_category_id'=>CATEGORY_PRODUCED),
			'fields' => '',
			'order' => ''
		),
		'Machine' => array(
			'className' => 'Machine',
			'foreignKey' => 'machine_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Operator' => array(
			'className' => 'Operator',
			'foreignKey' => 'operator_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Shift' => array(
			'className' => 'Shift',
			'foreignKey' => 'shift_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ProductionRunType' => array(
			'className' => 'ProductionRunType',
			'foreignKey' => 'production_run_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	public $hasMany = array(
		'ProductionMovement' => array(
			'className' => 'ProductionMovement',
			'foreignKey' => 'production_run_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

}
