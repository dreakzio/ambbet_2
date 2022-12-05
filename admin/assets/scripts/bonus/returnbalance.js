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
			url: BaseURL + 'bonus/returnbalance_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
			},
		},
		columns: [{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						username,
						username_from,
						username_from_ref
					} = full;
					let html = username;
					html = (html == null || html == "") ? username_from : html;

					return html+ (username_from_ref != "" && username_from_ref != null ? " แนะนำโดย "+username_from_ref+" (โบนัสขั้น 2)" : "");
				}
			},
			{
				className: 'text-right',
				render: function(data, types, full, meta) {
					let {
						amount,
						type,
						turnover_amount,
					} = full;
					let html = turnover_amount;
					html = html == null || html == '' ? 0 : html;
					if(type == "1"){
						html = "-"+numeral(html).format('0,0.00')
						return html;
					}else if(type == "3"){
						html = "-"+numeral(html).format('0,0.00')
						return html;
					}else if(type == "4"){
						html = "+"+numeral(html).format('0,0.00')
						return html;
					}else{
						html = numeral(html).format('0,0.00')
					}
					return "-"+html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let {
						percent
					} = full;
					let html = percent;
					return numeral(html).format('0,0.00') + ' %';
				}
			},
			{
				className: 'text-right',
				render: function(data, types, full, meta) {
					let {
						turn,
					} = full;
					let html = turn;
					return html == null || html == '' ? 0+" เท่า" : numeral(html).format('0,0.00')+" เท่า";
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let {
						sum_amount
					} = full;
					let html = sum_amount;
					return numeral(html).format('0,0.00');
				}
			},
			// {
			// 	className: 'text-right',
			// 	render: function(data, type, full, meta) {
			// 		let {
			// 			turn
			// 		} = full;
			// 		let html = turn;
			// 		return html;
			// 	}
			// },
			// {
			// 	className: 'text-right',
			// 	render: function(data, type, full, meta) {
			// 		let {
			// 			sum_amount
			// 		} = full;
			// 		let html = sum_amount;
			// 		return html;
			// 	}
			// },
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						created_at
					} = full;
					let html = created_at;
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
