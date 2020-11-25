$(document).ready(function(){

	$(".delete-vm-btn").click(function(){
		$('.modal').modal();
	});

	$(".confirm-delete").click(function (){
		$(".modal-loading").show();
		$(".btn-cancel-modal").attr('disabled','');
		$(".delete-vm-btn").attr('disabled','');
		$(this).attr('disabled','');

	});

	$(".retrieve-pass-btn").click(function()
	{
		var requestId=$('#hidden_request_id').val();
        $('.password-div').show(); 
		$.ajax({
            url: "index.php?r=project/retrieve-win-password",
            type: "GET",
            data: { "id": requestId},
            dataType: "html",
            success: function (data) 
            {
              $('.password-div').html(data); 
              $('.pass-warning-div').show();
            },
            retries: 2,
        });
	});
})