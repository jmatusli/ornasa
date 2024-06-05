<div class="userPageRights view">
<?php 
	echo '<h2>'.__('User Page Right').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Permission Datetime').'</dt>';
		echo '<dd>'.h($userPageRight['UserPageRight']['permission_datetime']).'</dd>';
		echo '<dt>'.__('Page Right').'</dt>';
		echo '<dd>'.$this->Html->link($userPageRight['PageRight']['name'], ['controller' => 'page_rights', 'action' => 'detalle', $userPageRight['PageRight']['id']]).'</dd>';
		echo '<dt>'.__('Role').'</dt>';
		echo '<dd>'.$this->Html->link($userPageRight['Role']['name'], ['controller' => 'roles', 'action' => 'detalle', $userPageRight['Role']['id']]).'</dd>';
		echo '<dt>'.__('User').'</dt>';
		echo '<dd>'.$this->Html->link($userPageRight['User']['userName'], ['controller' => 'users', 'action' => 'detalle', $userPageRight['User']['id']]).'</dd>';
		echo '<dt>'.__('Bool Allowed').'</dt>';
		echo '<dd>'.h($userPageRight['UserPageRight']['bool_allowed']).'</dd>';
		echo '<dt>'.__('Controller').'</dt>';
		echo '<dd>'.h($userPageRight['UserPageRight']['controller']).'</dd>';
		echo '<dt>'.__('Action').'</dt>';
		echo '<dd>'.h($userPageRight['UserPageRight']['action']).'</dd>';
	echo '</dl>';
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit User Page Right'), ['action' => 'editar', $userPageRight['UserPageRight']['id']]).'</li>';
	echo '<br/>';
		}
		echo '<li>'.$this->Html->link(__('List User Page Rights'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New User Page Right'), ['action' => 'crear']).'</li>';
		echo '<br/>';
		echo '<li>'.$this->Html->link(__('List Page Rights'), ['controller' => 'page_rights', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Page Right'), ['controller' => 'page_rights', 'action' => 'crear']).'</li>';
		echo '<li>'.$this->Html->link(__('List Roles'), ['controller' => 'roles', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Role'), ['controller' => 'roles', 'action' => 'crear']).'</li>';
		echo '<li>'.$this->Html->link(__('List Users'), ['controller' => 'users', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New User'), ['controller' => 'users', 'action' => 'crear']).'</li>';
	echo '</ul>';
?> 
</div>
<link href="https://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css">
<div style="float:left;width:100%;">
<?php 
		if ($bool_delete_permission){
			echo $this->Form->postLink(__($this->Html->tag('i', '', ['class' => 'glyphicon glyphicon-fire']).' '.'Eliminar Naturaleza'), ['action' => 'delete', $productNature['ProductNature']['id']], ['class' => 'btn btn-danger btn-sm','style'=>'text-decoration:none;','escape'=>false], __('Está seguro que quiere eliminar la naturaleza de producto # %s?  PELIGRO, NO SE PUEDE DESHACER ESTA OPERACIÓN.  LOS DATOS DESPARECERÁN DE LA BASE DE DATOS!!!', $productNature['ProductNature']['name']));
	echo '<br/>';
		}
?>
</div>