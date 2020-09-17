$(document).ready(function(){

	$(".delete-request-btn").click(function(){
		$('#delete-request-modal').modal();
		return false;
	});

	$(".delete-project-btn").click(function(){
		$('#delete-project-modal').modal();
		return false;
	});

	// $(".confirm-delete").click(function (){
	// 	$(".modal-loading").show();
	// 	$(".btn-cancel-modal").attr('disabled','');
	// 	$(".delete-vm-btn").attr('disabled','');
	// 	$(this).attr('disabled','');

	// });

	// $(".btn-cancel-modal").click(function(){
	// 	$('.modal').hide();
	// });
})