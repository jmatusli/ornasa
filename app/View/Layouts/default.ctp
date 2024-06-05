<!DOCTYPE html>
<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) intersinaptico (www.intersinaptico.com)
 * @link          http://www.intersinaptico.com
 * @package       app.View.Layouts
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = __d('cake_dev', 'Sistema de Producción Ornasa');
$cakeVersion = __d('cake_dev', 'CakePHP %s', Configure::version())
?>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $cakeDescription ?>:
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');
		
		echo $this->Html->css('bootstrap.min.css');
    echo $this->Html->css('bootstrap-grid.min');
		echo $this->Html->css('cake.generic.css');
		echo $this->Html->css('menu.css');
		echo $this->Html->css('ornasa.css');
    echo $this->Html->css('ornasa_tables.css');
		echo $this->Html->css('ornasa.print.css',['media' => 'print']);
		if ($currentController == 'orders' && $currentAction== 'imprimirVenta'){
      echo $this->Html->css('ornasa_invoice.css');  
    }

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
		//echo $this->Html->script('jquery-1.9.1.min');
		//echo $this->Html->script('jquery-1.12.4.min');
    //echo $this->Html->script('jquery-ui.min');
		echo $this->Html->script('date');
		echo $this->Html->script('moment.min');
    echo $this->Html->script('jquery-3.6.0.min');
    //echo $this->Html->script('jquery-migrate-1.4.1');
    echo $this->Html->script('bootstrap.bundle.min'); 
	?>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>

