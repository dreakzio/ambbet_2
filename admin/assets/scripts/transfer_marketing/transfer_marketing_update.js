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
	Swal.fire({
			text: "กรุณารอสักครู่..",
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
			confirmButtonText: '',
		}),
		Swal.showLoading();
});
