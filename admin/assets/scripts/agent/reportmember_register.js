let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	//$('#date_start_report').datepicker('update',moment().format('YYYY-MM-DD'));
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	//$('#date_end_report').datepicker('update',moment().format('YYYY-MM-DD'));
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
		ajax: {
			url: BaseURL + 'agent/reportmember_register_list',
			data: function(d) {
				//d.month = $('#month').val();
				//d.year = $('#year').val();
				d.account = $('#account').val();
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
				d.status = $('#status').val();
			},
			//dataSrc: 'result',
		},
		columns: [{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						username,
						account_agent_username
					} = full;
					html = username+ " : "+(account_agent_username != "" && account_agent_username != null ? account_agent_username : " ยังไม่ได้รับยูสเซอร์");
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
			/*{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						deposit
					} = full;
					html = html = numeral(deposit).format('0,0.00');
					return html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						withdraw
					} = full;
					html = html = numeral(withdraw).format('0,0.00');
					return html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						sum_amount,
						sum
					} = full;
					if (sum_amount) {
						if (sum_amount >= 0) {
							html += '+';
						} else {
							html += '';
						}
						html += numeral(sum_amount).format('0,0.00');
					} else {
						html = numeral(sum).format('0,0.00');
					}
					return html;
				}
			},*/
		],
		drawCallback: function(settings) {
			var infos = table.page.info();
			if(infos.recordsTotal){
				$("#text_sum_users").text(numeral(infos.recordsTotal).format('0,0'))
			}
			let api = this.api();
			Swal.close();
		}
	});
	/*setInterval(function() {
		table.ajax.reload(null, false);
	}, 2800 * 60);*/
}
