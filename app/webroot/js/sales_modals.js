/**
 * inspired by https://github.com/twbs/bootstrap/issues/3902 kimsy
 * https://stackoverflow.com/questions/19506672/how-to-check-if-bootstrap-modal-is-open-so-i-can-use-jquery-validate
*/ 
  var showInProgress = false;
  var hideInProgress = false;
  
  function showModal(elementId) {
      if (hideInProgress) {
      //    showModalId = elementId;
      } 
      else {
        showInProgress = true;
        $("#" + elementId).on('shown.bs.modal', showCompleted);
        $("#" + elementId).modal("show");
        
        function showCompleted() {
          showInProgress = false;
          
          $("#" + elementId).off('shown.bs.modal');
        }
      }
  }

  function hideModal(elementId) {
      hideInProgress = true;
      $("#" + elementId).on('hidden.bs.modal', hideCompleted);
      $("#" + elementId).modal("hide");

      function hideCompleted() {
          hideInProgress = false;
          
          $("#" + elementId).off('hidden.bs.modal');
      }
  }
  
  function showMessageModal(){
    if (!$('#msgModal').is(':visible') && !showInProgress){
      showModal('msgModal');
    }
  }
  function hideMessageModal(){
    $('#clientProcessMsg').html('');
    if (showInProgress){
      setTimeout(hideMessageModal, 500);
    }
    else {
      if ($('#msgModal').is(':visible')){
        hideModal('msgModal');
      }
    }
  }
