$(document).ready(function(){

  $("#arrow").click(function(){
    $("#expired-table").toggle();
    if($("#arrow").hasClass('fa-chevron-down')){

          $("#arrow").removeClass("fa-chevron-down");
          $("#arrow").addClass("fa-chevron-up");

      }else{

          $("#arrow").removeClass("fa-chevron-up");
          $("#arrow").addClass("fa-chevron-down");

      }
  });

  $("#types_dropdown").change(function(){
      $('#filters-form').submit();
    });
  $("#expiry_date").change(function(){
    $('#filters-form').submit();
  });
  $("#username").change(function(){
    $('#filters-form').submit();
  });
  $("#project_name").change(function(){
    $('#filters-form').submit();
  });

  $(".reactivate_btn").click(function(){
      var modal_id='#' + $(this).data()['modalId'];
      
      $(modal_id).modal();
  })

})