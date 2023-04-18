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
			url: BaseURL + 'promotion/promotion_list',
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
					switch (full.category) {
						case '1':
							html = 'ปกติ';
							break;
						case '2':
							html = 'Fix ยอดฝาก/โบนัส';
							break;
						default:
							html = 'ปกติ';
							break;

					}
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					switch (full.type) {
						case '1':
							html = 'ถาวร';
							break;
						case '2':
							html = 'รายวัน';
							break;
						case '3':
							html = 'รายอาทิตย์';
							break;
						case '4':
							html = 'รายเดือน';
							break;
						case '5':
							html = 'นาทีทอง';
							break;
						case '6':
							html = 'ฝากประจำ';
							break;
						default:

					}
					return html;
				}
			},
			{
				className: 'text-center',
				data: 'name'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						turn
					} = full;
					html = "<button type='button' data-all='" + JSON.stringify(full) + "' class='btn_description btn bg-gradient-warning waves-effect waves-light' >รายละเอียด</button>";
					return html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						fix_amount_deposit,
						fix_amount_deposit_bonus,
						category
					} = full;
					html = category == '2' ? numeral(fix_amount_deposit).format('0,00')+" / "+numeral(fix_amount_deposit_bonus).format('0,00') : "-";
					return html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						percent,
						category
					} = full;
					html = category == '1' ? percent : "-";
					return html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						max_value,
						category
					} = full;
					html = category == '1' ?  numeral(max_value).format('0,00') : '-';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						turn
					} = full;
					html = "<button type='button' data-all='" + JSON.stringify(full) + "' class='btn_turn btn bg-gradient-warning waves-effect waves-light' >รายละเอียด</button>";
					return html;
				}
			},
			{
				className: 'text-right',
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
					let update = BaseURL + 'promotion/promotion_form_update/' + id;
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
$(document).on('click', '.btn_turn', function() {
	let all_data = $(this).data();
	let data = all_data.all;
	$('#modal_turn .modal-body .row:eq(0)').empty();
	$('#modal_turn .modal-title:eq(0)').text('คูณยอดเทิร์น');
	for(let i =0;i<game_code_list.length;i++){
		if(typeof(data['turn_'+game_code_list[i].toString().toLowerCase()]) != "undefined"){
			$('#modal_turn .modal-body .row:eq(0)').append('<div class="col-md-6">\n' +
				'\t\t<strong class="">'+game_code_text_list[game_code_list[i]]+'</strong> : <span class="pull-right">'+data['turn_'+game_code_list[i].toString().toLowerCase()]+'</span>\n' +
				'\t</div>')
		}
	}
	$('#modal_turn').modal('toggle');
});
$(document).on('click', '.btn_description', function() {
	let all_data = $(this).data();
	let data = all_data.all;
	let description = data['description'] != null && data['description'] != '' ? data['description'] : ''
	$('#modal_promotion .modal-title:eq(0)').text('รายละเอียดโปรโมชั่น');
	$('#modal_promotion .modal-body .row:eq(0)').empty();
	$('#modal_promotion .modal-body .row:eq(0)').append('<div class="col-md-12">\n' +
		'\t\t<span class="">'+description+'</span>\n' +
		'\t</div>')
	$('#modal_promotion').modal('toggle');
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
					url: BaseURL + "promotion/promotion_delete/" + data.id,
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
