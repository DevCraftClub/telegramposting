<?php

require_once(DLEPlugins::Check(__DIR__ . '/getid3/getid3.php'));
require_once(DLEPlugins::Check(__DIR__ . '/repost.class.php'));
require_once(DLEPlugins::Check(__DIR__ . '/thumbs.php'));

class Telegram extends RePost {

	private $bot, $channel, $telegram_config, $max_media = 10, $links, $thumb;
	protected                                                          $media = [];

	/**
	 * Telegram constructor.
	 *
	 * @param        $post_id
	 * @param string $c
	 *
	 * @throws \JsonException
	 */
	public function __construct($post_id, string $c = 'addnews') {
		global $config;
		parent::__construct($c, $post_id);
		$this->setTelegramConfig();
		$mh_config = $this->getConfig('maharder');
		$this->setLogs($mh_config['logs']);
		$this->max_len = 1024;

		$content = $this->telegram_config['addnews'];
		$content_type = $this->getContentType();
		if($content_type == 'editnews') $content = $this->telegram_config['editnews']; elseif($content_type
		                                                                                      == 'cron_addnews') $content = $this->telegram_config['cron_addnews'];
		elseif($content_type == 'cron_editnews') $content = $this->telegram_config['cron_editnews'];

		$filter = [];
		$filter_cats = [];
		$search_fields = json_decode(base64_decode($this->telegram_config['field']), true);
		foreach($search_fields as $id => $field) {
			if($field['source'] == 'post') {
				$value = (int)$field['value'] ?: "'{$field['value']}'";
				$filter['fields'][] = "p.{$field['name']} = {$value}";
			} elseif($field['source'] == 'post_extras') {
				$value = (int)$field['value'] ?: "'{$field['value']}'";
				$filter['fields'][] = "e.{$field['name']} = {$value}";
			} elseif($field['source'] == 'xfields') {
				$filter['fields'][] = "p.xfields LIKE '%{$field['name']}|{$field['value']}%'";
			} elseif($field['source'] == 'category') {
				$filter_cats[] = $field['name'];
			}
		}

		$filter['cats'] = implode(',', $filter_cats);

		if(!isset($config['allow_multi_category'])) {
			$filter['fields'][] = "p.category in ('{$filter['cats']}')";
		}
		$filter['fields'] = implode(" {$this->telegram_config['field_relation']} ", $filter['fields']);

		$this->processContent($this->setContent($content, true, $filter), $filter);
	}

