let table;
// alert("hello world");
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
		"ordering": true,
		"pageLength": 25,
		// 'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		// 'serverSide': false,
		// 'serverMethod': 'get',
		ajax: {
			url: BaseURL + 'gamestatus/new_list_gamestatus',
			// data: function(d) {
			// 	d.plan = plan.id;
			// },
			dataSrc: 'result', // point to data ref : 
		},
        // we can add optional in columns : 
		columns: [
			{
				className: 'text-center', // point to class
				data: 'game' // name of column
			},
            {
				className: 'text-center', // point to class
				data: 'active', // name of column
                render: function(data, type, full, meta) {
					let html = "";
					let {
						active
					} = full;
					html = active == true ? '<span class="text-success">เปิด</span>' : '<span class="text-danger">ปิด</span>';
					return html;
				}
			},
            {
				className: 'text-center', // point to class
				data: 'status' // name of column
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
	}, 2800 * 60);
}
