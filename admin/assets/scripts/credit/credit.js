let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_start_report').datepicker('update',moment().subtract(2, "days").format('YYYY-MM-DD'));
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_end_report').datepicker('update',moment().format('YYYY-MM-DD'));
	$('#date').datepicker({
		format: 'yyyy-mm-dd',
		autoclose: true,
		// todayBtn: true,
		language: 'th',
		orientation: 'bottom'
		// thaiyear: true
	}).datepicker("setDate", moment().format('YYYY-MM-DD'));
	dataTable();
	$('#username').select2({
		language: 'th',
		theme: 'bootstrap4',
		width : '100%',
		ajax: {
			url: function (params) {
				params.search = {value : params.term};
				return BaseURL + 'user/user_list_page?start=0&length=50';
			},
			processResults: function (data) {
				var datas = JSON.parse(data)
				return {
					results: $.map(datas.data, function (item) {
						var label = (typeof(bank_list[item.bank]) != "undefined" ? bank_list[item.bank] : "")+" "+item.bank_number+" : "+item.username;
						return {
							text: label,
							id: item.id,
							data: item,
						}
					})
				};
			}}
	});
	$('#time_start_report').timepicker({
		maxHours : 24,
		showMeridian : false,
		showSeconds : true,
		minStep : 5,
		secondStep : 15,
		icons : {
			up: 'fa fa-angle-up',
			down: 'fa fa-angle-down'
		},
		defaultTime : "00:00:00"
	});
	$('#time_end_report').timepicker({
		maxHours : 24,
		showMeridian : false,
		showSeconds : true,
		minStep : 5,
		secondStep : 15,
		icons : {
			up: 'fa fa-angle-up',
			down: 'fa fa-angle-down'
		},
		defaultTime : "23:59:59"
	});
	$('#time').timepicker({
		maxHours : 24,
		showMeridian : false,
		showSeconds : true,
		minStep : 5,
		secondStep : 1,
		icons : {
			up: 'fa fa-angle-up',
			down: 'fa fa-angle-down'
		},
		defaultTime : moment().format('HH:ii')+":00"
	});
});

