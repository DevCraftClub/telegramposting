<?php


class RePost {
	private $content, $content_type, $post_id, $post_title;

	/**
	 * RePost constructor.
	 *
	 * @param $c
	 * @param $post_id
	 */
	public function __construct($c, $post_id) {
		$this->setPostId($post_id);
		$this->content_type = $c;
	}

	/**
	 * @param mixed $content
	 * @param bool  $parse
	 * @param array $parse_filter
	 *
	 * @return mixed
	 */
	public function setContent( $content, $parse = false, $parse_filter = array() ) {
		$this->content = $parse ? $this->parse_content($content, $parse_filter): $content;
		return $this->content;
	}

	/**
	 * @param mixed $post_id
	 *
	 * @return mixed
	 */
	public function setPostId( $post_id ) {
		$this->post_id = (int) $post_id;
		return (int) $this->post_id;
	}

	public function if_check($matches){
		global $config, $row;

		if( count($this->if_array) ) $row = $this->if_array;

		$regex = '/\[if (.+?)\]((?>(?R)|.)*?)\[\/if\]/is';

		if (is_array($matches)) {

		$matches[1] = trim(dle_strtolower($matches[1], $config['charset']));
		$find_type = true;
		$match_count = 0;

		if(stripos($matches[1], " or ")) {
		$find_type = false;
		$if_array = explode(" or ", $matches[1]);
		} else $if_array = explode(" and ", $matches[1]);

		foreach ($if_array as $if_str) {
			$if_str = trim($if_str);

			preg_match("#^(.+?)(!~|~|!=|=|>=|<=|<|>)\s*['\"]?(.*?)['\"]?$#is", $if_str, $m);

			$field = trim($m[1]);
			$operator = trim($m[2]);
			$value = trim($m[3]);

			$field = explode("xfield_",$field);

			if($field[1]) $fieldvalue = $row['xfields_array'][$field[1]];
			elseif( $field[0]=='date' OR $field[0]=='editdate' OR $field[0]=='lastdate' OR $field[0]=='reg_date') {

				$fieldvalue = strtotime( date( "Y-m-d H:i", $row[$field[0]]) );

				if( strtotime($value) !== false ) {
					$value = strtotime($value);
				}

			} elseif( $field[0]=='tags' AND is_array($row[$field[0]]) ) {

				$fieldvalue = array();

				foreach ( $row[$field[0]] as $temp_value ) {

					$fieldvalue[] = trim(dle_strtolower($temp_value, $config['charset']));

				}

			} elseif( $field[0]=='category' ) {

				$fieldvalue = $row['cats'];

			} else $fieldvalue = $row[$field[0]];

			if( !is_array($fieldvalue) ) {
				$fieldvalue = trim(dle_strtolower($fieldvalue, $config['charset']));
			}

			switch( $operator ){
				case ">":

					if( is_array($fieldvalue) ) {

						$found_match = false;

						foreach ( $fieldvalue as $temp_value ) {

							$temp_value = floatval($temp_value);
							$value = floatval($value);

							if($temp_value > $value) {
								$found_match = true;
							}

						}

						if( $found_match ) $match_count ++;

					} else {

						$fieldvalue = (float)$fieldvalue;
						$value = (float)$value;
						if($fieldvalue > $value) $match_count ++;

					}

					break;
				case "<":

					if( is_array($fieldvalue) ) {

						$found_match = false;

						foreach ( $fieldvalue as $temp_value ) {

							$temp_value = (float)$temp_value;
							$value = (float)$value;

							if($temp_value < $value) {
								$found_match = true;
							}

						}

						if( $found_match ) $match_count ++;

					} else {

						$fieldvalue = (float)$fieldvalue;
						$value = (float)$value;
						if($fieldvalue < $value) $match_count ++;

					}

					break;
				case ">=":

					if( is_array($fieldvalue) ) {

						$found_match = false;

						foreach ( $fieldvalue as $temp_value ) {

							$temp_value = (float)$temp_value;
							$value = (float)$value;

							if($temp_value >= $value) {
								$found_match = true;
							}

						}

						if( $found_match ) $match_count ++;

					} else {

						$fieldvalue = (float)$fieldvalue;
						$value = (float)$value;
						if($fieldvalue >= $value) $match_count ++;

					}

					break;
				case "<=":

					if( is_array($fieldvalue) ) {

						$found_match = false;

						foreach ( $fieldvalue as $temp_value ) {

							$temp_value = (float)$temp_value;
							$value = (float)$value;

							if($temp_value <= $value) {
								$found_match = true;
							}

						}

						if( $found_match ) $match_count ++;

					} else {

						$fieldvalue = (float)$fieldvalue;
						$value = (float)$value;
						if($fieldvalue <= $value) $match_count ++;

					}

					break;
				case "!=":

					if( is_array($fieldvalue) ) {

						if ( !in_array($value, $fieldvalue)) {
							$match_count ++;
						}

					} else {

						if($fieldvalue != $value) $match_count ++;

					}

					break;

				case "~":

					if( is_array($fieldvalue) ) {

						foreach ( $fieldvalue as $temp_value ) {

							if(dle_strpos($temp_value,$value,$config['charset'])!==false) {
								$match_count ++;
								break;
							}

						}

					} else {

						if(dle_strpos($fieldvalue,$value,$config['charset'])!==false) $match_count ++;

					}

					break;
				case "!~":

					if( is_array($fieldvalue) ) {

						$found_count = 0;

						foreach ( $fieldvalue as $temp_value ) {

							if(dle_strpos($temp_value,$value,$config['charset'])===false) {
								$found_count ++;
							}

						}

						if( $found_count == count($fieldvalue) ) $match_count ++;

					} else {

						if(dle_strpos($fieldvalue,$value,$config['charset'])===false) $match_count ++;

					}

					break;
				default:

					if( is_array($fieldvalue) ) {

						if ( in_array($value, $fieldvalue)) {
							$match_count ++;
						}

					} else {

						if($fieldvalue == $value) $match_count ++;

					}
			}
		}

		if($match_count AND $match_count == count($if_array) AND $find_type) {
			$matches = $matches[2];
		} elseif ($match_count AND !$find_type) {
			$matches = $matches[2];
		} else $matches = '';

		}

		return preg_replace_callback($regex, array( &$this, 'if_check'), $matches);

	}

