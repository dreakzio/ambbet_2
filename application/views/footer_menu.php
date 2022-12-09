<footer class="text-muted">
	<div class="container text-center mt-2">
		<p>Copyright By &copy; <?php echo date('Y'); ?> <?php echo isset($web_setting['web_name']) ? $web_setting['web_name']['value'] : ""; ?> All rights reserved.</p>
	</div>
</footer>
<div class="fix-nav-bottom">
	<div class="fix-nav-bottom">
		<div class="scroll-text">
			<marquee scrolldelay="100" onmouseover="this.stop();" onmouseout="this.start();" behavior="" direction="">
				<div class="p-marquee" id="postgame_Status">
					<?php echo ($web_setting['testdata']); ?>
					ยินดีต้อนรับเข้าสู่เว็บไซต์ <?php echo isset($web_setting['web_name']) ? $web_setting['web_name']['value'] : ""; ?> ฝาก-ถอนออโต้
					พนันออนไลน์ อันดับหนึ่ง ให้บริการ คาสิโนออนไลน์
					เราให้บริการด้านการเดิมพันแบบครบวงจรตลอด 24ชม
				</div>
			</marquee>
		</div>
		<div class="container pr-0 pl-0">
			<ul>
				<li>
					<a href="<?php echo base_url('dashboard'); ?>" class="hvr-buzz-out"><i class="fa fa-home"></i>
						<p>หน้าหลัก</p>
					</a>
				</li>

				<?php if(isset($_SESSION['user'])): ?>
					<li>
						<a href="<?php echo base_url('promotions'); ?>" class="hvr-buzz-out"><i class="fa fa-gift"></i>
							<p>โปรโมชั่น</p>
						</a>
					</li>
					<li class="fix-nav-bottom-play">
						<a href="<?php echo base_url('game'); ?>" class="hvr-buzz-out"><i class="fa fa-play"></i>
							<p>เล่นเกม</p>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url('profile'); ?>" class="hvr-buzz-out"><i class="fa fa-user"></i>
							<p>บัญชีผู้ใช้</p>
						</a>
					</li>
				<?php else: ?>
					<li>
						<a href="<?php echo base_url('auth'); ?>" class="hvr-buzz-out"><i class="fa fa-sign-in-alt"></i>
							<p>
								เข้าสู่ระบบ
							</p>
						</a>
					</li>
					<li class="fix-nav-bottom-play">
						<a href="<?php echo base_url('register'); ?>" class="hvr-buzz-out"><i class="fa fa-user-plus"></i>
							<p>
								สมัครสมาชิก
							</p>
						</a>
					</li>
					<li>
						<a href="<?php echo base_url('promotions'); ?>" class="hvr-buzz-out"><i class="fa fa-gift"></i>
							<p>โปรโมชั่น</p>
						</a>
					</li>
				<?php endif; ?>
				<li>
					<a href="<?php echo $web_setting['line_url']['value']; ?>" class="hvr-buzz-out"><i class="fab fa-line"></i>
						<p>ติดต่อเรา</p>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>

<script type="text/javascript">
	let arr_parse = [];
	let newParse = null;
	let text_annoument = [];

	$.ajax({
		url: "<?=base_url("home/get_Gamestatus")?>",
		type: "GET", // for protect sensitive data
		dataTyppe: 'json',
		async : false,
		success:function(response){
			arr_parse = JSON.parse(response);
		}
	});
	let madeAnnoument = function(){
		newParse = arr_parse.result.filter(function (el){
			return el.active == false;
		});
		for (var i = 0; i < newParse.length; i++) {
			text_annoument.push("ประกาศปิดปรับปรุงเกมส์จากค่าย:"+"'"+newParse[i].game +"'"+ " รายละเอียด:" +"'"+ newParse[i].status + "'");
			text_annoument.toString();
		}
	}
	madeAnnoument();
	document.getElementById("postgame_Status").innerHTML = text_annoument.toString();

	<?php

	$game_route = strpos(current_url(), 'game');
	if($game_route !==false){
	?>
	function disabledGame(){
		//console.log(arr_parse);
		var gamelist = arr_parse.result;
		for (var i = 0; i < gamelist.length; i++) {
			//console.log(gamelist[i]);
			if(gamelist[i].active==false){

				gameCloseId = gamelist[i].game;
				console.log(gameCloseId);
				$('#'+gameCloseId).find("#overlay").css('display','block');
				$('#'+gameCloseId).find("#text").html('<b style="font-size: 30px;">ปิดปรับปรุง</b>');
			}
		}
	}
	disabledGame();
	<?php
	}
	?>
</script>
