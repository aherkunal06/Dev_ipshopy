$(document).ready(function() {
	var typingTimer;
	var doneTypingInterval = 900;
	var $input = $('.otpinput'+otplocation);
	
	$input.on('keyup', function () {
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
					$('.otperror'+otplocation).after('<div class=""><div class=""><div class="alert alert-danger">'+ json['error'] +'</div> </div></div>');
				}
				if (json['success']) {
					$('.successmsg'+otplocation).before('<div class=" success-box"><div class=""><div class="alert alert-success">'+ json['success'] +'</div></div></div>');
					$(".continue").prop("disabled",false);
					$('.otpverify'+otplocation).stop().fadeOut(5850).delay(5000);
					$('.verifyacc'+otplocation).stop().fadeOut(5850).delay(5000);
					$(".telephonenew"+otplocation).prop("readonly",true);
					$(".btnverify"+otplocation).removeClass("btn btn-danger").addClass("btn btn-success");
					if(loginotpstatus != ''){
						if(json['redirect']){
							parent.location=json['redirect'];
						}
					}
				}
			}
		});
	}
	
	$(document).ready(function() {
		$(".continue").prop("disabled",true);
	});
	
	function countdown() {
		var seconds = resendtime;
		function tick() {
			seconds--;
			$('.counter'+otplocation).html( text_pleaswait + (seconds < 10 ? "0" : "") + String(seconds) + text_requesting);
			if( seconds > 0 ) {
				setTimeout(tick, 1000);
				$(".btnverify"+otplocation).prop("disabled",true);
			} else {
				$('.resendotp'+otplocation).stop().fadeIn(1000).delay(3000);
				$('.resendotp'+otplocation).prop("disabled",false);
			}
		}
		tick();
	}
	
	$(document).delegate('.btnverify'+otplocation, 'click', function() {
		var loginotpstatus = $(".loginotpstatus").val();
		if(loginotpstatus != ''){
			var telephone = $(".telephonenew"+otplocation).val();
			var actionurl = 'index.php?route=extension/waclient/verifytelephone/loginchkphonenumber&telephone='+telephone;
		} else {
			var actionurl = 'index.php?route=extension/waclient/verifytelephone/chkphonenumber';
		}
	
		$.ajax({
			url: actionurl,
			type: 'post',
			data: $('.waotpbox'+otplocation+' input[type=\'text\']'),
			dataType: 'json',
			beforeSend: function() {
				$('.btnverify'+otplocation).button('loading');
			},
			complete: function() {
				$('.btnverify'+otplocation).button('reset');
			},
			success: function(json) {
				$('.errordiv'+otplocation).remove();
				if (json['error']) {
					$('.telephoneerror'+otplocation).after('<div class="text-danger errordiv'+otplocation+'">'+ json['error'] + '</div>');
				}
				
				if (json['success']) {
					$('.successmsg'+otplocation).before('<div class=" success-box"><div class=""><div class="alert alert-success">'+ json['success'] +'</div> </div></div>');
					$('.loadtelephone'+otplocation).after('<div class="otpsendto">'+ text_enterotp + json['tele'] + '</div>');
					$('.otpverify'+otplocation).stop().fadeIn(800).delay(3000);
					setTimeout(function(){ $('.success-box').stop().fadeOut(1000);}, 1000);
					countdown();
				}
			}
		});
	});
	
	
	$(document).delegate('.resendotp'+otplocation, 'click', function() {
		$('.resendotp'+otplocation).prop("disabled",true);
		resendotp();
		countdown();
	});
	
	function resendotp(){
		$.ajax({
			url: 'index.php?route=extension/waclient/verifytelephone/resendotp',
			type: 'post',
			data: $('.waotpbox'+otplocation+' input[type=\'text\']'),
			dataType: 'json',
			beforeSend: function() {
				$('.resendotp'+otplocation).button('loading');
			},
			complete: function() {
				$('.resendotp'+otplocation).button('reset');
			},
			success: function(json) {
			$('.alert, .text-danger').remove();
				if (json['success']) {
					$('.successmsg'+otplocation).before('<div class=" success-box"><div class=""><div class="alert alert-success">'+ json['success'] +'</div> </div></div>');
					
				}
			}
		});
	}
	});
	