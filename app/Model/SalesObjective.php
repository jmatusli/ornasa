<?php
App::uses('AppModel', 'Model');
/**
 * SalesObjective Model
 *
 * @property User $User
 */
class SalesObjective extends AppModel {

	public function getApplicableSalesObjective($userId,$applicationDate){
		$applicableSalesObjective=$this->find('first',array(
			'conditions'=>array(
				'user_id'=>$userId,
				'objective_date <='=>$applicationDate,
			),
			'order'=>'objective_date DESC',
		));
		return $applicableSalesObjective;
	}

	public function getHistoricalPerformance($userId){
		$invoiceModel=ClassRegistry::init('Invoice');
		$invoiceSalesOrderModel=ClassRegistry::init('InvoiceSalesOrder');
		$userModel=ClassRegistry::init('User');
		
		$historicalPerformance=-100;
		
		$this->recursive=-1;
		$salesObjectivesForUser=$this->find('all',array(
			'conditions'=>array(
				'SalesObjective.user_id'=>$userId,
			)
		));
		$startDate=null;
		$endDate=null;
		if (!empty($salesObjectivesForUser)){
			$user=$this->User->find('first',array(
				'conditions'=>array(
					'User.id'=>$userId,
				),
				'contain'=>array(
					'Employee',
				),
			));
			$weightedAverage=0;
			$totalMonthCount=0;
			$objectiveCounter=0;
			foreach ($salesObjectivesForUser as $salesObjective){
				$objectiveCounter++;
				$objectiveDate=$salesObjective['SalesObjective']['objective_date'];
				$minimumObjective=$salesObjective['SalesObjective']['minimum_objective'];
				$maximumObjective=$salesObjective['SalesObjective']['maximum_objective'];
				
				if (empty($endDate)){
					if (!empty($user['Employee'])){
						//pr($user['Employee']);
						$startDate=$user['Employee']['starting_date'];
					}
					else{
						if ($user['User']['created']<date('2016-04-01')){
							$startDate=date('2016-04-01');
						}
						else {
							$startDate=date("Y-m-d",strtotime($user['User']['created']));
						}
					}
				}
				else {
					$startDate=$endDate;
				}
				$startDateMonth=date('m',strtotime($startDate));
				
				if ($objectiveCounter<count($salesObjectivesForUser)){
					$endDateYear=date('Y',strtotime($objectiveDate));
					$endDateMonth=date('m',strtotime($objectiveDate));
					$endDate=date("Y-m-d",strtotime($endDateYear."-".$endDateMonth."-01"));
				}
				else {
					$endDateYear=date('Y');
					$endDateMonth=date('m');
					$endDate=date("Y-m-01");
				}
				$numberOfMonths=$endDateMonth-$startDateMonth;
				
				$invoiceModel->recursive=-1;
				$invoiceModel->virtualFields['totalPaid']=0;
				$invoiceIds=$invoiceSalesOrderModel->find('list',array(
					'fields'=>array('InvoiceSalesOrder.invoice_id'),
					'conditions'=>array(
						'InvoiceSalesOrder.user_id'=>$userId,
					),
				));
				$invoices=$invoiceModel->find('all',array(
					'fields'=>array('SUM(Invoice.amount_paid) AS Invoice__totalPaid'),
					'conditions'=>array(
						'Invoice.id'=>$invoiceIds,
						'Invoice.invoice_date >='=>$startDate,
						'Invoice.invoice_date <'=>$endDate,
					),
				));
				if ($userId==-6){
					echo "user ".$user['User']['username']."<br/>";
					pr($startDate);
					pr($endDate);
					echo "number of months is ".$numberOfMonths."<br/>";
					pr($invoice);
					echo "minimum objective is ".$minimumObjective."<br/>";
					echo "maximum objective is ".$maximumObjective."<br/>";
				}
				
				if (!empty($invoice[0]['Invoice']['totalPaid'])){
					//pr($invoice);
					if ($invoice[0]['Invoice']['totalPaid']<=$minimumObjective*$numberOfMonths){
						$weightedAverage+=0;
						//echo "weigthed average is ".$weightedAverage."<br/>";
					}
					else {
						$weightedAverage+=$invoice[0]['Invoice']['totalPaid']/($maximumObjective*$numberOfMonths);
						//echo "weigthed average is ".$weightedAverage."<br/>";
					}
					$totalMonthCount+=$numberOfMonths;
				}
			}
		}
		if (!empty($totalMonthCount)){
			$historicalPerformance=$weightedAverage/$totalMonthCount;
		}
		else {
			$historicalPerformance=0;
		}
		
		return $historicalPerformance;
	}
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
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
		'objective_date' => array(
			'date' => array(
				'rule' => array('date'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'minimum_objective' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'maximum_objective' => array(
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
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