let loading_excel = false
$('#btn_export_excel').click(function(){
	if(!loading_excel){
		var start_datetime_search = $('#date_start_report').val()+" "+$('#time_start_report').val();
		var end_datetime_search  = $('#date_end_report').val()+" "+$('#time_end_report').val();
		if(
			start_datetime_search.trim().length == 0 &&
			end_datetime_search.trim().length == 0
		){
			sweetAlert2('warning', 'กรุณาระบุวัน-เวลา เริ่มต้นและสิ้นสุด');

		}else if(
			(start_datetime_search.trim().length > 0 && end_datetime_search.trim().length  == 0) ||
			end_datetime_search.trim().length > 0 && start_datetime_search.trim().length  == 0
		){
			sweetAlert2('warning', 'กรุณาระบุวัน-เวลา เริ่มต้นและสิ้นสุด');

		}else if(
			start_datetime_search.trim().length > 0 &&
			end_datetime_search.trim().length > 0 &&
			start_datetime_search > end_datetime_search
		){
			sweetAlert2('warning', 'กรุณาระบุวัน-เวลา เริ่มต้นและสิ้นสุดให้ถูกต้อง');
		}else {
			search_start_datetime = start_datetime_search;
			search_end_datetime = end_datetime_search;
			Swal.fire({
				type: 'warning',
				text: 'ยืนยันการดาวน์โหลด Excel',
				title: 'แจ้งเตือน',
				confirmButtonText: 'ตกลง',
				confirmButtonColor: '#7cd1f9',
				showCancelButton: true,
				cancelButtonText: 'ยกเลิก',
				reverseButtons: true,
			})
				.then((result) => {
					if (result.value) {
						loading_excel = true;
						Swal.fire({
							text: "กรุณารอสักครู่..",
							showConfirmButton: false,
							allowOutsideClick: false,
							allowEscapeKey: false,
						}),
							Swal.showLoading();
						var query = table.search()
						var params = {};
						if(search_start_datetime!="0" && search_start_datetime!=""){
							params.date_start = search_start_datetime;
							params.date_end = search_end_datetime;
						}
						params.search = {
							value : query
						};
						$.ajax({
							url: BaseURL + 'credit/credit_list_excel',
							data :params,
							method: "POST",
							dataType: 'json',
							success: function(response) {
								Swal.close();
								loading_excel = false;
								if (response.data) {
									if(response.data.length == 0){
										sweetAlert2('warning', 'ไม่มีข้อมูลที่สามารถออกรายงาน Excel ได้');
									}else{
										excel_config.data = [];
										let length_data = response.data.length;
										let data = response.data;
										let date_bank = '-';
										for(let i =0;i<length_data;i++){
											if(data[i].transaction == "1"){
												date_bank = data[i].date_bank;
											}
											excel_config.data.push({
												username : data[i].username,
												credit_before : data[i].credit_before,
												type :  data[i].type == 1 ? "เพิ่ม" : "ลด",
												process : data[i].process,
												credit_after : data[i].credit_after,
												admin_username : data[i].admin_username,
												date_bank : date_bank,
												created_at : data[i].created_at,
											})
										}
										excel_config.filename = "รายการเครดิต"+(search_start_datetime!="0"  && search_start_datetime!="" ? " "+search_start_datetime.replaceAll(":",".")+" ถึง "+search_end_datetime.replaceAll(":",".") : "");
										excel_config.sheetname = "รายการเครดิต";
										exportExcel();
									}
								} else {
									sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
								}
							},
							error: function() {
								Swal.close();
								loading_excel = false;
								sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
							}
						});
					} else {
						loading_excel = false;
					}
				});
		}
	}
	//alert('export excel');
});
let excel_config = {
	columns: [
		{
			label: "แก้ไขให้",
			field: "username",
		},
		{
			label: "ยอดก่อนหน้านี้",
			field: "credit_before",
		},
		{
			label: "ประเภท",
			field: "type",
		},
		{
			label: "จำนวน",
			field: "process",
		},
		{
			label: "รวม",
			field: "credit_after",
		},
		{
			label: "ทำรายการโดย",
			field: "admin_username",
		},
		{
			label: "วัน-เวลาสลิป",
			field: "date_bank",
		},
		{
			label: "วันที่สร้าง",
			field: "created_at",
		}
	],
	data: [],
	filename: 'excel',
	sheetname: 'SheetName'
}
var exportExcel =function(){
	let createXLSLFormatObj = [];
	let newXlsHeader = [];
	if (excel_config.columns.length === 0){
		console.log("Add columns!");
		return;
	}
	if (excel_config.data.length === 0){
		console.log("Add data!");
		return;
	}
	$.each(excel_config.columns, function(index, value) {
		newXlsHeader.push(value.label);
	});

	createXLSLFormatObj.push(newXlsHeader);
	$.each(excel_config.data, function(index, value) {
		let innerRowData = [];
		$.each(excel_config.columns, function(index, val) {
			if (val.dataFormat && typeof val.dataFormat === 'function') {
				innerRowData.push(val.dataFormat(value[val.field]));
			}else {
				innerRowData.push(value[val.field]);
			}
		});
		createXLSLFormatObj.push(innerRowData);
	});

	let filename = excel_config.filename + ".xlsx";

	let ws_name = excel_config.sheetname;

	let wb = XLSX.utils.book_new(),
		ws = XLSX.utils.aoa_to_sheet(createXLSLFormatObj);
	XLSX.utils.book_append_sheet(wb, ws, ws_name);
	XLSX.writeFile(wb, filename);
}

$(document).on('click',"#btn-search",function(){
	var date_start = $("#date_start_report").val();
	var date_end = $("#date_end_report").val();
	if(
		(date_start != "" && date_end == "") ||
		(date_end != "" && date_start == "")
	){
		sweetAlert2('warning', 'กรุณาระบุวันที่ (จาก) - วันที่ (ถึง)');
	}else if(moment(date_start).unix() > moment(date_end).unix()){
		sweetAlert2('warning', 'วันที่ (จาก) ไม่ควรมากกว่า วันที่ (ถึง)');
	}else{
		Swal.fire({
			// title: "แจ้งเตือน",
			text: "กรุณารอสักครู่..",
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
			confirmButtonText: '',
		}),
			Swal.showLoading();
		table.ajax.reload(null);
	}
});

