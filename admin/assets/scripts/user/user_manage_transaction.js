let table;
let table2;
let table3;
let table4;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_start_report').datepicker('update',moment().subtract(2, "days").format('YYYY-MM-DD'));
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_end_report').datepicker('update',moment().format('YYYY-MM-DD'));
	$('#date').datepicker({
		format: 'yyyy-mm-dd',
		autoclose: true,
		// todayBtn: true,
		language: 'th',
		orientation: 'bottom'
		// thaiyear: true
	}).datepicker("setDate", moment().format('YYYY-MM-DD'));
	dataTable();
	dataTable2();
	dataTable3();
	dataTable4();
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
	$('#time').timepicker({
		maxHours : 24,
		showMeridian : false,
		showSeconds : true,
		minStep : 5,
		secondStep : 1,
		icons : {
			up: 'fa fa-angle-up',
			down: 'fa fa-angle-down'
		},
		defaultTime : moment().format('HH:ii')+":00"
	});
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
		table2.ajax.reload(null);
		table3.ajax.reload(null);
		table4.ajax.reload(null);
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
		ajax: {
			url: BaseURL + 'credit/credit_list_page_manage_transaction/'+user_id,
			data: function(d) {
				d.date_start = $('#date_start_report').val()+" "+$('#time_start_report').val();
				d.date_end = $('#date_end_report').val()+" "+$('#time_end_report').val();
			},
			// success:function(result) {
			// 	console.log("result credit :",result);
			// }
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
				data: 'credit_before'
			},

			{
				className: 'text-center',
				render: function(data, type, full, meta) {

					let html = full.type == 1 ? '<i class="fa fa-arrow-up font-small-3 text-success mr-50"> เพิ่ม<i>' : '<i class="fa fa-arrow-down font-small-3 text-danger mr-50"> ลด<i>';
					return html;
				}
			},
			{
				className: 'text-center',
				data: 'process'
			},
			{
				className: 'text-center',
				data: 'credit_after'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						admin_username
					} = full;
					html = admin_username;
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						date_bank,
						transaction,
					} = full;
					html = transaction == "1" ? date_bank : '-';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						created_at
					} = full;
					html = created_at;
					return html;
				}
			}
		],
		drawCallback: function(settings) {
			let api = this.api();
			Swal.close();
		}
	});
	setInterval(function() {
		if(document.visibilityState == "visible") {
			table.ajax.reload(null, false);
		}
	}, 2800 * 60);
}

function dataTable2() {
	$.fn.dataTable.ext.errMode = 'none';
	table2 = $('#table2').DataTable({
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
			url: BaseURL + 'deposit/deposit_list_page_manage_transaction/'+user_id,
			data: function(d) {
				d.date_start = $('#date_start_report').val()+" "+$('#time_start_report').val();
				d.date_end = $('#date_end_report').val()+" "+$('#time_end_report').val();
			},
			// success:function(result) {
			// 	console.log("result deposit :",result);
			// }
		},
		columns: [{
				className: 'text-center',
				data: 'username'
			},
			// {
			// 	className: 'text-left',
			// 	data: 'account_agent_username'
			// },
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
					html = '<ul class="list-unstyled users-list m-0  align-items-center">\n' +
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
				data: 'promotion_name'
			},
			{
				className: 'text-right',
				data: 'amount'
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						amount,
						sum_amount
					} = full;
					html = parseFloat(Number(sum_amount) - Number(amount)).toFixed(2);
					return html;
				}
			},
			{
				className: 'text-right',
				data: 'sum_amount'
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
			table2.ajax.reload(null, false);
		}
	}, 2800 * 60);
}

function dataTable3() {
    $.fn.dataTable.ext.errMode = 'none';
    table3 = $('#table3').DataTable({
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
            url: BaseURL + 'withdraw/withdraw_list_page_manage_transaction/'+user_id, // old
            data: function(d) {
				d.date_start = $('#date_start_report').val()+" "+$('#time_start_report').val();
				d.date_end = $('#date_end_report').val()+" "+$('#time_end_report').val();
            },
        },
        columns: [{
                className: 'text-center',
                data: 'username'
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
                        '<li data-toggle="tooltip" data-popup="tooltip-custom" data-placement="bottom" data-original-title="' + (typeof(bank_list[bank]) != "undefined" ? bank_list[bank] : "-") + '" class="avatar pull-up m-0">' +
                        '<img class="media-object rounded" src="' + image + '" alt="Avatar" height="40" width="40"></li></ul>';
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
                render: function(data, type, full, meta) {
                    let html = "";
                    let {
                        created_at,
                        manage_by_fullname,
                        ip
                    } = full;
                    html = created_at;
                    return html;
                }
            },
            {
                className: 'text-center',
                render: function(data, type, full, meta) {
                    let html = "-";
                    let {
                        created_at,
                        manage_by_fullname,
						manage_by,
						is_auto_withdraw,
                        ip
                    } = full;
					if(ip != null && ip != ''){
						html =  '<span class="text-warning">IP : '+ip+', โดย : '+((is_auto_withdraw== "1" || is_auto_withdraw== 1 ) && manage_by == null ? 'AUTO' : manage_by_fullname )+'</span>';
					}
                    return html;
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
						remark
					} = full;
					if (remark != "" && remark != null) {
						html = remark;
					}else{
						html = "-"
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
            $('.tooltip.fade.show').each(function() {
                if ($('[aria-describedby="' + $(this).attr('id') + '"]').length == 0) {
                    $(this).remove();
                }
            });
            Swal.close();
        }
    });
    setInterval(function() {
		if(document.visibilityState == "visible") {
			table3.ajax.reload(null, false);
		}
    }, 2000 * 60);
}

function dataTable4() {
	$.fn.dataTable.ext.errMode = 'none';
	table4 = $('#table4').DataTable({
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
			url: BaseURL + 'LogDepositWithdraw/log_deposit_withdraw_list_page_manage_transaction/'+user_id,
			data: function(d) {
				d.date_start = $('#date_start_report').val();
				d.date_end = $('#date_end_report').val();
			},
		},
		columns: [
			{
				className: 'text-center',
				data: 'id'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						username
					} = full;
					let html = username;

					return html == null ? '-' : html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let {
						amount_before
					} = full;
					let html = amount_before;

					return html == null ? 0 : html;
				}
			},
			{
				className: 'text-right',
				render: function(data, type, full, meta) {
					let {
						amount
					} = full;
					let html = amount;

					return html == null ? 0 : html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = full.type == 1 ? '<i class="fa fa-arrow-up font-small-3 text-success mr-50"> ฝาก<i>' : '<i class="fa fa-arrow-down font-small-3 text-danger mr-50"> ถอน<i>';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						promotion_name
					} = full;
					let html = promotion_name;

					return html == null ? '-' : html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						description
					} = full;
					let html = description;

					return html == null ? '-' : html;
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
					let html = "";
					let {
						admin_username
					} = full;
					html = admin_username;
					return html;
				}
			},
		],
		drawCallback: function(settings) {
			let api = this.api();
			Swal.close();
		}
	});
	setInterval(function() {
		if(document.visibilityState == "visible") {
			table4.ajax.reload(null, false);
		}
	}, 2800 * 60);
}
