let table;
$(document).ready(function() {
    $('#date_start_report').datepicker({
        format: "yyyy-mm-dd",
        language: "th",
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function(e) {

    });
    $('#date_start_report').datepicker('update', moment().subtract(2, "days").format('YYYY-MM-DD'));
    $('#date_end_report').datepicker({
        format: "yyyy-mm-dd",
        language: "th",
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function(e) {

    });
    $('#date_end_report').datepicker('update', moment().format('YYYY-MM-DD'));
    dataTable();
    $('#username').select2({
        language: 'th',
        theme: 'bootstrap4',
        width: '100%',
        ajax: {
            url: function(params) {
                params.search = { value: params.term };
                return BaseURL + 'user/user_list_page?start=0&length=50';
            },
            processResults: function(data) {
                var datas = JSON.parse(data)
                return {
                    results: $.map(datas.data, function(item) {
                        var label = (typeof(bank_list[item.bank]) != "undefined" ? bank_list[item.bank] : "") + " " + item.bank_number + " : " + item.username;
                        return {
                            text: label,
                            id: item.id,
                            data: item,
                        }
                    })
                };
            }
        }
    });
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
});

let loading_excel = false
$('#btn_export_excel').click(function() {
    if (!loading_excel) {
		var start_datetime_search = $('#date_start_report').val()+" "+$('#time_start_report').val();
		var end_datetime_search  = $('#date_end_report').val()+" "+$('#time_end_report').val();
		var status_search  = $('#status').val();
        if (
            start_datetime_search.trim().length == 0 &&
            end_datetime_search.trim().length == 0
        ) {
            sweetAlert2('warning', 'กรุณาระบุวัน-เวลา เริ่มต้นและสิ้นสุด');

        } else if (
            (start_datetime_search.trim().length > 0 && end_datetime_search.trim().length == 0) ||
            end_datetime_search.trim().length > 0 && start_datetime_search.trim().length == 0
        ) {
            sweetAlert2('warning', 'กรุณาระบุวัน-เวลา เริ่มต้นและสิ้นสุด');

        } else if (
            start_datetime_search.trim().length > 0 &&
            end_datetime_search.trim().length > 0 &&
            start_datetime_search > end_datetime_search
        ) {
            sweetAlert2('warning', 'กรุณาระบุวัน-เวลา เริ่มต้นและสิ้นสุดให้ถูกต้อง');
        } else {
            search_start_datetime = start_datetime_search;
            search_end_datetime = end_datetime_search;
            Swal.fire({
                    type: 'warning',
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
                        if (search_start_datetime != "0" && search_start_datetime != "") {
                            params.date_start = search_start_datetime;
                            params.date_end = search_end_datetime;
                        }
						params.status= status_search;
                        params.search = {
                            value: query
                        };
                        $.ajax({
                            url: BaseURL + 'withdraw/withdraw_list_excel',
                            data: params,
                            method: "POST",
                            dataType: 'json',
                            success: function(response) {
                                Swal.close();
                                loading_excel = false;
                                if (response.data) {
                                    if (response.data.length == 0) {
                                        sweetAlert2('warning', 'ไม่มีข้อมูลที่สามารถออกรายงาน Excel ได้');
                                    } else {
                                        excel_config.data = [];
                                        let length_data = response.data.length;
                                        let data = response.data;
                                        for (let i = 0; i < length_data; i++) {
                                            status = "รอตรวจสอบ";
                                            if (data[i].status == 1) {
                                                status = "สำเร็จ (ถอนออโต้)";
                                            } else if (data[i].status == 3) {
                                                status = "สำเร็จ (ถอนมือ)";
                                            } else if (data[i].status == 4) {
                                                status = "ดำเนินการถอนออโต้";
                                            } else if (data[i].status == 2) {
                                                status = "ไม่อนุมัติ";
                                            }
                                            excel_config.data.push({
                                                username: data[i].username,
                                                bank: (typeof(bank_list[data[i].bank]) != "undefined" ? bank_list[data[i].bank] : ""),
                                                bank_number: data[i].bank_number,
                                                created_at: data[i].created_at,
                                                amount: data[i].amount,
                                                status: status,
                                            })
                                        }
                                        excel_config.filename = "รายการถอนเงิน" + (search_start_datetime != "0" && search_start_datetime != "" ? " " + search_start_datetime.replaceAll(":", ".") + " ถึง " + search_end_datetime.replaceAll(":", ".") : "");
                                        excel_config.sheetname = "รายการถอนเงิน";
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
    columns: [{
            label: "Username",
            field: "username",
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
            label: "ถอนเมื่อ",
            field: "created_at",
        },
        {
            label: "จำนวนเงิน",
            field: "amount",
        },
        {
            label: "สถานะ",
            field: "status",
        }
    ],
    data: [],
    filename: 'excel',
    sheetname: 'SheetName'
}
var exportExcel = function() {
    let createXLSLFormatObj = [];
    let newXlsHeader = [];
    if (excel_config.columns.length === 0) {
        console.log("Add columns!");
        return;
    }
    if (excel_config.data.length === 0) {
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
            } else {
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

$(document).on('click', "#btn-search", function() {
    var date_start = $("#date_start_report").val();
    var date_end = $("#date_end_report").val();
    if (
        (date_start != "" && date_end == "") ||
        (date_end != "" && date_start == "")
    ) {
        sweetAlert2('warning', 'กรุณาระบุวันที่ถอน (จาก) - วันที่ถอน (ถึง)');
    } else if (moment(date_start).unix() > moment(date_end).unix()) {
        sweetAlert2('warning', 'วันที่ถอน (จาก) ไม่ควรมากกว่า วันที่ถอน (ถึง)');
    } else {
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
            url: BaseURL + 'withdraw/withdraw_list_page',
            data: function(d) {
				d.date_start = $('#date_start_report').val()+" "+$('#time_start_report').val();
				d.date_end = $('#date_end_report').val()+" "+$('#time_end_report').val();
				d.status = $('#status').val();
            },
        },
        columns: [{
                className: 'text-left',
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
                data: 'bank_name'
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
                        ip
                    } = full;
                    if(ip != null && ip != ''){
                        html =  '<span class="text-warning">IP : '+ip+', โดย : '+manage_by_fullname+'</span>';
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
			},
            {
                className: 'text-right',
                render: function(data, type, full, meta) {
                    let html = "";
                    let {
                        status,
                        id
                    } = full;
                    let selected = status == 1 ? 'selected' : '';
                    let selected2 = status == 2 ? 'selected' : '';
                    let selected3 = status == 3 ? 'selected' : '';
                    let selected4 = status == 4 ? 'selected' : '';
                    let disabled = '';
                    if (status > 0) {
                        disabled = 'disabled';
                    }
                    html += '<select class="form-control status" ' + disabled + ' data-id="' + id + '" >';
                    html += '<option value="0">รอตรวจสอบ</option>';
                    html += '<option ' + selected + ' value="1">สำเร็จ (ถอนออโต้)</option>';
                    html += '<option ' + selected3 + ' value="3">สำเร็จ (ถอนมือ)</option>';
                    if (status == 4) {
                        html += '<option ' + selected4 + ' value="4">ดำเนินการถอนออโต้</option>';
                    }

                    html += '<option ' + selected2 + ' value="2">ไม่อนุมัติ</option>';
                    html += '</select">';
                    return html;
                }
            },
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						qrcode,
						status
					} = full;
					html = "-";
					if(status == "1" && typeof(qrcode) != "undefined" && qrcode != null && qrcode != ""){
						html = "<img class='img-fluid img-rounded cursor-pointer btn-show-qrcode' data-qrcode='"+qrcode+"' src='https://api.qrserver.com/v1/create-qr-code/?size=80x80&data="+qrcode+"&date="+moment().format('YYYY-MM-DD')+"'/>"
					}
					return html;
				}
			},
            {
                className: 'text-center',
                render: function(data, type, full, meta) {
                    let html = "";
                    let {
                        id,
                        username
                    } = full;
                    let detail = BaseURL + 'withdraw/withdraw_form_detail/' + id;
                    html += '<div class="btn-group">';
                    html += '<button type="button" class=" btn bg-gradient-success waves-effect waves-light  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span><i class="fa fa-edit mr-1"></i></span>จัดการ</button>';
                    html += '<div class="dropdown-menu animated ">'; //bounce flipInY
                    html += '<a class="dropdown-item" href="' + detail + '" ><span><i class="fa fa-search mr-1" style="color:#06d79c;"></i></span>ตรวจสอบ</a>';
                    // html += '<div class="dropdown-divider"></div>';
                    // html += '<a class="dropdown-item"  href="' + update + '"><span><i class="fa fa-edit mr-1" style="color:#ffb22b;"></i></span>แก้ไข</a>';
                    // html += '<div class="dropdown-divider"></div>';
                    // html += '<a class="dropdown-item btn_delete" href="javascript:void(0)" data-id="' + id + '" data-username="' + username + '"><span><i class="fa fa-trash mr-1" style="color:#ef5350;"></i></span>ลบ</a>';
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
			table.ajax.reload(null, false);
		}
    }, 2000 * 60);
}

$(document).on('change', '#username', function(e) {
    let value = $(this).val();
    if (value) {
        let data = $("#username").select2('data')[0].data;
        $('#credit_before').val(numeral(data.amount_deposit_auto).format('0,0.00'));
    }

});

$(document).on('click', '#btn_create', function() {
    let value = $('#username').val();
    let transaction = $('#transaction').val();
    let username = value;
    if (username == "") {
        sweetAlert2('warning', 'กรุณาเลือก Username');
        return;
    }
    let amount = $('#amount').val();
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
    Swal.fire({
            text: 'ยืนยันการถอนเครดิตออกจากประเป๋าเงิน ' + numeral(amount).format('0,0.00') + ' ฿',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#2ABA66',
            showCancelButton: true,
            cancelButtonText: 'ยกเลิก',
            cancelButtonColor: 'red',
            reverseButtons: true,
        })
        .then((result) => {
            if (result.value) {
                Swal.fire({
                        text: "กรุณารอสักครู่..",
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonText: '',
                    }),
                    Swal.showLoading();
                $.ajax({
                    url: BaseURL + "withdraw/withdraw_credit",
                    method: "POST",
                    data: {
                        account_id: username,
                        amount: amount,
                        transaction: transaction,
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.result) {
                            location.reload();
                        } else {
                            sweetAlert2('warning', response.message);
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
    let data = $(this).data();
    $('#username').html('Username : ' + data.username);
    $('#password').html('Password : ' + data.password);
    $('#modal_username').modal('toggle');
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
var loading_withdraw_auto = false;
$(document).on('change', '.status', function(e) {
	e.preventDefault();
	let element = $(this);
    let data = $(this).data();
    let value = $(this).val();
    let type = '';
    switch (value) {
        case '0':
            type = 'ยืนยันการทำรายการ รอตรวจสอบ';
            break;
        case '1':
            type = 'ยืนยันการทำรายการ สำเร็จ (ถอนออโต้)';
            break;
        case '2':
            type = 'ยืนยันการทำรายการ ไม่อนุมัติ';
            break;
        case '3':
            type = 'ยืนยันการทำรายการ สำเร็จ (ถอนมือ)';
            break;
        default:

    }
	if(value == "1"){
		Swal.fire({
			type: 'warning',
			title: 'แจ้งเตือน',
			text: type,
			confirmButtonText: 'ตกลง',
			confirmButtonColor: '#7cd1f9',
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
						confirmButtonText: '',
					}),
						Swal.showLoading();
					$.ajax({
						url: BaseURL + "bank/bank_list_for_withdraw" ,
						method: "GET",
						dataType: 'json',
						success: function(response) {
							if (response.success) {
								Swal.close();
								let data_row_bank_withdraw = response.result;
								let options = {};
								let inputValue = '';
								for(let i =0;i<data_row_bank_withdraw.length;i++){
									options[data_row_bank_withdraw[i]['id']] = data_row_bank_withdraw[i]['bank_number']+" | "+data_row_bank_withdraw[i]['bank_name']+" "+data_row_bank_withdraw[i]['account_name'];
								}
								if(inputValue == '' && data_row_bank_withdraw.length > 0){
									inputValue = data_row_bank_withdraw[0]['id'];
								}
								Swal.fire({
									type: 'warning',
									title: 'เลือกธนาคารที่จะใช้ถอนเงินออโต้',
									input: 'select',
									inputOptions: options,
									inputValue : inputValue,
									inputPlaceholder: 'กรุณาเลือก',
									confirmButtonText: 'ยืนยันถอนเงินออโต้',
									confirmButtonColor: '#7cd1f9',
									showCancelButton: true,
									cancelButtonText: 'ยกเลิก',
									reverseButtons: true,
								})
									.then((result) => {
										if(result.value  && result.value != '' && result.value !== '' && result.value != null){
											if (!loading_withdraw_auto) {
												loading_withdraw_auto = true;
												Swal.fire({
													text: "กรุณารอสักครู่..",
													showConfirmButton: false,
													allowOutsideClick: false,
													allowEscapeKey: false,
												}),
													Swal.showLoading();
												$.ajax({
													url: BaseURL + "withdraw/withdraw_status/" + data.id,
													method: "POST",
													dataType: 'json',
													data: {
														status: value,
														bank_id_withdraw: result.value,
													},
													success: function(response) {
														loading_withdraw_auto = false;
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
															loading_withdraw_auto = false;
															if (response.error && response.message) {
																Swal.fire({
																	type: 'warning',
																	title: 'แจ้งเตือน',
																	text: response.message,
																	confirmButtonText: 'ตกลง',
																	confirmButtonColor: '#7cd1f9',
																}).then(() => {
																	table.ajax.reload(null, false);
																});
															} else {
																Swal.fire({
																	type: 'warning',
																	title: 'แจ้งเตือน',
																	text: "ทำรายการไม่สำเร็จ",
																	confirmButtonText: 'ตกลง',
																	confirmButtonColor: '#7cd1f9',
																}).then(() => {
																	table.ajax.reload(null, false);
																});
															}

														}
													},
													error: function() {
														loading_withdraw_auto = false;
														Swal.fire({
															type: 'warning',
															title: 'แจ้งเตือน',
															text: "ทำรายการไม่สำเร็จ",
															confirmButtonText: 'ตกลง',
															confirmButtonColor: '#7cd1f9',
														}).then(() => {
															table.ajax.reload(null, false);
														});
													}
												});
											}
										}else if((result.value == '' || result.value === '')){
											element.val('0');
											sweetAlert2('warning', 'กรุณาเลือกธนาคารที่จะใช้ถอนเงินออโต้');
										}else{
											element.val('0');
										}
									});
							}else{
								element.val('0');
								sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
							}
						},
						error: function() {
							element.val('0');
							sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
						}
					});
				}
			});
	}else{
		Swal.fire({
			type: 'warning',
			title: 'แจ้งเตือน',
			text: type,
			confirmButtonText: 'ตกลง',
			confirmButtonColor: '#7cd1f9',
			showCancelButton: true,
			cancelButtonText: 'ยกเลิก',
			reverseButtons: true,
		})
			.then((result) => {
				if (result.value) {
					if (!loading_withdraw_auto) {
						loading_withdraw_auto = true;
						Swal.fire({
							text: "กรุณารอสักครู่..",
							showConfirmButton: false,
							allowOutsideClick: false,
							allowEscapeKey: false,
						}),
							Swal.showLoading();
						$.ajax({
							url: BaseURL + "withdraw/withdraw_status/" + data.id,
							method: "POST",
							dataType: 'json',
							data: {
								status: value
							},
							success: function(response) {
								loading_withdraw_auto = false;
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
									loading_withdraw_auto = false;
									if (response.error && response.message) {
										Swal.fire({
											type: 'warning',
											title: 'แจ้งเตือน',
											text: response.message,
											confirmButtonText: 'ตกลง',
											confirmButtonColor: '#7cd1f9',
										}).then(() => {
											table.ajax.reload(null, false);
										});
									} else {
										Swal.fire({
											type: 'warning',
											title: 'แจ้งเตือน',
											text: "ทำรายการไม่สำเร็จ",
											confirmButtonText: 'ตกลง',
											confirmButtonColor: '#7cd1f9',
										}).then(() => {
											table.ajax.reload(null, false);
										});
									}

								}
							},
							error: function() {
								loading_withdraw_auto = false;
								Swal.fire({
									type: 'warning',
									title: 'แจ้งเตือน',
									text: "ทำรายการไม่สำเร็จ",
									confirmButtonText: 'ตกลง',
									confirmButtonColor: '#7cd1f9',
								}).then(() => {
									table.ajax.reload(null, false);
								});
							}
						});
					}
				} else {
					if (value > 0) {
						$(this).val('0');
					}
				}
			});
	}
});
$(document).on('click', '.btn-show-qrcode', function() {
	let data = $(this).data();
	$("#modal_qrcode").find("img.img-qrcode").attr("src","https://api.qrserver.com/v1/create-qr-code/?size=450x450&data="+data.qrcode+"&date="+moment().format('YYYY-MM-DD'))
	setTimeout(function(){
		$('#modal_qrcode').modal('toggle');
	},100);
});
