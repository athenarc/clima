$(document).ready(function(){

	$(".delete-volume-btn").click(function(){
		id=$(this).attr("id");
		$(".modal-loading").hide();
		$('.'+id).modal('show');


	});

	$(".confirm-delete").click(function (){
		$(".modal-loading").show();
		$(".btn-cancel-modal").attr('disabled','');
		$(".delete-vm-btn").attr('disabled','');
		$(this).attr('disabled','');

	});


})