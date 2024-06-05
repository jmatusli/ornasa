<div class="plants form">
<?php echo $this->Form->create('Plant'); ?>
	<fieldset>
		<legend><?php echo __('Add Plant'); ?></legend>
	<?php
		echo $this->Form->input('name');
    echo $this->Form->input('short_name',['label'=>'Nombre corto']);
    echo $this->Form->input('series',['label'=>'Letra serial de facturas']);
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class='actions'>
	
<?php echo "<h3>".__('Actions')."</h3>"; 
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Plant'), ['action' => 'resumen'])."</li>";
	echo "</ul>";
?>	
</div>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
</script>
