<?php

//	+-----------------------------------------------------------------------+
//	|	Name		AnyMedia BBCode											|
//	|	Package		vBulletin 3.5.4											|
//	|	Version		3.0.4													|
//	|	Author		Crist Chsu Moded by Nix									|
//	|	E-Mail		Crist@vBulletin-Chinese.com								|
//	|	Blog		http://www.QuChao.com									|
//	|	Date		2006-6-7												|
//	|	Link		http://www.vbulletin.org/forum/showthread.php?t=106239	|
//	+-----------------------------------------------------------------------+


/**
 * AnyMedia class
 */
class Anymedia
{
	//	{{{	properties

	/**
	 * vBulletin registry object
	 * @var		object	Reference to registry object
	 */
	var $vbulletin = null;

	/**
	 * Media Infomation Array.
	 * @var		array
	 */
	var $_mediaInfo = array(
		'width' => 0,
		'height' => 0,
		'autoplay' => '',
		'extension' => '',
		'loop' => 0,
		'url' => '',
		'link' => '',
		'mime' => '',
		'type' => '',
		'id' => 0,
		'layout' => 0,
		'extra' => array(),
		'htitle' => '',
	);

	/**
	 * Media type list.
	 * @var		array
	 */
	var $_typeList = array(
		// Adobe Flash
		'swf'			=>	array('application/x-shockwave-flash',	'adobe_flash',	'anymediaadobeflash'),
		'spl'			=>	array('application/futuresplash',		'adobe_flash',	'anymediaadobeflash'),
		'flv'			=>	array('application/x-shockwave-flash',	'adobe_flv',	'anymediaadobeflash'),
		'mp3'			=>	array('application/x-shockwave-flash',	'adobe_mp3',	'anymediaadobeflash'),
		// Quick Time
		'mov'			=>	array('video/quicktime',				'quick_time',	'anymediaquicktime'),
		'qt'			=>	array('video/quicktime',				'quick_time',	'anymediaquicktime'),
		'mqv'			=>	array('video/quicktime',				'quick_time',	'anymediaquicktime'),
		'mpeg'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mpg'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm1s'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm1v'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm1a'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm75'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'm15'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mp2'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mpm'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mpv'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'mpa'			=>	array('video/x-mpeg',					'quick_time',	'anymediaquicktime'),
		'flc'			=>	array('video/flc',						'quick_time',	'anymediaquicktime'),
		'fli'			=>	array('video/flc',						'quick_time',	'anymediaquicktime'),
		'cel'			=>	array('video/flc',						'quick_time',	'anymediaquicktime'),
		'rtsp'			=>	array('application/x-rtsp',				'quick_time',	'anymediaquicktime'),
		'rts'			=>	array('application/x-rtsp',				'quick_time',	'anymediaquicktime'),
		'3gp'			=>	array('video/3gpp',						'quick_time',	'anymediaquicktime'),
		'3gpp'			=>	array('video/3gpp',						'quick_time',	'anymediaquicktime'),
		'3g2'			=>	array('video/3gpp2',					'quick_time',	'anymediaquicktime'),
		'3gp2'			=>	array('video/3gpp2',					'quick_time',	'anymediaquicktime'),
		'sdv'			=>	array('video/sd-video',					'quick_time',	'anymediaquicktime'),
		'amc'			=>	array('application/x-mpeg',				'quick_time',	'anymediaquicktime'),
		'mp4'			=>	array('video/mp4',						'quick_time',	'anymediaquicktime'),
		'sdp'			=>	array('application/sdp',				'quick_time',	'anymediaquicktime'),
		// Real Media
		'rm'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'rmvb'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'ra'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'rv'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'ram'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		'smil'			=>	array('audio/x-pn-realaudio-plugin',	'real_media',	'anymediarealplay'),
		// Windows Media
		'mp3'			=>	array('application/x-mplayer2',			'mp3',			'anymediawindowsmedia'),
		'wma'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wav'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'ogg'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'ape'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'mid'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'midi'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'asf'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'asx'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wm'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wmv'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wsx'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wax'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'wvx'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		'avi'			=>	array('application/x-mplayer2',			'windows_media','anymediawindowsmedia'),
		// Adobe PDF
		'pdf'			=>	array('application/pdf',				'adobe_pdf',	'anymediaadobepdf'),
		'fdf'			=>	array('application/vnd.fdf',			'adobe_pdf',	'anymediaadobepdf'),
		'xfdf'			=>	array('application/vnd.adobe.xfdf',		'adobe_pdf',	'anymediaadobepdf'),
		'xdp'			=>	array('application/vnd.adobe.xdp+xml',	'adobe_pdf',	'anymediaadobepdf'),
		'xfd'			=>	array('application/vnd.adobe.xfd+xml',	'adobe_pdf',	'anymediaadobepdf'),
		// Images
		'gif'			=>	array('image/gif',						'image',		'anymediaimage'),
		'jpg'			=>	array('image/pjpeg',					'image',		'anymediaimage'),
		'jpeg'			=>	array('image/pjpeg',					'image',		'anymediaimage'),
		'bmp'			=>	array('image/bmp',						'image',		'anymediaimage'),
		'png'			=>	array('image/x-png',					'image',		'anymediaimage'),
		'xpm'			=>	array('image/xpm',						'image',		'anymediaimage'),
		// P2P
		'torrent'		=>	array('application/x-bittorrent',		'torrent',		'anymediap2p'),
		'emule'			=>	array('',								'emule',		'anymediap2p'),
		'foxy'			=>	array('',								'foxy',			'anymediap2p'),
		'pplive'		=>	array('',								'pplive',		'anymediap2p'),
		// Video Sites
		'google'		=>	array('application/x-shockwave-flash',	'google',		'anymediaflv'),
		'youtube'		=>	array('application/x-shockwave-flash',	'youtube',		'anymediaflv'),
		'vsocial'		=>	array('application/x-shockwave-flash',	'vsocial',		'anymediaflv'),
		'ifilm'			=>	array('application/x-shockwave-flash',	'ifilm',		'anymediaflv'),
		'metacafe'		=>	array('application/x-shockwave-flash',	'metacafe',		'anymediaflv'),
		'dailymotion'	=>	array('application/x-shockwave-flash',	'dailymotion',	'anymediaflv'),
		'currenttv'		=>	array('application/x-shockwave-flash',	'currenttv',	'anymediaflv'),
		'vimeo'			=>	array('application/x-shockwave-flash',	'vimeo',		'anymediaflv'),
		'sharkle'		=>	array('application/x-shockwave-flash',	'sharkle',		'anymediaflv'),
		'vidiac'		=>	array('application/x-shockwave-flash',	'vidiac',		'anymediaflv'),
		'myvideode'		=>	array('application/x-shockwave-flash',	'myvideode',	'anymediaflv'),
		'myspace'		=>	array('application/x-shockwave-flash',	'myspace',		'anymediaflv'),
		'bvids'			=>	array('application/x-shockwave-flash',	'bvids',		'anymediaflv'),
		'filecabi'		=>	array('application/x-shockwave-flash',	'filecabi',		'anymediaflv'),
		'porntube'		=>	array('application/x-shockwave-flash',	'porntube',		'anymediaflv'),
		'stage6'		=>	array('video/divx',						'stage6',		'anymediaflv'),
		'brightcove'	=>	array('application/x-shockwave-flash',	'brightcove',	'anymediaflv'),
		'photobucket'	=>	array('application/x-shockwave-flash',	'photobucket',	'anymediaflv'),
		'liveleak'		=>	array('application/x-shockwave-flash',	'liveleak',		'anymediaflv'),
		'revver'		=>	array('application/x-shockwave-flash',	'revver',		'anymediaflv'),
		'veoh'			=>	array('application/x-shockwave-flash',	'veoh',			'anymediaflv'),
		'putfile'		=>	array('application/x-shockwave-flash',	'putfile',		'anymediaflv'),
		'sevenload'		=>	array('application/x-shockwave-flash',	'sevenload',	'anymediaflv'),
		'gametrailers'	=>	array('application/x-shockwave-flash',	'gametrailers',	'anymediaflv'),
		'spiked'		=>	array('application/x-shockwave-flash',	'spiked',		'anymediaflv'),
		'streetfire'	=>	array('application/x-shockwave-flash',	'streetfire',	'anymediaflv'),
		'yahoo'			=>	array('application/x-shockwave-flash',	'yahoo',		'anymediaflv'),
	);

