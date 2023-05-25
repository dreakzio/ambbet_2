<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
  <div class="content-wrapper">
    <div class="content-header row">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-left mb-0">ระบบจัดการตำแหน่งการใช้งาน</h2>
            <div class="breadcrumb-wrapper col-12">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url('role') ?>">รายการตำแหน่งการใช้งาน</a>
                </li>
                <li class="breadcrumb-item active">แก้ไขตำแหน่งการใช้งาน</li>
              </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
      <form class="form" id="form_update" method="POST" action="<?php echo site_url("role/role_update/{$role['role_id']}") ?>">
    <div class="content-body">
      <section class="card">
        <div class="card-content">
          <div class="card-body">
              <div class="form-body mt-3">
				  <h3 class="card-title header-form-api mt-2">ข้อมูลตำแหน่ง</h3>
				  <hr>
                <div class="row ">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">ชื่อตำแหน่ง</label>
                      <input type="text" id="role_name" name="role_name" class="form-control" value="<?php echo $role['role_name'] ?>" placeholder="ข้อมูลชื่อตำแหน่ง">
                    </div>
                  </div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">สถานะ</label>
							<select class="form-control" name="is_deleted" id="is_deleted">
								<option value="0">เปิดใช้งาน</option>
								<option value="1" <?php if ($role['is_deleted']=="1"): ?>
									selected
								<?php endif; ?> value="1">ปิดใช้งาน</option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">Level</label>
							<input oninput="validateInputNumber(this)" type="number" id="role_level" name="role_level" class="form-control" value="<?php echo $role['role_level'] ?>" placeholder="ข้อมูล Level">
						</div>
					</div>
                </div>
				  <h3 class="card-title header-form-api mt-2">ข้อมูลตำแหน่งภายใต้ที่จัดการได้</h3>
				  <hr>
				  <div class="row ">
					  <?php
					  $role_display = roleDisplay();
					  ?>
					  <div class="col-12 col-md-6">
						  <div class="form-group">
							  <ul class="list-group mb-2">
								  <li class="list-group-item active">ตำแหน่ง</li>
								  <?php foreach ($role_display as $role_id => $role_name): ?>
									  <?php if($role_id != roleSuperAdmin()): ?>
										  <li class="list-group-item">
											  <div class="form-check">
												  <input class="form-check-input" type="checkbox" value="<?php echo $role_id; ?>" <?php echo in_array($role_id,$role_can_manage_list) ? 'checked' : ''; ?> name="role_list[]" id="roleCheck<?php echo $role_id; ?>">
												  <label class="form-check-label" for="roleCheck<?php echo $role_id; ?>">
													  <?php echo $role_name; ?>
												  </label>
											  </div>
										  </li>
									  <?php endif; ?>
								  <?php endforeach; ?>
							  </ul>
						  </div>
					  </div>
				  </div>
				  <h3 class="card-title header-form-api mt-2">ข้อมูลเมนูที่สามารถเข้าได้</h3>
				  <hr>
				  <div class="row ">
					  <?php foreach ($group_menu_list as $group_menu_id => $group_menu): ?>
						  <div class="col-12 col-md-6">
							  <div class="form-group">
								  <ul class="list-group mb-2">
									  <li class="list-group-item active"><?php echo $group_menu['name']; ?></li>
									  <?php foreach ($group_menu['menu_list'] as $menu): ?>
										  <?php if(!in_array(strtolower($menu['menu_url']),$menu_url_ignore_list)): ?>
											  <?php
											  $chk_create = true;
											  $chk_node_exist = false;
											  if(isset($menu['node_menu_list']) && count($menu['node_menu_list']) > 0){
												  $chk_node_exist = true;
												  foreach ($menu['node_menu_list'] as $node_menu){
													  if(
															  $chk_create &&
															  in_array(strtolower($node_menu['url']),$menu_url_ignore_list)
													  ){
														  $chk_create = false;

													  }
												  }
											  }
											  ?>
											  <?php if($chk_create): ?>
												  <li class="list-group-item">
													  <div class="form-check ml-2">
														  <input class="form-check-input" type="checkbox" value="<?php echo $menu['menu_id']; ?>" <?php echo in_array($menu['menu_id'],$role_menu_list) ? 'checked' : ''; ?> name="menu_list[]" id="menuCheck<?php echo $menu['menu_id']; ?>">
														  <label class="form-check-label" for="menuCheck<?php echo $menu['menu_id']; ?>">
															  <?php echo $menu['menu_name']; ?>
														  </label>
													  </div>
													  <?php if($chk_node_exist): ?>
														  <ul class="ml-2 mt-1">
															  <?php foreach ($menu['node_menu_list'] as $node_menu): ?>
																  <li>&nbsp;&nbsp;<?php echo $node_menu['name']; ?></li>
															  <?php endforeach; ?>
														  </ul>
													  <?php endif; ?>
												  </li>
											  <?php endif; ?>
										  <?php endif; ?>
									  <?php endforeach; ?>
								  </ul>
							  </div>
						  </div>
					  <?php endforeach; ?>
				  </div>
			  <div class="row">
				  <div class="col-md-12">
					  <div class="text-right m-b-10">
						  <a type="button" href="<?php echo site_url('role') ?>" class=" btn bg-gradient-warning waves-effect waves-light mr-1"><span><i class="fa fa-arrow-left mr-1"></i></span>ย้อนกลับ</a>
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
<script src="<?php echo base_url('assets/scripts/role/role_update.js?t='.time()) ?>"></script>
