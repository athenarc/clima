$(document).ready(function()
{	
	

	$(".tab-button").click(function ()
	{
		var divClass= '.' + $(this).attr('data-controlling');


		var activeButtonId=$(this).attr('id');
		$('#hidden_active_button').val(activeButtonId);

		if (divClass=='.tab-general' || divClass=='.tab-email-configuration' || (divClass=='.tab-openstack-configuration') )
		{
			$('#typeDropdown').hide();
		}
		else
		{
			$('#typeDropdown').show();
		}

		var activeTab=$(".tab-active");
		var activeButton=$(".button-active");
		activeButton.removeClass("button-active");
		activeTab.removeClass("tab-active");
		activeTab.hide();
		$(divClass).show();
		$(divClass).addClass("tab-active");
		$(this).addClass("button-active");
		

	});

	$("#typeDropdown").change(function ()
	{
		$("#configuration_form").submit();
	});





// Get the container element
// var dropdown = document.getElementById("typeDropdown");

// // Get all buttons with class="btn" inside the container
// //var btns = btnContainer.getElementsByClassName("btn");

// // Loop through the buttons and add the active class to the current/clicked button
// for (var i = 0; i < dropdown.length; i++) {
//   dropdown[i].addEventListener("click", function() {
//     var current = document.getElementsByClassName("active");

//     // If there's no active class
//     if (current.length > 0) {
//       current[0].className = current[0].className.replace(" active", "");
//     }
//     $("#configuration_form").submit();
//     // Add the active class to the current/clicked button
//     this.className += " active";
//   });
// } 










});