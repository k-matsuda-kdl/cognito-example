USE demo;

CREATE TABLE `demo_table` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL COMMENT 'Eメール',
  `email2` varchar(255) DEFAULT NULL COMMENT 'Eメール2',
  `email3` varchar(255) DEFAULT NULL COMMENT 'Eメール3',
  `disp_name` varchar(20) NOT NULL COMMENT '表示名',
  `role` varchar(255) DEFAULT NULL COMMENT '権限',
  `user_name` varchar(255) DEFAULT NULL COMMENT 'ユーザ名',
  `remember_token` varchar(100) DEFAULT NULL COMMENT 'Autoログイントークン',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時',
  `create_account_id` bigint unsigned DEFAULT NULL COMMENT '作成者',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時',
  `update_account_id` bigint unsigned DEFAULT NULL COMMENT '更新者',
  PRIMARY KEY (`id`),
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;