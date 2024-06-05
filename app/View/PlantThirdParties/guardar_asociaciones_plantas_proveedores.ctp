<?php 
	$filename="Asociaciones_Plantas_Proveedores_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	