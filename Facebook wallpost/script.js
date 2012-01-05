
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
		keuze1: '',
		keuze2: '',
		keuze3: '',
		wallPost:	'',
		init: function() {
			wallpost.keuze1 = $('#top3selection div.slot1').attr('keuze');
			wallpost.keuze2 = $('#top3selection div.slot2').attr('keuze');
			wallpost.keuze3 = $('#top3selection div.slot3').attr('keuze');
			wallpost.keuze1img = $('#top3selection div.slot1').attr('excerpt');
		},
		postToWall: function(fbid) {
			wallpost.wallPost = {
				message:		'',
				link:			'http://www.fairminds.nl/',
				name:			'Fairminds',
				picture:		'http://www.fairminds.nl/wp-content/themes/bandbreed/images/producten/product_'+ wallpost.keuze1img +'.jpg',
				message:		'Hee! Dit is mijn fairlanglijstje voor de feestdagen. Ben benieuwd naar die van jou. Maak, deel en win jouw fairlanglijstje op www.fairminds.nl.',
				caption:		'Dit is mijn top3.',
				description:	'Keuze 1: '+ wallpost.keuze1 +' - Keuze 2: '+ wallpost.keuze2 +' - Keuze 3: '+ wallpost.keuze3 +''
			}
			FB.api('/'+fbid+'/feed', 'post', wallpost.wallPost, function(response) {
				if (!response || response.error) {
					alert('Er is iets mis gegaan met het posten op je wall... sorry :(');
				} else {
					window.location = '/save/?keuze1='+wallpost.keuze1+'&keuze2='+wallpost.keuze2+'&keuze3='+wallpost.keuze3;
				}
			});
		}
	}
	
