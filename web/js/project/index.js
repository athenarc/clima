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

})