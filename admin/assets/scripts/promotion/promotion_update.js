try{
	$('.dropify').dropify({
		tpl: {
			wrap: '<div class="dropify-wrapper"></div>',
			loader: '<div class="dropify-loader"></div>',
			message: '<div class="dropify-message"><span class="file-icon" /><br /> {{ default }}</div>',
			preview: '<div class="dropify-preview"><span class="dropify-render"></span><div class="dropify-infos"><div class="dropify-infos-inner"><p class="dropify-infos-message">{{ replace }}</p></div></div></div>',
			filename: '<p class="dropify-filename"><span class="file-icon"></span> <span class="dropify-filename-inner"></span></p>',
			clearButton: '<button type="button" class="dropify-clear">{{ remove }}</button>',
			errorLine: '<p class="dropify-error">ตรวจสอบชนิดไฟล์ให้ถูกต้อง</p>',
			errorsContainer: '<div class="dropify-errors-container"><ul></ul></div>'
		}
	});
}catch (err){

}
$(document).on('click', '#btn_update', function() {
	let category = $('#category').val();
	let name = $('#name').val();
	if (name.trim().length == 0) {
		$('#name').focus();
		sweetAlert2('warning', 'กรุณาระบุชื่อโปรโมชั่น');
		return false;
	}
	let percent = $('#percent').val();
	if (percent.trim().length == 0 && category == "1") {
		$('#percent').focus();
		sweetAlert2('warning', 'กรุณาระบุจำนวนโบนัส (%)');
		return false;
	}
	let max_value = $('#max_value').val();
	if (max_value.trim().length == 0 && category == "1") {
		$('#max_value').focus();
		sweetAlert2('warning', 'กรุณาระบุโบนัสสูงสุด (บาท)');
		return false;
	}
	let fix_amount_deposit_bonus = $('#fix_amount_deposit_bonus').val();
	if (fix_amount_deposit_bonus.trim().length == 0 && category == "2") {
		$('#fix_amount_deposit_bonus').focus();
		sweetAlert2('warning', 'Fix โบนัส (บาท)');
		return false;
	}
	let fix_amount_deposit = $('#fix_amount_deposit').val();
	if (fix_amount_deposit.trim().length == 0 && category == "2") {
		$('#fix_amount_deposit').focus();
		sweetAlert2('warning', 'Fix ยอดฝาก (บาท)');
		return false;
	}
	let turn = $('#turn').val();
	if (turn.trim().length == 0) {
		$('#turn').focus();
		sweetAlert2('warning', 'กรุณาระบุคูณยอดเทิร์น');
		return false;
	}
	let max_use = $('#max_use').val();
	if (max_use.trim().length == 0) {
		$('#max_use').focus();
		sweetAlert2('warning', 'กรุณาระบใช้ได้ต่อ User');
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
$('#percent,#max_value,#turn,#fix_amount_deposit_bonus,#fix_amount_deposit').bind("cut copy paste", function(e) {
	e.preventDefault();
});
$(document).on('keypress', '#percent,#max_value,#turn,#fix_amount_deposit_bonus,#fix_amount_deposit', function(e) {
	let key = e.keyCode;
	if (key >= 48 && key <= 57 && key != 32) {
		return true;
	} else {
		return false;
	}
});
$(document).on('change', '#type', function(e) {
	// let value = $(this).val();
	// if (value != 1) {
	// 	$('#div_max_use').css('display', 'block');
	// } else {
	// 	$('#div_max_use').css('display', 'none');
	// }
});
$(document).on('change', '#category', function(e) {
	changeCategory();
});
function changeCategory(){
	let category = $("#category").val();
	if(category == "2"){
		$("#percent").parent(".form-group").hide();
		$("#max_value").parent(".form-group").hide();
		$("#fix_amount_deposit_bonus").parent(".form-group").show();
		$("#fix_amount_deposit").parent(".form-group").show();
	}else{
		$("#percent").parent(".form-group").show();
		$("#max_value").parent(".form-group").show();
		$("#fix_amount_deposit_bonus").parent(".form-group").hide();
		$("#fix_amount_deposit").parent(".form-group").hide();
	}
	console.log('aaa')
}
$(document).on('change', '#type', function(e) {
	changeType();
});

function changeType(){
	let type = $("#type").val();
	if (type == 5) {
		$("#pro_start_time").parent(".form-group").show();
		$("#pro_end_time").parent(".form-group").show();
		$("#number_of_deposit_days").parent(".form-group").hide();
	} else if (type == 6) {
		$("#pro_start_time").parent(".form-group").hide();
		$("#pro_end_time").parent(".form-group").hide();
		$("#number_of_deposit_days").parent(".form-group").show();
	} else {
		$("#pro_start_time").parent(".form-group").hide();
		$("#pro_end_time").parent(".form-group").hide();
		$("#number_of_deposit_days").parent(".form-group").hide();
	}
}
$(document).ready(function(){
	changeCategory();
	changeType();

	var editor_config ={
		selector: '#description',
		height: 400,
		theme: 'modern',
		path_absolute : BaseURL,
		plugins: [
			'advlist autolink lists link image charmap print preview hr anchor pagebreak',
			'searchreplace wordcount visualblocks visualchars code fullscreen',
			'insertdatetime media nonbreaking save table contextmenu directionality',
			'emoticons template paste textcolor colorpicker textpattern imagetools codesample'
		],
		toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
		toolbar2: 'print preview media | forecolor backcolor emoticons | codesample',
		image_advtab: true,
		relative_urls : false,
		content_css: [
			'//fonts.googleapis.com/css?family=Kanit:300,300i,400,400i',
			'//www.tinymce.com/css/codepen.min.css'
		]
	};
	tinymce.init(editor_config);
})
