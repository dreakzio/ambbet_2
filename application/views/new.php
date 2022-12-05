<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> กลับ</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-newspaper"></i>&nbsp;ข่าวสาร</span>
	<hr style="margin-top: 15px">
</section>
<div class="row mx-auto">
	<?php foreach($news as $new): ?>
		<?php if(true): ?>
			<div class="col-12 col-sm-6 mb-3 Casino pl-1 pr-1">
				<div class="card">
					<div class="card-body p-0">
						<div class="" style="width:100%;">
							<?php if(!empty($new['url']) && !is_null($new['url'])): ?>
								<a href="<?php echo $new['url']; ?>"  target="_blank">
									<img style="width: 100%" src="<?php echo !empty($new['image']) ? $new['image_url'] : "/assets/images/not-found.png"; ?>" class="img-fluid" alt="<?php echo $new['name']; ?>">
								</a>
							<?php else: ?>
								<a href="#" data-toggle="modal" data-target="#newModal<?php echo $new['id']; ?>">
									<img style="width: 100%" src="<?php echo !empty($new['image']) ? $new['image_url'] : "/assets/images/not-found.png"; ?>" class="img-fluid" alt="<?php echo $new['name']; ?>">
								</a>
							<?php endif; ?>

						</div>
					</div>

				</div>
			</div>
			<div class="modal" id="newModal<?php echo $new['id']; ?>" data-keyboard="false" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-body">
							<div class="text-center">
								<img style="width: 100%" src="<?php echo !empty($new['image']) ? $new['image_url'] : "/assets/images/not-found.png"; ?>" class="img-fluid" alt="<?php echo $new['name']; ?>">
							</div>
							<div class="row" style="">
								<div class="col-12">
									<p class="text-dark text-center mt-2 mb-2"><?php echo $new['name']; ?>
									</p>
								</div>
								<div class="col-12 text-right">
									<a href="#" data-dismiss="modal" data-target="#newModal<?php echo $new['id']; ?>" class="btn btn-success">ปิด</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>

</div>


