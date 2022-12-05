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
		'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		// 'serverSide': false,
		// 'serverMethod': 'get',
		ajax: {
			url: BaseURL + 'LogTransferOut/log_transfer_out_list_page',
			// data: function(d) {
			// 	d.plan = plan.id;
			// },
			// dataSrc: 'result',
		},
		columns: [
			{
				className: 'text-center',
				data: 'id'
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let {
						amount
					} = full;
					let html = amount;

					return html == null ? 0 : numeral(html).format('0,0.00');
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						bank,
						bank_number,
						bank_acc_name
					} = full;
					let html = bank;
					let html_bank_number = bank_number;
					let html_bank_acc_name = bank_acc_name;

					return (typeof(bank_list[html]) != "undefined" ? bank_list[html] : html)+ bank_list[html]+(html_bank_number == null ? '' : ' | '+html_bank_number)+(html_bank_acc_name == null ? '' : ' | '+html_bank_acc_name);
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						bank_to,
						bank_number_to,
						bank_acc_name_to
					} = full;
					let html = bank_to;
					let html_bank_number = bank_number_to;
					let html_bank_acc_name = bank_acc_name_to;

					return (typeof(bank_code_list[html]) != "undefined" ? bank_code_list[html] :  "-")+(html_bank_number == null ? '' : ' | '+html_bank_number)+(html_bank_acc_name == null ? '' : ' | '+html_bank_acc_name) ;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						status
					} = full;
					let html = "";
					if(status == "1"){
						html = "<strong class='text-success'>สำเร็จ</strong>"
					}else if(status == "2"){
						html = "<strong class='danger'>ไม่สำเร็จ</strong>"
					}else{
						html = "<strong class='warning'>ระหว่างดำเนินการ</strong>"
					}

					return html == null ? '-' : html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						description,
						id
					} = full;
					let html = '<p>\n' +
						'  <a class="btn bg-gradient-warning waves-effect waves-light" data-toggle="collapse" href="#collapse'+id+'" role="button" aria-expanded="false" aria-controls="collapse'+id+'">\n' +
						'    รายละเอียด\n' +
						'  </a>\n' +
						'</p>\n' +
						'<div class="collapse" id="collapse'+id+'">\n' +
						'  <div class="card card-body">\n' +
						'    '+(description != '' && description != null ? description : '-')+'\n' +
						'  </div>\n' +
						'</div>';
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
					let {
						admin_full_name
					} = full;
					let html = admin_full_name == null || admin_full_name == '' ? 'AUTO' : admin_full_name;

					return html == null ? '-' : html;
				}
			},
		],
		drawCallback: function(settings) {
			let api = this.api();

		}
	});
	setInterval(function() {
		if(document.visibilityState == "visible") {
			table.ajax.reload(null, false);
		}
	}, 1000 * 30);
}

var loading_transfer = false;
$(document).on('click', '#btn_create', function() {
	let bank_id = $('#bank_id').val();
	let bank_to = $('#bank_to').val();
	let amount = $('#amount').val();
	let bank_number_to = $('#bank_number_to').val();
	let bank_number = $('#bank_number').val();
	let bank_acc_name_to = $('#bank_acc_name_to').val();
	let bank_acc_name = $('#bank_acc_name').val();
	let bank = $("#bank_id option:selected").attr('data-bank');
	if (amount.toString().trim().length == 0) {
		sweetAlert2('warning', 'กรุณาระบุจำนวนเงิน');
		return;
	}
	if (amount == 0) {
		sweetAlert2('warning', 'จำนวนเงินต้องมากกว่า 0');
		return;
	}
	if (isNaN(amount)) {
		sweetAlert2('warning', 'จำนวนเงินต้องมากกว่า 0');
		return;
	}
	if (bank_id.trim().length == 0) {
		sweetAlert2('warning', 'กรุณาเลือกธนาคารต้นทาง');
		return;
	}
	if (bank_to.trim().length == 0) {
		sweetAlert2('warning', 'กรุณาเลือกธนาคารปลายทาง');
		return;
	}
	if (bank_number_to.trim().length == 0) {
		sweetAlert2('warning', 'กรุณาระบุเลขบัญชีปลายทาง');
		return;
	}
	if (bank_number_to.trim().length < 10) {
		sweetAlert2('warning', 'เลขบัญชีปลายทางไม่ควรน้อยกว่า 10 ตัวอักษร');
		return;
	}
	if (isNaN(bank_number_to)) {
		sweetAlert2('warning', 'เลขบัญชีปลายทางต้องเป็นตัวเลขเท่านั้น');
		return;
	}
	if (bank_acc_name_to.trim().length == 0) {
		sweetAlert2('warning', 'กรุณาระบุชื่อบัญชีปลายทาง');
		return;
	}
	Swal.fire({
		text: 'ยืนยันการโยกเงินออก ' + numeral(amount).format('0,0.00') + ' ฿',
		confirmButtonText: 'ตกลง',
		confirmButtonColor: '#2ABA66',
		showCancelButton: true,
		cancelButtonText: 'ยกเลิก',
		cancelButtonColor: 'red',
		reverseButtons: true,
	})
		.then((result) => {
			if (result.value) {
				if(!loading_transfer) {
					loading_transfer = true;
					Swal.fire({
						text: "กรุณารอสักครู่..",
						showConfirmButton: false,
						allowOutsideClick: false,
						allowEscapeKey: false,
						confirmButtonText: '',
					}),
						Swal.showLoading();
					$.ajax({
						url: BaseURL + "TransferOut/transfer_out_money",
						method: "POST",
						data: {
							bank_id: bank_id,
							bank: bank,
							bank_number: bank_number,
							bank_acc_name: bank_acc_name,
							amount: amount,
							bank_to: bank_to,
							bank_number_to: bank_number_to,
							bank_acc_name_to: bank_acc_name_to,
						},
						dataType: 'json',
						success: function(response) {
							loading_transfer = false;
							if (response.result) {
								table.ajax.reload();
								let Toast = Swal.mixin({
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
								if(response.error && response.message){
									table.ajax.reload(null);
									sweetAlert2('warning', response.message);
								}else{
									table.ajax.reload(null);
									sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
								}

							}
						},
						error: function() {
							loading_transfer = false;
							table.ajax.reload(null);
							sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
						}
					});
				}

			} else {
				loading_transfer = false;
				if (value > 0) {
					$(this).val('0');
				}
			}
		});


});
$(document).on("change","#bank_id",function(){
	var value = $(this).val();
	console.log(value);
	if(value == ""){
		$("#bank_number").val("");
		$("#bank_acc_name").val("");
	}else{
		var bank_selected = $("#bank_id option:selected");
		$("#bank_number").val(bank_selected.attr('data-bank-number'));
		$("#bank_acc_name").val(bank_selected.attr('data-bank-acc-name'));
	}
})
var validateInputNumber = function(e) {
	var t = e.value;
	t = t.replace("-","");
	e.value = ((t.indexOf(".") >= 0) ? (t.substr(0, t.indexOf(".")) + t.substr(t.indexOf("."), 3)) : t).replace(/[^.\d]/g, '');
}
$(document).on('change',"#amount",function(){
	var value = $(this).val();
	if(value.indexOf("-") >= 0){
		$(this).val(value.replace("-",""))
	}
});
