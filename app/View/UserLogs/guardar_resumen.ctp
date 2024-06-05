<?php 
	$fileName="Logs de Usuario_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$fileName,"");
?>
	
	