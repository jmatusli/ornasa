<?php
App::uses('AppController', 'Controller');

class ProductionRunTypesController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->ProductionRunType->recursive = -1;
		$productionRunTypeCount=	$this->ProductionRunType->find('count', array(
			'fields'=>array('ProductionRunType.id'),
		));
		
		$this->Paginator->settings = array(
			'order' => array('ProductionRunType.name'=> 'ASC'),
			'limit'=>$productionRunTypeCount,
		);
		$productionRunTypes = $this->Paginator->paginate('ProductionRunType');
		$this->set(compact('productionRunTypes'));
		
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
	}

	public function view($id = null) {
		if (!$this->ProductionRunType->exists($id)) {
			throw new NotFoundException(__('Invalid production run type'));
		}
		$this->ProductionRunType->recursive=-1;
		$productionRunType=$this->ProductionRunType->find('first', array(
			'conditions' => array(
				'ProductionRunType.id'=> $id,
			),
			'contain'=>array(
				'ProductionRun',
			),
		));
		$productionRunType=$this->ProductionRunType->find('first', array(
			'conditions' => array(
				'ProductionRunType.id'=> $id,
			),
		));
		$this->set(compact('productionRunType'));
		
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->ProductionRunType->create();
			if ($this->ProductionRunType->save($this->request->data)) {
				$this->recordUserAction($this->ProductionRunType->id,null,null);
				$this->Session->setFlash(__('The production run type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The production run type could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
	}

	public function edit($id = null) {
		if (!$this->ProductionRunType->exists($id)) {
			throw new NotFoundException(__('Invalid production run type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProductionRunType->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The production run type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The production run type could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$this->ProductionRunType->recursive=-1;
			$this->request->data = $this->ProductionRunType->find('first', array(
				'conditions' => array(
					'ProductionRunType.id'=> $id,
				),
			));
		}
		
		$aco_name="ProductionRuns/index";		
		$bool_productionrun_index_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_index_permission'));
		$aco_name="ProductionRuns/add";		
		$bool_productionrun_add_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_productionrun_add_permission'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ProductionRunType->id = $id;
		if (!$this->ProductionRunType->exists()) {
			throw new NotFoundException(__('Tipo de producción inválido'));
		}
		$productionRunType=$this->ProductionRunType->find('first',array(
			'conditions'=>array(
				'ProductionRunType.id'=>$id,
			),
			'contain'=>array(
				'ProductionRun',
			),
		));

		$flashMessage="";
		$boolDeletionAllowed=true;
		
		if (!empty($productionRunType['ProductionRun'])){
			$boolDeletionAllowed='0';
			$flashMessage.="Este tipo de producción tiene procesos de producción correspondientes.  Para poder eliminar el tipo de producción, primero hay que eliminar o modificar los procesos de producción ";
			
			if (count($productionRunType['ProductionRun'])==1){
				$flashMessage.=$productionRunType['ProductionRun'][0]['production_run_code'].".";
			}
			else {
				for ($i=0;$i<count($productionRunType['ProductionRun']);$i++){
					$flashMessage.=$productionRunType['ProductionRun'][$i]['production_run_code'];
					if ($i==count($productionRunType['ProductionRun'])-1){
						$flashMessage.=".";
					}
					else {
						$flashMessage.=" y ";
					}
				}
			}
		}
		if (!$boolDeletionAllowed){
			$flashMessage.=" No se eliminó el tipo de producción.";
			$this->Session->setFlash($flashMessage, 'default',array('class' => 'error-message'));
			return $this->redirect(array('action' => 'view',$id));
		}
		else {
			$datasource=$this->ProductionRunType->getDataSource();
			$datasource->begin();	
			try {
				//delete all stockMovements, stockItems and stockItemLogs
				if (!empty($productionRunType['ProductionRun'])){
					foreach ($productionRunType['ProductionRun'] as $productionRun){
						echo "starting to delete the production run ".$productionRun['id']."<br/>";
						if (!$this->ProductionRunType->ProductionRun->delete($productionRun['id'])) {
							
							echo "Problema al eliminar el proceso de producción";
							pr($this->validateErrors($this->ProductionRunType->ProductionRun));
							throw new Exception();
						}
					}
				}
				
				if (!$this->ProductionRunType->delete($id)) {
					echo "Problema al eliminar el tipo de producción";
					pr($this->validateErrors($this->ProductionRunType));
					throw new Exception();
				}
						
				$datasource->commit();
				
				//$this->loadModel('Deletion');
				//$this->Deletion->create();
				//$deletionArray=array();
				//$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
				//$deletionArray['Deletion']['reference_id']=$productionRunType['ProductionRunType']['id'];
				//$deletionArray['Deletion']['reference']=$productionRunType['ProductionRunType']['name'];
				//$deletionArray['Deletion']['type']='ProductionRunType';
				//$this->Deletion->save($deletionArray);
						
				$this->recordUserActivity($this->Session->read('User.username'),"Se eliminó el tipo de producción ".$productionRunType['ProductionRunType']['name']);
						
				$this->Session->setFlash(__('Se eliminó el tipo de producción '.$productionRunType['ProductionRunType']['name'].'.'),'default',array('class' => 'success'));				
				return $this->redirect(array('action' => 'index'));
			}
			catch(Exception $e){
				$datasource->rollback();
				pr($e);
				$this->Session->setFlash(__('No se podía eliminar la orden de venta.'), 'default',array('class' => 'error-message'));
				//return $this->redirect(array('action' => 'view',$id));
			}
		}
	}
}
