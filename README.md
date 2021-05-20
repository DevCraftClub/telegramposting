[![GitHub issues](https://img.shields.io/github/issues/Gokujo/telegramposting.svg?style=flat-square)](https://github.com/Gokujo/telegramposting/issues)
[![GitHub forks](https://img.shields.io/github/forks/Gokujo/telegramposting.svg?style=flat-square)](https://github.com/Gokujo/telegramposting/network)

![DLE-11.x](https://img.shields.io/badge/DLE-11.x-red.svg?style=flat-square)
![DLE-12.x](https://img.shields.io/badge/DLE-12.x-red.svg?style=flat-square)
![DLE-13.x](https://img.shields.io/badge/DLE-13.x-red.svg?style=flat-square)
![DLE-14.x](https://img.shields.io/badge/DLE-14.x-green.svg?style=flat-square)

![Версия_релиза](https://img.shields.io/github/manifest-json/v/Gokujo/telegramposting?filename=manifest.json&style=flat-square)

# Telegram Posting
Отправка сообщений в телеграм при отправке новостей на сайте
Полная совместимость проверенна на DLE-версиях 14.х

# Подключение в сторонние разработки
**Полезно для парсеров**

После того, как новость будет сохранена в базу данных, добавьте этот код:

```PHP
$tg_post_id = $news_id; #идентификатор ID новости
$tg_post_type = 'addnews'; #тип добавления, addnews или editnews
@include DLEPlugins::Check(ENGINE_DIR . '/inc/maharder/telegram/custom_add.php');
```



Для установки достаточно скачать [релиз](https://github.com/Gokujo/telegramposting/releases).

**Установка / Обновление**

1. **У вас три варианта для установки:**

1.1. **При помощи bat-Скрипта. Для пользователей Windows**

Для этого устанавливаем [7Zip](https://www.7-zip.org/download.html).
После установки запускаем скрипт install_archive.bat.
После завершения установки - загружаем install.zip в менеджер плагинов.

1.2. **Упаковать самому**

Любым архиватором запаковать всё содержимое в папке upload (нужен формат zip!), причём так, чтобы в корне архива был файл install.xml и папка engine.
Затем устанавливаем архив через менеджер плагинов.

1.3. **Просто залить**

Залейте папку engine в корень сайта и установите плагин через менеджер плагинов.


2. Для дополнительных инструкций проследуйте https://devcraft.club/articles/telegram-posting.7/
