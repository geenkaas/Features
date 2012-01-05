<?PHP if (!defined('PROJECT')) { trigger_error('No direct script access allowed', E_USER_ERROR); }
/** googlemap.php
*	
*	This is the class to be used to generate javascript for a Google Maps map
*	
*	Copyright (c) 2008 Granville, All Rights Reserved.
*	http://www.granville.nl
*
*/

/**
 * @package: 	Google Map Class
 * @author: 	Mitchelle C. Pascual (mitch.pascual at gmail dot com)
 *				http://ordinarywebguy.wordpress.com
 * @date: 		March 27, 2007
 * @warning:	Use this class at your own risk. Not recommended to set more than 20 addresses at a time.
 */
 
 
class GoogleMap extends Object {


//------------------------------------------------------------------------------------------------------------------
// FIELD DEFINITIONS
//------------------------------------------------------------------------------------------------------------------

	/**
	 * @desc: 	Google Map Key
	 * @type: 	string
	 * @access: private
	 */
	private $mapKey;

	/**
	 * @desc: 	Map Place Holder & Sizes
	 * @type: 	int
	 * @access:	private
	 */
	protected $mapId = 'map';
	protected $mapWidth;
	protected $mapHeight;

	/**
	 * @desc: 	Map Sensor usage
	 * @type: 	string
	 * @access:	private
	 */
	private $sensor = 'false';
	
	/**
	 * @desc: 	Map Zoom Value
	 * @type: 	int
	 * @access:	private
	 */
	private $mapZoom;
	
	/**
	 * @desc: 	Address Data Array Holder
	 * @type: 	array
	 * @access: private
	 */
	private $addressArr = array();

	/**
	 * @desc: 	Map type
	 * @type: 	string
	 * @access: private
	 */
	private $mapType;

	/**
	 * @desc: 	Center point
	 * @type: 	array
	 * @access: private
	 */
	private $center = array();
	
	/**
	 * @desc: 	Var Holder of Marker Icon Color Scheme
	 * @type: 	string
	 * @access: private
	 */
	private $defColor;
	
	/**
	 * @desc: 	Arrays of Marker Icon Color Scheme
	 * @type: 	array
	 * @access: private
	 */
	private $iconColor = array(
		'PACIFICA'		=>'pacifica',
		'YOSEMITE'		=>'yosemite',
		'MOAB'			=>'moab',
		'GRANITE_PINE'	=>'granitepine',
		'DESERT_SPICE'	=>'desertspice',
		'CABO_SUNSET'	=>'cabosunset',
		'TAHITI_SEA'	=>'tahitisea',
		'POPPY'			=>'poppy',
		'NAUTICA'		=>'nautica',
		'DEEP_JUNGLE'	=>'deepjungle',
		'SLATE'			=>'slate'
	);

	/**
	 * @desc: 	Var Holder of Marker Icon
	 * @type: 	string
	 * @acess: 	private
	 */
	private $defStyle;
	
