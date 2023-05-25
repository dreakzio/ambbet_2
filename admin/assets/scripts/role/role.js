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
			url: BaseURL + 'role/role_list_page',
			data: function(d) {

			},
		},
		columns: [
			{
				className: 'text-center',
				data: 'role_id'
			},
			{
				className: 'text-center',
				data: 'role_name'
			},
			{
				className: 'text-center',
				data: 'role_level'
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						role_id,
						role_name,
					} = full;
					let html = role_id != role_user ? '<button type="button" data-role_id="'+role_id+'" data-role_name="'+role_name+'" class="btn bg-gradient-'+(role_id == role_superadmin ? 'warning' : 'primary')+' waves-effect waves-light btn_detail_menu"><i class="fa fa-search"></i>&nbsp;'+(role_id == role_superadmin ? 'เข้าได้ทุกเมนู' : 'รายละเอียด')+'</button>' : '-';
					return html;
				}
			},
			{
				className: 'text-center',
				render: function(data, type, full, meta) {
					let {
						role_id,
						role_name,
					} = full;
					let html = role_id != role_user ? '<button type="button"  data-role_id="'+role_id+'" data-role_name="'+role_name+'" class="btn bg-gradient-'+(role_id == role_superadmin ? 'warning' : 'success')+' waves-effect waves-light btn_detail_role"><i class="fa fa-search"></i>&nbsp;'+(role_id == role_superadmin ? 'จัดการได้ทุกตำแหน่ง' : 'รายละเอียด')+'</button>' : '-';
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
				render: function(data, type, full, meta) {
					let html = "";
					let {
						role_id
					} = full;
					if(role_id == role_user || role_id == role_superadmin ){
						return '-';
					}
					let update = BaseURL + 'role/role_form_update/' + role_id;
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
$(document).on('click', '.btn_detail_menu', function() {
	let data = $(this).data();
	let role_name = data.role_name;
	$("#modal_detail").find(".modal-body").empty();
	$("#modal_detail").find(".modal-title").empty();
	Swal.fire({
		text: "กรุณารอสักครู่..",
		showConfirmButton: false,
		allowOutsideClick: false,
		allowEscapeKey: false,
		confirmButtonText: '',
	}),
		Swal.showLoading();
	$.ajax({
		url: BaseURL + "role/role_get_menu_list/" + data.role_id,
		method: "GET",
		dataType: 'json',
		success: function(response) {
			$("#modal_detail").find(".modal-title").text("รายละเอียด : เมนูที่สามารถเข้าได้ ["+role_name+"]")
			Swal.close();
			let data = response.data;
			if(Object.keys(data).length > 0){
				let html_menu_list = "";
				for(group_id in data){
					if(typeof(data[group_id].menu_list) != "undefined" && data[group_id].menu_list.length > 0){
						html_menu_list += '<ul class="list-group mb-2">\n' +
							'\t\t\t\t\t\t<li class="list-group-item active">'+data[group_id].name+'</li>\n';
						for(let i =0;i<data[group_id].menu_list.length;i++){
							html_menu_list += '\t\t\t\t\t\t<li class="list-group-item">&nbsp;&nbsp;-&nbsp;&nbsp;'+data[group_id].menu_list[i].menu_name+'</li>\n';
							if(typeof(data[group_id].menu_list[i].node_menu_list) != "undefined" && data[group_id].menu_list[i].node_menu_list.length > 0){
								for(let j =0;j<data[group_id].menu_list[i].node_menu_list.length;j++) {
									html_menu_list += '\t\t\t\t\t\t<li class="list-group-item">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;' + data[group_id].menu_list[i].node_menu_list[j].name + '</li>\n';
								}
							}
						}
						html_menu_list += '\t\t\t\t\t</ul>';
					}
				}
				if(html_menu_list == ""){
					$("#modal_detail").find(".modal-body").append('<div class="row"><div class="col-10 mx-auto text-center">ไม่มีเมนูที่ผูกกับตำแหน่งนี้</div></div>');
				}else{
					$("#modal_detail").find(".modal-body").append('<div class="row"><div class="col-10 mx-auto">'+html_menu_list+'</div></div>');
				}

			}else{
				$("#modal_detail").find(".modal-body").append('<div class="row"><div class="col-10 mx-auto text-center">ไม่มีเมนูที่ผูกกับตำแหน่งนี้</div></div>');
			}
			setTimeout(function(){
				$('#modal_detail').modal('toggle');
			},100)
		},
		error: function() {
			Swal.close();
			sweetAlert2('warning', 'ดึงข้อมูลไม่สำเร็จ');
		}
	});

});
$(document).on('click', '.btn_detail_role', function() {
	let data = $(this).data();
	let role_name = data.role_name;
	$("#modal_detail").find(".modal-body").empty();
	$("#modal_detail").find(".modal-title").empty();
	Swal.fire({
		text: "กรุณารอสักครู่..",
		showConfirmButton: false,
		allowOutsideClick: false,
		allowEscapeKey: false,
		confirmButtonText: '',
	}),
		Swal.showLoading();
	$.ajax({
		url: BaseURL + "role/role_get_role_can_manage_list/" + data.role_id,
		method: "GET",
		dataType: 'json',
		success: function(response) {
			$("#modal_detail").find(".modal-title").text("รายละเอียด : ตำแหน่งภายใต้ที่จัดการได้ ["+role_name+"]")
			Swal.close();
			let data = response.data;
			if(data.length > 0){
				let html_role_list = "";
				html_role_list += '<ul class="list-group mb-2">\n';
				for(let i =0;i<data.length;i++){
					if(typeof(role_display[data[i]]) != "undefined"){
						html_role_list += '\t\t\t\t\t\t<li class="list-group-item bg-gradient-warning border-warning">&nbsp;&nbsp;'+role_display[data[i]]+'</li>\n';
					}else{
						html_role_list += '\t\t\t\t\t\t<li class="list-group-item bg-gradient-warning border-warning">&nbsp;&nbsp;'+data[i]+'</li>\n';
					}
				}
				html_role_list += '\t\t\t\t\t</ul>';
				if(html_role_list == ""){
					$("#modal_detail").find(".modal-body").append('<div class="row"><div class="col-10 mx-auto text-center">ไม่มีตำแหน่งภายใต้ที่ผูกกับตำแหน่งนี้</div></div>');
				}else{
					$("#modal_detail").find(".modal-body").append('<div class="row"><div class="col-10 mx-auto">'+html_role_list+'</div></div>');
				}

			}else{
				$("#modal_detail").find(".modal-body").append('<div class="row"><div class="col-10 mx-auto text-center">ไม่มีตำแหน่งภายใต้ที่ผูกกับตำแหน่งนี้</div></div>');
			}
			setTimeout(function(){
				$('#modal_detail').modal('toggle');
			},100)
		},
		error: function() {
			Swal.close();
			sweetAlert2('warning', 'ดึงข้อมูลไม่สำเร็จ');
		}
	});

});
