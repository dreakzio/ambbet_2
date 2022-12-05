let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_start_report').datepicker('update',moment().subtract(2,"days").format('YYYY-MM-DD'));
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_end_report').datepicker('update',moment().format('YYYY-MM-DD'));
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
		"ordering": false,
		"pageLength": 25,
		'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		// 'serverSide': false,
		// 'serverMethod': 'get',
		ajax: {
			url: BaseURL + 'LogLineNotify/log_line_notify_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
			},
		},
		columns: [
			{
				className: 'text-center',
				data: 'id'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						message
					} = full;
					let html = message;

					return html != "" && html != null ? html : "-";
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						status
					} = full;
					let html = "-";
					if(status == "1"){
						html = "<p class='mb-0 text-success'>ดำเนินการเรียบร้อย</p>";
					}else{
						html = "<p class='mb-0 text-warning'>รอดำเนินการ</p>";
					}
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, types, full, meta) {
					let {
						type
					} = full;
					if(type == "1"){
						html = "<strong class='mb-0 font-weight-bold text-success'><i class='fa fa-arrow-up font-weight-bold mr-50'></i>ฝาก</strong>";
					}else if(type == "2"){
						html = "<strong class='mb-0 font-weight-bold text-danger'><i class='fa fa-arrow-down font-weight-bold mr-50'></i>ถอน</strong>";
					}else if(type == "3"){
						html = "<strong class='mb-0 font-weight-bold text-info'><i class='fa fa-bar-chart font-weight-bold mr-50'></i>รายงานประจำวัน</strong>";
					}else if(type == "5"){
						html = "<strong class='mb-0 font-weight-bold text-primary'><i class='fa fa-user-plus font-weight-bold mr-50'></i>สมัครสมาชิก</strong>";
					}else{
						html = "<strong class='mb-0 font-weight-bold text-warning'><i class='fa fa-list-alt font-weight-bold mr-50'></i>อื่นๆ</strong>";
					}
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						response,
						id
					} = full;
					let html = '<p>\n' +
						'  <a class="btn bg-gradient-warning waves-effect waves-light" data-toggle="collapse" href="#collapse'+id+'" role="button" aria-expanded="false" aria-controls="collapse'+id+'">\n' +
						'    รายละเอียด\n' +
						'  </a>\n' +
						'</p>\n' +
						'<div class="collapse" id="collapse'+id+'">\n' +
						'  <div class="card card-body">\n' +
						'    '+(response != '' && response != null ? response : '-')+'\n' +
						'  </div>\n' +
						'</div>';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						created_at
					} = full;
					let html = created_at;
					return html;
				}
			},
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
