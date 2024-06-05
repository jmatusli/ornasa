<?php
	if (!empty($dueDate)){
		echo $this->Form->input('PurchaseOrder.due_date',['type'=>'date','label'=>__('Fecha de Vencimiento'),'dateFormat'=>'DMY','default'=>$dueDate,'minYear'=>2018,'maxYear'=>date('Y')]);
	}
