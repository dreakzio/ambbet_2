$(document).ready(function() {
	$.ajax({
		url: BaseURL + "setting/check_sms_credit",
		method: "GET",
		dataType: 'json',
		async : false,
		success: function(response) {
			document.getElementById("sms_credit").innerHTML = response.result.amount;
		},
		error: function() {
			document.getElementById("sms_credit").innerHTML = "ติดต่อแอดมินเพื่อสมัครบริการ SMS";
		}
	});
});

$(document).on('click', '#btn_update', function() {
	let line = $('#line').val();
	if (line.trim().length == 0) {
		$('#line').focus();
		sweetAlert2('warning', 'กรุณาระบุ Line');
		return false;
	}
	/*let logo = $('#logo').val();
	if (logo.trim().length == 0) {
		$('#logo').focus();
		sweetAlert2('warning', 'กรุณาระบุ Logo');
		return false;
	}*/
	let title = $('#title').val();
	if (title.trim().length == 0) {
		$('#title').focus();
		sweetAlert2('warning', 'กรุณาระบุ Title');
		return false;
	}
	let web_description = $('#web_description').val();
	if (web_description.trim().length == 0) {
		$('#web_description').focus();
		sweetAlert2('warning', 'กรุณาระบุ Web description');
		return false;
	}
	let auto_create_member = $('#auto_create_member').val();
	let auto_create_member_deposit_amount = $('#auto_create_member_deposit_amount').val();
	if (auto_create_member == "0" && (auto_create_member_deposit_amount.trim().length == 0 || isNaN(auto_create_member_deposit_amount))) {
		$('#auto_create_member_deposit_amount').focus();
		sweetAlert2('warning', 'กรุณาระบุจำนวนเงินฝากครั้งแรก');
		return false;
	}
	let line_notify_status = $('#line_notify_status').val();
	let line_notify_token = $('#line_notify_token').val();
	if (line_notify_status == "1" && (line_notify_token == "" || line_notify_token == null)) {
		$('#auto_create_member_deposit_amount').focus();
		sweetAlert2('warning', 'กรุณากรอก Line notify token');
		return false;
	}
	let line_login_status = $('#line_login_status').val();
	let line_login_client_id = $('#line_login_client_id').val();
	let line_login_client_secret = $('#line_login_client_secret').val();
	let line_login_callback = $('#line_login_callback').val();
	if (line_login_status == "1" && ((line_login_client_id == "" || line_login_client_id == null) || (line_login_client_secret == "" || line_login_client_secret == null) || (line_login_callback == "" || line_login_callback == null))) {
		$('#auto_create_member_deposit_amount').focus();
		sweetAlert2('warning', 'กรุณากรอก Line Login Client ID, Line Login Client Secret, Line Login Callback URL');
		return false;
	}

	let line_send_messages_status = $('#line_send_messages_status').val();
	let line_messages_token = $('#line_messages_token').val();
	if (line_send_messages_status == "1" && (line_messages_token == "" || line_messages_token == null) ) {
		$('#line_messages_token').focus();
		sweetAlert2('warning', 'กรุณากรอก Line Masseges API Token');
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
});
var validateInputNumber = function(e) {
	var t = e.value;
	t = t.replace("-","");
	e.value = ((t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), 3)) : t).replace(/[^.\d]/g, '');
}
$(document).on('change',"#auto_create_member_deposit_amount",function(){
	var value = $(this).val();
	if(value.indexOf("-") >= 0){
		$(this).val(value.replace("-",""))
	}
});
$(document).on('change',"#withdraw_min_amount",function(){
	var value = $(this).val();
	if(value.indexOf("-") >= 0){
		$(this).val(value.replace("-",""))
	}
	if(value.indexOf(".") >= 0){
		$(this).val(value.split(".")[0])
	}
});
$(document).on('change','#auto_create_member',function(){
	if($(this).val() == "1"){
		$("#auto_create_member_deposit_amount").parents('.form-group').addClass('d-none');
	}else{
		$("#auto_create_member_deposit_amount").parents('.form-group').removeClass('d-none');
	}
});
$(document).on('change','#ref_return_balance_status',function(){
	if($(this).val() == "1"){
		$("#ref_return_balance_percent").parents('.form-group').removeClass('d-none');
		$("#ref_return_balance_turn").parents('.form-group').removeClass('d-none');
		$("#ref_return_balance_max").parents('.form-group').removeClass('d-none');
		$("#ref_return_balance_rank1_container").removeClass('d-none');
		$("#ref_return_balance_rank2_container").removeClass('d-none');
		$("#ref_return_balance_rank3_container").removeClass('d-none');
	}else{
		$("#ref_return_balance_percent").parents('.form-group').addClass('d-none');
		$("#ref_return_balance_turn").parents('.form-group').addClass('d-none');
		$("#ref_return_balance_max").parents('.form-group').addClass('d-none');
		$("#ref_return_balance_rank1_container").addClass('d-none');
		$("#ref_return_balance_rank2_container").addClass('d-none');
		$("#ref_return_balance_rank3_container").addClass('d-none');
	}
});
$(document).on('change','#login_status',function(){
	if($(this).val() == "1"){
		$("#login_point").parents('.form-group').removeClass('d-none');
		$("#login_turn").parents('.form-group').removeClass('d-none');
	}else{
		$("#login_point").parents('.form-group').addClass('d-none');
		$("#login_turn").parents('.form-group').addClass('d-none');
	}
});
$(document).on('change','#ref_step2_status',function(){
	if($(this).val() == "1"){
		$("#ref_step2_percent").parents('.form-group').removeClass('d-none');
		$("#ref_step2_turn").parents('.form-group').removeClass('d-none');
	}else{
		$("#ref_step2_percent").parents('.form-group').addClass('d-none');
		$("#ref_step2_turn").parents('.form-group').addClass('d-none');
	}
});
$(document).on('change','#line_notify_status',function(){
	if($(this).val() == "1"){
		$("#line_notify_token").parents('.form-group').removeClass('d-none');
	}else{
		$("#line_notify_token").parents('.form-group').addClass('d-none');
	}
});
$(document).on('change','#line_send_messages_status',function(){
	if($(this).val() == "1"){
		$("#line_messages_webhook").parents('.form-group').removeClass('d-none');
		$("#line_messages_token").parents('.form-group').removeClass('d-none');

	}else{
		$("#line_messages_webhook").parents('.form-group').addClass('d-none');
		$("#line_messages_token").parents('.form-group').addClass('d-none');
	}
});

