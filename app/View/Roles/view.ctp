<div class="roles view">
<h2><?php echo __('Role'); ?></h2>
	<dl>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($role['Role']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($role['Role']['description']); ?>
			&nbsp;
		</dd>
    <dt><?php echo __('List Order'); ?></dt>
		<dd>
			<?php echo h($role['Role']['list_order']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php 
				$date=new DateTime($role['Role']['created']);
				echo $date->format('d-m-Y'); 
			?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php 
				$date=new DateTime($role['Role']['modified']);
				echo $date->format('d-m-Y'); 
			?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
  <?php
    if ($bool_edit_permission){
      echo "<li>".$this->Html->link(__('Edit Role'), array('action' => 'edit', $role['Role']['id']))."</li>";
    }
  ?>
		<!--li><?php //echo $this->Form->postLink(__('Delete Role'), array('action' => 'delete', $role['Role']['id']), array(), __('Are you sure you want to delete # %s?', $role['Role']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Roles'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Role'), array('action' => 'add')); ?> </li>
		<br/>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	
	<?php if (!empty($role['User'])): ?>
	<h3><?php echo __('Usuarios con este Papel'); ?></h3>
	<table cellpadding = '0' cellspacing = '0'>
		<thead>
			<tr>
				<th><?php echo __('Username'); ?></th>
				<th><?php echo __('First Name'); ?></th>
				<th><?php echo __('Last Name'); ?></th>
				<th><?php echo __('Email'); ?></th>
				<th><?php echo __('Phone'); ?></th>
				<th><?php echo __('Created'); ?></th>
				<th><?php echo __('Modified'); ?></th>
				<th class="actions"><?php echo __('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($role['User'] as $user): ?>
			<tr>
				<td><?php echo $user['username']; ?></td>
				<td><?php echo $user['first_name']; ?></td>
				<td><?php echo $user['last_name']; ?></td>
				<td><?php echo $user['email']; ?></td>
				<td><?php echo $user['phone']; ?></td>
				<?php $date=new DateTime($user['created']); ?>
				<td><?php echo $date->format('d-m-Y'); ?></td>
				<?php $date=new DateTime($user['modified']); ?>
				<td><?php echo $date->format('d-m-Y'); ?></td>
				<td class="actions">
					<?php echo $this->Html->link(__('View'), array('controller' => 'users', 'action' => 'view', $user['id'])); ?>
					<?php echo $this->Html->link(__('Edit'), array('controller' => 'users', 'action' => 'edit', $user['id'])); ?>
					<?php // echo $this->Form->postLink(__('Delete'), array('controller' => 'users', 'action' => 'delete', $user['id']), array(), __('Are you sure you want to delete # %s?', $user['id'])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
