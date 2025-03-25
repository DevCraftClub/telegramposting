<?php

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity(role: 'telegram_posting_cron', repository: TgCronRepository::class, table: 'telegram_posting_cron')]
class TgCron extends BasisModel {
	#[Column('bigPrimary')]
	public int               $id;
	#[Column('bigInteger')]
	public int               $news_id;
	#[Column('datetime', default: 'CURRENT_TIMESTAMP')]
	public DateTimeImmutable $time;
	#[Column('string')]
	public string            $type;

	public function getColumnVal(string $name): string|int|DateTimeImmutable {
		return match ($name) {
			'news_id' => $this->news_id,
			'time'    => $this->time,
			'type'    => $this->type,
			default   => $this->id
		};
	}
}
