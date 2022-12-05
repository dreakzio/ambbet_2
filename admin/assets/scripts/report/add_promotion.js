let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	}).datepicker("setDate", moment().subtract('6','days').format('YYYY-MM-DD'));
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
		"searching": false,
		info: false,
		"ordering": false,
		"paging": false,
		'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		ajax: {
			url: BaseURL + 'report/add_promotion_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val()+" "+$('#time_start_report').val();
				d.date_end = $('#date_end_report').val()+" "+$('#time_end_report').val();
			},
		},
		columns: [
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					if(full.name.toString().indexOf("สรุป") >= 0){
						return '<strong>'+full.name+'</strong>';
					}
					return full.name;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						total,
					} = full;
					if(full.name.toString().indexOf("สรุป") >= 0){
						return '<strong class="text-success">'+numeral(total).format('0,0')+'</strong>';
					}
					return '<strong class="text-dark">'+numeral(total).format('0,0')+'</strong>';
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						total_bonus,
					} = full;
					if(full.name.toString().indexOf("สรุป") >= 0){
						return '<strong class="text-success">'+numeral(total_bonus).format('0,0.00')+'</strong>';
					}
					return '<strong class="text-dark">'+numeral(total_bonus).format('0,0.00')+'</strong>';
				}
			}
		],
		drawCallback: function(settings) {
			let api = this.api();
			Swal.close();
			$("#table tbody tr:last").css({"background-color":"rgba(34, 41, 47, 0.075)"})
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
