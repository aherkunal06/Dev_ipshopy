$(document).ready(function() {
//setup before functions
var typingTimer;
var doneTypingInterval = 900;
var $input = $('#otpinput'+otplocation);

//$input.on('keyup', function () {
$(document).on('keyup', "#otpinput"+otplocation, function () {
  clearTimeout(typingTimer);
  typingTimer = setTimeout(doneTyping, doneTypingInterval);
});

$input.on('keydown', function () {
  clearTimeout(typingTimer);
});

function doneTyping () {
	var loginotpstatus = $(".loginotpstatus").val();
	if(loginotpstatus != ''){
		var telephone = $(".telephonenew"+otplocation).val();
		var otp = $(".otpinput"+otplocation).val();
		var actionurl = 'index.php?route=extension/waclient/verifytelephone/loginaddotp&telephone='+telephone+'&otp='+otp;
	} else {
		var actionurl = 'index.php?route=extension/waclient/verifytelephone/addotp';
	}
	
	$.ajax({
		url: actionurl,
		type: 'post',
		data: $('.otpverify'+otplocation+' input[name=\'otp\'], .waotpbox'+otplocation+' input[type=\'text\']'),
		dataType: 'json',
		success: function(json) {
			$('.alert, .alert-success, .text-danger').remove();
			if (json['error']) {
				$('.otperror'+otplocation).after('<div class="form-group"><label class="col-sm-2"></label><div class="col-sm-10"><div class="alert alert-danger">'+ json['error'] +'</div> </div></div>');
			}
			if (json['success']) {
				$('.successmsg'+otplocation).before('<div class="form-group success-box"><label class="col-sm-2"></label><div class="col-sm-10"><div class="alert alert-success">'+ json['success'] +'</div></div></div>');
				$(".continue").prop("disabled",false);
				$('.otpverify'+otplocation).stop().fadeOut(5850).delay(5000);
				$('.verifyacc'+otplocation).stop().fadeOut(5850).delay(5000);
				$(".telephonenew"+otplocation).prop("readonly",true);
				$(".btnverify"+otplocation).removeClass("btn btn-danger").addClass("btn btn-success");
				if(loginotpstatus != ''){location=json['redirect'];}
			}
		}
	});
}
});
