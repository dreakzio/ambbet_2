let table;
$(document).ready(function() {
	dataTable();
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
		// 'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		// 'serverSide': false,
		// 'serverMethod': 'get',
		ajax: {
			url: BaseURL + 'bank/bank_list',
			// data: function(d) {
			// 	d.plan = plan.id;
			// },
			dataSrc: 'result',
		},
		columns: [
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						bank_name,
						bank_code,
						api_type,
						id
					} = full;

					html = bank_name;
					if(bank_code == "02" || bank_code == "2" || bank_code == "06" || bank_code == "6" || bank_code == "05" || bank_code == "5" || bank_code == "11"){
						if(api_type == "1"){
							html += "<br/><p class='text-success mb-0'>App</p>"
						}else{
							html += "<br/><p class='text-success mb-0'>Internet Banking</p>"
						}
					}
					return html;
				}
			},
			{
				className: 'text-center',
				data: 'account_name'
			},
			{
				className: 'text-center',
				data: 'bank_number'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						username,
						password
					} = full;

					html = username+" / "+password;

					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						start_time_can_not_deposit,
						end_time_can_not_deposit,
						id
					} = full;

					html = (start_time_can_not_deposit ==null ? '' : start_time_can_not_deposit)+' - '+(end_time_can_not_deposit ==null ? '' : end_time_can_not_deposit);

					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						status_withdraw,
						max_amount_withdraw_auto
					} = full;

					html = status_withdraw !=null && status_withdraw == "1" ? numeral(max_amount_withdraw_auto).format('0,0.00') : '-';

					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "<i class='fa fa-times text-danger'></i>";
					let {
						auto_transfer,
						api_type,
						id,
						bank_code,
					} = full;
					if(api_type == "1" && auto_transfer == "1" && (bank_code == "02" || bank_code == "2" || bank_code == "03" || bank_code == "3" || bank_code == "05" || bank_code == "5" || bank_code == "06" || bank_code == "6" || bank_code == "11")){
						html = "<i class='fa fa-check text-success'></i>";
					}

					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						status,
						id
					} = full;
					let selected = status == 1 ? 'selected' : '';
					html += '<select class="form-control status" style="width: auto " data-id="' + id + '">';
					html += '<option value="0">ปิด</option>';
					html += '<option ' + selected + ' value="1">เปิด</option>';
					html += '</select>';

					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						status_withdraw,
						id
					} = full;
					let selected = status_withdraw == 1 ? 'selected' : '';
					html += '<select class="form-control status_withdraw" style="width: auto " data-id="' + id + '">';
					html += '<option value="0">ปิด</option>';
					html += '<option ' + selected + ' value="1">เปิด</option>';
					html += '</select>';

					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id,
						account_name
					} = full;
					let update = BaseURL + 'bank/bank_form_update/' + id;
					html += '<div class="btn-group">';
					html += '<button type="button" class=" btn bg-gradient-success waves-effect waves-light  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span><i class="fa fa-edit mr-1"></i></span>จัดการ</button>';
					html += '<div class="dropdown-menu animated ">'; //bounce flipInY
					// html += '<a class="dropdown-item" href="detail" ><span><i class="fa fa-search mr-1" style="color:#06d79c;"></i></span>ตรวจสอบ</a>';
					// html += '<div class="dropdown-divider"></div>';
					html += '<a class="dropdown-item"  href="' + update + '"><span><i class="fa fa-edit mr-1" style="color:#ffb22b;"></i></span>แก้ไข</a>';
					html += '<div class="dropdown-divider"></div>';
					html += '<a class="dropdown-item btn_delete" href="javascript:void(0)" data-id="' + id + '" data-name="' + account_name + '"><span><i class="fa fa-trash mr-1" style="color:#ef5350;"></i></span>ลบ</a>';
					html += '</div>';
					html += '</div>';
					return html;
				}
			}
		],
		drawCallback: function(settings) {
			let api = this.api();

		}
	});
	setInterval(function() {
		if(document.visibilityState == "visible") {
			table.ajax.reload(null, false);
		}
	}, 2800 * 60);
}
$(document).on('change', '.status_withdraw', function() {
	let status_withdraw = $(this).val();
	let data = $(this).data();
	Swal.fire({
		text: "กรุณารอสักครู่..",
		showConfirmButton: false,
		allowOutsideClick: false,
		allowEscapeKey: false,
	}),
		Swal.showLoading();
	$.ajax({
		url: BaseURL + "bank/bank_status_withdraw_update/",
		method: "POST",
		dataType: 'json',
		data: {
			status_withdraw,
			id: data.id
		},
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
				table.ajax.reload();
				Toast.fire({
					type: 'success',
					title: 'ทำรายการไม่สำเร็จ'
				});
			}
		},
		error: function() {
			table.ajax.reload();
			sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
		}
	});

});
$(document).on('change', '.status', function() {
	let status = $(this).val();
	let data = $(this).data();
	Swal.fire({
			text: "กรุณารอสักครู่..",
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
		}),
		Swal.showLoading();
	$.ajax({
		url: BaseURL + "bank/bank_status_update/",
		method: "POST",
		dataType: 'json',
		data: {
			status,
			id: data.id
		},
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
				table.ajax.reload();
				Toast.fire({
					type: 'success',
					title: 'ทำรายการไม่สำเร็จ'
				});
			}
		},
		error: function() {
			table.ajax.reload();
			sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
		}
	});

});
$(document).on('click', '.btn_delete', function() {
	let data = $(this).data();
	Swal.fire({
			type: 'warning',
			title: 'แจ้งเตือน',
			text: 'ยืนยันการลบข้อมูล ' + data.name,
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
					url: BaseURL + "bank/bank_delete/" + data.id,
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
								title: 'ทำรายการไม่สำเร็จ'
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
