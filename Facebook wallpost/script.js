//	Facebook Wallpost

	function loginFB() {
		FB.login(function(response) {	// user is logged in
			if (response.authResponse) {	// user has authorised app
				FB.api('/me', function(response) {
					wallpost.init();
					wallpost.postToWall(response.id);
				});
			} else {								
				alert('Je bent nog niet op Facebook ingelogd!');
			}
		}, {
			scope:'publish_stream,email'
		});
	}

	
	var wallpost = {
		init: function() {
		},
		postToWall: function(fbid) {
			wallpost.wallPost = {
				link:			'http://www.link.nl/',
				name:			'dit is de name',
				picture:		'http://client.bandbreed.nl/enexis/images/logo_enexis.png',
				message:		'dit is de message',
				caption:		'dit is de caption',
				description:	'dit is de description'
			}
			FB.api('/'+fbid+'/feed', 'post', wallpost.wallPost, function(response) {
				if (!response || response.error) {
					alert('Er is iets mis gegaan met het posten op je wall... excuus.');
				} else {
					// window.location = '/save/';
				}
			});
		}
	}