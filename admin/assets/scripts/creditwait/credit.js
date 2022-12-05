let table;
var select_user = "";
var select_user_options = "";
$(document).ready(function() {
	select_user = $("#dummy-user-select").clone();
	select_user_options = $(select_user).html();
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_start_report').datepicker('update',moment().subtract(1, "days").format('YYYY-MM-DD'));
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
		sweetAlert2('warning', 'กรุณาระบุวันที่สลิป (จาก) - วันที่สลิป (ถึง)');
	}else if(moment(date_start).unix() > moment(date_end).unix()){
		sweetAlert2('warning', 'วันที่สลิป (จาก) ไม่ควรมากกว่า ววันที่สลิป (ถึง)');
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
			url: BaseURL + 'creditwait/credit_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
			},
		},
		columns: [
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let image = "";

					let {
						bank_bank_code
					} = full;
					if (bank_bank_code == '1' || bank_bank_code == '01') {
						image = BaseURL + 'assets/images/bank/1.png';
					}
					if (bank_bank_code == '2' || bank_bank_code == '02') {
						image = BaseURL + 'assets/images/bank/2.png';
					}
					if (bank_bank_code == '3' || bank_bank_code == '03') {
						image = BaseURL + 'assets/images/bank/3.png';
					}
					if (bank_bank_code == '4' || bank_bank_code == '04') {
						image = BaseURL + 'assets/images/bank/5.png';
					}
					if (bank_bank_code == '5' || bank_bank_code == '05') {
						image = BaseURL + 'assets/images/bank/6.png';
					}
					if (bank_bank_code == '6' || bank_bank_code == '06') {
						image = BaseURL + 'assets/images/bank/4.png';
					}
					if (bank_bank_code == '7' || bank_bank_code == '07') {
						image = BaseURL + 'assets/images/bank/7.png';
					}
					if (bank_bank_code == '8' || bank_bank_code == '08') {
						image = BaseURL + 'assets/images/bank/9.png';
					}
					if (bank_bank_code == '9' || bank_bank_code == '09') {
						image = BaseURL + 'assets/images/bank/baac.png';
					}
					if (bank_bank_code == '10') {
						image = BaseURL + 'assets/images/bank/10.png';
					}
					html = '<ul class="list-unstyled users-list m-0   align-items-center">\n' +
						'<li data-toggle="tooltip" data-popup="tooltip-custom" data-placement="bottom" data-original-title="'+(typeof(bank_list[bank_bank_code]) != "undefined" ? bank_list[bank_bank_code] : "-")+'" class="avatar pull-up m-0">' +
						'<img class="media-object rounded" src="'+image+'" alt="Avatar" height="40" width="40"></li></ul>';
					return html;
				}
			},
			{
				className: 'text-center',
				data: 'bank_bank_number'
			}
			,{
				className: 'text-center',
				data: 'bank_account_name'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						create_date,
						create_time
					} = full;
					return create_date+" "+create_time;
				}
			},
			{
				className: 'text-right',
				data: 'amount'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						payment_gateway,
						ref_number,
						bank_bank_code
					} = full;
					if(bank_bank_code=="10"){
						return payment_gateway+" (เลขที่อ้างอิง : "+ref_number+")";
					}
					return payment_gateway;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						users,
						amount,
						id
					} = full;
					if(users != "" && users != null && users.length > 0){
						html += '<select class="form-control status select-user" data-all="false" data-amount="'+amount+'" data-id="' + id + '" >';
						html += '<option value="" disabled selected>กรุณาเลือก</option>';
						$(users).each(function(index,value){
							html += '<option value="'+value.id+'">'+bank_list[value.bank]+' '+value.bank_number+' '+value.bank_name+' : '+value.username+' ('+(value.account_agent_username != "" && value.account_agent_username != null ? value.account_agent_username  : 'ยังไม่ได้รับยูส')+')</option>';
						})
						html += '</select">';
					}else{

						html += '<select style="width: 100%" class="form-control status select-user" data-all="true" data-amount="'+amount+'" data-id="' + id + '" >';
						html += '<option value="" disabled selected>กรุณาเลือก</option>';
						//html += select_user_options;
						html += '</select">';
					}
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
			$('.select-user[data-all="true"]').select2({
				language: 'th',
				theme: 'bootstrap4',
				width : '100%',
				ajax: {
					url: function (params) {
						params.search = {value : params.term};
						return BaseURL + 'user/user_list_page?start=0&length=50';
					},
					processResults: function (data) {
						var datas = JSON.parse(data)
						return {
							results: $.map(datas.data, function (item) {
								var label = (typeof(bank_list[item.bank]) != "undefined" ? bank_list[item.bank] : "")+" "+item.bank_number+" : "+item.username+" - "+item.full_name;
								return {
									text: label,
									id: item.id
								}
							})
						};
					}}
			});
			$('.select-user[data-all="false"]').select2({
				language: 'th',
				theme: 'bootstrap4',
				width : '100%'
			});
			Swal.close();
		}
	});
	/*setInterval(function() {
		table.ajax.reload(null, false);
	}, 2800 * 60);*/
}
var loading_adjust_auto =false;
$(document).on('change', '.status', function() {
	let data = $(this).data();
	let value = $(this).val();
	Swal.fire({
		type: 'warning',
		title: 'แจ้งเตือน',
		text: "ยืนยันการปรับเครดิต "+data.amount+" บาท ให้กับ => "+$(this).find("option:selected").text(),
		confirmButtonText: 'ตกลง',
		confirmButtonColor: '#7cd1f9',
		showCancelButton: true,
		cancelButtonText: 'ยกเลิก',
		reverseButtons: true,
	})
		.then((result) => {
			if (result.value) {
				if(!loading_adjust_auto){
					loading_adjust_auto = true;
					Swal.fire({
						text: "กรุณารอสักครู่..",
						showConfirmButton: false,
						allowOutsideClick: false,
						allowEscapeKey: false,
					}),
						Swal.showLoading();
					$.ajax({
						url: BaseURL + "creditwait/credit_history_create/" + data.id,
						method: "POST",
						dataType: 'json',
						data: {
							account_id: value
						},
						success: function(response) {
							loading_adjust_auto = false;
							if (response.result) {

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
								table.ajax.reload();
							} else {
								loading_adjust_auto = false;
								if(response.error && response.message){
									Swal.fire({
										type: 'warning',
										title: 'แจ้งเตือน',
										text: response.message,
										confirmButtonText: 'ตกลง',
										confirmButtonColor: '#7cd1f9',
									}).then(()=>{
										table.ajax.reload(null, false);
									});
								}else{
									Swal.fire({
										type: 'warning',
										title: 'แจ้งเตือน',
										text: "ทำรายการไม่สำเร็จ",
										confirmButtonText: 'ตกลง',
										confirmButtonColor: '#7cd1f9',
									}).then(()=>{
										table.ajax.reload(null, false);
									});
								}

							}
						},
						error: function() {
							loading_adjust_auto = false;
							Swal.fire({
								type: 'warning',
								title: 'แจ้งเตือน',
								text: "ทำรายการไม่สำเร็จ",
								confirmButtonText: 'ตกลง',
								confirmButtonColor: '#7cd1f9',
							}).then(()=>{
								table.ajax.reload(null, false);
							});
						}
					});
				}
			} else {
				if (value > 0) {
					$(this).val('');
				}
			}
		});
});
