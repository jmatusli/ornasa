<?php
App::uses('AppController', 'Controller');
/**
 * Currencies Controller
 *
 * @property Currency $Currency
 * @property PaginatorComponent $Paginator
 */
class CurrenciesController extends AppController {

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
		$this->Currency->recursive = 0;
		$this->set('currencies', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Currency->exists($id)) {
			throw new NotFoundException(__('Invalid currency'));
		}
		$options = array('conditions' => array('Currency.' . $this->Currency->primaryKey => $id));
		$this->set('currency', $this->Currency->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Currency->create();
			if ($this->Currency->save($this->request->data)) {
				$this->recordUserAction($this->Currency->id,null,null);
				$this->Session->setFlash(__('The currency has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The currency could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
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
		if (!$this->Currency->exists($id)) {
			throw new NotFoundException(__('Invalid currency'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Currency->save($this->request->data)) {
				$this->recordUserAction($this->Currency->id,null,null);
				$this->Session->setFlash(__('The currency has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The currency could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('Currency.' . $this->Currency->primaryKey => $id));
			$this->request->data = $this->Currency->find('first', $options);
		}
		$this->set(compact('id'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Currency->id = $id;
		if (!$this->Currency->exists()) {
			throw new NotFoundException(__('Invalid currency'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Currency->delete()) {
			$this->Session->setFlash(__('The currency has been deleted.'));
		} else {
			$this->Session->setFlash(__('The currency could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
