let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	}).datepicker("setDate", moment().format('YYYY-MM-DD'));
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
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	}).datepicker("setDate", moment().format('YYYY-MM-DD'));
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
	dataTable();
});

let loading_excel = false
$('#btn_export_excel').click(function(){
	if(!loading_excel){
		var start_datetime_search = $('#date_start_report').val()+" "+$('#time_start_report').val();
		var end_datetime_search  = $('#date_end_report').val()+" "+$('#time_end_report').val();
		var type_search  = $('#type').val();
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
			search_type = type_search;
			Swal.fire({
				icon: 'warning',
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
						params.type = search_type;
						params.search = {
							value : query
						};
						$.ajax({
							url: BaseURL + 'report/add_bonus_list_excel',
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
										for(let i =0;i<length_data;i++){
											if(data[i].created_at.toString().indexOf("สรุป") >= 0){
												excel_config.data.push({
													created_at : data[i].created_at,
													username : '',
													from_amount : '',
													amount :  '',
													type : '',
													description : '',
													manage_by : data[i].manage_by,
												})
											}else{
												excel_config.data.push({
													created_at : data[i].created_at,
													username : data[i].username,
													from_amount : data[i].from_amount,
													amount : data[i].amount,
													type : typeof(type_log_add_credit_list[data[i].type]) != "undefined" ? type_log_add_credit_list[data[i].type] : data[i].type,
													description :  data[i].description,
													manage_by : data[i].manage_by != null ? data[i].manage_by_username+" - "+data[i].manage_by_full_name : "AUTO"
												})
											}

										}
										excel_config.filename = "รายงานการรับโบนัส"+(search_start_datetime!="0"  && search_start_datetime!="" ? " "+search_start_datetime.replaceAll(":",".")+" ถึง "+search_end_datetime.replaceAll(":",".") : "");
										excel_config.sheetname = "รายงานการรับโบนัส";
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
			label: "วัน-เวลา",
			field: "created_at",
		},
		{
			label: "ยูส",
			field: "username",
		},
		{
			label: "จากยอด",
			field: "from_amount",
		},
		{
			label: "โบนัสที่ได้รับ",
			field: "amount",
		},
		{
			label: "ประเภท",
			field: "type",
		},
		{
			label: "รายละเอียด",
			field: "description",
		},
		{
			label: "ดำเนินการโดย",
			field: "manage_by",
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
			url: BaseURL + 'report/add_bonus_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val()+" "+$('#time_start_report').val();
				d.date_end = $('#date_end_report').val()+" "+$('#time_end_report').val();
				d.type = $('#type').val();
			},
		},
		columns: [
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					if(full.created_at.toString().indexOf("สรุป") >= 0){
						return '<strong>'+full.created_at+'</strong>';
					}
					return full.created_at;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					if(full.created_at.toString().indexOf("สรุป") >= 0){
						return '';
					}
					return full.username;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					if(full.created_at.toString().indexOf("สรุป") >= 0){
						return '';
					}
					return numeral(full.from_amount).format('0,0.00');
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					if(full.created_at.toString().indexOf("สรุป") >= 0){
						return '';
					}
					return '<strtong class="text-success">'+numeral(full.amount).format('0,0.00')+'</strtong>';
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					if(full.created_at.toString().indexOf("สรุป") >= 0){
						return '';
					}
					return typeof(type_log_add_credit_list[full.type]) != "undefined" ? type_log_add_credit_list[full.type] : full.type;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					if(full.created_at.toString().indexOf("สรุป") >= 0){
						return '';
					}
					return full.description;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						manage_by,
					} = full;
					if(full.created_at.toString().indexOf("สรุปยอดรวม") >= 0){
						return '<strong class="text-success">'+numeral(manage_by).format('0,0.00')+'</strong>';
					}else if(full.created_at.toString().indexOf("สรุปจำนวนรายการ") >= 0){
						return '<strong class="text-success">'+numeral(manage_by).format('0,0')+'</strong>';
					}
					html = full.manage_by_username == null ? "AUTO" : full.manage_by_username+" - "+full.manage_by_full_name;
					return html;
				}
			}
		],
		drawCallback: function(settings) {
			let api = this.api();
			Swal.close();
			$("#table tbody tr:last").css({"background-color":"rgba(34, 41, 47, 0.075)"})
			$("#table tbody tr").eq(-2).css({"background-color":"rgba(34, 41, 47, 0.075)"})
		}
	});
	setInterval(function() {
		if(document.visibilityState == "visible") {
			table.ajax.reload(null, false);
		}
	}, 2800 * 60);
}

$('.clockpicker').clockpicker({
	donetext: 'Done',
}).find('input').change(function() {
	// console.log(this.value);
});
var validateInputNumber = function(e) {
	var t = e.value;
	t = t.replace("-","");
	e.value = ((t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), 3)) : t).replace(/[^.\d]/g, '');
}