	private function if_category_rating( $category ) {
		global $cat_info;

		$category = explode( ',', $category );

		$found = false;

		foreach ( $category as $element ) {

			if( isset( $cat_info[$element]['rating_type'] ) AND $cat_info[$element]['rating_type'] > -1 ) {
				return $cat_info[$element]['rating_type'];
			}

		}

		return $found;
	}

	private function ShowRating($id, $rating, $vote_num, $allow = true) {
		global $lang, $config, $row, $dle_module;

		if( !$config['rating_type'] ) {

			if( $rating AND $vote_num ) $rating = round( ($rating / $vote_num), 0 );
			else $rating = 0;

			if ($rating < 0 ) $rating = 0;

			if ($vote_num AND $dle_module == "showfull") {

				$shema_title = " itemprop=\"aggregateRating\" itemscope itemtype=\"https://schema.org/AggregateRating\"";
				$shema_ratig = $rating;
				$shema_ratig_title = str_replace("&amp;amp;", "&amp;",  htmlspecialchars( strip_tags( stripslashes( $row['title'] ) ), ENT_QUOTES, $config['charset'] ) );
				$shema = "<meta itemprop=\"itemReviewed\" content=\"{$shema_ratig_title}\"><meta itemprop=\"worstRating\" content=\"1\"><meta itemprop=\"ratingCount\" content=\"{$vote_num}\"><meta itemprop=\"ratingValue\" content=\"{$shema_ratig}\"><meta itemprop=\"bestRating\" content=\"5\">";

			} else {
				$shema_title = "";
				$shema = "";
			}

			$rating = $rating * 20;

			if( !$allow ) {

				$rated = <<<HTML
<div class="rating"{$shema_title}>
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}%;">{$rating}</li>
		</ul>{$shema}
</div>
HTML;

				return $rated;
			}

			$rated = <<<HTML
<div id='ratig-layer-{$id}'>
	<div class="rating"{$shema_title}>
		<ul class="unit-rating">
		<li class="current-rating" style="width:{$rating}%;">{$rating}</li>
		<li><a href="#" title="{$lang['useless']}" class="r1-unit" onclick="doRate('1', '{$id}'); return false;">1</a></li>
		<li><a href="#" title="{$lang['poor']}" class="r2-unit" onclick="doRate('2', '{$id}'); return false;">2</a></li>
		<li><a href="#" title="{$lang['fair']}" class="r3-unit" onclick="doRate('3', '{$id}'); return false;">3</a></li>
		<li><a href="#" title="{$lang['good']}" class="r4-unit" onclick="doRate('4', '{$id}'); return false;">4</a></li>
		<li><a href="#" title="{$lang['excellent']}" class="r5-unit" onclick="doRate('5', '{$id}'); return false;">5</a></li>
		</ul>{$shema}
	</div>
</div>
HTML;

			return $rated;

		} elseif ($config['rating_type'] == "1") {

			if( $rating < 0 ) $rating = 0;

			if( $allow ) $rated = "<span id=\"ratig-layer-{$id}\" class=\"ignore-select\"><span class=\"ratingtypeplus ignore-select\" >{$rating}</span></span>";
			else $rated = "<span class=\"ratingtypeplus ignore-select\" >{$rating}</span>";

			return $rated;

		} elseif ($config['rating_type'] == "2" OR $config['rating_type'] == "3") {

			$extraclass = "ratingzero";

			if( $rating < 0 ) {
				$extraclass = "ratingminus";
			}

			if( $rating > 0 ) {
				$extraclass = "ratingplus";
				$rating = "+".$rating;
			}

			if( $allow ) $rated = "<span id=\"ratig-layer-{$id}\" class=\"ignore-select\"><span class=\"ratingtypeplusminus ignore-select {$extraclass}\" >{$rating}</span></span>";
			else $rated = "<span class=\"ratingtypeplusminus ignore-select {$extraclass}\" >{$rating}</span>";

			return $rated;

		}

	}

	/**
	 * @param       $content
	 * @param array $filter
	 *
	 * @return string
	 */
	public function parse_content($content, $filter = array()) {
		global $lang, $_TIME, $PHP_SELF, $cat_info, $config, $db, $user_group, $member_id, $customlangdate;

		$where = [
			'p.id = e.news_id',
			"p.id = {$this->post_id}"
		];
		if ( !empty($filter['fields']) ) $where[] = "({$filter['fields']})";
		$where = implode(' AND ', $where);

		$join = '';
		if ($config['allow_multi_category'] && $filter['cats']) $join = "INNER JOIN (SELECT DISTINCT(" . PREFIX . "_post_extras_cats.news_id) FROM " . PREFIX . "_post_extras_cats WHERE cat_id IN ('{$filter['cats']}') c ON (p.id=c.news_id)";

		$row = $db->super_query('SELECT * FROM ' . PREFIX . '_post p LEFT JOIN ' . PREFIX . "_post_extras e on (p.id = e.news_id) {$join} WHERE {$where}");

		$category_id = (int) $row['category'];
		$row['date'] = strtotime( $row['date'] );

		$xfields = xfieldsload();
		$empty_full = false;
		if( (strlen( $row['full_story'] ) < 13) and (strpos( $content, "{short-story}" ) === false) ) {
			$row['full_story'] = $row['short_story'];
			$empty_full = true;
		}

		$content = htmlspecialchars_decode($content);

		$row['full_story'] = str_replace( "{PAGEBREAK}", "", $row['full_story'] );
		$row['full_story'] = preg_replace( "'\[page=(.*?)\](.*?)\[/page\]'si", "", $row['full_story'] );
		preg_match_all('/\\[pages\\](.*?)\\[\\/pages\\]/', $content, $content_array);
		foreach ( $content_array[0] as $id => $arr) {
			$content = str_replace($arr, '', $content);
		}
		$content = str_replace('{pages}', '', $content);

		if( $config['allow_alt_url'] ) {
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				if( $category_id AND $config['seo_type'] == 2 ) {
					$c_url = get_url(  $row['category'] );
					if($c_url) {
						$full_link = $config['http_home_url'] . $c_url . "/" . $row['id'] . "-" . $row['alt_name'] . ".html";
					} else {
						$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
					}
				} else {
					$full_link = $config['http_home_url'] . $row['id'] . "-" . $row['alt_name'] . ".html";
				}
			} else {
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $row['date'] ) . $row['alt_name'] . ".html";
			}
		} else {
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $row['id'];
		}

		$row['full_story'] = preg_replace( "#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", "", $row['full_story'] );
		$row['full_story'] = preg_replace( "#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", "", $row['full_story'] );
		$row['full_story'] = preg_replace( "'\[attachment=(.*?)\]'si", "", $row['full_story'] );
		$row['full_story'] = preg_replace ( "#\[hide(.*?)\](.+?)\[/hide\]#is", "", $row['full_story'] );

		$row['full_story'] = str_replace( "><", "> <", $row['full_story'] );
		$row['full_story'] = strip_tags( $row['full_story'], "<br>" );
		$row['full_story'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $row['full_story'] ) ) ) ));
		$row['full_story'] = preg_replace('/\s+/u', ' ', $row['full_story']);
		$row['full_story'] = preg_replace( '#(\A[\s]*<br[^>]*>[\s]*|<br[^>]*>[\s]*\Z)#is', '', $row['full_story'] );
		$row['title'] = stripslashes( $row['title'] );
		$comments_num = $row['comm_num'];

		if( date( 'Ymd', $row['date'] ) == date( 'Ymd', $_TIME ) ) {
			$content = str_replace('{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ), $content);
		} elseif( date( 'Ymd', $row['date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
			$content = str_replace('{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ), $content);
		} else {
			$content = str_replace('{date}', langdate( $config['timestamp_active'], $row['date']	), $content);
		}

		$content = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $content );

		if ( $row['fixed'] ) {
			$content = str_replace([ '[fixed]', '[/fixed]' ], '', $content);
			preg_match_all('/\\[not-fixed\\](.*?)\\[\\/not-fixed\\]/', $content, $content_array);
			foreach ( $content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		} else {
			$content = str_replace([ '[not-fixed]', '[/not-fixed]' ], '', $content);
			preg_match_all('/\\[fixed\\](.*?)\\[\\/fixed\\]/', $content, $content_array);
			foreach ( $content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		}

		if ( $comments_num ) {
			if( $row['allow_comm'] ) {
				$content = str_replace([ '[comments]', '[/comments]' ], '', $content);
			} else {
				preg_match_all('/\\[comments\\](.*?)\\[\\/comments\\]/', $content, $content_array);
				foreach ( $content_array[0] as $id => $arr) {
					$content = str_replace($arr, '', $content);
				}
			}

			preg_match_all('/\\[not-comments\\](.*?)\\[\\/not-comments\\]/', $content, $content_array);
			foreach ( $content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		} else {
			if( $row['allow_comm'] ) {
				$content = str_replace([ '[not-comments]', '[/not-comments]' ], '', $content);
			} else {
				preg_match_all('/\\[not-comments\\](.*?)\\[\\/not-comments\\]/', $content, $content_array);
				foreach ( $content_array[0] as $id => $arr) {
					$content = str_replace($arr, '', $content);
				}
			}

			preg_match_all('/\\[comments\\](.*?)\\[\\/comments\\]/', $content, $content_array);
			foreach ( $content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		}

		if ( $row['votes'] ) {
			$content = str_replace([ '[poll]', '[/poll]' ], '', $content);
			preg_match_all('/\\[not-poll\\](.*?)\\[\\/not-poll\\]/', $content, $content_array);
			foreach ( $content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		} else {
			$content = str_replace([ '[not-poll]', '[/not-poll]' ], '', $content);
			preg_match_all('/\\[poll\\](.*?)\\[\\/poll\\]/', $content, $content_array);
			foreach ( $content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		}

		if( $row['view_edit'] and $row['editdate'] ) {
			if( date( 'Ymd', $row['editdate'] ) == date( 'Ymd', $_TIME ) ) {
				$content = str_replace('{edit-date}', $lang['time_heute'] . langdate( ", H:i", $row['editdate'] ), $content);
			} elseif( date( 'Ymd', $row['editdate'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
				$content = str_replace('{edit-date}', $lang['time_gestern'] . langdate( ", H:i", $row['editdate'] ), $content);
			} else {
				$content = str_replace('{edit-date}', langdate( $config['timestamp_active'], $row['editdate'] ), $content);
			}
			$content = str_replace([ '{editor}', '{edit-reason}' ], [ $row['editor'], $row['reason'] ], $content);
			if ($row['reason'] ) {
				$content = str_replace([ '[edit-reason]', '[/edit-reason]' ], '', $content);

			} else {
				preg_match_all('/\\[edit-reason\\](.*?)\\[\\/edit-reason\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$content = str_replace($arr, '', $content);
				}
			}
			$content = str_replace([ '[edit-date]', '[/edit-date]' ], '', $content);
		} else {
			$content = str_replace([ '{edit-date}', '{editor}', '{edit-reason}' ], '', $content);
			preg_match_all('/\\[edit-reason\\](.*?)\\[\\/edit-reason\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[edit-date\\](.*?)\\[\\/edit-date\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		}

		if( $config['allow_tags'] and $row['tags'] ) {
			$content = str_replace([ '[tags]', '[/tags]' ], '', $content);

			$tags = array ();
			$tags_no_link = array ();
			$tags_hashtag = array ();

			$row['tags'] = explode( ",", $row['tags'] );

			foreach ( $row['tags'] as $value ) {

				$value = trim( $value );
				$url_tag = str_replace(array("&#039;", "&quot;", "&amp;"), array("'", '"', "&"), $value);
				$tags_no_link[] = $url_tag;
				$tags_hashtag[] = "#{$url_tag}";

				if( $config['allow_alt_url'] ) $tags[] = "<a href=\"" . $config['http_home_url'] . "tags/" .
														 rawurlencode( $url_tag ) . "/\">" . $value . "</a>";
				else $tags[] = "<a href=\"$PHP_SELF?do=tags&amp;tag=" . rawurlencode( $url_tag ) . "\">" . $value . "</a>";

			}
			$content = str_replace(
				[ '{tags}', '{tags_no_link}', '{hashtags}' ],
				[ implode($config['tags_separator'], $tags),
				  implode($config['tags_separator'], $tags_no_link),
				  implode($config['tags_separator'], $tags_hashtag) ], $content
			);

		} else {
			preg_match_all('/\\[tags\\](.*?)\\[\\/tags\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			$content = str_replace(
				[ '{tags}', '{tags_no_link}', '{hashtags}' ],
				'', $content
			);
		}

		$url_cat = $category_id;

		if( strpos( $content, "[catlist=" ) !== false ) {
			$content = preg_replace_callback ( "#\\[(catlist)=(.+?)\\](.*?)\\[/catlist\\]#is", "check_category", $content );
		}

		if( strpos( $content, "[not-catlist=" ) !== false ) {
			$content = preg_replace_callback ( "#\\[(not-catlist)=(.+?)\\](.*?)\\[/not-catlist\\]#is", "check_category", $content );
		}

		$temp_rating = $config['rating_type'];
		$config['rating_type'] = $this->if_category_rating( $row['category'] );

		if ( $config['rating_type'] === false ) {
			$config['rating_type'] = $temp_rating;
		}

		$category_id = $url_cat;

		if( $category_id AND $cat_info[$category_id]['icon'] ) {
			$content = str_replace(
				[ '{category-icon}', '[category-icon]', '[/category-icon]'  ],
				[ $cat_info[$category_id]['icon'], '', '' ], $content
			);
			preg_match_all('/\\[not-category-icon\\](.*?)\\[\\/not-category-icon\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		} else {
			$content = str_replace(
				[ '[not-category-icon]', '[/not-category-icon]', '{category-icon}' ],
				[ '', '', '{category-icon}' ], $content
			);
			preg_match_all('/\\[category-icon\\](.*?)\\[\\/category-icon\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		}

		if ( $category_id ) {
			$cats_url = get_url( $row['category'] );
			if( $cats_url ) $cats_url .= "/";

			if( $config['allow_alt_url'] )
				$content = str_replace('{category-url}', $config['http_home_url'] . $cats_url, $content);
			else
				$content = str_replace('{category-url}', "$PHP_SELF?do=cat&category={$cat_info[$category_id]['alt_name']}", $content);

		} else $content = str_replace('{category-url}', "#", $content);

		if ( $config['rating_type'] == "1" ) {
			$content = str_replace([ '[rating-type-2]', '[/rating-type-2]' ], '', $content);
			preg_match_all('/\\[rating-type-1\\](.*?)\\[\\/rating-type-1\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-type-3\\](.*?)\\[\\/rating-type-3\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-type-4\\](.*?)\\[\\/rating-type-4\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		} elseif ( $config['rating_type'] == "2" ) {
			$content = str_replace([ '[rating-type-3]', '[/rating-type-3]' ], '', $content);
			preg_match_all('/\\[rating-type-1\\](.*?)\\[\\/rating-type-1\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-type-2\\](.*?)\\[\\/rating-type-2\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-type-4\\](.*?)\\[\\/rating-type-4\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		} elseif ( $config['rating_type'] == "3" ) {
			$content = str_replace([ '[rating-type-4]', '[/rating-type-4]' ], '', $content);
			preg_match_all('/\\[rating-type-1\\](.*?)\\[\\/rating-type-1\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-type-3\\](.*?)\\[\\/rating-type-3\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-type-2\\](.*?)\\[\\/rating-type-2\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		} else {
			$content = str_replace([ '[rating-type-1]', '[/rating-type-1]' ], '', $content);
			preg_match_all('/\\[rating-type-2\\](.*?)\\[\\/rating-type-2\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-type-3\\](.*?)\\[\\/rating-type-3\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-type-4\\](.*?)\\[\\/rating-type-4\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		}

		if( $row['allow_rate'] ) {

			$dislikes = ($row['vote_num'] - $row['rating'])/2;
			$likes    = $row['vote_num'] - $dislikes;
			$content  = str_replace(
				[ '[/rating]', '{rating}', '[rating]', '{vote-num}', '{dislikes}', '{likes}' ],
				[ '', $this->ShowRating($row['id'], $row['rating'], $row['vote_num'],
								  $user_group[$member_id['user_group']]['allow_rating']), '', $row['vote_num'], $dislikes, $likes ], $content
			);

			if( $row['vote_num'] ) $ratingscore = str_replace( ',', '.', round( ($row['rating'] / $row['vote_num']), 1 ) );
			else $ratingscore = 0;

			$content  = str_replace('{ratingscore}', $ratingscore, $content);

			if( $user_group[$member_id['user_group']]['allow_rating'] ) {
				if ( $config['rating_type'] ) {
					$content  = str_replace([ '[rating-plus]', '[/rating-plus]' ], '', $content);

					if ( $config['rating_type'] == "2" OR $config['rating_type'] == "3") {
						$content  = str_replace([ '[rating-minus]', '[/rating-minus]' ], '', $content);

					} else {
						preg_match_all('/\\[rating-minus\\](.*?)\\[\\/rating-minus\\]/', $content, $content_array);
						foreach ($content_array[0] as $id => $arr) {
							$content = str_replace($arr, '', $content);
						}
					}
				} else {
					preg_match_all('/\\[rating-minus\\](.*?)\\[\\/rating-minus\\]/', $content, $content_array);
					foreach ($content_array[0] as $id => $arr) {
						$content = str_replace($arr, '', $content);
					}
					preg_match_all('/\\[rating-plus\\](.*?)\\[\\/rating-plus\\]/', $content, $content_array);
					foreach ($content_array[0] as $id => $arr) {
						$content = str_replace($arr, '', $content);
					}
				}
			} else {
				preg_match_all('/\\[rating-minus\\](.*?)\\[\\/rating-minus\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$content = str_replace($arr, '', $content);
				}
				preg_match_all('/\\[rating-plus\\](.*?)\\[\\/rating-plus\\]/', $content, $content_array);
				foreach ($content_array[0] as $id => $arr) {
					$content = str_replace($arr, '', $content);
				}
			}

		} else {
			$content  = str_replace(
				[ '{rating}', '{vote-num}', '{dislikes}', '{likes}', '{ratingscore}' ],
				'', $content
			);

			preg_match_all('/\\[rating\\](.*?)\\[\\/rating\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-minus\\](.*?)\\[\\/rating-minus\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
			preg_match_all('/\\[rating-plus\\](.*?)\\[\\/rating-plus\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		}

		$config['rating_type'] = $temp_rating;

		preg_match_all('/\\[comments-subscribe\\](.*?)\\[\\/comments-subscribe\\]/', $content, $content_array);
		foreach ($content_array[0] as $id => $arr) {
			$content = str_replace($arr, '', $content);
		}

		if( $config['allow_alt_url'] ) {
			$go_page = $config['http_home_url'] . "user/" . urlencode( $row['autor'] ) . "/";
			$content  = str_replace('[day-news]', "<a href=\"".$config['http_home_url'] . date( 'Y/m/d/', $row['date'])."\" >", $content);
		} else {
			$go_page = "$PHP_SELF?subaction=userinfo&amp;user=" . urlencode( $row['autor'] );
			$content  = str_replace('[day-news]', "<a href=\"$PHP_SELF?year=".date( 'Y', $row['date'])."&amp;month=".date( 'm', $row['date'])."&amp;day=".date( 'd', $row['date'])."\" >", $content);
		}

		$content = str_replace(
			[ '{full-link}', '[full-link]', '{login}', '{author}', '[profile]' ],
			[ $full_link, "<a href=\"" . $full_link . "\">", $row['autor'], "<a href=\"" . $go_page . "\">" . $row['autor'] . "</a>",
			  "<a href=\"" . $go_page . "\">" ], $content
		);

		$content = str_replace(
			[ '[/full-link]', '[/profile]', '[/day-news]' ],"</a>", $content
		);

		if( $row['allow_comm'] ) {
			$content = str_replace(
				[ '[com-link]', '[/com-link]' ],
				[ "<a id=\"dle-comm-link\" href=\"" . $full_link . "#comment\">", "</a>" ], $content
			);
		} else {
			preg_match_all('/\\[com-link\\](.*?)\\[\\/com-link\\]/', $content, $content_array);
			foreach ($content_array[0] as $id => $arr) {
				$content = str_replace($arr, '', $content);
			}
		}

		preg_match_all('/\\[edit\\](.*?)\\[\\/edit\\]/', $content, $content_array);
		foreach ($content_array[0] as $id => $arr) {
			$content = str_replace($arr, '', $content);
		}

		$content = str_replace(['{favorites}', '{poll}'], "", $content);

		preg_match_all('/\\[add-favorites\\](.*?)\\[\\/add-favorites\\]/', $content, $content_array);
		foreach ($content_array[0] as $id => $arr) {
			$content = str_replace($arr, '', $content);
		}
		preg_match_all('/\\[del-favorites\\](.*?)\\[\\/del-favorites\\]/', $content, $content_array);
		foreach ($content_array[0] as $id => $arr) {
			$content = str_replace($arr, '', $content);
		}
		preg_match_all('/\\[complaint\\](.*?)\\[\\/complaint\\]/', $content, $content_array);
		foreach ($content_array[0] as $id => $arr) {
			$content = str_replace($arr, '', $content);
		}
		preg_match_all('/\\[banner_(.*?)\\](.*?)\\[\\/banner_(.*?)\\]/', $content, $content_array);
		foreach ($content_array[0] as $id => $arr) {
			$content = str_replace($arr, '', $content);
		}
		preg_match_all('/{banner_(.*?)}/', $content, $content_array);
		foreach ($content_array[0] as $id => $arr) {
			$content = str_replace($arr, '', $content);
		}

		$row['short_story'] = stripslashes($row['short_story']);
		$row['full_story'] = stripslashes($row['full_story']);
		$row['xfields'] = stripslashes( $row['xfields'] );

		if ($config['allow_links'] AND function_exists('replace_links') AND isset($replace_links['news']) ) {
			$row['short_story'] = replace_links ( $row['short_story'], $replace_links['news'] );
			$row['full_story'] = replace_links ( $row['full_story'], $replace_links['news'] );
		}

		if (stripos ( $content, "{image-" ) !== false) {

			$images = array();
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['short_story'].$row['xfields'], $media);
			$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);

			foreach($data as $url) {
				$info = pathinfo($url);
				if (isset($info['extension'])) {
					if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" OR strpos($info['dirname'], 'engine/data/emoticons') !== false) continue;
					$info['extension'] = strtolower($info['extension']);
					if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png') || ($info['extension'] == 'webp')) array_push($images, $url);
				}
			}

			if ( count($images) ) {
				$i=0;
				foreach($images as $url) {
					$i++;
					$content = str_replace( '{image-'.$i.'}', $url, $content );
					$content = str_replace([ '[image-' . $i . ']', '[/image-' . $i . ']' ], "", $content);
					$content = preg_replace("#\[not-image-{$i}\](.+?)\[/not-image-{$i}\]#is", "", $content );
				}
			}

			$content = preg_replace( "#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $content );
			$content = preg_replace( "#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $content );
			$content = preg_replace( "#\[not-image-(.+?)\]#i", "", $content );
			$content = preg_replace( "#\[/not-image-(.+?)\]#i", "", $content );

		}

		if (stripos ( $content, "{fullimage-" ) !== false) {

			$images = array();
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['full_story'], $media);
			$data=preg_replace('/(img|src)("|\'|="|=\')(.*)/i',"$3",$media[0]);

			foreach($data as $url) {
				$info = pathinfo($url);
				if (isset($info['extension'])) {
					if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus" OR strpos($info['dirname'], 'engine/data/emoticons') !== false) continue;
					$info['extension'] = strtolower($info['extension']);
					if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png') || ($info['extension'] == 'webp')) array_push($images, $url);
				}
			}

			if ( count($images) ) {
				$i=0;
				foreach($images as $url) {
					$i++;
					$content = str_replace( '{fullimage-'.$i.'}', $url, $content );
					$content = str_replace([ '[fullimage-' . $i . ']', '[/fullimage-' . $i . ']' ], "", $content);
				}

			}

			$content = preg_replace( "#\[fullimage-(.+?)\](.+?)\[/fullimage-(.+?)\]#is", "", $content );
			$content = preg_replace( "#\\{fullimage-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg", $content );

		}

		$content = str_replace([ '{addcomments}', '{navigation}', '{comments}' ], "", $content);

		if( count($xfields) ) {
			$row['xfields_array'] = xfieldsdataload( $row['xfields'] );
		}

		$content = $this->if_check($content);

		if( count($xfields) ) {
			$xfieldsdata = $row['xfields_array'];
			foreach ( $xfields as $value ) {
				$preg_safe_name = preg_quote( $value[0], "'" );
				if( $value[20] ) {
					$value[20] = explode( ',', $value[20] );
					if( $value[20][0] AND !in_array( $member_id['user_group'], $value[20] ) ) {
						$xfieldsdata[$value[0]] = "";
					}
				}
				if ( $value[3] == "yesorno" ) {
					if((int)$xfieldsdata[$value[0]]) {
						$xfgiven = true;
						$xfieldsdata[$value[0]] = $lang['xfield_xyes'];
					} else {
						$xfgiven = false;
						$xfieldsdata[$value[0]] = $lang['xfield_xno'];
					}
				} else {
					if($xfieldsdata[$value[0]] == "") $xfgiven = false; else $xfgiven = true;
				}

				if( !$xfgiven ) {
					$content = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $content );
					$content = str_ireplace( "[xfnotgiven_{$value[0]}]", "", $content );
					$content = str_ireplace( "[/xfnotgiven_{$value[0]}]", "", $content );
				} else {
					$content = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $content );
					$content = str_ireplace( "[xfgiven_{$value[0]}]", "", $content );
					$content = str_ireplace( "[/xfgiven_{$value[0]}]", "", $content );
				}

				if(strpos( $content, "[ifxfvalue {$value[0]}" ) !== false ) {
					$content = preg_replace_callback ( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "check_xfvalue", $content );
				}

				if ($value[3] == "select" ) {

					if (!$xfieldsdata[$value[0]]) {
						$content = str_replace(["[xfvalue_tagvalue_{$value[0]}]", "[xfvalue_tagvalue_url_{$value[0]}]"], '', $content);
					} else {
						$xf_val = array();
						$xf_val_url = array();
						$xf_splitted = explode(',', $xfieldsdata[$value[0]]);
						foreach (preg_split("/((\r?\n)|(\r\n?))/", $value[4]) as $line) {
							$select_values = explode('|', $line);
							$tag_name = $select_values[0];
							$tag_val = $tag_name;

							foreach($xf_splitted as $xf) {
								if ($tag_name == $xf) {
									if (count($select_values) > 1) {
										$tag_val = $select_values[1];
									}
									$tag_val = trim($tag_val);

									$xf_val[] = $tag_val;
									if ($value[6]) {
										$value4 = str_replace(['&#039;', '&quot;', '&amp;', '&#123;', '&#91;', '&#58;'], ["'", '"', '&', '{', '[', ':'], $tag_name);

										if ($config['allow_alt_url']) {
											$xf_val_url[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" .$value[0]."/". rawurlencode( $value4 ) . "/\">".$tag_val.'</a>';
										} else {
											$xf_val_url[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=".$value[0]."&amp;xf=" . rawurlencode( $value4 ) . "\">".$tag_val.'</a>';
										}
									} else {
										$xf_val_url[] = $tag_val;
									}
								}
							}
						}

						$sep_sign = ( empty($value[21]) ) ? ', ' : $value[21];
						$content = str_replace(
							[ "[xfvalue_tagvalue_{$value[0]}]", "[xfvalue_tagvalue_url_{$value[0]}]" ],
							[ implode($sep_sign, $xf_val), implode($sep_sign, $xf_val_url) ], $content
						);
					}
				} else {
					$content = str_replace(["[xfvalue_tagvalue_{$value[0]}]", "[xfvalue_tagvalue_url_{$value[0]}]"], '', $content);
				}

				if ( $value[6] AND !empty( $xfieldsdata[$value[0]] ) ) {
					$temp_array = explode( ",", $xfieldsdata[$value[0]] );
					$value3 = array();
					$value3_no_link = array();
					$value3_hashtag = array();

					foreach ($temp_array as $value2) {
						$value2 = trim($value2);
						if($value2) {
							$value4 = str_replace(array("&#039;", "&quot;", "&amp;", "&#123;", "&#91;", "&#58;"), array("'", '"', "&", "{", "[", ":"), $value2);
							if( $value[3] == "datetime" ) {
								$value2 = strtotime( $value4 );
								if( !trim($value[24]) ) $value[24] = $config['timestamp_active'];
								if( $value[25] ) {
									if($value[26]) $value2 = langdate($value[24], $value2);
									else $value2 = langdate($value[24], $value2, false, $customlangdate);
								} else $value2 = date( $value[24], $value2 );
							}

							if( $config['allow_alt_url'] ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" .$value[0]."/". rawurlencode( $value4 ) . "/\">" . $value2 . "</a>";
							else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=".$value[0]."&amp;xf=" . rawurlencode( $value4 ) . "\">" . $value2 . "</a>";
							$value3_no_link[] = $value2;
							$value3_hashtag[] = "#{$value2}";
						}
					}

					if( empty($value[21]) ) $value[21] = ", ";
					$xfieldsdata[$value[0]] = implode($value[21], $value3);
					$xfieldsdata["{$value[0]}_text"] = implode($value[21], $value3_no_link);
					$xfieldsdata["{$value[0]}_hashtag"] = implode($value[21], $value3_hashtag);

					unset($temp_array);
					unset($value2);
					unset($value3);
					unset($value3_no_link);
					unset($value3_no_link);
					unset($value4);

				} elseif ( $value[3] == "datetime" AND !empty($xfieldsdata[$value[0]]) ) {

					$xfieldsdata[$value[0]] = strtotime( str_replace("&#58;", ":", $xfieldsdata[$value[0]]) );

					if( !trim($value[24]) ) $value[24] = $config['timestamp_active'];

					if( $value[25] ) {

						if($value[26]) $xfieldsdata[$value[0]] = langdate($value[24], $xfieldsdata[$value[0]]);
						else $xfieldsdata[$value[0]] = langdate($value[24], $xfieldsdata[$value[0]], false, $customlangdate);

					} else $xfieldsdata[$value[0]] = date( $value[24], $xfieldsdata[$value[0]] );


				}

				if ($config['allow_links'] AND $value[3] == "textarea" AND function_exists('replace_links')) $xfieldsdata[$value[0]] = replace_links ( $xfieldsdata[$value[0]], $replace_links['news'] );

				if($value[3] == "image" AND $xfieldsdata[$value[0]] ) {

					$temp_array = explode('|', $xfieldsdata[$value[0]]);

					if (count($temp_array) > 1 ){

						$temp_alt = $temp_array[0];
						$temp_value = $temp_array[1];

					} else {

						$temp_alt = '';
						$temp_value = $temp_array[0];

					}

					$path_parts = @pathinfo($temp_value);

					if( $value[12] AND file_exists(ROOT_DIR . "/uploads/posts/" .$path_parts['dirname']."/thumbs/".$path_parts['basename']) ) {
						$thumb_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/thumbs/".$path_parts['basename'];
						$img_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
					} else {
						$img_url = 	$config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
						$thumb_url = "";
					}

					$content = str_replace(
						[ "[xfvalue_thumb_url_{$value[0]}]", "[xfvalue_image_url_{$value[0]}]" ],
						[ $thumb_url, $img_url ], $content
					);
					$xfieldsdata[$value[0]] = "<a href=\"$img_url\">$temp_alt</a>";
				}

				if($value[3] == "image" AND !$xfieldsdata[$value[0]]) {
					$content = str_replace(
						[ "[xfvalue_thumb_url_{$value[0]}]", "[xfvalue_image_url_{$value[0]}]" ],
						'', $content
					);
				}

				if($value[3] == "imagegalery" AND $xfieldsdata[$value[0]] AND stripos ( $content, "[xfvalue_{$value[0]}" ) !== false) {

					$fieldvalue_arr = explode(',', $xfieldsdata[$value[0]]);
					$gallery_image = array();
					$gallery_single_image = array();
					$xf_image_count = 0;
					$single_need = false;

					if(stripos ( $content, "[xfvalue_{$value[0]} image=" ) !== false) $single_need = true;

					foreach ($fieldvalue_arr as $temp_value) {
						$xf_image_count ++;

						$temp_value = trim($temp_value);

						if($temp_value == "") continue;

						$temp_array = explode('|', $temp_value);

						if (count($temp_array) > 1 ){

							$temp_alt = $temp_array[0];
							$temp_value = $temp_array[1];

						} else {

							$temp_alt = '';
							$temp_value = $temp_array[0];

						}

						$path_parts = @pathinfo($temp_value);

						if( $value[12] AND file_exists(ROOT_DIR . "/uploads/posts/" .$path_parts['dirname']."/thumbs/".$path_parts['basename']) ) {
							$thumb_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/thumbs/".$path_parts['basename'];
							$img_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
						} else {
							$img_url = 	$config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
							$thumb_url = "";
						}

						$gallery_image[] = "<a href=\"$img_url\">{$temp_alt}</a>";
						$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<a href=\"{$img_url}\">{$temp_alt}</a>";

					}

					if($single_need AND count($gallery_single_image) ) {
						foreach($gallery_single_image as $temp_key => $temp_value) $content = str_replace($temp_key, $temp_value, $content);
					}

					$xfieldsdata[$value[0]] = implode($gallery_image);

				}
				$content = str_replace("[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]], $content);

				$all_xf_content[] = $xfieldsdata[$value[0]];

				if ( preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $content, $matches ) ) {
					$count= (int)$matches[1];

					$xfieldsdata[$value[0]] = str_replace( "><", "> <", $xfieldsdata[$value[0]] );
					$xfieldsdata[$value[0]] = strip_tags( $xfieldsdata[$value[0]], "<br>" );
					$xfieldsdata[$value[0]] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $xfieldsdata[$value[0]] ) ) ) ));
					$xfieldsdata[$value[0]] = preg_replace('/\s+/u', ' ', $xfieldsdata[$value[0]]);

					if( $count AND dle_strlen( $xfieldsdata[$value[0]], $config['charset'] ) > $count ) {

						$xfieldsdata[$value[0]] = dle_substr( $xfieldsdata[$value[0]], 0, $count, $config['charset'] );

						if( ($temp_dmax = dle_strrpos( $xfieldsdata[$value[0]], ' ', $config['charset'] )) ) $xfieldsdata[$value[0]] = dle_substr( $xfieldsdata[$value[0]], 0, $temp_dmax, $config['charset'] );

					}

					$content = str_replace($matches[0], $xfieldsdata[$value[0]], $content);

				}
			}
		}


		$content =
			str_replace([ '{short-story}', '{full-story}' ], [ $row['short_story'], $row['full_story'] ], $content);

		if ( preg_match( "#\\{full-story limit=['\"](.+?)['\"]\\}#i", $content, $matches ) ) {
			$count = (int) $matches[1];

			if( $count AND dle_strlen( $row['full_story'], $config['charset'] ) > $count ) {

				$row['full_story'] = dle_substr( $row['full_story'], 0, $count, $config['charset'] );

				if( ($temp_dmax = dle_strrpos( $row['full_story'], ' ', $config['charset'] )) ) $row['full_story'] = dle_substr( $row['full_story'], 0, $temp_dmax, $config['charset'] );

			}
			$content = str_replace($matches[0], $row['full_story'], $content);

		}

		if ( preg_match( "#\\{short-story limit=['\"](.+?)['\"]\\}#i", $content, $matches ) ) {
			$count = (int) $matches[1];

			if( $count AND dle_strlen( $row['short_story'], $config['charset'] ) > $count ) {

				$row['short_story'] = dle_substr( $row['short_story'], 0, $count, $config['charset'] );

				if( ($temp_dmax = dle_strrpos( $row['short_story'], ' ', $config['charset'] )) ) $row['short_story'] = dle_substr( $row['short_story'], 0, $temp_dmax, $config['charset'] );

			}
			$content = str_replace($matches[0], $row['short_story'], $content);

		}

		$content = str_replace('{title}', str_replace("&amp;amp;", "&amp;", htmlspecialchars( $row['title'],
																							  ENT_QUOTES, $config['charset'] ) ) , $content);

		$this->setPostTitle(str_replace("&amp;amp;", "&amp;", htmlspecialchars( $row['title'],ENT_QUOTES, $config['charset'] )));

		if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $content, $matches ) ) {
			$count= (int) $matches[1];
			$row['title'] = strip_tags( $row['title'] );

			if( $count AND dle_strlen( $row['title'], $config['charset'] ) > $count ) {

				$row['title'] = dle_substr( $row['title'], 0, $count, $config['charset'] );

				if( ($temp_dmax = dle_strrpos( $row['title'], ' ', $config['charset'] )) ) $row['title'] = dle_substr( $row['title'], 0, $temp_dmax, $config['charset'] );

			}
			$content = str_replace($matches[0], str_replace("&amp;amp;", "&amp;", htmlspecialchars( $row['title'],
																									ENT_QUOTES,
																									$config['charset'] ) ), $content);

		}

		$content = str_replace(
			[ "&lt;", "&gt;", "<p>", "</p>", "[b]", "[/b]", "[/code]", "[code]", "[/i]", "[i]" ],
			[ "<", ">", "", "<br>", "<b>", "</b>", "</code>", "<code>", "</i>", "<i>" ], $content
		);

		$content = preg_replace("/\[url=(.*)\](.*)\[\/url\]/", "<a href=\"$1\">$2</a>", $content);
		$content = preg_replace("/\[url\](.*)\[\/url\]/", "<a href=\"$1\">$1</a>", $content);

		$content = strip_tags($content, '<b><code><i><a>' );

		return $content;
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
	 * @param        $url
	 * @param null   $proxy
	 * @param string $type
	 * @param null   $auth
	 *
	 * @return bool|string
	 */
	public function send($url, $post =[], $proxy = null, $type = 'http', $auth = null) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if($type === "socks") curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		if($proxy !== null) curl_setopt($ch, CURLOPT_PROXY, $proxy);
		if($auth !== null) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
		curl_setopt($ch, CURLOPT_HTTPHEADER,  array(
			"Content-Type:multipart/form-data"
		));
//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
		$content = curl_exec($ch);
		curl_close($ch);

		$this->generate_log('telegram', 'send', $content, 'info');

		return $content;
	}

	/**
	 * @param        $service
	 * @param        $function_name
	 * @param        $message
	 * @param string $type
	 */
	public function generate_log($service, $function_name, $message, $type = 'error') {
		$root_dir = dirname(__DIR__);
		if (!mkdir($concurrentDirectory = $root_dir . '/logs/' . $service . '/' . $type, 0777, true)
			&& !is_dir(
				$concurrentDirectory
			)) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		$file = "{$concurrentDirectory}/{$function_name}.txt";
		$date = date('[Y-m-d] d.m.Y, H:i');
		$message = serialize($message);
		file_put_contents($file, "{$date}\n{$message}\n=====================================\n", FILE_APPEND);
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
}
