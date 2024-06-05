<?php
App::uses('AppController', 'Controller');
/**
 * ProductionResultCodes Controller
 *
 * @property ProductionResultCode $ProductionResultCode
 * @property PaginatorComponent $Paginator
 */
class ProductionResultCodesController extends AppController {

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
		$this->ProductionResultCode->recursive = 0;
		$this->set('productionResultCodes', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->ProductionResultCode->exists($id)) {
			throw new NotFoundException(__('Invalid production result code'));
		}
		$options = array('conditions' => array('ProductionResultCode.' . $this->ProductionResultCode->primaryKey => $id));
		$this->set('productionResultCode', $this->ProductionResultCode->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->ProductionResultCode->create();
			if ($this->ProductionResultCode->save($this->request->data)) {
				$this->recordUserAction($this->ProductionResultCode->id,"add",null);
				$this->Session->setFlash(__('The production result code has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The production result code could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
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
		if (!$this->ProductionResultCode->exists($id)) {
			throw new NotFoundException(__('Invalid production result code'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ProductionResultCode->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The production result code has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The production result code could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('ProductionResultCode.' . $this->ProductionResultCode->primaryKey => $id));
			$this->request->data = $this->ProductionResultCode->find('first', $options);
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
		$this->ProductionResultCode->id = $id;
		if (!$this->ProductionResultCode->exists()) {
			throw new NotFoundException(__('Invalid production result code'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ProductionResultCode->delete()) {
			$this->Session->setFlash(__('The production result code has been deleted.'));
		} else {
			$this->Session->setFlash(__('The production result code could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
