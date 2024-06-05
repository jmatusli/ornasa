<?php 
	$filename="Asociaciones_Usuarios_Bodegas_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	