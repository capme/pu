<script>
var FormValidation = function () {

    var apiFormValidate = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation

            var form1 = $('#form_<?php echo $this->va_input->getGroup()?>');
            var error1 = $('.alert-danger', form1);
            var success1 = $('.alert-success', form1);

            form1.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",
                messages: {
                    "user[password]": {
                        "pwcheck" : "Field should contain character (upper & lower case), number" 
                    }
                },
                rules: {
                    "user[username]": {
                        minlength: 5,
                        required: true
                    },
                    "user[password]": {
                        minlength: 5,
                        required: true,
                        pwcheck: true
                    },
                    "user[retypepassword]": {
                    	equalTo: "#password"
                    },
                    "user[group]": {
                        required: true,
                    },
                    "user[fullname]": {
                        required: true,
                    },
                    "user[email]": {
                    	required: true,
                    	email: true,
                    },
                    "user[active]": {
                        required: true
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit              
                    success1.hide();
                    error1.show();
                    App.scrollTo(error1, -200);
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change done by hightlight
                    $(element)
                        .closest('.form-group').removeClass('has-error'); // set error class to the control group
                },

                success: function (label) {
                    label
                        .closest('.form-group').removeClass('has-error'); // set success class to the control group
                },

                submitHandler: function (form) {
                    //success1.show();
                    error1.hide();
                    form.submit();
                }
            });
    }

    return {
        //main function to initiate the module
        init: function () {
            apiFormValidate();
            <?php if($this->router->method == "view"):?>
            $("#password").rules("remove", "pwcheck");
            $("input[name='user\[changepass\]']").change(function(){
            	if($(this).is(":checked")){
            		$("#password").rules("add", {"pwcheck": true})
            	} else {
            		$("#password").rules("remove", "pwcheck");
            	}
            })
            <?php endif;?>
        }

    };

}();
</script>
