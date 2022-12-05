<section class="user-infor text-center">
    <a style="font-size:23px !important; margin-bottom:13px;"><b>รหัสสมาชิกของคุณคือ :</b><a
            style="background-image: linear-gradient(to right, gold , yellow);"><i style='font-size:24px'
                class='fas'>&#xf2bb;</i>
            <?php echo $user['username']; ?></a></a> <br>
    <?php if(isset($_SESSION['user']['rank']) && !is_null($_SESSION['user']['rank'])): ?>
    <div class="row">
        <div class="col-12 text-center">
            <?php if($_SESSION['user']['rank'] =="1"): ?>
            <h5 class="d-inline"><strong>Rank : Member</strong></h5>&nbsp;<img style="width: 65px"
                src="<?php echo base_url('assets/images/rank-bronze.png'); ?>" class="img-fluid" alt="...">
            <?php elseif($_SESSION['user']['rank'] =="2"): ?>
            <h5 class="d-inline"><strong>Rank : <span class="text-secondary">Silver</span></strong></h5>&nbsp;<img
                style="width:  65px" src="<?php echo base_url('assets/images/rank-silver.png'); ?>" class="img-fluid"
                alt="...">
            <?php elseif($_SESSION['user']['rank'] =="3"): ?>
            <h5 class="d-inline"><strong>Rank : <span class="text-warning">Gold</span></strong></h5>&nbsp;<img
                style="width:  65px" src="<?php echo base_url('assets/images/rank-gold.png'); ?>" class="img-fluid"
                alt="...">
            <?php else: ?>
            <h5 class="d-inline"><strong>Rank : Member</strong></h5>&nbsp;<img style="width:  65px"
                src="<?php echo base_url('assets/images/rank-bronze.png'); ?>" class="img-fluid" alt="...">
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <!-- <a href="<?php echo base_url('change-password') ?>" class="btn btn-secondary mt-2"
	   style="font-size: 16px;padding-left: 12px;padding-right: 12px;padding-top: 6px;padding-bottom: 6px;"><i
				class="fa fa-key"></i> เปลี่ยนรหัสผ่าน</a> -->
</section>
<div>
    <section class="credit">
        <div class="credit-box" style="background-color:#000;">
            <div class="amount-box float-left"><small>ยอดเงินคงเหลือ</small>
                <small class="float-right mr-3">
                    <i v-if="!loading_wallet" @click.prevent="getCreditBalance" title="อัพเดทยอดเงิน"
                        class="fas fa-sync-alt refresh pointer animated"></i>
                    <i title="อัพเดทยอดเงิน" v-else class="fas fa-sync-alt refresh fa-spin"></i>
                </small>
                <p class="amount">
                    <vue-numeric id="main_wallet" :read-only="true" empty-value="0.00" output-type="String"
                        v-bind:precision="2" v-bind:value="wallet" separator=","></vue-numeric>
                </p>
            </div>
            <div class="button-box float-left"><a href="<?php echo base_url('deposit') ?>" class="btn-block btn-gold"><i
                        class="fa fa-wallet"></i> ฝากเงิน</a>
                <a href="<?php echo base_url('withdraw') ?>" class="btn-block btn-silver"><i
                        class="fa fa-hand-holding-usd"></i>
                    ถอนเงิน</a>
            </div>
            <div class="clearfix"></div>
        </div>
    </section>
