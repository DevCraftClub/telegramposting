![Текущая версия](https://img.shields.io/github/manifest-json/v/DevCraftClub/telegramposting/master?style=for-the-badge&label=%D0%92%D0%B5%D1%80%D1%81%D0%B8%D1%8F)![Статус разработки](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FDevCraftClub%2Ftelegramposting%2Frefs%2Fheads%2Fmaster%2Fmanifest.json&query=%24.status&style=for-the-badge&label=%D0%A1%D1%82%D0%B0%D1%82%D1%83%D1%81&color=orange)

![Версия DLE](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FDevCraftClub%2Ftelegramposting%2Frefs%2Fheads%2Fmaster%2Fmanifest.json&query=%24.dle&style=for-the-badge&label=DLE)![Версия PHP](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FDevCraftClub%2Ftelegramposting%2Frefs%2Fheads%2Fmaster%2Fmanifest.json&query=%24.php&style=for-the-badge&logo=php&logoColor=777BB4&label=PHP&color=777BB4)![Версия MHAdmin](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FDevCraftClub%2Ftelegramposting%2Frefs%2Fheads%2Fmaster%2Fmanifest.json&query=%24.mhadmin&style=for-the-badge&label=MH-ADMIN&color=red)

# Telegram Posting
Отправка сообщений в телеграм при отправке новостей на сайте
Полная совместимость проверенна на DLE-версиях 15.х

# Подключение в сторонние разработки
**Полезно для парсеров**

После того, как новость будет сохранена в базу данных, добавьте этот код:

```PHP
include_once (DLEPlugins::Check(ENGINE_DIR . "/inc/maharder/telegram/helpers/sender.php"));
sendTelegram($id, $type); // $id - ID новости, $type - шаблон оформления (addnews, editnews, cron_addnews, cron_editnews)
```


# Скриншоты

[![](http://i120.fastpic.org/big/2022/0606/cd/73509c64d18e61bdce14edf2f8b762cd.jpg)](http://i120.fastpic.org/big/2022/0606/97/_fc27cfc646a4ef53a6b3321bc27d2a97.png)  [![](http://i120.fastpic.org/big/2022/0606/b6/ff8d3d63c1ffe359a0c71dbd249bb2b6.jpg)](http://i120.fastpic.org/big/2022/0606/ce/_4d4075b71ee11b4b8bb90711fe6639ce.png)  [![](http://i120.fastpic.org/big/2022/0606/43/dbcb0fa1f9219b39b6d5f43472d3a543.jpg)](http://i120.fastpic.org/big/2022/0606/d4/_d32064f40baad76a47f675f91b7606d4.png)  [![](http://i120.fastpic.org/big/2022/0606/14/b113800f03e62a2028c24579eb346414.jpg)](http://i120.fastpic.org/big/2022/0606/7d/6283672176e66a0ef6d837c30ad2877d.png)

[![](http://i120.fastpic.org/big/2022/0606/11/7c5be59f6ba20d0df9757fde8aabb411.jpg)](http://i120.fastpic.org/big/2022/0606/f5/ad3c4b097b1e45312c94ac42cebff3f5.png)  [![](http://i120.fastpic.org/big/2022/0606/30/01eb3cade0d1a1a76c3b8bf76afe7030.jpg)](http://i120.fastpic.org/big/2022/0606/2d/0223f92d7b188d741b7d4fc6288c072d.png)  [![](http://i120.fastpic.org/big/2022/0606/f2/7badf1f9be7c7d3da0c72d6fcec636f2.jpg)](http://i120.fastpic.org/big/2022/0606/45/0645b5922523293607c26eabdda37845.png)  [![](http://i120.fastpic.org/big/2022/0606/95/df656f55fba1e7cca9415a25f1c0d195.jpg)](http://i120.fastpic.org/big/2022/0606/a3/456d0e6be3590a0f996ff932024b7aa3.png)

[![](http://i120.fastpic.org/big/2022/0606/73/fe270a3355604bbf17677a9002493173.jpg)](http://i120.fastpic.org/big/2022/0606/a6/330009eb48876f27a45837c3d91ecda6.png)  [![](http://i120.fastpic.org/big/2022/0607/20/0f93b1d7b7c8c609b3897ff6b016ea20.jpg)](http://i120.fastpic.org/big/2022/0607/fe/5919c1a614383a6aadf47df21efb83fe.png)  [![](http://i120.fastpic.org/big/2022/0607/62/88270468cb151be063ad5cba97630d62.jpg)](http://i120.fastpic.org/big/2022/0607/f7/6e6900dc2d4213967c1c161463b1a2f7.png)  [![](http://i120.fastpic.org/big/2022/0607/1e/8b9be57421732ceae87a0f5eb8638a1e.jpg)](http://i120.fastpic.org/big/2022/0607/79/747321a9bee7a6459a2f91585665e379.png) 

 [![](http://i120.fastpic.org/big/2022/0607/6b/1d8d2b9d92e80a5c809d4e477169016b.jpg)](http://i120.fastpic.org/big/2022/0607/ee/2195d85f43d09e4f3b0088874c31a6ee.png) [![](http://i120.fastpic.org/big/2022/0607/47/385545757ab1315ebcd2c98120793947.jpg)](http://i120.fastpic.org/big/2022/0607/cb/eff26371704200543fc23fe50276b6cb.png)  [![](http://i120.fastpic.org/big/2022/0607/51/6ab22ec3b9389dc67f0201aeb5219c51.jpg)](http://i120.fastpic.org/big/2022/0607/c6/_2af59418b887d159ef97e96975bbb2c6.png)  [![](http://i120.fastpic.org/big/2022/0607/31/90d494bfad646c557eb9861b1abfb731.jpg)](http://i120.fastpic.org/big/2022/0607/3e/e8bea90a9fe2380b841c0e5265113b3e.png)  
 
 [![](http://i120.fastpic.org/big/2022/0607/de/75999f7b30af991d4bc0c8705b80ccde.jpg)](http://i120.fastpic.org/big/2022/0607/64/885c8f5d5b32ead2503871e8af19f364.png)  [![](http://i120.fastpic.org/big/2022/0607/c4/e011936663bd60d5dd2a350a9dfa40c4.jpg)](http://i120.fastpic.org/big/2022/0607/e8/36469586747076106ed5533f5f7d2de8.png)



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


2. Для дополнительных инструкций проследуйте https://readme.devcraft.club/latest/dev/telegramposting/install/
