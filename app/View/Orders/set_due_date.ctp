<?php
	if (!empty($dueDate)){
		echo $this->Form->input('Invoice.due_date',['type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY','default'=>$dueDate]);
	}
?>