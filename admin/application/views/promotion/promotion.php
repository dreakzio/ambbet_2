<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
  <link rel="stylesheet" href="<?php echo base_url('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบจัดการโปรโมชั่น</h2>
          </div>
        </div>
      </div>
    </div>
    <div class="content-body">
      <section class="card">
        <div class="card-header">
          <h4 class="card-title">รายการโปรโมชั่น</h4>
        </div>
        <div class="card-content">
          <div class="card-body">
            <div class="row" style=" margin-bottom: 40px;">
              <div class="col-12 text-right">
                <a href="<?php echo site_url('promotion/promotion_form_create'); ?>" class="btn bg-gradient-primary"><span><i class="fa fa-plus mr-2"></i></span>เพิ่มโปรโมชั่น</a>
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="table-responsive">
                  <!-- table-bordered -->
                  <table id="table" class="table dataTables_wrapper dt-bootstrap4  table-hover" style="width:100%">
                    <thead>
                      <tr>
                        <th class="text-center">รูปแบบ</th>
                        <th class="text-center">ประเภท</th>
                        <th class="text-center">ชื่อโปรโมชั่น</th>
						<th class="text-right">Fix ยอดฝาก / โบนัส (บาท)</th>
                        <th class="text-right">จำนวนโบนัส (%)</th>
                        <th class="text-right">โบนัสสูงสุด (บาท)</th>
                        <th class="text-center">คูณยอดเทิร์น</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center" >จัดการข้อมูล</th>
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
  <div class="modal fade" id="modal_turn" tabindex="-1" role="dialog"  aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" >คูณยอดเทิร์น</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                <div class="row">

                </div>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn bg-gradient-primary" data-dismiss="modal">ตกลง</button>
              </div>
          </div>
      </div>
  </div>
  <script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') ?>"></script>
<script>
	const game_code_list = JSON.parse('<?php echo json_encode(game_code_list()) ?>');
	const game_code_text_list = JSON.parse('<?php echo json_encode(game_code_text_list()) ?>');
</script>
  <script src="<?php echo base_url('assets/scripts/promotion/promotion.js?'.time()) ?>"></script>
