let table;
$(document).ready(function() {
	$('#date_start_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_start_report').datepicker('update',moment().startOf('month').format('YYYY-MM-DD'));
	$('#date_end_report').datepicker({
		format: "yyyy-mm-dd",
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#date_end_report').datepicker('update',moment().format('YYYY-MM-DD'));
	$('#year_start_report').datepicker({
		format: "yyyy",
		startView: 2,
		minViewMode : 2,
		changeMonth: true,
		changeYear: true,
		language: "th",
		autoclose: true,
		todayHighlight: true
	}) .on('changeDate', function(e) {

	});
	$('#year_start_report').datepicker('update',moment().format('YYYY'));
	showGraph();
});
let myChartPick = null;
let myChartYear = null;
$(document).on('click',"#btn-search",function(){
		Swal.fire({
			// title: "แจ้งเตือน",
			text: "กรุณารอสักครู่..",
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
			confirmButtonText: '',
		}),
			Swal.showLoading();
	showGraph();
});
function addData(chart, label, data) {
	chart.data.labels.push(label);
	chart.data.datasets.forEach((dataset) => {
		dataset.data.push(data);
	});
	chart.update();
}

function removeData(chart) {
	chart.data.labels.pop();
	chart.data.datasets.forEach((dataset) => {
		dataset.data.pop();
	});
	chart.update();
}
function showGraph(){
	$.ajax({
		url: BaseURL + 'report/business_profit_page',
		data :{
			date_start : $('#date_start_report').val(),
			date_end : $('#date_end_report').val(),
			year_start : $('#year_start_report').val(),
			year_end : $('#year_start_report').val()
		},
		method: "GET",
		dataType: 'json',
		success: function(response) {
			Swal.close();
			if (response.data_all_year) {
				if(myChartYear != null){
					myChartYear.data.datasets = response.data_all_year.datasets;
					myChartYear.data.labels = response.data_all_year.labels;
					myChartYear.update();
				}else{
					myChartYear = new Chart(
						document.getElementById('myChartYear'),
						{
							type: 'bar',
							data: {
								labels: response.data_all_year.labels,
								datasets: response.data_all_year.datasets
							},
							options: {
								scales: {
									y: {
										beginAtZero: true
									}
								}
							},
						}
					);
				}
				if(response.data_all_year.labels.length  == 0){
					sweetAlert2('warning', 'รายงานผลประกอบการรายเดือน => ไม่พบข้อมูล');
				}
			}else{

			}
			if (response.data_all_pick) {
				if(myChartPick != null){
					myChartPick.data.datasets = response.data_all_pick.datasets;
					myChartPick.data.labels = response.data_all_pick.labels;
					myChartPick.update();
				}else{
					myChartPick = new Chart(
						document.getElementById('myChartPick'),
						{
							type: 'bar',
							data: {
								labels: response.data_all_pick.labels,
								datasets: response.data_all_pick.datasets
							},
							options: {
								scales: {
									y: {
										beginAtZero: true
									}
								}
							},
						}
					);
				}
				if(response.data_all_pick.labels.length   == 0){
					sweetAlert2('warning', 'รายงานผลประกอบการรายวัน => ไม่พบข้อมูล');
				}
			}else{

			}
		},
		error: function() {
			Swal.close();
			sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
		}
	});
}
let loading_excel = false
$('#btn_export_excel').click(function(){
	if(!loading_excel){
		var start_datetime_search = $('#date_start_report').val();
		var end_datetime_search  = $('#date_end_report').val();
		if(
			start_datetime_search.trim().length == 0 &&
			end_datetime_search.trim().length == 0
		){
			sweetAlert2('warning', 'กรุณาระบุวัน เริ่มต้นและสิ้นสุด');

		}else if(
			(start_datetime_search.trim().length > 0 && end_datetime_search.trim().length  == 0) ||
			end_datetime_search.trim().length > 0 && start_datetime_search.trim().length  == 0
		){
			sweetAlert2('warning', 'กรุณาระบุวัน เริ่มต้นและสิ้นสุด');

		}else if(
			start_datetime_search.trim().length > 0 &&
			end_datetime_search.trim().length > 0 &&
			start_datetime_search > end_datetime_search
		){
			sweetAlert2('warning', 'กรุณาระบุวัน เริ่มต้นและสิ้นสุดให้ถูกต้อง');
		}else {
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
						var params = {};
						if(search_start_datetime!="0" && search_start_datetime!=""){
							params.date_start = search_start_datetime;
							params.date_end = search_end_datetime;
						}
						$.ajax({
							url: BaseURL + 'report/business_profit_excel',
							data :params,
							method: "POST",
							dataType: 'json',
							success: function(response) {
								Swal.close();
								loading_excel = false;
								if (response.data_all_pick) {
									if(response.data_all_pick.length == 0){
										sweetAlert2('warning', 'ไม่มีข้อมูลที่สามารถออกรายงาน Excel ได้');
									}else{
										excel_config.data = [];
										let length_data = response.data_all_pick.length;
										let data = response.data_all_pick;
										for(let i =0;i<length_data;i++){
											excel_config.data.push({
												process_date : data[i].process_date,
												deposit : data[i].deposit,
												withdraw : data[i].withdraw,
												total : data[i].total
											})
										}
										excel_config.filename = "รายงานผลประกอบการรายวัน"+(search_start_datetime!="0"  && search_start_datetime!="" ? " "+search_start_datetime.replaceAll(":",".")+" ถึง "+search_end_datetime.replaceAll(":",".") : "");
										excel_config.sheetname = "รายงานผลประกอบการรายวัน";
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
			label: "วันที่",
			field: "process_date",
		},
		{
			label: "ยอดฝากรวม",
			field: "deposit",
		},
		{
			label: "ยอดถอนรวม",
			field: "withdraw",
		},
		{
			label: "กำไร",
			field: "total",
		},
	],
	data: [],
	filename: 'excel',
	sheetname: 'SheetName'
}
let loading_excel_year = false
$('#btn_export_year_excel').click(function(){
	if(!loading_excel_year){
		var start_datetime_search = $('#year_start_report').val();
		var end_datetime_search  = $('#year_start_report').val();
		if(
			start_datetime_search.trim().length == 0 &&
			end_datetime_search.trim().length == 0
		){
			sweetAlert2('warning', 'กรุณาระบุปี เริ่มต้นและสิ้นสุด');

		}else if(
			(start_datetime_search.trim().length > 0 && end_datetime_search.trim().length  == 0) ||
			end_datetime_search.trim().length > 0 && start_datetime_search.trim().length  == 0
		){
			sweetAlert2('warning', 'กรุณาระบุปี เริ่มต้นและสิ้นสุด');

		}else if(
			start_datetime_search.trim().length > 0 &&
			end_datetime_search.trim().length > 0 &&
			start_datetime_search > end_datetime_search
		){
			sweetAlert2('warning', 'กรุณาระบุปี เริ่มต้นและสิ้นสุดให้ถูกต้อง');
		}else {
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
						loading_excel_year = true;
						Swal.fire({
							text: "กรุณารอสักครู่..",
							showConfirmButton: false,
							allowOutsideClick: false,
							allowEscapeKey: false,
						}),
							Swal.showLoading();
						var params = {};
						if(search_start_datetime!="0" && search_start_datetime!=""){
							params.year_start = search_start_datetime;
							params.year_end = search_end_datetime;
						}
						$.ajax({
							url: BaseURL + 'report/business_profit_excel',
							data :params,
							method: "POST",
							dataType: 'json',
							success: function(response) {
								Swal.close();
								loading_excel_year = false;
								if (response.data_all_year) {
									if(response.data_all_year.length == 0){
										sweetAlert2('warning', 'ไม่มีข้อมูลที่สามารถออกรายงาน Excel ได้');
									}else{
										excel_year_config.data = [];
										let length_data = response.data_all_year.length;
										let data = response.data_all_year;
										for(let i =0;i<length_data;i++){
											excel_year_config.data.push({
												process_date : data[i].process_date,
												deposit : data[i].sum_deposit,
												withdraw : data[i].sum_withdraw,
												total : data[i].sum_total
											})
										}
										excel_year_config.filename = "รายงานผลประกอบการรายเดือนปี"+(search_start_datetime!="0"  && search_start_datetime!="" ? " "+search_start_datetime.replaceAll(":",".")+" ถึง "+search_end_datetime.replaceAll(":",".") : "");
										excel_year_config.sheetname = "รายงานผลประกอบการรายเดือนปี"+search_start_datetime;
										exportExcelYear();
									}
								} else {
									sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
								}
							},
							error: function() {
								Swal.close();
								loading_excel_year = false;
								sweetAlert2('warning', 'ทำรายการไม่สำเร็จ');
							}
						});
					} else {
						loading_excel_year = false;
					}
				});
		}
	}
	//alert('export excel');
});
let excel_year_config = {
	columns: [
		{
			label: "เดือนที่",
			field: "process_date",
		},
		{
			label: "ยอดฝากรวม",
			field: "deposit",
		},
		{
			label: "ยอดถอนรวม",
			field: "withdraw",
		},
		{
			label: "กำไร",
			field: "total",
		},
	],
	data: [],
	filename: 'excel',
	sheetname: 'SheetName'
}
var exportExcelYear =function(){
	let createXLSLFormatObj = [];
	let newXlsHeader = [];
	if (excel_year_config.columns.length === 0){
		console.log("Add columns!");
		return;
	}
	if (excel_year_config.data.length === 0){
		console.log("Add data!");
		return;
	}
	$.each(excel_year_config.columns, function(index, value) {
		newXlsHeader.push(value.label);
	});

	createXLSLFormatObj.push(newXlsHeader);
	$.each(excel_year_config.data, function(index, value) {
		let innerRowData = [];
		$.each(excel_year_config.columns, function(index, val) {
			if (val.dataFormat && typeof val.dataFormat === 'function') {
				innerRowData.push(val.dataFormat(value[val.field]));
			}else {
				innerRowData.push(value[val.field]);
			}
		});
		createXLSLFormatObj.push(innerRowData);
	});

	let filename = excel_year_config.filename + ".xlsx";

	let ws_name = excel_year_config.sheetname;

	let wb = XLSX.utils.book_new(),
		ws = XLSX.utils.aoa_to_sheet(createXLSLFormatObj);
	XLSX.utils.book_append_sheet(wb, ws, ws_name);
	XLSX.writeFile(wb, filename);
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