	/**
	 * @throws \JsonException
	 */
	private function processContent($content, $filter = []) {
		global $config;

		$where = [
			'p.id = e.news_id', "p.id = {$this->getPostId()}"
		];
		if(!empty($filter['fields'])) $where[] = "({$filter['fields']})";
		$where = implode(' AND ', $where);

		$join = '';
		if($config['allow_multi_category'] && $filter['cats']) $join = "INNER JOIN (SELECT DISTINCT(" . PREFIX
		                                                               . "_post_extras_cats.news_id) FROM " . PREFIX
		                                                               . "_post_extras_cats WHERE cat_id IN ('{$filter['cats']}')) c ON (p.id=c.news_id)";

		$sql = 'SELECT * FROM ' . PREFIX . '_post p LEFT JOIN ' . PREFIX
		       . "_post_extras e on (p.id = e.news_id) {$join} WHERE {$where}";

		$row = $this->load_data('post', ['table' => 'post', 'sql' => $sql, 'where' => ['news_id' => $this->getPostId()]]
		)[0];

		if($row !== null) {
			$allcontent = $row['full_story'] . $row['short_story'] . $row['xfields'];
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $allcontent, $media);
			$data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

			foreach($data as $url) {
				$info = pathinfo($url);
				if(isset($info['extension'])) {
					if($info['filename'] == "spoiler-plus" or $info['filename'] == "spoiler-minus" or strpos(
						                                                                                  $info['dirname'],
						                                                                                  'engine/data/emoticons'
					                                                                                  )
					                                                                                  !== false) continue;
					$info['extension'] = strtolower($info['extension']);
					if(($info['extension'] == 'jpg' || $info['extension'] == 'jpeg' || $info['extension'] == 'gif'
					    || $info['extension'] == 'png'
					    || $info['extension'] == 'webp') and !in_array($url, $this->images)) $this->images[] = $url;
				}
			}

			if(preg_match("#<!--dle_video_begin:(.+?)-->#is", $allcontent, $media)) {
				$media[1] = str_replace("&#124;", "|", $media[1]);
				$media[1] = explode(",", trim($media[1]));

				if(count($media[1]) > 1 and stripos($media[1][0], "http") === false and (int)$media[1][0]) {
					$media[1] = explode("|", $media[1][1]);
				} else $media[1] = explode("|", $media[1][0]);

				if(!in_array($media[1][0], $this->videos)) $this->videos[] = $media[1][0];
			}

			if(preg_match("#<!--dle_audio_begin:(.+?)-->#is", $allcontent, $media)) {
				$media[1] = str_replace("&#124;", "|", $media[1]);

				$media[1] = explode(",", trim($media[1]));

				if(count($media[1]) > 1 and stripos($media[1][0], "http") === false and (int)$media[1][0]) {
					$media[1] = explode("|", $media[1][1]);
				} else $media[1] = explode("|", $media[1][0]);
				if(!in_array($media[1][0], $this->audios)) $this->audios[] = $media[1][0];

			}

			if(preg_grep('/\[attachment=(\d):(.*)\]/', explode("\n", $allcontent))) {
				preg_match_all('/\[attachment=(\d+?):(.*)\]/', $allcontent, $file_arr);
				foreach($file_arr[0] as $i => $arr) {
					$file_id = $file_arr[1][$i];
					$file = $this->load_data('files', ['where' => ['id' => $file_id]]);
					$url = $config['http_home_url'] . "uploads/files/" . $file['onserver'];
					$path = pathinfo($url);

					$audio = ['mp3', 'm4a'];
					$video = ['mp4'];
					$allowed_media = array_merge($audio, $video);
					if(in_array($path['extension'], $allowed_media)) {
						if(in_array($path['extension'], $audio)) {
							$file_in_arr = array_search($url, array_column($this->audios, 'url'), true);
							if($file_in_arr === false) $this->audios[] = [
								'url' => $url, 'size' => $file['size'], 'checksum' => $file['checksum'],
							];
						} else {
							$file_in_arr = array_search($url, array_column($this->videos, 'url'), true);
							if($file_in_arr === false) $this->videos[] = [
								'url' => $url, 'size' => $file['size'], 'checksum' => $file['checksum'],
							];
						}
					}
				}
			}

			$xfields = xfieldsload();
			if(count($xfields)) {
				$row['xfields_array'] = xfieldsdataload($row['xfields']);
			}

			$content = $this->if_check($content);
			$temp_files = [];
			if(count($xfields)) {

				$xfieldsdata = $row['xfields_array'];

				foreach($xfields as $value) {

					if($value[3] == "image" and $xfieldsdata[$value[0]]) {

						$temp_array = explode('|', $xfieldsdata[$value[0]]);

						if(count($temp_array) > 1) {
							$temp_value = $temp_array[1];
						} else {
							$temp_value = $temp_array[0];
						}

						$path_parts = @pathinfo($temp_value);

						$img_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname'] . "/"
						           . $path_parts['basename'];

						if(!in_array($img_url, $this->xf_images[$value[0]])) $this->xf_images[$value[0]][] = $img_url;

					}

					if($value[3] == "text" and $xfieldsdata[$value[0]]) {

						$images = ['png', 'jpg', 'jpeg', 'gif'];
						$audio = ['mp3', 'm4a'];
						$video = ['mp4'];

						$temp_array = explode('|', $xfieldsdata[$value[0]]);

						if(count($temp_array) > 1) {
							$temp_value = $temp_array[1];
						} else {
							$temp_value = $temp_array[0];
						}

						$path_parts = @pathinfo($temp_value);

						if(in_array($path_parts['extension'], $images)) {
							if(!in_array($temp_value, $this->xf_images[$value[0]])) {
								$this->xf_images[$value[0]][] = $temp_value;
							}
							if(!in_array($temp_value, $this->images)) {
								$this->images[] = $temp_value;
							}
						} elseif(in_array($path_parts['extension'], $audio)) {
							if(!in_array($temp_value, $temp_files)) {
								$this->xf_audios[$value[0]][] = [
									'url' => $temp_value, 'size' => '', 'checksum' => '',
								];
							}
						} elseif(in_array($path_parts['extension'], $video)) {
							if(!in_array($temp_value, $temp_files)) {
								$this->xf_videos[$value[0]][] = [
									'url' => $temp_value, 'size' => '', 'checksum' => '',
								];
							}
						}


						if(!in_array($temp_value, $this->audios)) {
							if(in_array($path_parts['extension'], ['mp3', 'm4a'])) $this->audios[] = [
								'url' => $temp_value, 'size' => '', 'checksum' => '',
							];
						}

						if(!in_array($temp_value, $this->videos)) {
							if($path_parts['extension'] == 'mp4') $this->videos[] = [
								'url' => $temp_value, 'size' => '', 'checksum' => '',
							];
						}


					}

					if($value[3] == "file" and $xfieldsdata[$value[0]]) {

						$allowed_media = ['mp4', 'mp3', 'm4a'];

						$temp_array = explode('|', $xfieldsdata[$value[0]]);

						if(count($temp_array) > 1) {
							$temp_value = $temp_array[1];
						} else {
							$temp_value = $temp_array[0];
						}

						if(preg_grep('/\[attachment=(\d+?):(.*)\]/', explode("\n", $temp_value))) {
							preg_match('/\[attachment=(\d+?):(.*)\]/', $temp_value, $file_arr);

							$file = $this->load_data('files', ['where' => ['id' => $file_arr[1]]]);
						} else $file = $this->load_data('files', ['where' => ['name' => $temp_value]]);

						$url = $config['http_home_url'] . "uploads/files/" . $file['onserver'];
						$path = pathinfo($url);

						if(!in_array($url, $temp_files)) {
							$temp_files[] = $url;
							if(isset($path['extension']) && in_array($path['extension'], $allowed_media)) {
								if(in_array($path['extension'], ['mp3', 'm4a'])) $this->xf_audios[$value[0]][] = [
									'url' => $url, 'size' => $file['size'], 'checksum' => $file['checksum'],
								]; else
									$this->xf_videos[$value[0]][] = [
										'url' => $url, 'size' => $file['size'], 'checksum' => $file['checksum'],
									];

							} else
								$this->xf_files[$value[0]][] = [
									'url' => $url, 'size' => $file['size'], 'checksum' => $file['checksum'],
								];
						}

						if(!in_array($url, $this->audios)) {
							if(in_array($path['extension'], ['mp3', 'm4a'])) $this->audios[] = [
								'url' => $url, 'size' => $file['size'], 'checksum' => $file['checksum'],
							];
						}

						if(!in_array($url, $this->videos)) {
							if(in_array($path['extension'], ['mp4'])) $this->videos[] = [
								'url' => $url, 'size' => $file['size'], 'checksum' => $file['checksum'],
							];
						}
					}

					if($value[3] == "imagegalery" and $xfieldsdata[$value[0]]) {

						$fieldvalue_arr = explode(',', $xfieldsdata[$value[0]]);

						foreach($fieldvalue_arr as $temp_value) {

							$temp_value = trim($temp_value);

							if($temp_value == "") continue;

							$temp_array = explode('|', $temp_value);

							if(count($temp_array) > 1) {
								$temp_value = $temp_array[1];
							} else {
								$temp_value = $temp_array[0];
							}

							$path_parts = @pathinfo($temp_value);
							$img_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname'] . "/"
							           . $path_parts['basename'];

							if(!in_array(
								$img_url, $this->xf_images[$value[0]]
							)) $this->xf_images[$value[0]][] = $img_url;
							if(!in_array($img_url, $this->images)) $this->images[] = $img_url;

						}

					}
				}
			}

			foreach($this->images as $id => $ims) {
				foreach($this->xf_images as $ii => $im) {
					if(!in_array($ims, $im)) $this->images_post[$ii][] = $im;
				}
			}

			if(preg_grep('/\[telegram_title\](.*?)\[\/telegram_title\]/', explode("\n", $content))) {
				$this->setPostTitle(preg_replace('/\[telegram_title\](.*?)\[\/telegram_title\]/', '$1', $content));
				$content = preg_replace('/\[telegram_title\](.*?)\[\/telegram_title\]/', '', $content);
			}

			$this->refactorFiles();
			$content = $this->generateThumb($content);
			$content = $this->generateLinks($content);
			$content = $this->generateMedia($content);

			return $this->setContent($content, true);
		}

