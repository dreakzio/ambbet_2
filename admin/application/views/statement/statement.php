<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
  <link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบรายการเดินบัญชี</h2>
          </div>
        </div>
      </div>
    </div>
	  <div class="content-body">
		  <section class="card">
			  <div class="card-header">
				  <h4 class="card-title">ธนาคาร</h4>
			  </div>
			  <div class="card-content">
				  <div class="card-body">
					  <div class="row mx-auto" style="width: 98%">
						  <div class="col-12">
							  <div class="table-responsive mt-1">
								  <table id="tableBank" class="table table-hover-animation mb-0">
									  <thead>
									  <tr>
										  <th class="text-left">ธนาคาร</th>
										  <th class="text-left">ชื่อบัญชี</th>
										  <th class="text-left">เลขบัญชี</th>
										  <th class="text-right">ยอดคงเหลือ</th>
										  <th class="text-right">อัพเดตล่าสุด</th>
									  </tr>
									  </thead>
									  <tbody>
									  </tbody>
								  </table>
								  <script>
									  var loading_bank = false;
									  function getTableBank(){
										  if(!loading_bank){
											  loading_bank = true;
											  $.ajax({
												  url: BaseURL + "/bank/bank_list?&security=1&group_by=bank_number",
												  method: "GET",
												  dataType: 'json',
												  success: function(response) {
													  loading_bank = false;
													  var base_url = '<?php echo base_url('assets/images') ?>';
													  if(response.result.length > 0){
														  $("#tableBank > tbody").empty();
													  }else{
														  $("#tableBank > tbody").empty();
														  $("#tableBank > tbody").append("<tr colspan='4'>ไม่มีข้อมูล</tr>");
													  }
													  var bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
													  $.each(response.result,function(i,value){

														  if(true){

															  var url_img_bank = "";
															  if (value.bank_code ==1 || value.bank_code =='01') {
																  url_img_bank =  base_url+"/bank/1.png";
															  }
															  else if (value.bank_code ==2 || value.bank_code =='02') {
																  url_img_bank =  base_url+"/bank/2.png";
															  }
															  else if (value.bank_code ==3 || value.bank_code =='03') {
																  url_img_bank =  base_url+"/bank/3.png";
															  }
															  else if (value.bank_code ==4 || value.bank_code =='04') {
																  url_img_bank =  base_url+"/bank/5.png";
															  }
															  else if (value.bank_code ==5 || value.bank_code =='05') {
																  url_img_bank =  base_url+"/bank/6.png";
															  }
															  else if (value.bank_code ==6 || value.bank_code =='06') {
																  url_img_bank =  base_url+"/bank/4.png";
															  }
															  else if (value.bank_code ==7 || value.bank_code =='07') {
																  url_img_bank =  base_url+"/bank/7.png";
															  }else if (value.bank_code ==8 || value.bank_code =='08') {
																  url_img_bank =  base_url+"/bank/9.png";
															  }else if (value.bank_code ==9 || value.bank_code =='09') {
																  url_img_bank =  base_url+"/bank/baac.png";
															  }
															  else if (value.bank_code ==10 || value.bank_code =='10') {
																  url_img_bank =  base_url+"/bank/10.png";
															  }else if (value.bank_code ==11 || value.bank_code =='11') {
																  url_img_bank =  base_url+"/bank/kkp.png";
															  }
															  else {
																  url_img_bank = base_url+"/bank/not-found.png";
															  }
															  var parts = parseFloat(value.balance).toFixed(2).split(".");
															  var num = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
																  (parts[1] ? "." + parts[1] : "");
															  url_img_bank = '<ul class="list-unstyled users-list m-0  d-flex align-items-center">\n' +
																  '<li data-toggle="tooltip" data-popup="tooltip-custom" data-placement="bottom" data-original-title="'+(typeof(bank_list[value.bank_code]) != "undefined" ? bank_list[value.bank_code] : "-")+'" class="avatar pull-up m-0">' +
																  '<img class="media-object rounded" src="'+url_img_bank+'" alt="Avatar" height="30" width="30"></li></ul>';
															  $("#tableBank > tbody").append("<tr>" +
																  "<td class='left'>"+url_img_bank+"</td>" +
																  "<td class='text-left'>"+value.account_name+"</td>" +
																  "<td class='text-left'>"+value.bank_number+"</td>" +
																  "<td class='text-right'>"+num+"</td>" +
																  "<td class='text-right'>"+value.updated_at+"</td>" +
																  "</tr>" +
																  "");
														  }
													  })
												  },
												  error: function() {
													  loading_bank = false;
												  }
											  });
										  }
									  }
									  getTableBank();
									  setInterval(function(){
										  getTableBank();
									  },15000);
								  </script>
							  </div>
						  </div>
					  </div>
				  </div>
			  </div>
		  </section>
	  </div>
    <div class="content-body">
      <section class="card">
        <div class="card-header">
          <h4 class="card-title">รายการเดินบัญชี</h4>
        </div>
        <div class="card-content">
          <div class="card-body">
			  <div class="row mx-auto" style="width: 98%">
				  <div class="col-12 col-sm-2">
					  <div class="form-group">
						  <div class="input-group mb-3">
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">ธนาคาร</span>
							  </div>
							  <select id="bank_number" name="bank_number" class="form-control">
								  <option value="" selected>ทั้งหมด</option>
								  <?php foreach ($bank_list as $bank): ?>
									  <option value="<?php echo $bank['bank_number']; ?>"><?php echo $bank['bank_number'].' : '.getBankList()[$bank['bank_code']].' '.$bank['account_name']; ?></option>
								  <?php endforeach; ?>
							  </select>
						  </div>
					  </div>
				  </div>
				  <div class="col-12 col-md-6 pr-1 pl-1">
					  <div class="input-group mb-1">
						  <div class="input-daterange input-group " id="datepicker">
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">วัน-เวลาสลิป (จาก)</span>
							  </div>
							  <input type="text" class="input-sm form-control"  id="date_start_report" name="start" />
							  <input type="text" class="input-sm form-control"  id="time_start_report" name="time_start" />
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">วัน-เวลาสลิป (ถึง)</span>
							  </div>
							  <input type="text" class="input-sm form-control"  id="date_end_report" name="end" />
							  <input type="text" class="input-sm form-control"  id="time_end_report" name="time_nd" />
						  </div>
					  </div>
				  </div>
				  <div class="col-12 col-sm-2">
					  <div class="form-group">
						  <div class="input-group mb-3">
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">ประเภท</span>
							  </div>
							  <select id="type_deposit_withdraw" name="type_deposit_withdraw" class="form-control">
								  <option value="" selected>ทั้งหมด</option>
								  <option value="D">ฝาก</option>
								  <option value="W">ถอน</option>
							  </select>
						  </div>
					  </div>
				  </div>
				  <div class="col-12 col-sm-2">
					  <div class="form-group">
						  <button type="button" id="btn-search" name="button" class="btn bg-gradient-primary waves-effect waves-light"><span class="text-silver">ค้นหา</span></button>
					  </div>
				  </div>
			  </div>
            <div class="row">
              <div class="col-12">
                <div class="table-responsive">
                  <!-- table-bordered -->
                  <table id="table" class="table dataTables_wrapper dt-bootstrap4  table-hover" style="width:100%">
                    <thead>
                      <tr>
						  <th class="text-center">ธนาคาร</th>
						  <th class="text-center">เลขบัญชี</th>
						  <th class="text-center">ชื่อบัญชี</th>
						  <th class="text-center">วัน-เวลาสลิป</th>
						  <th class="text-center">รายละเอียด</th>
						  <th class="text-center">ประเภท</th>
						  <th class="text-right">จำนวน</th>
						  <th class="text-center">ยูส</th>
						  <th class="text-center">วันที่สร้าง</th>
                      </tr>
                    </thead>
                    <tbody>


                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js" ></script>
  <link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
  <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
  <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8"></script>
  <script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/xlsx.full.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script>
	const bank_list = JSON.parse('<?php echo json_encode(getBankList()); ?>');
</script>
  <script src="<?php echo base_url('assets/scripts/statement/statement.js?').time() ?>"></script>
