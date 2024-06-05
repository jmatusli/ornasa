<?php
App::uses('AppController', 'Controller');
/**
 * StockMovementTypes Controller
 *
 * @property StockMovementType $StockMovementType
 * @property PaginatorComponent $Paginator
 */
class StockMovementTypesController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->StockMovementType->recursive = 0;
		$this->set('stockMovementTypes', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->StockMovementType->exists($id)) {
			throw new NotFoundException(__('Invalid stock movement type'));
		}
		$options = array('conditions' => array('StockMovementType.' . $this->StockMovementType->primaryKey => $id));
		$this->set('stockMovementType', $this->StockMovementType->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->StockMovementType->create();
			if ($this->StockMovementType->save($this->request->data)) {
				$this->Session->setFlash(__('The stock movement type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock movement type could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->StockMovementType->exists($id)) {
			throw new NotFoundException(__('Invalid stock movement type'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->StockMovementType->save($this->request->data)) {
				$this->Session->setFlash(__('The stock movement type has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The stock movement type could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('StockMovementType.' . $this->StockMovementType->primaryKey => $id));
			$this->request->data = $this->StockMovementType->find('first', $options);
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
		$this->StockMovementType->id = $id;
		if (!$this->StockMovementType->exists()) {
			throw new NotFoundException(__('Invalid stock movement type'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->StockMovementType->delete()) {
			$this->Session->setFlash(__('The stock movement type has been deleted.'));
		} else {
			$this->Session->setFlash(__('The stock movement type could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
