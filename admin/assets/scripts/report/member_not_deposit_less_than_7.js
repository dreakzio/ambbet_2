let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_start_report').datepicker('update',moment().subtract('6','days').format('YYYY-MM-DD'));
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_end_report').datepicker('update',moment().format('YYYY-MM-DD'));
	$("#txt_report_date_all").text(moment().subtract('6','days').format('YYYY-MM-DD')+' - '+moment().format('YYYY-MM-DD'))
	dataTable();
});

$(document).on('click',"#btn-search",function(){
	var date_start = $("#date_start_report").val();
	var date_end = $("#date_end_report").val();
	if(
		(date_end == "")
	){
		sweetAlert2('warning', 'กรุณาระบุวันที่');
	}else{
		$('#date_start_report').datepicker('update',moment(date_end).subtract('6','days').format('YYYY-MM-DD'));
		$("#txt_report_date_all").text($('#date_start_report').val()+' - '+$('#date_end_report').val())
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
			url: BaseURL + 'report/user_list_member_not_deposit_less_than_7_page',
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
					if (bank == '10') {
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
						id,
						username,
						account_agent_username,
						account_agent_password
					} = full;
					// data-toggle="popover" data-placement="right" data-container="body" data-original-title="Username / Password" data-content="ดด" aria-describedby="popover804090"
					if(account_agent_username==null || account_agent_username == ""){
						html = '<button type="button" data-id="'+id+'" data-username="'+username+'" class="btn bg-gradient-secondary waves-effect waves-light" >ยังไม่ได้รับยูสเซอร์<small class="d-block"></small></button>';
					}else{
						html = '<button type="button" data-id="' + id + '" data-username="' + account_agent_username + '"  data-password="' + account_agent_password + '" class="btn_username btn bg-gradient-warning waves-effect waves-light" >'+account_agent_username+'</button>';
					}
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
$(document).on('click', '.btn_username', function() {
	//$('#credit').html('');
	$('#others_credit').html('');
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
		url: BaseURL + "user/remaining_credit_all/" + data.id,
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
						$('#others_credit').append(' <li class="list-group-item bg-light text-white border-0"><span class="font-weight-bold">Turnover ('+start_date+' - '+end_date+')</span></li>');
						for(let i =0;i<game_code_list.length;i++){
							if(typeof(response.turnover.data[game_code_list[i]]) != "undefined"){
								let amount = numeral(response.turnover.data[game_code_list[i]].amount).format('0,0.00');
								$('#others_credit').append(' <li class="list-group-item bg-light text-white border-0"><span class="font-weight-bold">'+game_code_list[i]+'</span> : <span class="pull-right">'+amount+'</span></li>');
							}
						}
					}
					$('#others_credit').append(' <li class="list-group-item font-weight-bold"><span class="active">Credit</span> : <span class="pull-right">'+response.result+'</span></li>');
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


let loading_excel = false
$('#btn_export_excel').click(function(){
	if(!loading_excel){
		var start_datetime_search = $('#date_start_report').val();
		var end_datetime_search  = $('#date_end_report').val();
		if(
			end_datetime_search.trim().length == 0
		){
			sweetAlert2('warning', 'กรุณาระบุวันที่');

		} else {
			$('#date_start_report').datepicker('update',moment(end_datetime_search).subtract('6','days').format('YYYY-MM-DD'));
			start_datetime_search = $('#date_start_report').val();
			search_start_datetime = start_datetime_search;
			search_end_datetime = end_datetime_search;
			Swal.fire({
				icon: 'warning',
				text: 'ยืนยันการดาวน์โหลด Excel',
				title: 'แจ้งเตือน',
				confirmButtonText: 'ตกลง',
				confirmButtonColor: '#7cd1f9',
				showCancelButton: true,
				cancelButtonText: 'ยกเลิก',
				reverseButtons: true,
			})
				.then((result) => {
					if (result.value) {
						loading_excel = true;
						Swal.fire({
							text: "กรุณารอสักครู่..",
							showConfirmButton: false,
							allowOutsideClick: false,
							allowEscapeKey: false,
						}),
							Swal.showLoading();
						var query = table.search()
						var params = {};
						if(search_start_datetime!="0" && search_start_datetime!=""){
							params.date_start = search_start_datetime;
							params.date_end = search_end_datetime;
						}
						params.search = {
							value : query
						};
						$.ajax({
							url: BaseURL + 'report/user_list_member_not_deposit_less_than_7_excel',
							data :params,
							method: "POST",
							dataType: 'json',
							success: function(response) {
								Swal.close();
								loading_excel = false;
								if (response.data) {
									if(response.data.length == 0){
										sweetAlert2('warning', 'ไม่มีข้อมูลที่สามารถออกรายงาน Excel ได้');
									}else{
										excel_config.data = [];
										let length_data = response.data.length;
										let data = response.data;
										for(let i =0;i<length_data;i++){
											full_name = data[i].full_name;
											if (full_name == "") {
												full_name = data[i].bank_name;
											}
											account_agent_username = data[i].account_agent_username
											if(account_agent_username==null || account_agent_username == ""){
												account_agent_username = "ยังไม่ได้รับยูสเซอร์";
											}
											excel_config.data.push({
												phone : data[i].phone,
												full_name : full_name,
												line_id : data[i].line_id,
												bank : (typeof(bank_list[data[i].bank]) != "undefined" ? bank_list[data[i].bank] : ""),
												bank_number : data[i].bank_number,
												created_at : moment(data[i].created_at).format('YYYY-MM-DD'),
												amount_deposit_auto : data[i].amount_deposit_auto,
												amount_wallet_ref : data[i].amount_wallet_ref,
												account_agent_username : account_agent_username
											})
										}
										excel_config.filename = "รายงานไม่ได้ฝากเข้ามามากกว่า 7 วัน"+(search_start_datetime!="0"  && search_start_datetime!="" ? " "+search_start_datetime.replaceAll(":",".")+" ถึง "+search_end_datetime.replaceAll(":",".") : "");
										excel_config.sheetname = "รายงานไม่ได้ฝากเข้ามามากกว่า 7 วัน";
										exportExcel();
									}
								} else {
									sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
								}
							},
							error: function() {
								Swal.close();
								loading_excel = false;
								sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
							}
						});
					} else {
						loading_excel = false;
					}
				});
		}
	}
	//alert('export excel');
});
let excel_config = {
	columns: [
		{
			label: "เบอร์มือถือ",
			field: "phone",
		},
		{
			label: "ชื่อ - นามสกุล",
			field: "full_name",
		},
		{
			label: "ไลน์",
			field: "line_id",
		},
		{
			label: "ธนาคาร",
			field: "bank",
		},
		{
			label: "เลขบัญชี",
			field: "bank_number",
		},
		{
			label: "วันที่สมัคร",
			field: "created_at",
		},
		{
			label: "Wallet Auto",
			field: "amount_deposit_auto",
		},
		{
			label: "Wallet Commission",
			field: "amount_wallet_ref",
		},
		{
			label: "User / Password[พนัน]",
			field: "account_agent_username",
		}
	],
	data: [],
	filename: 'excel',
	sheetname: 'SheetName'
}
var exportExcel =function(){
	let createXLSLFormatObj = [];
	let newXlsHeader = [];
	if (excel_config.columns.length === 0){
		console.log("Add columns!");
		return;
	}
	if (excel_config.data.length === 0){
		console.log("Add data!");
		return;
	}
	$.each(excel_config.columns, function(index, value) {
		newXlsHeader.push(value.label);
	});

	createXLSLFormatObj.push(newXlsHeader);
	$.each(excel_config.data, function(index, value) {
		let innerRowData = [];
		$.each(excel_config.columns, function(index, val) {
			if (val.dataFormat && typeof val.dataFormat === 'function') {
				innerRowData.push(val.dataFormat(value[val.field]));
			}else {
				innerRowData.push(value[val.field]);
			}
		});
		createXLSLFormatObj.push(innerRowData);
	});

	let filename = excel_config.filename + ".xlsx";

	let ws_name = excel_config.sheetname;

	let wb = XLSX.utils.book_new(),
		ws = XLSX.utils.aoa_to_sheet(createXLSLFormatObj);
	XLSX.utils.book_append_sheet(wb, ws, ws_name);
	XLSX.writeFile(wb, filename);
}
