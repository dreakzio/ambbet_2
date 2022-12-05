<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
  <style >
.form-control:disabled, .form-control[readonly]{
  background-color:white !important;
}
  </style>
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบจัดการเครดิต</h2>
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('deposit') ?>">รายการเครดิต</a>
                </li>
                <li class="breadcrumb-item active">ตรวจสอบเครดิต</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="content-body">
      <section class="card">
        <div class="card-content">
          <div class="card-body">
              <h3 class="card-title">ข้อมูล User</h3>
              <hr>
              <div class="form-body mt-3">
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">Username</label>
                      <input type="text" id="username" name="username" class="form-control" value="<?php echo $deposit['username'] ?>" placeholder="ข้อมูลเบอร์มือถือ" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อ-นามสกุล</label>
                      <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo $deposit['full_name'] ?>" placeholder="ข้อมูลชื่อ-นามสกุล" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">Line</label>
                      <input type="text" id="line_id" name="line_id" class="form-control" value="<?php echo $deposit['line_id'] ?>" placeholder="ข้อมูล Line" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">Username (member)</label>
                      <input type="text" id="account_agent_username" name="account_agent_username" class="form-control" value="<?php echo $deposit['account_agent_username'] ?>" placeholder="ข้อมูล Username (member)" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">Password (member)</label>
                      <input type="text" id="account_agent_password" name="account_agent_password" class="form-control" value="<?php echo $deposit['account_agent_password'] ?>" placeholder="ข้อมูล Password (member)" readonly>
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
              <h3 class="card-title">ข้อมูลธนาคาร</h3>
              <hr>
              <div class="form-body mt-3">
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ธนาคาร</label>
                    <select class="form-control" id="bank" name="bank" disabled>
                      <option <?php if ($deposit['bank']=="01"): ?>
                        selected
                      <?php endif; ?> value="01">ธนาคารกรุงเทพ จำกัด (มหาชน)</option>
                      <option <?php if ($deposit['bank']=="02"): ?>
                        selected
                      <?php endif; ?> value="02">ธนาคารกสิกรไทย จำกัด (มหาชน)</option>
                      <option <?php if ($deposit['bank']=="03"): ?>
                        selected
                      <?php endif; ?> value="03">ธนาคารกรุงไทย จำกัด (มหาชน)</option>
                      <option  <?php if ($deposit['bank']=="04"): ?>
                        selected
                      <?php endif; ?> value="04">ธนาคารทีเอ็มบีธนชาต จำกัด (มหาชน)</option>
                      <option <?php if ($deposit['bank']=="05"): ?>
                        selected
                      <?php endif; ?> value="05">ธนาคารไทยพาณิชย์ จำกัด (มหาชน)</option>
                      <option <?php if ($deposit['bank']=="06"): ?>
                        selected
                      <?php endif; ?> value="06">ธนาคารกรุงศรีอยุธยา จำกัด (มหาชน)</option>
                      <option <?php if ($deposit['bank']=="07"): ?>
                        selected
                      <?php endif; ?> value="07">ธนาคารออมสิน จำกัด (มหาชน)</option>
                      <option <?php if ($deposit['bank']=="08"): ?>
                        selected
                      <?php endif; ?> value="08">ธนาคารทีเอ็มบีธนชาต จำกัด (มหาชน)</option>
						<option <?php if ($deposit['bank']=="09"): ?>
                        selected
                      <?php endif; ?> value="09">ธนาคารเพื่อการเกษตรและสหกรณ์การเกษตร จำกัด (มหาชน)</option>
                    </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">เลขบัญชี</label>
                      <input type="text" id="bank_number" name="bank_number" class="form-control" value="<?php echo $deposit['bank_number'] ?>" placeholder="ข้อมูลเลขบัญชี" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อบัญชี</label>
                      <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?php echo $deposit['bank_name'] ?>" placeholder="ข้อมูลชื่อบัญชี" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">จำนวนเงิน</label>
                      <input type="text"  class="form-control" value="<?php echo $deposit['amount'] ?>" placeholder="ข้อมูลจำนวนเงิน" readonly style=" text-align: right;">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ฝากเมื่อ</label>
                      <input type="text" id="created_at" name="created_at" class="form-control" value="<?php echo $deposit['created_at'] ?>" placeholder="ข้อมูลฝากเมื่อ" readonly>
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

              <h3 class="card-title">ข้อมูลโปรโมชั่น</h3>
              <hr>
              <div class="form-body mt-3">
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อโปรโมชั่น</label>
                      <input type="text" id="name" name="name" class="form-control"  value="<?php echo $deposit['promotion_name']; ?>" placeholder="ข้อมูลชื่อโปรโมชั่น" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">จำนวนโบนัส (%)</label>
                      <input type="text" id="percent" name="percent" class="form-control text-right" value="<?php echo $deposit['percent']; ?>" placeholder="ข้อมูลจำนวนโบนัส (%)" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">โบนัสสูงสุด (บาท)</label>
                      <input type="text" id="max_value" name="max_value" class="form-control text-right" value="<?php echo $deposit['max_value']; ?>"  placeholder="ข้อมูลโบนัสสูงสุด (บาท)" readonly>
                    </div>
                  </div>
                </div>
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">คูณยอดเทิร์น</label>
                      <input type="text" id="turn" name="turn" class="form-control text-right" value="<?php echo $deposit['turn']; ?>" placeholder="ข้อมูลคูณยอดเทิร์น" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">โบนัส</label>
                      <input type="text"  class="form-control text-right" value="<?php echo ($deposit['sum_amount']-$deposit['amount']); ?>" placeholder="ข้อมูลโบนัส" readonly>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">รวม</label>
                      <input type="text"  class="form-control text-right" value="<?php echo ($deposit['sum_amount']); ?>" placeholder="ข้อมูลรวม" readonly>
                    </div>
                  </div>
                </div>
                <hr />
                <div class="row">
                  <div class="col-md-12">
                    <div class="text-right m-b-10">
                      <a type="button" href="<?php echo site_url('deposit') ?>" class=" btn bg-gradient-warning waves-effect waves-light mr-1"><span><i class="fa fa-arrow-left mr-1"></i></span>ย้อนกลับ</a>
                    </div>
                  </div>
                </div>
              </div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/scripts/user/user_update.js') ?>"></script>
