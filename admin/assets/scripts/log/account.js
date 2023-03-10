let table;
$(document).ready(function() {
	/*function start() {
        setTimeout(function() {
			document.addEventListener("visibilitychange", function() {
				if (document.visibilityState == "visible") {
					table.ajax.reload(null);
				}
			})
            start();
        }, 2800 * 60);
    }*/

    //start();
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
			url: BaseURL + 'LogAccount/log_account_list_page',
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
						username
					} = full;
					let html = username;

					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						manage_by_username,
						role,
					} = full;
					let html = manage_by_username+ (role != "" && role != null ? " ("+role+")" : "");

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
					let html = "";
					let {
						id,
						data_before,
						data_after
					} = full;
					html = "<button type='button' data-id='" + id + "' data-index='"+meta.row+"' class='btn_detail btn bg-gradient-warning waves-effect waves-light' >รายละเอียด</button>";
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
$(document).on('click', '.btn_detail', function() {
	$('#table_detail tbody').html('');
	let data = $(this).data();
	let data_list = table.rows().data()
	let data_before = JSON.parse(data_list[data.index]['data_before'])
	let data_after = JSON.parse(data_list[data.index]['data_after'])
	let turn_over_html = "";
	for(let i =0;i<game_code_list.length;i++){
		turn_over_html += '<td>Turn over before ('+game_code_list[i]+')</td>' +
			'<td class="text-center '+(data_before['turn_before_'+game_code_list[i].toString().toLowerCase()] != data_after['turn_before_'+game_code_list[i].toString().toLowerCase()] ? "bg-warning" : "")+'">'+data_before['turn_before_'+game_code_list[i].toString().toLowerCase()]+'</td>' +
			'<td class="text-center '+(data_before['turn_before_'+game_code_list[i].toString().toLowerCase()] != data_after['turn_before_'+game_code_list[i].toString().toLowerCase()] ? "bg-warning" : "")+'">'+data_after['turn_before_'+game_code_list[i].toString().toLowerCase()]+'</td>' +
			'</tr>'+
			'<tr>' +
			'<td>Turn over  ('+game_code_list[i]+')</td>' +
			'<td class="text-center '+(data_before['turn_over_'+game_code_list[i].toString().toLowerCase()] != data_after['turn_over_'+game_code_list[i].toString().toLowerCase()] ? "bg-warning" : "")+'">'+data_before['turn_over_'+game_code_list[i].toString().toLowerCase()]+'</td>' +
			'<td class="text-center '+(data_before['turn_over_'+game_code_list[i].toString().toLowerCase()] != data_after['turn_over_'+game_code_list[i].toString().toLowerCase()] ? "bg-warning" : "")+'">'+data_after['turn_over_'+game_code_list[i].toString().toLowerCase()]+'</td>' +
			'</tr>'+
			'<tr>';
	}
	$('#table_detail tbody').append(
		'<tr>' +
		'<td>เบอร์มือถือ</td>' +
		'<td class="text-center '+(data_before['phone'] != data_after['phone'] ? "bg-warning" : "")+'">'+data_before['phone']+'</td>' +
		'<td class="text-center '+(data_before['phone'] != data_after['phone'] ? "bg-warning" : "")+'">'+data_after['phone']+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>รหัสผ่าน</td>' +
		'<td colspan="2" class="text-center '+(data_before['is_edit_pass'] != data_after['is_edit_pass'] ? "bg-warning" : "")+'">'+(data_before['is_edit_pass'] != data_after['is_edit_pass'] ? "ใช่" : "ไม่")+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>ชื่อ-นามสกุล</td>' +
		'<td class="text-center '+(data_before['full_name'] != data_after['full_name'] ? "bg-warning" : "")+'">'+data_before['full_name']+'</td>' +
		'<td class="text-center '+(data_before['full_name'] != data_after['full_name'] ? "bg-warning" : "")+'">'+data_after['full_name']+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>Line</td>' +
		'<td class="text-center '+(data_before['line_id'] != data_after['line_id'] ? "bg-warning" : "")+'">'+data_before['line_id']+'</td>' +
		'<td class="text-center '+(data_before['line_id'] != data_after['line_id'] ? "bg-warning" : "")+'">'+data_after['line_id']+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>ธนาคาร</td>' +
		'<td class="text-center '+(data_before['bank'] != data_after['bank'] ? "bg-warning" : "")+'">'+bank_list[data_before['bank']]+'</td>' +
		'<td class="text-center '+(data_before['bank'] != data_after['bank'] ? "bg-warning" : "")+'">'+bank_list[data_after['bank']]+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>เลขบัญชี</td>' +
		'<td class="text-center '+(data_before['bank_number'] != data_after['bank_number'] ? "bg-warning" : "")+'">'+data_before['bank_number']+'</td>' +
		'<td class="text-center '+(data_before['bank_number'] != data_after['bank_number'] ? "bg-warning" : "")+'">'+data_after['bank_number']+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>ชื่อบัญชี</td>' +
		'<td class="text-center '+(data_before['bank_name'] != data_after['bank_name'] ? "bg-warning" : "")+'">'+data_before['bank_name']+'</td>' +
		'<td class="text-center '+(data_before['bank_name'] != data_after['bank_name'] ? "bg-warning" : "")+'">'+data_after['bank_name']+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>Turn date</td>' +
		'<td class="text-center '+(data_before['turn_date'] != data_after['turn_date'] ? "bg-warning" : "")+'">'+data_before['turn_date']+'</td>' +
		'<td class="text-center '+(data_before['turn_date'] != data_after['turn_date'] ? "bg-warning" : "")+'">'+data_after['turn_date']+'</td>' +
		'</tr>'+
		'<tr>' +
		turn_over_html+
		'<td>สถานะ</td>' +
		'<td class="text-center '+(data_before['role'] != data_after['role'] ? "bg-warning" : "")+'">'+role_display_list[data_before['role']]+'</td>' +
		'<td class="text-center '+(data_before['role'] != data_after['role'] ? "bg-warning" : "")+'">'+role_display_list[data_after['role']]+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>พันธมิตร</td>' +
		'<td class="text-center '+(data_before['agent'] != data_after['agent'] ? "bg-warning" : "")+'">'+(data_before['agent'] == "1" ? "ใช่" : "ไม่ใช่")+'</td>' +
		'<td class="text-center '+(data_before['agent'] != data_after['agent'] ? "bg-warning" : "")+'">'+(data_after['agent'] == "1" ? "ใช่" : "ไม่ใช่")+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>Commission Percent</td>' +
		'<td class="text-center '+(data_before['commission_percent'] != data_after['commission_percent'] ? "bg-warning" : "")+'">'+data_before['commission_percent']+'</td>' +
		'<td class="text-center '+(data_before['commission_percent'] != data_after['commission_percent'] ? "bg-warning" : "")+'">'+data_after['commission_percent']+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>รับโบนัสคืนยอดเสีย</td>' +
		'<td class="text-center '+(data_before['is_active_return_balance'] != data_after['is_active_return_balance'] ? "bg-warning" : "")+'">'+(data_before['is_active_return_balance'] == "1" ? "ใช่" : "ไม่")+'</td>' +
		'<td class="text-center '+(data_before['is_active_return_balance'] != data_after['is_active_return_balance'] ? "bg-warning" : "")+'">'+(data_after['is_active_return_balance'] == "1" ? "ใช่" : "ไม่")+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>อยู่ภายใต้ยูส</td>' +
		'<td class="text-center '+(data_before['ref_name'] != data_after['ref_name'] ? "bg-warning" : "")+'">'+data_before['ref_name']+'</td>' +
		'<td class="text-center '+(data_before['ref_name'] != data_after['ref_name'] ? "bg-warning" : "")+'">'+data_after['ref_name']+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>สถานะ BOT ถอนออโต้</td>' +
		'<td class="text-center '+(typeof(data_before['is_auto_withdraw'] ) != "undefined" && data_before['is_auto_withdraw'] != data_after['is_auto_withdraw'] ? "bg-warning" : "")+'">'+(typeof(data_before['is_auto_withdraw'] ) != "undefined" && data_before['is_auto_withdraw'] == "0" ? "ปิด" : "เปิด")+'</td>' +
		'<td class="text-center '+(typeof(data_before['is_auto_withdraw'] ) != "undefined" && data_before['is_auto_withdraw'] != data_after['is_auto_withdraw'] ? "bg-warning" : "")+'">'+(typeof(data_after['is_auto_withdraw'] ) != "undefined" && data_after['is_auto_withdraw'] == "0" ? "ปิด" : "เปิด")+'</td>' +
		'</tr>'+
		'<tr>' +
		'<td>หมายเหตุ</td>' +
		'<td class="text-center '+(typeof(data_before['remark']) != "undefined" && typeof(data_after['remark']) != "undefined" && data_before['remark'] != data_after['remark'] ? "bg-warning" : "")+'">'+(typeof(data_before['remark']) != "undefined" ? data_before['remark'] : "-")+'</td>' +
		'<td class="text-center '+(typeof(data_before['remark']) != "undefined" && typeof(data_after['remark']) != "undefined" && data_before['remark'] != data_after['remark'] ? "bg-warning" : "")+'">'+(typeof(data_after['remark']) != "undefined" ? data_after['remark'] : "-")+'</td>' +
		'</tr>'
	)
	$("#modal_detail").modal('show')
});
