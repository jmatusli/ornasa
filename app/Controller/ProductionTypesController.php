<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');
class ProductionTypesController extends AppController {

	public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel');

  public function resumen() {
		$this->ProductionType->recursive = -1;
		/*
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
		*/
		$productionTypeCount=	$this->ProductionType->find('count', [
			'fields'=>['ProductionType.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($productionTypeCount!=0?$productionTypeCount:1),
      'order'=>'ProductionType.list_order',
		] ;

		$productionTypes = $this->Paginator->paginate('ProductionType');
		$this->set(compact('productionTypes'));
	}

  public function guardarResumenTiposProduccion($fileName) {
		$exportData=$_SESSION['resumenTiposProducción'];
		$this->set(compact('exportData','fileName'));
	}
  
	public function detalle($id = null) {
		if (!$this->ProductionType->exists($id)) {
			throw new NotFoundException(__('Invalid production type'));
		}
    
    $this->loadModel('UserPlant');
    $this->loadModel('Plant');
    $this->loadModel('PlantProductionType');
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
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
		$options = [
      'conditions' => [
        'ProductionType.id' => $id,
      ],
    ];
		$this->set('productionType', $this->ProductionType->find('first', $options));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    $productionTypePlants=[];
    foreach ($plants as $plantId=>$plantName){
      if ($this->PlantProductionType->hasPlant($id,$plantId)){
        $productionTypePlants[$plantId]=$plantName;
      }
    }
    //pr($productionTypePlants);
    $this->set(compact('productionTypePlants'));
    
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->ProductionType->create();
			if ($this->ProductionType->save($this->request->data)) {
				$this->Session->setFlash(__('The production type has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} else {
				$this->Session->setFlash(__('The production type could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
	}

	public function editar($id = null) {
    if (!$this->ProductionType->exists($id)) {
			throw new NotFoundException(__('Invalid production type'));
		}
    
    $this->loadModel('UserPlant');
    $this->loadModel('Plant');
    $this->loadModel('PlantProductionType');
    
    $loggedUserId=$userId=$this->Auth->User('id');
		$this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
		$this->set(compact('userRoleId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    $productionTypePlants=[];
    foreach ($plants as $plantId=>$plantName){
      if ($this->PlantProductionType->hasPlant($id,$plantId)){
        $productionTypePlants[$plantId]=$plantName;
      }
    }
    //pr($productionTypePlants);
    $this->set(compact('productionTypePlants'));
    
		
		if ($this->request->is(['post', 'put'])) {
      $plantAssigned='0';
      foreach ($this->request->data['Plant'] as $plantId=>$plantData){
        $plantAssigned = $plantAssigned || $plantData['bool_assigned'];
      }
      
      if (!$plantAssigned){
        $this->Session->setFlash('El tipo de producción se tiene que asociar con por lo menos una planta.', 'default',['class' => 'error-message']);
      }
      else {
        $successMessage='Se editó el tipo de producción '.$this->request->data['Product']['name'].'.  ';
        
				$datasource=$this->ProductionType->getDataSource();
				$datasource->begin();
				try {
					$this->ProductionType->id=$id;
					if (!$this->ProductionType->save($this->request->data)) {
						echo "problema al editar el tipo de producción";
						pr($this->validateErrors($this->ProductionType));
						throw new Exception();
					} 
					$productionTypeId=$this->ProductionType->id;
          
          foreach ($this->request->data['Plant'] as $plantId=>$plantData){
            if (
              (in_array($plantId,array_keys($productionTypePlants)) && !$plantData['bool_assigned']) || 
              (!in_array($plantId,array_keys($productionTypePlants)) && $plantData['bool_assigned']) 
            ){
              $plantProductionTypeArray=[
                'PlantProductionType'=>[
                  'assignment_datetime'=>date('Y-m-d H:i:s'),
                  'plant_id'=>$plantId,
                  'production_type_id'=>$productionTypeId,
                  'bool_assigned'=>$plantData['bool_assigned'],
                ]
              ];  
              $this->PlantProductionType->create();
              if (!$this->PlantProductionType->save($plantProductionTypeArray)) {
                echo "Problema guardando las asociaciones del tipo de producción ".$productionTypeId.' con planta '.$plantId;
                pr($this->validateErrors($this->PlantProductionType));
                throw new Exception();
              } 
            }            
          }

					
          $datasource->commit();
					
					$this->recordUserAction($this->ProductionType->id,null,null);
					$this->Session->setFlash($successMessage,'default',['class' => 'success']);
          
          return $this->redirect(['action' => 'detalle',$id]);  
				} 		
				catch(Exception $e){
					$datasource->rollback();
					pr($e);					
					$this->Session->setFlash(__('The production type could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
				}
      }
		} 
    else {
			$options = ['conditions' => ['ProductionType.id' => $id]];
			$this->request->data = $this->ProductionType->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ProductionType->id = $id;
		if (!$this->ProductionType->exists()) {
			throw new NotFoundException(__('Invalid production type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProductionType->delete()) {
			$this->Session->setFlash(__('The production type has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The production type could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
