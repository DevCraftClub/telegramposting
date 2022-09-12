<?php

class RePost {

	use DataLoader;

	/**
	 * @var string|null
	 */
	private $content_type = null;
	/**
	 * @var string|null
	 */
	private $post_title = null;
	/**
	 * @var int
	 */
	private $post_id;
	/**
	 * @var string|null
	 */
	private $content = null;
	/**
	 * @var array
	 */
	private $xf_images = [], $files = [], $images_post = [], $videos = [], $audios = [], $xf_videos = [], $xf_audios = [], $xf_files = [], $images = [], $content_tags = [], $if_array = [];
	/**
	 * @var int
	 */
	private $max_len = 0;
	/**
	 * @var string
	 */
	protected $allowed_html = '<b><code><i><a><u><s><br>';
	/**
	 * @version 1.7.3
	 * @var string
	 */
	private $hashtag_separator = null;
	/**
	 * @version 1.7.3
	 * @var string
	 */
	private $tag_separator = null;
	/**
	 * @version 1.7.3
	 * @var string
	 */
	private $category_separator = null;

	/**
	 * RePost constructor.
	 *
	 * @param $c
	 * @param $post_id
	 */
	public function __construct($c, $post_id) {

		$this->setPostId($post_id);
		$this->content_type = $c;

		$this->setHashtagSeparator($this->getTagSeparator());
		$this->setTagSeparator($this->getTagSeparator());
		$this->setCategorySeparator($this->getCategorySeparator());
	}

	/**
	 * @version 1.7.3
	 *
	 * @param string $tag_separator
	 */
	public function setTagSeparator(?string $tag_separator) {
		$this->tag_separator = $tag_separator;
	}

	/**
	 * @version 1.7.3
	 * @return string
	 */
	public function getTagSeparator() {
		return $this->tag_separator;
	}

	/**
	 * @version 1.7.3
	 *
	 * @param string $hashtag_separator
	 */
	public function setHashtagSeparator($hashtag_separator) {
		$this->hashtag_separator = $hashtag_separator;
	}

	/**
	 * @version 1.7.3
	 * @return string
	 */
	public function getHashtagSeparator() {
		return $this->hashtag_separator;
	}

	/**
	 * @version 1.7.3
	 *
	 * @param string $category_separator
	 */
	public function setCategorySeparator($category_separator) {
		$this->category_separator = $category_separator;
	}

	/**
	 * @version 1.7.3
	 * @return string
	 */
	public function getCategorySeparator() {
		return $this->category_separator;
	}


	/**
	 * @param int $max_len
	 */
	public function setMaxLen(int $max_len) {
		$this->max_len = $max_len;
	}

	/**
	 * @return int
	 */
	public function getMaxLen() {
		return $this->max_len;
	}


	/**
	 * Обработка содержимого
	 *
	 * @param mixed $content          Содержимое
	 * @param bool  $parse            Параметр для повторной обработки
	 *                                содержимого, по умолчанию: false
	 * @param array $parse_filter     Параметры фильтрации содержимого
	 *
	 * @return mixed
	 * @throws \JsonException
	 */
	public function setContent(
		$content,
		bool $parse = false,
		array $parse_filter = []) {
		if ($content !== null) {
			$this->content = $parse ?
				$this->parse_content($content, $parse_filter) : $content;
		} else {
			$this->content = $content;
		}

		return $this->content;
	}

	/**
	 * Проставляет ID новости
	 *
	 * @param mixed $post_id ID новости
	 *
	 * @return int
	 */
	public function setPostId(int $post_id) {
		$this->post_id = (int)$post_id;

		return $this->post_id;
	}

	/**
	 * Возвращает обработанное содержимое
	 * - Обрабатывает перенос строк
	 * - Обрабатывает макс. длину
	 *
	 * @version 1.7.7
	 * @return string
	 */
	protected function finalContent() {
		$len = $this->getMaxLen() - 3;
		try {
			$new_line = PHP_EOL;
		} catch (Exception $e) {
			$new_line = '%0A';
		}

		$content = $this->getContent();
		$content = preg_replace('/\[[^\]]+\]/', '', $content);
		preg_match_all('/\[[^\]]+\]/', $content, $content_arr);
		foreach ($content_arr[0] as $bb) {
			$content = str_replace($bb, '', $content);
		}
		$content = strip_tags($content, $this->getAllowedHtml());

		$longText = false;
		if (strlen($content) >= $this->max_len) {
			$content = mb_substr($content, 0, $len, "utf-8");
			$longText = true;
		}

		preg_match_all('/<(\w+)>/', $this->getAllowedHtml(), $matches);
		$allowed_tags = $matches[1];

		// Формируем альтернативы для регулярного выражения
		$tag_pattern = implode('|', array_map('preg_quote', $allowed_tags));

		// Регулярное выражение для поиска НЕ закрывающихся (не завершённых) открывающих тегов
		$regex = '/<(?:' . $tag_pattern . ')(?![^>]*>)/i';

		$content = preg_replace($regex, '', $content);

		if ($longText) $content .= '...';

		return $content;
	}

