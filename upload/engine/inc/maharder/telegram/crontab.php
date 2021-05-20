<?php

global $config, $name, $version, $descr, $adminlink, $PHP_SELF, $db;



/**
 * Функция создания кеша запросов,
 * чтобы сократить кол-во обращений к базе данных
 *
 * @param       $name	//	Переменная для названия кеша
 * @param array $vars 	// 	table	Название таблицы, в противном случае будет браться переменная $name
 *                     	//	sql		Запрос полностью, если он заполнен, то будет испольняться именно он,
 *                     				другие значения игнорируются
 *                      //  where   Массив выборки запроса, прописывается в название файла кеша.
 *                      //  		Заполняется так: 'поле' => 'значение', 'news_id' => '1'
 *						//	selects	Массив вывод значений, если он пуст, то будут возвращены все значения
 *                     				таблицы. Заполняется так: ['Ячейка 1', 'Ячейка 2', ...]
 *                     				Прописывается в названии файла кеша
 *                     	//	order	Массив сортировки вывода, прописывается в название файла кеша
 *                     				Заполняется так: 'поле' => 'Порядок сортировки', 'news_id' => 'ASC'
 *                     	//	limit	Ограничение вывода запросов, возможно указывать следующие значения:
 *                     				n 	->	просто максимальное кол-во данных
 *                                  n,x	->	ограничение вывода,
 *                                          n - с какого захода начать сбор данных,
 * 											x - до какого значения делать сбор данных
 *
 * @return array
 */
if (!function_exists('load_data')) {
	function load_data(
		$name,
		$vars = [ 'table'   => NULL,
				  'sql'     => NULL,
				  'where'   => [],
				  'selects' => [],
				  'order'   => [],
				  'limit'   => NULL,
		]
	) {
		global $db;

		$where     = [];
		$order     = [];
		$file_name = $name;
		foreach ($vars['selects'] as $s) {
			$file_name .= "_s{$s}";
		}
		foreach ($vars['where'] as $id => $key) {
			$file_name .= "_{$id}-{$key}";
			$where[]   = "{$id} = '{$key}'";
		}
		foreach ($vars['order'] as $n => $sort) {
			$file_name .= "_o{$n}-{$sort}";
			$order[]   = "{$n} {$sort}";
		}
		if ($vars['limit']) $file_name .= "_l{$vars['limit']}";

		if (!file_exists(ENGINE_DIR . "/cache/system/{$file_name}.php")) {
			$data   = [];
			$prefix = PREFIX;
			if (in_array($name, [ 'users', 'usergroup' ])) $prefix = USERPREFIX;

			$order = implode(', ', $order);
			if (!empty($order)) $order = "ORDER BY {$order}";

			$limit = '';
			if (!empty($vars['limit'])) $limit = "LIMIT {$vars['limit']}";

			if (count($vars['where']) > 0 && $vars['sql'] === NULL) {
				$selects = implode(",", $vars['selects']);
				if (empty($selects)) $selects = '*';
				$where = implode(' AND ', $where);
				if (!empty($where)) $where = "WHERE {$where}";

				if ($vars['table'] !== NULL) $sql =
					"SELECT {$selects} FROM {$prefix}_{$vars['table']} {$where} {$order} {$limit}";
				else $sql = "SELECT {$selects} FROM {$prefix}_{$name} {$where} {$order} {$limit}";
			}
			else {
				if ($vars['table'] === NULL && $vars['sql'] === NULL) $vars['table'] = $name;

				if ($vars['table'] !== NULL) {
					$selects = implode(",", $vars['selects']);
					if (empty($selects)) $selects = '*';
					$sql = "SELECT {$selects} FROM {$prefix}_{$vars['table']} {$order} {$limit}";
				}
				if ($vars['sql'] !== NULL) $sql = $vars['sql'];
			}

			$db->query($sql);
			while ($row = $db->get_row()) {
				$data[] = $row;
			}

			$db->close();

			set_vars($file_name, $data);
		}

		return get_vars($file_name);
	}
}

$rq = filter_input_array(INPUT_GET);

