// Load a tab normally
$(document).on('click', '.tab-button', function () {
    const tab = $(this).data('tab');
    $('#config-tab-content').html('<p>Loading...</p>');
    $.get('index.php?r=administration/load-tab&tab=' + tab, function (html) {
        $('#config-tab-content').html(html);
    });
});

// When user type is changed inside the ondemand tab
$(document).on('change', '#ondemand-user-type', function () {
    const userType = $(this).val();
    $('#config-tab-content').html('<p>Loading...</p>');
    $.get('index.php?r=administration/load-tab&tab=ondemand&userType=' + userType, function (html) {
        $('#config-tab-content').html(html);
    });
});
$(document).on('change', '#service-user-type', function () {
    const userType = $(this).val();
    $('#config-tab-content').html('<p>Loading...</p>');
    $.get('index.php?r=administration/load-tab&tab=service&userType=' + userType, function (html) {
        $('#config-tab-content').html(html);
    });
});
$(document).on('change', '#machines-user-type', function () {
    const userType = $(this).val();
    $('#config-tab-content').html('<p>Loading...</p>');
    $.get('index.php?r=administration/load-tab&tab=machines&userType=' + userType, function (html) {
        $('#config-tab-content').html(html);
    });
});
$(document).on('change', '#storage-user-type', function () {
    const userType = $(this).val();
    $('#config-tab-content').html('<p>Loading...</p>');
    $.get('index.php?r=administration/load-tab&tab=storage&userType=' + userType, function (html) {
        $('#config-tab-content').html(html);
    });
});

$(document).on('change', '#jupyter-user-type', function () {
    const userType = $(this).val();
    $('#config-tab-content').html('<p>Loading...</p>');
    $.get('index.php?r=administration/load-tab&tab=jupyter&userType=' + userType, function (html) {
        $('#config-tab-content').html(html);
    });
});


