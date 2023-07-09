$(document).on('click', '#btn_update', function() {
	let bank_code = $('#bank_code').val();
	if (bank_code == "") {
		$('#bank_code').focus();
		sweetAlert2('warning', 'กรุณาเลือกธนาคาร');
		return false;
	}
	let account_name = $('#account_name').val();
	if (account_name.trim().length == "") {
		$('#account_name').focus();
		sweetAlert2('warning', 'กรุณาระบุชื่อบัญชี');
		return false;
	}
	let bank_number = $('#bank_number').val();
	if (bank_number.trim().length == 0) {
		$('#bank_number').focus();
		sweetAlert2('warning', 'กรุณาระบุเลขบัญชี');
		return false;
	}
	if (bank_number.trim().length < 10) {
		sweetAlert2('warning', 'เลขบัญชีไม่ถูกต้อง');
		return false;
	}
	let username = $('#username').val();
	if (username.trim().length == 0) {
		$('#username').focus();
		sweetAlert2('warning', 'กรุณาระบุ Username');
		return false;
	}
	let password = $('#password').val();
	if (password.trim().length == 0) {
		$('#password').focus();
		sweetAlert2('warning', 'กรุณาระบุ Password');
		return false;
	}
	let start_time_can_not_deposit = $('#start_time_can_not_deposit').val();
	let end_time_can_not_deposit = $('#end_time_can_not_deposit').val();
	if (start_time_can_not_deposit != "" || end_time_can_not_deposit != "") {
		if(
			(start_time_can_not_deposit != "" && end_time_can_not_deposit == "") ||
			(end_time_can_not_deposit != "" && start_time_can_not_deposit == "")
		){
			$('#start_time_can_not_deposit').focus();
			sweetAlert2('warning', 'กรุณาระบุเวลาจาก-ถึง ที่ปิดระบบฝากออโต้');
			return false;
		}
	}
	let status_withdraw = $('#status_withdraw').val();
	let max_amount_withdraw_auto = $("#max_amount_withdraw_auto").val()
	if (status_withdraw == "1" && max_amount_withdraw_auto.trim().length == 0) {
		$('#max_amount_withdraw_auto').focus();
		sweetAlert2('warning', 'กรุณาระบุ จำนวนเงินถอนออโต้ได้ไม่เกิน (บาท/ครั้ง)');
		return false;
	}
	/*let api_token_1 = $('#api_token_1').val();
	if (api_token_1.trim().length == 0 &&
		($('#bank_code').val().toString().trim() == "05" || $('#bank_code').val().toString().trim() == "5" || $('#bank_code').val().toString().trim() == "02" || $('#bank_code').val().toString().trim() == "2"  || $('#bank_code').val().toString().trim() == "03" || $('#bank_code').val().toString().trim() == "3" || $('#bank_code').val().toString().trim() == "10" )
	) {
		$('#api_token_1').focus();
		sweetAlert2('warning', 'กรุณาระบุ '+$("#api_token_1_label").text());
		return false;
	}
	let api_token_2 = $('#api_token_2').val();
	if (api_token_2.trim().length == 0 &&
		($('#bank_code').val().toString().trim() == "05" || $('#bank_code').val().toString().trim() == "5" || $('#bank_code').val().toString().trim() == "02" || $('#bank_code').val().toString().trim() == "2"  || $('#bank_code').val().toString().trim() == "03" || $('#bank_code').val().toString().trim() == "3"  || $('#bank_code').val().toString().trim() == "10" )
	) {
		$('#api_token_2').focus();
		sweetAlert2('warning', 'กรุณาระบุ '+$("#api_token_2_label").text());
		return false;
	}
	let api_token_3 = $('#api_token_3').val();
	if (api_token_3.trim().length == 0 &&
		($('#bank_code').val().toString().trim() == "03" || $('#bank_code').val().toString().trim() == "3" )
	) {
		$('#api_token_3').focus();
		sweetAlert2('warning', 'กรุณาระบุ '+$("#api_token_3_label").text());
		return false;
	}*/
	if(start_time_can_not_deposit.indexOf(".") >= 0){
		if(moment(start_time_can_not_deposit,"HH.SS").isValid() == true){
			$('#start_time_can_not_deposit').val(moment(start_time_can_not_deposit,"HH.SS").format("HH:SS"))
		}else{
			$('#start_time_can_not_deposit').val("")
		}
	}
	if(end_time_can_not_deposit.indexOf(".") >= 0){
		if(moment(end_time_can_not_deposit,"HH.SS").isValid() == true){
			$('#end_time_can_not_deposit').val(moment(end_time_can_not_deposit,"HH.SS").format("HH:SS"))
		}else{
			$('#end_time_can_not_deposit').val("")
		}
		moment(end_time_can_not_deposit,"HH.SS").format("HH:SS")
	}
	if((bank_code == "05" || bank_code == "5"
			|| bank_code == "02" || bank_code == "2"
			|| bank_code == "3" || bank_code == "03"
			|| bank_code == "06" || bank_code == "6"
			|| bank_code == "11")
		&& $("#api_type").val() == "1" && $("#auto_transfer").val() == "1"
	){
		if($('#auto_min_amount_transfer').val().toString().trim().length == 0){
			$('#auto_min_amount_transfer').focus();
			sweetAlert2('warning', 'กรุณาระบุยอดเงินขั้นต่ำที่จะโยกออกเป็นตัวเลข');
			return false;
		}else if($('#auto_transfer_bank_code').val() == ""){
			$('#auto_transfer_bank_code').focus();
			sweetAlert2('warning', 'กรุณาเลือกธนาคารปลายทาง');
			return false;
		}else if($('#auto_transfer_bank_number').val().toString().trim().length == 0 || $('#auto_transfer_bank_number').val().toString().trim().length < 10){
			$('#auto_transfer_bank_number').focus();
			sweetAlert2('warning', 'กรุณาระบุเลขบัญชีปลายทางเป็นตัวเลข (10 ตัว)');
			return false;
		}else if($('#auto_transfer_bank_acc_name').val().toString().trim() == ""){
			$('#auto_transfer_bank_acc_name').focus();
			sweetAlert2('warning', 'กรุณาระบุชื่อบัญชีปลายทาง');
			return false;
		}
	}
	if(bank_code == "11"){
		Swal.fire({
			html: '<h3 >กดตกลงเพื่อรับ OTP</h3>',
			showCancelButton : false,
			cancelButtonText:'ยกเลิก',
			confirmButtonText: 'ตกลง',
			allowOutsideClick: false,
			allowEscapeKey: false
		}).then((result) => {
			//console.log('test',result);
			if (result.value) {
				Swal.fire({
					text: "กรุณารอสักครู่..",
					showConfirmButton: false,
					allowOutsideClick: false,
					allowEscapeKey: false,
					confirmButtonText: '',
				}),
					Swal.showLoading();
				$.ajax ({
					type: "POST",
					url: BaseURL +"bank/bank_kkp_get_otp",
					dataType: "json",
					data: {idCard: api_token_1, pin: api_token_2},
					success: function (data) {
						//console.log(data);
						let verifyTransactionId = data['result']['verifyTransactionId'];
						let referenceNo = data['result']['referenceNo'];
						Swal.fire({
							title: 'กรอก OTP ที่ได้รับจากธนาคาร <br/>Ref : '+data['result']['referenceNo'],
							input: 'text',
							showCancelButton: false,
							confirmButtonText: 'ยืนยัน OTP',
							showLoaderOnConfirm: true,
							allowOutsideClick: false,
							allowEscapeKey: false,
							preConfirm: (data) => {
								console.log(data.result)
								Swal.fire({
									text: "กรุณารอสักครู่..",
									showConfirmButton: false,
									allowOutsideClick: false,
									allowEscapeKey: false,
									confirmButtonText: '',
								}),
									Swal.showLoading();
								$.ajax ({
									type: "POST",
									url: BaseURL +"bank/bank_kkp_confirm_otp",
									dataType: "json",
									data: {idCard: api_token_1, pin: api_token_2,otp :data,verifyTransactionId:verifyTransactionId,referenceNo:referenceNo },
									success: function (data) {
										if(data['message']=='success'){
											Swal.fire({
												title: `ยืนยัน OTP เรียบร้อย `,
												confirmButtonText: 'บันทึกรายการ',
											}).then((result) => {
												if(result.value){
													$( "#form_create" ).submit();
												}
											})
										}else{
											sweetAlert2('warning', 'ยืนยัน OTP ไม่สำเร็จ');
											timerInterval = setInterval(() => {
												swal.close();
											}, 100)
										}
									}
								})
							},
						}).then((result)=>{
							Swal.fire({
								text: "กรุณารอสักครู่..",
								showConfirmButton: false,
								allowOutsideClick: false,
								allowEscapeKey: false,
								confirmButtonText: '',
							}),
								Swal.showLoading();
							console.log(result);
						})
					}

				});
			}
		})

		event.preventDefault();

	}else{
		Swal.fire({
			text: "กรุณารอสักครู่..",
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
			confirmButtonText: '',
		}),
			Swal.showLoading();
	}
});
$(document).on("change","#start_time_can_not_deposit,#end_time_can_not_deposit",function(){
	if($(this).val() != "" && $(this).val() != null){
		try{
			if(moment($(this).val(),"HH.SS").isValid() == true){
				$(this).val(moment($(this).val(),"HH.SS").format("HH:SS"))
			}else{
				$(this).val("")
			}
		}catch (err){
			$(this).val("")
		}
	}
})
$('#username,#password').bind("cut copy paste", function(e) {
	e.preventDefault();
});
$(document).on('keypress', '#username,#password', function(e) {
	let key = e.keyCode;
	if (key != 32) {
		return true;
	} else {
		return false;
	}
});
$(document).on('keypress', '#bank_number', function(e) {
	let key = e.keyCode;
	//a-z 0-9
	if ((key >= 48 && key <= 57) && key != 32) {
		return true;
	} else {
		return false;
	}
});
$(document).on('change', '#status_withdraw', function(e) {
	let value =$(this).val()
	if(value == "1"){
		$("#start_time_can_not_deposit").prop("readonly",true);
		$("#end_time_can_not_deposit").prop("readonly",true);
		$("#message_can_not_deposit").prop("readonly",true);
		$("#max_amount_withdraw_auto").prop("readonly",false);
	}else{
		$("#start_time_can_not_deposit").prop("readonly",false);
		$("#end_time_can_not_deposit").prop("readonly",false);
		$("#message_can_not_deposit").prop("readonly",false);
		$("#max_amount_withdraw_auto").prop("readonly",true);
	}
});
$(document).on('change', '#bank_code', function(e) {
	let value =$(this).val()
	$("#username_label").text("Username");
	$("#username").attr("placeholder","Username");
	$("#password_label").text("Password");
	$("#password").attr("placeholder","Password");
	$("#api_type").parents('.form-group').hide();
	$("#container_auto_transfer").hide();
	if(value == "05" || value == "5" || value == "02" || value == "2" || value == "06" || value == "6"|| bank_code == "11"){
		$(".form-api").show();
		$(".header-form-api").show();
		$("#api_token_1_label").text("Device ID");
		$("#api_token_1").attr("placeholder","Device ID");
		$("#api_token_2_label").show();
		$("#api_token_2").show();
		$("#api_token_2_label").text("PIN");
		$("#api_token_2").attr("placeholder","PIN");
		$("#api_token_3_label").parent('.form-group').hide();
		$("#api_token_3_label").hide();
		$("#api_token_3").hide();
		$("#api_token_3_label").text("Other");
		$("#api_token_3").attr("placeholder","Other");
		if($('#bank_code').val() == "05" || $('#bank_code').val() == "5"){
			$("#container_check_regis").show();
		}
		if(value == "02" || value == "2" || value == "06" || value == "6" || value == "05" || value == "5" || value == "11"){
			if($('#api_type').val() == "1"){
				$("#container_auto_transfer").show();
				if($('#auto_transfer').val() == "0"){
					$("#auto_min_amount_transfer").prop("disabled",true);
					$("#auto_transfer_bank_code").prop("disabled",true);
					$("#auto_transfer_bank_number").prop("disabled",true);
					$("#auto_transfer_bank_acc_name").prop("disabled",true);
				}else{
					$("#auto_min_amount_transfer").prop("disabled",false);
					$("#auto_transfer_bank_code").prop("disabled",false);
					$("#auto_transfer_bank_number").prop("disabled",false);
					$("#auto_transfer_bank_acc_name").prop("disabled",false);
				}
			}
			$("#api_type").find("option").removeAttr("disabled");
			$("#api_type").parents('.form-group').show();
			if(value == "05" || value == "5"){
				$("#api_type").find("option:eq(1)").prop("disabled",true);
			}
			if(value=='11'){
				$('#status_withdraw').val(1);
				$("#status_withdraw").find("option:eq(0)").prop("disabled",true);
				$('#api_token_1_label').html('รหัสบัตรประชาชน');
				$('#api_token_1').attr("placeholder", "รหัสบัตรประชาชน");
				$('#username').prop("disabled",true);
				$('#password').prop("disabled",true);
				$("#api_type").find("option:eq(1)").prop("disabled",true);
				$("#promptpay_number").prop("disabled",true);
				$("#promptpay_status").prop("disabled",true);
				$("#message_can_not_deposit").prop("readonly",true);
			}
		}
	}else if(value == "03" || value == "3"){
		$(".form-api").show();
		$(".header-form-api").show();
		$("#api_token_2_label").show();
		$("#api_token_2").show();
		$("#api_token_1_label").text("Account Token No");
		$("#api_token_1").attr("placeholder","Account Token No");
		$("#api_token_2_label").text("User Token ID");
		$("#api_token_2").attr("placeholder","User Token ID");
		$("#api_token_3_label").parent('.form-group').show();
		$("#api_token_3_label").show();
		$("#api_token_3").show();
		$("#api_token_3_label").text("User Identity");
		$("#api_token_3").attr("placeholder","User Identity");
	}else if(value == "10"){
		$(".form-api").show();
		$(".header-form-api").show();
		$("#api_token_2_label").show();
		$("#api_token_2").show();
		$("#api_token_1_label").text("Pin 6 ตัว");
		$("#api_token_1").attr("placeholder","Pin 6 ตัว");
		$("#api_token_2_label").text("Login Token (login_token จากระบบ TMNOne)");
		$("#api_token_2").attr("placeholder","Login Token (login_token จากระบบ TMNOne)");
		$("#username_label").text("TMN Key ID (tmn_key_id จากระบบ TMNOne)");
		$("#username").attr("placeholder","TMN Key ID (tmn_key_id จากระบบ TMNOne)");
		$("#password_label").text("TMN ID (tmn_id จากระบบ TMNOne)");
		$("#password").attr("placeholder","TMN ID (tmn_id จากระบบ TMNOne)");
		$("#api_token_3_label").parent('.form-group').hide();
		$("#api_token_3_label").hide();
		$("#api_token_3").hide();
		$("#api_token_3_label").text("Other");
		$("#api_token_3").attr("placeholder","Other");
	}else{
		$(".form-api").hide();
		$(".header-form-api").hide();
		$("#api_token_1_label").text("Device ID");
		$("#api_token_1").attr("placeholder","Device ID");
		$("#api_token_2_label").text("PIN");
		$("#api_token_2").attr("placeholder","PIN");
		$("#api_token_3_label").text("Other");
		$("#api_token_3").attr("placeholder","Other");
		$("#container_check_regis").hide();
	}
});
$(document).ready(function() {
	$("#api_type").parents('.form-group').hide();
	$("#container_auto_transfer").hide();
	if($('#bank_code').val() == "05" || $('#bank_code').val() == "5"  || $('#bank_code').val() == "02" || $('#bank_code').val() == "2"  || $('#bank_code').val() == "3" || $('#bank_code').val() == "03" || $('#bank_code').val() == "10" || $('#bank_code').val() == "06" || $('#bank_code').val() == "6" || $('#bank_code').val() == "11"){
		$(".form-api").show();
		$(".header-form-api").show();
		$("#api_token_3_label").parent('.form-group').hide();
		if($('#bank_code').val() == "10"){
			$("#api_token_1_label").text("Pin 6 ตัว");
			$("#api_token_1").attr("placeholder","Pin 6 ตัว");
			$("#username_label").text("TMN Key ID (tmn_key_id จากระบบ TMNOne)");
			$("#username").attr("placeholder","TMN Key ID (tmn_key_id จากระบบ TMNOne)");
			$("#password_label").text("TMN ID (tmn_id จากระบบ TMNOne)");
			$("#password").attr("placeholder","TMN ID (tmn_id จากระบบ TMNOne)");
			$("#api_token_2_label").text("Login Token (login_token จากระบบ TMNOne)");
			$("#api_token_2").attr("placeholder","Login Token (login_token จากระบบ TMNOne)");
		}else if($('#bank_code').val() == "2" || $('#bank_code').val() == "02" || $('#bank_code').val() == "6" || $('#bank_code').val() == "06" || $('#bank_code').val() == "5" || $('#bank_code').val() == "05"){
			if($('#api_type').val() == "1"){
				$("#container_auto_transfer").show();
				if($('#auto_transfer').val() == "0"){
					$("#auto_min_amount_transfer").prop("disabled",true);
					$("#auto_transfer_bank_code").prop("disabled",true);
					$("#auto_transfer_bank_number").prop("disabled",true);
					$("#auto_transfer_bank_acc_name").prop("disabled",true);
				}else{
					$("#auto_min_amount_transfer").prop("disabled",false);
					$("#auto_transfer_bank_code").prop("disabled",false);
					$("#auto_transfer_bank_number").prop("disabled",false);
					$("#auto_transfer_bank_acc_name").prop("disabled",false);
				}
				$(".form-api").show();
				$(".header-form-api").show();
			}else if($('#api_type').val() == "3"){
				$("#container_auto_transfer").hide();
				$('#username_label').html('Email login Line Lift');
				$('#password_label').html('password login Line Lift');
				$('#status_withdraw').val(0);
				$('#status_withdraw').find("option:eq(1)").prop("disabled",true);

				$("#start_time_can_not_deposit").prop("readonly",false);
				$("#end_time_can_not_deposit").prop("readonly",false);
				$("#message_can_not_deposit").prop("readonly",false);
				$("#max_amount_withdraw_auto").prop("readonly",true);
				$(".form-api").hide();
				$(".header-form-api").hide();
			}else if($('#api_type').val() == "4"){
				$("#container_auto_transfer").hide();
				$('#username_label').html('Email login Line Lift');
				$('#password_label').html('password login Line Lift');
				$('#status_withdraw').val(0);
				$('#status_withdraw').find("option:eq(1)").prop("disabled",true);

				$("#start_time_can_not_deposit").prop("readonly",false);
				$("#end_time_can_not_deposit").prop("readonly",false);
				$("#message_can_not_deposit").prop("readonly",false);
				$("#max_amount_withdraw_auto").prop("readonly",true);
				$(".form-api").hide();
				$(".header-form-api").hide();
			}
			$("#api_type").find("option").removeAttr("disabled");
			$("#api_type").parents('.form-group').show();

			if($('#bank_code').val() == "05" || $('#bank_code').val() == "5"){
				$("#api_type").find("option:eq(1)").prop("disabled",true);

				$("#container_check_regis").show();

				/*if($('#status_withdraw').val()=='1'){
					$("#api_type").find("option:eq(2)").prop("disabled",true);
				}else{
					$("#api_type").find("option:eq(2)").prop("disabled",false);
				}*/
			}
		}else if($('#bank_code').val() == "11"){
				$('#status_withdraw').val(1);
				$("#status_withdraw").find("option:eq(0)").prop("disabled",true);
				$('#api_token_1_label').html('รหัสบัตรประชาชน');
				$('#api_token_1').attr("placeholder", "รหัสบัตรประชาชน");
				$('#username').prop("disabled",true);
				$('#password').prop("disabled",true);
				$("#api_type").find("option:eq(1)").prop("disabled",true);
				$("#promptpay_number").prop("disabled",true);
				$("#promptpay_status").prop("disabled",true);
				$("#message_can_not_deposit").prop("readonly",true);
		}
	}else{
		$(".form-api").hide();
		$(".header-form-api").hide();
	}
	let value =$("#status_withdraw").val()
	if(value == "1"){
		$("#start_time_can_not_deposit").prop("readonly",true);
		$("#end_time_can_not_deposit").prop("readonly",true);
		$("#message_can_not_deposit").prop("readonly",true);
		$("#max_amount_withdraw_auto").prop("readonly",false);
	}else{
		$("#start_time_can_not_deposit").prop("readonly",false);
		$("#end_time_can_not_deposit").prop("readonly",false);
		$("#message_can_not_deposit").prop("readonly",false);
		$("#max_amount_withdraw_auto").prop("readonly",true);
	}
});
$(document).on('click', '#manee_create', function() {
	let account_id = $('#account_id').val();
	let store_code = $('#store_code').val();
	let manee_qr = $('#manee_qr').val();
	if (store_code == "") {
		$('#store_code').focus();
		sweetAlert2('warning', 'กรุณากรอกรหัสร้าน');
		return false;
	}
	if (manee_qr == "") {
		$('#manee_qr').focus();
		sweetAlert2('warning', 'กรุณากรอกข้อมูล QR Code');
		return false;
	}
	Swal.fire({
		text: "กรุณารอสักครู่..",
		showConfirmButton: false,
		allowOutsideClick: false,
		allowEscapeKey: false,
		confirmButtonText: '',
	}),
		Swal.showLoading();
	$.ajax({
		url: BaseURL + 'welcome/manee',
		type: "GET",
		data: {
			account_id: account_id,
			store_code: store_code,
			manee_qr: manee_qr,
		},
		dataType: 'json',
		success: function(response) {
			if(response.error){
				sweetAlert2('success', response.message);
			} else {
				sweetAlert2('warning', response.message);
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			swal("Error deleting!", "Please try again", "error");
		}
	});

});
$(document).on('change', '#auto_transfer', function(e) {
	let value =$(this).val()
	if($('#api_type').val() == "1" && ($('#bank_code').val() == "05" || $('#bank_code').val() == "5"  || $('#bank_code').val() == "02" || $('#bank_code').val() == "2"  || $('#bank_code').val() == "3" || $('#bank_code').val() == "03"  || $('#bank_code').val() == "06" || $('#bank_code').val() == "6" || $('#bank_code').val() == "11")){
		$("#container_auto_transfer").show();
		if(value == "0"){
			$("#auto_min_amount_transfer").prop("disabled",true);
			$("#auto_transfer_bank_code").prop("disabled",true);
			$("#auto_transfer_bank_number").prop("disabled",true);
			$("#auto_transfer_bank_acc_name").prop("disabled",true);
		}else{
			$("#auto_min_amount_transfer").prop("disabled",false);
			$("#auto_transfer_bank_code").prop("disabled",false);
			$("#auto_transfer_bank_number").prop("disabled",false);
			$("#auto_transfer_bank_acc_name").prop("disabled",false);
		}
	}else{
		$("#container_auto_transfer").hide();
	}
});
$(document).on('change', '#api_type', function(e) {
	let value =$(this).val();
	//console.log($('#bank_code').val());
	if(value == "1" && ($('#bank_code').val() == "05" || $('#bank_code').val() == "5"
		|| $('#bank_code').val() == "02" || $('#bank_code').val() == "2"
		|| $('#bank_code').val() == "3" || $('#bank_code').val() == "03"
		|| $('#bank_code').val() == "06" || $('#bank_code').val() == "6"
		|| $('#bank_code').val() == "11")){
		$("#container_auto_transfer").show();
		if($("#auto_transfer") == "0"){
			$("#auto_min_amount_transfer").prop("disabled",true);
			$("#auto_transfer_bank_code").prop("disabled",true);
			$("#auto_transfer_bank_number").prop("disabled",true);
			$("#auto_transfer_bank_acc_name").prop("disabled",true);
		}else{
			$("#auto_min_amount_transfer").prop("disabled",false);
			$("#auto_transfer_bank_code").prop("disabled",false);
			$("#auto_transfer_bank_number").prop("disabled",false);
			$("#auto_transfer_bank_acc_name").prop("disabled",false);
		}
		$(".form-api").show();
		$(".header-form-api").show();
	}else if (value == "3" && ($('#bank_code').val() == "05" || $('#bank_code').val() == "5")){
		$("#container_auto_transfer").show();
		$('#username_label').html('Email login Line Lift');
		$('#password_label').html('password login Line Lift');
		$('#status_withdraw').val(0);
		$('#status_withdraw').find("option:eq(1)").prop("disabled",true);

		$("#start_time_can_not_deposit").prop("readonly",false);
		$("#end_time_can_not_deposit").prop("readonly",false);
		$("#message_can_not_deposit").prop("readonly",false);
		$("#max_amount_withdraw_auto").prop("readonly",true);
	}else if (value == "4" && ($('#bank_code').val() == "05" || $('#bank_code').val() == "5")){
		//console.log(value);
		$("#container_auto_transfer").hide();
		$('#username_label').html('Email login Line Lift');
		$('#password_label').html('password login Line Lift');
		$('#status_withdraw').val(0);
		$('#status_withdraw').find("option:eq(1)").prop("disabled",true);

		$("#start_time_can_not_deposit").prop("readonly",false);
		$("#end_time_can_not_deposit").prop("readonly",false);
		$("#message_can_not_deposit").prop("readonly",false);
		$("#max_amount_withdraw_auto").prop("readonly",true);
		$(".form-api").hide();
		$(".header-form-api").hide();
	}else{
		$("#container_auto_transfer").hide();
	}
});
var validateInputNumber = function(e) {
	var t = e.value;
	t = t.replace("-","");
	e.value = ((t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), 3)) : t).replace(/[^.\d]/g, '');
}