</head>
<body id="ornasa">
	<div id="container">
		<div id="header">
			
				<?php
          echo '<div id="headerbar">';
					echo $this->Html->image("ornasa_logo_small.png", ['alt' => 'Ornasa','url' => $userhomepage,'style'=>'max-width:200px;']);
				
          echo '<nav role="navigation">';				
            echo $this->MenuBuilder->build('main-menu',$active); 
          echo '</nav>';
				
          if(!empty($modificationInfo)){
            //if ($currentController == 'purchaseOrders' && $currentAction== 'ver'){
              // pr($modificationInfo);
            //}
            if ($modificationInfo!=NA){
              echo "<div class='useractions' style='position:absolute;right:0px;top:0px;'>".$modificationInfo."</div>";
            }
          }
          echo $this->Html->link(__('Logout'),'/users/logout', ['class' => 'btn btn-primary logout']);	
          echo "<a href='javascript:window.print();' class='btn btn-primary print'>Imprimir</a>";

          echo "<span class='username'>".$username."</span>";
        echo "</div>";
        
        if ($sub!="NA"){
          echo '<div id="sub-menu">';
          echo $this->MenuBuilder->build($sub,$active); 
          echo '</div>';
        }
        echo "<div class='noprint' style='clear:left;'>";
          echo "<span style='margin-right:50px;color:white;font-weight:500;'>Tasa de Cambio US$:".$currentExchangeRate."</span>";
          if ($exchangeRateUpdateNeeded){
            echo "<div class='noprint'>Tasa de cambio se venció, por favor ".$this->Html->Link('actualizar tasa!',['controller' => 'exchange_rates','action' => 'add'],['class' => 'btn btn-primary','target'=>'blank'])."</div>";
          }
        echo "</div>";
        
			
      ?>
		</div>
		<div id="content">
			
			<?php echo $this->Session->flash(); ?>
			<?php echo $this->Session->flash('auth'); ?>
			<?php echo $this->fetch('content'); ?>
		</div>
		<?php 
			$currentController= $this->params['controller'];
			$currentAction= $this->params['action'];
			if (!($currentController=="users"&&$currentAction=="login")){
		?>	
		<script>       
      function roundToTwo(num) {    
        return +(Math.round(num + "e+2")  + "e-2");
      }
      function roundToThree(num) {    
        return +(Math.round(num + "e+3")  + "e-3");
      }
      function roundToFour(num) {    
        return +(Math.round(num + "e+4")  + "e-4");
      }

      function roundToFive(num) {    
        return +(Math.round(num + "e+5")  + "e-5");
      }      
    
			$('body').on('change','input[type=text]',function(){	
        if (!$(this).hasClass('keepcase')){
          var uppercasetext=$(this).val().toUpperCase();
          $(this).val(uppercasetext)
        }
			});
			function confirmBackspaceNavigations () {
				// http://stackoverflow.com/a/22949859/2407309
				var backspaceIsPressed = false
				$(document).keydown(function(event){
					if (event.which == 8) {
						backspaceIsPressed = true
					}
				})
				$(document).keyup(function(event){
					if (event.which == 8) {
						backspaceIsPressed = false
					}
				})
				$(window).on('beforeunload', function(){
					if (backspaceIsPressed) {
						backspaceIsPressed = false
						return "Está seguro de ir a la pantalla anterior?"
					}
				})
			} // confirmBackspaceNavigations
			
      //http://stackoverflow.com/questions/20788411/how-to-exclude-weekends-between-two-dates-using-moment-js
			function addWeekdays(date, days) {
			  date = moment(date); // use a clone
			  while (days > 0) {
				date = date.add(1, 'days');
				// decrease "days" only if it's a weekday.
				if (date.isoWeekday() !== 6 && date.isoWeekday() !== 7) {
				  days -= 1;
				}
			  }
			  return date;
			}
			
			function getMonthFromDate(startdate){				
				var startdatemonth=startdate.getMonth();
				switch (startdatemonth){
					case 0:
						resultdatemonth="01";
						break;
					case 1:
						resultdatemonth="02";
						break;
					case 2:
						resultdatemonth="03";
						break;
					case 3:
						resultdatemonth="04";
						break;
					case 4:
						resultdatemonth="05";
						break;
					case 5:
						resultdatemonth="06";
						break;
					case 6:
						resultdatemonth="07";
						break;
					case 7:
						resultdatemonth="08";
						break;
					case 8:
						resultdatemonth="09";
						break;
					case 9:
						resultdatemonth="10";
						break;
					case 10:
						resultdatemonth="11";
						break;
					case 11:
						resultdatemonth="12";
						break;
				}
				return resultdatemonth;
			}
      
			$('#previousmonth').click(function(event){
				var thisMonth = parseInt($('#ReportStartdateMonth').val());
				var previousMonth= (thisMonth-1)%12;
				var previousYear=parseInt($('#ReportStartdateYear').val());
				if (previousMonth==0){
					previousMonth=12;
					previousYear-=1;
				}
				if (previousMonth<10){
					previousMonth="0"+previousMonth;
				}
				$('#ReportStartdateDay').val('01');
				$('#ReportStartdateMonth').val(previousMonth);
				$('#ReportStartdateYear').val(previousYear);
				var daysInPreviousMonth=daysInMonth(previousMonth,previousYear);
				$('#ReportEnddateDay').val(daysInPreviousMonth);
				$('#ReportEnddateMonth').val(previousMonth);
				$('#ReportEnddateYear').val(previousYear);
			});
			
			$('#nextmonth').click(function(event){
				var thisMonth = parseInt($('#ReportStartdateMonth').val());
				var nextMonth= (thisMonth+1)%12;
				var nextYear=parseInt($('#ReportStartdateYear').val());
				if (nextMonth==0){
					nextMonth=12;
				}
				if (nextMonth==1){
					nextYear+=1;
				}
				if (nextMonth<10){
					nextMonth="0"+nextMonth;
				}
				$('#ReportStartdateDay').val('01');
				$('#ReportStartdateMonth').val(nextMonth);
				$('#ReportStartdateYear').val(nextYear);
				var daysInNextMonth=daysInMonth(nextMonth,nextYear);
				$('#ReportEnddateDay').val(daysInNextMonth);
				$('#ReportEnddateMonth').val(nextMonth);
				$('#ReportEnddateYear').val(nextYear);
			});
      
      $('#previousyear').click(function(event){
				var previousYear=parseInt($('#ReportStartdateYear').val())-1;
				$('#ReportStartdateDay').val('01');
				$('#ReportStartdateMonth').val('01');
				$('#ReportStartdateYear').val(previousYear);
				$('#ReportEnddateDay').val('31');
				$('#ReportEnddateMonth').val('12');
				$('#ReportEnddateYear').val(previousYear);
			});
			
			$('#nextyear').click(function(event){
				var nextYear=parseInt($('#ReportStartdateYear').val())+1;
				$('#ReportStartdateDay').val('01');
				$('#ReportStartdateMonth').val('01');
				$('#ReportStartdateYear').val(nextYear);
				$('#ReportEnddateDay').val('31');
				$('#ReportEnddateMonth').val('12');
				$('#ReportEnddateYear').val(nextYear);
			});
			
			function daysInMonth(month,year) {
				return new Date(year, month, 0).getDate();
			}
      
      $('body').on('keypress','#content',function(e){
				 var node = (e.target) ? e.target : ((e.srcElement) ? e.srcElement : null);
				if(e.which == 13 && node.type !="textarea") { // Checks for the enter key
				//if(e.which == 13) { // Checks for the enter key
					e.preventDefault(); // Stops IE from triggering the button to be clicked
				}
			});
      
      $('body').on('click','div.numeric input',function(){
				if (!$(this).attr('readonly')){
					if ($(this).val()=="0"){
						$(this).val("");
					}
				}
			});
			$('body').on('click','div.number input',function(){
				if (!$(this).attr('readonly')){
					if ($(this).val()=="0"){
						$(this).val("");
					}
				}
			});
			$('body').on('click','div.decimal input',function(){
				if (!$(this).attr('readonly')){
					if ($(this).val()=="0"){
						$(this).val("");
					}
				}
			});
			
			$('body').on('blur','div.numeric input',function(){
				if (!$(this).val()||isNaN($(this).val())){
					$(this).val(0);
				}
			});	
      $('body').on('blur','div.number input',function(){
				if (!$(this).val()||isNaN($(this).val())){
					$(this).val(0);
				}
			});	
			$('body').on('blur','div.decimal input',function(){
				if (!$(this).val()||isNaN($(this).val())){
					$(this).val(0);
				}
			});	
			
			$(document).ready(function(){
				confirmBackspaceNavigations ()
			});
		</script>
		<?php
			}
		?>
		<div id="footer">
			<?php 
				echo '<div id="copyright">Copyright 2014-'.date('Y').' @ Intersinaptico</div>';
				echo $this->Html->image('logo_intersinaptico_50.jpg', ['alt' => 'intersinaptico', 'border' => '0']);
        echo "<div style='padding-left:300px;'>sesión hasta ".date('d-m-Y H:i:s',$this->Session->read('Config.time'))."</div>";
			?>
		</div>
	</div>
<?php 
  echo $this->element('sql_dump'); 
  echo $this->Html->script('jquery.number'); 
?>
</body>
</html>
