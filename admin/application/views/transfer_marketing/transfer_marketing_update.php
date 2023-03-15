<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2/dist/css/select2.css') ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/plugins/select2-theme/dist/select2-bootstrap4.css') ?>">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบโยกสมาชิกการตลาด</h2>
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('user') ?>">รายการสมาชิก</a>
                </li>
                <li class="breadcrumb-item active">แก้ไขสมาชิก</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
      <form class="form" id="form_update" method="POST" action="<?php echo site_url("transfer_marketing/user_update/{$user['id']}") ?>">
    <div class="content-body">
      <section class="card">
        <div class="card-content">
          <div class="card-body">

              <h3 class="card-title">ข้อมูลสมาชิก</h3>
              <?php
                $role_list_can_mange = canManageRole()[$_SESSION['user']['role']];
              ?>
              <hr>
              <div class="form-body mt-3">
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">เบอร์มือถือ</label>
                      <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $user['phone'] ?>" placeholder="ข้อมูลเบอร์มือถือ">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อ-นามสกุล</label>
                      <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo $user['full_name'] ?>" placeholder="ข้อมูลชื่อ-นามสกุล">
                    </div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">อยู่ภายใต้ยูส</label>
							<select id="username_ref" name="username_ref" class="form-control" style=" width: 100%;">
								<option value="">ไม่มี</option>
								<?php foreach ($user_select as $key => $value): ?>
									<option <?php echo $user['ref_from_account'] == $value['id'] ? "selected" : ""; ?> value='<?php echo $value['id'] ?>'><?php echo $value['agent'] == "1" ? "พันธมิตร" : "สมาชิกปกติ" ?> : <?php echo $value['username'] ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
          </div>
        </div>
      </section>
    </div>
    <div class="content-body">
     
        <div class="card-content">
          <div class="card-body">
              
              <div class="form-body mt-3">
                <div class="row">
                  <div class="col-md-12">
                    <div class="text-right m-b-10">
                      <a type="button" href="<?php echo site_url('user') ?>" class=" btn bg-gradient-warning waves-effect waves-light mr-1"><span><i class="fa fa-arrow-left mr-1"></i></span>ย้อนกลับ</a>
                      <button  id="btn_update" type="submit" class=" btn bg-gradient-success waves-effect waves-light"><span><i class="fa fa-save mr-1"></i></span>แก้ไข</button>
                    </div>
                  </div>
                </div>
              </div>
          </div>
        </div>
     
    </div>
    </form>
  </div>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/i18n/th.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/transfer_marketing/transfer_marketing_update.js?t='.time()) ?>"></script>
