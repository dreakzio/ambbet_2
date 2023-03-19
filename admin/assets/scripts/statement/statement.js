let table;
$(document).ready(function() {
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
	$('#date_end_report').datepicker('update',moment().add('days', 1).format('YYYY-MM-DD'));
	$('#time_start_report').timepicker({
		maxHours : 24,
		showMeridian : false,
		showSeconds : true,
		minStep : 5,
		secondStep : 15,
		icons : {
			up: 'fa fa-angle-up',
			down: 'fa fa-angle-down'
		},
		defaultTime : "00:00:00"
	});
	$('#time_end_report').timepicker({
		maxHours : 24,
		showMeridian : false,
		showSeconds : true,
		minStep : 5,
		secondStep : 15,
		icons : {
			up: 'fa fa-angle-up',
			down: 'fa fa-angle-down'
		},
		defaultTime : "23:59:59"
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
		sweetAlert2('warning', 'กรุณาระบุวัน-เวลาสลิป (จาก) - วัน-เวลาสลิป (ถึง)');
	}else if(moment(date_start).unix() > moment(date_end).unix()){
		sweetAlert2('warning', 'วัน-เวลาสลิป (จาก) ไม่ควรมากกว่า วัน-เวลาสลิป (ถึง)');
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
		"pageLength": 50,
		'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		ajax: {
			url: BaseURL + 'statement/statement_list_page',
			data: function(d) {
				d.date_start = $('#date_start_report').val()+" "+$('#time_start_report').val();
				d.date_end = $('#date_end_report').val()+" "+$('#time_end_report').val();
				d.bank_number = $('#bank_number').val();
				d.type_deposit_withdraw = $('#type_deposit_withdraw').val();
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
					if (bank_bank_code == '11') {
						image = BaseURL + 'assets/images/bank/kkp.png';
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
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						type_deposit_withdraw
					} = full;
					if(type_deposit_withdraw=="D"){
						return "<span class='success feather icon-chevrons-up'>ฝาก</span>";
					}
					return "<span class='danger feather icon-chevrons-down'>ถอน</span>";
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						type_deposit_withdraw,
						amount
					} = full;
					if(type_deposit_withdraw=="D"){
						return "<span class='text-success'>"+numeral(amount).format('0,0.00')+"</span>";
					}
					return "<span class='text-danger'>"+numeral(amount).format('0,0.00')+"</span>";
				}
			},

			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						username,
					} = full;
					if(username!=null){
						return "<span class=''>"+username+"</span>";
					}
					return "<span class=''>-</span>";
				}
			},
			{
				className: 'text-center',
				data: 'created_at'
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
	}, 30000);
}
