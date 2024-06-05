<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
	public $actsAs = array('Containable');
  
  	public function checkUnique($ignoredData, $fields, $or = true) {
      return $this->isUnique($fields, $or);
  }
  
  public function getMonthArray($startDate,$endDate){
    $monthArray=[];
    // get the relevant time period
		$startDateDay=date("d",strtotime($startDate));
		$startDateMonth=date("m",strtotime($startDate));
		$startDateYear=date("Y",strtotime($startDate));
		$endDateDay=date("d",strtotime($endDate));
		$endDateMonth=date("m",strtotime($endDate));
		$endDateYear=date("Y",strtotime($endDate));
		$counter=0;
		for ($yearCounter=$startDateYear;$yearCounter<=$endDateYear;$yearCounter++){
			if ($yearCounter==$startDateYear && $yearCounter==$endDateYear){
				// only 1 year in consideration
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=$endDateMonth;
			}
			else if($yearCounter==$startDateYear){
				// starting year (not the same as ending year)
				$startingDay=$startDateDay;
				$startingMonth=$startDateMonth;
				$endingMonth=12;
			}
			else if ($yearCounter==$endDateYear){
				// ending year (not the same as starting year)
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=$endDateMonth;
			}
			else {
				// in between year
				$startingDay=1;
				$startingMonth=1;
				$endingMonth=12;
			}
      
			for ($monthCounter=$startingMonth;$monthCounter<=$endingMonth;$monthCounter++){
				$monthArray[$counter]['period']=$monthCounter.'_'.$yearCounter;
				if ($monthCounter==$startDateMonth && $yearCounter == $startDateYear){
					$monthArray[$counter]['start']=$startingDay;
				}
				else {
					$monthArray[$counter]['start']=1;
				}
				$monthArray[$counter]['month']=$monthCounter;
				$monthArray[$counter]['year']=$yearCounter;
        
        $nextmonth=($monthCounter==12)?1:($monthCounter+1);
        $nextyear=($monthCounter==12)?($yearCounter+1):$yearCounter;
        $monthArray[$counter]['sale_start_date']=date('Y-m-d',strtotime($yearCounter.'-'.$monthCounter.'-'.$monthArray[$counter]['start']));
        $monthArray[$counter]['sale_end_date_plus_one']=date('Y-m-d',strtotime($nextyear.'-'.$nextmonth.'-'.$monthArray[$counter]['start']));
        
				$counter++;
			}
		}
    return $monthArray;
  }

  public function getYearArray($startYear,$endYear){
    $yearArray=[];
    // get the relevant time period
		
		$counter=0;
		for ($yearCounter=$startYear;$yearCounter<=$endYear;$yearCounter++){
      $yearArray[$counter]['period']=$yearCounter;
      $yearArray[$counter]['year']=$yearCounter;
      
      $yearArray[$counter]['sale_start_date']=date('Y-m-d',strtotime($yearCounter.'-01-01'));
      $yearArray[$counter]['sale_end_date_plus_one']=date('Y-m-d',strtotime(($yearCounter+1).'-01-01'));
      
      $counter++;
		}
    return $yearArray;
  }

}
