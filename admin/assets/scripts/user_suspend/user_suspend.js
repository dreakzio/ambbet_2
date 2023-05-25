let table;
// console.log("is user_suspended.js page");
$(document).ready(function() {
	// option for datepicker from start search
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
		// console.log("my e :",e);
	});
	dataTable();
});

$(document).on('click',"#btn-search",function(){
	var date_start = $("#date_start_report").val();
	
	var date_end = $("#date_end_report").val();
	// console.log("date_end :",date_end);
	if((date_start != "" && date_end == "") || (date_end != "" && date_start == "")){
		// console.log("I'm in if loop now ");
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
			"processing": "กำลังโหลดข้อมูล...",
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
			url: BaseURL + 'user_suspend/user_list_page',
			// method: "POST",
			// 		dataType: 'json',
			// 		success: function(response) {
			// 			console.log("response :",response);
			// 		},
			// 		error: function(error) {
			// 			console.log("error : ",error);
			// 		},
			data: function(d) {
				// console.log("my d : ",d);
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
				d.status = $('#status').val();
				d.role = typeof($("#role").val()) != "undefined" ? $("#role").val() : ""
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
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						sum_amount
					} = full;
					if (sum_amount != "" && sum_amount != null) {
						if(parseFloat(sum_amount) > 0.00){
							html = "<span class='text-success'>"+numeral(sum_amount).format('0,0.00')+"</span>"
						}else{
							html = "<span class='text-danger'>"+numeral(sum_amount).format('0,0.00')+"</span>"
						}
					}else{
						html = "<span class='text-dark'>0.00</span>"
					}
					return html;
				}
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
					html = '<ul class="list-unstyled users-list m-0   align-items-center">\n' +
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
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						amount_deposit_auto,
						amount_wallet_ref,
						login_point,
						point_for_return_balance,
					} = full;
					return numeral(amount_deposit_auto).format('0,0.00')+" / "+numeral(amount_wallet_ref).format('0,0.00')+" / "+numeral(point_for_return_balance).format('0,0.00')+" / "+numeral(login_point).format('0,0.00');
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						rank,
						rank_point_sum,
					} = full;
					html = "-";
					if(rank != null){
						if (rank == "1") {
							html = "<span class='text-dark'>Member</span>";
						}else if(rank == "2"){
							html = "<span class='text-secondary'>Silver</span>";
						}else if(rank == "3"){
							html = "<span class='text-warning'>Gold</span>";
						}else{
							html = "<span class='text-dark'>Member</span>";
						}
					}
					return html+" / "+numeral(rank_point_sum).format('0,0.00');
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id,
						username,
						account_agent_username,
						account_agent_password
					} = full;
					// data-toggle="popover" data-placement="right" data-container="body" data-original-title="Username / Password" data-content="ดด" aria-describedby="popover804090"
					if(account_agent_username==null || account_agent_username == ""){
						html = '<button type="button" data-id="'+id+'" data-username="'+username+'" class="btn btn-create-user-auto bg-gradient-secondary waves-effect waves-light" >ยังไม่ได้รับยูสเซอร์<small class="d-block"> (กดเพื่อสร้างยูส)</small></button>';
					}else{
						html = "<button type='button' data-all='" + JSON.stringify(full) + "' data-id='" + id + "' data-username='" + account_agent_username + "'  data-password='" + account_agent_password + "' class='btn_username btn bg-gradient-warning waves-effect waves-light' >"+account_agent_username+"</button>";
					}
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						remark
					} = full;
					if (remark != "" && remark != null) {
						html = remark;
					}else{
						html = "-"
					}
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						role
					} = full;
					if (role !== "" && role !== null && role_display != null && typeof(role_display[role]) != "undefined") {
						html = role_display[role];
					}else{
						html = "-"
					}
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					// console.log("full :",full);
					let html = "";
					let {
						id,
						username
					} = full;
					let update = BaseURL + 'user_suspend/user_form_update/' + id;
					
					html += '<div class="btn-group">';
					html += '<button type="button" class=" btn bg-gradient-success waves-effect waves-light  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span><i class="fa fa-edit mr-1"></i></span> จัดการ </button>';
					html += '<div class="dropdown-menu animated ">'; //bounce flipInY
					// html += '<a class="dropdown-item" href="detail" ><span><i class="fa fa-search mr-1" style="color:#06d79c;"></i></span>ตรวจสอบ</a>';
					// html += '<div class="dropdown-divider"></div>';
					html += '<a class="dropdown-item"  href="' + update + '"><span><i class="fa fa-edit mr-1" style="color:#ffb22b;"></i></span> แก้ไข </a>';
					html += '<div class="dropdown-divider"></div>';
					html += '<a class="dropdown-item btn_delete" href="javascript:void(0)" data-id="' + id + '" data-username="' + username + '"><span><i class="fa fa-trash mr-1" style="color:#ef5350;"></i></span> ลบ </a>';
					html += '</div>';
					html += '</div>';
					return html;
				}
			}
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
$(document).on('click', '.btn-create-user-auto', function() {
	var id = $(this).attr('data-id');
	var username = $(this).attr('data-username');
	Swal.fire({
		type: 'warning',
		title: 'แจ้งเตือน',
		text: 'ยืนยันการสร้างยูสเล่นเกมส์ของสมาชิก : ' + username,
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
					url: BaseURL + "user_suspend/user_create_agent_username/" + id,
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
							const Toast = Swal.mixin({
								toast: true,
								position: 'top-end',
								showConfirmButton: false,
								timer: 2500
							});
							Toast.fire({
								type: 'error',
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
$(document).on('click', '.btn_username', function() {
	//$('#credit').html('');
	$('#others_credit').html('');
	let data = $(this).data();
	let data_all = data.all;
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
		url: BaseURL + "user_suspend/remaining_credit_all/" + data.id,
		method: "POST",
		dataType: 'json',
		success: function(response) {
			if (!response.error) {
				Swal.close();
				if(response.result == null){
					$('#others_credit').append(' <li class="list-group-item">ดึงข้อมูลจาก API ไม่สำเร็จ</li>');
				}else{
					if(response.turnover){
						let start_date = response.turnover.start_date != "" ? response.turnover.start_date : "";
						let end_date = response.turnover.end_date != "" ? response.turnover.end_date : "";
						$('#others_credit').append(' <li class="list-group-item border-0 bg-gradient-primary text-white"><span class="font-weight-bold">Turnover ('+start_date+' - '+end_date+')</span></li>');
						for(let i =0;i<game_code_list.length;i++){
							if(typeof(response.turnover.data[game_code_list[i]]) != "undefined"){
								let amount = numeral(response.turnover.data[game_code_list[i]].amount).format('0,0.00');
								let outstanding = numeral(response.turnover.data[game_code_list[i]].outstanding).format('0,0.00');
								$('#others_credit').append(' <li class="list-group-item "><span class="font-weight-bold">'+game_code_text_list[game_code_list[i]]+'</span> : <span class="pull-right text-warning">&nbsp;Outstanding : '+outstanding+'</span><span class="pull-right text-success">Turnover : '+amount+' | </span></li>');
							}
						}
					}
					$('#others_credit').append(' <li class="list-group-item border-0 bg-gradient-primary text-white font-weight-bold"><span class="active">Credit</span> : <span class="pull-right">'+response.result+'</span></li>');
				}
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
					url: BaseURL + "user_suspend/user_delete/" + data.id,
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
