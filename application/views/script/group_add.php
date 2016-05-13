<script>
$(function(){
	$("input[type='checkbox']").click(function(){
		console.log("checkbox clicked");
		if ($(this).closest("li").children("ul").length > 0 ) {
			checked = $(this).is(":checked");
			$(this).closest("label").next().find("input[type='checkbox']").each(function(k,v){
				if(checked) {
					$(v).prop("checked", checked).parent().addClass("checked");
				} else {
					$(v).prop("checked", checked).parent().removeClass("checked");
				}
			})
		}
	})
})


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

                rules: {                    
                    "user[group]": {
                        minlength:5,
                        required: true,
                    },
                     "user[status]": {
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
