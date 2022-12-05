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
			url: BaseURL + 'news/new_list',
			// data: function(d) {
			// 	d.plan = plan.id;
			// },
			dataSrc: 'result',
		},
		columns: [
			{
				className: 'text-left',
				data: 'name'
			},
			{
				className: 'text-right',
				data: 'seq'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						url
					} = full;
					html = url != null && url != '' ? '<a href="'+url+'" target="_blank">'+url+'</a>' : '-';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						status_alert
					} = full;
					html = status_alert == 1 ? '<span class="text-success">เปิด</span>' : '<span class="text-danger">ปิด</span>';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						status_image_alert
					} = full;
					html = status_image_alert == 1 ? '<span class="text-success">เปิด</span>' : '<span class="text-danger">ปิด</span>';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						status
					} = full;
					html = status == 1 ? '<span class="text-success">เปิด</span>' : '<span class="text-danger">ปิด</span>';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id,
						name
					} = full;
					// if (id == 15) {
					// 	return;
					// }
					let update = BaseURL + 'news/new_form_update/' + id;
					html += '<div class="btn-group">';
					html += '<button type="button" class=" btn bg-gradient-success waves-effect waves-light  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span><i class="fa fa-edit mr-1"></i></span>จัดการ</button>';
					html += '<div class="dropdown-menu animated ">'; //bounce flipInY
					// html += '<a class="dropdown-item" href="detail" ><span><i class="fa fa-search mr-1" style="color:#06d79c;"></i></span>ตรวจสอบ</a>';
					// html += '<div class="dropdown-divider"></div>';
					html += '<a class="dropdown-item"  href="' + update + '"><span><i class="fa fa-edit mr-1" style="color:#ffb22b;"></i></span>แก้ไข</a>';
					html += '<div class="dropdown-divider"></div>';
					html += '<a class="dropdown-item btn_delete" href="javascript:void(0)" data-id="' + id + '" data-name="' + name + '"><span><i class="fa fa-trash mr-1" style="color:#ef5350;"></i></span>ลบ</a>';
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
					url: BaseURL + "news/new_delete/" + data.id,
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
								icon: 'success',
								title: response.message
							});
						} else {
							Toast.fire({
								icon: 'success',
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
