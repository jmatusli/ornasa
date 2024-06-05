<?php 
	$filename="Estado_Resultados_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	