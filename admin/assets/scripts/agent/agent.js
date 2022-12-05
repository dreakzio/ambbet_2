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
			url: BaseURL + 'agent/agent_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
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
				data: 'line_id'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let image = "";
					let {
						bank
					} = full;
					if (bank == '1' || bank == '01') {
						image = BaseURL + 'assets/images/bank/1.png';
					}
					if (bank == '2' || bank == '02') {
						image = BaseURL + 'assets/images/bank/2.png';
					}
					if (bank == '3' || bank == '03') {
						image = BaseURL + 'assets/images/bank/3.png';
					}
					if (bank == '4' || bank == '04') {
						image = BaseURL + 'assets/images/bank/5.png';
					}
					if (bank == '5' || bank == '05') {
						image = BaseURL + 'assets/images/bank/6.png';
					}
					if (bank == '6' || bank == '06') {
						image = BaseURL + 'assets/images/bank/4.png';
					}
					if (bank == '7' || bank == '07') {
						image = BaseURL + 'assets/images/bank/7.png';
					}
					if (bank == '8' || bank == '08') {
						image = BaseURL + 'assets/images/bank/9.png';

					}
					if (bank == '9' || bank == '09') {
						image = BaseURL + 'assets/images/bank/baac.png';
					}
					if (bank == '10' ) {
						image = BaseURL + 'assets/images/bank/10.png';
					}
					html = '<ul class="list-unstyled users-list m-0 align-items-center">\n' +
						'<li data-toggle="tooltip" data-popup="tooltip-custom" data-placement="bottom" data-original-title="'+(typeof(bank_list[bank]) != "undefined" ? bank_list[bank] : "-")+'" class="avatar pull-up m-0">' +
						'<img class="media-object rounded" src="'+image+'" alt="Avatar" height="40" width="40"></li></ul>';
					//html = '<img src="' + image + '" class="rounded" title="'+(typeof(bank_list[bank]) != "undefined" ? bank_list[bank] : "-")+'" style="width:50px;  " />'
					return html;
				}
			},
			{
				className: 'text-center',
				data: 'bank_number'
			},
			{
				className: 'text-center',
				data: 'created_at'
			},
			/*{
				 className: 'text-right',
				data : 'sum_commission',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						sum_commission
					} = full;
					html = parseFloat(sum_commission) <= 0 ? 0 : numeral(parseFloat(sum_commission)).format('0,00');
					return html;
				}
			},*/
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id,
					} = full;
					let report_url = BaseURL + 'agent/commission/' + id;
					html = '<a href="'+report_url+'" class="btn_report_member btn bg-gradient-success waves-effect waves-light" >Commission</a>';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id,
						sum_member
					} = full;
					let report_url = BaseURL + 'agent/reportmember_register/' + id;
					html = '<a href="'+report_url+'" class="btn_report_member btn bg-gradient-primary waves-effect waves-light" >'+numeral(parseFloat(sum_member)).format('0,00')+' คน : ข้อมูลรายงาน</a>';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id,
						account_agent_username,
						account_agent_password
					} = full;
					// data-toggle="popover" data-placement="right" data-container="body" data-original-title="Username / Password" data-content="ดด" aria-describedby="popover804090"
					//html = '<button type="button" data-id="' + id + '" data-username="' + account_agent_username + '"  data-password="' + account_agent_password + '" class="btn_report_member btn bg-gradient-warning waves-effect waves-light" >ข้อมูลรายงาน</button>';
					let report_url = BaseURL + 'agent/reportmember/' + id;
					html = '<a href="'+report_url+'" class="btn_report_member btn bg-gradient-warning waves-effect waves-light" >ข้อมูลรายงาน</a>';
					return html;
				}
			},
			// {
			// 	className: 'text-right',
			// 	render: function(data, type, full, meta) {
			// 		let html = "";
			// 		return html;
			// 	}
			// },
			/*{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id,
						username
					} = full;
					let update = BaseURL + 'user/user_form_update/' + id;
					let commission = BaseURL + 'agent/commission/' + id;

					html += '<div class="btn-group">';
					html += '<button type="button" class=" btn bg-gradient-success waves-effect waves-light  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span><i class="fa fa-edit mr-1"></i></span>จัดการ</button>';
					html += '<div class="dropdown-menu animated ">'; //bounce flipInY
					html += '<a class="dropdown-item" href="' + commission + '" ><span><i class="fa fa-search mr-1" style="color:#06d79c;"></i></span>Commission</a>';
					html += '</div>';
					html += '</div>';
					return html;
				}
			}*/
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
$(document).on('click', '.btn_username', function() {
	$('#credit').html('');
	let data = $(this).data();
	$('#username').html('Username : ' + data.username);
	$('#password').html('Password : ' + data.password);
	Swal.fire({
		text: "กรุณารอสักครู่..",
		showConfirmButton: false,
		allowOutsideClick: false,
		allowEscapeKey: false,
		confirmButtonText: '',
	}),
		Swal.showLoading();
	$.ajax({
		url: BaseURL + "user/remaining_credit/" + data.id,
		method: "POST",
		dataType: 'json',
		success: function(response) {
			if (!response.error) {
				Swal.close();
				$('#credit').html('Credit : ' + response.result.toString());
				$('#modal_username').modal('toggle');
			} else {
				sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
			}
		},
		error: function() {
			sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
		}
	});

});
$(document).on('click', '.btn_delete', function() {
	let data = $(this).data();
	Swal.fire({
		type: 'warning',
		title: 'แจ้งเตือน',
		text: 'ยืนยันการลบข้อมูล ' + data.username,
		confirmButtonText: 'ตกลง',
		confirmButtonColor: 'red',
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
					url: BaseURL + "user/user_delete/" + data.id,
					method: "POST",
					dataType: 'json',
					success: function(response) {
						if (response.result) {
							table.ajax.reload();
							const Toast = Swal.mixin({
								toast: true,
								position: 'top-end',
								showConfirmButton: false,
								timer: 2500
							});
							Toast.fire({
								type: 'success',
								title: response.message
							});
						} else {
							Toast.fire({
								type: 'success',
								title: response.message
							});
						}
					},
					error: function() {
						sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
					}
				});
			}
		});
});
