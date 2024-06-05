<div class="pageRights view">
<?php 
	echo "<h2>".__('Page Right')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Code')."</dt>";
		echo "<dd>".h($pageRight['PageRight']['code'])."</dd>";
    echo "<dt>Valor por defecto</dt>";
		echo "<dd>".($pageRight['PageRight']['bool_default_assignment']?'Activado':'Desactivado')."</dd>";
    echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($pageRight['PageRight']['name'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		echo "<dd>".(empty($pageRight['PageRight']['description'])?"-":$pageRight['PageRight']['description'])."</dd>";
    echo "<dt>".__('Pages')."</dt>";
		echo "<dd>".(empty($pageRight['PageRight']['pages'])?"-":$pageRight['PageRight']['pages'])."</dd>";
		echo "<dt>".__('List Order')."</dt>";
		echo "<dd>".h($pageRight['PageRight']['list_order'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
  if ($bool_edit_permission){
		echo "<li>".$this->Html->link(__('Edit Page Right'), ['action' => 'editar', $pageRight['PageRight']['id']])."</li>";
    echo "<br/>";
  }
  if ($bool_delete_permission){
		echo "<li>".$this->Form->postLink(__('Delete Page Right'), ['action' => 'delete', $pageRight['PageRight']['id']], [], __('Está seguro que quiere eliminar permiso individual # %s?', $pageRight['PageRight']['code']))."</li>";
    echo "<br/>";
  }
		echo "<li>".$this->Html->link(__('List Page Rights'), ['action' => 'resumen'])."</li>";
		echo "<li>".$this->Html->link(__('New Page Right'), ['action' => 'crear'])."</li>";
	echo "</ul>";
?> 
</div>
