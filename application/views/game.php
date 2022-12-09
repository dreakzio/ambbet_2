<style>
	@media (min-width: 320px) and (max-width: 1024px) {
		.mobile-tran {
			height: 100%;
			margin-top: 0px !important;
		}

		.mobile-text {
			font-size: 15px !important;
		}
	}

	.mobile-tran {
		margin-top: 50px;
	}



	.banner-overlay {
		overflow: hidden;
	}
	#overlay {
		position: absolute;
		display: none;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #000000d1;
		z-index: 2;
		cursor: pointer;
	}

	#text{
		position: absolute;
		color: white;
		text-align: center;
		width: 100%;
		height: 100%;
		top: 25%;
	}
	figure {
		width: 100%;
		height: auto;
		margin: 0;
		padding: 0;
		background: #fff;
		overflow: hidden;
	}

	figure:hover+span {
		bottom: -36px;
		opacity: 1;
	}

	.play figure {
		position: relative;
	}

	.play figure::before {
		position: absolute;
		top: 0;
		left: -75%;
		z-index: 2;
		display: block;
		content: '';
		width: 50%;
		height: 100%;
		background: -webkit-linear-gradient(left, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, .3) 100%);
		background: linear-gradient(to right, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, .3) 100%);
		-webkit-transform: skewX(-25deg);
		transform: skewX(-25deg);
	}

	.play figure:hover::before {
		-webkit-animation: shine .75s;
		animation: shine .75s;
	}

	@-webkit-keyframes shine {
		100% {
			left: 125%;
		}
	}

	@keyframes shine {
		100% {
			left: 125%;
		}
	}

	@media (min-width: 1025px) {
		.games-nav {
			width: 69% !important;
			margin: 0 auto !important;
			margin-bottom: 20px !important;
		}

	}

	.games-main {
		background: url(img/pt1.png);

		background-color: #000;
		border-radius: 12px;
	}

	.games-nav {
		width: 100%;

	}

	.games-nav-list {
		width: 25%;
		float: left;
	}

	.games-nav-list-first img {
		border-top-left-radius: 12px;
		border-bottom-left-radius: 12px;
	}
