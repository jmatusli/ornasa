<?php 
	$filename="Resumen Ordenes de Venta_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	