function dataTable() {
	$.fn.dataTable.ext.errMode = 'none';
	table = $('#table').DataTable({
		"language": {
			"decimal": "",
			"emptyTable": "ไม่พบข้อมูล",
			"info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
			"infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
			"infoFiltered": "(ค้นหา จากทั้งหมด _MAX_ รายการ )",
			"infoPostFix": "",
			"thousands": ",",
			"lengthMenu": "แสดง _MENU_ รายการ",
			"loadingRecords": "Loading...",
			"processing": "กำลังค้นหาข้อมูล...",
			"search": "ค้นหา:",
			"zeroRecords": "ไม่พบข้อมูล",
			"paginate": {
				"first": "หน้าแรก",
				"last": "หน้าสุดท้าย",
				"next": "ถัดไป",
				"previous": "ย้อนกลับ"
			},
			"aria": {
				"sortAscending": ": activate to sort column ascending",
				"sortDescending": ": activate to sort column descending"
			}

		},
		"ordering": false,
		"pageLength": 25,
		'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		ajax: {
			url: BaseURL + 'credit/credit_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val()+" "+$('#time_start_report').val();
				d.date_end = $('#date_end_report').val()+" "+$('#time_end_report').val();
			},
		},
		columns: [
			// {
			// 	className: 'text-right',
			// 	render: function(data, type, full, meta) {
			// 		let row = meta.row + 1;
			// 		return row;
			// 	}
			// },
			{
				className: 'text-left',
				data: 'username'
			},
			{
				className: 'text-right',
				data: 'credit_before'
			},

			{
				className: 'text-center',
				render: function(data, type, full, meta) {

					let html = full.type == 1 ? '<i class="fa fa-arrow-up font-small-3 text-success mr-50"> เพิ่ม<i>' : '<i class="fa fa-arrow-down font-small-3 text-danger mr-50"> ลด<i>';
					return html;
				}
			},
			{
				className: 'text-right',
				data: 'process'
			},
			{
				className: 'text-right',
				data: 'credit_after'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						admin_username
					} = full;
					html = admin_username;
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						date_bank,
						transaction,
					} = full;
					html = transaction == "1" ? date_bank : '-';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						created_at
					} = full;
					html = created_at;
					return html;
				}
			}
			,
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let image = "";
					let {
						slip_image
					} = full;
					if (slip_image !== null) {
						image = BaseURL + 'assets/images/slip/'+slip_image;
						html = '<ul class="list-unstyled users-list m-0 align-items-center">\n' +
							'<img class="media-object rounded" src="'+image+'" alt="Avatar" height="40" width="40" onClick="showImage(\''+image+'\')" style="cursor: pointer;"></li></ul>';
					}else{
						html ='';
					}
					return html;
				}
			}
		],
		drawCallback: function(settings) {
			let api = this.api();
			Swal.close();
		}
	});
	setInterval(function() {
		if(document.visibilityState == "visible") {
			table.ajax.reload(null, false);
		}
	}, 2800 * 60);
}

// $('#username').select2({
// 	ajax: {
// 		url: BaseURL + '/user/user_select2',
// 		dataType: 'json',
// 		delay: 1000,
// 		data: function(params) {
// 			params.page = params.page || 1;
// 			return {
// 				search: params.term,
// 			};
// 		},
// 		error: function(jqXHR, status, error) {
// 			// sweetAlert2("warning", "การเชื่อมต่อขัดข้อง");
// 			return {
// 				results: []
// 			}; // Return dataset to load after error
// 		},
// 		processResults: function(data, params) {
// 			// console.log(data.count_filtered);
// 			return {
// 				results: data.data,
// 				// pagination: {
// 				// 	"more": true
// 				// 	// more: (params.page * 10) < data.count
// 				// }
// 			};
// 		}
// 	},
// 	escapeMarkup: function(markup) {
// 		return markup;
// 	},
// 	templateResult: function(data) {
// 		if (data.loading) {
// 			return 'กำลังค้นหาข้อมูล'
// 		}
// 		let option = "";
// 		option += '<option value=' + data.id + ' >' + data.username + '</option>';
// 		return option;
// 	},
// 	templateSelection: function(data) {
// 		if (data.id == "" || data.selected) {
// 			return data.text;
// 		}
// 		let option = "";
// 		option += '<option value=' + data.id + ' >' + data.username + '</option>';
// 		$('#credit_before').val(numeral(data.amount_deposit_auto).format('0,0.00'));
// 		return option;
// 	},
// 	language: 'th',
// 	theme: 'bootstrap4',
// 	placeholder: 'เลือก Username'
// });