	//	}}}

	//	{{{	constructor

	/**
	 * Constructor.
	 * @param	object	Reference to registry object
	 * @return	void
	 */
	function Anymedia(& $registry)
	{
		$this->vbulletin =& $registry;
	}

	//	}}}

	//	{{{	destructor

	/**
	 * Destructor.
	 * @return	void
	 */
	function __destruct()
	{
	}

	//	}}}

	//	{{{	fetch()

	/**
	 * Fetch the parsed HTML.
	 * @param	string	Code of the media
	 * @param	string	Options of the media
	 * @return	string	HTML representation of the media
	 */
	function fetch(& $text, & $options)
	{
		$this->processOptions($text, $options);

		if (empty($this->_mediaInfo['extension'])) {
			$this->processExtension($text);
		}

		$this->processMedia();

		return $this->_mediaInfo;
	}

	//	}}}

	//	{{{	attachment()

	/**
	 * Fetch the parsed attachment.
	 * @param	string	Url to the attachment
	 * @param	string	extension of the attachment
	 * @return	string	HTML representation of the media
	 */
	function attachment(& $id, & $extension)
	{
		$this->_mediaInfo['width'] = $this->vbulletin->options['anymediawidth'];
		$this->_mediaInfo['height'] = $this->vbulletin->options['anymediaheight'];
		$this->_mediaInfo['autoplay'] = iif($this->vbulletin->options['anymediaautoplay'], 'true', 'false');
		$this->_mediaInfo['loop'] = $this->vbulletin->options['anymedialoop'];
		$this->_mediaInfo['extension'] = $extension;
		$this->_mediaInfo['url'] = $this->_mediaInfo['link'] = 'attachment.php?'. $this->vbulletin->session->vars['sessionurl'] . 'attachmentid=' . $id;
		$this->_mediaInfo['id'] = vbrand(1, 1000);
		$this->_mediaInfo['download'] = iif(($this->vbulletin->userinfo['permissions']['anymediapermissions'] & $this->vbulletin->bf_ugp_anymediapermissions['candownload']) && $this->vbulletin->options['anymediadownload'], true, false);

		$this->processMedia();

		return $this->_mediaInfo;
	}

	//	}}}
	
	// {{{
	
