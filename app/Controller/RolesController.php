<?php
App::uses('AppController', 'Controller');
/**
 * Roles Controller
 *
 * @property Role $Role
 * @property PaginatorComponent $Paginator
 */
class RolesController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->Role->recursive = -1;
    $roleCount=$this->Role->find('count',array(
			'fields'=>array('Role.id'),
		));
		
		$this->Paginator->settings = array(
			'order' => 'list_order',
			'limit'=>$roleCount
		);
    
		$this->set('roles', $this->Paginator->paginate());
	}

	public function view($id = null) {
		if (!$this->Role->exists($id)) {
			throw new NotFoundException(__('Invalid role'));
		}
		$options = [
      'conditions' => ['Role.id'  => $id],
      'contain'=>['User']
    ];
		$this->set('role', $this->Role->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->Role->create();
			if ($this->Role->save($this->request->data)) {
				$this->recordUserAction($this->Role->id,null,null);
				$this->Session->setFlash(__('The role has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The role could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
	}

	public function edit($id = null) {
		if (!$this->Role->exists($id)) {
			throw new NotFoundException(__('Invalid role'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Role->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The role has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The role could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('Role.' . $this->Role->primaryKey => $id));
			$this->request->data = $this->Role->find('first', $options);
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
		$this->Role->id = $id;
		if (!$this->Role->exists()) {
			throw new NotFoundException(__('Invalid role'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Role->delete()) {
			$this->Session->setFlash(__('The role has been deleted.'));
		} else {
			$this->Session->setFlash(__('The role could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