	/**
	 * @desc: 	Arrays of Marker Icon Scheme
	 * @type: 	array
	 * @access: private
	 */
	private $iconStyle = array(
		'FLAG'		=> array (
			'DIR'				=>'flag', 
			'ICON_W'			=>31, 
			'ICON_H'			=>35, 
			'ICON_ANCHR_W'		=>4, 
			'ICON_ANCHR_H'		=>27, 
			'INFO_WIN_ANCHR_W'	=>8, 
			'INFO_WIN_ANCHR_H'	=>3
		),
						
		'GT_FLAT'	=> array (
			'DIR'				=>'traditionalflat', 
			'ICON_W'			=>34, 
			'ICON_H'			=>35, 
			'ICON_ANCHR_W'		=>9, 
			'ICON_ANCHR_H'		=>23, 
			'INFO_WIN_ANCHR_W'	=>19, 
			'INFO_WIN_ANCHR_H'	=>0
		),
						
		'GT_PILLOW'	=> array (
			'DIR'				=>'traditionalpillow', 
			'ICON_W'			=>34, 
			'ICON_H'			=>35, 
			'ICON_ANCHR_W'		=>9, 
			'ICON_ANCHR_H'		=>23, 
			'INFO_WIN_ANCHR_W'	=>19, 
			'INFO_WIN_ANCHR_H'	=>0
		),
						
		'HOUSE'		=> array (
			'DIR'				=>'house', 
			'ICON_W'			=>24, 
			'ICON_H'			=>14, 
			'ICON_ANCHR_W'		=>9, 
			'ICON_ANCHR_H'		=>13, 
			'INFO_WIN_ANCHR_W'	=>9, 
			'INFO_WIN_ANCHR_H'	=>0
		),
						
		'PIN'		=> array (
			'DIR'				=>'pin', 
			'ICON_W'			=>31, 
			'ICON_H'			=>24, 
			'ICON_ANCHR_W'		=>17, 
			'ICON_ANCHR_H'		=>22, 
			'INFO_WIN_ANCHR_W'	=>17, 
			'INFO_WIN_ANCHR_H'	=>0
		),
						
		'PUSH_PIN'	=> array (
			'DIR'				=>'pushpin', 
			'ICON_W'			=>40, 
			'ICON_H'			=>41, 
			'ICON_ANCHR_W'		=>7, 
			'ICON_ANCHR_H'		=>38, 
			'INFO_WIN_ANCHR_W'	=>26, 
			'INFO_WIN_ANCHR_H'	=>1
		),
						
		'STAR'		=> array (
			'DIR'				=>'star', 
			'ICON_W'			=>29, 
			'ICON_H'			=>39, 
			'ICON_ANCHR_W'		=>15, 
			'ICON_ANCHR_H'		=>15, 
			'INFO_WIN_ANCHR_W'	=>19, 
			'INFO_WIN_ANCHR_H'	=>7
		)
	);

	/**
	 * @desc: Var Holder of Map Control 
	 * @type: string
	 * @access: private
	 */
	private $defControl = 'NONE';
	private $defControlPosition;
	private $defControlOffsetX;
	private $defControlOffsetY;
	

	/**
	 * @desc: 	Arrays of Map Control Scheme
	 * @type: 	array
	 * @access: private
	 */
	private $control = array (
		'NONE',
		'SMALL_PAN_ZOOM',
		'LARGE_PAN_ZOOM',
		'SMALL_ZOOM'
	);

	/**
	 * @desc: 	Enable/Disable Map Continuous Zooming
	 * @type: 	boolean
	 * @acess: 	public
	 */
	public $continuousZoom = FALSE;

	/**
	 * @desc: 	
	 * @type: 	booleanEnable/Disable Map Double Click Zooming
	 * @access: public
	 */
	public $doubleClickZoom = TRUE;

	/**
	 * @desc: 	Enable/Disable Map Scale (MI/KM)
	 * @type: 	boolean
	 * @access: public
	 */
	public $scale = FALSE;

	/**
	 * @desc: 	Enable/Disable Map Inset
	 * @type: 	boolean
	 * @acess: 	public
	 */
	public $inset = FALSE;

	/**
	 * @desc: 	Enable/Disable Map Type (Map/Satellite/Hybrid)
	 * @type: 	boolean
	 * @acess: 	public
	 */
	public $mapTypeControl = TRUE;

	/**
	 * @desc: 	Info window width
	 * @type: 	int
	 * @access: private
	 */
	private $infoWindowWidth;


	
//------------------------------------------------------------------------------------------------------------------
// CONSTRUCTOR DEFINITION
//------------------------------------------------------------------------------------------------------------------

	/** __construct()
	 * @desc:	Constructor
	 * @param: 	string (Google Map Key)
	 * @access: public
	 * @return: void
	 */
	public function __construct($mapKey) {
		$this->mapKey = $mapKey;
		$this->set_map_width();
		$this->set_map_height();
		$this->set_map_zoom();
		$this->set_marker_icon_color();
		$this->set_marker_icon_style();
		$this->set_map_control();
	} # end function



//------------------------------------------------------------------------------------------------------------------
// SET METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------


