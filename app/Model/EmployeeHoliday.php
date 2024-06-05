<?php
App::uses('AppModel', 'Model');

class EmployeeHoliday extends AppModel {

  public function holidaysTaken($employeeId,$startDate,$endDate){
    $taken=0;
    $endDatePlusOne=date("Y-m-d",strtotime(($endDate->format('Y-m-d'))."+1 days"));
    $this->virtualFields['total_holidays_taken']=0;
    $employeeHolidays=$this->find('all',array(
				'fields'=>array(
					'SUM(days_taken) AS EmployeeHoliday__total_holidays_taken', 
				),
				'conditions'=>array(
					'EmployeeHoliday.employee_id'=>$employeeId,
					'EmployeeHoliday.holiday_date >='=>$startDate->format('Y-m-d'),
          'EmployeeHoliday.holiday_date <'=>$endDatePlusOne,
				),
			));
      //pr($employeeHolidays);
      if (!empty($employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'])){
        $taken= $employeeHolidays[0]['EmployeeHoliday']['total_holidays_taken'];
      }
      return $taken;
  }
  
  public function getHolidayYearArray($employeeId,$startingDateTime,$endingDateTime){
    $yearArray=[];
    
    $nowDateTime=new DateTime();
    $firstYear=$startingDateTime->format('Y');
    $lastYear=$endingDateTime->format('Y');
    if (date('Y')<$lastYear){
      $lastYear=date('Y');
    }
    //echo "first year is ".$firstYear."<br>";
    //echo "last year is ".$lastYear."<br>";
    
    for ($year=$firstYear;$year<=$lastYear;$year++){
      if ($year==$firstYear){
        $beginningOfYearDateTime=DateTime::createFromFormat('Y-m-d',$startingDateTime->format('Y-m-d'));
      }
      else {
        $beginningOfYearDateTime=DateTime::createFromFormat('Y-m-d',$year.'-01-01');
      }
      $endOfYearDateTime= DateTime::createFromFormat('Y-m-d',$year.'-12-31');
      if ($endingDateTime<$endOfYearDateTime){
        $endOfYearDateTime=$endingDateTime;
      }
      if ($nowDateTime<$endOfYearDateTime){
        $endOfYearDateTime=$nowDateTime;
      }
      //echo "beginning of year datetime is ".($beginningOfYearDateTime->format('d-m-Y'))."<br>";
      //echo "end of year datetime is ".($endOfYearDateTime->format('d-m-Y'))."<br>";
      $daysWorkedInYear=$endOfYearDateTime->diff($beginningOfYearDateTime);
      $workingDays=$daysWorkedInYear->days;
			$holidaysEarned=round(2.5*$workingDays/30,1);
      $holidaysTaken=0;
      $holidaysTaken=$this->holidaysTaken($employeeId,$beginningOfYearDateTime,$endOfYearDateTime);
      
			$yearArray[]=[
        'year'=>$year,
        'earned'=>$holidaysEarned,
        'taken'=>$holidaysTaken
      ];		
    }
    return $yearArray;
  }
  
	public $validate = array(
		'employee_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'holiday_date' => array(
			'date' => array(
				'rule' => array('date'),
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
		'Employee' => array(
			'className' => 'Employee',
			'foreignKey' => 'employee_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'HolidayType' => array(
			'className' => 'HolidayType',
			'foreignKey' => 'holiday_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
}
