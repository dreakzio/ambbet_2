$(document).on('click', '#btn_update', function() {
	let name = $('#name').val();
	if (name.trim().length == 0) {
		$('#name').focus();
		sweetAlert2('warning', 'กรุณาระบุชื่อประกาศ');
		return false;
	}
	/*let image = $('#image').val();
	if (image == "") {
		$('#image').focus();
		sweetAlert2('warning', 'กรุณาเลือกรูปภาพ');
		return false;
	}*/
	Swal.fire({
			text: "กรุณารอสักครู่..",
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
			confirmButtonText: '',
		}),
		Swal.showLoading();
});
