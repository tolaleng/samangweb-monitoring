CREATE TABLE `activate` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `calendar` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `information` varchar(255) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `state` enum('0','1','2') NOT NULL DEFAULT '0',
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `config` (
  `name` varchar(30) NOT NULL DEFAULT 'Website Monitor',
  `timeout` varchar(11) NOT NULL DEFAULT '9',
  `default_max_servers` int(11) NOT NULL DEFAULT '5',
  `captcha` enum('1','0') NOT NULL DEFAULT '1',
  `register` enum('1','0') NOT NULL DEFAULT '1',
  `keep_history` int(11) NOT NULL DEFAULT '14',
  `keep_events` int(11) NOT NULL DEFAULT '14',
  `custom_server_interval` enum('1','0') NOT NULL DEFAULT '1',
  `default_language` varchar(5) NOT NULL DEFAULT 'en',
  `user_activate` enum('1','0') NOT NULL DEFAULT '1',
  `date_format` varchar(255) NOT NULL DEFAULT 'd-m-Y',
  `time_format` varchar(255) NOT NULL DEFAULT 'H:i:s',
  `last_cron` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `info_send` enum('1','0') NOT NULL DEFAULT '0',
  `version` varchar(10) NOT NULL DEFAULT '1.4.4'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `history` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `load_average` varchar(11) NOT NULL DEFAULT '0',
  `request_succeed` int(11) NOT NULL DEFAULT '0',
  `request_failed` int(11) NOT NULL DEFAULT '0',
  `response_codes` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `mail_settings` (
  `mail_type` enum('php','smtp') NOT NULL DEFAULT 'php',
  `php_mail` varchar(255) NOT NULL DEFAULT '',
  `smtp_host` varchar(255) NOT NULL DEFAULT '',
  `smtp_port` varchar(255) NOT NULL DEFAULT '',
  `smtp_username` varchar(255) NOT NULL DEFAULT '',
  `smtp_password` varchar(500) NOT NULL DEFAULT '',
  `smtp_from` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pushbullet` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `access_token` varchar(500) NOT NULL,
  `email` varchar(150) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `reset_password` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `response_codes` (
  `code` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `response_codes` (`code`) VALUES
(200),
(201),
(202),
(203),
(204),
(205),
(206),
(300),
(301),
(302),
(307),
(308),
(400),
(401),
(402),
(403),
(404),
(405),
(413);

CREATE TABLE `servers` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `server_url` varchar(300) NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `email_to` varchar(100) NOT NULL DEFAULT '',
  `last_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `back_online` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_check` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_load` varchar(10) NOT NULL DEFAULT '0.000',
  `response_code` int(3) NOT NULL DEFAULT '0',
  `curl_error` varchar(255) NOT NULL DEFAULT '',
  `deleted` enum('1','0') NOT NULL DEFAULT '0',
  `disabled` enum('1','0') NOT NULL DEFAULT '0',
  `desktop_notif` enum('1','0') NOT NULL DEFAULT '1',
  `pushbullet` int(11) NOT NULL DEFAULT '0',
  `widget` enum('1','0') NOT NULL DEFAULT '0',
  `check_time` int(11) NOT NULL DEFAULT '1',
  `timeout` int(11) NOT NULL DEFAULT '0',
  `state` enum('active','down','unknown') NOT NULL DEFAULT 'unknown'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `server_events` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `state` enum('up','down') NOT NULL,
  `response_code` int(11) NOT NULL,
  `curl_error` varchar(255) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `server_stats` (
  `id` int(11) NOT NULL,
  `server_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `response_code` int(11) NOT NULL,
  `load_time` varchar(10) NOT NULL,
  `curl_error` varchar(255) NOT NULL,
  `check_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` enum('active','down') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `hash` text NOT NULL,
  `ip` varchar(60) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `chart_1` int(11) NOT NULL DEFAULT '0',
  `chart_2` int(11) NOT NULL DEFAULT '0',
  `max_servers` int(11) NOT NULL DEFAULT '5',
  `admin` enum('1','0') NOT NULL DEFAULT '0',
  `last_signin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `theme` enum('light','dark') NOT NULL DEFAULT 'light',
  `language` varchar(5) NOT NULL DEFAULT '',
  `active` enum('1','0') NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `activate`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `calendar`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `config`
  ADD PRIMARY KEY (`version`);

ALTER TABLE `history`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pushbullet`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `reset_password`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `response_codes`
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `code_2` (`code`);

ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `server_events`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `server_stats`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `activate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pushbullet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reset_password`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `server_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `server_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;