		function hTittle()
		{
			$hurl = $this->_mediaInfo['link'];
			if (function_exists('curl_init'))
			{
				$handle = curl_init();
				curl_setopt ($handle, CURLOPT_URL, $hurl);
				curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);

				$content = curl_exec($handle);
				curl_close($handle);

			
			} elseif (@fclose(@fopen($hurl, "r")) && ini_get('allow_url_fopen'))
				{
					$content = file($hurl);
					$content = implode("",$content);
			} else {
				return false;
			}
			
			
			if(preg_match('/<title>(.+)<\/title>/i',$content,$m))
			{
				return "$m[1]";
			}
			
		}
		
	/// }}}

	//	{{{	processOptions()

	/**
	 * Set value for basic options.
	 * @param	string	Code of the media
	 * @param	string	Options of the media
	 * @return	string	HTML representation of the media
	 */
	function processOptions(& $text, & $options)
	{
		$optionArray = explode(',', $options);
		$this->_mediaInfo['width'] = iif(isset($optionArray[0]) && !empty($optionArray[0]) && ereg('^[0-9]{1,3}$', $optionArray[0]), $optionArray[0], $this->vbulletin->options['anymediawidth']);
		$this->_mediaInfo['height'] = iif(isset($optionArray[1]) && !empty($optionArray[1]) && ereg('^[0-9]{1,3}$', $optionArray[1]), $optionArray[1], $this->vbulletin->options['anymediaheight']);
		$this->_mediaInfo['autoplay'] = iif(isset($optionArray[2]) && !empty($optionArray[2]), iif(in_array($optionArray[2], array('true', 'yes' ,'1')), 'true', 'false'), iif($this->vbulletin->options['anymediaautoplay'], 'true', 'false'));
		$this->_mediaInfo['loop'] = iif(isset($optionArray[3]) && !empty($optionArray[3]) && ereg('^[0-9]{1,3}$', $optionArray[3]), $optionArray[3], $this->vbulletin->options['anymedialoop']);
		$this->_mediaInfo['extension'] = iif(isset($optionArray[4]) && !empty($optionArray[4]) && array_key_exists(strtolower($optionArray[4]), $this->_typeList), strtolower($optionArray[4]));
		$this->_mediaInfo['url'] = $this->_mediaInfo['link'] = $text;
		$this->_mediaInfo['id'] = vbrand(1, 1000);
		$this->_mediaInfo['htitle'] = $this->hTittle();
		$this->_mediaInfo['download'] = iif(($this->vbulletin->userinfo['permissions']['anymediapermissions'] & $this->vbulletin->bf_ugp_anymediapermissions['candownload']) && $this->vbulletin->options['anymediadownload'], true, false);
	}

	//	}}}

	//	{{{	processExtension()

	/**
	 * Auto-detect the extension of the file.
	 * @param	string	Code of the media
	 * @return	string	HTML representation of the media
	 */
	function processExtension(& $text)
	{
	
		if ((strpos(strtolower($text), 'foxy://') === 0) && array_key_exists('foxy', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'foxy';
		} elseif ((strpos(strtolower($text), 'ed2k://') === 0) && array_key_exists('emule', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'emule';
		} elseif ((strpos(strtolower($text), 'synacast://') === 0) && array_key_exists('pplive', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'pplive';
		} elseif ((strpos(strtolower($text), 'http://video.google.com') === 0) || (strpos(strtolower($text), 'http://video.google.co.uk') === 0)) {
			$this->_mediaInfo['extension'] = 'google';
		} elseif ((strpos(strtolower($text), 'http://www.youtube.com') === 0) || (strpos(strtolower($text), 'http://youtube.com') === 0) || (strpos(strtolower($text), 'http://es.youtube.com') === 0) || (strpos(strtolower($text), 'http://it.youtube.com') === 0) || (strpos(strtolower($text), 'http://uk.youtube.com') === 0)  || (strpos(strtolower($text), 'http://fr.youtube.com') === 0)  || (strpos(strtolower($text), 'http://br.youtube.com') === 0) || (strpos(strtolower($text), 'http://ie.youtube.com') === 0) || (strpos(strtolower($text), 'http://jp.youtube.com') === 0) || (strpos(strtolower($text), 'http://nl.youtube.com') === 0) || (strpos(strtolower($text), 'http://pl.youtube.com') === 0) || (strpos(strtolower($text), 'http://br.youtube.com') === 0)) {
			$this->_mediaInfo['extension'] = 'youtube';
		} elseif ((strpos(strtolower($text), 'http://www.vsocial.com') === 0) || (strpos(strtolower($text), 'http://vsocial.com') === 0) && array_key_exists('vsocial', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'vsocial';
		} elseif ((strpos(strtolower($text), 'http://www.ifilm.com') === 0) || (strpos(strtolower($text), 'http://ifilm.com') === 0) && array_key_exists('ifilm', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'ifilm';
		} elseif ((strpos(strtolower($text), 'http://www.metacafe.com') === 0) || (strpos(strtolower($text), 'http://metacafe.com') === 0) && array_key_exists('metacafe', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'metacafe';
		} elseif ((strpos(strtolower($text), 'http://www.dailymotion.com') === 0) || (strpos(strtolower($text), 'http://dailymotion.com') === 0) && array_key_exists('dailymotion', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'dailymotion';
		} elseif ((strpos(strtolower($text), 'http://www.current.tv') === 0) || (strpos(strtolower($text), 'http://current.tv') === 0) && array_key_exists('currenttv', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'currenttv';
		} elseif ((strpos(strtolower($text), 'http://www.vimeo.com') === 0) || (strpos(strtolower($text), 'http://www.vimeo') === 0) && array_key_exists('vimeo', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'vimeo';
		} elseif ((strpos(strtolower($text), 'http://www.sharkle.com') === 0) || (strpos(strtolower($text), 'http://sharkle.com') === 0) && array_key_exists('sharkle', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'sharkle';
		} elseif ((strpos(strtolower($text), 'http://www.vidiac.com') === 0) || (strpos(strtolower($text), 'http://vidiac.com') === 0) && array_key_exists('freevideoblog', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'vidiac';
		} elseif ((strpos(strtolower($text), 'http://www.myvideo.de') === 0) || (strpos(strtolower($text), 'http://myvideo.de') === 0) && array_key_exists('myvideode', $this->_typeList)) {
			$this->_mediaInfo['extension'] = 'myvideode';
		} elseif ((strpos(strtolower($text), 'http://vids.myspace.com') === 0) || (strpos(strtolower($text), 'http://myspacetv.com') === 0) && array_key_exists('myspace', 			  	$this->_typeList)) {
				$this->_mediaInfo['extension'] = 'myspace';
		} elseif ((strpos(strtolower($text), 'http://www.break.com') === 0) || (strpos(strtolower($text), 'http://break.com') === 0) && array_key_exists('bvids', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'bvids';
		} elseif ((strpos(strtolower($text), 'http://www.filecabi.net') === 0) || (strpos(strtolower($text), 'http://filecabi.net') === 0) && array_key_exists('filecabi', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'filecabi';
		} elseif ((strpos(strtolower($text), 'http://www.pornotube.com') === 0) || (strpos(strtolower($text), 'http://pornotube.com') === 0) && array_key_exists('porntube', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'porntube';
		} elseif ((strpos(strtolower($text), 'http://stage6.divx.com') === 0) || (strpos(strtolower($text), 'http://video.stage6.com') === 0) && array_key_exists('stage6', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'stage6';
		} elseif ((strpos(strtolower($text), 'http://www.brightcove.com') === 0) || (strpos(strtolower($text), 'http://brightcove.com') === 0) && array_key_exists('brightcove', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'brightcove';
		} elseif ((strpos(strtolower($text), 'http://photobucket.com') === 0) || (preg_match('{photobucket.com}', $text, $match) == 1) && array_key_exists('photobucket', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'photobucket';
		} elseif ((strpos(strtolower($text), 'http://www.liveleak.com') === 0) || (strpos(strtolower($text), 'http://liveleak.com') === 0) && array_key_exists('liveleak', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'liveleak';
		} elseif ((strpos(strtolower($text), 'http://one.revver.com') === 0) || (strpos(strtolower($text), 'http://revver.com') === 0) && array_key_exists('revver', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'revver';
		} elseif ((strpos(strtolower($text), 'http://www.veoh.com') === 0) || (strpos(strtolower($text), 'http://veoh.com') === 0) && array_key_exists('veoh', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'veoh';
		} elseif ((strpos(strtolower($text), 'http://media.putfile.com') === 0) && array_key_exists('putfile', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'putfile';
		} elseif ((strpos(strtolower($text), 'http://en.sevenload.com') === 0) || (strpos(strtolower($text), 'http://de.sevenload.com') === 0) || (strpos(strtolower($text), 'http://tr.sevenload.com') === 0) || (strpos(strtolower($text), 'http://tr.sevenload.com') === 0) && array_key_exists('sevenload', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'sevenload';
		} elseif ((strpos(strtolower($text), 'http://www.gametrailers.com') === 0) || (strpos(strtolower($text), 'http://gametrailers.com') === 0) && array_key_exists('gametrailers', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'gametrailers';
		} elseif ((strpos(strtolower($text), 'http://www.spikedhumor.com') === 0) || (strpos(strtolower($text), 'http://spikedhumor.com') === 0) && array_key_exists('spiked', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'spiked';
		} elseif ((strpos(strtolower($text), 'http://www.streetfire.net') === 0) || (strpos(strtolower($text), 'http://videos.streetfire.net') === 0) && array_key_exists('streetfire', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'streetfire';
		} elseif ((strpos(strtolower($text), 'http://video.yahoo.com') === 0) && array_key_exists('yahoo', $this->_typeList)) {
						$this->_mediaInfo['extension'] = 'yahoo';
		} elseif (array_key_exists(strtolower(file_extension($text)), $this->_typeList)) {
						$this->_mediaInfo['extension'] = strtolower(file_extension($text));
		} elseif (strpos(strtolower($text), $this->vbulletin->options['bburl'] . '/attachment.php') === 0 && preg_match('/attachmentid=(\d+)/i', $text, $match) && 	$this->vbulletin->options['anymediaattachurl']) {
			$attach = $this->vbulletin->db->query_first("
				SELECT `extension`
				FROM `" . TABLE_PREFIX . "attachment`
				WHERE `attachmentid`= " . $match[1]
			);
			$this->_mediaInfo['extension'] = strtolower($attach['extension']);
		}
	}

	//	}}}

	//	{{{	processMedia()

	/**
	 * Parse media base on the options.
	 * @param	string	Code of the media
	 * @return	string	HTML representation of the media
	 */
	function processMedia()
	{
		$thisMedia = $this->_typeList[$this->_mediaInfo['extension']];
		if (is_array($thisMedia)) {
		
			if ($this->vbulletin->options[$thisMedia[2]]) {
				eval('$this->' . $thisMedia[1] . '($thisMedia);');
			} else {
				$this->_mediaInfo['type'] = 'unknown';
			}
		} else {
			$this->_mediaInfo['type'] = 'unknown';
		}
	}

	//	}}}

	//	{{{	fetchContent()

	/**
	 * Fetch the remote content.
	 * @param	string	url of the page
	 * @param	string	get the http header?
	 * @return	string	HTML of the page
	 */
	function fetchContent($url, $getHeader = false)
	{
		$content = "";
		if (ini_get('allow_url_fopen') && !$getHeader) {
			//ByFile
			$handle = @fopen($url,"r");
			if(!$handle){
				return false;
			}
			while($buffer = fgets($handle, 4096)) {
			  $content .= $buffer;
			}
			fclose($handle);
			return $content;
		} elseif (function_exists('curl_init')) {
			//ByCurl
			$handle = curl_init();
			curl_setopt ($handle, CURLOPT_URL, $url);
			curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);
			if ($getHeader) {
				curl_setopt ($handle, CURLOPT_HEADER, 1);
				curl_setopt ($handle, CURLOPT_NOBODY, 1);
			}
			$content = curl_exec($handle);
			curl_close($handle);
			return $content;
		} elseif (function_exists('fsockopen')) {
			//BySocket
			if (!($pos = strpos($url, '://'))) {
				return false;
			}
			$host = substr($url, $pos+3, strpos($url, '/', $pos+3) - $pos - 3);
			$uri = substr($url, strpos($url, '/', $pos+3));
			$request = "GET " . $uri . " HTTP/1.0\r\n"
					   ."Host: " . $host . "\r\n"
					   ."Accept: */*\r\n"
					   ."User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)\r\n"
					   ."\r\n";
			$handle = @fsockopen($host, 80, $errno, $errstr, 30);
			if (!$handle) {
				return false;
			}
			@fputs($handle, $request);
			while (!feof($handle)){
				$content .= fgets($handle, 4096);
			}
			fclose($handle);
			$separator = strpos($content, "\r\n\r\n");
			if($getHeader) {
				if($separator === false) {
					return false;
				} else {
					return substr($content, 0, $separator);
				}
			} else {
				if($separator === false) {
					return $content;
				} else {
					return substr($content, $separator + 4);
				}
			}
		} else {
			return false;
		}
	}

	//	}}}

	//	{{{	adobe_flv()

	/**
	 * Adobe Flash Video.
	 * @param	array	media info array
	 */
	function adobe_flv(& $mediaArray)
	{
		$this->_mediaInfo['url'] = $this->vbulletin->options['bburl'] . '/players/flvplayer.swf?file=' . htmlentities(urlencode($this->_mediaInfo['url'])) . '&autoStart=' . iif($this->_mediaInfo['autoplay'] == 'true', 'true', 'false');
		$this->_mediaInfo['autoplay'] = 'true';
		$this->_mediaInfo['height'] += 20;
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = 'adobe_flash';
	}

	//	}}}

	//	{{{	player()

	/**
	 * Use Official Player.
	 * @param	array	media info array
	 */
	function player(& $mediaArray)
	{
		$this->_mediaInfo['autoplay'] = 'false';
		$this->_mediaInfo['mime'] = 'application/x-shockwave-flash';
		$this->_mediaInfo['type'] = 'adobe_flash';
	}

	//	}}}

	//	{{{	adobe_flash()

	/**
	 * Adobe Flash.
	 * @param	array	media info array
	 */
	function adobe_flash(& $mediaArray)
	{
		$this->_mediaInfo['autoplay'] = 'false';
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	//	}}}
	
	//	{{{	divx()

	/**
	 * divx.
	 * @param	array	media info array
	 */
	function divx_video(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = 'video/divx';
		$this->_mediaInfo['type'] = 'divx_video';
	}

	//	}}}

	//	{{{	quick_time()

	/**
	 * Quick Time.
	 * @param	array	media info array
	 */
	function quick_time(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	//	}}}

	//	{{{	real_media()

	/**
	 * Real Media.
	 * @param	array	media info array
	 */
	function real_media(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	//	}}}

	//	{{{	mp3()

	/**
	 * MP3.
	 * @param	array	media info array
	 */
	function mp3(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamp3player']) {
			$this->_mediaInfo['url'] = $this->vbulletin->options['bburl'] . '/players/mp3player.swf?file=' . $this->_mediaInfo['url'] . '&autoStart=' . iif($this->_mediaInfo['autoplay'] == 'true', 'true', 'false') . '&showDownload=false&repeatPlay=' . iif($this->_mediaInfo['loop'] > 1, 'true', 'false');
			$this->_mediaInfo['autoplay'] = 'true';
			$this->_mediaInfo['loop'] = '1';
			$this->_mediaInfo['height'] = 30;
			$this->_mediaInfo['mime'] = 'application/x-shockwave-flash';
			$this->_mediaInfo['type'] = 'adobe_flash';
			} else {
			$this->_mediaInfo['mime'] = 'application/x-mplayer2';
			$this->_mediaInfo['type'] = 'windows_media';
		}
	}

	//	}}}

	//	{{{	windows_media()

	/**
	 * Windows Media.
	 * @param	array	media info array
	 */
	function windows_media(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	//	}}}

	//	{{{	adobe_pdf()

	/**
	 * Adobe PDF.
	 * @param	array	media info array
	 */
	function adobe_pdf(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	//	}}}

	//	{{{	image()

	/**
	 * Image.
	 * @param	array	media info array
	 */
	function image(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
	}

	//	}}}

	//	{{{	torrent()

	/**
	 * Torrent.
	 * @param	array	media info array
	 */
	function torrent(& $mediaArray)
	{
		include_once 'bencode.php';
		$content = $this->fetchContent($this->_mediaInfo['url']);
		if (!empty($content)) {
			$bencode = new BEncodeLib();
			$torrent = $bencode->bdecode($content);
			if (is_array($torrent)) {
				if (is_array($torrent['announce-list'])) {
					foreach ($torrent['announce-list'] as $key => $value) {
						$this->_mediaInfo['extra']['announce'] .= $torrent['announce-list'][$key][0] . '<br />';
					}
				} else {
					$this->_mediaInfo['extra']['announce'] = $torrent['announce'];
				}
				$this->_mediaInfo['extra']['created_by'] = $torrent['created by'];
				$this->_mediaInfo['extra']['creation_date'] = iif($torrent['creation date'], vbdate($this->vbulletin->options['dateformat'], $torrent['creation date'], false) . ' <span class="time">' . vbdate($this->vbulletin->options['timeformat'], $torrent['creation date'], false) . '</span>');
				$this->_mediaInfo['extra']['encoding'] = $torrent['encoding'];
				$this->_mediaInfo['extra']['codepage'] = $torrent['codepage'];
				$this->_mediaInfo['extra']['name'] = iif($torrent['info']['name.utf-8'], $torrent['info']['name.utf-8'], $torrent['info']['name']);
				$this->_mediaInfo['extra']['length'] = iif($torrent['info']['length'], vb_number_format($torrent['info']['length'], 1, true));
				$this->_mediaInfo['extra']['piece_length'] = iif($torrent['info']['piece length'], vb_number_format($torrent['info']['piece length'], 1, true));
				$this->_mediaInfo['extra']['publisher'] = iif($torrent['info']['publisher.utf-8'], $torrent['info']['publisher.utf-8'], $torrent['info']['publisher']);
				$this->_mediaInfo['extra']['publisher_url'] = iif($torrent['info']['publisher-url.utf-8'], $torrent['info']['publisher-url.utf-8'], $torrent['info']['publisher-url']);
				if (is_array($torrent['nodes'])) {
					foreach ($torrent['nodes'] as $key => $value) {
						$this->_mediaInfo['extra']['nodes'] .= $torrent['nodes'][$key][0] . ':' . $torrent['nodes'][$key][1] . '<br />';
					}
				}
				if (is_array($torrent['info']['files'])) {
					foreach ($torrent['info']['files'] as $key => $value) {
						if($torrent['info']['files'][$key]['path.utf-8']) {
							$this->_mediaInfo['extra']['files'] .= iif(is_array($torrent['info']['files'][$key]['path.utf-8']), implode('/', $torrent['info']['files'][$key]['path.utf-8']), $torrent['info']['files'][$key]['path.utf-8']) . ' (' . vb_number_format($torrent['info']['files'][$key]['length'], 1, true) . ') <br />';
						} else {
							$this->_mediaInfo['extra']['files'] .= iif(is_array($torrent['info']['files'][$key]['path']), implode('/', $torrent['info']['files'][$key]['path']), $torrent['info']['files'][$key]['path']) . ' (' . vb_number_format($torrent['info']['files'][$key]['length'], 1, true) . ') <br />';
						}
					}
				}
				$this->_mediaInfo['type']='p2p';
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
			$this->_mediaInfo['type'] = 'error';
		}
	}

	//	}}}

	//	{{{	emule()

	/**
	 * Emule.
	 * @param	array	media info array
	 */
	function emule(& $mediaArray)
	{
		$list = explode("\n", $this->_mediaInfo['url']);
		$totalSize = 0;
		foreach ($list as $emule) {
			$emuleTitle = $emuleSize = '';
			$emuleArray = explode('|', $emule);
			$emuleTitle = rawurldecode($emuleArray[2]);
			$emuleSize = vb_number_format($emuleArray[3], 1, true);
			$totalSize += $emuleArray[3];
			if($emuleTitle && $emuleSize) {
				$this->_mediaInfo['extra']['content'] .= '<tr><td align="left" class="alt2" width="80%"><input type="checkbox" name="anymedia_check_' . $this->_mediaInfo['id'] . '" value="' . $emule . '" onClick="anymedia_size(\'' . $this->_mediaInfo['id'] . '\');" checked="checked" /> <a href="' . $emule . '">' . $emuleTitle . '</a></td><td align="center" class="alt1">' . $emuleSize . '<input type="hidden" name="item_anymedia_' . $this->_mediaInfo['id'] . '" value="' . $emuleArray[3] . '" /></td></tr>';
			} else {
				continue;
			}
		}
		if($this->_mediaInfo['extra']['content']) {
			$this->_mediaInfo['extra']['size'] = vb_number_format($totalSize, 1, true);
			$this->_mediaInfo['type'] = 'p2p';
		} else {
			$this->_mediaInfo['type'] = 'error';
		}
	}

	//	}}}

	//	{{{	foxy()

	/**
	 * Emule.
	 * @param	array	media info array
	 */
	function foxy(& $mediaArray)
	{
		$list = explode("\n", $this->_mediaInfo['url']);
		$totalSize = 0;
		foreach ($list as $foxy) {
			$foxyTitle = $foxySize = '';
			if(preg_match('/dn=([^(\&|$)]*)/i', $foxy, $match)) {
				$foxyTitle = rawurldecode($match[1]);
			}
			if(preg_match('/fs=(\d+)/i', $foxy, $match)) {
				$foxySize = vb_number_format($match[1], 1, true);
				$totalSize += $match[1];
			}
			if($foxyTitle && $foxySize) {
				$this->_mediaInfo['extra']['content'] .= '<tr><td align="left" class="alt2" width="80%"><input type="checkbox" name="anymedia_check_' . $this->_mediaInfo['id'] . '" value="' . $foxy . '" onClick="anymedia_size(\'' . $this->_mediaInfo['id'] . '\');" checked="checked" /> <a href="' . $foxy . '">' . $foxyTitle . '</a></td><td align="center" class="alt1">' . $foxySize . '<input type="hidden" name="item_anymedia_' . $this->_mediaInfo['id'] . '" value="' . $match[1] . '" /></td></tr>';
			} else {
				continue;
			}
		}
		if($this->_mediaInfo['extra']['content']) {
			$this->_mediaInfo['extra']['size'] = vb_number_format($totalSize, 1, true);
			$this->_mediaInfo['type'] = 'p2p';
		} else {
			$this->_mediaInfo['type'] = 'error';
		}
	}

	//	}}}

	//	{{{	pplive()

	/**
	 * PPLive.
	 * @param	array	media info array
	 */
	function pplive(& $mediaArray)
	{
		$this->_mediaInfo['mime'] = $mediaArray[0];
		$this->_mediaInfo['type'] = $mediaArray[1];
		$this->_mediaInfo['height'] += 45;
	}

	//	}}}

	//    {{{    google()

	    /**
	     * Google Video.
	     * @param    array    media info array
	     */
	    function google(& $mediaArray)
	    {
			if ($this->vbulletin->options['anymediagoogle'] == '1') {
	        	if (preg_match('/docid=([^(\&|$)]*)/i', $this->_mediaInfo['url'], $match) || preg_match('/docid\/([^(\&|$)]*)/i', $this->_mediaInfo['url'], $match) || preg_match('/video_id=([^(\&|$)]*)/i', $this->_mediaInfo['url'], $match)) {
	            $this->_mediaInfo['url'] = 'http://video.google.com/googleplayer.swf?docid=' . $match[1];
	            $this->player($mediaArray);
	        	} else {
	            	$this->_mediaInfo['type'] = 'error';
	        	}
			} else {
					$this->_mediaInfo['type'] = 'vidsiteoff';
			}
	    }

	    //    }}}

	//	{{{	youtube()

	/**
	 * Youtube Video.
	 * @param	array	media info array
	 */
	function youtube(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediayoutube']) {
				if (preg_match('/watch\?v=([^(\&|$)]*)/i', $this->_mediaInfo['url'], $match) || preg_match('/v\/([^(\&|$)]*)/i', $this->_mediaInfo['url'], $match) || preg_match('/video_id=([^(\&|$)]*)/i', $this->_mediaInfo['url'], $match)) {
			$this->_mediaInfo['url'] = 'http://www.youtube.com/v/' . $match[1];
			$this->player($mediaArray);
				} else {
					$this->_mediaInfo['type'] = 'error';
				}
		} else {
					$this->_mediaInfo['type'] = 'vidsiteoff';
		}
		
	}

	//	}}}

	//	{{{	vsocial()

	/**
	 * vSocial Video.
	 * @param	array	media info array
	 */
	function vsocial(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediavsocial'] == '1') {
			if (preg_match('{\?d=([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match) || preg_match('holder', $this->_mediaInfo['url'], $match)) {
			$this->_mediaInfo['url'] = 'http://static.vsocial.com/flash/ups.swf?d=' . $match[1] .'&a=1&s=false';
			$this->player($mediaArray);
			}	else {
				$this->_mediaInfo['type'] = 'error';
			}			
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	

	//	}}}

	//	{{{	ifilm()

	/**
	 * iFilm Video.
	 * @param	array	media info array
	 */
	function ifilm(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaifilm'] == '1') {
			if (preg_match('/video\/(\d+)/i', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.ifilm.com/efp?flvBaseClip=' . $match[1];
				$this->player($mediaArray);
			}	else {
				$this->_mediaInfo['type'] = 'error';
			}
		}	else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	

	//	{{{	metacafe()

	/**
	 * MetaCafe Video.
	 * @param	array	media info array
	 */
	function metacafe(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediametacafe'] == '1') {
			if (preg_match('/watch\/(\S+)\//i', $this->_mediaInfo['url'], $match) || preg_match('/watch\/(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.metacafe.com/fplayer/' . $match[1] .'.swf';
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
			$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}

	//	{{{	dailymotion()

	/**
	 * DailyMotion Video.
	 * @param	array	media info array
	 */
	function dailymotion(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediadailymotion'] == '1') {
			if (preg_match('{dailymotion\.com}i', $this->_mediaInfo['url'], $match) || preg_match('/watch\/(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$hurl = $this->_mediaInfo['link'];
				if (function_exists('curl_init'))
				{
					$handle = curl_init();
					curl_setopt ($handle, CURLOPT_URL, $hurl);
					curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
					curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);

					$content = curl_exec($handle);
					curl_close($handle);
				} elseif (@fclose(@fopen($hurl, "r")) && ini_get('allow_url_fopen')) {
					$content = file($hurl);
					$content = implode("",$content);
				} else {
					return $this->_mediaInfo['type'] = 'error';
				}
				

				preg_match("{dailymotion\.com/swf/([a-zA-Z0-9]+)\&quot;}i",$content,$m);
				$this->_mediaInfo['url'] = 'http://www.dailymotion.com/swf/' . $m[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}

	//	{{{	currenttv()

	/**
	 * Current TV Video.
	 * @param	array	media info array
	 */
	function currenttv(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediacurrenttv'] == '1') {
			if (preg_match('{watch\/([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match) || preg_match('/type=vcc&id=(\d+)/i', $this->_mediaInfo['url'], $match) || preg_match('/videoID=(\d+)/i', $this->_url, $match)) {
				$this->_mediaInfo['url'] = 'http://www.current.tv/studio/vm2/vm2.swf?videoType=vcc&videoID=' . $match[1];
				$this->player($mediaArray);		
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}

	//	{{{	vimeo()

	/**
	 * Vimeo Video.
	 * @param	array	media info array
	 */
	function vimeo(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediavimeo'] == '1') {
			if (preg_match('/clip:(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.vimeo.com/moogaloop.swf?clip_id=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}

	//	{{{	sharkle()

	/**
	 * Sharkle Video.
	 * @param	array	media info array
	 */
	function sharkle(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediasharkle'] == '1') {
			if (preg_match('/video\/(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.sharkle.com/sharkle.swf?rnd=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}

	//	{{{	vidiac()

	/**
	 * vidiac.com Video.  Used to be freevideoblog
	 * @param	array	media info array
	 */
	function vidiac(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediavidiac'] == '1') {
			if (preg_match('{video/([a-zA-Z0-9-]+)}', $this->_mediaInfo['url'], $match) || preg_match('{hottestvideos/\d/([a-zA-Z0-9-]+)}', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.vidiac.com/vidiac.swf?video=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}

	//	{{{	myvideode()

	/**
	 * MyVideo.De Video.
	 * @param	array	media info array
	 */
	function myvideode(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamyvideode'] == '1') {
			if (preg_match('/watch\/(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.myvideo.de/movie/' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	/**
	 * Myspace Video.
	 * @param	array	media info array
	 */
	function myspace(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediamyspace'] == '1') {
			if (preg_match('/videoid=(\d+)/i', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://lads.myspace.com/videos/vplayer.swf?m=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	
	/**
	 * Break Video.
	 * @param	array	media info array
	 */
	function bvids(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediabvids'] == '1') {
			if (preg_match('{break\.com}', $this->_mediaInfo['url'], $match)) {
				$hurl = $this->_mediaInfo['link'];
				if (function_exists('curl_init'))
				{
					$handle = curl_init();
					curl_setopt ($handle, CURLOPT_URL, $hurl);
					curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
					curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);

					$content = curl_exec($handle);
					curl_close($handle);
				} elseif (@fclose(@fopen($hurl, "r")) && ini_get('allow_url_fopen')) {
					$content = file($hurl);
					$content = implode("",$content);
				} else {
					return $this->_mediaInfo['type'] = 'error';
				}

				preg_match("{embed\.break\.com/([a-zA-Z0-9]+)}i",$content,$m);
				$this->_mediaInfo['url'] = 'http://embed.break.com/' . $m[1];
				$this->player($mediaArray);
			} else {
					$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	filecabi()

	/**
	 * filecabi.net Video.
	 * @param	array	media info array
	 */
	function filecabi(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediafilecabi'] == '1') {
			if (preg_match('{video/([a-zA-Z0-9_-]+)}', $this->_mediaInfo['url'], $match) || preg_match('{hottestvideos/\d/([a-zA-Z0-9-]+)}', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.filecabi.net/movieplayer.swf?video=http%3A%2F%2Fwww.filecabi.net%2Fplayvideo.php%3Fcid%3D' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	porntube()

	/**
	 * porntube.com Video.
	 * @param	array	media info array
	 */
	function porntube(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaporntube'] == '1') {
			if (preg_match('{v=([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match) || preg_match('{hottestvideos/\d/([a-zA-Z0-9-]+)}', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://pornotube.com/player/v.swf?v=' . $match[1] . '==';
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}	

	
	//	{{{	stage6()

	/**
	 * stage6.divx.com Video.
	 * @param	array	media info array
	 */
	function stage6(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediastage6'] == '1') {
			if (preg_match('{stage6\.divx\.com/user/\S+/video/(\d+)\S+}', $this->_mediaInfo['url'], $match) || preg_match('{stage6\.divx\.com/\S+/video/(\d+)\S+}', $this->_mediaInfo['url'], $match) || preg_match('{video\.stage6\.com/(\d+)\S+}', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://video.stage6.com/' . $match[1] . '/.divx';
				$this->divx_video($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	
	/**
	 * Brightcove Video.
	 * @param	array	media info array
	 */
	function brightcove(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediabrightcove'] == '1') {
			if (preg_match('{brightcove\.com/title\.jsp\?title=([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://admin.brightcove.com/destination/player/player.swf?=llowFullScreen=true&initVideoId=' . $match[1] . '&servicesURL=http://www.brightcove.com&viewerSecureGatewayURL=https://www.brightcove.com&cdnURL=http://admin.brightcove.com&autoStart=fals';
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}	
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	photobucket()

	/**
	 * photobucket.com Video.
	 * @param	array	media info array
	 */
	function photobucket(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaphotobucket'] == '1') {
			if (preg_match('{http://\w(\d+)\.photobucket\.com/\w+/([a-zA-Z0-9]+)/([a-zA-Z0-9_-]+)/\?action=view[&amp;]*\w+=([a-zA-Z0-9_-]+)}', $this->_mediaInfo['url'], $matches)) {
				$this->_mediaInfo['url'] = 'http://vid' . $matches[1] . '.photobucket.com/player.swf?file=http://vid' . $matches[1] . '.photobucket.com/albums/' . $matches[2] . '/' . $matches[3] . '/' . $matches[4] . '.flv';
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	/**
	 * Revver Video.
	 * @param	array	media info array
	 */
	function revver(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediarevver'] == '1') {
			if (preg_match('{revver\.com/watch/([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://flash.revver.com/player/1.0/player.swf';
				$this->_mediaInfo['url2'] = 'mediaId=' . $match[1] . '&affiliateId=0&allowFullScreen=true';
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	liveleak()

	/**
	 * liveleak Video.
	 * @param	array	media info array
	 */
	function liveleak(& $mediaArray)
	{
		if ($this->vbulletin->options['anymedialiveleak'] == '1') {
			if (preg_match('/view\?\i=([a-zA-Z0-9_-]+)/i', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.liveleak.com/player.swf?autostart=false&token=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	Veoh()

	/**
	 * Veoh Video.
	 * @param	array	media info array
	 */
	function veoh(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaveoh'] == '1') {
			if (preg_match('{videos/([a-zA-Z0-9]+)}', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.veoh.com/videodetails2.swf?permalinkId=' . $match[1] . '&id=anonymous&player=videodetailsembedded&videoAutoPlay=0';
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	putfile()

	/**
	 * Veoh Video.
	 * @param	array	media info array
	 */
	function putfile(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaputfile'] == '1') {
			if (preg_match('{media\.putfile\.com/([a-zA-Z0-9_-]+)}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://feat.putfile.com/flow/putfile.swf?videoFile=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	Sevenload()

	/**
	 * Sevenload Video.
	 * @param	array	media info array
	 */
	function sevenload(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediasevenload'] == '1') {
			if (preg_match('{videos/([a-zA-Z0-9_-]+)}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://page.sevenload.com/swf/en_GB/player.swf?id=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	Gamertrailers()

	/**
	 * Sevenload Video.
	 * @param	array	media info array
	 */
	function gametrailers(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediagametrailers'] == '1') {
			if (preg_match('{/player/(\d+)\.}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.gametrailers.com/remote_wrap.php?mid=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	//	{{{	Spikedhumor()

	/**
	 * Spikedhumor Video.
	 * @param	array	media info array
	 */
	function spiked(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediaspiked'] == '1') {
			if (preg_match('{spikedhumor.com/articles/(\d+)/}', $this->_mediaInfo['url'], $match) || preg_match('/flvBaseClip=(\d+)/i', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://www.spikedhumor.com/player/vcplayer.swf?file=http://www.spikedhumor.com/videocodes/' . $match[1] . '/data.xml&auto_play=false';
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	/**
	 * Streetfire Video.
	 * @param	array	media info array
	 */
	function streetfire(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediastreetfire'] == '1') 
		{
			if (preg_match('{/video/([\d\w-]+)}', $this->_mediaInfo['url'], $match) || preg_match('{streetfire\.net/[\d\w]+/0/([\d\w-]+)}', $this->_mediaInfo['url'], $match)) {
				$this->_mediaInfo['url'] = 'http://videos.streetfire.net/vidiac.swf';
				$this->_mediaInfo['url2'] = 'video=' . $match[1];
				$this->player($mediaArray);
			} else {
				$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}

	//	}}}
	
	/**
	 * Yahoo Video.
	 * @param	array	media info array
	 */
	function yahoo(& $mediaArray)
	{
		if ($this->vbulletin->options['anymediayahoo'] == '1' &&  function_exists('curl_init'))
		{
			if (preg_match('{video\.yahoo\.com}', $this->_mediaInfo['url'], $match))
			{
				$hurl = $this->_mediaInfo['link'];
				if (function_exists('curl_init'))
				{
					$handle = curl_init();
					curl_setopt ($handle, CURLOPT_URL, $hurl);
					curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 5);
					curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt ($handle, CURLOPT_FOLLOWLOCATION, 0);

					$content = curl_exec($handle);
					curl_close($handle);
				} elseif (@fclose(@fopen($hurl, "r")) && ini_get('allow_url_fopen')) {
					$content = file($hurl);
					$content = implode("",$content);
				} else {
					return $this->_mediaInfo['type'] = 'error';
				}

				preg_match("/<embed src=(.+)<\/embed>/i",$content,$m);
				$this->_mediaInfo['url2'] = "$m[1]";
			} else {
					$this->_mediaInfo['type'] = 'error';
			}
		} else {
				$this->_mediaInfo['type'] = 'vidsiteoff';
		}
	}
	
	//	}}}

}

?>