$(document).on('change', '#username', function(e) {
	let value = $(this).val();
	if (value) {
		let data = $("#username").select2('data')[0].data;
		$('#credit_before').val(numeral(data.amount_deposit_auto).format('0,0.00'));
	}

});
$('#process').bind("cut copy paste", function(e) {
	e.preventDefault();
});
$(document).on('keyup', '#process', function(e) {
	let process = $(this).val();
	if (process != "") {
		$(this).val(numeral(process).format('0,0'));
	}
});
var loadding_credit_add = false;
function fileValidation() {
	var fileInput =
		document.getElementById('image_file');

	var filePath = fileInput.value;

	// Allowing file type
	var allowedExtensions =
		/(\.jpg|\.jpeg|\.png|\.gif)$/i;

	if (!allowedExtensions.exec(filePath)) {
		//alert('Invalid file type');
		sweetAlert2('warning', 'กรุณาเลือกไฟล์รูปภาพเท่านั้น ขนาดไม่เกิน 300px X 600px');
		fileInput.value = '';
		return false;
	}
	else
	{

		// Image preview
		if (fileInput.files && fileInput.files[0]) {
			var reader = new FileReader();
			reader.onload = function(e) {
				document.getElementById(
					'imagePreview').innerHTML =
					'<img src="' + e.target.result
					+ '"/>';
			};

			reader.readAsDataURL(fileInput.files[0]);
		}
	}
}

