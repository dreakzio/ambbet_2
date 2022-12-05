$(window).on('load', function() {
	//$('#preloader').addClass('preloader-hide');
})

$(document).ready(function() {

	function alignModal() {
		var modalDialog = $(this).find(".modal-dialog");
		modalDialog.css("margin-top", Math.max(0, ($(window).height() - modalDialog.height()) / 2));
	}
	$(".modal").on("shown.bs.modal", alignModal);
	$(window).on("resize", function() {
		$(".modal:visible").each(alignModal);
	});
	$('[data-toggle="popover"]').popover()
	$('[data-toggle="tooltip"]').tooltip()

	$(".icon-bank-box img").click(function(){
		$('.icon-bank-box img').removeClass('active')
		$(this).addClass('active')
	});

	$(document).on('click','.menu-bar-mobile',function(){
		$('.menu-bar-mobile').removeClass('active')
		$(this).addClass('active')
	})
});

