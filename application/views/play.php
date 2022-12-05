
<html>
<head>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;800&display=swap" rel="stylesheet">
  <script src="<?php echo base_url('assets/js/jquery-3.5.1.min.js')?>"></script>
  <script src="<?php echo base_url('assets/plugins/bootstrap/dist/js/bootstrap.min.js')?>"></script>
  <script src="<?php echo base_url('assets/plugins/sweetalert2/dist/sweetalert2.all.js')?>"></script>
</head>
<body>
	<script>
	Swal.fire({
			text: "กำลังเข้าสู่ <?php echo $title ?>" ,
			showConfirmButton: false,
			allowOutsideClick: false,
			allowEscapeKey: false,
			confirmButtonText: '',
		}),
		Swal.showLoading();
	</script>
	<?php
		if(empty($url)){
			$url = base_url('/');
			redirect($url);
		}else{
			echo '<script>location.replace("'.$url.'")</script>';
		}
	 ?>
</body>
</html>
