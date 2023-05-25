$(document).on('click', '#btn_update', function() {
	let role_name = $('#role_name').val();
	if (role_name.trim().length == "") {
		$('#role_name').focus();
		sweetAlert2('warning', 'กรุณาระบุชื่อตำแหน่ง');
		return false;
	}
	let is_deleted = $('#is_deleted').val();
	if (is_deleted.trim().length == "") {
		$('#is_deleted').focus();
		sweetAlert2('warning', 'กรุณาเลือกสถานะ');
		return false;
	}
	let role_level = $('#role_level').val();
	if (role_level.trim().length == 0) {
		$('#role_level').focus();
		sweetAlert2('warning', 'กรุณาระบุ Level');
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
$(document).on('keypress', '#role_level', function(e) {
	let key = e.keyCode;
	//a-z 0-9
	if ((key >= 48 && key <= 57) && key != 32) {
		return true;
	} else {
		return false;
	}
});
var validateInputNumber = function(e) {
	var t = e.value;
	t = t.replace("-","");
	e.value = ((t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), 3)) : t).replace(/[^.\d]/g, '');
}
