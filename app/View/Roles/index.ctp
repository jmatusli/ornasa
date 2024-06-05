<div class="roles index">
<?php 
	echo "<h2>".__('Roles')."</h2>";
	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
					//echo "<th>".$this->Paginator->sort('id')."</th>";
					echo "<th>".$this->Paginator->sort('name')."</th>";
					echo "<th>".$this->Paginator->sort('description')."</th>";
          echo "<th>".$this->Paginator->sort('list_order')."</th>";
					echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		foreach ($roles as $role){
			echo "<tr>";
				//echo "<td>".h($role['Role']['id'])."</td>";
				echo "<td>".$this->Html->link($role['Role']['name'], array('action' => 'view', $role['Role']['id']))."</td>";
				echo "<td>".h($role['Role']['description'])."</td>";
        echo "<td>".h($role['Role']['list_order'])."</td>";
				echo "<td class='actions'>";
        if ($bool_edit_permission){
					echo $this->Html->link(__('Edit'), array('action' => 'edit', $role['Role']['id'])); 
        }
					//echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $role['Role']['id']), array(), __('Are you sure you want to delete # %s?', $role['Role']['id'])); 
				echo "</td>";
			echo "</tr>";
		}
		echo "</tbody>";
	echo "</table>";
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Nuevo Papel'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
