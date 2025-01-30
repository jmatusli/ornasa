<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class IncidencesController extends AppController {

	public $components = array('Paginator','RequestHandler');
  public $helpers = array('PhpExcel'); 

	public function resumenIncidencias() {
		$this->Incidence->recursive = -1;
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		
		$incidenceCount=	$this->Incidence->find('count', array(
			'fields'=>array('Incidence.id'),
			'conditions' => array(
      
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>['CreatingUser'],
      'order'=>'list_order ASC,name ASC',
			'limit'=>($incidenceCount!=0?$incidenceCount:1),
		);

		$incidences = $this->Paginator->paginate('Incidence');
		$this->set(compact('incidences'));
    
    $aco_name="Incidences/editarIncidencia";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
		
	}
  
  public function guardarResumen() {
		$exportData=$_SESSION['resumen'];
		$this->set(compact('exportData'));
	}

	public function verIncidencia($id = null) {
    $userrole = $this->Auth->User('role_id');
		$this->set(compact('userrole'));
  
		if (!$this->Incidence->exists($id)) {
			throw new NotFoundException(__('Invalid incidence'));
		}
    
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		$incidence=$this->Incidence->find('first', [
      'conditions' => ['Incidence.id' => $id],
      'contain'=>['CreatingUser'],
    ]);
    $productionRunConditions=array(
			'ProductionRun.production_run_date >='=> $startDate,
			'ProductionRun.production_run_date <'=> $endDatePlusOne,
		);
		
		$this->Paginator->settings = [
			'contain'=>[
				'Operator',
				'Shift',
				'Machine',
				'RawMaterial',
				'FinishedProduct'=>[
					'ProductProduction'=>[
						'conditions'=>['ProductProduction.application_date <'=> $endDatePlusOne],
					],
				],
				'ProductionMovement',
			],
			'order'=>'production_run_date DESC, production_run_code DESC',
		];
    
    $productionRunConditions['ProductionRun.incidence_id']=$id;
    //pr($productionRunConditions);
    $this->loadModel('ProductionRun');
    $productionRunCount=$this->ProductionRun->find('count', ['conditions' =>$productionRunConditions]);
    $this->Paginator->settings['conditions'] = $productionRunConditions;
    $this->Paginator->settings['limit']=($productionRunCount!=0?$productionRunCount:1);
    $productionRuns=$this->Paginator->paginate('ProductionRun');
    $incidence['ProductionRuns']=$productionRuns;
    
    $this->set(compact('incidence'));
    
    $aco_name="Incidences/editarIncidencia";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
	}

	public function crearIncidencia() {
    $userrole = $this->Auth->User('role_id');
		$this->set(compact('userrole'));
  
		$this->loadModel('User');
		$this->User->recursive=-1;
		$users = $this->User->find('all',array(
			'fields'=>array('User.id','User.username','User.first_name','User.last_name'),
      'conditions'=>array(
        'User.bool_active'=>true,
      ),
		));
		$this->set(compact('users'));
    
		if ($this->request->is('post')) {
			$this->Incidence->create();
      $this->request->data['Incidence']['creating_user_id']=$this->Auth->User('id');
			if ($this->Incidence->save($this->request->data)) {
				$this->Session->setFlash(__('The incidence has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'resumenIncidencias'));
			} 
      else {
				$this->Session->setFlash(__('The incidence could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$creatingUsers = $this->Incidence->CreatingUser->find('list');
		$this->set(compact('creatingUsers'));
	}

	public function editarIncidencia($id = null) {
		if (!$this->Incidence->exists($id)) {
			throw new NotFoundException(__('Invalid incidence'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Incidence->save($this->request->data)) {
				$this->Session->setFlash(__('The incidence has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'resumenIncidencias'));
			} 
      else {
				$this->Session->setFlash(__('The incidence could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
    else {
			$this->request->data = $this->Incidence->find('first', ['conditions' => ['Incidence.id' => $id]]);
		}
		$creatingUsers = $this->Incidence->CreatingUser->find('list');
		$this->set(compact('creatingUsers'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Incidence->id = $id;
		if (!$this->Incidence->exists()) {
			throw new NotFoundException(__('Invalid incidence'));
		}
    $incidence=$this->Incidence->find('first',array(
			'conditions'=>array(
				'Incidence.id'=>$id,
			),
			'contain'=>array(
				'ProductionRun',
			),
		));
    
		$this->request->allowMethod('post', 'delete');
    
    $flashMessage="";
		$boolDeletionAllowed=true;
		
		if (count($incidence['ProductionRun'])>0){
			$boolDeletionAllowed='0';
			$flashMessage.="Esta incidencia tiene ordenes de producción correspondientes.  Para poder eliminar la incidencia, primero hay que eliminar o modificar las ordenes de producción ";
			if (count($incidence['ProductionRun'])==1){
				$flashMessage.=$incidence['ProductionRun'][0]['production_run_code'].".";
			}
			else {
				for ($i=0;$i<count($incidence['ProductionRun']);$i++){
					$flashMessage.=$incidence['ProductionRun'][$i]['production_run_code'];
					if ($i==count($incidence['ProductionRun'])-1){
						$flashMessage.=".";
					}
					else {
						$flashMessage.=" y ";
					}
				}
			}
		}
		
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó la incidencia.";
			$this->Session->setFlash($flashMessage, 'default',array('class' => 'error-message'));
			return $this->redirect(array('action' => 'view',$id));
		}
		else {
			$datasource=$this->Incidence->getDataSource();
			$datasource->begin();	
			try{	
				if (!$this->Incidence->delete($id)) {
					echo "Problema al eliminar la incidencia";
					pr($this->validateErrors($this->Incidence));
					throw new Exception();
				}
						
				$datasource->commit();
					
				$this->loadModel('Deletion');
				$this->Deletion->create();
				$deletionArray=array();
				$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				$deletionArray['Deletion']['reference_id']=$incidence['Incidence']['id'];
				$deletionArray['Deletion']['reference']=$incidence['Incidence']['name'];
				$deletionArray['Deletion']['type']='Incidence';
				$this->Deletion->save($deletionArray);
				
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la incidencia  ".$incidence['Incidence']['name']);
						
				$this->Session->setFlash(__('Se eliminó la incidencia.'),'default',array('class' => 'success'));				
				return $this->redirect(array('action' => 'resumenIncidencias'));
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar el cliente.'), 'default',array('class' => 'error-message'));
				return $this->redirect(array('action' => 'view',$id));
			}
		}
		return $this->redirect(array('action' => 'resumenIncidencias'));
	}
  
  public function reporteIncidencias() {
    $this->Incidence->recursive = -1;
		
    $this->loadModel('ProductionRun');
    
    define('DISPLAY_BY_INCIDENCE','0');
		define('DISPLAY_BY_OPERATOR','1');
		define('DISPLAY_BY_MACHINE','2');
    define('DISPLAY_BY_SHIFT','3');
		
    $displays=array(
			DISPLAY_BY_INCIDENCE=>'Mostrar por incidencia',
			DISPLAY_BY_OPERATOR=>'Mostrar por operador',
			DISPLAY_BY_MACHINE=>'Mostrar por máquina',
      DISPLAY_BY_SHIFT=>'Mostrar por turno',
		);
		$this->set(compact('displays'));
    
    $displayId=DISPLAY_BY_INCIDENCE;
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      
      $displayId=$this->request->data['Report']['display_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		$this->set(compact('startDate','endDate'));
    $this->set(compact('displayId'));
    
    $productionRunConditions=array(
			'ProductionRun.production_run_date >='=> $startDate,
			'ProductionRun.production_run_date <'=> $endDatePlusOne,
		);
		
		$this->Paginator->settings = [
			'contain'=>[
				'Operator',
				'Shift',
				'Machine',
				'RawMaterial',
				'FinishedProduct'=>[
					'ProductProduction'=>[
						'conditions'=>['ProductProduction.application_date <'=> $endDatePlusOne],
					],
				],
				'ProductionMovement',
        'Incidence',
			],
			'order'=>'production_run_date DESC, production_run_code DESC',
		];
    
		switch ($displayId){
      case DISPLAY_BY_INCIDENCE:
        //$incidences=$this->Incidence->find('all',['conditions'=>['bool_active'=>true],'order'=>'list_order,name']);
        $incidences=$this->Incidence->find('all',['order'=>'list_order,name']);
        for ($i=0;$i<count($incidences);$i++){
          $productionRunConditions['ProductionRun.incidence_id']=$incidences[$i]['Incidence']['id'];
          //pr($productionRunConditions);
          $productionRunCount=$this->ProductionRun->find('count', ['conditions' =>$productionRunConditions]);
          $this->Paginator->settings['conditions'] = $productionRunConditions;
          $this->Paginator->settings['limit']=($productionRunCount!=0?$productionRunCount:1);
          $productionRuns=$this->Paginator->paginate('ProductionRun');
          $incidences[$i]['ProductionRuns']=$productionRuns;
          //pr($incidences);
          $this->set(compact('incidences'));
        }
        break;
      case DISPLAY_BY_OPERATOR:
        $this->loadModel('Operator');
        $this->Operator->recursive=-1;
        $operators=$this->Operator->find('all',[
          'conditions'=>['bool_active'=>true],
          'order'=>'name'
        ]);
        for ($i=0;$i<count($operators);$i++){
          $productionRunConditions['ProductionRun.operator_id']=$operators[$i]['Operator']['id'];
          $productionRunConditions['ProductionRun.incidence_id >']=0;
          //pr($productionRunConditions);
          $productionRunCount=$this->ProductionRun->find('count', ['conditions' =>$productionRunConditions]);
          $this->Paginator->settings['conditions'] = $productionRunConditions;
          $this->Paginator->settings['limit']=($productionRunCount!=0?$productionRunCount:1);
          $productionRuns=$this->Paginator->paginate('ProductionRun');
          $operators[$i]['ProductionRuns']=$productionRuns;
          //pr($operators);
          $this->set(compact('operators'));
        }
        break;
      case DISPLAY_BY_MACHINE:
        $this->loadModel('Machine');
        $this->Machine->recursive=-1;
        $machines=$this->Machine->find('all',[
          'conditions'=>['bool_active'=>true],
          'order'=>'name'
        ]);
        for ($i=0;$i<count($machines);$i++){
          $productionRunConditions['ProductionRun.machine_id']=$machines[$i]['Machine']['id'];
          $productionRunConditions['ProductionRun.incidence_id >']=0;
          //pr($productionRunConditions);
          $productionRunCount=$this->ProductionRun->find('count', ['conditions' =>$productionRunConditions]);
          $this->Paginator->settings['conditions'] = $productionRunConditions;
          $this->Paginator->settings['limit']=($productionRunCount!=0?$productionRunCount:1);
          $productionRuns=$this->Paginator->paginate('ProductionRun');
          $machines[$i]['ProductionRuns']=$productionRuns;
          //pr($machines);
          $this->set(compact('machines'));
        }
        break;
      case DISPLAY_BY_SHIFT:
        $this->loadModel('Shift');
        $this->Shift->recursive=-1;
        $shifts=$this->Shift->find('all',['order'=>'name']);
        for ($i=0;$i<count($shifts);$i++){
          $productionRunConditions['ProductionRun.incidence_id']=$shifts[$i]['Shift']['id'];
          $productionRunConditions['ProductionRun.incidence_id >']=0;
          //pr($productionRunConditions);
          $productionRunCount=$this->ProductionRun->find('count', ['conditions' =>$productionRunConditions]);
          $this->Paginator->settings['conditions'] = $productionRunConditions;
          $this->Paginator->settings['limit']=($productionRunCount!=0?$productionRunCount:1);
          $productionRuns=$this->Paginator->paginate('ProductionRun');
          $shifts[$i]['ProductionRuns']=$productionRuns;
          //pr($shifts);
          $this->set(compact('shifts'));
        }
        break;
    }
  }
  
  public function guardarReporteIncidencias($displayId) {
		$exportData=$_SESSION['reporteIncidencias'];
		$this->set(compact('exportData'));
    $this->set(compact('displayId'));
	}
}
