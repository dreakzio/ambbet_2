<style>
  .play-title{
    font-size: 15px !important;
	  color : #0B0B0B;
  }
  .btn-toggle {
	  top: 50%;
	  transform: translateY(-50%);
  }
  .btn-toggle {
	  margin: 0 4rem;
	  padding: 0;
	  position: relative;
	  border: none;
	  height: 1.5rem;
	  width: 3rem;
	  border-radius: 1.5rem;
	  color: #6b7381;
	  background: #bdc1c8;
  }
  .btn-toggle:focus, .btn-toggle:focus.active, .btn-toggle.focus, .btn-toggle.focus.active {
	  outline: none;
  }
  .btn-toggle:before, .btn-toggle:after {
	  line-height: 1.5rem;
	  width: 4rem;
	  text-align: center;
	  font-weight: 600;
	  font-size: .75rem;
	  text-transform: uppercase;
	  letter-spacing: 2px;
	  position: absolute;
	  bottom: 0;
	  transition: opacity .25s;
  }
  .btn-toggle:before {
	  content: 'Off';
	  left: -4rem;
  }
  .btn-toggle:after {
	  content: 'On';
	  right: -4rem;
	  opacity: .5;
  }
  .btn-toggle > .handle {
	  position: absolute;
	  top: 0.1875rem;
	  left: 0.1875rem;
	  width: 1.125rem;
	  height: 1.125rem;
	  border-radius: 1.125rem;
	  background: #fff;
	  transition: left .25s;
  }
  .btn-toggle.active {
	  transition: background-color .25s;
  }
  .btn-toggle.active > .handle {
	  left: 1.6875rem;
	  transition: left .25s;
  }
  .btn-toggle.active:before {
	  opacity: .5;
  }
  .btn-toggle.active:after {
	  opacity: 1;
  }
  .btn-toggle.btn-sm:before, .btn-toggle.btn-sm:after {
	  line-height: -0.5rem;
	  color: #fff;
	  letter-spacing: .75px;
	  left: 0.4125rem;
	  width: 2.325rem;
  }
  .btn-toggle.btn-sm:before {
	  text-align: right;
  }
  .btn-toggle.btn-sm:after {
	  text-align: left;
	  opacity: 0;
  }
  .btn-toggle.btn-sm.active:before {
	  opacity: 0;
  }
  .btn-toggle.btn-sm.active:after {
	  opacity: 1;
  }
  .btn-toggle:before, .btn-toggle:after {
	  color: #6b7381;
  }
  .btn-toggle.active {
	  background-color: #29b5a8;
  }
  .btn-toggle.btn-sm {
	  margin: 0 .5rem;
	  padding: 0;
	  position: relative;
	  border: none;
	  height: 1.5rem;
	  width: 3rem;
	  border-radius: 1.5rem;
  }
  .btn-toggle.btn-sm:focus, .btn-toggle.btn-sm:focus.active, .btn-toggle.btn-sm.focus, .btn-toggle.btn-sm.focus.active {
	  outline: none;
  }
  .btn-toggle.btn-sm:before, .btn-toggle.btn-sm:after {
	  line-height: 1.5rem;
	  width: .5rem;
	  text-align: center;
	  font-weight: 600;
	  font-size: .55rem;
	  text-transform: uppercase;
	  letter-spacing: 2px;
	  position: absolute;
	  bottom: 0;
	  transition: opacity .25s;
  }
  .btn-toggle.btn-sm:before {
	  content: 'Off';
	  left: -0.5rem;
  }
  .btn-toggle.btn-sm:after {
	  content: 'On';
	  right: -0.5rem;
	  opacity: .5;
  }
  .btn-toggle.btn-sm > .handle {
	  position: absolute;
	  top: 0.1875rem;
	  left: 0.1875rem;
	  width: 1.125rem;
	  height: 1.125rem;
	  border-radius: 1.125rem;
	  background: #fff;
	  transition: left .25s;
  }
  .btn-toggle.btn-sm.active {
	  transition: background-color .25s;
  }
  .btn-toggle.btn-sm.active > .handle {
	  left: 1.6875rem;
	  transition: left .25s;
  }
  .btn-toggle.btn-sm.active:before {
	  opacity: .5;
  }
  .btn-toggle.btn-sm.active:after {
	  opacity: 1;
  }
  .btn-toggle.btn-sm.btn-sm:before, .btn-toggle.btn-sm.btn-sm:after {
	  line-height: -0.5rem;
	  color: #fff;
	  letter-spacing: .75px;
	  left: 0.4125rem;
	  width: 2.325rem;
  }
  .btn-toggle.btn-sm.btn-sm:before {
	  text-align: right;
  }
  .btn-toggle.btn-sm.btn-sm:after {
	  text-align: left;
	  opacity: 0;
  }
  .btn-toggle.btn-sm.btn-sm.active:before {
	  opacity: 0;
  }
  .btn-toggle.btn-sm.btn-sm.active:after {
	  opacity: 1;
  }

