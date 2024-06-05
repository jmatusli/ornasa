<?php 
	$filename="Asociaciones_Plantas_Tipos_de_Producto_".date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$filename,"");
?>
	
	