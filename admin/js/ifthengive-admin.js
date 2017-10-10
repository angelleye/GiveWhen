(function ($) {
    'use strict';
    jQuery(document).ready(function ($) {        
        $('#upload-btn').click(function (e) {
            e.preventDefault();
            var image = wp.media({
                title: 'Upload Image',
                // mutiple: true if you want to upload multiple files at once
                multiple: false
            }).open()
                    .on('select', function (e) {
                        // This will return the selected image from the Media Uploader, the result is an object
                        var uploaded_image = image.state().get('selection').first();
                        // We convert uploaded_image to a JSON object to make accessing it easier
                        // Output to the console uploaded_image                    
                        var image_url = uploaded_image.toJSON().url;
                        // Let's assign the url value to the input field
                        $('#image_url').val(image_url);
                    });
        });
        
        $(document).on('click','#fixed_radio',function () {
            if ($(this).is(':checked')) {
                $('#fixed_amount_input_div').removeClass('hidden');
                $('#dynamic_options').addClass('hidden');
                $('#manual_amount_input_div').addClass('hidden');
            }
        });
        $(document).on('click','#manual_radio',function () {
            if ($(this).is(':checked')) {
                $('#manual_amount_input_div').removeClass('hidden');
                $('#fixed_amount_input_div').addClass('hidden');
                $('#dynamic_options').addClass('hidden');                
            }
        });
        
        $(document).on('click','#option_radio',function () {
            if ($(this).is(':checked')) {
                $('#dynamic_options').removeClass('hidden');
                $('#fixed_amount_input_div').addClass('hidden');                
                $('#manual_amount_input_div').addClass('hidden');
            }
        });
                
        var room = jQuery("button#remove_dynamic_fields").length;
        if(room === 0){
            room = 1;
        }
        else{
            room++;
        }
        $('#add_dynamic_field').click(function(){            
             room++;
            var objTo = document.getElementById('education_fields');
            var divtest = document.createElement("div");
                divtest.setAttribute("class", "form-group removeclass"+room);
                var rdiv = 'removeclass'+room;
            divtest.innerHTML = '<div class="col-sm-1 nopadding"><label class="control-label">Option </label></div><div class="col-sm-3 nopadding"><div class="form-group"><input type="text" class="form-control" id="option_name" name="option_name[]" value="" placeholder="Option Name"></div></div><div class="col-sm-3 nopadding"><div class="form-group"><div class="input-group"><input type="text" class="form-control" id="option_amount" name="option_amount[]" value="" placeholder="Option Amount"><div class="input-group-btn"> <button class="btn btn-danger" type="button" id="remove_dynamic_fields" data-fieldid="'+room+'"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span> </button></div></div></div></div><div class="clear"></div>';
            objTo.appendChild(divtest);
        });
        
        $(document).on('click','#remove_dynamic_fields',function(){            
           var rid = $(this).attr('data-fieldid');
           $('.removeclass'+rid).remove();
        });
                        
        if(typeof tinymce != 'undefined') {            
            tinymce.PluginManager.add('ifthengive_shortcodes', function( editor )
            {
                var shortcodeValues = [];                
                jQuery.each(itg_shortcodes_button_array.shortcodes_button, function( post_id, post_title )
                {                    
                    shortcodeValues.push({
                        text: post_title, 
                        value: post_id
                    });                  
                    
                });
                editor.addButton('itg_shortcodes', {
                    text: 'IfThenGive',
                    type: 'listbox',
                    title: 'IfThenGive',
                    cmd: 'itg_shortcodes',
                    icon: 'mce-ico mce-i-wp_more',
                    onselect: function() {                           
                        if(this.text()=='Transaction'){
                            tinyMCE.activeEditor.selection.setContent( '[ifthengive_transactions]' );
                        }
                        else if(this.text()=='Account'){
                            tinyMCE.activeEditor.selection.setContent( '[ifthengive_account]' );
                        }
                        else if (this.text() == 'My Signedup Goals'){
                            tinyMCE.activeEditor.selection.setContent( '[ifthengive_goals]' );
                        }
                        else{
                            tinyMCE.activeEditor.selection.setContent( '[ifthengive_goal id=' + this.value() + ']' );
                        }
                    },
                    values: shortcodeValues
                });                                
            });
        }
        
        $(document).on('click','#give_when_fun',function(){
            alertify.defaults.transition = "slide";
            alertify.defaults.theme.ok = "btn btn-primary";
            alertify.defaults.theme.cancel = "btn btn-danger";
            alertify.defaults.theme.input = "form-control";
            alertify.confirm('Process Donation', 'Are you sure you want to Process Donation..?',
                function ()
                {                                        
                    alertify.success('Process Doantion is Starting.'); 
                    window.location.href = $('#give_when_fun').attr('data-redirectUrl');
                },
                function ()
                {
                    alertify.error('You Pressed Cancel');
                });            
        });
        
        $(document).on('click','#itg_sandbox_enable',function(){
            var sandbox = '';            
            if ($(this).is(':checked')){
                sandbox = true;         
                $('#give_when_sandbox_fields').show();
                $('#give_when_live_fields').hide();
            } else { 
                sandbox = false;
                $('#give_when_sandbox_fields').hide();
                $('#give_when_live_fields').show();
            }
             $.ajax({
                type: 'POST',
                url: admin_ajax_url,
                 data: { 
                    action  : 'sandbox_enabled',
                    sandbox : sandbox
                },
                dataType: "json",
                success: function (result) {
                }
            });
        });
        $("#gwsandboxClass").on('show.bs.collapse', function(){
            $("#gwsandbox_details").text('').text('Hide Advanced Details');
        });
        $("#gwsandboxClass").on('hide.bs.collapse', function(){
            $("#gwsandbox_details").text('').text('Show Advanced Details');
        });
        
        $("#gwliveClass").on('show.bs.collapse', function(){
            $("#gwlive_details").text('').text('Hide Advanced Details');
        });
        $("#gwliveClass").on('hide.bs.collapse', function(){
            $("#gwlive_details").text('').text('Show Advanced Details');
        });
        $('#fixed_amount_input').on('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        });
        $(document).on('input','input[name="option_amount[]"]', function() {
           this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); 
        });
        $('#gw_manual_amount_input').on('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        });
        
        $( "input[name='post_title']" ).focusout(function() {
            $('input[name="trigger_thing"]').focus();    
        });
        $( ".btn-preview" ).focusout(function() {
            $('#save-post').focus();
        });
        
        $(document).on('click','.btn-cbaid',function(){
            var userid = $(this).data('userid');
            var status = $(this).data('gwchangestatus');
            var post_id = $(this).data('postid');
            var btn = $(this);
            alertify.defaults.transition = "slide";
            alertify.defaults.theme.ok = "btn btn-primary";
            alertify.defaults.theme.cancel = "btn btn-danger";
            alertify.defaults.theme.input = "form-control";
            alertify.confirm(status + ' this Giver ?', 'Are you sure you want to '+status+' this Giver..?',
                function ()
                {                                                            
                    $.ajax({
                       type: 'POST',
                       url: admin_ajax_url,
                        data: { 
                           action  : 'cancel_billing_agreement_giver',
                           userid : userid,
                           postid : post_id
                       },
                       dataType: "json",
                       success: function (result) {                           
                           if(btn.hasClass('btn-warning')){
                               btn.removeClass('btn-warning');
                               btn.addClass('btn-defalt');
                               btn.text('Activate');
                               btn.closest('tr').addClass('gw_suspended_row');
                               alertify.error('Giver Suspended');
                           }
                           else{
                               btn.removeClass('btn-defalt');
                               btn.addClass('btn-warning');
                               btn.text('Suspend');
                               btn.closest('tr').removeClass('gw_suspended_row');
                               alertify.success('Giver Activated');
                           }
                       }
                   });
                },
                function ()
                {
                    alertify.error('You Pressed Cancel');
                });               
        });
        
        $(document).on('click','#gw_sandbox_add_manually', function(){
            if ($(this).is(':checked')){                
                $("#itg_sb_api_credentials_username").removeAttr('disabled');
                $("#itg_sb_api_credentials_password").removeAttr('disabled');
                $("#itg_sb_api_credentials_signature").removeAttr('disabled');
            }
            else{
                $('#itg_sb_api_credentials_username').attr('disabled','disabled');
                $('#itg_sb_api_credentials_password').attr('disabled','disabled');
                $('#itg_sb_api_credentials_signature').attr('disabled','disabled');
            }
        });
        
        $(document).on('click','#gw_live_add_manually', function(){
            if ($(this).is(':checked')){                
                $("#itg_lv_api_credentials_username").removeAttr('disabled');
                $("#itg_lv_api_credentials_password").removeAttr('disabled');
                $("#itg_lv_api_credentials_signature").removeAttr('disabled');
            }
            else{
                $('#itg_lv_api_credentials_username').attr('disabled','disabled');
                $('#itg_lv_api_credentials_password').attr('disabled','disabled');
                $('#itg_lv_api_credentials_signature').attr('disabled','disabled');
            }
        });
        
    });    

})(jQuery);