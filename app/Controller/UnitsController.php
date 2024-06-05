<?php
App::uses('AppController', 'Controller');

class UnitsController extends AppController {


	public $components = ['Paginator'];

public function resumen() {
		$this->Unit->recursive = -1;
		
		$unitCount=	$this->Unit->find('count', [
			'fields'=>['Unit.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($unitCount!=0?$unitCount:1),
		] ;

		$units = $this->Paginator->paginate('Unit');
		$this->set(compact('units'));
	}

	public function detalle($id = null) {
		if (!$this->Unit->exists($id)) {
			throw new NotFoundException(__('Invalid unit'));
		}
		
		$options = [
      'conditions' => [
        'Unit.id' => $id,
      ],
    ];
		$this->set('unit', $this->Unit->find('first', $options));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->Unit->create();
			if ($this->Unit->save($this->request->data)) {
				$this->Session->setFlash(__('The unit has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash(__('The unit could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
			}
		}
	}


	public function editar($id = null) {
		if (!$this->Unit->exists($id)) {
			throw new NotFoundException(__('Invalid unit'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this->Unit->save($this->request->data)) {
				$this->Session->setFlash(__('The unit has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The unit could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('Unit.id' => $id));
			$this->request->data = $this->Unit->find('first', $options);
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
		$this->Unit->id = $id;
		if (!$this->Unit->exists()) {
			throw new NotFoundException(__('Invalid unit'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Unit->delete()) {
			$this->Session->setFlash(__('The unit has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The unit could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
