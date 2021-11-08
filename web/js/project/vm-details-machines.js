$(document).ready(function(){

    $(".delete-vm-btn").click(function(){
        $('.delete').modal();
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

    $(".instructions-for-volume").click(function(){
        $('.instructions').modal();
    });

    $(".start-btn").click(function(){

        var status=$("#status-span-machines").html().trim();
        /*
         * If vm is not in shutoff state, it cannot be started
         */
        if (status!="SHUTOFF")
        {
            var danger="<div class='alert alert-danger' id='danger-alert'>VM must be in SHUTOFF state.</div>";
            if ($('#danger-alert').length)
            {
                $('#danger-alert').remove();
            }
            $(danger).insertBefore($('.first-info'));
            $("#danger-alert").fadeTo(2000, 500).slideUp(500, function() {
                $("#danger-alert").slideUp(500);
            });

        }
        else
        {
            /*
             * Start VM; read status every 5 seconds and reload page as soon as it's active
             */
            
            /* 
             * Show something to the user
             */
            var success="<div class='alert alert-success success-start' id='success-alert'><i class='fas fa-spinner fa-spin'></i>&nbsp;VM starting. Please wait...</div>";
            if (!$('#success-start').length)
            {
                $('#success-alert').remove();
            }
            $(success).insertBefore($('.first-info'));
            startVM();

            
        }

    });

    $(".shutdown-btn").click(function(){

        var status=$("#status-span-machines").html().trim();
        /*
         * If vm is not in active state, it cannot be stopped
         */
        if (status!="ACTIVE")
        {
            var danger="<div class='alert alert-danger' id='danger-alert'>VM must be in ACTIVE state.</div>";
            if ($('#danger-alert').length)
            {
                $('#danger-alert').remove();
            }
            $(danger).insertBefore($('.first-info'));
            $("#danger-alert").fadeTo(2000, 500).slideUp(500, function() {
                $("#danger-alert").slideUp(500);
            });

        }
        else
        {
            /*
             * Restart VM; read status every 5 seconds and reload page as soon as it's active
             */
            
            /* 
             * Show something to the user
             */
            var success="<div class='alert alert-success success-start' id='success-alert'><i class='fas fa-spinner fa-spin'></i>&nbsp;VM stopping. Please wait...</div>";
            if (!$('#success-start').length)
            {
                $('#success-alert').remove();
            }
            $(success).insertBefore($('.first-info'));
            stopVM();

            
        }

    });

    $(".reboot-btn").click(function(){

        var status=$("#status-span-machines").html().trim();
        /*
         * If vm is not in active state, it cannot be stopped
         */
        if (status!="ACTIVE")
        {
            var danger="<div class='alert alert-danger' id='danger-alert'>VM must be in ACTIVE state.</div>";
            if ($('#danger-alert').length)
            {
                $('#danger-alert').remove();
            }
            $(danger).insertBefore($('.first-info'));
            $("#danger-alert").fadeTo(2000, 500).slideUp(500, function() {
                $("#danger-alert").slideUp(500);
            });

        }
        else
        {
            /*
             * Restart VM; read status every 5 seconds and reload page as soon as it's active
             */
            
            /* 
             * Show something to the user
             */
            var success="<div class='alert alert-success success-start' id='success-alert'><i class='fas fa-spinner fa-spin'></i>&nbsp;VM rebooting. Please wait...</div>";
            if (!$('#success-start').length)
            {
                $('#success-alert').remove();
            }
            $(success).insertBefore($('.first-info'));
            rebootVM();

            
        }

    });

    function startVM()
    {
        var vm_id=$("#hidden_vm_machines_field").val();
        $.ajax({
            url: "index.php?r=project/start-vm-machines",
            type: "GET",
            data: { "vm_id": vm_id },
            dataType: "html",
            success: function (data) 
            {
                var refreshId = setInterval(function() 
                {
                    getStatusStart(refreshId);
                  
                }, 5000);
                      

            },
            error: function(data)
            {
                if ($('#success-alert').length)
                {
                    $('#success-alert').remove();
                }
                if ($('#danger-alert').length)
                {
                    $('#danger-alert').remove();
                }
                var danger="<div class='alert alert-danger' id='danger-alert'>Error starting VM.</div>";
                $(danger).insertBefore($('.first-info'));
                $("#danger-alert").fadeTo(2000, 500).slideUp(500, function() {
                    $("#danger-alert").slideUp(500);
                });
            },
            retries: 2
              
        });

    }

    function stopVM()
    {
        var vm_id=$("#hidden_vm_machines_field").val();
        $.ajax({
            url: "index.php?r=project/stop-vm-machines",
            type: "GET",
            data: { "vm_id": vm_id },
            dataType: "html",
            success: function (data) 
            {
                var refreshId = setInterval(function() 
                {
                    getStatusStop(refreshId);
                  
                }, 5000);
                      

            },
            error: function(data)
            {
                if ($('#success-alert').length)
                {
                    $('#success-alert').remove();
                }
                if ($('#danger-alert').length)
                {
                    $('#danger-alert').remove();
                }
                var danger="<div class='alert alert-danger' id='danger-alert'>Error stopping VM.</div>";
                $(danger).insertBefore($('.first-info'));
                $("#danger-alert").fadeTo(2000, 500).slideUp(500, function() {
                    $("#danger-alert").slideUp(500);
                });
            },
            retries: 2
              
        });
    }

    function rebootVM()
    {
        var vm_id=$("#hidden_vm_machines_field").val();
        $.ajax({
            url: "index.php?r=project/reboot-vm-machines",
            type: "GET",
            data: { "vm_id": vm_id },
            dataType: "html",
            success: function (data) 
            {
                var refreshId = setInterval(function() 
                {
                    getStatusReboot(refreshId);
                  
                }, 2000);
                      

            },
            error: function(data)
            {
                if ($('#success-alert').length)
                {
                    $('#success-alert').remove();
                }
                if ($('#danger-alert').length)
                {
                    $('#danger-alert').remove();
                }
                var danger="<div class='alert alert-danger' id='danger-alert'>Error rebooting VM.</div>";
                $(danger).insertBefore($('.first-info'));
                $("#danger-alert").fadeTo(2000, 500).slideUp(500, function() {
                    $("#danger-alert").slideUp(500);
                });
            },
            retries: 2
              
        });
    }


    function getStatusStart(refId)
    {
        var vm_id=$("#hidden_vm_machines_field").val();
        var previous=$("#status-span-machines").html().trim();
        
        $.ajax({
            url: "index.php?r=project/get-vm-machines-status",
            type: "GET",
            data: { "vm_id": vm_id },
            dataType: "html",
            success: function (data) 
            {
                status = data.replace(/\"/g, "");
                if ( status == "ACTIVE" )
                {
                    clearInterval(refId);
                    window.location.href = window.location.href;
                }
                      

            },
            retries: 2,
              
        });
    }

    function getStatusStop(refId)
    {
        var vm_id=$("#hidden_vm_machines_field").val();
        
        $.ajax({
            url: "index.php?r=project/get-vm-machines-status",
            type: "GET",
            data: { "vm_id": vm_id },
            dataType: "html",
            success: function (data) 
            {
                status = data.replace(/\"/g, "");
                if ( status == "SHUTOFF" )
                {
                    clearInterval(refId);
                    window.location.href = window.location.href;
                }
                      

            },
            retries: 2,
              
        });
    }

    function getStatusReboot(refId)
    {
        var vm_id=$("#hidden_vm_machines_field").val();
        
        $.ajax({
            url: "index.php?r=project/get-vm-machines-status",
            type: "GET",
            data: { "vm_id": vm_id },
            dataType: "html",
            success: function (data) 
            {
                status = data.replace(/\"/g, "");
                if ( status == "ACTIVE" )
                {
                    clearInterval(refId);
                    if ($('#success-alert').length)
                    {
                        $('#success-alert').remove();
                    }
                    if ($('#danger-alert').length)
                    {
                        $('#danger-alert').remove();
                    }
                    var success="<div class='alert alert-success' id='success-alert'>Successfully rebooted VM.</div>";
                        $(success).insertBefore($('.first-info'));
                        $("#success-alert").fadeTo(5000, 500).slideUp(500, function() {
                            $("#success-alert").slideUp(500);
                        });

                }
                      

            },
            retries: 2,
              
        });
    }


});