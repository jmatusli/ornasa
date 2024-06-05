<?php 
	$filename="Resumen_Cotizaciones_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	