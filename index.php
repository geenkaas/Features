
	<div id="fbsend">
		<div class="fbfeed pie" id="fb-login">
			<a onclick='javascript:loginFB();' onlogin="Log.info('onlogin callback')">Deel dit op Facebook</a>
		</div>
	</div>

	<script>
		window.fbAsyncInit = function() {
			FB.init({
				appId 	: '266805616685359',
				status	: true, //	check login status
				cookie	: true, //	enable cookies to allow the server to access the session
				xfbml	: true,	//	parse XFBML,
				oauth	: true	//	Open Auth 2.0 since december 13th
			});
		};
		(function() {
			var e = document.createElement('script');
			e.src = document.location.protocol + '//connect.facebook.net/nl_NL/all.js';
			e.async = true;
			document.getElementById('fb-root').appendChild(e);
		}());
	</script>