</style>
<?php
//print_r($_SESSION);
if($_SESSION['user']['username']=='0826539264'){
	//echo "test";
	//print_r($Game_status);
}
?>
<section class="game-trans">
	<div class="games-main">
		<div class="games-nav mt-3 mb-3">
			<div id="SlotIcon" class="games-nav-list games-nav-list-first" onclick="Open('Slot')">
				<img id="nav_slot" src="<?php echo base_url(); ?>assets/images/nav-slot-red.png" class="w-100 pointer" />
			</div>
			<div id="CasinoIcon" class="games-nav-list games-nav-list-first" onclick="Open('Casino')">
				<img id="nav_casino" src="<?php echo base_url(); ?>assets/images/nav-casino.png" class="w-100 pointer" />
			</div>
			<div id="FootballIcon" class="games-nav-list games-nav-list-last" onclick="Open('Football')">
				<img id="nav_sport" src="<?php echo base_url(); ?>assets/images/nav-sport.png" class="w-100 pointer" />
			</div>
			<div id="LottoIcon" class="games-nav-list games-nav-list-last" onclick="Open('Lotto')">
				<img id="nav_lotto" src="<?php echo base_url(); ?>assets/images/nav-huay.png" class="w-100 pointer" />
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="all-games">
		<div class="play">
			<div>
				<div id="Slot">
					<figure id="pg">
						<a href="<?php echo base_url('lobby/pg_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/pgbt.jpg"
																			   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="live22">
						<a href="<?php echo base_url('lobby/live22_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/live22bt.jpg"
																				   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="spg">
						<a href="<?php echo base_url('lobby/spg_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/spbt.jpg"
																				style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="ganapati">
						<a href="<?php echo base_url('lobby/ganapati_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/gmtbt.jpg"
																					 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="ameba">
						<a href="<?php echo base_url('lobby/ameba_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/ambabt.jpg"
																				  style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="askmebetslot">
						<a href="<?php echo base_url('lobby/askmebetslot_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/dgsbt.jpg"
																						 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="slotxo">
						<a href="<?php echo base_url('lobby/slotxo_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/xobt.jpg"
																				   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="ambgame">
						<a href="<?php echo base_url('lobby/ambgame_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/ambbt.jpg"
																					style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="evoplay">
						<a href="<?php echo base_url('lobby/evoplay_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/evoplaybt.jpg"
																					style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="pragmaticslot">
						<a href="<?php echo base_url('lobby/pragmaticslot_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/pragmaticslotbt.jpg"
																						  style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure  id="ambslot">
						<a href="<?php echo base_url('lobby/ambslot_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/ambslotbt.jpg"
																					style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="allwayspin">
						<a href="<?php echo base_url('lobby/allwayspin_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/allwayspinbt.jpg"
																					   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="booongo">
						<a href="<?php echo base_url('lobby/booongo_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/booongobt.jpg"
																					style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="funkygame">
						<a href="<?php echo base_url('lobby/funkygame_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/funkygamebt.jpg"
																					  style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="funtagaming">
						<a href="<?php echo base_url('lobby/funtagaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/funtabt.jpg"
																						style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="iconicgaming">
						<a href="<?php echo base_url('lobby/iconicgaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/iconicbt.jpg"
																						 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="kagaming">
						<a href="<?php echo base_url('lobby/kagaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/kabt.jpg"
																					 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="mannaplay">
						<a href="<?php echo base_url('lobby/mannaplay_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/mannaplaybt.jpg"
																					  style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="wazdandirect">
						<a href="<?php echo base_url('lobby/wazdandirect_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/wazdandirectbt.jpg"
																						 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="hotgraph">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/hotgraph_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/hotgraphbt.jpg"
																												   style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="jili">
						<a href="<?php echo base_url('lobby/jili_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/jilibt.jpg"
																				 style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="simpleplay">
						<a href="<?php echo base_url('lobby/simpleplay_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/simpleplay.png"
																					   style="width: 100%; border-radius: 10px" alt="SimplePlay Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="microgame">
						<a href="<?php echo base_url('lobby/microgame_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/microgame.png"
																					  style="width: 100%; border-radius: 10px" alt="Micro Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="yggdrasil">
						<a href="<?php echo base_url('lobby/yggdrasil_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/yggdrasil.png"
																					  style="width: 100%; border-radius: 10px" alt="Yggdrasil Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="upgslot">
						<a href="<?php echo base_url('lobby/upgslot_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/upgslot.png"
																					style="width: 100%; border-radius: 10px" alt="UPGslot Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="p8">
						<a href="<?php echo base_url('lobby/p8_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/p8.png"
																			   style="width: 100%; border-radius: 10px" alt="P8 Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="mpoker">
						<a href="<?php echo base_url('lobby/mpoker_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/mpoker.png"
																				   style="width: 100%; border-radius: 10px" alt="M-Poker Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="avgaming">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/avgaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/avgaming.png"
																												   style="width: 100%; border-radius: 10px" alt="Cherry Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="dragongaming">
						<a href="<?php echo base_url('lobby/dragongaming_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/dragongaming.png"
																						 style="width: 100%; border-radius: 10px" alt="Dragon Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="i8">
						<a href="<?php echo base_url('lobby/i8_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/i8.png"
																			   style="width: 100%; border-radius: 10px" alt="I8 Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="kingmaker">
						<a href="<?php echo base_url('lobby/kingmaker_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/kingmaker.png"
																					  style="width: 100%; border-radius: 10px" alt="King Maker Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="kingpoker">
						<a href="<?php echo base_url('lobby/kingpoker_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/kingpoker.png"
																					  style="width: 100%; border-radius: 10px" alt="King Poker Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="mega7">
						<a href="<?php echo base_url('lobby/mega7_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/mega7.png"
																				  style="width: 100%; border-radius: 10px" alt="Mega7 Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure  id="relaxgame">
						<a href="<?php echo base_url('lobby/relaxgame_game') ?>"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/relaxgame.png"
																					  style="width: 100%; border-radius: 10px" alt="Relax Gaming"/></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
				</div>
				<div id="Casino" style="display: none">
					<figure id="sa">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/sa_game')?>"><img class="mb-3"
																											src="<?php echo base_url(); ?>assets/images/game/sabt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="sexy">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/sexy_game')?>"><img class="mb-3"
																											  src="<?php echo base_url(); ?>assets/images/game/sebt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="pretty">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/pretty_game')?>"><img class="mb-3"
																												src="<?php echo base_url(); ?>assets/images/game/ptbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="ag">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/ag_game')?>"><img class="mb-3"
																											src="<?php echo base_url(); ?>assets/images/game/agbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure  id="dream">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/dream_game')?>"><img class="mb-3"
																											   src="<?php /*echo base_url(); */?>assets/images/game/dgbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure  id="allbet">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/allbet_game')?>"><img class="mb-3"
																												src="<?php /*echo base_url(); */?>assets/images/game/allbetbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="ebet">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/ebet_game')?>"><img class="mb-3"
																											  src="<?php /*echo base_url(); */?>assets/images/game/ebetbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure  id="bg">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/bg_game')?>"><img class="mb-3"
																											src="<?php /*echo base_url(); */?>assets/images/game/bgcasinobt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="pragmatic">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/pragmatic_game')?>"><img class="mb-3"
																												   src="<?php /*echo base_url(); */?>assets/images/game/pragmaticbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="greendragon">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/greendragon_game')?>"><img class="mb-3"
																													 src="<?php /*echo base_url(); */?>assets/images/game/greendgbt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="betgame">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/betgame_game')?>"><img class="mb-3"
																												 src="<?php /*echo base_url(); */?>assets/images/game/betgamebt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
					<figure id="keno">
						<a target="_blank" href="<?php echo base_url('home/play_game_once/keno_game')?>"><img class="mb-3"
																											  src="<?php /*echo base_url(); */?>assets/images/game/kenobt.jpg" style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>

				</div>
				<div id="Football" style="display: none">
					<figure id="ambbet">
						<a href="<?php echo base_url('home/play_game_once/ambbet') ?>"
						   target="_blank"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/ambbetbt.jpg"
												style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
				</div>
				<div id="Lotto" style="display: none">
					<figure id="lotto">
						<a href="<?php echo base_url('home/play_game_once/lotto_game') ?>"
						   target="_blank"><img class="mb-3" src="<?php echo base_url(); ?>assets/images/game/amblottobt.jpg"
												style="width: 100%; border-radius: 10px" /></a>
						<div id="overlay" >
							<div id="text">ปิด</div>
						</div>
					</figure>
				</div>
			</div>
		</div>
	</div>
	<script>
		let slot_status = "<?php echo $web_setting['slot_status']['value']?>";
		let casino_status = "<?php echo $web_setting['casino_status']['value']?>";
		let football_status = "<?php echo $web_setting['football_status']['value']?>";
		let lotto_status = "<?php echo $web_setting['lotto_status']['value']?>";
		let result_alert = [];
		// console.log("slot_status :",slot_status);
		// console.log("casino_status :",casino_status);
		// console.log("football_status :",football_status);
		// console.log("lotto_status",lotto_status);
		window.onload = function() {
			if (slot_status == false){
				document.getElementById("SlotIcon").style.display='none';
				document.getElementById("Slot").style.display='none';
				result_alert.push("สล็อตออนไลน์");
			}if (casino_status == false){
				document.getElementById("CasinoIcon").style.display='none';
				document.getElementById("Casino").style.display='none';
				result_alert.push("คาสิโนสด");
			}if (football_status == false){
				document.getElementById("FootballIcon").style.display='none';
				document.getElementById("Football").style.display='none';
				result_alert.push("เดิมพันกีฬา");
			}if (lotto_status == false){
				document.getElementById("LottoIcon").style.display='none';
				document.getElementById("Lotto").style.display='none';
				result_alert.push("หวย")
			}if(result_alert.length > 0){
				// console.log("typeof",typeof(result_alert));
				// console.log("result :",result_alert);
				// sweetAlert2('warning', 'ขณะนี้มีเกมส์กำลังปิดใช้งานดังนี้ :'+ result_alert);
			}
		};
		function Open(type) {
			if (type == "Slot") {
				$("#Slot").fadeIn();
				$("#Casino").hide();
				$("#Football").hide();
				$("#Lotto").hide();

				$("#nav_slot").attr("src", "<?php echo base_url() ?>assets/images/nav-slot-red.png");
				$("#nav_casino").attr("src", "<?php echo base_url() ?>assets/images/nav-casino.png");
				$("#nav_sport").attr("src", "<?php echo base_url() ?>assets/images/nav-sport.png");
				$("#nav_lotto").attr("src", "<?php echo base_url() ?>assets/images/nav-huay.png");
			} else if (type == "Casino") {
				$("#Slot").hide();
				$("#Casino").fadeIn();
				$("#Football").hide();
				$("#Lotto").hide();

				$("#nav_slot").attr("src", "<?php echo base_url() ?>assets/images/nav-slot.png");
				$("#nav_casino").attr("src", "<?php echo base_url() ?>assets/images/nav-casino-red.png");
				$("#nav_sport").attr("src", "<?php echo base_url() ?>assets/images/nav-sport.png");
				$("#nav_lotto").attr("src", "<?php echo base_url() ?>assets/images/nav-huay.png");
			}else if (type == "Lotto") {
				$("#Slot").hide();
				$("#Lotto").fadeIn();
				$("#Football").hide();
				$("#Casino").hide();

				$("#nav_slot").attr("src", "<?php echo base_url() ?>assets/images/nav-slot.png");
				$("#nav_casino").attr("src", "<?php echo base_url() ?>assets/images/nav-casino.png");
				$("#nav_sport").attr("src", "<?php echo base_url() ?>assets/images/nav-sport.png");
				$("#nav_lotto").attr("src", "<?php echo base_url() ?>assets/images/nav-huay-red.png");
			} else {
				$("#Slot").hide();
				$("#Casino").hide();
				$("#Lotto").hide();
				$("#Football").fadeIn();

				$("#nav_slot").attr("src", "<?php echo base_url() ?>assets/images/nav-slot.png");
				$("#nav_casino").attr("src", "<?php echo base_url() ?>assets/images/nav-casino.png");
				$("#nav_sport").attr("src", "<?php echo base_url() ?>assets/images/nav-sport-red.png");
				$("#nav_lotto").attr("src", "<?php echo base_url() ?>assets/images/nav-huay.png");
			}
		}
	</script>
</section>
