<?php

require_once DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/_includes/classes/Model.php');

class Cron extends Model {

	public function __construct() {
		parent::__construct(
			'TelegramPosting',
			'telegram_cron',
			'cron_id',
			[
				[
					'name'  => 'news_id',
					'type'  => 'int',
				],
				[
					'name'  => 'time',
					'type'  => 'datetime',
					'limit' => 255
				],
				[
					'name'  => 'type',
					'type'  => 'string',
					'limit' => 255
				],
			],
			[
				[
					'name' => 'foreign',
					[
						'type' => 'new',
						'column' => 'news_id',
						'target_table' => 'post',
						'target_column' => 'id',
						'on_delete' => 'CASCADE'
					]
				]
			]
		);
	}

	/**
	 * Функция по созданию аббревиатуры для базы данных
	 *
	 * @link https://stackoverflow.com/questions/15830222/text-abbreviation-from-a-string-in-php
	 * @param $string
	 * @param $id
	 * @param $l
	 *
	 * @return string
	 */
	protected static function abbr($string, $id = null, $l = 2){
		$results = ''; // empty string
		$vowels = array('a', 'e', 'i', 'o', 'u', 'y'); // vowels
		preg_match_all('/[A-Z][a-z]*/', ucfirst($string), $m); // Match every word that begins with a capital letter, added ucfirst() in case there is no uppercase letter
		foreach($m[0] as $substring){
			$substring = str_replace($vowels, '', strtolower($substring)); // String to lower case and remove all vowels
			$results .= preg_replace('/([a-z]{'.$l.'})(.*)/', '$1', $substring); // Extract the first N letters.
		}
		$results .= '_'. str_pad($id, 4, 0, STR_PAD_LEFT); // Add the ID
		return $results;
	}

	/**
	 * @throws \JsonException
	 */
	public function getAll(
		array $main = [
			'limit' => null, 'order' => [], 'where' => [], 'selects' => []
		]
	)
	: array {

		$vars = array_merge([
			'limit' => null,
			'order' => [
				'main' => [],
				'join' => []
			],
			'where' => [
				'main' => [],
				'join' => []
			],
			'selects' => [
				'main' => [],
				'join' => []
			]
		], $main);


		$main_table = $this->table->getPrefix() . '_' . $this->table->getName();
		$join_table = $this->table->getPrefix() . '_post';

		$main_abbr = self::abbr($main_table, strlen($main_table));
		$join_abbr = self::abbr($join_table, strlen($join_table));

		$select_data = [];
		$where_data = [];
		$order_data = [];

		foreach($vars['selects']['main'] as $key ) {
			$select_data[] = "{$main_abbr}.{$key}";
		}

		foreach($vars['selects']['join'] as $key ) {
			$select_data[] = "{$join_abbr}.{$key}";
		}

		foreach($vars['where']['main'] as $key => $data) {
			$where_data[] = "{$main_abbr}.{$key} " . self::getComparer($data);
		}

		foreach($vars['where']['join'] as $key => $data) {
			$where_data[] = "{$join_abbr}.{$key} " . self::getComparer($data);
		}

		foreach($vars['order']['main'] as $key => $data) {
			$order_data[] = "{$main_abbr}.{$key} {$data}";
		}

		foreach($vars['order']['join'] as $key => $data) {
			$order_data[] = "{$join_abbr}.{$key} {$data}";
		}

		$select = count($select_data) > 0 ? implode(', ', $select_data) : '*';
		$where = count($where_data) > 0 ? 'WHERE ' . implode(' AND ', $where_data) : '';
		$order = count($order_data) > 0 ? 'ORDER BY ' . implode(', ', $order_data) : '';
		$limit = $vars['limit'] !== null ? "LIMIT {$vars['limit']}" : '';

		$sql = "SELECT {$select} FROM {$main_table} {$main_abbr} LEFT JOIN {$join_table} {$join_abbr} ON {$main_abbr}.news_id = {$join_abbr}.id {$where} {$order} {$limit}";

		return $this->load_data($this->table->getName(), [
			'table' => $this->table->getName(),
			'sql' => $sql
		]);
	}
}