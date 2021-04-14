<?php


require_once (DLEPlugins::Check(__DIR__ . '/getid3/getid3.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/classes/thumb.class.php'));

use MaHarder\RePost\RePost;

class Telegram extends RePost {

	private $bot, $channel, $telegram_config, $max_media = 10,
		$images = array(), $images_post = array(), $videos = array(), $audios = array(),
		$xf_images = array(), $xf_videos = array(), $xf_audios = array(), $xf_files = array(),
		$links, $thumb, $media = array();

	/**
	 * Telegram constructor.
	 *
	 * @param        $bot
	 * @param        $ch
	 * @param        $post_id
	 * @param string $tc
	 * @param string $c
	 */
	public function __construct( $post_id, $tc = '', $c = 'addnews' ) {
		global $config;
		parent::__construct($c, $post_id);
		$this->setTelegramConfig($tc);

		$content = $this->telegram_config['addnews'];
		$content_type = $this->getContentType();
		if ($content_type == 'editnews') $content = $this->telegram_config['editnews'];
		elseif ($content_type == 'cron_addnews') $content = $this->telegram_config['cron_addnews'];
		elseif ($content_type == 'cron_editnews') $content = $this->telegram_config['cron_editnews'];

		$filter = array();
		$filter_cats = array();
		$search_fields = json_decode(base64_decode($this->telegram_config['field']), true);
		foreach ($search_fields as $id => $field) {
			if ($field['source'] == 'post') {
				$value = (int)$field['value'] ?: "'{$field['value']}'";
				$filter['fields'][] = "p.{$field['name']} = {$value}";
			} elseif ($field['source'] == 'post_extras') {
				$value = (int)$field['value'] ?: "'{$field['value']}'";
				$filter['fields'][] = "e.{$field['name']} = {$value}";
			} elseif ($field['source'] == 'xfields') {
				$filter['fields'][] = "p.xfields LIKE '%{$field['name']}|{$field['value']}%";
			} elseif ($field['source'] == 'category') {
				$filter_cats[] = $field['name'];
			}
		}

		$filter['cats'] = implode(',', $filter_cats);

		if (!isset($config['allow_multi_category'])) {
			$filter['fields'][] = "p.category in ('{$filter['cats']}')";
		}
		$filter['fields'] = implode(" {$this->telegram_config['field_relation']} ", $filter['fields']);

		$this->processContent($this->setContent($content, true, $filter), $filter);
	}

