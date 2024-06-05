<div class="productionRunTypes view">
<h2><?php echo __('Production Run Type'); ?></h2>
	<dl>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($productionRunType['ProductionRunType']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Description'); ?></dt>
		<dd>
			<?php echo h($productionRunType['ProductionRunType']['description']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
	if ($bool_edit_permission){
		echo "<li>".$this->Html->link(__('Edit Production Run Type'), array('action' => 'edit', $productionRunType['ProductionRunType']['id']))."</li>";
		echo "<br/>";
	}
	if ($bool_delete_permission) { 
		echo "<li>".$this->Form->postLink(__('Eliminar Tipo de Producción'), array('action' => 'delete', $productionRunType['ProductionRunType']['id']), array(), __('Está seguro que quiere eliminar tipo de producción %s?', $productionRunType['ProductionRunType']['name']))."</li>";
		echo "<br/>";
	}
	echo "<li>".$this->Html->link(__('List Production Run Types'), array('action' => 'index'))."</li>";
	if ($bool_add_permission) { 
		echo "<li>".$this->Html->link(__('New Production Run Type'), array('action' => 'add'))."</li>";
	}
	echo "<br/>";
	if ($bool_productionrun_index_permission) { 
		echo "<li>".$this->Html->link(__('List Production Runs'), array('controller' => 'production_runs', 'action' => 'index'))."</li>";
	}
	if ($bool_productionrun_add_permission) { 
		echo "<li>".$this->Html->link(__('New Production Run'), array('controller' => 'production_runs', 'action' => 'add'))."</li>";
	} 
	echo "</ul>";
?>	
</div>

<div class="related">
	<?php if (!empty($productionRunType['ProductionRun'])): ?>
	<h3><?php echo __('Related Production Runs for Production Run Type'); ?></h3>
	
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Name'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($productionRunType['ProductionRun'] as $productionRun): ?>
		<tr>
			<td><?php echo $productionRun['production_run_code']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'production_runs', 'action' => 'view', $product['id'])); ?>
				<? if ($userrole!=ROLE_FOREMAN){ ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'production_runs', 'action' => 'edit', $product['id'])); ?>
				<? } ?>
				<?php // echo $this->Form->postLink(__('Delete'), array('controller' => 'production_runs', 'action' => 'delete', $product['id']), array(), __('Are you sure you want to delete # %s?', $product['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<? if ($userrole!=ROLE_FOREMAN){ ?>
			<li><?php echo $this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add')); ?> </li>
			<? } ?>
		</ul>
	</div>
</div>