	public function if_check($matches) {
		global $config, $row;

		if (count($this->if_array)) {
			$row = $this->if_array;
		}

		$regex = '/\[if (.+?)\]((?>(?R)|.)*?)\[\/if\]/is';

		if (is_array($matches)) {
			$matches[1]  = trim(dle_strtolower($matches[1],
											   $config['charset']
								)
			);
			$find_type   = true;
			$match_count = 0;

			if (stripos($matches[1], " or ")) {
				$find_type = false;
				$if_array  = explode(" or ", $matches[1]);
			} else {
				$if_array = explode(" and ", $matches[1]);
			}

			foreach ($if_array as $if_str) {
				$if_str = trim($if_str);

				preg_match("#^(.+?)(!~|~|!=|=|>=|<=|<|>)\s*['\"]?(.*?)['\"]?$#is",
						   $if_str,
						   $m
				);

				$field    = trim($m[1]);
				$operator = trim($m[2]);
				$value    = trim($m[3]);

				$field = explode("xfield_", $field);

				if ($field[1]) {
					$fieldvalue = $row['xfields_array'][$field[1]];
				} elseif ($field[0] == 'date' || $field[0] == 'editdate' || $field[0] == 'lastdate' || $field[0] == 'reg_date') {
					$fieldvalue = strtotime(date("Y-m-d H:i", $row[$field[0]]));

					if (strtotime($value) !== false) {
						$value = strtotime($value);
					}
				} elseif ($field[0] == 'tags' && is_array($row[$field[0]])) {
					$fieldvalue = [];

					foreach ($row[$field[0]] as $temp_value) {
						$fieldvalue[] = trim(dle_strtolower($temp_value, $config['charset']));
					}
				} elseif ($field[0] == 'category') {
					$fieldvalue = $row['cats'];
				} else {
					$fieldvalue = $row[$field[0]];
				}

				if (!is_array($fieldvalue)) {
					$fieldvalue = trim(dle_strtolower($fieldvalue, $config['charset']));
				}

				switch ($operator) {
					case ">":

						if (is_array($fieldvalue)) {
							$found_match = false;

							foreach ($fieldvalue as $temp_value) {
								$temp_value = floatval($temp_value);
								$value      = floatval($value);

								if ($temp_value > $value) {
									$found_match = true;
								}
							}

							if ($found_match) {
								$match_count++;
							}
						} else {
							$fieldvalue = (float)$fieldvalue;
							$value      = (float)$value;
							if ($fieldvalue > $value) {
								$match_count++;
							}
						}

						break;
					case "<":

						if (is_array($fieldvalue)) {
							$found_match = false;

							foreach ($fieldvalue as $temp_value) {
								$temp_value = (float)$temp_value;
								$value      = (float)$value;

								if ($temp_value < $value) {
									$found_match = true;
								}
							}

							if ($found_match) {
								$match_count++;
							}
						} else {
							$fieldvalue = (float)$fieldvalue;
							$value      = (float)$value;
							if ($fieldvalue < $value) {
								$match_count++;
							}
						}

						break;
					case ">=":

						if (is_array($fieldvalue)) {
							$found_match = false;

							foreach ($fieldvalue as $temp_value) {
								$temp_value = (float)$temp_value;
								$value      = (float)$value;

								if ($temp_value >= $value) {
									$found_match = true;
								}
							}

							if ($found_match) {
								$match_count++;
							}
						} else {
							$fieldvalue = (float)$fieldvalue;
							$value      = (float)$value;
							if ($fieldvalue >= $value) {
								$match_count++;
							}
						}

						break;
					case "<=":

						if (is_array($fieldvalue)) {
							$found_match = false;

							foreach ($fieldvalue as $temp_value) {
								$temp_value = (float)$temp_value;
								$value      = (float)$value;

								if ($temp_value <= $value) {
									$found_match = true;
								}
							}

							if ($found_match) {
								$match_count++;
							}
						} else {
							$fieldvalue = (float)$fieldvalue;
							$value      = (float)$value;
							if ($fieldvalue <= $value) {
								$match_count++;
							}
						}

						break;
					case "!=":

						if (is_array($fieldvalue)) {
							if (!in_array($value, $fieldvalue)) {
								$match_count++;
							}
						} else {
							if ($fieldvalue != $value) {
								$match_count++;
							}
						}

						break;

					case "~":

						if (is_array($fieldvalue)) {
							foreach ($fieldvalue as $temp_value) {
								if (dle_strpos($temp_value, $value, $config['charset']) !== false) {
									$match_count++;
									break;
								}
							}
						} else {
							if (dle_strpos($fieldvalue, $value, $config['charset']) !== false) {
								$match_count++;
							}
						}

						break;
					case "!~":

						if (is_array($fieldvalue)) {
							$found_count = 0;

							foreach ($fieldvalue as $temp_value) {
								if (dle_strpos($temp_value, $value, $config['charset']) === false) {
									$found_count++;
								}
							}

							if ($found_count == count($fieldvalue)) {
								$match_count++;
							}
						} else {
							if (dle_strpos($fieldvalue, $value, $config['charset']) === false) {
								$match_count++;
							}
						}

						break;
					default:

						if (is_array($fieldvalue)) {
							if (in_array($value, $fieldvalue)) {
								$match_count++;
							}
						} else {
							if ($fieldvalue == $value) {
								$match_count++;
							}
						}
				}
			}

			if ($match_count and
				$match_count == count($if_array) and
				$find_type) {
				$matches = $matches[2];
			} elseif ($match_count and !$find_type) {
				$matches = $matches[2];
			} else {
				$matches = '';
			}
		}

		return preg_replace_callback($regex, [&$this, 'if_check'], $matches);
	}

	private function if_category_rating($category) {
		global $cat_info;

		$category = explode(',', $category);

		$found = false;

		foreach ($category as $element) {
			if (isset($cat_info[$element]['rating_type']) && $cat_info[$element]['rating_type'] > -1) {
				return $cat_info[$element]['rating_type'];
			}
		}

		return $found;
	}

	private function ShowRating($rating, $vote_num) {
		global $config;

		if (!$config['rating_type']) {
			if ($rating and $vote_num) {
				$rating = round(($rating / $vote_num), 0);
			} else {
				$rating = 0;
			}

			if ($rating < 0) {
				$rating = 0;
			}

			return $rating * 20;

		} elseif ($config['rating_type'] == "1") {
			if ($rating < 0) {
				$rating = 0;
			}

			return $rating;
		} elseif ($config['rating_type'] == "2" or
			$config['rating_type'] == "3") {

			if ($rating > 0) {
				$rating = "+" . $rating;
			}


		}
		return $rating;
	}

