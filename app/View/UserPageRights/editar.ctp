<div class="userPageRights form">
<?php
	echo $this->Form->create('UserPageRight'); 
	echo '<fieldset>';
		echo '<legend>'.__('Edit User Page Right').'</legend>';
		echo $this->Form->input('id');
		//echo $this->Form->input('assignment_datetime');
		echo $this->Form->input('page_right_id');
		echo $this->Form->input('role_id',['empty'=>[0=>'-- Seleccione un papel --']]);
		echo $this->Form->input('user_id',['empty'=>[0=>'-- Seleccione un papel --']]);
		echo $this->Form->input('bool_allowed');
		echo $this->Form->input('controller',['class'=>'keepcase']);
		echo $this->Form->input('action',['class'=>'keepcase']);
	echo '</fieldset>';
	echo $this->Form->Submit(__('Submit'));
	echo $this->Form->end();
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
    echo '<li>'.$this->Html->link(__('List User Page Rights'), ['action' => 'resumen']).'</li>';
		echo '<br/>';
    echo '<li>'.$this->Html->link(__('List Page Rights'), ['controller' => 'page_rights', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Page Right'), ['controller' => 'page_rights', 'action' => 'crear']).'</li>';
    //echo '<br/>';
		//echo '<li>'.$this->Html->link(__('List Roles'), ['controller' => 'roles', 'action' => 'resumen']).'</li>';
		//echo '<li>'.$this->Html->link(__('New Role'), ['controller' => 'roles', 'action' => 'crear']).'</li>';
		//echo '<br/>';
    //echo '<li>'.$this->Html->link(__('List Users'), ['controller' => 'users', 'action' => 'resumen']).'</li>';
		//echo '<li>'.$this->Html->link(__('New User'), ['controller' => 'users', 'action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
