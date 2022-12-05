const BaseURL = $('#base_url').val();

function sweetAlert2(type, text) {
	Swal.fire({
		type: type,
		// title: 'แจ้งเตือน',
		html: text,
		confirmButtonText: 'ตกลง',
		confirmButtonColor: 'red',
	});
}
Vue.use(VueLoading);
Vue.use(VueNumeric.default);
Vue.component('loading', VueLoading);
var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
if (isMobile) {
	isMobile = true;
} else {
	isMobile = false;
}
var browser = (function() {
	var test = function(regexp) {return regexp.test(window.navigator.userAgent)}
	switch (true) {
		case test(/edg/i): return "Microsoft Edge";
		case test(/trident/i): return "Microsoft Internet Explorer";
		case test(/firefox|fxios/i): return "Mozilla Firefox";
		case test(/opr\//i): return "Opera";
		case test(/ucbrowser/i): return "UC Browser";
		case test(/samsungbrowser/i): return "Samsung Browser";
		case test(/chrome|chromium|crios/i): return "Google Chrome";
		case test(/safari/i): return "Apple Safari";
		default: return "Other";
	}
})();
function openInNewTab(href) {
	if(browser == "Apple Safari"){
		window.location.href = href;
	}else{
		window.location.href = href;
		/*Object.assign(document.createElement('a'), {
			target: '_blank',
			href,
		}).click();*/
	}
}
function getOS() {
	var userAgent = window.navigator.userAgent,
		platform = window.navigator.platform,
		macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K'],
		windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE'],
		iosPlatforms = ['iPhone', 'iPad', 'iPod'],
		os = 'DSK';

	if (macosPlatforms.indexOf(platform) !== -1) {
		os = 'DSK';
	} else if (iosPlatforms.indexOf(platform) !== -1) {
		os = 'IOS';
	} else if (windowsPlatforms.indexOf(platform) !== -1) {
		os = 'DSK';
	} else if (/Android/.test(userAgent)) {
		os = 'AND';
	} else if (!os && /Linux/.test(platform)) {
		os = 'DSK';
	}
	return os;
}

