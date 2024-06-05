<?php
App::uses('AppController', 'Controller');

class PlantsController extends AppController {

	public $components = array('Paginator');

	public function resumen() {
    
    
		$this->Plant->recursive = -1;
		$this->set('plants', $this->Paginator->paginate());
	}

	public function detalle($id = null) {
		if (!$this->Plant->exists($id)) {
			throw new NotFoundException(__('Invalid plant'));
		}
		
    $this->loadModel('PlantProductionResultCode');
    $this->loadModel('PlantProductionType');
    $this->loadModel('Role');
    $this->loadModel('UserPlant');
    
		$startDate = null;
		$endDate = null;
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		elseif (!empty($this->params['named']['sort'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date( "Y-m-d", strtotime( $endDate."+1 days" ) );
		}

		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
				
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
    $this->set(compact('startDate','endDate'));
    
		$options = [
			'conditions' => [
				'Plant.id'=> $id,
			],
			'contain'=>[
        'Machine',
        'Operator',
        'ProductionRun'=>[
          'conditions'=>[
            'ProductionRun.production_run_date >='=>$startDate,
            'ProductionRun.production_run_date <'=>$endDatePlusOne,
          ],
          'RawMaterial',
          'FinishedProduct',
          'ProductionMovement'=>['StockItem'],
          'Machine'=>[
            'conditions'=>['Machine.bool_active'=>true],
          ],
          'Operator'=>[
            'conditions'=>['Operator.bool_active'=>true],
          ],
        ]
      ],
		];
		$plant=$this->Plant->find('first', $options);
		//pr($plant);
		$this->set(compact('plant'));
        
    $plantProductionResultCodes=$this->PlantProductionResultCode->getProductionResultCodesForPlant($id);
    $this->set(compact('plantProductionResultCodes'));
    
    $plantProductionTypes=$this->PlantProductionType->getProductionTypesForPlant($id);
    $this->set(compact('plantProductionTypes'));
    
    $plantUsers=$this->UserPlant->getUsersForPlant($id);
    $this->set(compact('plantUsers'));
    //pr($plantUsers);  
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->Plant->create();
			if ($this->Plant->save($this->request->data)) {
				$this->recordUserAction($this->Plant->id,null,null);
				$this->Session->setFlash(__('The plant has been saved.'),'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
			else {
				$this->Session->setFlash(__('The plant could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		}
	}

	public function editar($id = null) {
		if (!$this->Plant->exists($id)) {
			throw new NotFoundException(__('Invalid plant'));
		}
		$this->Plant->recursive=-1;
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Plant->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The plant has been saved.'),'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
			else {
				$this->Session->setFlash(__('The plant could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} 
		else {
			$options = ['conditions' => ['Plant.id' => $id]];
			$this->request->data = $this->Plant->find('first', $options);
		}
	}

	public function delete($id = null) {
		$this->Plant->id = $id;
		if (!$this->Plant->exists()) {
			throw new NotFoundException(__('Invalid plant'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Plant->delete()) {
			$this->Session->setFlash(__('The plant has been deleted.'));
		} 
		else {
			$this->Session->setFlash(__('The plant could not be deleted. Please, try again.'));
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
