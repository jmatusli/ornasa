<div class="constants view">
<?php 
	echo "<h2>".__('Constant')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Constant')."</dt>";
		echo "<dd>".h($constant['Constant']['constant'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".h($constant['Constant']['description'])."</dd>";
		echo "<dt>".__('Value')."</dt>";
		echo "<dd>".h($constant['Constant']['value'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
    if ($bool_edit_permission){
      echo "<li>".$this->Html->link(__('Edit Constant'), array('action' => 'edit', $constant['Constant']['id']))."</li>";
      echo "<br/>";
    }
    if ($bool_delete_permission){
      echo "<li>".$this->Form->postLink(__('Eliminar Constante'), array('action' => 'delete', $constant['Constant']['id']), array(), __('Está seguro que quiere eliminar el constante %s?', $constant['Constant']['constant']))."</li>";
      echo "<br/>";
    }
		echo "<li>".$this->Html->link(__('List Constants'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Constant'), array('action' => 'add'))."</li>";
		echo "<br/>";
	echo "</ul>";
?> 
</div>
