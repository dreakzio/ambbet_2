$(document).on('click', '#btn_update', function() {
	let parent_id = $('#parent_id').val();
	if (parent_id.trim().length == "") {
		$('#parent_id').focus();
		sweetAlert2('warning', 'กรุณาเลือกหมวดหมู่');
		return false;
	}
	let name = $('#name').val();
	if (name.trim().length == "") {
		$('#name').focus();
		sweetAlert2('warning', 'กรุณาระบุชื่อ');
		return false;
	}
	let is_deleted = $('#is_deleted').val();
	if (is_deleted.trim().length == "") {
		$('#is_deleted').focus();
		sweetAlert2('warning', 'กรุณาเลือกสถานะ');
		return false;
	}
	let order = $('#order').val();
	if (order.trim().length == 0) {
		$('#order').focus();
		sweetAlert2('warning', 'กรุณาระบุเรียงลำดับ');
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
$(document).on('keypress', '#order', function(e) {
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