</div>
<section class="navigation">
    <div class="nav-play-button">
        <a href="<?php echo base_url('game') ?>"
            class="btn-block play-button text-center hvr-buzz-out d-none d-sm-block"
            style="text-decoration: none;padding: <?php echo $_SESSION['user']['role'] == roleAdmin() || $_SESSION['user']['role'] == roleSuperAdmin() ? '100px' : '53px' ?>;"><i
                class="fa fa-play mb-2" style="font-size: 85px !important;"></i>
            <p>เข้าเล่นเกม</p>
        </a>
        <a href="<?php echo base_url('game') ?>"
            class="btn-block play-button text-center hvr-buzz-out d-block d-sm-none"
            style="text-decoration: none;padding:53px"><i class="fa fa-play mb-2"
                style="font-size: 85px !important;"></i>
            <p>เข้าเล่นเกม</p>
        </a>
    </div>
    <div class="nav-other-button p-1">
        <div class="other-list other-list-1"><a
                href="<?php echo $user['agent'] == "1" ? base_url('agent') : base_url('ref'); ?>"
                class="btn-dark-tri hvr-buzz-out"><i class="fa fa-handshake mb-2"></i>
                <p><?php echo $user['agent'] == "1" ? 'พันธมิตร' : 'แนะนำเพื่อน'; ?></p>
            </a></div>
        <div class="other-list other-list-2"><a href="<?php echo base_url('promotions') ?>"
                class="btn-dark-tri hvr-buzz-out"><i class="fa fa-gift mb-2"></i>
                <p>โปรโมชั่น</p>
            </a></div>
        <div class="other-list other-list-1"><a href="<?php echo base_url('news') ?>"
                class="btn-dark-tri hvr-buzz-out"><i class="far fa-newspaper mb-2"></i>
                <p>ข่าวสาร</p>
            </a></div>
        <!-- <div class="other-list other-list-2"><a href="<?php echo base_url('profile') ?>"
												class="btn-dark-tri hvr-buzz-out"><i class="fas fa-donate mb-2"></i>
				<p>คืนยอดเสีย</p>
			</a></div> -->
        <!-- <div class="other-list other-list-1"><a href="<?php echo base_url('play_wheel') ?>"
												class="btn-dark-tri hvr-buzz-out"><i class="fas fa-life-ring mb-2"></i>
				<p>วงล้อพารวย</p>
			</a></div> -->
        <div class="other-list other-list-2"><a href="<?php echo base_url('event') ?>"
                class="btn-dark-tri hvr-buzz-out"><i class="fas fa-award mb-2"></i>
                <p>กิจกรรม</p>
            </a></div>
        <div class="other-list other-list-1"><a class="btn-dark-tri hvr-buzz-out"
                href="<?php echo base_url('history') ?>"><i class="fa fa-list-alt mb-2"></i>
                <p>ประวัติการเงิน</p>
            </a></div>
        <?php if($_SESSION['user']['role'] == roleAdmin() || $_SESSION['user']['role'] == roleSuperAdmin()): ?>
        <div class="other-list other-list-2"><a class="btn-dark-tri hvr-buzz-out"
                href="<?php echo base_url('admin') ?>"><i class="fa fa-list-alt mb-2"></i>
                <p>แอดมิน</p>
            </a></div>
        <?php endif; ?>
        <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
</section>
<div></div>
<?php
$chk_run_notify = false;
if(count($news) > 0){
	if(!isset($_SESSION['alert_notify_date_chk'])){
		$chk_run_notify = true;
		$alert_notify_date = null;
		foreach($news as $new_data){
			try{
				if(!is_null($new_data['updated_at'])){
					if(is_null($alert_notify_date)){
						$alert_notify_date = new DateTime($new_data['updated_at']);
						$alert_notify_date = $alert_notify_date->format('Y-m-d H:i:s');
					}else{
						$alert_notify_date = new DateTime($alert_notify_date);
						$alert_notify_date = $alert_notify_date->format('Y-m-d H:i:s');
						$alert_notify_date_two = new DateTime($new_data['updated_at']);
						$alert_notify_date_two = $alert_notify_date_two->format('Y-m-d H:i:s');
						if (
								strtotime($alert_notify_date_two)>strtotime($alert_notify_date)
						){
							$alert_notify_date = $alert_notify_date_two;
						}
					}
				}
			}catch (Exception $ex){

			}
		}
		$_SESSION['alert_notify_date_chk'] = "EMPTY";
	}else if(isset($_SESSION['alert_notify_date_chk'])){
		$alert_notify_date = $_SESSION['alert_notify_date_chk'];
		foreach($news as $new_data){
			try{
				if(!is_null($new_data['updated_at'])){
					if(is_null($alert_notify_date)){
						$alert_notify_date = new DateTime($new_data['updated_at']);
						$alert_notify_date = $alert_notify_date->format('Y-m-d H:i:s');
					}else{
						$alert_notify_date = new DateTime($alert_notify_date);
						$alert_notify_date = $alert_notify_date->format('Y-m-d H:i:s');
						$alert_notify_date_two = new DateTime($new_data['updated_at']);
						$alert_notify_date_two = $alert_notify_date_two->format('Y-m-d H:i:s');
						if (
								strtotime($alert_notify_date_two)>strtotime($alert_notify_date)
						){
							$chk_run_notify = true;
							$alert_notify_date = $alert_notify_date_two;
						}
					}
				}
			}catch (Exception $ex){
				$_SESSION['alert_notify_date_chk'] = date('Y-m-d H:i:s');
				$alert_notify_date = $_SESSION['alert_notify_date_chk'];
			}
		}

		if(!is_null($alert_notify_date) && !empty($alert_notify_date)){
			$_SESSION['alert_notify_date_chk'] = $alert_notify_date;
		}
	}
}
?>
<?php
if($chk_run_notify):
	//if(true) :
	?>