	/** set_map_type()
	 * @desc: 	Set the map type
	 * @param: 	string 
	 * @access: public
	 * @return: void
	 */
	public function set_map_type($type = 'G_NORMAL_MAP') {
		$this->mapType = $type;
	} # end function

	/** set_center()
	 * @desc: 	Set the center point/address
	 * @param: 	string 
	 * @param: 	string [point or address]
	 * @access: public
	 * @return: void
	 */
	public function set_center($center, $type = 'point') {
		$this->center = array(
			'type' => $type,
			'center' => $center
		);
	} # end function
	
	/** add_address()
	 * @desc: 	Add Address(es)
	 * @param: 	string 
	 * @access: public
	 * @return: void
	 */
	public function add_address($address, $description = false) {
		$this->addressArr[] = array(
			'type' => 'address',
			'address' => $address,
			'description' => $description
		);
	} # end function

	/** add_point()
	 * @desc: 	Add Point(s)
	 * @param: 	string 
	 * @access: public
	 * @return: void
	 */
	public function add_point($point, $description = false) {
		$this->addressArr[] = array(
			'type' => 'point',
			'address' => $point,
			'description' => $description
		);
	} # end function

	/**
	 * @desc: 	Set Map Width
	 * @param: 	int 
	 * @access:	public
	 * @return: void
	 */
	public function set_map_width($width=300) {
		$this->mapWidth = $width;
	} # end function

	/**
	 * @desc: 	Set Map Zoom
	 * @param: 	int
	 * @access:	public
	 * @return:	void
	 */
	public function set_map_zoom($zoom=13) {
		$this->mapZoom = $zoom;
	} # end function

	/**
	 * @desc: 	Set Map Height
	 * @param: 	int
	 * @access:	public
	 * @return:	void
	 */
	public function set_map_height($height=300) {
		$this->mapHeight = $height;
	} # end function

	/**
	 * @desc: 	Set Marker Icon Color Scheme
	 * @param: 	string [options('PACIFICA','YOSEMITE','MOAB','GRANITE_PINE','DESERT_SPICE','CABO_SUNSET','TAHITI_SEA','POPPY','NAUTICA','SLATE')]
	 * @access:	public
	 * @return: void
	 */
	public function set_marker_icon_color($colorScheme="PACIFICA") {
		$this->defColor = $colorScheme;
	} # end function

	/**
	 * @desc: 	Set Marker Icon Style Scheme
	 * @param: 	string [options('FLAG','GT_FLAT','GT_PILLOW','HOUSE','PIN','PUSH_PIN','STAR')]
	 * @access:	public
	 * @return: void
	 */
	public function set_marker_icon_style($style="GT_FLAT") {
		$this->defStyle = $style;
	} # end function

	/**
	 * @desc: 	Set Map Control
	 * @param: 	string [options('NONE','SMALL_PAN_ZOOM','LARGE_PAN_ZOOM','SMALL_ZOOM')]
	 * @param: 	string [options('TOP_LEFT','TOP_RIGHT','BOTTOM_LEFT','BOTTOM_RIGHT')]
	 * @param: 	int
	 * @param: 	int
	 * @access:	public
	 * @return: void
	 */
	public function set_map_control($control="SMALL_PAN_ZOOM", $position='TOP_LEFT', $offsetX=5, $offsetY=5) {
		$this->defControl = $control;
		$this->defControlPosition = 'G_ANCHOR_'.$position;
		$this->defControlOffsetX = $offsetX;
		$this->defControlOffsetY = $offsetY;
	} # end function

	/**
	 * @desc: 	Set Map Info window Width
	 * @param: 	int
	 * @access:	public
	 * @return:	void
	 */
	public function set_info_window_width($width=200) {
		$this->infoWindowWidth = $width;
	} # end function


//------------------------------------------------------------------------------------------------------------------
// DISPLAY METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------

