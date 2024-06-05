<?php
App::uses('AppController', 'Controller');
/**
 * PlantProductionTypes Controller
 *
 * @property PlantProductionType $PlantProductionType
 * @property PaginatorComponent $Paginator
 */
class PlantProductionTypesController extends AppController {

	public $components = ['Paginator'];

  public function resumen() {
		$this->PlantProductionType->recursive = -1;
		
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
		
		$plantProductionTypeCount=	$this->PlantProductionType->find('count', [
			'fields'=>['PlantProductionType.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($plantProductionTypeCount!=0?$plantProductionTypeCount:1),
		] ;

		$plantProductionTypes = $this->Paginator->paginate('PlantProductionType');
		$this->set(compact('plantProductionTypes'));
	}

	public function detalle($id = null) {
		if (!$this->PlantProductionType->exists($id)) {
			throw new NotFoundException(__('Invalid plant production type'));
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
		$options = [
      'conditions' => [
        'PlantProductionType.id' => $id,
      ],
    ];
		$this->set('PlantProductionType', $this->PlantProductionType->find('first', $options));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->PlantProductionType->create();
			if ($this->PlantProductionType->save($this->request->data)) {
				$this->Session->setFlash('Se guardó el lazo entre planta y tipo de producción.', 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash('No se podía guardar el lazo entre planta y tipo de producción.', 'default',['class' => 'error-message'] );
			}
		}
		$plants = $this->PlantProductionType->Plant->find('list');
		$productionTypes = $this->PlantProductionType->ProductionType->find('list');
		$this->set(compact('plants', 'productionTypes'));
	}


	public function editar($id = null) {
		if (!$this->PlantProductionType->exists($id)) {
			throw new NotFoundException(__('Invalid plant production type'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->PlantProductionType->save($this->request->data)) {
				$this->Session->setFlash('Se editó el lazo entre planta y tipo de producción.', 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} else {
				$this->Session->setFlash('No se podía editar el lazo entre planta y tipo de producción.', 'default',['class' => 'error-message']);
			}
		} else {
			$options = ['conditions' => ['PlantProductionType.id' => $id]];
			$this->request->data = $this->PlantProductionType->find('first', $options);
		}
		$plants = $this->PlantProductionType->Plant->find('list');
		$productionTypes = $this->PlantProductionType->ProductionType->find('list');
		$this->set(compact('plants', 'productionTypes'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->PlantProductionType->id = $id;
		if (!$this->PlantProductionType->exists()) {
			throw new NotFoundException(__('Invalid plant production type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PlantProductionType->delete()) {
			$this->Session->setFlash('Se eliminó el lazo entre planta y tipo de producción.', 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash('No se podía eliminar el lazo entre planta y tipo de producción.', 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
