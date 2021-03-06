<script>
        $('#destin_city').change(function(){
                document.location = 'pickup?awb=<?php echo $_GET['awb'];?>&destin_city=' + document.getElementById("destin_city").value + '&destin_province=' + document.getElementById("destin_province").value + '&service_type=' + document.getElementById("service_type").value;
        });

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
                    "rpx[awb]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[service]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[shipper_name]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[shipper_company]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[shipper_address1]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[shipper_city]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[shipper_state]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[shipper_account]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[consignee_name]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[consignee_address1]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[consignee_city]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[consignee_state]": {
                        minlength: 1,
                        required: true
                    },
                    "rpx[consignee_phone]": {
                        required: true,
                        minlength: 1
                    },
                    "rpx[tot_package]": {
                        required: true,
                        minlength: 1
                    },
                    "rpx[tot_weight]": {
                        minlength: 1,
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
