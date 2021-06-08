$(document).ready(function(){

	$(".create-vm-btn").click(function(){
		$('.loading').show();
		$(this).attr('disabled','');
		$('.btn-default').attr('disabled','');
		$('#vm_form').submit();
	});

	$(".help-block").bind("DOMSubtreeModified",function(){
		$('.loading').hide();
		$(".create-vm-btn").removeAttr('disabled');
		$('.btn-default').removeAttr('disabled');

	});

	$(".instructions-for-volume").click(function(){
		$('.instructions').modal();
	});
})