	/**
	 * @desc: 	Generate JS Code 
	 * @param: 	string 
	 * @access: public
	 * @return: string
	 */
	public function display_init_javascript() {
        
		$ret = NULL;
		// show error if misconfigured
		$is_error = $this->check_configuration();
		if ($is_error) { 
			// Notify error
			trigger_error($is_error, E_USER_WARNING); 
		} else {		
			$cnt_add = count($this->addressArr);
			
			$color = $this->iconColor[$this->defColor];
			$dir = $this->iconStyle[$this->defStyle]['DIR'];
	
			$icon_w  = $this->iconStyle[$this->defStyle]['ICON_W'];
			$icon_h  = $this->iconStyle[$this->defStyle]['ICON_H'];
	
			$icon_anchr_w  = $this->iconStyle[$this->defStyle]['ICON_ANCHR_W'];
			$icon_anchr_h  = $this->iconStyle[$this->defStyle]['ICON_ANCHR_H'];
	
			$info_win_anchr_w  = $this->iconStyle[$this->defStyle]['INFO_WIN_ANCHR_W'];
			$info_win_anchr_h  = $this->iconStyle[$this->defStyle]['INFO_WIN_ANCHR_H'];
			
			// start of JS SCRIPT		
            $ret .= '
				<script type="text/javascript">
				<!--
					var gmarkers = [];
					var points = [];
					var descriptions = [];
			' . "\n";
			
			// Browser check
			$ret .= 'if(GBrowserIsCompatible()) {' . "\n";
			$ret .= '	var map = new GMap2(document.getElementById("' . $this->mapId . '"));' . "\n";
	
			// handle map continuous zooming
			$ret .= ($this->continuousZoom==TRUE)? '	map.enableContinuousZoom();' . "\n":'';
	
			// handle map double click zooming
			$ret .= ($this->doubleClickZoom==TRUE)?'	map.enableDoubleClickZoom();' . "\n" :'map.disableDoubleClickZoom();' . "\n";
	
			// handle map controls
			$mapCtrl = "";
			$mapCtrlPosition = 'new GControlPosition('.$this->defControlPosition.', new GSize('.$this->defControlOffsetX.','.$this->defControlOffsetY.'))';
			switch ($this->defControl) {
				case 'NONE':
					$mapCtrl = 'NONE';
					break;
					
				case 'SMALL_PAN_ZOOM':
					$mapCtrl = 'new GSmallMapControl(), '.$mapCtrlPosition;
					break;
					
				case 'LARGE_PAN_ZOOM':
					$mapCtrl = 'new GLargeMapControl(), '.$mapCtrlPosition;
					break;
	
				case 'SMALL_ZOOM':
					$mapCtrl = 'new GSmallZoomControl(), '.$mapCtrlPosition;
					break;
				
				default;
					break;
			
			} # end switch
			$ret .= ($mapCtrl != 'NONE') ? '	map.addControl(' . $mapCtrl . ');' . "\n":'';
			
			# handle map scale (mi/km)
			$ret .= ($this->scale==TRUE) ? '	map.addControl(new GScaleControl());' . "\n":'';
	
			# handle map type (map/satellite/hybrid)
			$ret .= ($this->mapTypeControl==TRUE) ? '	map.addControl(new GMapTypeControl());' . "\n":'';
	
			# handle map inset
			$ret .= ($this->inset==TRUE) ? '	map.addControl(new GOverviewMapControl());' . "\n":'';
	
			# set map type
			$ret .= ($this->mapType) ? '	map.setMapType(' . $this->mapType . ');' . "\n":'';			
			
			# Geocoder
			$ret .= '	var geocoder = new GClientGeocoder();' . "\n";	
								
			# Map center
			if ($this->center['type'] == 'point') {
				$ret .= '	
					// Set map center
					map.setCenter(new GLatLng('.$this->center['center'].'), '.$this->mapZoom.');
				';
			} else {
				$ret .= '
					// Set map center
					geocoder.getLatLng(
						"'.addslashes($this->center['center']).'",
						function(point) {
							if (!point) {
								alert("Map center not found");
							} else {
								map.setCenter(point, '.$this->mapZoom.');
							}
						}
					);				
				';
			
			}

			# Configure icon
			$ret .= "	var icon = new GIcon(); \n";
			$ret .= "	icon.image = 'http://google.webassist.com/google/markers/$dir/$color.png'; \n";
			$ret .= "	icon.shadow = 'http://google.webassist.com/google/markers/$dir/shadow.png'; \n";
			$ret .= "	icon.iconSize = new GSize($icon_w,$icon_h); \n";
			$ret .= "	icon.shadowSize = new GSize($icon_w,$icon_h); \n";
			$ret .= "	icon.iconAnchor = new GPoint($icon_anchr_w,$icon_anchr_h); \n";
			$ret .= "	icon.infoWindowAnchor = new GPoint($info_win_anchr_w,$info_win_anchr_h); \n";
			$ret .= "	icon.printImage = 'http://google.webassist.com/google/markers/$dir/$color.gif'; \n";
			$ret .= "	icon.mozPrintImage = 'http://google.webassist.com/google/markers/$dir/{$color}_mozprint.png'; \n";
			$ret .= "	icon.printShadow = 'http://google.webassist.com/google/markers/$dir/shadow.gif'; \n";
			$ret .= "	icon.transparent = 'http://google.webassist.com/google/markers/$dir/{$color}_transparent.png'; \n\n";

			# loop set address(es)
			$i = NULL;
			$infoWindowOptions = ($this->infoWindowWidth) ? ',{maxWidth: '.intval($this->infoWindowWidth).'}' : '';
			foreach ($this->addressArr as $place) {
				
				$i++;
				
				if ($place['type'] == 'point') {
					
					$ret .= '
						// New point marker	
						point = new GLatLng('.addslashes($place['address']).');
						points['.$i.'] = point;
						var marker'.$i.' = new GMarker(point, icon);
						GEvent.addListener(marker'.$i.', "click", function() {
							marker'.$i.'.openInfoWindowHtml(\''.addcslashes($place['description'], '/\'').'\''.$infoWindowOptions.');
						});
						
						map.addOverlay(marker'.$i.');
						gmarkers['.$i.'] = marker'.$i.';
						descriptions['.$i.'] = \''.addcslashes($place['description'], '/\'').'\';
					
					';							
				
				} else {
					
					$ret .= '	
						// New address marker
						geocoder.getLatLng (
							"'.addslashes($place['address']).'", 
							function(point) {
								if(point) {
									points['.$i.'] = point;
									var marker'.$i.' = new GMarker(point, icon);
									GEvent.addListener(marker'.$i.', "click", function() {
										marker'.$i.'.openInfoWindowHtml("'.addcslashes($place['description'], '/\'').'"'.$infoWindowOptions.');
									});
									
									map.addOverlay(marker'.$i.');
									gmarkers['.$i.'] = marker'.$i.';
									descriptions['.$i.'] = "'.addcslashes($place['description'], '/\'').'";
								}
							}
						); // end geocoder.getLatLng
					
					';
					
				}
			
			} # end for
			
			$ret .= '} // end if' . "\n\n";
			
			$ret .= '
				function sideClick(i) {
					if (gmarkers[i]) {
						gmarkers[i].openInfoWindowHtml(descriptions[i]'.$infoWindowOptions.');
						map.setCenter(points[i],' . $this->mapZoom . ');
					}
				} //end function
				
				function findFormAddress(address, targetfield) {
					
				  if (geocoder) {
					geocoder.getLatLng(
					  address,
					  function(latlng) {
						if (!latlng) {
						  alert(address + " not found, please use the map to locate your property. Browse to the location and \"double click\" on the spot.");
				
						} else {

							// Remove old marker
							map.clearOverlays();
							
							map.setCenter(latlng, 16);
							var marker = new GMarker(latlng);
							map.addOverlay(marker);
						
							// Fill in form
							coordinates = latlng.toString();
							coordinates = coordinates.replace("(", "");
							coordinates = coordinates.replace(")", "");
							document.getElementById(targetfield).value = coordinates;
						}
						
					  }
					);
				  }

				// Prepare doubleclick action
				GEvent.addListener(map,"dblclick", function(overlay,latlng) {     
				  if (latlng) {
					
					// Remove old marker
					map.clearOverlays();
					
					// Add new marker
					var marker = new GMarker(latlng);
					map.addOverlay(marker);
					
					// Fill in form
					coordinates = latlng.toString();
					coordinates = coordinates.replace("(", "");
					coordinates = coordinates.replace(")", "");
					document.getElementById(targetfield).value = coordinates;
				  }
				});				  
				  
				}
				
			' . "\n";

			$ret .= '
				-->
				</script>
			' . "\n";
		} # end if

		return $ret;
	} # end function

	/**
	 * @desc: 	Generate JS for Map Key (static)
	 * @access: public
	 * @return: string
	 */
	public function display_google_maps_key() {
		return '<script type="text/javascript" src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $this->mapKey . '&amp;sensor=' . $this->sensor . '"></script>' . "\n";	
	} # end function

	/**
	 * @desc: 	Generate Links for Multiple Addresses (static)
	 * @access: public
	 * @return: string
	 */
	public function display_side_click() {
		$ret = "";
		$loop = count($this->addressArr);
		for ($i=1; $i<=$loop; $i++) {
			$ret .=	"<a href=\"javascript:void($i);\" onclick=\"javascript:sideClick($i);\">{$i}</a><br />\n";
		} # end for

		return $ret;
	} # end function

	/**
	 * @desc: 	Generate Map Holder/Container (static)
	 * @access: public
	 * @return: string
	 */
	public function display_map_holder() {
		return '
			<div id="' . $this->mapId . '" style="width:'.$this->mapWidth.'px; height:'.$this->mapHeight.'px;">
			</div>
		';
	} # end function

	/**
	 * @desc: 	Generate Unloading Script for Google Map (static)
	 * @access: public
	 * @return: string
	 */
	public function display_unload_map() {
		return '<script type="text/javascript">window.onunload = function() { GUnload(); }</script>';
	} # end function



//------------------------------------------------------------------------------------------------------------------
// SUPPORTING METHOD DEFINITIONS	
//------------------------------------------------------------------------------------------------------------------

	/**
	 * @desc: 	Check Passed Method Parameters
	 * @access: private
	 * @return: string
	 */
	private function check_configuration() {
		$ret = "";
		# map height and width
		if (!is_numeric($this->mapWidth) || !is_numeric($this->mapHeight)) 
			$ret .= "<h1>INVALID set_map_width() OR set_map_height() PARAMETER</h1><br />\n";		
		
		# map control
		if (!in_array($this->defControl, $this->control)) {
			$ret .= "<h1>INVALID set_map_control() PARAMETER:  $this->defControl</h1><br />\n";
			$ret .= "<b>POSSIBLE PARAMETER VALUES: </b><br />\n";
			foreach ($this->control as $option=>$value) {
				$ret .= "=>'$option' <br />\n";
			} # end foreach
		} # end if

		# color
		if (!array_key_exists($this->defColor, $this->iconColor)) {
			$ret .= "<h1>INVALID set_marker_icon_color() PARAMETER:  $this->defColor</h1><br />\n";
			$ret .= "<b>POSSIBLE PARAMETER VALUES: </b><br />\n";
			foreach ($this->iconColor as $option=>$value) {
				$ret .= "=>'$option' <br />\n";
			} # end foreach
		} # end if
			
		# style
		if (!array_key_exists($this->defStyle, $this->iconStyle)) {
			$ret .= "<h1>INVALID set_marker_icon_style() PARAMETER: $this->defStyle</h1><br />\n";
			$ret .= "<b>POSSIBLE PARAMETER VALUES: </b><br />\n";
			foreach ($this->iconStyle as $option=>$value) {
				$ret .= "=>'$option' <br />\n";
			} # end foreach
		} # end if
	
		return $ret;
	} # end function
	
	
	
} # end class
?>