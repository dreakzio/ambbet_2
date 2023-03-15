let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

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
		sweetAlert2('warning', 'กรุณาระบุวันที่สมัคร (จาก) - วันที่สมัคร (ถึง)');
	}else if(moment(date_start).unix() > moment(date_end).unix()){
		sweetAlert2('warning', 'วันที่สมัคร (จาก) ไม่ควรมากกว่า วันที่สมัคร (ถึง)');
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
			url: BaseURL + 'Transfer_marketing/user_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
				d.status = $('#status').val();
				d.role = typeof($("#role").val()) != "undefined" ? $("#role").val() : ""
			},
			// --- SATRT --- DEBUG RES
			// success: function (textStatus, status) {
			// 	console.log(textStatus);
			// 	console.log(status);
			// },
			// error: function(xhr, textStatus, error) {
			// 	console.log(xhr.responseText);
			// 	console.log(xhr.statusText);
			// 	console.log(textStatus);
			// 	console.log(error);
			// }
			// --- END --- DEBUG RES
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
				className: 'text-center',
				data: 'username'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						full_name,
						bank_name
					} = full;
					html = full_name;
					if (full_name == "") {
						html = bank_name;
					}
					return html;
				}
			},
			{
				className: 'text-center',
				data: 'from_account_username',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						from_account_username
					} = full;
					html = from_account_username !== null ? '<span class="text-success"> '+from_account_username+' </span>' : '<span class="text-danger">ไม่มี</span>';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id,
						username
					} = full;
					let update = BaseURL + 'transfer_marketing/transfer_marketing_form_update/' + id;
					html += '<div class="btn-group">';
					html += '<button type="button" class=" btn bg-gradient-success waves-effect waves-light  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span><i class="fa fa-edit mr-1"></i></span>จัดการ</button>';
					html += '<div class="dropdown-menu animated ">'; //bounce flipInY
					html += '<a class="dropdown-item"  href="' + update + '"><span><i class="fa fa-edit mr-1" style="color:#ffb22b;"></i></span>แก้ไข</a>';
					html += '</div>';
					html += '</div>';
					return html;
				}
			},
		],
		drawCallback: function(settings) {
			let api = this.api();
			$("body").tooltip({
				selector: '[data-toggle="tooltip"]'
			});
			$('.tooltip.fade.show').each(function(){
				if($('[aria-describedby="'+$(this).attr('id')+'"]').length == 0){
					$(this).remove();
				}
			});
			Swal.close();
		}
	});
	setInterval(function() {
		if(document.visibilityState == "visible") {
			table.ajax.reload(null, false);
		}
	}, 2800 * 60);
}