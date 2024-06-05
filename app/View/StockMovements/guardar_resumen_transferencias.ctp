<?php 
	$fileName="Resumen_Transferencias_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$fileName,"");
?>
	
	