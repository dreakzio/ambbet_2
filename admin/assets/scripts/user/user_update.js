$(document).ready(function() {
	$('#username_ref').select2({
		language: 'th',
		theme: 'bootstrap4',
		width : '100%',
		ajax: {
			url: function (params) {
				params.search = {value : params.term};
				return BaseURL + 'user/user_list_page?start=0&length=50&empty=true&ignore_self=true&sortBy=account.agent&orderBy=DESC';
			},
			processResults: function (data) {
				var datas = JSON.parse(data)
				return {
					results: $.map(datas.data, function (item) {
						var label = item.username;
						if(item.agent=="1"){
							label = "พันธมิตร : "+label;
						}else if(item.agent == "0"){
							label = "สมาชิกปกติ : "+label;
						}
						return {
							text: label,
							id: item.id,
							data : item
						}
					})
				};
			}}
	});
});
$(document).on('click', '#btn_update', function() {
	let phone = $('#phone').val();
	if (phone.trim().length == 0) {
		$('#phone').focus();
		sweetAlert2('warning', 'กรุณาระบุเบอร์มือถือ');
		return false;
	}
	if (phone.trim().length != 10) {
		sweetAlert2('warning', 'เบอร์มือถือไม่ถูกต้อง');
		return false;
	}
	let full_name = $('#full_name').val();
	if (full_name.trim().length == 0) {
		$('#full_name').focus();
		sweetAlert2('warning', 'กรุณาระบุชื่อ - นามสกุล');
		return false;
	}
	let bank = $('#bank').val();
	if (bank == "") {
		$('#bank').focus();
		sweetAlert2('warning', 'กรุณาเลือกธนาคาร');
		return false;
	}
	/*let line_id = $('#line_id').val();
	if (line_id.trim().length == 0) {
		sweetAlert2('warning', 'กรุณาระบุไลน์');
		return false;
	}*/
	let bank_number = $('#bank_number').val();
	if (bank_number.trim().length == 0) {
		$('#bank_number').focus();
		sweetAlert2('warning', 'กรุณาระบุเลขบัญชี');
		return false;
	}
	if (bank_number.trim().length < 10 || bank_number.trim().length > 15) {
		sweetAlert2('warning', 'เลขบัญชีไม่ถูกต้อง');
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
$('#phone,#bank_number').bind("cut copy paste", function(e) {
	e.preventDefault();
});
$(document).on('keypress', '#bank_number,#phone', function(e) {
	let key = e.keyCode;
	if (key >= 48 && key <= 57 && key != 32) {
		return true;
	} else {
		return false;
	}
});
