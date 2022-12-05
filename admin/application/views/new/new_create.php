<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
  <link rel="stylesheet" href="<?php echo base_url('assets/plugins/dropify/dist/css/dropify.min.css'); ?>">
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบจัดการประกาศ</h2>
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('news') ?>">รายการประกาศ</a>
                </li>
                <li class="breadcrumb-item active">เพิ่มประกาศ</li>
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
            <form class="form" id="form_update" method="POST" action="<?php echo site_url("news/new_create") ?>"  enctype="multipart/form-data">
              <h3 class="card-title">ข้อมูลประกาศ</h3>
              <hr>
              <div class="form-body mt-3">
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อประกาศ</label>
                      <input type="text" id="name" name="name" class="form-control"  placeholder="ข้อมูลชื่อประกาศ">
                    </div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">เรียงลำดับ</label>
							<input type="number" id="seq" name="seq" class="form-control" placeholder="เรียงลำดับ">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">URL</label>
							<input type="text" id="url" name="url" class="form-control"  placeholder="ข้อมูล URL">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">แจ้งเตือนหน้าเว็ป (สถานะ)</label>
							<select class="form-control" name="status_alert" id="status_alert">
								<option value="0">ปิด</option>
								<option value="1">เปิด</option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">แจ้งเตือนหน้าเว็ป (แสดงรูป)</label>
							<select class="form-control" name="status_image_alert" id="status_image_alert">
								<option value="0">ปิด</option>
								<option value="1">เปิด</option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">สถานะ</label>
							<select class="form-control" name="status" id="status">
								<option value="0">ปิด</option>
								<option value="1">เปิด</option>
							</select>
						</div>
					</div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                   <fieldset class="form-group">
                     <label >รูปภาพ (1040x1040)</label>
                     <input type="file" id="image"  name="image" class="dropify" data-height="200" data-allowed-file-extensions="jpg png"  />
                   </fieldset>
                 </div>
                </div>
                <hr />
                <div class="row">
                  <div class="col-md-12">
                    <div class="text-right m-b-10">
                      <a type="button" href="<?php echo site_url('news') ?>" class=" btn bg-gradient-warning waves-effect waves-light mr-1"><span><i class="fa fa-arrow-left mr-1"></i></span>ย้อนกลับ</a>
                      <button  id="btn_create" type="submit" class=" btn bg-gradient-success waves-effect waves-light"><span><i class="fa fa-save mr-1"></i></span>บันทึก</button>
                    </div>
                  </div>
                </div>
              </div>

            </form>
          </div>
        </div>
      </section>
    </div>
  </div>
  <script src="<?php echo base_url('assets/plugins/dropify/dist/js/dropify.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
  <script src="<?php echo base_url('assets/scripts/new/new_create.js?'.time()) ?>"></script>
