<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('profile') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-university"></i>&nbsp;ข้อมูลธนาคาร</span>
	<hr style="margin-top: 15px">
</section>
<div class="row">
	<div class="col-sm-12 col-md-12">
		<center>
			<?php if(in_array($user['bank'],["01","1"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/1.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["02","2"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/2.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["03","3"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/3.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["04","4"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/5.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["05","5"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/6.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["06","6"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/4.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["07","7"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/7.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["08","8"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/9.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php elseif(in_array($user['bank'],["09","9"])): ?>
				<img src="<?php echo base_url() ?>assets/images/banks/baac.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php else: ?>
				<img src="<?php echo base_url() ?>assets/images/banks/not-found.png" class="img-rounded img-responsive" style="width: 100px" />
			<?php endif; ?>
			<br>
			<br>
			<p style="font-size: 22px;">
				ธนาคาร : <?php echo array_key_exists($user['bank'],getBankList()) ? getBankList()[$user['bank']] : '-'; ?><br />
				ชื่อบัญชี : <?php echo $user['full_name']; ?><br />
				เลขบัญชี : <?php echo getBankNumberFormat($user['bank_number']); ?>
			</p>
		</center>
	</div>
</div>