	/**
	 * @param          $content
	 * @param array    $filter
	 *
	 * @return string
	 * @throws \JsonException
	 */
	public function parse_content($content, array $filter = []) {
		global $lang, $_TIME, $PHP_SELF, $cat_info, $config, $user_group, $member_id, $customlangdate, $news_date;

		if (count($this->getContentTags()) === 0) {

			$content = htmlspecialchars_decode($content);

			$sql = $this->sqlBuilder($filter);

			$row = $this->load_data('post', [
											  'sql'   => $sql,
											  'where' => [
												  'news_id' => $this->getPostId()
											  ]
										  ]
			)[0];

			$category_id = (int)$row['category'];
			$row['date'] = strtotime($row['date']);

			$xfields    = xfieldsload();
			$empty_full = false;
			if (strlen($row['full_story']) < 13) {
				$row['full_story'] = $row['short_story'];
				$empty_full        = true;
			}

			$r_content = ($empty_full) ? $row['short_story'] : $row['shor_story'] . $row['full_story'];

			$r_content = str_replace("{PAGEBREAK}", "", $r_content);
			$r_content = preg_replace("'\[page=(.*?)\](.*?)\[/page\]'si", "", $r_content);
			preg_match_all('/\\[pages\\](.*?)\\[\\/pages\\]/', $r_content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$r_content = str_replace($arr, '', $r_content);
				$this->setContentTags($arr, "");
			}
			$r_content = str_replace('{pages}', '', $r_content);

			$this->setContentTags("{PAGEBREAK}", "");

			if ($config['allow_alt_url']) {
				if ($config['seo_type'] == 1 or $config['seo_type'] == 2) {
					if ($category_id and $config['seo_type'] == 2) {
						$c_url = get_url($row['category']);
						if ($c_url) {
							$full_link = $config['http_home_url'] . $c_url . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
						} else {
							$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
						}
					} else {
						$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
					}
				} else {
					$full_link = $config['http_home_url'] . date('Y/m/d/', $row['date']) . $row['alt_name'] . ".html";
				}
			} else {
				$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
			}

			$r_content = preg_replace("#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", "", $r_content);
			$r_content = preg_replace("#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", "", $r_content);
			$r_content = preg_replace("'\[attachment=(.*?)\]'si", "", $r_content);
			$r_content = preg_replace("#\[hide(.*?)\](.+?)\[/hide\]#is", "", $r_content);

			$r_content    = str_replace("><", "> <", $r_content);
			$r_content    = strip_tags($r_content, "<br>");
			$r_content    = trim($r_content);
			$r_content    = preg_replace('/\s+/u', ' ', $r_content);
			$r_content    = preg_replace('#(\A[\s]*<br[^>]*>[\s]*|<br[^>]*>[\s]*\Z)#is', '', $r_content);
			$row['title'] = stripslashes($row['title']);
			$comments_num = $row['comm_num'];

			if (date('Ymd', $row['date']) == date('Ymd', $_TIME)) {
				$this->setContentTags("{date}", $lang['time_heute'] . langdate(", H:i", $row['date']));
			} elseif (date('Ymd', $row['date']) == date('Ymd', ($_TIME - 86400))) {
				$this->setContentTags("{date}", $lang['time_gestern'] . langdate(", H:i", $row['date']));
			} else {
				$this->setContentTags("{date}", $lang['timestamp_active'] . langdate(", H:i", $row['date']));
			}

			// TODO
			$content = preg_replace_callback(
				"#\{date=(.+?)\}#i",
				fn($matches) => langdate(
					$matches[1],
					$news_date,
					!$config['decline_date'], // true, если $config['decline_date'] = false
					$config['decline_date'] ? false : $customlangdate // $customlangdate обрабатывается только при необходимости
				),
				$content
			);


			if ($row['fixed']) {
				$this->setContentTags("[fixed]", "");
				$this->setContentTags("[/fixed]", "");
				preg_match_all('/\\[not-fixed\\](.*?)\\[\\/not-fixed\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			} else {
				$this->setContentTags('[not-fixed]', "");
				$this->setContentTags('[/not-fixed]', "");
				preg_match_all('/\\[fixed\\](.*?)\\[\\/fixed\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			}

			if ($comments_num) {
				if ($row['allow_comm']) {
					$this->setContentTags("[comments]", "");
					$this->setContentTags("[/comments]", "");
				} else {
					preg_match_all('/\\[comments\\](.*?)\\[\\/comments\\]/', $content, $content_array);
					foreach ($content_array[0] as $id => $arr) {
						$this->setContentTags($arr, "");
					}
				}

				preg_match_all('/\\[not-comments\\](.*?)\\[\\/not-comments\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			} else {
				if ($row['allow_comm']) {
					$this->setContentTags("[not-comments]", "");
					$this->setContentTags("[/not-comments]", "");
				} else {
					preg_match_all('/\\[not-comments\\](.*?)\\[\\/not-comments\\]/', $content, $content_array);
					foreach ($content_array[0] as $id => $arr) {
						$this->setContentTags($arr, "");
					}
				}

				preg_match_all('/\\[comments\\](.*?)\\[\\/comments\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			}

			if ($row['votes']) {
				$this->setContentTags("[poll]", "");
				$this->setContentTags("[/poll]", "");

				preg_match_all('/\\[not-poll\\](.*?)\\[\\/not-poll\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			} else {
				$this->setContentTags("[not-poll]", "");
				$this->setContentTags("[/not-poll]", "");

				preg_match_all('/\\[poll\\](.*?)\\[\\/poll\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			}

			if ($row['view_edit'] and $row['editdate']) {
				if (date('Ymd', $row['editdate']) == date('Ymd', $_TIME)) {
					$this->setContentTags("{edit-date}", $lang['time_heute'] . langdate(", H:i", $row['editdate']));
				} elseif (date('Ymd', $row['editdate']) == date('Ymd', ($_TIME - 86400))) {
					$this->setContentTags("{edit-date}", $lang['time_gestern'] . langdate(", H:i", $row['editdate']));
				} else {
					$this->setContentTags("{edit-date}", $lang['timestamp_active'] . langdate(", H:i", $row['editdate']));
				}

				$this->setContentTags("{editor}", $row['editor']);
				$this->setContentTags("{edit-reason}", $row['reason']);

				if ($row['reason']) {
					$this->setContentTags("[edit-reason]", '');
					$this->setContentTags("[/edit-reason]", '');
				} else {
					preg_match_all('/\\[edit-reason\\](.*?)\\[\\/edit-reason\\]/', $content, $content_array);
					foreach ($content_array[0] as $id => $arr) {
						$this->setContentTags($arr, "");
					}
				}
				$this->setContentTags("[edit-date]", '');
				$this->setContentTags("[/edit-date]", '');
			} else {
				$this->setContentTags("{editor}", '');
				$this->setContentTags("{edit-date}", '');
				$this->setContentTags("{edit-reason}", '');

				preg_match_all('/\\[edit-reason\\](.*?)\\[\\/edit-reason\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[edit-date\\](.*?)\\[\\/edit-date\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			}

			if ($config['allow_tags'] and $row['tags']) {
				$this->setContentTags("[tags]", '');
				$this->setContentTags("[/tags]", '');


				$tags         = [];
				$tags_no_link = [];
				$tags_hashtag = [];

				$row['tags'] = explode(",", $row['tags']);

				foreach ($row['tags'] as $value) {
					$value          = trim($value);
					$url_tag        = str_replace(["&#039;", "&quot;", "&amp;"], ["'", '"', "&"], $value);
					$tags_no_link[] = $url_tag;
					$tags_hashtag[] = "#{$url_tag}";

					if ($config['allow_alt_url']) {
						$tags[] = "<a href=\"" . $config['http_home_url'] . "tags/" . rawurlencode($url_tag) . "/\">" . $value . "</a>";
					} else {
						$tags[] = "<a href=\"$PHP_SELF?do=tags&amp;tag=" . rawurlencode($url_tag) . "\">" . $value . "</a>";
					}
				}
				$this->setContentTags("{tags}", implode($this->getTagSeparator(), $tags));
				$this->setContentTags("{tags_no_link}", implode($this->getTagSeparator(), $tags_no_link));
				$this->setContentTags("{hashtags}", implode($this->getHashtagSeparator(), $tags_hashtag));
			} else {
				preg_match_all('/\\[tags\\](.*?)\\[\\/tags\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				$this->setContentTags("{tags}", '');
				$this->setContentTags("{tags_no_link}", '');
				$this->setContentTags("{hashtags}", '');
			}

			if (!$row['category']) {
				$my_cat      = "---";
				$my_cat_link = "---";

				$this->setContentTags('[not-has-category]', "", $content);
				$this->setContentTags('[/not-has-category]', "", $content);
				preg_match_all("'\\[has-category\\](.*?)\\[/has-category\\]'si",
							   $content,
							   $content_array
				);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			} else {
				$my_cat      = [];
				$my_cat_link = [];
				$cat_list    = $row['cats'] = explode($this->getCategorySeparator(), $row['category']);

				$this->setContentTags('[has-category]', "");
				$this->setContentTags('[/has-category]', "");
				preg_match_all("'\\[not-has-category\\](.*?)\\[/not-has-category\\]'si", $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}

				if (count($cat_list) == 1) {
					if ($cat_info[$cat_list[0]]['id']) {
						$my_cat[]    = $cat_info[$cat_list[0]]['name'];
						$my_cat_link = self::get_categories($cat_list[0], $this->getCategorySeparator()
						);
					} else {
						$my_cat_link = "---";
					}
				} else {
					foreach ($cat_list as $element) {
						if ($element and $cat_info[$element]['id']) {
							$my_cat[] = $cat_info[$element]['name'];
							if ($config['allow_alt_url']) {
								$my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url($element) . "/\">{$cat_info[$element]['name']}</a>";
							} else {
								$my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
							}
						}
					}

					if (count($my_cat_link)) {
						$my_cat_link = implode($this->getCategorySeparator(), $my_cat_link);
					} else {
						$my_cat_link = "---";
					}
				}

				if (count($my_cat)) {
					$my_cat = implode($this->getCategorySeparator(), $my_cat);
				} else {
					$my_cat = "---";
				}
			}

			$url_cat = $category_id;

			// TODO
			if (strpos($content, "[catlist=") !== false) {
				$content = preg_replace_callback(
					"#\\[(catlist)=(.+?)\\](.*?)\\[/catlist\\]#is",
					"check_category",
					$content
				);
			}

			// TODO
			if (strpos($content, "[not-catlist=") !== false) {
				$content = preg_replace_callback(
					"#\\[(not-catlist)=(.+?)\\](.*?)\\[/not-catlist\\]#is",
					"check_category",
					$content
				);
			}

			$temp_rating           = $config['rating_type'];
			$config['rating_type'] = $this->if_category_rating($row['category']);

			if ($config['rating_type'] === false) {
				$config['rating_type'] = $temp_rating;
			}

			$category_id = $url_cat;

			if ($category_id and $cat_info[$category_id]['icon']) {
				$this->setContentTags('{category-icon}', '');
				$this->setContentTags('[category-icon]', '');
				$this->setContentTags('[/category-icon]', '');

				preg_match_all('/\\[not-category-icon\\](.*?)\\[\\/not-category-icon\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			} else {
				// TODO
				$this->setContentTags('{category-icon}', '');
				$this->setContentTags('[not-category-icon]', '');
				$this->setContentTags('[/not-category-icon]', '');

				preg_match_all('/\\[category-icon\\](.*?)\\[\\/category-icon\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			}

			if ($category_id) {
				$cats_url = get_url($row['category']);
				if ($cats_url) {
					$cats_url .= "/";
				}

				if ($config['allow_alt_url']) {
					$this->setContentTags('{category-url}', $config['http_home_url'] . $cats_url);
				} else {
					$this->setContentTags('{category-url}', "$PHP_SELF?do=cat&category={$cat_info[$category_id]['alt_name']}");
				}
			} else {
				$this->setContentTags('{category-url}', "#");
			}

			$cat_hashtags = [];
			foreach ($cat_list as $c) {
				$cat_hashtags[] = '#' . str_replace(' ', '_', $cat_info[$c]['name']);
			}

			$this->setContentTags('{comments-num}', number_format($row['comm_num'], 0, ',', ' '));
			$this->setContentTags('{views}', number_format($row['news_read'], 0, ',', ' '));
			$this->setContentTags('{category-hashtag}', implode($this->getTagSeparator(), $cat_hashtags));
			$this->setContentTags('{category}', $my_cat);
			$this->setContentTags('{link-category}', $my_cat_link);
			$this->setContentTags('{news-id}', $row['id']);

			if ($config['rating_type'] == "1") {
				$this->setContentTags('[rating-type-2]', '');
				$this->setContentTags('[/rating-type-2]', '');
				preg_match_all('/\\[rating-type-1\\](.*?)\\[\\/rating-type-1\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-type-3\\](.*?)\\[\\/rating-type-3\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-type-4\\](.*?)\\[\\/rating-type-4\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			} elseif ($config['rating_type'] == "2") {
				$this->setContentTags('[rating-type-3]', '');
				$this->setContentTags('[/rating-type-3]', '');
				preg_match_all('/\\[rating-type-1\\](.*?)\\[\\/rating-type-1\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-type-2\\](.*?)\\[\\/rating-type-2\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-type-4\\](.*?)\\[\\/rating-type-4\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			} elseif ($config['rating_type'] == "3") {
				$this->setContentTags('[rating-type-4]', '');
				$this->setContentTags('[/rating-type-4]', '');
				preg_match_all('/\\[rating-type-1\\](.*?)\\[\\/rating-type-1\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-type-3\\](.*?)\\[\\/rating-type-3\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-type-2\\](.*?)\\[\\/rating-type-2\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			} else {
				$this->setContentTags('[rating-type-1]', '');
				$this->setContentTags('[/rating-type-1]', '');
				preg_match_all('/\\[rating-type-2\\](.*?)\\[\\/rating-type-2\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-type-3\\](.*?)\\[\\/rating-type-3\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-type-4\\](.*?)\\[\\/rating-type-4\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			}

			if ($row['allow_rate']) {
				$dislikes = ($row['vote_num'] - $row['rating']) / 2;
				$likes    = $row['vote_num'] - $dislikes;
				$this->setContentTags(
					[
						'[/rating]',
						'{rating}',
						'[rating]',
						'{vote-num}',
						'{dislikes}',
						'{likes}',
					],
					[
						'',
						$this->ShowRating(
							$row['rating'],
							$row['vote_num']
						),
						'',
						$row['vote_num'],
						$dislikes,
						$likes,
					]
				);


				$ratingscore = 0;

				if ($row['vote_num']) {
					$ratingscore = str_replace(',', '.', round(($row['rating'] / $row['vote_num']), 1));
				}

				$this->setContentTags('{ratingscore}', $ratingscore);

				if ($user_group[$member_id['user_group']]['allow_rating']) {
					if ($config['rating_type']) {
						$this->setContentTags([
												  '[rating-plus]',
												  '[/rating-plus]',
											  ], '');

						if ($config['rating_type'] == "2" or
							$config['rating_type'] == "3") {
							$this->setContentTags([
													  '[rating-minus]',
													  '[/rating-minus]',
												  ], '');
						} else {
							preg_match_all('/\\[rating-minus\\](.*?)\\[\\/rating-minus\\]/', $content, $content_array);
							foreach ($content_array[0] as $id => $arr) {
								$this->setContentTags($arr, "");
							}
						}
					} else {
						preg_match_all('/\\[rating-minus\\](.*?)\\[\\/rating-minus\\]/', $content, $content_array);
						foreach ($content_array[0] as $id => $arr) {
							$this->setContentTags($arr, "");
						}
						preg_match_all('/\\[rating-plus\\](.*?)\\[\\/rating-plus\\]/', $content, $content_array);
						foreach ($content_array[0] as $id => $arr) {
							$this->setContentTags($arr, "");
						}
					}
				} else {
					preg_match_all('/\\[rating-minus\\](.*?)\\[\\/rating-minus\\]/', $content, $content_array);
					foreach ($content_array[0] as $id => $arr) {
						$this->setContentTags($arr, "");
					}
					preg_match_all('/\\[rating-plus\\](.*?)\\[\\/rating-plus\\]/', $content, $content_array);
					foreach ($content_array[0] as $id => $arr) {
						$this->setContentTags($arr, "");
					}
				}
			} else {
				$this->setContentTags(
					[
						'{rating}',
						'{vote-num}',
						'{dislikes}',
						'{likes}',
						'{ratingscore}',
					],
					''
				);

				preg_match_all('/\\[rating\\](.*?)\\[\\/rating\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-minus\\](.*?)\\[\\/rating-minus\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
				preg_match_all('/\\[rating-plus\\](.*?)\\[\\/rating-plus\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			}

			$config['rating_type'] = $temp_rating;

			preg_match_all('/\\[comments-subscribe\\](.*?)\\[\\/comments-subscribe\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$this->setContentTags($arr, "");
			}

			if ($config['allow_alt_url']) {
				$go_page = $config['http_home_url'] . "user/" . urlencode($row['autor']) . "/";
				$this->setContentTags('[day-news]', "<a href=\"" . $config['http_home_url'] . date('Y/m/d/', $row['date']) . "\" >");
			} else {
				$go_page = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode($row['autor']);
				$this->setContentTags('[day-news]', "<a href=\"$PHP_SELF?year=" . date('Y', $row['date']) . "&amp;month=" . date('m', $row['date']) . "&amp;day=" . date('d', $row['date']) . "\" >");
			}

			$this->setContentTags(
				[
					'{full-link}',
					'[full-link]',
					'{login}',
					'{author}',
					'[profile]',
				],
				[
					$full_link,
					"<a href=\"" . $full_link . "\">",
					$row['autor'],
					"<a href=\"" . $go_page . "\">" . $row['autor'] . "</a>",
					"<a href=\"" . $go_page . "\">",
				]
			);

			$this->setContentTags(
				['[/full-link]',
				 '[/profile]',
				 '[/day-news]'],
				"</a>"
			);

			if ($row['allow_comm']) {
				$this->setContentTags(
					['[com-link]', '[/com-link]'],
					["<a id=\"dle-comm-link\" href=\"" . $full_link . "#comment\">", "</a>",]
				);
			} else {
				preg_match_all('/\\[com-link\\](.*?)\\[\\/com-link\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$this->setContentTags($arr, "");
				}
			}

			preg_match_all('/\\[edit\\](.*?)\\[\\/edit\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$this->setContentTags($arr, "");
			}

			preg_match_all('/\\[complaint\\](.*?)\\[\\/complaint\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$this->setContentTags($arr, "");
			}

			$this->setContentTags(['[/complaint]', '[complaint]'], "");
			$this->setContentTags(['{favorites}', '{poll}'], "");

			preg_match_all('/\\[add-favorites\\](.*?)\\[\\/add-favorites\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$this->setContentTags($arr, "");
			}
			preg_match_all('/\\[del-favorites\\](.*?)\\[\\/del-favorites\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$this->setContentTags($arr, "");
			}
			preg_match_all('/\\[complaint\\](.*?)\\[\\/complaint\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$this->setContentTags($arr, "");
			}
			preg_match_all('/\\[banner_(.*?)\\](.*?)\\[\\/banner_(.*?)\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$this->setContentTags($arr, "");
			}
			preg_match_all('/{banner_(.*?)}/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$this->setContentTags($arr, "");
			}

			$row['short_story'] = stripslashes($row['short_story']);
			$row['full_story']  = stripslashes($row['full_story']);
			$row['xfields']     = stripslashes($row['xfields']);

			if (stripos($content, "{image-") !== false) {
				$images = [];
				preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['short_story'] . $row['xfields'], $media);
				$data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

				foreach ($data as $url) {
					$info = pathinfo($url);
					if (isset($info['extension'])) {
						if ($info['filename'] == "spoiler-plus" ||
							$info['filename'] == "spoiler-minus" ||
							strpos($info['dirname'], 'engine/data/emoticons') !== false
						) continue;
						$info['extension'] = strtolower($info['extension']);
						if (($info['extension'] == 'jpg')
							|| ($info['extension'] == 'jpeg')
							|| ($info['extension'] == 'gif')
							|| ($info['extension'] == 'png')
							|| ($info['extension'] == 'webp')
						)
							$images[] = $url;

					}
				}

				if (count($images)) {
					$i = 0;
					foreach ($images as $url) {
						$i++;
						$this->setContentTags('{image-' . $i . '}', $url);
						$this->setContentTags([
												  '[image-' . $i . ']',
												  '[/image-' . $i . ']',
											  ], "");
						// TODO
						$content = preg_replace("#\[not-image-{$i}\](.+?)\[/not-image-{$i}\]#is", "", $content);
					}
				}

				// TODO
				$content = preg_replace("#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $content);
				$content = preg_replace("#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $content);
				$content = preg_replace("#\[not-image-(.+?)\]#i", "", $content);
				$content = preg_replace("#\[/not-image-(.+?)\]#i", "", $content);

				$this->setAllImages($images);
				$this->setImagesPost($images);
			}

			if (stripos($content, "{fullimage-") !== false) {
				$images = [];
				preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['full_story'], $media);
				$data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

				foreach ($data as $url) {
					$info = pathinfo($url);
					if (isset($info['extension'])) {
						if ($info['filename'] == "spoiler-plus" ||
							$info['filename'] == "spoiler-minus" ||
							strpos($info['dirname'], 'engine/data/emoticons') !== false
						) continue;

						$info['extension'] = strtolower($info['extension']);
						if (($info['extension'] == 'jpg')
							|| ($info['extension'] == 'jpeg')
							|| ($info['extension'] == 'gif')
							|| ($info['extension'] == 'png')
							|| ($info['extension'] == 'webp')
						)
							$images[] = $url;

					}
				}

				if (count($images)) {
					$i = 0;
					foreach ($images as $url) {
						$i++;
						$this->setContentTags('{fullimage-' . $i . '}', $url);
						$this->setContentTags([
												  '[fullimage-' . $i . ']',
												  '[/fullimage-' . $i . ']',
											  ], "");
					}
				}

				// TODO
				$content = preg_replace("#\[fullimage-(.+?)\](.+?)\[/fullimage-(.+?)\]#is", "", $content);
				$content = preg_replace("#\\{fullimage-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $content);

				$this->setAllImages($images);
				$this->setImagesPost($images);
			}

			$this->setContentTags([
									  '{addcomments}',
									  '{navigation}',
									  '{comments}',
								  ], "");

			if (count($xfields)) {
				$row['xfields_array'] = xfieldsdataload($row['xfields']);
			}

			$content = $this->if_check($content);

			if (count($xfields)) {
				$xfieldsdata = $row['xfields_array'];
				foreach ($xfields as $value) {
					$preg_safe_name = preg_quote($value[0], "'");
					if ($value[20]) {
						$value[20] = explode(',', $value[20]);
						if ($value[20][0] and
							!in_array($member_id['user_group'], $value[20])) {
							$xfieldsdata[$value[0]] = "";
						}
					}
					if ($value[3] == "yesorno") {
						if ((int)$xfieldsdata[$value[0]]) {
							$xfgiven                = true;
							$xfieldsdata[$value[0]] = $lang['xfield_xyes'];
						} else {
							$xfgiven                = false;
							$xfieldsdata[$value[0]] = $lang['xfield_xno'];
						}
					} else {
						if ($xfieldsdata[$value[0]] == "") {
							$xfgiven = false;
						} else {
							$xfgiven = true;
						}
					}

					if (!$xfgiven) {
						// TODO
						$content = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $content);
						$content = str_ireplace("[xfnotgiven_{$value[0]}]", "", $content);
						$content = str_ireplace("[/xfnotgiven_{$value[0]}]", "", $content);
					} else {
						// TODO
						$content = preg_replace("'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $content);
						$content = str_ireplace("[xfgiven_{$value[0]}]", "", $content);
						$content = str_ireplace("[/xfgiven_{$value[0]}]", "", $content);
					}

					// TODO
					if (strpos($content, "[ifxfvalue {$value[0]}") !== false) {
						$content = preg_replace_callback("#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "check_xfvalue", $content);
					}

					if ($value[3] == "select") {
						if (!$xfieldsdata[$value[0]]) {
							$this->setContentTags([
													  "[xfvalue_tagvalue_{$value[0]}]",
													  "[xfvalue_tagvalue_url_{$value[0]}]",
												  ], '');
						} else {
							$xf_val      = [];
							$xf_val_url  = [];
							$xf_splitted = explode(',',
												   $xfieldsdata[$value[0]]
							);
							foreach (
								preg_split("/((\r?\n)|(\r\n?))/", $value[4]) as
								$line
							) {
								$select_values = explode('|', $line);
								$tag_name      = $select_values[0];
								$tag_val       = $tag_name;

								foreach ($xf_splitted as $xf) {
									if ($tag_name == $xf) {
										if (count($select_values) > 1) {
											$tag_val = $select_values[1];
										}
										$tag_val = trim($tag_val);

										$xf_val[] = $tag_val;
										if ($value[6]) {
											$value4 = str_replace(
												['&#039;', '&quot;', '&amp;', '&#123;', '&#91;', '&#58;',],
												["'", '"', '&', '{', '[', ':'],
												$tag_name
											);

											if ($config['allow_alt_url']) {
												$xf_val_url[] = "<a href=\"" .
													$config['http_home_url'] . "xfsearch/" . $value[0] . "/" . rawurlencode($value4) . "/\">" . $tag_val . '</a>';
											} else {
												$xf_val_url[]
													= "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=" . $value[0] . "&amp;xf=" . rawurlencode($value4) . "\">" . $tag_val . '</a>';
											}
										} else {
											$xf_val_url[] = $tag_val;
										}
									}
								}
							}

							$sep_sign = (empty($value[21])) ? $this->getTagSeparator() : $value[21];
							$this->setContentTags(
								[
									"[xfvalue_tagvalue_{$value[0]}]",
									"[xfvalue_tagvalue_url_{$value[0]}]",
								],
								[
									implode($sep_sign, $xf_val),
									implode($sep_sign, $xf_val_url),
								]
							);
						}
					} else {
						$this->setContentTags([
												  "[xfvalue_tagvalue_{$value[0]}]",
												  "[xfvalue_tagvalue_url_{$value[0]}]",
											  ], '');
					}

					$xfieldsdata["{$value[0]}_text"]    = '';
					$xfieldsdata["{$value[0]}_hashtag"] = '';

					if ($value[6] and !empty($xfieldsdata[$value[0]])) {
						$temp_array     = explode(",", $xfieldsdata[$value[0]]);
						$value3         = [];
						$value3_no_link = [];
						$value3_hashtag = [];

						foreach ($temp_array as $value2) {
							$value2 = trim($value2);
							if ($value2) {
								$value4 = str_replace(
									["&#039;", "&quot;", "&amp;", "&#123;", "&#91;", "&#58;",],
									["'", '"', "&", "{", "[", ":"],
									$value2
								);
								if ($value[3] == "datetime") {
									$value2 = strtotime($value4);
									if (!trim($value[24])) {
										$value[24] = $config['timestamp_active'];
									}
									if ($value[25]) {
										if ($value[26]) {
											$value2 = langdate($value[24], $value2);
										} else {
											$value2 = langdate($value[24], $value2, false, $customlangdate);
										}
									} else {
										$value2 = date($value[24], $value2);
									}
								}

								if ($config['allow_alt_url']) {
									$value3[]
										= "<a href=\"" . $config['http_home_url'] . "xfsearch/" . $value[0] . "/" . rawurlencode($value4) . "/\">" . $value2 . "</a>";
								} else {
									$value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=" . $value[0] . "&amp;xf=" . rawurlencode($value4) . "\">" . $value2 . "</a>";
								}
								$value3_no_link[] = $value2;
								$value3_hashtag[] = '#' . str_replace(' ', '_', $value2);
							}
						}

						if (empty($value[21])) {
							$value[21] = $this->getTagSeparator();
						}
						$xfieldsdata[$value[0]]             = implode($value[21], $value3);
						$xfieldsdata["{$value[0]}_text"]    = implode($value[21], $value3_no_link);
						$xfieldsdata["{$value[0]}_hashtag"] = implode($this->getTagSeparator(), $value3_hashtag);

						unset($temp_array);
						unset($value2);
						unset($value3);
						unset($value3_no_link);
						unset($value3_no_link);
						unset($value4);
					} elseif ($value[3] == "datetime" && !empty($xfieldsdata[$value[0]])) {
						$xfieldsdata[$value[0]] = strtotime(str_replace("&#58;", ":", $xfieldsdata[$value[0]]));

						if (!trim($value[24])) {
							$value[24] = $config['timestamp_active'];
						}

						if ($value[25]) {
							if ($value[26]) {
								$xfieldsdata[$value[0]] = langdate($value[24], $xfieldsdata[$value[0]]);
							} else {
								$xfieldsdata[$value[0]] = langdate($value[24], $xfieldsdata[$value[0]], false, $customlangdate);
							}
						} else {
							$xfieldsdata[$value[0]] = date($value[24], $xfieldsdata[$value[0]]);
						}
					}

					if ($value[3] == "image" and $xfieldsdata[$value[0]]) {
						$temp_array = explode('|', $xfieldsdata[$value[0]]);

						if (count($temp_array) > 1) {
							$temp_alt   = $temp_array[0];
							$temp_value = $temp_array[1];
						} else {
							$temp_alt   = '';
							$temp_value = $temp_array[0];
						}

						$path_parts = @pathinfo($temp_value);

						if ($value[12] && file_exists(ROOT_DIR . "/uploads/posts/" . $path_parts['dirname'] . "/thumbs/" . $path_parts['basename'])) {
							$thumb_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname'] . "/thumbs/" . $path_parts['basename'];
							$img_url   = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname'] . "/" . $path_parts['basename'];
						} else {
							$img_url   = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname'] . "/" . $path_parts['basename'];
							$thumb_url = "";
						}

						$this->setContentTags(
							["[xfvalue_thumb_url_{$value[0]}]", "[xfvalue_image_url_{$value[0]}]",],
							[$thumb_url, $img_url]

						);
						$this->setXfImages($img_url, $value[0]);
						$xfieldsdata[$value[0]] = "<a href=\"$img_url\">$temp_alt</a>";
					}

					if ($value[3] == "image" and !$xfieldsdata[$value[0]]) {
						$this->setContentTags(
							[
								"[xfvalue_thumb_url_{$value[0]}]",
								"[xfvalue_image_url_{$value[0]}]",
							], ''
						);
					}

					if ($value[3] == "imagegalery" &&
						$xfieldsdata[$value[0]] &&
						stripos($content, "[xfvalue_{$value[0]}") !== false
					) {
						$fieldvalue_arr       = explode(',', $xfieldsdata[$value[0]]);
						$gallery_image        = [];
						$gallery_single_image = [];
						$xf_image_count       = 0;
						$single_need          = false;

						if (stripos($content, "[xfvalue_{$value[0]} image=") !== false)
							$single_need = true;

						foreach ($fieldvalue_arr as $temp_value) {
							$xf_image_count++;

							$temp_value = trim($temp_value);

							if ($temp_value == "") continue;

							$temp_array = explode('|', $temp_value);

							if (count($temp_array) > 1) {
								$temp_alt   = $temp_array[0];
								$temp_value = $temp_array[1];
							} else {
								$temp_alt   = '';
								$temp_value = $temp_array[0];
							}

							$path_parts = @pathinfo($temp_value);

							$img_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname'] . "/" . $path_parts['basename'];

							// Gallery links
							// @version 1.7.7
							$gallery_image[]                                                                     = $img_url;
							$gallery_single_image['[xfvalue_' . $value[0] . ' image="' . $xf_image_count . '"]'] = $img_url;
						}

						if ($single_need && count($gallery_single_image)) {
							foreach ($gallery_single_image as $temp_key => $temp_value) {
								$this->setContentTags($temp_key, $temp_value);
							}
						}
						$this->setAllImages($gallery_image);
						$this->setXfImages($gallery_image);

						$xfieldsdata[$value[0]] = implode(", ", $gallery_image);
					}
					$this->setContentTags(
						[
							"[xfvalue_{$value[0]}]",
							"[xfvalue_{$value[0]}_text]",
							"[xfvalue_{$value[0]}_hashtag]",
						],
						[
							$xfieldsdata[$value[0]],
							$xfieldsdata["{$value[0]}_text"],
							$xfieldsdata["{$value[0]}_hashtag"],
						]
					);

					if (preg_match("#\\[xfvalue_{$preg_safe_name} limit=['\"]?(\d+)['\"]?\\]#i", $content, $matches)) {
						$count = (int)$matches[1];

						$xfieldsdata[$value[0]] = str_replace("><", "> <", $xfieldsdata[$value[0]]);
						$xfieldsdata[$value[0]] = strip_tags($xfieldsdata[$value[0]], "<br>");
						$xfieldsdata[$value[0]] = trim($xfieldsdata[$value[0]]);
						$xfieldsdata[$value[0]] = preg_replace('/\s+/u', ' ', $xfieldsdata[$value[0]]);

						if ($count && dle_strlen($xfieldsdata[$value[0]], $config['charset']) > $count) {
							$xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $count, $config['charset']);

							if ((
							$temp_dmax = dle_strrpos($xfieldsdata[$value[0]], ' ', $config['charset']))) {
								$xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $temp_dmax, $config['charset']);
							}
						}

						$this->setContentTags($matches[0], $xfieldsdata[$value[0]]);
					}
				}
			}


			$this->setContentTags(
				['{short-story}', '{full-story}'],
				[strip_tags($row['short_story']), strip_tags($row['full_story'])]);

			if (preg_match("#\\{full-story limit=['\"]?(\d+)['\"]?\\}#i", $content, $matches)) {
				$count = (int)$matches[1];

				if ($count && dle_strlen($row['full_story'], $config['charset']) > $count) {
					$row['full_story'] = dle_substr($row['full_story'], 0, $count, $config['charset']);

					if ((
					$temp_dmax = dle_strrpos($row['full_story'], ' ', $config['charset']))) {
						$row['full_story'] = dle_substr($row['full_story'], 0, $temp_dmax, $config['charset']);
					}
				}
				$this->setContentTags($matches[0], $row['full_story']);
			}

			if (preg_match("#\\{short-story limit=['\"]?(\d+)['\"]?\\}#i", $content, $matches)) {
				$count = (int)$matches[1];

				if ($count && dle_strlen($row['short_story'], $config['charset']) > $count) {
					$row['short_story'] = dle_substr($row['short_story'], 0, $count, $config['charset']);

					if ((
					$temp_dmax = dle_strrpos($row['short_story'], ' ', $config['charset']))) {
						$row['short_story'] = dle_substr($row['short_story'], 0, $temp_dmax, $config['charset']);
					}
				}
				$this->setContentTags($matches[0], $row['short_story']);
			}

			$this->setContentTags('{title}', str_replace("&amp;amp;", "&amp;", htmlspecialchars($row['title'], ENT_QUOTES, $config['charset'])));

			$this->setPostTitle(str_replace("&amp;amp;", "&amp;", htmlspecialchars($row['title'], ENT_QUOTES, $config['charset'])));

			if (preg_match("#\\{title limit=['\"]?(\d+)['\"]?\\}#i", $content, $matches)) {
				$count        = (int)$matches[1];
				$row['title'] = strip_tags($row['title']);

				if ($count && dle_strlen($row['title'], $config['charset']) > $count) {
					$row['title'] = dle_substr($row['title'], 0, $count, $config['charset']);

					if ((
					$temp_dmax = dle_strrpos($row['title'], ' ', $config['charset'])))
						$row['title'] = dle_substr($row['title'], 0, $temp_dmax, $config['charset']);
				}
				$this->setContentTags($matches[0], str_replace("&amp;amp;", "&amp;", htmlspecialchars($row['title'], ENT_QUOTES, $config['charset'])));
			}

			$content = preg_replace("#\\{THEME\\}#i", "{$config['http_home_url']}/templates/{$config['skin']}", $content);
		}

		$this->setContentTags('{now}', date('d.m.y, H:i', $_TIME));

		foreach ($this->getContentTags() as $tag => $value)
			$content = str_replace($tag, $value, $content);

		$content = str_replace(
			[
				"&lt;",
				"&gt;",
				"<p>",
				"</p>",
				"[b]",
				"[/b]",
				"[/code]",
				"[code]",
				"[/i]",
				"[i]",
			],
			[
				"<",
				">",
				"",
				"<br>",
				"<b>",
				"</b>",
				"</code>",
				"<code>",
				"</i>",
				"<i>",
			],
			$content
		);

		$content = preg_replace("/\[url=(.*)\](.*)\[\/url\]/", "<a href=\"$1\">$2</a>", $content);
		$content = preg_replace("/\[url\](.*)\[\/url\]/", "<a href=\"$1\">$1</a>", $content);

		$content = preg_replace_callback("#\{now=(.+?)\}#i", function ($matches = []) use ($_TIME) { return date($matches[1], $_TIME); }, $content);

		$this->getFiles();
		$this->getImages();

		return $content;
	}

	/**
	 * Сбор файлов новости из базы данных
	 *
	 * @return array
	 * @throws \JsonException
	 */
	public function getFiles() {

		$files = $this->load_data('files',
								  [
									  'table' => 'files',
									  'where' => ['news_id' => $this->getPostId()],
								  ]
		);
		foreach ($files as $id => $file) {
			$file_path = ROOT_DIR . "/uploads/files/{$file['onserver']}";

			$file_in_arr = array_search($file_path, array_column($this->files, 'url'));

			if ($file_in_arr === false) {
				$this->files[] = [
					'url'      => $file_path,
					'size'     => (int)$file['size'],
					'checksum' => (int)$file['checksum'],
				];
			}
		}

		return $this->files;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function unsetFileById($id) {
		unset($this->files[$id]);
	}

	/**
	 * Собирает все изображения новости из базы данных,
	 * если их нет в массиве, то добавляет изображение в него
	 *
	 * @return array
	 * @throws \JsonException
	 */
	public function getImages() {
		$images = $this->load_data('images',
								   [
									   'table' => 'images',
									   'where' => ['news_id' => $this->getPostId()],
								   ]
		);

		foreach ($images as $id => $image) {
			$file_path = ROOT_DIR . "/uploads/posts/";
			$imgs      = explode("||", $image['images']);
			foreach ($imgs as $img) {
				$im       = explode("|", $img);
				$img_src  = $im[0];
				$img_info = pathinfo($img_src);
				if (!isset($img_info['extension'])) {
					$img_src = $im[1];
				}
				$img_file = "{$file_path}{$img_src}";
				if (!in_array($img_file, $this->images)) {
					$this->images[] = $img_file;
				}
			}
		}

		return $this->images;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param $title
	 *
	 * @return mixed
	 */
	public function setPostTitle($title) {
		$this->post_title = $title;

		return $this->post_title;
	}

	/**
	 * @return mixed
	 */
	public function getPostTitle() {
		return $this->post_title;
	}

	/**
	 * @param           $url
	 * @param array     $post
	 * @param null      $proxy
	 * @param string    $type
	 * @param null      $auth
	 *
	 * @return bool|string
	 * @throws \Monolog\Handler\MissingExtensionException
	 */
	public function send(
		$url,
		array $post = [],
		$proxy = null,
		string $type = 'http',
		$auth = null
	) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($type === "socks") {
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		}
		if ($proxy !== null) {
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		}
		if ($auth !== null) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-Type:multipart/form-data",
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$content = curl_exec($ch);
		curl_close($ch);

		try {
			LogGenerator::generateLog(
				'telegram',
				'send',
				[
					'content' => $content,
					'url'     => $url,
					'post'    => $post,
					'proxy'   => $proxy,
					'type'    => $type,
					'auth'    => $auth,
				],
				'info'
			);
		} catch (Exception $e) {
			LogGenerator::generateLog(
				'telegram', 'send', [
							  'content' => $content,
							  'url'     => $url,
							  'post'    => $post,
							  'proxy'   => $proxy,
							  'type'    => $type,
							  'auth'    => $auth,
							  'error'   => $e->getMessage(),
						  ]
			);
		}

		return $content;
	}

	public function getContentType() {
		return $this->content_type;
	}

	public function setContentType($type) {
		$this->content_type = $type;

		return $this->content_type;
	}

	public function getPostId() {
		return $this->post_id;
	}

	/**
	 * @param array|string $xf_images
	 * @param string|null  $param
	 *
	 * @return array
	 */
	public function setXfImages($xf_images, string $param = null) {
		if (is_array($xf_images)) {
			$this->xf_images = array_merge($this->getXfImages(), $xf_images);
		} else {
			if ($param !== null) {
				$this->xf_images[$param][] = $xf_images;
			} else {
				$this->xf_images[] = $xf_images;
			}
		}

		return $this->getXfImages();
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function unsetXfImages($id) {
		unset($this->xf_images[$id]);
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param int    $id
	 * @param string $img
	 *
	 * @return void
	 */
	public function setXfImagesById($id, $img) {
		$this->xf_images[$id] = $img;
	}

	/**
	 * @return array
	 */
	public function getXfImages() {
		return $this->xf_images;
	}

	/**
	 * @param array|string $images_post
	 *
	 * @return array
	 */
	public function setImagesPost($images_post) {
		if (is_array($images_post)) {
			$this->images_post = array_merge($this->getImagesPost(), $images_post);
		} else {
			$this->images_post[] = $images_post;
		}

		return $this->getImagesPost();
	}

	/**
	 * @return array
	 */
	public function getImagesPost() {
		return $this->images_post;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function unsetPostImages($id) {
		unset($this->images_post[$id]);
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param int    $id
	 * @param string $img
	 *
	 * @return void
	 */
	public function setPostImagesById($id, $img) {
		$this->images_post[$id] = $img;
	}

	/**
	 * @param array|string $videos
	 *
	 * @return array
	 */
	public function setVideos($videos) {
		if (is_array($videos)) {
			$this->videos = array_merge($this->getVideos(), $videos);
		} else {
			$this->videos[] = $videos;
		}

		return $this->getVideos();
	}

	/**
	 * @return array
	 */
	public function getVideos() {
		return $this->videos;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 * @param $video
	 *
	 * @return void
	 */
	public function setVideoById($id, $video) {
		$this->videos[$id] = $video;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function unsetVideoById($id) {
		unset($this->videos[$id]);
	}

	/**
	 * @param array|string $audios
	 *
	 * @return array
	 */
	public function setAudios($audios) {
		if (is_array($audios)) {
			$this->audios = array_merge($this->getAudios(), $audios);
		} else {
			$this->audios[] = $audios;
		}

		return $this->getAudios();
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 * @param $audio
	 *
	 * @return void
	 */
	public function setAudioById($id, $audio) {
		$this->audios[$id] = $audio;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function unsetAudioById($id) {
		unset($this->audios[$id]);
	}

	/**
	 * @return array
	 */
	public function getAudios() {
		return $this->audios;
	}

	/**
	 * @param array|string $xf_videos
	 *
	 * @return array
	 */
	public function setXfVideos($xf_videos, string $param = null) {
		if (is_array($xf_videos)) {
			$this->xf_videos = array_merge($this->getXfVideos(),
										   $xf_videos
			);
		} else {
			if ($param !== null) {
				$this->xf_videos[$param][] = $xf_videos;
			} else {
				$this->xf_videos[] = $xf_videos;
			}
		}

		return $this->getXfVideos();
	}

	/**
	 * @return array
	 */
	public function getXfVideos() {
		return $this->xf_videos;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 * @param $video
	 *
	 * @return void
	 */
	public function setXfVideoById($id, $video) {
		$this->xf_videos[$id] = $video;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function unsetXfVideoById($id) {
		unset($this->xf_videos[$id]);
	}


	/**
	 * @param array|string $xf_audios
	 *
	 * @return array
	 */
	public function setXfAudios($xf_audios, string $param = null) {
		if (is_array($xf_audios)) {
			$this->xf_audios = array_merge($this->getXfAudios(),
										   $xf_audios
			);
		} else {
			if ($param !== null) {
				$this->xf_audios[$param][] = $xf_audios;
			} else {
				$this->xf_audios[] = $xf_audios;
			}
		}

		return $this->getXfAudios();
	}

	/**
	 * @return array
	 */
	public function getXfAudios() {
		return $this->xf_audios;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 * @param $audio
	 *
	 * @return void
	 */
	public function setXfAudioById($id, $audio) {
		$this->audios[$id] = $audio;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function unsetXfAudioById($id) {
		unset($this->audios[$id]);
	}

	/**
	 * @param array|string $xf_files
	 *
	 * @return array
	 */
	public function setXfFiles($xf_files, string $param = null) {
		if (is_array($xf_files)) {
			$this->xf_files = array_merge($this->getXfFiles(), $xf_files);
		} else {
			if ($param !== null) {
				$this->xf_files[$param][] = $xf_files;
			} else {
				$this->xf_files[] = $xf_files;
			}
		}

		return $this->getXfFiles();
	}

	/**
	 * @return array
	 */
	public function getXfFiles() {
		return $this->xf_files;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 * @param $file
	 *
	 * @return void
	 */
	public function setXfFileById($id, $file) {
		$this->xf_files[$id] = $file;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param $id
	 *
	 * @return void
	 */
	public function unsetXfFileById($id) {
		unset($this->xf_files[$id]);
	}

	/**
	 * @param string $allowed_html
	 */
	public function setAllowedHtml($allowed_html) {
		$this->allowed_html = $allowed_html;
	}

	/**
	 * @return string
	 */
	public function getAllowedHtml() {
		return $this->allowed_html;
	}

	/**
	 * @return array
	 */
	public function getAllImages() {
		return $this->images;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param int $id
	 *
	 * @return void
	 */
	public function unsetAllImages($id) {
		unset($this->images[$id]);
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param int    $id
	 * @param string $img
	 *
	 * @return void
	 */
	public function setAllImagesById($id, $img) {
		$this->images[$id] = $img;
	}

	/**
	 * @version 1.7.3
	 *
	 * @param array|string $image
	 *
	 * @return array
	 */
	public function setAllImages($image) {
		if (is_array($image)) {
			$this->images = array_merge($this->getAllImages(), $image);
		} else {
			$this->images[] = $image;
		}

		return $this->getAllImages();
	}

	/**
	 * @param $filter
	 *
	 * @return string
	 */
	protected function sqlBuilder($filter = []) {
		global $config;

		$where = [
			'p.id = e.news_id',
			"p.id = {$this->getPostId()}",
		];
		if (!empty($filter['fields'])) {
			$where[] = "({$filter['fields']})";
		}
		$where = implode(' AND ', $where);

		$join = '';
		if ($config['allow_multi_category'] && $filter['cats']) {
			$join
				= "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('{$filter['cats']}')) c ON (p.id=c.news_id)";
		}

		return 'SELECT * FROM ' . PREFIX . '_post p LEFT JOIN ' . PREFIX . "_post_extras e on (p.id = e.news_id) {$join} WHERE {$where}";
	}

	/**
	 * Функция DLE
	 *
	 * @param $id
	 * @param $separator
	 *
	 * @return string|void
	 */
	protected function get_categories($id, $separator = " &raquo;") {
		global $cat_info, $config, $PHP_SELF;

		if (!$id) {
			return;
		}

		$parent_id = $cat_info[$id]['parentid'];

		if ($config['allow_alt_url']) {
			$list = "<a href=\"" . $config['http_home_url'] . get_url($id) . "/\">{$cat_info[$id]['name']}</a>";
		} else {
			$list = "<a href=\"$PHP_SELF?do=cat&amp;category={$cat_info[$id]['alt_name']}\">{$cat_info[$id]['name']}</a>";
		}

		while ($parent_id) {
			if ($config['allow_alt_url']) {
				$list = "<a href=\"" . $config['http_home_url'] . get_url($parent_id) . "/\">{$cat_info[$parent_id]['name']}</a>" . $separator . $list;
			} else {
				$list = "<a href=\"$PHP_SELF?do=cat&amp;category={$cat_info[$parent_id]['alt_name']}\">{$cat_info[$parent_id]['name']}</a>" . $separator . $list;
			}

			$parent_id = $cat_info[$parent_id]['parentid'];

			if (!isset($cat_info[$parent_id]['id']) || (isset($cat_info[$parent_id]['id']) && !$cat_info[$parent_id]['id'])) break;

			if ($parent_id) {
				if ($cat_info[$parent_id]['parentid'] == $cat_info[$parent_id]['id']) break;
			}
		}

		return $list;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 * @return array
	 */
	public function getContentTags($type = null) {
		if ($type !== null) return $this->content_tags[$type];

		return $this->content_tags;
	}

	/**
	 * @version 1.7.7
	 * @since   1.7.7
	 *
	 * @param array|string      $tag
	 * @param null|string|array $value
	 */
	public function setContentTags($tag, $value = null) {
		if (is_array($tag)) {
			if ($value !== null) {
				for (
					$i = 0,
					$m = count($tag); $i < $m; $i++) {
					$this->setContentTags($tag[$i], is_array($value) ? $value[$i] : $value);
				}
			} else {
				foreach ($tag as $t => $v) $this->setContentTags($t, $v);
			}
		} else
			$this->content_tags[$tag] = $value;
	}


}
