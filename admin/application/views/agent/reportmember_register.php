<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
  <link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">รายการยูสเซอร์ภายใต้พันธมิตร</h2>
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('agent') ?>">พันธมิตร</a>
                </li>
                <!-- <li class="breadcrumb-item active">แก้ไขสมาชิก</li> -->
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="content-body">
      <input type="hidden"  id="account" value="<?php echo $user['id'] ?>"/>
      <section class="card">
        <div class="card-header">
          <h4 class="card-title">รายการยูสเซอร์ภายใต้พันธมิตร</h4>
        </div>
        <div class="card-content">
          <div class="card-body">
			  <div class="row mx-auto">
				 <div class="col-12 col-sm-4">
					 <div class="card border bg-warning mb-1">
						 <div class="card-body p-1">
							 <h5 class="card-title"><strong class="text-white">พันธมิตร : <?php echo $user['username']; ?> (<?php echo $user['full_name']; ?>)</strong></h5>
							 <h5 class="card-title"><strong class="text-white">ยูสภายใต้รวมทั้งหมด : <?php echo number_format($user['sum_member']); ?> คน</strong></h5>
							 <p class="card-text">
								 <a href="<?php echo base_url('agent'); ?>" class="btn-block btn btn-white waves-effect waves-light"><i class="fa fa-backward"></i>&nbsp;กลับหน้ารายการ</a>
							 </p>
						 </div>
					 </div>
				 </div>
				  <div class="col-12 col-sm-4">
					  <div class="card border bg-warning mb-1">
					  <div class="card-body p-1">
							  <h5 class="card-title"><strong class="text-white">Commission : <?php echo $user['commission_percent']; ?> %</strong></h5>
						      <h5>&nbsp;</h5>
							  <p class="card-text">
								  <a href="<?php echo base_url('agent/commission/'.$user['id']); ?>" class="btn-block btn btn-white waves-effect waves-light"><i class="fa fa-gift"></i>&nbsp;รายงานคอมมิชชั่น</a>
							  </p>
						  </div>
					  </div>
				  </div>
				  <div class="col-12 col-sm-4">
					  <div class="card border bg-warning mb-1">
						  <div class="card-body p-1">
							  <h5 class="card-title"><strong class="text-white">สรุปฝาก-ถอนยูส</strong></h5>
							  <h5>&nbsp;</h5>
							  <p class="card-text">
								  <a href="<?php echo base_url('agent/reportmember/'.$user['id']); ?>" class="btn-block btn btn-white waves-effect waves-light"><i class="fa fa-list-alt"></i>&nbsp;รายงานฝาก-ถอน</a>
							  </p>
						  </div>
					  </div>
				  </div>
			  </div>
			  <div class="row mx-auto" style="width: 98%">
				  <div class="col-12 col-md-10 pr-1 pl-1">
					  <div class="input-group mb-1">
						  <div class="input-daterange input-group " id="datepicker">
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">วันสมัคร (จาก)</span>
							  </div>
							  <input type="text" class="input-sm form-control"  id="date_start_report" name="start" />
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">วันสมัคร (ถึง)</span>
							  </div>
							  <input type="text" class="input-sm form-control"  id="date_end_report" name="end" />
							  <div class="input-group-prepend">
								  <span class="input-group-text" id="basic-addon1">สถานะ</span>
							  </div>
							  <select name="status" id="status" class="form-control">
								  <option value="">ทั้งหมด</option>
								  <option value="1">ได้รับยูสเซอร์แล้ว</option>
								  <option value="0">ยังไม่ได้รับยูสเซอร์</option>
							  </select>
						  </div>
					  </div>
				  </div>
				  <div class="col-12 col-md-2 mx-auto">
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
                        <th class="text-center">ยูสเซอร์</th>
                        <th class="text-center">วันที่สมัคร</th>
                       <!-- <th class="text-right">ยอดถอน</th>
                        <th class="text-right">ยอดเสีย</th>-->
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
  <link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
  <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
  <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/locales/bootstrap-datepicker.th.min.js" charset="UTF-8"></script>
  <script src="<?php echo base_url('assets/plugins/moment/min/moment.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/scripts/agent/reportmember_register.js?').time() ?>"></script>
