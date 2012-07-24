
	<div id="fbsend">
		<a onclick='javascript:loginFB();' onlogin="Log.info('onlogin callback')">
			Deel dit op Facebook
		</a>
	</div>

	<!-- Facebook -->
		<div id="fb-root"></div>
		<script>
			window.fbAsyncInit = function() {
				FB.init({
					appId 	: 'REPLACE' //	https://developers.facebook.com/apps
					status	: true,
					cookie	: true,
					xfbml	: true,
					oauth	: true
				});
			};
			(function() {
				var e = document.createElement('script');
				e.src = document.location.protocol + '//connect.facebook.net/nl_NL/all.js';
				e.async = true;
				document.getElementById('fb-root').appendChild(e);
			}());
		</script>

