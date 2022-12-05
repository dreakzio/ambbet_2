<section class="register">
	<a style="
    padding-top: 6px;
    padding-bottom: 6px;
    padding-left: 12px;
    padding-right: 12px;
" href="<?php echo base_url('game') ?>" class="float-right btn btn-outline-red btn-md">
		<i class="fa fa-play"></i> เล่นเกมส์</a>
	<span class="mb-4 mt-1" style="font-size: 25px"><i class="fa fa-gamepad"></i>&nbsp;<?php echo empty($game_title) ? "" : " ".$game_title; ?><?php echo empty($game_name) ? "" : " - ".$game_name; ?></span>
	<hr style="margin-top: 15px">
</section>
<script>
	Swal.fire({
		text: "กำลังเข้าสู่เกมส์<?php echo empty($game_title) ? "" : " ".$game_title; ?>..." ,
		showConfirmButton: false,
		allowOutsideClick: false,
		allowEscapeKey: false,
		confirmButtonText: '',
	}),
			Swal.showLoading();
	<?php if($type == "lobby"): ?>
		openGameById('<?php echo $game_code; ?>','<?php echo $game_code_id; ?>');
	<?php else: ?>
		openGameOnce('<?php echo $game_code; ?>');
	<?php endif; ?>
	function openGameOnce(game_code){
		if(true){
			let _device_type = getOS();
			axios.get(BaseURL + "play/"+game_code+"?device_type="+_device_type+"&isMobile="+isMobile)
					.then(function (response) {
						Swal.close();
						if (response.data.result && response.data.url != '') {
							openInNewTab(BaseURL+"home/play_game_open/?token="+response.data.url);
						} else {
							if(response.data.res && response.data.res.toString().indexOf("Game is under maintenance") >= 0){
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'เกมส์อยู่ระหว่างการบำรุงรักษา',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
								.then((result) => {
									//location.replace(BaseURL + "dashboard")
								});
								//sweetAlert2('error', 'เกมส์อยู่ระหว่างการบำรุงรักษา');
							}else if(response.data.res && response.data.res.toString().indexOf("Server is under maintenance") >= 0){
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'เซิร์ฟเวอร์อยู่ระหว่างการบำรุงรักษา',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'เซิร์ฟเวอร์อยู่ระหว่างการบำรุงรักษา');
							}else if(response.data.res && response.data.res.toString().indexOf("System is under maintenance") >= 0){
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'ระบบอยู่ระหว่างการบำรุงรักษา',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'ระบบอยู่ระหว่างการบำรุงรักษา');
							}else if(response.data.res && response.data.res.toString().indexOf("gameProduct") >= 0 && response.data.res.toString().indexOf("not found") >= 0){
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'เกมส์อยู่ระหว่างการบำรุงรักษา',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'ระบบอยู่ระหว่างการบำรุงรักษา');
							}else{
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'เข้าเกมส์ไม่สำเร็จ',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'เข้าเกมส์ไม่สำเร็จ');
							}
						}
					}).catch(err=>{
				Swal.fire({
					type: 'error',
					title: 'แจ้งเตือน',
					text: 'เข้าเกมส์ไม่สำเร็จ',
					confirmButtonText: 'ตกลง',
					confirmButtonColor: 'red',
				})
						.then((result) => {
							//location.replace(BaseURL + "dashboard")
						});
				//sweetAlert2('error', 'เข้าเกมส์ไม่สำเร็จ');
			});
		}
	}
	function openGameById(game_code,$game_code_id){
		if(true){
			let _device_type = getOS();
			axios.get(BaseURL + "play/"+game_code+"/"+$game_code_id+"?device_type="+_device_type+"&isMobile="+isMobile)
					.then(function (response) {
						Swal.close();
						if (response.data.result && response.data.url != '') {
							openInNewTab(BaseURL+"home/play_game_open/?token="+response.data.url);
						} else {
							if(response.data.res && response.data.res.toString().indexOf("Game is under maintenance") >= 0){
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'เกมส์อยู่ระหว่างการบำรุงรักษา',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'เกมส์อยู่ระหว่างการบำรุงรักษา');
							}else if(response.data.res && response.data.res.toString().indexOf("Server is under maintenance") >= 0){
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'เซิร์ฟเวอร์อยู่ระหว่างการบำรุงรักษา',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'เซิร์ฟเวอร์อยู่ระหว่างการบำรุงรักษา');
							}else if(response.data.res && response.data.res.toString().indexOf("System is under maintenance") >= 0){
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'ระบบอยู่ระหว่างการบำรุงรักษา',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'ระบบอยู่ระหว่างการบำรุงรักษา');
							}else if(response.data.res && response.data.res.toString().indexOf("gameProduct") >= 0 && response.data.res.toString().indexOf("not found") >= 0){
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'เกมส์อยู่ระหว่างการบำรุงรักษา',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'ระบบอยู่ระหว่างการบำรุงรักษา');
							}else{
								Swal.fire({
									type: 'error',
									title: 'แจ้งเตือน',
									text: 'เข้าเกมส์ไม่สำเร็จ',
									confirmButtonText: 'ตกลง',
									confirmButtonColor: 'red',
								})
										.then((result) => {
											//location.replace(BaseURL + "dashboard")
										});
								//sweetAlert2('error', 'เข้าเกมส์ไม่สำเร็จ');
							}
						}
					}).catch(err=>{
				Swal.fire({
					type: 'error',
					title: 'แจ้งเตือน',
					text: 'เข้าเกมส์ไม่สำเร็จ',
					confirmButtonText: 'ตกลง',
					confirmButtonColor: 'red',
				})
						.then((result) => {
							//location.replace(BaseURL + "dashboard")
						});
				//sweetAlert2('error', 'เข้าเกมส์ไม่สำเร็จ');
			});
		}
	}
</script>
