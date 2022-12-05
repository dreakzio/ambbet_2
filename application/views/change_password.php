<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-key"></i>&nbsp;เปลี่ยนรหัสผ่าน</span>
	<hr style="margin-top: 15px">
</section>
<div class="row mx-auto">
	<div class="col-12 mx-auto">
		<div>
			<div class="form-group">
				<label>รหัสผ่านเก่า</label>
				<input type="password" class="form-control" v-model="form.old_password" name="old_password" id="old_password" required="" placeholder="***********" />
			</div>
			<div class="form-group">
				<label>รหัสผ่านใหม่</label>
				<input type="password" class="form-control" v-model="form.password" name="password" id="password" required="" placeholder="***********" />
			</div>
			<div class="form-group">
				<label>ยืนยันรหัสผ่านใหม่</label>
				<input type="password" class="form-control" v-model="form.password_confirm" name="password_confirm" id="password_confirm" required="" placeholder="***********" />
			</div>
			<div class="form-group">
				<button type="button" :disabled="pre_loader"  @click.prevent="doChangePassword()"  class="btn-red btn-lg btn-block" style="font-size: 17px" name="button">
					<i v-if="pre_loader" class="fa fa-circle-notch fa-spin"></i>
					<span v-else class="text-silver">เปลี่ยนรหัสผ่าน</span>
				</button>
			</div>
		</div>
	</div>
</div>
<loading :active.sync="pre_loader"
		 :can-cancel="false"
		 :width="80"
		 :height="60"
		 :opacity="0.2"
		 color="#fff"
		 :is-full-page="true"></loading>
<script>
	const ref_turn = "<?php echo isset($web_setting['ref_return_balance_turn']) && !empty($web_setting['ref_return_balance_turn']['value']) ? $web_setting['ref_return_balance_turn']['value'] : '0'?>"
	const is_active_return_balance = '<?php echo $user['is_active_return_balance']; ?>'
</script>
<script src="<?php echo base_url('assets/scripts/profile.js?').time() ?>"></script>