<div class="modal" id="notifyModal" tabindex="-1" aria-labelledby="exampleModalLabelass" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body" style="">
                <div class="row">
                    <div class="col-12">
                        <h5>&nbsp;&nbsp;<i class="fa fa-bullhorn" style="font-size: 16px !important;"></i>&nbsp;ประกาศ
                        </h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="bd-example">
                            <div id="carouselExampleCaptions" class="carousel slide"
                                style=" background: var(--base-color-main) !important;border-radius: 10px"
                                data-ride="carousel">
                                <ol class="carousel-indicators">
                                    <?php foreach($news as $index => $new): ?>
                                    <li data-target="#carouselExampleCaptions" data-slide-to="<?php echo $index ?>"
                                        class="<?php echo $index == 0 ? "active" : 0 ?>"></li>
                                    <?php endforeach; ?>
                                </ol>
                                <div class="carousel-inner">
                                    <?php foreach($news as $index => $new): ?>
                                    <div class="carousel-item pl-2 pr-2 <?php echo $index == 0 ? "active" : 0 ?>">
                                        <?php if(!empty($new['image_url']) && !is_null($new['image_url']) && $new['status_image_alert'] == "1"): ?>
                                        <?php if(!empty($new['url']) && !is_null($new['url'])): ?>
                                        <a href="<?php echo $new['url']; ?>" target="_blank">
                                            <img style="width: 100%" src="<?php echo $new['image_url']; ?>"
                                                class="d-block w-100" alt="...">
                                        </a>
                                        <?php else: ?>
                                        <img style="width: 100%" src="<?php echo $new['image_url']; ?>"
                                            class="d-block w-100" alt="...">
                                        <?php endif; ?>
                                        <?php else: ?>

                                        <?php endif; ?>
                                        <div class="row">
                                            <div class="col-12 text-center">
                                                <h4 class="text-white"><?php echo $new['name']; ?></h4>
                                            </div>
                                        </div>
                                    </div>

                                    <?php endforeach; ?>
                                </div>
                                <a class="carousel-control-prev" href="#carouselExampleCaptions" role="button"
                                    data-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <a class="carousel-control-next" href="#carouselExampleCaptions" role="button"
                                    data-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-3 text-right">
                        <a href="#" data-dismiss="modal" data-target="#notifyModal" class="btn btn-success">ปิด</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    function alignModal() {
        var modalDialog = $(this).find(".modal-dialog");
        modalDialog.css("margin-top", Math.max(0, ($(window).height() - modalDialog.height()) / 2));
    }
    $(".modal").on("shown.bs.modal", alignModal);
    $(window).on("resize", function() {
        $(".modal:visible").each(alignModal);
    });
    $("#notifyModal").modal('show')
});
</script>
<?php endif; ?>
<loading :active.sync="pre_loader" :can-cancel="false" :width="80" :height="60" :opacity="0.2" color="#fff"
    :is-full-page="true"></loading>
<script src="<?php echo base_url('assets/scripts/dashboard.js?').date('Y-m-d') ?>"></script>