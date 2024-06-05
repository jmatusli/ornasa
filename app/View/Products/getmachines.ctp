<?php
	if (!empty($machineList)){
		$output="<option value='0'>Seleccione Máquina</option>";
		foreach ($machineList as $key=>$value){
			$output.="<option value='".$key."'>".$value."</option>";
		}
	}
	else {
		$output="<option value='0'>No hay máquinas que pueden producir este producto!</option>";
	}
	echo $output;
			