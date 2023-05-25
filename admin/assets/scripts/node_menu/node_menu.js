let table;
$(document).ready(function() {
	dataTable();
});

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
	table.ajax.reload(null);
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
		"pageLength": 100,
		'serverSide': true,
		'serverMethod': 'GET',
		'processing': true,
		// 'serverSide': false,
		// 'serverMethod': 'get',
		ajax: {
			url: BaseURL + 'menu/sub_list_page',
			data: function(d) {
				if($("#parent_id").val() != "" && $("#parent_id").val() != null){
					d.parent_id = $("#parent_id").val();
				}
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
						group_menu_name,
						group_menu_is_deleted,
						menu_name,
						menu_is_deleted,
					} = full;
					let html = '-';
					if(group_menu_name != '' && group_menu_name != null){
						html = '<strong>'+group_menu_name + (group_menu_is_deleted == "1" ? ' <span class="text-danger">(ปิดใช้งาน)</span>' : '')+"</strong>";
					}
					if(menu_name != '' && menu_name != null){
						html += (group_menu_name != '' && group_menu_name != null ? ' | ' : '')+'<strong>'+menu_name + (menu_is_deleted == "1" ? ' <span class="text-danger">(ปิดใช้งาน)</span>' : '')+"</strong>";
					}
					return html;
				}
			},
			{
				className: 'text-center',
				data: 'name'
			},
			{
				className: 'text-left',
				render: function(data, type, full, meta) {
					let {
						description
					} = full;
					let html = description;

					return html == null || html == '' ? '-' : html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						url
					} = full;
					let html = '-';
					if(url != '' && url != null){
						html = '<a target="_blank" href="'+BaseURL+url+'">'+BaseURL+url+'</a>';
					}
					return html;
				}
			},
			{
				className: 'text-left',
				render: function(data, type, full, meta) {
					let {
						icon_class
					} = full;
					let html = '-';
					if(icon_class != "" && icon_class != null){
						html =  "<i class='"+icon_class+"'></i>&nbsp;&nbsp;<span>"+icon_class+"</span>";
					}
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						is_deleted
					} = full;
					let html = '';
					if(is_deleted == "1"){
						html = '<span class="text-danger">ปิดใช้งาน</span>';
					}else{
						html = '<span class="text-success">เปิดใช้งาน</span>';
					}
					return html;
				}
			},
			{
				className: 'text-center',
				data: 'order'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let html = "";
					let {
						id
					} = full;
					let update = BaseURL + 'menu/sub_form_update/' + id;
					html += '<div class="btn-group">';
					html += '<button type="button" class=" btn bg-gradient-success waves-effect waves-light  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span><i class="fa fa-edit mr-1"></i></span>จัดการ</button>';
					html += '<div class="dropdown-menu animated ">'; //bounce flipInY
					html += '<a class="dropdown-item"  href="' + update + '"><span><i class="fa fa-edit mr-1" style="color:#ffb22b;"></i></span>แก้ไข</a>';
					html += '</div>';
					html += '</div>';
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
		if(document.visibilityState == "visible"){
			table.ajax.reload(null, false);
		}
	}, 2800 * 60);
}
