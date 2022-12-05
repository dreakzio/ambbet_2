const BaseURL = $('#base_url').val();

// alert("Hello world")

function sweetAlert2(type, text) {
	Swal.fire({
		type: type,
		title: 'แจ้งเตือน',
		html: text,
		confirmButtonText: 'ตกลง',
		confirmButtonColor: '#7cd1f9',
	});
}