</style>
<script>
	$(document).ready(function(){
		$('.progress').hide(); // hide class 1st
		$('#btn_write').click(function () {
			if ($('.progress').is(':hidden')) {
				$('.progress').show();
			} else {
				$('.progress').hide();
			}
		});
	});
</script>
<section class="lobby">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('game') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-play"></i> <?php echo $this->lang->line('playgame'); ?></a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-gamepad"></i>&nbsp;
		<?php 
		echo empty($game_name) ? "" : " ".$game_name; 
		?>
	</span>
	<span><?php echo $this->lang->line('winrate'); ?></span>
	<button type="button" id="btn_write" class="btn btn-sm btn-toggle" style="top: 10px; bottom: 10px;" data-toggle="button" aria-pressed="false" autocomplete="off">
		<div class="handle"></div>
	</button>
	<hr style="margin-top: 15px">
</section>
<div class="page-content" >
  <div  class="" style="">
	  <div class="row mt-3 mb-0 text-center" >
		  <?php foreach($game_list as $data_game): ?>
			<div class="col-3 col-sm-2 pl-1 pr-1">
				<?php
					$percentage = $_SESSION['percentage_'.$game_code][$data_game['gameId']]; // $agent_name should be"pg_game"oranything else
					$game_type = strtolower($data_game['gameType']);
					if(isset($data_game['gameName'])){
						$game_name =$data_game['gameName'];
					}else if(isset($data_game['name'])){
						$game_name =$data_game['name']['th'];
					}
					$imgUrl = "";
					if(isset($data_game['imgUrl'])){
						$imgUrl =$data_game['imgUrl'];
					}else if(isset($data_game['thumbnail'])){
						$imgUrl =$data_game['thumbnail'];
					}
				?>
				<?php if((isset($data_game['isActivated']) && $data_game['isActivated']) || isset($data_game['isActive']) && $data_game['isActive']): ?>
				  <a target="_blank" href="<?php echo site_url('/home/play_game/'.$game_code.'/'.$data_game['gameId'].'?title='.$game_name) ?>" data-name="<?php echo $game_name; ?>" data-type="<?php echo $game_code; ?>" class="btn-play-game">
					<div class="card-game box-shadow-3 hvr-grow">
					  <div class="card-body p-1">
						<img class="img-fluid rounded img-game" src="<?php echo $imgUrl; ?>" />
						<h5 class="play-title mt-0 mb-0"><?php echo $game_name; ?></h5>
						<?php if($game_type == "slot" || $game_type == "table"): ?>
						   <div class="progress">
								  <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $percentage; ?>%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"><?php echo $percentage; ?></div>
							</div>
						<?php endif; ?>
					  </div>
					</div>
				  </a>
				<?php endif; ?>
			</div>
		  <?php endforeach; ?>
      </div>
  </div>
</div>