//$(document).on('click', '#btn_create', function() {
$('#upload_form').on('submit', function(e){
	e.preventDefault();
	if(!loadding_credit_add){
		let value = $('#username').val();
		if (value == "") {
			sweetAlert2('warning', 'กรุณาเลือก Username');
			return;
		}
		let sum = 0;
		let username = value;
		if (username == "") {
			sweetAlert2('warning', 'กรุณาเลือก Username');
			return;
		}
		let credit_before = $('#credit_before').val();
		let process = $('#process').val();
		if (process.toString().trim().length == 0) {
			sweetAlert2('warning', 'กรุณารบุจำนวนเงิน');
			return;
		}
		if (process == 0) {
			sweetAlert2('warning', 'จำนวนเงินต้องมากกว่า 0');
			return;
		}
		if (process == "") {
			$('#credit_after').val('');
			return;
		}
		let type = $('#type').val();
		if (type == 1) {
			if (username != "" && process != "") {
				sum = Number(numeral(credit_before).format('0')) + Number(numeral(process).format('0'));
				$('#credit_after').val(numeral(sum).format('0,0.00'));
			}
		} else {
			if (username != "" && process != "") {
				sum = Number(numeral(credit_before).format('0')) - Number(numeral(process).format('0'));
				$('#credit_after').val(numeral(sum).format('0,0.00'));
			}
		}
		if (sum < 0) {
			sweetAlert2('warning', 'เครดิตคงเหลือไม่เพียงพอ');
			return;
		}
		if($('#image_file').val() == '')
		{
			sweetAlert2('warning', 'กรุณาแนบสลิปโอนเงิน');
			return;
		}
		let transaction = $('#transaction').val();
		let date = $('#date').val();
		let time = $('#time').val();
		if(transaction != 1){
			let date = moment().format('YYYY-MM-DD');
			let time =  moment().format('HH:mm');
		}
		if (transaction == 1) {
			if (date == "") {
				sweetAlert2('warning', 'กรุณาเลือกวันที่');
				return;
			}
			if (time == "") {
				sweetAlert2('warning', 'กรุณาเลือกเวลา');
				return;
			}
		}
		loadding_credit_add = true;
		Swal.fire({
			text: "กรุณารอสักครู่..",
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
			confirmButtonText: '',
		});
		Swal.showLoading();

		//console.log(fileUpload);
		var d = $('#image_file')[0].files[0]
		let formData = new FormData();
		formData.append('account_id', username);
		formData.append('process', process);
		formData.append('type', type);
		formData.append('date', date);
		formData.append('time', time);
		formData.append('transaction', transaction);
		formData.append('image_file', d);

		$.ajax({
			url: BaseURL + "credit/credit_history_create",
			method: "POST",
			/*data: {
				account_id: username,
				process,
				type,
				date,
				time,
				transaction
			},*/
			data : formData,
			dataType: 'json',
			contentType: false,
			cache: false,
			processData:false,
			enctype: 'multipart/form-data',
			success: function(response) {
				loadding_credit_add = false;
				if (response.result) {
					location.reload();
					// table.ajax.reload();
					// // $('#username').empty().trigger('change').val('');
					// $('#username').trigger('change').val('');
					//
					// $('#credit_before').val('');
					// $('#type').val('1');
					// $('#process').val('');
					// $('#credit_after').val('');
					// $('#transaction').val('0').trigger('change');
					// let Toast = Swal.mixin({
					// 	toast: true,
					// 	position: 'top-end',
					// 	showConfirmButton: false,
					// 	timer: 2500
					// });
					// Toast.fire({
					// 	type: 'success',
					// 	title: 'บันทึกข้อมูลเรียบร้อยแล้ว'
					// });
				} else {
					if(response.code && response.code == "DUPLICATE") {
						Swal.close();
						Swal.fire({
							type: 'warning',
							title: 'แจ้งเตือน',
							text: response.message+" ยืนยันการทำรายการนี้หรือไม่ ?",
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
									}),
										Swal.showLoading();
									$.ajax({
										url: BaseURL + "credit/credit_history_create",
										method: "POST",
										data: {
											account_id: username,
											force_add_credit: "Y",
											process :process,
											type : type,
											date : date,
											time : time,
											transaction : transaction
										},
										dataType: 'json',
										success: function (response) {
											if (response.result) {
												location.reload();
											}
										},
										error: function () {
											sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
										}
									})
								}
							})
					}else{
						sweetAlert2('warning', response.message);
					}

				}
			},
			error: function() {
				loadding_credit_add = false;
				sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
			}
		});
	}
});
$(document).on('change', '#username', function() {
	calCulateCredit()
});

$(document).on('keyup', '#process', function(e) {
	calCulateCredit();
});

$(document).on('change', '#process', function(e) {
	calCulateCredit();
});
$(document).on('change', '#type', function(e) {
	let value = $(this).val();
	if (value == 2) {
		$('#div_transaction').css('display', 'none');
		$('#transaction').val('0').trigger('change');
	} else {
		$('#div_transaction').css('display', 'block');
	}
	calCulateCredit();
});

function calCulateCredit() {
	let sum = 0;
	let username = $('#username').val();
	let credit_before = $('#credit_before').val();
	let process = $('#process').val();
	if (process == "") {
		$('#credit_after').val('');
		return;
	}
	let type = $('#type').val();
	if (type == 1) {
		if (username != "" && process != "") {
			sum = Number(numeral(credit_before).format('0')) + Number(numeral(process).format('0'));
			$('#credit_after').val(numeral(sum).format('0,0.00'));
		}
	} else {
		if (username != "" && process != "") {
			sum = Number(numeral(credit_before).format('0')) - Number(numeral(process).format('0'));
			$('#credit_after').val(numeral(sum).format('0,0.00'));
		}
	}
}

function showImage(url){
	Swal.fire({
		title: null,
		text: null,
		imageUrl: url,
		imageWidth: 800,
		imageHeight: 600,
		imageAlt: 'สลิปโอนเงิน',
	})
}
/*$('.clockpicker').clockpicker({
	donetext: 'Done',
}).find('input').change(function() {
	// console.log(this.value);
});*/
$(document).on('change', '#transaction', function(e) {
	let value = $(this).val();
	if (value == 0) {
		$('#div_date').css('display', 'none');
	} else {
		$('#div_date').css('display', 'flex');
	}
});
