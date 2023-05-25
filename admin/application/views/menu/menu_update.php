<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบจัดการเมนู (หลัก)</h2>
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('menu/main') ?>">รายการเมนู (หลัก)</a>
                </li>
                <li class="breadcrumb-item active">แก้ไขเมนู (หลัก)</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
      <form class="form" id="form_update" method="POST" action="<?php echo site_url("menu/main_update/{$menu['id']}") ?>">
    <div class="content-body">
      <section class="card">
        <div class="card-content">
          <div class="card-body">
              <div class="form-body mt-3">
                <div class="row ">
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">หมวดหมู่</label>
							<select name="parent_id" id="parent_id" class="form-control">
								<option value="" selected disabled>กรุณาเลือก</option>
								<?php foreach ($group_menu_list as $group_menu): ?>
									<option value="<?php echo $group_menu['id']; ?>" <?php echo $group_menu['id'] == $menu['parent_id'] ? 'selected' : '' ?>>
										<?php echo $group_menu['name'].($group_menu['is_deleted'] == '1' ? ' (ปิดใช้งาน)' : ''); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อ</label>
                      <input type="text" id="name" name="name" class="form-control" value="<?php echo $menu['name'] ?>" placeholder="ข้อมูลชื่อ">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">รายละเอียด</label>
						<textarea type="text" id="description" rows="3" name="description" class="form-control" placeholder="ข้อมูลรายละเอียด"><?php echo $menu['description'] ?></textarea>
                    </div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">URL</label>
							<input type="text" id="url" name="url" readonly disabled class="form-control" value="<?php echo site_url('/').$menu['url'] ?>" placeholder="ข้อมูล URL">
						</div>
					</div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">Icon Class</label>
                      <input type="text" id="icon_class" name="icon_class" class="form-control" value="<?php echo $menu['icon_class'] ?>" placeholder="ข้อมูล Icon Class">
                    </div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">มีเมนูย่อย</label>
							<input type="text" id="have_child" name="have_child"  readonly disabled  class="form-control" value="<?php echo $menu['have_child'] == "1" ? 'YES' : "NO" ?>" placeholder="ข้อมูลมีเมนูย่อย">
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">สถานะ</label>
							<select class="form-control" name="is_deleted" id="is_deleted">
								<option value="0">เปิดใช้งาน</option>
								<option value="1" <?php if ($menu['is_deleted']=="1"): ?>
									selected
								<?php endif; ?> value="1">ปิดใช้งาน</option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">เรียงลำดับ</label>
							<input oninput="validateInputNumber(this)" type="number" id="order" name="order" class="form-control" value="<?php echo $menu['order'] ?>" placeholder="ข้อมูลเรียงลำดับ">
						</div>
					</div>
                </div>
			  <div class="row">
				  <div class="col-md-12">
					  <div class="text-right m-b-10">
						  <a type="button" href="<?php echo site_url('menu/main') ?>" class=" btn bg-gradient-warning waves-effect waves-light mr-1"><span><i class="fa fa-arrow-left mr-1"></i></span>ย้อนกลับ</a>
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
  <script src="<?php echo base_url('assets/plugins/numeral/min/numeral.min.js') ?>"></script>
<script src="<?php echo base_url('assets/scripts/menu/menu_update.js?t='.time()) ?>"></script>