switch($rq['site']) {

	case 'save':
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array
		($adminlink => $name, $adminlink . '&do=cron' => 'Новости ожидающие публикации', $adminlink . '&do=cron&site=edit&id=' . $rq['id'] => 'Редактирование задачи', '' => 'Сохранение задачи') );
		$post = filter_input_array(INPUT_POST);
		if (!isset($post['save'])) {
			messageOut('Ошибка', 'Параметры сохранения не были переданы обработчику. Вернитесь назад, чтобы проверить целостность ваших данных.', [$adminlink . '&do=cron&site=edit&id=' . $rq['id'] => 'Назад', $adminlink . '&do=cron' => 'Список новостей', $adminlink => 'Настройки'], 'error');
		} else{
			$save = $post['save'];
			$save['time'] = date('Y-m-d H:i:s', strtotime($save['time']));

			try {
				$db->super_query('UPDATE ' . PREFIX . "_telegram_cron SET type='{$save['type']}', time='{$save['time']}' WHERE cron_id = '{$save['id']}'");
				messageOut('Сохранено', 'Задание обновлено.', [$adminlink . '&do=cron&site=edit&id=' . $rq['id'] => 'Назад',
											   $adminlink . '&do=cron' => 'Список новостей', $adminlink => 'Настройки']);
			} catch (Exception $e) {
				messageOut('Ошибка', 'Произошла ошибка при сохранении. Текст ошибки:<br>' . print_r($e->getMessage()), [$adminlink . '&do=cron&site=edit&id=' . $rq['id'] => 'Назад', $adminlink . '&do=cron' => 'Список новостей', $adminlink => 'Настройки'], 'error');

			}
		}

		break;

	case 'dodelete':
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array
		($adminlink => $name, $adminlink . '&do=cron' => 'Новости ожидающие публикации', $adminlink . '&do=cron&site=edit&id=' . $rq['id'] => 'Удаление задачи', '' => 'Подтверждение удаления') );
		try {
			$db->super_query('DELETE FROM ' . PREFIX . "_telegram_cron WHERE cron_id = '{$rq['id']}'");
			messageOut('Удалено', 'Задание было удалено.', [$adminlink . '&do=cron' => 'Список новостей', $adminlink => 'Настройки']);
		} catch (Exception $e) {
			messageOut('Ошибка', 'Произошла ошибка при сохранении. Текст ошибки:<br>' . print_r($e->getMessage()), [$adminlink . '&do=cron' => 'Список новостей', $adminlink => 'Настройки'], 'error');

		}

		break;

	case 'delete':
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array
		($adminlink => $name, $adminlink . '&do=cron' => 'Новости ожидающие публикации',  '' => 'Удаление задачи') );
		messageOut('Подтвердите действие', 'Вы уверены, что хотите удалить эту задачу? Восстановлению не подлежит!',
				   [$adminlink . '&do=cron&site=dodelete&id=' . $rq['id'] => 'Удалить', $adminlink . '&do=cron' => 'Список новостей', $adminlink => 'Настройки'], 'warning');

		break;

	case 'edit':
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array
		($adminlink => $name, $adminlink . '&do=cron' => 'Новости ожидающие публикации', '' => 'Редактирование задачи') );
		$cron = load_data('telegram_cron', ['table' => 'telegram_cron', 'where' => ['cron_id' => $rq['id']]])[0];
		$news = load_data('post', ['table' => 'post', 'where' => ['id' => $cron['news_id']]])[0];

		if( $config['allow_alt_url'] ) {
			$category_id = $news['category'];
			if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
				if( $category_id AND $config['seo_type'] == 2 ) {
					$c_url = get_url(  $news['category'] );
					if($c_url) {
						$full_link = $config['http_home_url'] . $c_url . "/" . $news['id'] . "-" . $news['alt_name'] . ".html";
					} else {
						$full_link = $config['http_home_url'] . $news['id'] . "-" . $news['alt_name'] . ".html";
					}
				} else {
					$full_link = $config['http_home_url'] . $news['id'] . "-" . $news['alt_name'] . ".html";
				}
			} else {
				$full_link = $config['http_home_url'] . date( 'Y/m/d/', $news['date'] ) . $news['alt_name'] . ".html";
			}
		} else {
			$full_link = $config['http_home_url'] . "index.php?newsid=" . $news['id'];
		}
		$news_name = "<a href='{$full_link}' target='_blank' alt='Открыть на сайте' title='Открыть на сайте'><i class='external link square alternate icon'></i> {$news['title']} (ID: {$cron['news_id']})</a>";

		$block_edit = [
			segRow("Новость", "Данная задача запланирована для этой новости.", $news_name,  'news_name'),
			segRow("Шаблон", "Какой шаблон будет использоваться для отправки новостей",
				   addSelect('type', array('addnews' => "Добавление новости",
										   'editnews' => "Редактирование новости"),
							 'Шаблон', $cron['type']), 'type'),
			segRow("Запланированное время", "Отредактируйте или поправьте время отправки новости",
				   addInput('time', date('Y-m-d', strtotime($cron['time'])) . 'T' . date('H:i', strtotime
								  ($cron['time'])), "Запланированное время", false,
							'datetime-local'), 'time'),
		];
		echo "<form class=\"ui form\" method=\"POST\" action=\"{$adminlink}&do=cron&site=save&id={$rq['id']}\">";
		segment('edit', $block_edit, true);
		saveButton();
		echo "<input type='hidden' name='id' value='{$rq['id']}'></form>";

		break;

	case 'list':
	default:
		echoheader( "<i class=\"cogs icon\"></i> ".$name." ".$version."<br><small>".$descr."</small>", array
		($adminlink => $name, '' => 'Новости ожидающие публикации') );

		$cfg = [
			'limit' => (int) (isset($rq['limit']) ? $rq['limit'] : $config['news_number']),
			'page' => (int) ((isset($rq['page']) && (int) $rq['page'] > 1) ? $rq['page'] : 0),
			'news_id' => (int) (isset($rq['news_id']) ? $rq['news_id'] : 0),
			'sort' => (isset($pq['sort']) && in_array($pq['sort'], ['asc', 'desc', 'ASC', 'DESC'])) ? $pq['sort'] : 'ASC',
			'order' => (isset($pq['order']) && in_array($pq['sort'], ['cron_id', 'news_id', 'time', 'type'])) ? $pq['order'] : 'cron_id'
		];
		$offset = ($cfg['page'] > 0 ? $cfg['page'] - 1 : 0) * $cfg['limit'];

		$where = array();
		if ($cfg['news_id'] !== 0) $where[] = "news_id = {$cfg['news_id']}";
		$where = implode(' AND ', $where);
		if(!empty($where)) $where = " WHERE {$where}";
		$sql = 'SELECT * FROM ' . PREFIX . "_telegram_cron
			{$where}
			ORDER BY {$cfg['order']} {$cfg['sort']}
			LIMIT {$cfg['limit']}
			OFFSET {$offset}
		";

		if ($cfg['news_id'] !== 0) $crontab = load_data('telegram_cron', ['table' => 'telegram_cron', 'sql' => $sql, 'order' => [$cfg['order'] => $cfg['sort']], 'limit' => $cfg['limit'] . ',' . $offset, 'where' => ['news_id' => $cfg['news_id']]]);
		else $crontab = load_data('telegram_cron', ['table' => 'telegram_cron', 'sql' => $sql, 'order' => [$cfg['order'] => $cfg['sort']], 'limit' => $cfg['limit'] . ',' . $offset]);

		$cCount = load_data('telegram_cron', ['table' => 'telegram_cron', 'selects' => ['count(*) as count']])[0];
		$data = array();

		foreach ($crontab as $c) {
			$news = load_data('post', ['table' => 'post', 'where' => ['id' => $c['news_id']]])[0];
			if( $config['allow_alt_url'] ) {
				$category_id = $news['category'];
				if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
					if( $category_id AND $config['seo_type'] == 2 ) {
						$c_url = get_url(  $news['category'] );
						if($c_url) {
							$full_link = $config['http_home_url'] . $c_url . "/" . $news['id'] . "-" . $news['alt_name'] . ".html";
						} else {
							$full_link = $config['http_home_url'] . $news['id'] . "-" . $news['alt_name'] . ".html";
						}
					} else {
						$full_link = $config['http_home_url'] . $news['id'] . "-" . $news['alt_name'] . ".html";
					}
				} else {
					$full_link = $config['http_home_url'] . date( 'Y/m/d/', $news['date'] ) . $news['alt_name'] . ".html";
				}
			} else {
				$full_link = $config['http_home_url'] . "index.php?newsid=" . $news['id'];
			}

			$news_name = "<a href='{$full_link}' target='_blank' alt='Открыть на сайте' title='Открыть на сайте'><i class='external link square alternate icon'></i> {$news['title']} (ID: {$c['news_id']})</a>";

			$action_btns = [
				[
					'link' => $adminlink . '&do=cron&site=edit&id=' . $c['cron_id'],
					'name' => 'Изменить',
					'icon' => 'pencil alternate'
				],
				[
					'link' => $adminlink . '&do=cron&site=delete&id=' . $c['cron_id'],
					'name' => 'Удалить',
					'icon' => 'trash alternate'
				],
				[
					'link' => $PHP_SELF . '?mod=editnews&action=editnews&id=' . $c['news_id'],
					'name' => 'Редактировать новость',
					'icon' => 'edit'
				],
			];

			$btns = <<<HTML
				<div class="ui right pointing dropdown icon button">
					<i class="settings icon"></i>
					<div class="menu">
						<div class="ui left search icon input">
							<i class="search icon"></i>
							<input type="text" name="search" placeholder="Найти решение...">
						</div>
						<div class="divider"></div>
						<div class="header">
							<i class="settings icon"></i>
							Действие
						</div>
HTML;
			foreach ($action_btns as $id => $btn) {
				$btns .= <<<HTML
						<a href="{$btn['link']}" class="item">
							<i class="{$btn['icon']} icon"></i>
							{$btn['name']}
						</a>
HTML;
			}

			$btns .= <<<HTML
					</div>
				</div>
HTML;

			$data[] = [
				$c['cron_id'],
				$news_name,
				$c['time'],
				$c['type'],
				$btns
			];
		}

		$pagination = new Pagination();

		$table = createTable($data, ['ID', 'Новость', 'Время', 'Тип', 'Действие'], '<td colspan="5"><a href="' .
																				   $adminlink .'" role="button" class="ui negative basic button pull-left">Настройки</a> ' . $pagination->getLinks($cCount['count'], $cfg['limit'], $cfg['page']) . '</td>');

		segmentTable('cron', $table, true);

		break;
}

