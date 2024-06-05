<?php
/**
 * Bake Template for Controller action generation.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Console.Templates.default.actions
 * @since         CakePHP(tm) v 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>


  public function <?php echo $admin ?>resumen() {
		$this-><?php echo $currentModelName ?>->recursive = -1;
		
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
		
		$<?php echo $singularName; ?>Count=	$this-><?php echo $currentModelName; ?>->find('count', [
			'fields'=>['<?php echo $currentModelName; ?>.id'],
			'conditions' => [
			],
		]);
		
		$this->Paginator->settings = [
			'conditions' => [
			],
			'contain'=>[				
			],
			'limit'=>($<?php echo $singularName; ?>Count!=0?$<?php echo $singularName; ?>Count:1),
		] ;

		$<?php echo $pluralName ?> = $this->Paginator->paginate('<?php echo $currentModelName; ?>');
		$this->set(compact('<?php echo $pluralName ?>'));
	}

	public function <?php echo $admin ?>detalle($id = null) {
		if (!$this-><?php echo $currentModelName; ?>->exists($id)) {
			throw new NotFoundException(__('Invalid <?php echo strtolower($singularHumanName); ?>'));
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
        '<?php echo $currentModelName; ?>.id' => $id,
      ],
    ];
		$this->set('<?php echo $singularName; ?>', $this-><?php echo $currentModelName; ?>->find('first', $options));
	}

<?php $compact = []; ?>
	public function <?php echo $admin ?>add() {
		if ($this->request->is('post')) {
			$this-><?php echo $currentModelName; ?>->create();
			if ($this-><?php echo $currentModelName; ?>->save($this->request->data)) {
<?php if ($wannaUseSession): ?>
				$this->Session->setFlash(__('The <?php echo strtolower($singularHumanName); ?> has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(['action' => 'resumen'] );
			} 
      else {
				$this->Session->setFlash(__('The <?php echo strtolower($singularHumanName); ?> could not be saved. Please, try again.'), 'default',['class' => 'error-message'] );
<?php else: ?>
				return $this->flash(__('The <?php echo strtolower($singularHumanName); ?> has been saved.', 'default',['class' => 'success']), ['action' => 'resumen']);
<?php endif; ?>
			}
		}
<?php
	foreach (array('belongsTo', 'hasAndBelongsToMany') as $assoc):
		foreach ($modelObj->{$assoc} as $associationName => $relation):
			if (!empty($associationName)):
				$otherModelName = $this->_modelName($associationName);
				$otherPluralName = $this->_pluralName($associationName);
				echo "\t\t\${$otherPluralName} = \$this->{$currentModelName}->{$otherModelName}->find('list');\n";
				$compact[] = "'{$otherPluralName}'";
			endif;
		endforeach;
	endforeach;
	if (!empty($compact)):
		echo "\t\t\$this->set(compact(".join(', ', $compact)."));\n";
	endif;
?>
	}

<?php $compact = []; ?>

	public function <?php echo $admin; ?>edit($id = null) {
		if (!$this-><?php echo $currentModelName; ?>->exists($id)) {
			throw new NotFoundException(__('Invalid <?php echo strtolower($singularHumanName); ?>'));
		}
		if ($this->request->is(['post', 'put'])) {
			if ($this-><?php echo $currentModelName; ?>->save($this->request->data)) {
<?php if ($wannaUseSession): ?>
				$this->Session->setFlash(__('The <?php echo strtolower($singularHumanName); ?> has been saved.'), 'default',['class' => 'success']);
				return $this->redirect(array('action' => 'resumen'));
			} else {
				$this->Session->setFlash(__('The <?php echo strtolower($singularHumanName); ?> could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
<?php else: ?>
				return $this->flash(__('The <?php echo strtolower($singularHumanName); ?> has been saved.'), array('action' => 'resumen'), 'default',['class' => 'success']);
<?php endif; ?>
			}
		} else {
			$options = array('conditions' => array('<?php echo $currentModelName; ?>.id' => $id));
			$this->request->data = $this-><?php echo $currentModelName; ?>->find('first', $options);
		}
<?php
		foreach (array('belongsTo', 'hasAndBelongsToMany') as $assoc):
			foreach ($modelObj->{$assoc} as $associationName => $relation):
				if (!empty($associationName)):
					$otherModelName = $this->_modelName($associationName);
					$otherPluralName = $this->_pluralName($associationName);
					echo "\t\t\${$otherPluralName} = \$this->{$currentModelName}->{$otherModelName}->find('list');\n";
					$compact[] = "'{$otherPluralName}'";
				endif;
			endforeach;
		endforeach;
		if (!empty($compact)):
			echo "\t\t\$this->set(compact(".join(', ', $compact)."));\n";
		endif;
	?>
	}

/**
 * <?php echo $admin ?>delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function <?php echo $admin; ?>delete($id = null) {
		$this-><?php echo $currentModelName; ?>->id = $id;
		if (!$this-><?php echo $currentModelName; ?>->exists()) {
			throw new NotFoundException(__('Invalid <?php echo strtolower($singularHumanName); ?>'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this-><?php echo $currentModelName; ?>->delete()) {
<?php if ($wannaUseSession): ?>
			$this->Session->setFlash(__('The <?php echo strtolower($singularHumanName); ?> has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The <?php echo strtolower($singularHumanName); ?> could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
<?php else: ?>
			return $this->flash(__('The <?php echo strtolower($singularHumanName); ?> has been deleted.'), ['action' => 'resumen']);
		} else {
			return $this->flash(__('The <?php echo strtolower($singularHumanName); ?> could not be deleted. Please, try again.'), ['action' => 'index']);
		}
<?php endif; ?>
	}
