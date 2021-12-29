$( document ).ready(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.subscription-delete-button').on('click', function(e)
    {
        if(confirm('Are you sure you want to delete this subscription and it\'s related page licenses?') === false)
            e.preventDefault();
    });

    $('.create-workflow-template-button').on('click', function(e)
    {
        e.preventDefault();
        var button          = $(this);
        var workflow_uid    = button.data('workflow-uid');
        var page_uid        = button.data('page-uid');
        var workflow_name   = button.data('workflow-name');

        // prompt for a template name
        var template_name   = prompt("Please enter a name for the Template", workflow_name);

        // submit request to create the template
        $.post('/page/' + page_uid + '/workflow/' + workflow_uid + '/create-template', { template_name: template_name}, function(response)
        {
            if(response.success === true)
            {
                var template_uid = response.template_uid;
                alert('Template created!');
                window.location.href = "/template/" + template_uid;
            }
            else
            {
                alert(response.error_msg);
            }

            console.log(response);
        }, 'json');
    });

    $('#push-template-to-page').on('click', function(e)
    {
        e.preventDefault();

        var button          = $(this);
        var template_uid    = button.data('template-uid');
        var page_uid        = prompt("What page(s) do you want this template to go to? Provide a page_uid or more, separated by commas.");
        var workflow_name   = prompt("What do you want this new workflow to be named?");

        // submit request to create the template
        $.post('/template/' + template_uid + '/push-to-page', { template_uid: template_uid, page_uid: page_uid, workflow_name: workflow_name}, function(response)
        {
            if(response.success === true)
            {
                alert('Template push to page(s) queued');
            }
            else
            {
                alert(response.error);
            }

            console.log(response);
        }, 'json');
    });

});