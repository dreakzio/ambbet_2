<section class="bonus text-left">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('dashboard') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-backward"></i> <?php echo $this->lang->line('back'); ?></a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-gift"></i>&nbsp;<?php echo $this->lang->line('promotion'); ?></span>
	<hr style="margin-top: 15px">
</section>
	<div class="row mx-auto">
		<?php
		$chk_gen = false;
		?>
		<?php foreach($promotions as $promotion): ?>
			<?php if((($promotion['percent'] > 0 || !empty($promotion['image_url'])) || ($promotion['fix_amount_deposit'] > 0  && $promotion['category'] == "2"))): ?>
				<div class="col-12 col-sm-6 mb-3 Casino pl-1 pr-1">
					<div class="card">
						<div class="card-body p-0">
							<div class="" style="width:100%;">
								<a href="#" data-toggle="modal" data-target="#promotionModal<?php echo $promotion['id']; ?>">
									<img style="width: 100%" src="<?php echo !empty($promotion['image']) ? $promotion['image_url'] : "/assets/images/not-found.png"; ?>" class="img-fluid" alt="<?php echo $promotion['name']; ?>">
								</a>
							</div>
						</div>

					</div>
				</div>
				<div class="modal" id="promotionModal<?php echo $promotion['id']; ?>" data-keyboard="false" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-body">
								<div class="text-center">
									<img style="width: 100%" src="<?php echo !empty($promotion['image']) ? $promotion['image_url'] : "/assets/images/not-found.png"; ?>" class="img-fluid" alt="<?php echo $promotion['name']; ?>">
								</div>
								<div class="row" style="">
									<div class="col-12">
										<h4 class="text-dark text-center mt-2 mb-2"><?php echo $promotion['name']; ?>&nbsp;
											<?php if ($promotion['max_value']>0 && $promotion['category'] == "1"): ?>
												<?php echo $this->lang->line('max'); ?> <?php echo number_format($promotion['max_value']) ?> <?php echo $this->lang->line('bath'); ?>
											<?php elseif ($promotion['category'] == "2"): ?>
											<?php endif; ?>
										</h4>
										<h6 class="text-danger font-weight-bold">** <?php echo $this->lang->line('just_do_some_turn'); ?></h6>
										<hr class="mt-1 mb-1">
										<div class="row">
											<?php foreach (game_code_list() as $game_code): ?>
												<?php if(isset($promotion['turn_'.strtolower($game_code)])): ?>
													<div class="col-md-6">
														<strong class=""><?php echo array_key_exists($game_code,game_code_text_list()) ? game_code_text_list()[$game_code] : $game_code; ?></strong> : <span class="float-right mr-2"><?php echo is_numeric($promotion['turn_'.strtolower($game_code)]) && (float)$promotion['turn_'.strtolower($game_code)] >0 ? $promotion['turn_'.strtolower($game_code)].$this->lang->line('tao') : '-'; ?></span>
													</div>
												<?php endif; ?>
											<?php endforeach; ?>
										</div>
									</div>
									<div class="col-12 text-right">
										<a href="#" data-dismiss="modal" data-target="#promotionModal<?php echo $promotion['id']; ?>" class="btn btn-success"><?php echo $this->lang->line('close'); ?></a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

	</div>


