$(document).ready(function(){

  $("#additional").change(function(){
    
    $("#textarea").toggle();
   
  });

  $("#coldstoragerequest-vm_type").change(function(){
    
    if ($(this).val()==2)
    {
        $(".num_of_volumes_dropdown").removeClass('hidden');
    }
    else
    {
        $(".num_of_volumes_dropdown").addClass('hidden');
    }
    
  })

})