<script>
counter=0;
  function action()
  {
	length =$('.form-control').length;
	le = ((length -1)/2)-1;	
	count = le+1;
	counterNext = counter + 1;
    document.getElementById("key"+counter).innerHTML = "<p><input id='key'  placeholder='Input Brand Name' style='width:80%' class='form-control' type='text' name='brandcode[key]["+count+"]' required><div id=\"key"+counterNext+"\"></div></p>";
	document.getElementById("brands"+counter).innerHTML = "<p><input  id ='brands' placeholder='Input Brand Name' style='width:80%' class='form-control' type='text' name='brandcode[brands]["+count+"]' required><div id=\"brands"+counterNext+"\"></div></p>";
	counter++;
	}
i=0;	
function removekey(){	
	$('#key').remove();
	$('#brands').remove();
    i--;
	}
	
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
                },
                rules: {
                    "client[client_code]": {
                        minlength: 5,
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
        }

    };

}();
</script>