<?php 
	$filename="Resumen Entregas a Domicilio_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	