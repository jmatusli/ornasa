<?php 
	$filename="Resumen_Ordenes_Compra_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	