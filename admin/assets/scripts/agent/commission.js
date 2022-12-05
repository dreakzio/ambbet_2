let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_start_report').datepicker('update',moment().startOf('month').format('YYYY-MM-DD'));
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_end_report').datepicker('update',moment().endOf('month').format('YYYY-MM-DD'));
	dataTable();
});

$(document).on('click',"#btn-search",function(){
	var date_start = $("#date_start_report").val();
	var date_end = $("#date_end_report").val();
	if(date_start == "" || date_end == ""){
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
		// "pageLength": 50,
		"paging": false,
		// 'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		// 'serverSide': false,
		// 'serverMethod': 'get',
		ajax: {
			url: BaseURL + 'agent/commission_list',
			data: function(d) {
				d.account = $('#account').val();
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
			},
			dataSrc: 'result',
		},
		columns: [{
			className: 'text-center',
			render: function(data, type, full, meta) {
				let html = "";
				let {
					day
				} = full;
				html = day;
				return html;
			}
		},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						deposit,day
					} = full;
					html = day.toString().indexOf("สรุปคอมมิชชั่น") >= 0 ? "" : numeral(deposit).format('0,0.00');
					return html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						withdraw,day
					} = full;
					html = day.toString().indexOf("สรุปคอมมิชชั่น") >= 0 ? "" :  numeral(withdraw).format('0,0.00');
					return html;
				}
			},{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						bonus,day
					} = full;
					html = day.toString().indexOf("สรุปคอมมิชชั่น") >= 0 ? "" : html = numeral(bonus).format('0,0.00');
					return html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						sum_amount,
						sum,
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
			},
			// {
			// 	className: 'text-center',
			// 	render: function(data, type, full, meta) {
			// 		let html = "";
			// 		let {
			// 			id,
			// 			day
			// 		} = full;
			// 		let detail = BaseURL + 'agent/commission_detail/' + $('#account').val() + '/' + $('#year').val() + '/' + $('#month').val() + '/' + day;
			// 		html += '<div class="btn-group">';
			// 		html += '<button type="button" class=" btn bg-gradient-success waves-effect waves-light  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span><i class="fa fa-edit mr-1"></i></span>จัดการ</button>';
			// 		html += '<div class="dropdown-menu animated ">'; //bounce flipInY
			// 		html += '<a target="_blank" class="dropdown-item" href="' + detail + '" ><span><i class="fa fa-search mr-1" style="color:#06d79c;"></i></span>ตรวจสอบ</a>';
			// 		// html += '<a class="dropdown-item" href="detail" ><span><i class="fa fa-search mr-1" style="color:#06d79c;"></i></span>ตรวจสอบ</a>';
			// 		// html += '<div class="dropdown-divider"></div>';
			// 		// html += '<a class="dropdown-item"  href="' + update + '"><span><i class="fa fa-edit mr-1" style="color:#ffb22b;"></i></span>แก้ไข</a>';
			// 		// html += '<div class="dropdown-divider"></div>';
			// 		// html += '<a class="dropdown-item btn_delete" href="javascript:void(0)" data-id="' + id + '" data-username="' + username + '"><span><i class="fa fa-trash mr-1" style="color:#ef5350;"></i></span>ลบ</a>';
			// 		html += '</div>';
			// 		html += '</div>';
			// 		return html;
			// 	}
			// }
		],
		drawCallback: function(settings) {
			let api = this.api();
			Swal.close();
		}
	});
	/*setInterval(function() {
		table.ajax.reload(null, false);
	}, 2800 * 60);*/
}