$(document).on('change','#line_login_status',function(){
	if($(this).val() == "1"){
		$("#line_login_client_id").parents('.form-group').removeClass('d-none');
		$("#line_login_client_secret").parents('.form-group').removeClass('d-none');
		$("#line_login_callback").parents('.form-group').removeClass('d-none');
	}else{
		$("#line_login_client_id").parents('.form-group').addClass('d-none');
		$("#line_login_client_secret").parents('.form-group').addClass('d-none');
		$("#line_login_callback").parents('.form-group').addClass('d-none');
	}
});

$(document).on('change','#gg_2fa_status',function(){
	if($(this).val() == "1"){
		$("#gg_2fa_secret").parents('.form-group').removeClass('d-none');
	}else{
		$("#gg_2fa_secret").parents('.form-group').addClass('d-none');
	}
});
$(document).on('click','#btn_copy_gg_2fa_secret',function(){
	const Toast = Swal.mixin({
		toast: true,
		position: 'top-end',
		showConfirmButton: false,
		timer: 3500
	});
	Toast.fire({
		type: 'success',
		title: "คัดลอกเรียบร้อย"
	});
});
$(document).on('click','#btn_gen_gg_2fa_secret',function(){
	Swal.fire({
		type: 'warning',
		title: 'แจ้งเตือน',
		text: "ยืนยันการสร้าง Google 2FA Secret Code ?",
		confirmButtonText: 'ตกลง',
		confirmButtonColor: '#7cd1f9',
		showCancelButton: true,
		cancelButtonText: 'ยกเลิก',
		reverseButtons: true,
	})
		.then((result) => {
			if (result.value) {
				Swal.fire({
					text: "กรุณารอสักครู่..",
					showConfirmButton: false,
					allowOutsideClick: false,
					allowEscapeKey: false,
					confirmButtonText: '',
				}),
					Swal.showLoading();
				//$("#gg_2fa_gen").val('Y');
				$.ajax({
					url: BaseURL + "setting/web_setting_2fa_gen",
					method: "POST",
					dataType: 'json',
					success: function(response) {
						Swal.close();
						if (response.result) {
							$("#gg_2fa_secret").val(response.gg_2fa_secret);
							$("#img_2fa_qrcode").attr('src',response.img_2fa_qrcode);
							const Toast = Swal.mixin({
								toast: true,
								position: 'top-end',
								showConfirmButton: false,
								timer: 8000
							});
							Toast.fire({
								type: 'success',
								title: 'สร้างใหม่เรียบร้อย, สามาถใช้แอพ Scan Qrcode ได้เลย'
							});
						} else {
							sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
						}
					},
					error: function() {
						Swal.close();
						sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
					}
				});
				//$("#form_create").submit();
			}
		});
});
$(document).on('change','#auto_withdraw_status',function(){
	if($(this).val() == "1"){
		$("#auto_withdraw_min_amount_disabled").parents('.form-group').removeClass('d-none');
		$("#auto_withdraw_total_per_day").parents('.form-group').removeClass('d-none');
		$("#auto_withdraw_cnt_per_day").parents('.form-group').removeClass('d-none');
	}else{
		$("#auto_withdraw_min_amount_disabled").parents('.form-group').addClass('d-none');
		$("#auto_withdraw_total_per_day").parents('.form-group').addClass('d-none');
		$("#auto_withdraw_cnt_per_day").parents('.form-group').addClass('d-none');
	}
});
