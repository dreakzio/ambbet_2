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
            <h2 class="content-header-title float-left mb-0">ระบบจัดการสมาชิกที่ถูกระงับ</h2>
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('user_suspend') ?>">รายการสมาชิกที่ถูกระงับการใช้งาน</a>
                </li>
                <li class="breadcrumb-item active">แก้ไขสมาชิกที่ถูกระงับ</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
      <form class="form" id="form_update" method="POST" action="<?php echo site_url("user_suspend/user_update/{$user['id']}") ?>">
    <div class="content-body">
      <section class="card">
        <div class="card-content">
          <div class="card-body">
              <h3 class="card-title">ข้อมูลสมาชิก</h3>
              <hr>
              <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">สถานะของสมาชิก</label>
                      <!-- <pre>
                      <?php print_r($user) ?>
                      </pre> -->
                      <input type="text" class="form-control" value="<?php echo $user['deleted'] ?>" disabled="disabled" style="background-color : #fa6f6f" >
                      <!-- <input type="text" class="form-control" value="<?php echo $user['deleted'] == "1" ? "ถูกระงับการใช้งาน" : "ปกติ" ?>" disabled="disabled" style="background-color : #fa6f6f" > -->
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ปรับสถานะของสมาชิก</label>
                      <select class="form-control" name="deleted" id="deleted">
                        <option value="1">ระงับการใช้งาน</option>
                        <option value="0" <?php if ($user['deleted']==0): ?>
                          selected
                        <?php endif; ?> value="0">เปิดการใช้งาน</option>
                      </select>
                    </div>
                  </div>
                  <span>หมายเหตุ * สถานะของสมาชิก 1 = ระงับการใช้งาน 0 = เปิดการใช้งาน *</span>
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
                      <label class="control-label">Line</label>
                      <input type="text" id="line_id" name="line_id" class="form-control" value="<?php echo $user['line_id'] ?>" placeholder="ข้อมูล Line">
                    </div>
                  </div>
                </div>
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ธนาคาร</label>
                    <select class="form-control" id="bank" name="bank">
                      <option value="">เลือกธนาคาร</option>
                      <option <?php if ($user['bank']=="01"): ?>
                        selected
                      <?php endif; ?> value="01">ธนาคารกรุงเทพ จำกัด (มหาชน)</option>
                      <option <?php if ($user['bank']=="02"): ?>
                        selected
                      <?php endif; ?> value="02">ธนาคารกสิกรไทย จำกัด (มหาชน)</option>
                      <option <?php if ($user['bank']=="03"): ?>
                        selected
                      <?php endif; ?> value="03">ธนาคารกรุงไทย จำกัด (มหาชน)</option>
                      <option  <?php if ($user['bank']=="04" || $user['bank']=="08"): ?>
                        selected
                      <?php endif; ?> value="04">ธนาคารทีเอ็มบีธนชาต จำกัด (มหาชน)</option>
                      <option <?php if ($user['bank']=="05"): ?>
                        selected
                      <?php endif; ?> value="05">ธนาคารไทยพาณิชย์ จำกัด (มหาชน)</option>
                      <option <?php if ($user['bank']=="06"): ?>
                        selected
                      <?php endif; ?> value="06">ธนาคารกรุงศรีอยุธยา จำกัด (มหาชน)</option>
                      <option <?php if ($user['bank']=="07"): ?>
                        selected
                      <?php endif; ?> value="07">ธนาคารออมสิน จำกัด (มหาชน)</option>
					  <option <?php if ($user['bank']=="09"): ?>
						selected
					  <?php endif; ?> value="09">ธนาคารเพื่อการเกษตรและสหกรณ์การเกษตร จำกัด (มหาชน)</option>
						<option <?php if ($user['bank']=="10"): ?>
							selected
						<?php endif; ?> value="10">ทรูมันนี่วอลเล็ท</option>
                      <!-- <option value="01">ธนาคารกรุงเทพ จำกัด (มหาชน)</option>
                      <option value="02">ธนาคารกสิกรไทย จำกัด (มหาชน)</option>
                      <option value="03">ธนาคารกรุงไทย จำกัด (มหาชน)</option>
                      <option value="04">ธนาคารทหารไทย จำกัด (มหาชน)</option>
                      <option value="05">ธนาคารไทยพาณิชย์ จำกัด (มหาชน)</option>
                      <option value="06">ธนาคารกรุงศรีอยุธยา จำกัด (มหาชน)</option>
                      <option value="07">ธนาคารออมสิน จำกัด (มหาชน)</option>
                      <option value="08">ธนาคารธนชาติ จำกัด (มหาชน)</option> -->
                    </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">เลขบัญชี</label>
                      <input type="text" id="bank_number" name="bank_number" class="form-control" value="<?php echo $user['bank_number'] ?>" placeholder="ข้อมูลเลขบัญชี">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อบัญชี</label>
                      <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?php echo $user['bank_name'] ?>" placeholder="ข้อมูลเลขบัญชี">
                    </div>
                  </div>
                  <div class="col-md-4 d-none">
                    <div class="form-group">
                      <label class="control-label">Wallet</label>
                      <input type="text" id="amount_wallet" name="amount_wallet" class="form-control" value="<?php echo $user['amount_wallet'] ?>" placeholder="ข้อมูล Wallet">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">สถานะ</label>
                      <select class="form-control" name="role" id="role">
						<?php
							$role_list_can_mange = canManageRole()[$_SESSION['user']['role']];
						?>
						  <?php foreach(roleDisplay() as $key => $role): ?>
						      <?php if(in_array($key,$role_list_can_mange)): ?>
								  <option value="<?php echo $key; ?>" <?php echo $user['role'] == $key ? 'selected' : '' ?>><?php echo $role; ?></option>
						      <?php endif; ?>
						  <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">พันธมิตร</label>
                      <select class="form-control" name="agent" id="agent">
                        <option value="0">ไม่</option>
                        <option value="1" <?php if ($user['agent']==1): ?>
                          selected
                        <?php endif; ?> value="1">ใช่</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">Commission Percent</label>
                      <input type="text" id="commission_percent" name="commission_percent" class="form-control" value="<?php echo $user['commission_percent'] ?>" placeholder="ข้อมูล Commission Percent">
                    </div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">รับโบนัสคืนยอดเสีย</label>
							<select class="form-control" name="is_active_return_balance" id="is_active_return_balance">
								<option value="0">ไม่</option>
								<option value="1" <?php if ($user['is_active_return_balance']==1): ?>
									selected
								<?php endif; ?> value="1">ใช่</option>
							</select>
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
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">Turn date</label>
							<input type="text" id="turn_date" name="turn_date" class="form-control" value="<?php echo $user['turn_date'] ?>" placeholder="ข้อมูล Turn date ตัวอย่าง 2020-10-30">
						</div>
					</div>
					<?php foreach (game_code_list() as $game_code): ?>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">Turn over before (<?php echo game_code_text_list()[$game_code]; ?>)</label>
								<input type="text" id="turn_before_<?php echo strtolower($game_code); ?>" name="turn_before_<?php echo strtolower($game_code); ?>" class="form-control" value="<?php echo $user['turn_before_'.strtolower($game_code)] ?>" placeholder="ข้อมูล Turn before (<?php echo $game_code; ?>)">
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label class="control-label">Turn over (<?php echo game_code_text_list()[$game_code]; ?>)</label>
								<input type="text" id="turn_over_<?php echo strtolower($game_code); ?>" name="turn_over_<?php echo strtolower($game_code); ?>" class="form-control" value="<?php echo $user['turn_over_'.strtolower($game_code)] ?>" placeholder="ข้อมูล Turn over (<?php echo $game_code; ?>)">
							</div>
						</div>
						<div class="col-md-4">
              
						</div>
					<?php endforeach; ?>
                </div>
				  <div class="row">
					  <div class="col-md-4">
						  <div class="form-group">
							  <label class="control-label">กำไร (ยอดฝาก-ถอน)</label>
							  <input type="text" disabled id="sum_amount" name="sum_amount" class="form-control" value="<?php echo is_null($user['sum_amount']) || empty($user['sum_amount']) ? '' : $user['sum_amount'] ?>" placeholder="ข้อมูลกำไร (ยอดฝาก-ถอน)">
						  </div>
					  </div>
					  <div class="col-md-4">
						  <div class="form-group">
							  <label class="control-label">หมายเหตุ</label>
							  <textarea id="remark" rows="4" name="remark" class="form-control"  placeholder="ข้อมูลหมายเหตุ"><?php echo is_null($user['remark']) || empty($user['remark']) ? '' : $user['remark'] ?></textarea>
						  </div>
					  </div>
            
				  </div>
              </div>


          </div>
        </div>
      </section>
    </div>
    <div class="content-body">
      <section class="card">
        <div class="card-content">
          <div class="card-body">
              <h3 class="card-title">ข้อมูล User</h3>
              <hr>
              <div class="form-body mt-3">
                <div class="row ">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">Username</label>
                      <input type="text" id="username" name="username" class="form-control" value="<?php echo $user['username'] ?>" placeholder="ข้อมูลเบอร์มือถือ" readonly>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="control-label">New Password</label>
                      <input type="password" id="password" name="password" class="form-control"  placeholder="ข้อมูล Password">
                    </div>
                  </div>
                </div>
                <hr />
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
      </section>
    </div>
    </form>
  </div>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/select2.full.min.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/select2/dist/js/i18n/th.js') ?>"></script>
<script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/user_suspend/user_suspend_update.js?t='.time()) ?>"></script>
