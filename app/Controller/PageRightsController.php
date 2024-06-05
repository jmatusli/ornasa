<?php
App::uses('AppController', 'Controller');
class PageRightsController extends AppController {

	public $components = array('Paginator');

	public function resumen() {
		$this->PageRight->recursive = -1;
		
		$pageRightCount=	$this->PageRight->find('count', [
			'fields'=>['PageRight.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [	
			],
			'contain'=>[				
			],
			'limit'=>($pageRightCount!=0?$pageRightCount:1),
		];

		$pageRights = $this->Paginator->paginate('PageRight');
		$this->set(compact('pageRights'));
    
    $aco_name="PageRights/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
	}

	public function detalle($id = null) {
		if (!$this->PageRight->exists($id)) {
			throw new NotFoundException(__('Invalid page permission'));
		}
		
		$options = [
      'conditions' => ['PageRight.id' => $id],
      'contain'=>[
      
      ],
    ];
		$this->set('pageRight', $this->PageRight->find('first', $options));
    
    $aco_name="PageRights/editar";		
		$bool_edit_permission=$this->hasPermission($this->Auth->User('id'),$aco_name);
		$this->set(compact('bool_edit_permission'));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->PageRight->create();
			if ($this->PageRight->save($this->request->data)) {
				$this->Session->setFlash('Se guardó el permiso individual.','default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
      else {
				$this->Session->setFlash('No se podía guardar el permiso individual.','default',['class' => 'error-message']);
			}
		}
	}

	public function editar($id = null) {
		if (!$this->PageRight->exists($id)) {
			throw new NotFoundException(__('Invalid page permission'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->PageRight->save($this->request->data)) {
				$this->Session->setFlash('Se guardó el permiso individual.','default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen']);
			} 
      else {
				$this->Session->setFlash('No se podía guardar el permiso individual.','default',['class' => 'error-message']);
			}
		} else {
			$options = [
        'conditions' => ['PageRight.id' => $id],
      ];
			$this->request->data = $this->PageRight->find('first', $options);
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
		$this->PageRight->id = $id;
		if (!$this->PageRight->exists()) {
			throw new NotFoundException(__('Invalid page permission'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->PageRight->delete()) {
			$this->Session->setFlash('Se eliminó el permiso individual');
		} else {
			$this->Session->setFlash(__('No se podía eliminar el permiso individual.'));
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
