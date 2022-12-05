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
			url: BaseURL + 'LogTransferOut/log_transfer_out_list_page',
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
				className: 'text-right',
				render: function(data, type, full, meta) {
					let {
						amount
					} = full;
					let html = amount;

					return html == null ? 0 : numeral(html).format('0,0.00');
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						bank,
						bank_number,
						bank_acc_name
					} = full;
					let html = bank;
					let html_bank_number = bank_number;
					let html_bank_acc_name = bank_acc_name;

					return (typeof(bank_list[html]) != "undefined" ? bank_list[html] : html)+ bank_list[html]+(html_bank_number == null ? '' : ' | '+html_bank_number)+(html_bank_acc_name == null ? '' : ' | '+html_bank_acc_name);
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						bank_to,
						bank_number_to,
						bank_acc_name_to
					} = full;
					let html = bank_to;
					let html_bank_number = bank_number_to;
					let html_bank_acc_name = bank_acc_name_to;

					return (typeof(bank_code_list[html]) != "undefined" ? bank_code_list[html] : "-")+ (html_bank_number == null ? '' : ' | '+html_bank_number)+(html_bank_acc_name == null ? '' : ' | '+html_bank_acc_name) ;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						status
					} = full;
					let html = "";
					if(status == "1"){
						html = "<strong class='text-success'>สำเร็จ</strong>"
					}else if(status == "2"){
						html = "<strong class='danger'>ไม่สำเร็จ</strong>"
					}else{
						html = "<strong class='warning'>ระหว่างดำเนินการ</strong>"
					}

					return html == null ? '-' : html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						description,
						id
					} = full;
					let html = '<p>\n' +
						'  <a class="btn bg-gradient-warning waves-effect waves-light" data-toggle="collapse" href="#collapse'+id+'" role="button" aria-expanded="false" aria-controls="collapse'+id+'">\n' +
						'    รายละเอียด\n' +
						'  </a>\n' +
						'</p>\n' +
						'<div class="collapse" id="collapse'+id+'">\n' +
						'  <div class="card card-body">\n' +
						'    '+(description != '' && description != null ? description : '-')+'\n' +
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
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						admin_full_name
					} = full;
					let html = admin_full_name == null || admin_full_name == '' ? 'AUTO' : admin_full_name;

					return html == null ? '-' : html;
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
