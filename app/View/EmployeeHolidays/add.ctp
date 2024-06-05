<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.js"></script>
<script>
	$('body').on('change','input[type=text]',function(){	
		var uppercasetext=$(this).val().toUpperCase();
		$(this).val(uppercasetext)
	});
  
  $(document).ready(function(){
		$('#saving').addClass('hidden');
	});
  
  $('body').on('click','#submit',function(e){	
    $(this).data('clicked', true);
  });
  $('body').on('submit','#EmployeeHolidayAddForm',function(e){	
    if($("#submit").data('clicked'))
    {
      $('#submit').attr('disabled', 'disabled');
      $("#mainform").fadeOut();
      $("#saving").removeClass('hidden');
      $("#saving").fadeIn();
      var opts = {
          lines: 12, // The number of lines to draw
          length: 7, // The length of each line
          width: 4, // The line thickness
          radius: 10, // The radius of the inner circle
          color: '#000', // #rgb or #rrggbb
          speed: 1, // Rounds per second
          trail: 60, // Afterglow percentage
          shadow: false, // Whether to render a shadow
          hwaccel: false // Whether to use hardware acceleration
      };
      var target = document.getElementById('saving');
      var spinner = new Spinner(opts).spin(target);
    }
    
    return true;
  });
</script>
<div class="employeeHolidays form">
<?php 
  echo "<div id='saving' style='min-height:180px;z-index:9998!important;position:relative;'>";
    echo "<div id='savingcontent'  style='z-index:9999;position:relative;'>";
      echo "<p id='savingspinner' style='font-weight:700;font-size:24px;text-align:center;z-index:100!important;position:relative;'>Guardando los días de vacaciones ...</p>";
    echo "</div>";
  echo "</div>";
  
  echo $this->Form->create('EmployeeHoliday'); 
	echo "<fieldset id='mainform'>";
		echo "<legend>".__('Add Employee Holiday')."</legend>";
	
		echo $this->Form->input('employee_id');
		echo $this->Form->input('holiday_date',array('dateFormat'=>'DMY','minYear'=>'2014','maxYear'=>'2025'));
		echo $this->Form->input('days_taken',array('default'=>'1'));
		echo $this->Form->input('holiday_type_id');
		echo $this->Form->input('observation');
	
	echo "</fieldset>";
  echo $this->Form->Submit(__('Submit'),array('id'=>'submit','name'=>'submit'));
  echo $this->Form->end();
?>  
</div>
<div class='actions'>
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('List Employee Holidays'), array('action' => 'index'))."</li>";
		echo "<br/>";
		if ($bool_employee_index_permission) {
			echo "<li>".$this->Html->link(__('List Employees'), array('controller' => 'employees', 'action' => 'index'))." </li>";
		}
		if ($bool_employee_add_permission) {
			echo "<li>".$this->Html->link(__('New Employee'), array('controller' => 'employees', 'action' => 'add'))." </li>";
		}
	echo "</ul>";
?>
</div>