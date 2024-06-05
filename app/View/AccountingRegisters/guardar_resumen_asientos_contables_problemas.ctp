<?php 
	$filename="Resumen_Asientos_Contables_Problemas_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	