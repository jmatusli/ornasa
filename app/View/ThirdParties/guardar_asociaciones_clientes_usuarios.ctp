<?php 
	$filename="Resumen_Asociaciones_Clientes_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	