	private function processContent($content, $filter = array()) {
		global $db, $config;

		$where = [
			'p.id = e.news_id',
			"p.id = {$this->getPostId()}"
		];
		if ( !empty($filter) ) $where[] = "({$filter['fields']})";
		$where = implode(' AND ', $where);

		$join = '';
		if ($config['allow_multi_category'] && $filter['cats']) $join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('{$filter['cats']}') c ON (p.id=c.news_id)";

		$row = $db->super_query('SELECT * FROM ' . PREFIX . '_post p LEFT JOIN ' . PREFIX . "_post_extras e on (p.id = e.news_id) {$join} WHERE {$where}");


		$allcontent = $row['full_story'].$row['short_story'].$row['xfields'];
		preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $allcontent, $media);
		$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);

		foreach($data as $url) {
			$info = pathinfo($url);
			if (isset($info['extension'])) {
				if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" OR strpos($info['dirname'], 'engine/data/emoticons') !== false) continue;
				$info['extension'] = strtolower($info['extension']);
				if (($info['extension'] == 'jpg' || $info['extension'] == 'jpeg' || $info['extension'] == 'gif' ||
					 $info['extension'] == 'png' || $info['extension'] == 'webp') AND !in_array($url, $this->images) )
					array_push($this->images, $url);
			}
		}

		if ( preg_match("#<!--dle_video_begin:(.+?)-->#is", $allcontent, $media) ){
			$media[1] = str_replace( "&#124;", "|", $media[1] );
			$media[1] = explode( ",", trim( $media[1] ) );

			if( count($media[1]) > 1 AND stripos ( $media[1][0], "http" ) === false AND (int)$media[1][0]) {
				$media[1] = explode( "|", $media[1][1] );
			} else $media[1] = explode( "|", $media[1][0] );

			if(!in_array($media[1][0], $this->videos)) $this->videos[] = $media[1][0];
		}

		if ( preg_match("#<!--dle_audio_begin:(.+?)-->#is", $allcontent, $media) ){
			$media[1] = str_replace( "&#124;", "|", $media[1] );

			$media[1] = explode( ",", trim( $media[1] ) );

			if( count($media[1]) > 1 AND stripos ( $media[1][0], "http" ) === false AND (int)$media[1][0]) {
				$media[1] = explode( "|", $media[1][1] );
			} else $media[1] = explode( "|", $media[1][0] );
			if(!in_array($media[1][0], $this->audios)) $this->audios[] = $media[1][0];

		}

		$xfields = xfieldsload();
		if( count($xfields) ) {
			$row['xfields_array'] = xfieldsdataload( $row['xfields'] );
		}

		$content = if_check($content);
		$temp_files = array();
		if( count($xfields) ) {

			$xfieldsdata = $row['xfields_array'];

			foreach ( $xfields as $value ) {

				if($value[3] == "image" AND $xfieldsdata[$value[0]] ) {

					$temp_array = explode('|', $xfieldsdata[$value[0]]);

					if (count($temp_array) > 1 ){
						$temp_value = $temp_array[1];
					} else {
						$temp_value = $temp_array[0];
					}

					$path_parts = @pathinfo($temp_value);

					$img_url = 	$config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];

					if(!in_array($img_url, $this->xf_images[$value[0]])) $this->xf_images[$value[0]][] = $img_url;

				}

				if($value[3] == "file" AND $xfieldsdata[$value[0]] ) {

					$allowed_media = ['mp4', 'mp3', 'm4a'];

					$temp_array = explode('|', $xfieldsdata[$value[0]]);

					if (count($temp_array) > 1 ){
						$temp_value = $temp_array[1];
					} else {
						$temp_value = $temp_array[0];
					}

					$file = $db->super_query("SELECT * FROM " . PREFIX . "_files WHERE name = '{$temp_value}'");

					$url = 	$config['http_home_url'] . "uploads/files/" . $file['onserver'];
					$path = pathinfo($url);

					if(!in_array($url, $temp_files)) {
						$temp_files[] = $url;
						if (isset($path['extension']) && in_array($path['extension'], $allowed_media)) {
							if (in_array($path['extension'], [ 'mp3', 'm4a' ]))
								$this->xf_audios[$value[0]][] = [
									'url'      => $url,
									'size'     => $file['size'],
									'checksum' => $file['checksum'],
								];
							else
								$this->xf_videos[$value[0]][] = [
									'url'      => $url,
									'size'     => $file['size'],
									'checksum' => $file['checksum'],
								];

						}
						else
							$this->xf_files[$value[0]][] = [
								'url'      => $url,
								'size'     => $file['size'],
								'checksum' => $file['checksum'],
							];
					}
				}

				if($value[3] == "imagegalery" AND $xfieldsdata[$value[0]]) {

					$fieldvalue_arr = explode(',', $xfieldsdata[$value[0]]);

					foreach ($fieldvalue_arr as $temp_value) {

						$temp_value = trim($temp_value);

						if($temp_value == "") continue;

						$temp_array = explode('|', $temp_value);

						if (count($temp_array) > 1 ){
							$temp_value = $temp_array[1];
						} else {
							$temp_value = $temp_array[0];
						}

						$path_parts = @pathinfo($temp_value);
						$img_url = 	$config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];

						if(!in_array($img_url, $this->xf_images[$value[0]])) $this->xf_images[$value[0]][] = $img_url;

					}

				}
			}
		}

		foreach($this->images as $id => $ims) {
			foreach($this->xf_images as $ii => $im)
				if (!in_array($ims, $im)) $this->images_post[$ii][] = $im;
		}

		$content = $this->generateMedia($content);
		$content = $this->generateLinks($content);
		$content = $this->generateThumb($content);

		if(preg_grep('/\[telegram_title\](.*?)\[\/telegram_title\]/', explode("\n", $content))) {
			$this->setPostTitle(preg_replace('/\[telegram_title\](.*?)\[\/telegram_title\]/', '$1', $content));
			$content = preg_replace('/\[telegram_title\](.*?)\[\/telegram_title\]/', '', $content);
		}

		return $this->setContent($content);
	}

	private function cleanUp() {
		$dir = ROOT_DIR . '/uploads/telegram';
		$handle = opendir( $dir );
		$file_list = array();
		$skip_files = ['.', '..', '.htaccess'];

		while ( false !== ($file = readdir( $handle )) ) {
			if( !in_array($file, $skip_files) ) {
				$file_list[] = $file;
			}
		}

		closedir( $handle );

		foreach($file_list as $id => $file) @unlink($file);
	}

	private function generateLinks($content) {
		preg_match_all('/\[button=(.*?)\](.*?)\[\/button\]/', $content, $links_array);
		$temp_links = array(
			'inline_keyboard' => []
		);

		foreach ($links_array[0] as $id => $button) {

			$link_name = $links_array[1][$id];
			$link_value = $links_array[2][$id];
			$temp_links['inline_keyboard'][] = [
				'text' => $link_value,
				'callback_data' => $link_name
			];

			$content = str_replace($button, '', $content);
		}

		$this->links = json_encode($temp_links);
		unset($temp_links);

		return $content;
	}

	private function generateMedia($content) {
		preg_match_all('/\[(telegram_media_(image|xfield_(.+?)|allimages|video|audio) (image|max|file|video|audio)=(\d)|telegram_media_(image|xfield_(.+?)|allimages|video|audio))\]/', $content, $media);

		function checkCount() {
			return (count($this->media) > $this->max_media);
		}

		foreach($media[0] as $i => $field) {
			$type = (!empty($media[2][$i])) ? $media[2][$i] : $media[6][$i];
			$limit_type = $media[4][$i];
			$limiter = (empty($media[5][$i])) ? null : (int) $media[5][$i] - 1;
			if(checkCount() === false) break;

			if(preg_grep('/xfield_(.*)/', explode("\n", $type))) {
				preg_match('/xfield_(.*)/', $type, $type_array);
				$xf_name = $type_array[1];
				if(isset($this->xf_images[$xf_name])) {
					if($limit_type == 'file' && $limiter !== null) {
						$file_id = $limiter;
						$this->media[] = $this->mediaPhoto($this->xf_images[$xf_name][$file_id]);
					} elseif ($limit_type == 'max') {
						for($f = 0; $f <= $limiter; $f++) {
							if(checkCount()) {
								$this->media[] = $this->mediaPhoto($this->xf_images[$xf_name][$f]);
							}
						}
					} else {
						foreach($this->xf_images[$xf_name] as $id => $file) {
							if(checkCount()) {
								$this->media[] = $this->mediaPhoto($file);
							}
						}
					}
				}
				elseif(isset($this->xf_audios[$xf_name])) {
					if($limit_type == 'file' && $limiter !== null) {
						$file_id = $limiter;
						$this->media[] = $this->mediaAudio($this->xf_audios[$xf_name][$file_id]);
					} elseif ($limit_type == 'max') {
						for($f = 0; $f <= $limiter; $f++) {
							if(checkCount()) {
								$this->media[] = $this->mediaAudio($this->xf_audios[$xf_name][$f]);
							}
						}
					} else {
						foreach($this->xf_audios[$xf_name] as $id => $file) {
							if(checkCount()) {
								$this->media[] = $this->mediaAudio($file);
							}
						}
					}
				} elseif(isset($this->xf_videos[$xf_name])) {
					if($limit_type == 'file' && $limiter !== null) {
						$file_id = $limiter;
						$this->media[] = $this->mediaVideo($this->xf_videos[$xf_name][$file_id]);
					} elseif ($limit_type == 'max') {
						for($f = 0; $f <= $limiter; $f++) {
							if(checkCount()) {
								$this->media[] = $this->mediaVideo($this->xf_videos[$xf_name][$f]);
							}
						}
					} else {
						foreach($this->xf_videos[$xf_name] as $id => $file) {
							if(checkCount()) {
								$this->media[] = $this->mediaVideo($file);
							}
						}
					}
				} elseif(isset($this->xf_files[$xf_name])) {
					if($limit_type == 'file' && $limiter !== null) {
						$file_id = $limiter;
						$this->media[] = $this->mediaDocument($this->xf_files[$xf_name][$file_id]);
					} elseif ($limit_type == 'max') {
						for($f = 0; $f <= $limiter; $f++) {
							if(checkCount()) {
								$this->media[] = $this->mediaDocument($this->xf_files[$xf_name][$f]);
							}
						}
					} else {
						foreach($this->xf_files[$xf_name] as $id => $file) {
							if(checkCount()) {
								$this->media[] = $this->mediaDocument($file);
							}
						}
					}
				}
			} elseif ($type == 'image') {
				if($limit_type == 'image' && $limiter !== null) {
					$file_id = $limiter;
					$this->media[] = $this->mediaPhoto($this->images_post[$file_id]);
				} elseif ($limit_type == 'max') {
					for($f = 0; $f <= $limiter; $f++) {
						if(checkCount()) {
							$this->media[] = $this->mediaPhoto($this->images_post[$f]);
						}
					}
				} else {
					foreach($this->images_post as $id => $file) {
						if(checkCount()) {
							$this->media[] = $this->mediaPhoto($file);
						}
					}
				}
			} elseif ($type == 'video') {
				if($limit_type == 'video' && $limiter !== null) {
					$file_id = $limiter;
					$this->media[] = $this->mediaVideo($this->videos[$file_id]);
				} elseif ($limit_type == 'max') {
					for($f = 0; $f <= $limiter; $f++) {
						if(checkCount()) {
							$this->media[] = $this->mediaVideo($this->videos[$f]);
						}
					}
				} else {
					foreach($this->videos as $id => $file) {
						if(checkCount()) {
							$this->media[] = $this->mediaVideo($file);
						}
					}
				}
			} elseif ($type == 'audio') {
				if($limit_type == 'audio' && $limiter !== null) {
					$file_id = $limiter;
					$this->media[] = $this->mediaAudio($this->audios[$file_id]);
				} elseif ($limit_type == 'max') {
					for($f = 0; $f <= $limiter; $f++) {
						if(checkCount()) {
							$this->media[] = $this->mediaAudio($this->audios[$f]);
						}
					}
				} else {
					foreach($this->audios as $id => $file) {
						if(checkCount()) {
							$this->media[] = $this->mediaAudio($file);
						}
					}
				}
			} elseif ($type == 'allimages') {
				if($limit_type == 'image' && $limiter !== null) {
					$file_id = $limiter;
					$this->media[] = $this->mediaPhoto($this->images[$file_id]);
				} elseif ($limit_type == 'max') {
					for($f = 0; $f <= $limiter; $f++) {
						if(checkCount()) {
							$this->media[] = $this->mediaPhoto($this->images[$f]);
						}
					}
				} else {
					foreach($this->images as $id => $file) {
						if(checkCount()) {
							$this->media[] = $this->mediaPhoto($file);
						}
					}
				}
			}

			$content = str_replace($field, '', $content);
		}

		return $content;
	}

	private function generateThumb($content) {

		function processImage($image, $quality = 90) {
			global $config;
			$getID3 = new getID3();

			$thumb = $getID3->analyze($image);
			if(!isset($thumb['error'])) {
				$max_size = 200000;
				$max_res = 320;
				$thumb_folder = '/uploads/telegram';
				if (!mkdir($thumb_dir = ROOT_DIR . '/' . $thumb_folder, 0777, true)
					&& !is_dir(
						$thumb_dir
					)) {
					$this->generate_log(
						'telegram',
						'processImage',
						sprintf('Directory "%s" was not created', $thumb_dir)
					);
				}
				$thumb_path = pathinfo($thumb['filenamepath']);
				$thumb_name = "{$thumb_path['filename']}_thumb.{$thumb_path['extension']}";
				$thumb_server = "{$thumb_dir}/{$thumb_name}";
				$thumb_url = "{$config['http_home_url']}/{$thumb_folder}/{$thumb_name}";
				$thumbnail = new thumbnail($thumb);
				$thumbnail->jpeg_quality($quality);
				
				if($thumb['jpg']['exif']['COMPUTED']['Height'] > $max_res || $thumb['jpg']['exif']['COMPUTED']['Width'] > $max_res ) {
					$thumbnail->size_auto($max_res);
				}

				$thumbnail->save($thumb_server);

				$new_thumb = $getID3->analyze($thumb_server);
				if($new_thumb['jpg']['exif']['FILE']['FileSize'] > $max_size) {
					$thumb_url = processImage($thumb_url, ($quality -1));

				}

				return $thumb_url;

			}

			return false;

		}

		if (preg_grep('/\[telegram_thumb\](.*?)\[\/telegram_thumb\]/', explode("\n", $content))) {

			$thumb = preg_replace('/\[telegram_thumb\](.*?)\[\/telegram_thumb\]/', '$1', $content);
			$thumb = (processImage($thumb) !== false) ?: processImage($this->images[0]);
			$thumb = ($thumb !== false) ?: processImage($this->telegram_config['thumb_placeholder']);
			if($thumb) $this->thumb = new CURLFile($thumb);

		}

		$content = preg_replace('/\[telegram_thumb\](.*?)\[\/telegram_thumb\]/', '', $content);

		return $content;

	}

	private function mediaPhoto($link) {
		return [
			'type' => 'photo',
			'media' => [
				'photo' => new CURLFile($link),
				'caption' => '',
				'reply_markup' => ''
			]
		];

	}

	private function mediaDocument($file_array) {
		return [
			'type' => 'document',
			'media' => [
				'document' => new CURLFile($file_array['url']),
				'caption' => '',
				'reply_markup' => '',
				'thumb' => $this->thumb
			]
		];

	}

	private function mediaAudio($file_array) {
		global $config;
		$getID3 = new getID3();
		$file = str_replace($config['http_home_url'], ROOT_DIR, $file_array['url']);
		$audio = $getID3->analyze($file);
		$duration_arr = explode(':', $audio['playtime_string']);
		$duration = ((int) $duration_arr[0] * 60) + (int) $duration_arr[1];
		$tag_selector = (isset($audio['tags']['id3v2'])) ? 'id3v2' : 'id3v1';

		return [
			'type' => 'audio',
			'media' => [
				'audio' => new CURLFile($file_array['url']),
				'caption' => '',
				'reply_markup' => '',
				'duration' => $duration,
				'performer' => $audio['tags'][$tag_selector]['artist'],
				'title' => $audio['tags'][$tag_selector]['title'],
				'thumb' => $this->thumb
			]
		];
	}

	private function mediaVideo($file_array) {
		global $config;
		require_once (DLEPlugins::Check(__DIR__ . '/getid3/getid3.php'));
		$getID3 = new getID3();
		$file = str_replace($config['http_home_url'], ROOT_DIR, $file_array['url']);
		$video = $getID3->analyze($file);
		$duration_arr = explode(':', $video['playtime_string']);
		$duration = ((int) $duration_arr[0] * 60) + (int) $duration_arr[1];

		return [
			'type' => 'video',
			'media' => [
				'video' => new CURLFile($file_array['url']),
				'caption' => '',
				'reply_markup' => '',
				'duration' => $duration,
				'thumb' => $this->thumb,
				'width' => $video['video']['resolution_x'],
				'height' => $video['video']['resolution_y'],
			]
		];
	}


	private function mediaGroup() {
		$media = array();

		if(count($this->media) === 1) {
			$media[] = $this->media[0]['media'];
			if (!empty($this->thumb) && $this->thumb !== null && isset($media[0]['thumb']))
				$media[0]['thumb'] = $this->thumb;
			if (!empty($this->links) && $this->links !== null && isset($media[0]['reply_markup']))
				$media[0]['reply_markup'] = $this->links;
			if (!empty($this->getContent()) && $this->getContent() !== null && isset($media[0]['caption']))
				$media[0]['caption'] = $this->getContent();
		} else {
			foreach ($this->media as $i => $iValue) {
				$media_arr = $iValue;
				$media_tmp = $media_arr['media'];
				$first = $i === 0;

				if($first) {
					if (!empty($this->thumb) && $this->thumb !== null && isset($media_tmp['thumb']))
						$media_tmp['thumb'] = $this->thumb;
					if (!empty($this->links) && $this->links !== null && isset($media_tmp['reply_markup']))
						$media_tmp['reply_markup'] = $this->links;
					if (!empty($this->getContent()) && $this->getContent() !== null && isset($media_tmp['caption']))
						$media_tmp['caption'] = $this->getContent();
				}

				$media[] = [
					'type' => $media_arr['type'],
					'media' => $media_tmp
				];
			}
		}

		return [
			'single' => count($this->media) === 1,
			'type' => $this->media[0]['type'],
			'media' => $media
		];

	}

	public function sendMessage() {
		$proxy = null;
		$type = 'http';
		$auth = null;
		$url = $this->telegram_link();
		if($this->telegram_config['proxy']) $proxy = $this->telegram_config['proxyip'] . ':' . $this->telegram_config['proxyport'];
		if($this->telegram_config['proxytype'] == "socks") {
			$proxy = "socks5://{$proxy}";
			$type = $this->telegram_config['proxytype'];
		}
		if($this->telegram_config['proxyauth']) $auth = $this->telegram_config['proxyuser'] . ':' . $this->telegram_config['proxypass'];

		$this->cleanUp();

		return $this->send($url, $proxy, $type, $auth);
	}


	private function telegram_link() {
		$types = [
			'text' => 'sendMessage',
			'media' => 'sendMediaGroup',
			'photo' => 'sendPhoto',
			'audio' => 'sendAudio',
			'video' => 'sendVideo',
			'document' => 'sendDocument',
		];

		$send_array = array();
		$media_group = $this->mediaGroup();

		switch($this->telegram_config['message_type']) {

			default:
			case 'text':

				$send_array = [
					'text' => urlencode($this->getContent())
				];

				break;

			case 'media':
				if ($media_group['single']) {
					$send_array = $media_group['media'][0];
					$this->telegram_config['message_type'] = $media_group['type'];
				} else {
					$send_array = [
						'media' => json_encode($media_group['media'])
					];
				}

				break;

			case 'photo':
				if (isset($this->images[0])) {
					$send_array = $this->mediaPhoto($this->images[0]);
				}
				break;

			case 'audio':
				if (isset($this->audios[0])) {
					$send_array = $this->mediaAudio($this->audios[0]);
				}
				break;

			case 'video':
				if (isset($this->videos[0])) {
					$send_array = $this->mediaVideo($this->videos[0]);
				}
				break;
		}
		if (!empty($this->thumb) && $this->thumb !== null && isset($send_array['thumb']))
			$send_array['thumb'] = $this->thumb;
		if (!empty($this->links) && $this->links !== null && isset($send_array['reply_markup']))
			$send_array['reply_markup'] = $this->links;
		if (!empty($this->getContent()) && $this->getContent() !== null && isset($send_array['caption']))
			$send_array['caption'] = $this->getContent();
		$send_array['chat_id'] = $this->channel;
		$send_array['parse_mode'] = 'HTML';

		$url_query = http_build_query($send_array);

		return "https://api.telegram.org/bot" . $this->bot . "/{$types[$this->telegram_config['message_type']]}?{$url_query}";
	}

	/**
	 * @param mixed $bot
	 *
	 * @return mixed
	 */
	public function setBot( $bot ) {
		$this->bot = $bot;
		return $this->bot;
	}

	/**
	 * @param mixed $channel
	 *
	 * @return mixed
	 */
	public function setChannel( $channel ) {
		$this->channel = $channel;
		return $this->bot;
	}

	/**
	 * @param mixed $telegram_config
	 *
	 * @return mixed
	 */
	public function setTelegramConfig( $telegram_config ) {
		if(!empty($telegram_config))
			$this->telegram_config = $telegram_config;
		else {
			include_once (DLEPlugins::Check(ENGINE_DIR . '/data/telegram.php'));
			$this->telegram_config = $telebot;
		}
		$this->setBot($this->telegram_config['token']);
		$this->setChannel($this->telegram_config['chat']);
		return $this->telegram_config;
	}
}