		return $this->setContent(null, true);
	}

	private function refactorFiles() {
		$audio = ['mp3', 'm4a'];
		$video = ['mp4'];
		$allowed_media = array_merge($audio, $video);
		foreach($this->files as $file) {
			$file_info = pathinfo($file['url']);
			if(in_array($file_info['extension'], $allowed_media)) {

				if(in_array($file_info['extension'], $audio)) {
					$file_in_arr = array_search($file['url'], array_column($this->audios, 'url'), true);
					if($file_in_arr === false) $this->audios[] = $file;
				} else {
					$file_in_arr = array_search($file['url'], array_column($this->videos, 'url'), true);
					if($file_in_arr === false) $this->videos[] = $file;
				}
			}

		}
	}

	private function cleanUp() {
		$dir = ROOT_DIR . '/uploads/telegram';
		$handle = opendir($dir);
		if($handle) {
			$file_list = [];
			$skip_files = ['.', '..', '.htaccess'];

			while(false !== ($file = readdir($handle))) {
				if(!in_array($file, $skip_files)) {
					$file_list[] = $file;
				}
			}

			closedir($handle);

			foreach($file_list as $id => $file) @unlink($file);
		}
	}

	private function tempFile($file) {
		$_folder = '/uploads/telegram';
		if(!mkdir($_dir = ROOT_DIR . $_folder, 0777, true)
		   && !is_dir(
				$_dir
			)) {
			$this->generate_log(
				'telegram', 'tempFile', sprintf('Directory "%s" was not created', $_dir)
			);
		}

		$_path = pathinfo($file);
		$_name = "{$_path['filename']}_temp.{$_path['extension']}";
		$_file_path = "{$_dir}/{$_name}";
		file_put_contents($_file_path, file_get_contents($file));

		return $_file_path;

	}

	private function generateLinks($content) {
		preg_match_all('/\[button=(.*?)\](.*?)\[\/button\]/', $content, $links_array);
		$temp_links = [
			'inline_keyboard' => []
		];

		foreach($links_array[0] as $id => $button) {

			$link_name = $links_array[1][$id];
			$link_value = $links_array[2][$id];
			$temp_links['inline_keyboard'][$id][] = [
				'text' => $link_value, 'url' => $link_name
			];

			$content = str_replace($button, '', $content);
		}

		$this->links = json_encode($temp_links, JSON_UNESCAPED_UNICODE);
		unset($temp_links);

		return $content;
	}

	private function generateMedia($content) {
		preg_match_all(
			'/\[(telegram_media_(image|xfield_(.+?)|allimages|video|audio) (image|max|file|video|audio)=(\d)|telegram_media_(image|xfield_(.+?)|allimages|video|audio))\]/',
			$content, $media
		);

		if(!function_exists('checkCount')) {
			function checkCount($arr, $max) {
				return (count($arr) <= $max);
			}
		}

		foreach($media[0] as $i => $field) {
			$type = (!empty($media[2][$i])) ? $media[2][$i] : $media[6][$i];
			$limit_type = $media[4][$i];
			$limiter = (empty($media[5][$i])) ? null : (int)$media[5][$i] - 1;
			if(checkCount($this->media, $this->max_media) == false) break;

			if(preg_grep('/xfield_(.*)/', explode("\n", $type))) {
				preg_match('/xfield_(.*)/', $type, $type_array);
				$xf_name = $type_array[1];
				if(isset($this->xf_images[$xf_name])) {
					if($limit_type == 'file' && $limiter !== null) {
						$file_id = $limiter;
						if(checkCount($this->media, $this->max_media)) $this->media[] = $this->mediaPhoto(
							$this->xf_images[$xf_name][$file_id]
						);
					} elseif($limit_type == 'max') {
						for($f = 0; $f <= $limiter; $f++) {
							if(checkCount($this->media, $this->max_media)) {
								$this->media[] = $this->mediaPhoto($this->xf_images[$xf_name][$f]);
							}
						}
					} else {
						foreach($this->xf_images[$xf_name] as $id => $file) {
							if(checkCount($this->media, $this->max_media)) {
								$this->media[] = $this->mediaPhoto($file);
							}
						}
					}
				} elseif(isset($this->xf_audios[$xf_name])) {
					if($limit_type == 'file' && $limiter !== null) {
						$file_id = $limiter;
						if(checkCount($this->media, $this->max_media)) $this->media[] = $this->mediaAudio(
							$this->xf_audios[$xf_name][$file_id]
						);
					} elseif($limit_type == 'max') {
						for($f = 0; $f <= $limiter; $f++) {
							if(checkCount($this->media, $this->max_media)) {
								$this->media[] = $this->mediaAudio($this->xf_audios[$xf_name][$f]);
							}
						}
					} else {
						foreach($this->xf_audios[$xf_name] as $id => $file) {
							if(checkCount($this->media, $this->max_media)) {
								$this->media[] = $this->mediaAudio($file);
							}
						}
					}
				} elseif(isset($this->xf_videos[$xf_name])) {
					if($limit_type == 'file' && $limiter !== null) {
						$file_id = $limiter;
						if(checkCount($this->media, $this->max_media)) $this->media[] = $this->mediaVideo(
							$this->xf_videos[$xf_name][$file_id]
						);
					} elseif($limit_type == 'max') {
						for($f = 0; $f <= $limiter; $f++) {
							if(checkCount($this->media, $this->max_media)) {
								$this->media[] = $this->mediaVideo($this->xf_videos[$xf_name][$f]);
							}
						}
					} else {
						foreach($this->xf_videos[$xf_name] as $id => $file) {
							if(checkCount($this->media, $this->max_media)) {
								$this->media[] = $this->mediaVideo($file);
							}
						}
					}
				} elseif(isset($this->xf_files[$xf_name])) {
					if($limit_type == 'file' && $limiter !== null) {
						$file_id = $limiter;
						if(checkCount($this->media, $this->max_media)) $this->media[] = $this->mediaDocument(
							$this->xf_files[$xf_name][$file_id]
						);
					} elseif($limit_type == 'max') {
						for($f = 0; $f <= $limiter; $f++) {
							if(checkCount($this->media, $this->max_media)) {
								$this->media[] = $this->mediaDocument($this->xf_files[$xf_name][$f]);
							}
						}
					} else {
						foreach($this->xf_files[$xf_name] as $id => $file) {
							if(checkCount($this->media, $this->max_media)) {
								$this->media[] = $this->mediaDocument($file);
							}
						}
					}
				}
			} elseif($type == 'image') {
				if($limit_type == 'image' && $limiter !== null) {
					$file_id = $limiter;
					if(checkCount($this->media, $this->max_media)) $this->media[] = $this->mediaPhoto(
						$this->images_post[$file_id]
					);
				} elseif($limit_type == 'max') {
					for($f = 0; $f <= $limiter; $f++) {
						if(checkCount($this->media, $this->max_media)) {
							$this->media[] = $this->mediaPhoto($this->images_post[$f]);
						}
					}
				} else {
					foreach($this->images_post as $id => $file) {
						if(checkCount($this->media, $this->max_media)) {
							$this->media[] = $this->mediaPhoto($file);
						}
					}
				}
			} elseif($type == 'video') {
				if($limit_type == 'video' && $limiter !== null) {
					$file_id = $limiter;
					if(checkCount($this->media, $this->max_media)) $this->media[] = $this->mediaVideo(
						$this->videos[$file_id]
					);
				} elseif($limit_type == 'max') {
					for($f = 0; $f <= $limiter; $f++) {
						if(checkCount($this->media, $this->max_media)) {
							$this->media[] = $this->mediaVideo($this->videos[$f]);
						}
					}
				} else {
					foreach($this->videos as $id => $file) {
						if(checkCount($this->media, $this->max_media)) {
							$this->media[] = $this->mediaVideo($file);
						}
					}
				}
			} elseif($type == 'audio') {
				if($limit_type == 'audio' && $limiter !== null) {
					$file_id = $limiter;
					if(checkCount($this->media, $this->max_media)) $this->media[] = $this->mediaAudio(
						$this->audios[$file_id]
					);
				} elseif($limit_type == 'max') {
					for($f = 0; $f <= $limiter; $f++) {
						if(checkCount($this->media, $this->max_media)) {
							$this->media[] = $this->mediaAudio($this->audios[$f]);
						}
					}
				} else {
					foreach($this->audios as $id => $file) {
						if(checkCount($this->media, $this->max_media)) {
							$this->media[] = $this->mediaAudio($file);
						}
					}
				}
			} elseif($type == 'allimages') {
				if($limit_type == 'image' && $limiter !== null) {
					$file_id = $limiter;
					if(checkCount($this->media, $this->max_media)) $this->media[] = $this->mediaPhoto(
						$this->images[$file_id]
					);
				} elseif($limit_type == 'max') {
					for($f = 0; $f <= $limiter; $f++) {
						if(checkCount($this->media, $this->max_media)) {
							$this->media[] = $this->mediaPhoto($this->images[$f]);
						}
					}
				} else {
					foreach($this->images as $id => $file) {
						if(checkCount($this->media, $this->max_media)) {
							$this->media[] = $this->mediaPhoto($file);
						}
					}
				}
			}

			$content = str_replace($field, '', $content);
		}

		return $content;
	}

	/**
	 * Обработка изображения под нужные стандарты
	 *
	 * @param string $image       Путь до изображения
	 * @param int    $quality     Качество изображения, по умолчинию: 90
	 * @param int    $min_quality Минимальное качество изображения, по умолчинию: 75
	 * @param int    $max_res     Максимальное количество пискелей для изображений, по умолчинию: 320
	 * @param int    $min_res     Максимальное количество пискелей для изображений, по умолчинию: 240
	 *
	 * @return false|string
	 * @throws \Exception
	 */
	private function processImage(
		string $image, int $quality = 90, int $min_quality = 75, int $max_res = 320, int $min_res = 240
	) {
		global $config;
		$getID3 = new getID3();

		$thumb = $getID3->analyze($image);
		if(!isset($thumb['error'])) {
			$max_size = 200000;
			$thumb_folder = 'uploads/telegram';
			if(!mkdir($thumb_dir = ROOT_DIR . DIRECTORY_SEPARATOR . $thumb_folder, 0777, true)
			   && !is_dir(
					$thumb_dir
				)) {
				$this->generate_log(
					'telegram', 'processImage', sprintf('Directory "%s" was not created', $thumb_dir)
				);
			}
			$thumb_path = pathinfo($thumb['filenamepath']);
			$thumb_name = "{$thumb_path['filename']}_thumb.{$thumb_path['extension']}";
			$thumb_server = "{$thumb_dir}/{$thumb_name}";
			$thumb_url = "{$config['http_home_url']}{$thumb_folder}/{$thumb_name}";
			$thumbnail = new Thumbs($thumb['filenamepath']);

			if($thumb['jpg']['exif']['COMPUTED']['Height'] > $max_res
			   || $thumb['jpg']['exif']['COMPUTED']['Width'] > $max_res) {
				if($thumb['jpg']['exif']['COMPUTED']['Height'] > $max_res) $thumbnail->reduce(
					0, $max_res
				); else $thumbnail->reduce($max_res, 0);
			}

			$thumbnail->save($thumb_server, $quality);

			$new_thumb = $getID3->analyze($thumb_server);
			if($new_thumb['jpg']['exif']['FILE']['FileSize'] > $max_size) {
				if($quality >= $min_quality) $thumb_url = $this->processImage(
					$thumb_url, ($quality - 1)
				); elseif($max_res > $min_res) $thumb_url = $this->processImage(
					$thumb_url, $quality, $min_quality, ($max_res - 1)
				);
			}

			return $thumb_url;

		}

		return false;

	}

	/**
	 * @param $content
	 *
	 * @return array|string|string[]|null
	 * @throws \Exception
	 */
	private function generateThumb($content) {
		global $config;

		if(!function_exists('serverLink')) {
			function serverLink($link) {
				global $config;
				$_link = str_replace($config['http_home_url'], ROOT_DIR . DIRECTORY_SEPARATOR, $link);
				if(file_exists($_link)) return new CURLFile($_link);

				return $link;
			}
		}

		if(preg_grep('/\[telegram_thumb\](.*?)\[\/telegram_thumb\]/', explode("\n", $content))) {
			$thumb = preg_replace('/\[telegram_thumb\](.*?)\[\/telegram_thumb\]/', '$1', $content);
			$thumb = ($this->processImage($thumb) !== false) ?: $this->processImage($this->images[0]);
			$thumb = ($thumb !== false) ?: $this->processImage($this->telegram_config['thumb_placeholder']);
			if($thumb) $this->thumb = serverLink($thumb);
		} elseif(!empty($this->images[0])) {
			$thumb = $this->images[0];
			$url_parts = parse_url($thumb);
			$config_url = parse_url($config['http_home_url']);
			if($url_parts['host'] != $config_url['host'] && !file_exists($thumb)) $thumb = $this->tempFile($thumb);
			$thumb = $this->processImage($thumb);
			$this->thumb = serverLink($thumb);
		} elseif(!empty($this->telegram_config['thumb_placeholder'])) {
			$thumb = $this->telegram_config['thumb_placeholder'];
			$url_parts = parse_url($thumb);
			$config_url = parse_url($config['http_home_url']);
			if($url_parts['host'] != $config_url['host']) $thumb = $this->tempFile($thumb);
			$thumb = $this->processImage($thumb);
			if($thumb
			   && !in_array('127.0.0.1', [$_SERVER['SERVER_ADDR'], $_SERVER['REMOTE_ADDR']])) $this->thumb = serverLink(
				$thumb
			); else {
				$title = str_replace(' ', '+', $this->getPostTitle());
				$this->thumb = "https://dummyimage.com/320x320/202328/fff.png&text={$title}";
			}
		} else {
			$title = str_replace(' ', '+', $this->getPostTitle());
			$this->thumb = "https://dummyimage.com/320x320/202328/fff.png&text={$title}";
		}

		return preg_replace('/\[telegram_thumb\](.*?)\[\/telegram_thumb\]/', '', $content);

	}

	private function mediaPhoto($link)
	: array {
		global $config;

		$url_parts = parse_url($link);
		$config_url = parse_url($config['http_home_url']);
		if($url_parts['host'] == $config_url['host'] || !isset($url_parts['host'])) {
			if(!isset($url_parts['host'])) {
				$trenner = ($link[0] === '/') ? DIRECTORY_SEPARATOR : '';
				$link_info = pathinfo($link);
				$dirs_link = explode('/', str_replace([DIRECTORY_SEPARATOR, '\\'], '/', $link_info['dirname']));
				if($dirs_link[0] === 'uploads' || $dirs_link[1] === 'uploads') $link = ROOT_DIR . $trenner . $link;
			} else $link = str_replace($config['http_home_url'], ROOT_DIR . DIRECTORY_SEPARATOR, $link);
			$image = new CURLFile($link);
		} else $image = $link;
		return [
			'type' => 'photo', 'media' => [
				'photo' => $image, 'caption' => '', 'reply_markup' => ''
			]
		];

	}

	private function mediaDocument($file_array)
	: array {
		global $config;

		$file_array['url'] = str_replace([ROOT_DIR . DIRECTORY_SEPARATOR, ROOT_DIR . '/'], $config['http_home_url'],
		                                 $file_array['url']);
		$url_parts = parse_url($file_array['url']);
		$config_url = parse_url($config['http_home_url']);
		if($url_parts['host'] == $config_url['host'] || !isset($url_parts['host'])) {
			if(!isset($url_parts['host'])) {
				$trenner = ($file_array['url'][0] !== '/') ? DIRECTORY_SEPARATOR : '';
				$url_info = parse_url($file_array['url']);
				if(isset($url_info['host']) && $url_info['host'] == $config_url['host']
				   && $file_array['url'][0] === '/') $file_array['url'] = ROOT_DIR . $trenner . $file_array['url'];
			} else $file_array['url'] = str_replace(
				$config['http_home_url'], ROOT_DIR . DIRECTORY_SEPARATOR, $file_array['url']
			);
			$file = new CURLFile($file_array['url']);
		} else $file = $file_array['url'];

		$f_info = pathinfo($file_array['url']);

		$send_array = [
			'type' => 'document', 'media' => [
				'audio'        => "attach://{$f_info['basename']}", $f_info['basename'] => $file, 'caption' => '',
				'reply_markup' => '', 'thumb' => ''
			]
		];

		if($this->thumb !== null) {
			$thumb = pathinfo($this->thumb);
			if($thumb == null) $thumb = pathinfo($this->thumb->getFilename());
			$send_array['media']['thumb'] = "attach://{$thumb['basename']}";
			$send_array['media'][$thumb['basename']] = $this->thumb;
		}
		return $send_array;

	}

	private function mediaAudio($file_array) {
		global $config;

		$file_array['url'] = str_replace([ROOT_DIR . DIRECTORY_SEPARATOR, ROOT_DIR . '/'], $config['http_home_url'],
		                                 $file_array['url']);
		$url_parts = parse_url($file_array['url']);
		$config_url = parse_url($config['http_home_url']);
		$getID3 = new getID3();

		if($url_parts['host'] == $config_url['host']) {
			$trenner = ($file_array['url'][0] !== '/') ? DIRECTORY_SEPARATOR : '';
			$file = str_replace($config['http_home_url'], ROOT_DIR . $trenner, $file_array['url']);
		} else
			$file = $this->tempFile($file_array['url']);
		$audio = $getID3->analyze($file);
		$duration_arr = explode(':', $audio['playtime_string']);
		$duration = ((int)$duration_arr[0] * 60) + (int)$duration_arr[1];
		$tag_selector = (isset($audio['tags']['id3v2'])) ? 'id3v2' : 'id3v1';

		if($url_parts['host'] == $config_url['host'] || !isset($url_parts['host'])) {
			if(!isset($url_parts['host'])) {
				$trenner = ($file_array['url'][0] !== '/') ? DIRECTORY_SEPARATOR : '';
				$url_info = parse_url($file_array['url']);
				if(isset($url_info['host']) && $url_info['host'] == $config_url['host']
				   && $file_array['url'][0] === '/') $file_array['url'] = ROOT_DIR . $trenner . $file_array['url'];
			} else $file_array['url'] = str_replace(
				$config['http_home_url'], ROOT_DIR . DIRECTORY_SEPARATOR, $file_array['url']
			);
			$audio_file = new CURLFile($file_array['url']);
		} else $audio_file = $file_array['url'];

		$af_info = pathinfo($file_array['url']);

		$send_array = [
			'type' => 'audio', 'media' => [
				'audio'        => "attach://{$af_info['basename']}", $af_info['basename'] => $audio_file,
				'caption'      => '', 'reply_markup' => '', 'duration' => $duration,
				'performer'    => implode(', ', $audio['tags'][$tag_selector]['artist']),
				'title'        => implode('; ', $audio['tags'][$tag_selector]['title']), 'thumb' => ''
			]
		];

		if(!$audio['tags'][$tag_selector]['artist']) unset($send_array['media']['performer']);
		if(!$audio['tags'][$tag_selector]['title']) unset($send_array['media']['title']);
		if($duration === 0) unset($send_array['media']['duration']);

		if($this->thumb !== null) {
			$thumb = pathinfo($this->thumb);
			if($thumb == null) $thumb = pathinfo($this->thumb->getFilename());
			$send_array['media']['thumb'] = "attach://{$thumb['basename']}";
			$send_array['media'][$thumb['basename']] = $this->thumb;
		}
		return $send_array;
	}

	private function mediaVideo($file_array) {
		global $config;

		$file_array['url'] = str_replace([ROOT_DIR . DIRECTORY_SEPARATOR, ROOT_DIR . '/'], $config['http_home_url'],
		                                 $file_array['url']);
		$url_parts = parse_url($file_array['url']);
		$config_url = parse_url($config['http_home_url']);
		$getID3 = new getID3();

		if($url_parts['host'] == $config_url['host']) {
			$trenner = ($file_array['url'][0] !== '/') ? DIRECTORY_SEPARATOR : '';
			$file = str_replace($config['http_home_url'], ROOT_DIR . $trenner, $file_array['url']);
		} else
			$file = $this->tempFile($file_array['url']);

		$video = $getID3->analyze($file);
		$duration_arr = explode(':', $video['playtime_string']);
		$duration = ((int)$duration_arr[0] * 60) + (int)$duration_arr[1];

		if($url_parts['host'] == $config_url['host'] || !isset($url_parts['host'])) {
			if(!isset($url_parts['host'])) {
				$trenner = ($file_array['url'][0] !== '/') ? DIRECTORY_SEPARATOR : '';
				$url_info = parse_url($file_array['url']);
				if(isset($url_info['host']) && $url_info['host'] == $config_url['host']
				   && $file_array['url'][0] === '/') $file_array['url'] = ROOT_DIR . $trenner . $file_array['url'];
			} else $file_array['url'] = str_replace(
				$config['http_home_url'], ROOT_DIR . DIRECTORY_SEPARATOR, $file_array['url']
			);
			$video_file = new CURLFile($file_array['url']);
		} else $video_file = $file_array['url'];

		$vf_info = pathinfo($file_array['url']);

		$send_array = [
			'type' => 'video', 'media' => [
				'video'        => "attach://{$vf_info['basename']}", $vf_info['basename'] => $video_file,
				'caption'      => '', 'reply_markup' => '', 'duration' => $duration,
				'width'        => $video['video']['resolution_x'], 'height' => $video['video']['resolution_y'],
				'thumb'        => ''
			]
		];
		if(!$video['video']['resolution_x']) unset($send_array['media']['width']);
		if(!$video['video']['resolution_y']) unset($send_array['media']['height']);

		if($this->thumb !== null) {
			$thumb = pathinfo($this->thumb);
			if($thumb == null) $thumb = pathinfo($this->thumb->getFilename());
			$send_array['media']['thumb'] = "attach://{$thumb['basename']}";
			$send_array['media'][$thumb['basename']] = $this->thumb;
		}
		return $send_array;
	}

	private function mediaGroup() {
		$media = [];

		if(count($this->media) === 1) {
			$media[] = $this->media[0]['media'];
			if(!empty($this->thumb) && $this->thumb !== null
			   && isset($media[0]['thumb'])) $media[0]['thumb'] = $this->thumb;
			if(!empty($this->links) && $this->links !== null
			   && isset($media[0]['reply_markup'])) $media[0]['reply_markup'] = $this->links;
			if(!empty($this->getContent()) && $this->getContent() !== null
			   && isset($media[0]['caption'])) $media[0]['caption'] = $this->finalContent();
		} else {
			foreach($this->media as $i => $iValue) {
				$media_arr = $iValue;
				$media_tmp = $media_arr['media'];
				$media_tmp['type'] = $media_arr['type'];
				$media_tmp['media'] = $media_tmp[$media_arr['type']];
				unset($media_tmp[$media_arr['type']]);
				$first = $i === 0;

				if($first) {
					if(!empty($this->links) && $this->links !== null
					   && isset($media_tmp['reply_markup'])) $media_tmp['reply_markup'] = $this->links;
					if(!empty($this->getContent()) && $this->getContent() !== null
					   && isset($media_tmp['caption'])) $media_tmp['caption'] = $this->finalContent();
					$media_tmp['parse_mode'] = 'HTML';
				}
				if(!empty($this->thumb) && $this->thumb !== null && isset($media_tmp['thumb'])) {
					$file = pathinfo($this->thumb);
					$media_tmp['thumb'] = "attach://{$file['basename']}";
					$media_tmp[$file['basename']] = $this->thumb;
				}

				$media[] = $media_tmp;
			}
		}

		return [
			'single' => count($this->media) === 1, 'type' => $this->media[0]['type'], 'media' => $media
		];

	}

	public function sendMessage() {
		if($this->getContent() !== null) {
			$proxy = null;
			$type = 'http';
			$auth = null;
			$url = $this->telegram_link();
			if($this->telegram_config['proxy']) $proxy = $this->telegram_config['proxyip'] . ':'
			                                             . $this->telegram_config['proxyport'];
			if($this->telegram_config['proxytype'] == "socks") {
				$proxy = "socks5://{$proxy}";
				$type = $this->telegram_config['proxytype'];
			}
			if($this->telegram_config['proxyauth']) $auth = $this->telegram_config['proxyuser'] . ':'
			                                                . $this->telegram_config['proxypass'];

			$this->generate_log('telegram', 'sendMessage', $url, 'info');

			$response = $this->send($url['url'], $url['post'], $proxy, $type, $auth);

			$this->cleanUp();
		} else {
			$response = json_encode([
				'ok' => false,
				'message' => _('Новость не соответствует требованиям!')
			], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
		}

		return $response;
	}

	private function telegram_link() {

		$types = [
			'text'  => 'sendMessage', 'media' => 'sendMediaGroup', 'photo' => 'sendPhoto', 'audio' => 'sendAudio',
			'video' => 'sendVideo', 'document' => 'sendDocument',
		];

		$send_array = [];
		$media_group = $this->mediaGroup();

		switch($this->telegram_config['message_type']) {

			default:
			case 'text':

				$send_array = [
					'text' => $this->finalContent(), 'reply_markup' => ''
				];

				break;

			case 'media':
				if($media_group['single']) {
					$send_array = $media_group['media'][0];
					$this->telegram_config['message_type'] = $media_group['type'];
				} else {
					foreach($media_group['media'] as $i => $media) {
						$first = $i === 0;
						try {
							$file = $media['media']->getFilename();
							$file = pathinfo($file);
							$images = ['png', 'jpeg', 'jpg', 'gif'];
							$video = ['mp4'];
							$audio = ['mp3', 'm4a'];
							$allowed_media = array_merge($images, $video, $audio);

							$media['media']->setPostFilename($file['filename']);
							if(in_array($file['extension'], $allowed_media)) {
								$mime_type = '';
								$extension = $file['extension'];
								if(in_array($file['extension'], $images)) {
									$mime_type = 'image';
									if($file['extension'] == 'jpg') $extension = $file['extension'];
								} elseif(in_array($file['extension'], $video)) {
									$mime_type = 'video';
									if(!$first) {
										if($media_group['media'][$i]['duration']
										   === 0) unset($media_group['media'][$i]['duration']);
										if($media_group['media'][$i]['width'] === 0
										   || $media_group['media'][$i]['width']
										      === null) unset($media_group['media'][$i]['width']);
										if($media_group['media'][$i]['height'] === 0
										   || $media_group['media'][$i]['height']
										      === null) unset($media_group['media'][$i]['height']);
									}
								} elseif(in_array($file['extension'], $audio)) {
									$mime_type = 'audio';
									if($file['extension'] == 'm4a') $extension = $extension = 'mp4';
									if(!$first) {
										if($media_group['media'][$i]['duration']
										   === 0) unset($media_group['media'][$i]['duration']);
										if($media_group['media'][$i]['performer']
										   === null) unset($media_group['media'][$i]['performer']);
										if($media_group['media'][$i]['title'] === null) if(!empty(
										$this->getPostTitle()
										)) $media_group['media'][$i]['title'] = $this->getPostTitle(); else
											unset($media_group['media'][$i]['title']);
									}
								}
								$media['media']->setMimeType("{$mime_type}/{$extension}");
							}

						} catch(Exception $e) {
							$file = pathinfo($media['media']);
						}
						$send_array[$file['basename']] = $media['media'];
						$media_group['media'][$i]['media'] = "attach://{$file['basename']}";
						if(empty($media_group['media'][$i]['caption'])) unset($media_group['media'][$i]['caption']);
						if(empty($media_group['media'][$i]['reply_markup'])) unset($media_group['media'][$i]['reply_markup']);
					}
					$m = json_encode($media_group['media'], JSON_UNESCAPED_UNICODE);
					$send_array['media'] = $m;
				}

				break;

			case 'photo':
				if(isset($this->images[0])) {
					$send_array = $this->mediaPhoto($this->images[0])['media'];
				}
				break;

			case 'audio':
				if(isset($this->audios[0])) {
					$send_array = $this->mediaAudio($this->audios[0])['media'];
				}
				break;

			case 'video':
				if(isset($this->videos[0])) {
					$send_array = $this->mediaVideo($this->videos[0])['media'];
				}
				break;
		}
		if(!empty($this->thumb) && $this->thumb !== null && isset($send_array['thumb'])) {
			$thumb = pathinfo($this->thumb);
			if($thumb == null) $thumb = pathinfo($this->thumb->getFilename());

			if(isset($thumb['extension'])) {
				$send_array['thumb'] = "attach://{$thumb['basename']}";
				$send_array[$thumb['basename']] = $this->thumb;
			} else unset($send_array['thumb']);
		}
		if(!empty($this->links) && $this->links !== null
		   && isset($send_array['reply_markup'])) $send_array['reply_markup'] = $this->links;
		if(!empty($this->getContent()) && $this->getContent() !== null
		   && isset($send_array['caption'])) $send_array['caption'] = $this->finalContent();

		$send_array['chat_id'] = str_replace('%40', '@', $this->channel);
		$send_url['parse_mode'] = 'HTML';

		$this->generate_log('telegram', 'telegram_link', ['url' => $send_url, 'arr' => $send_array], 'info');

		$url_query = http_build_query($send_url);
		$url_query = str_replace('chat_id=%40', 'chat_id=@', $url_query);

		return [
			'url' => "https://api.telegram.org/bot" . $this->bot
			         . "/{$types[$this->telegram_config['message_type']]}?{$url_query}", 'post' => $send_array
		];
	}

	/**
	 * @param mixed $bot
	 *
	 * @return mixed
	 */
	public function setBot($bot) {
		$this->bot = $bot;
		return $this->bot;
	}

	/**
	 * @param mixed $channel
	 *
	 * @return mixed
	 */
	public function setChannel($channel) {
		$this->channel = $channel;
		return $this->bot;
	}

	/**
	 * @return array
	 */
	public function setTelegramConfig()
	: array {
		$this->telegram_config = $this->getConfig('telegram');
		$this->setBot($this->telegram_config['token']);
		$this->setChannel($this->telegram_config['chat']);
		return $this->telegram_config;
	}
}
