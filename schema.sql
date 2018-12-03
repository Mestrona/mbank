/** Initial Database Table Version */

CREATE TABLE `mbank_transactions`
(
  `id`                         bigint(20) UNSIGNED                  NOT NULL,
  `date`                       datetime                             NOT NULL,
  `valuta_date`                datetime                             NOT NULL,
  `amount`                     decimal(8,2)                         NOT NULL,
  `currency`                   varchar(3) COLLATE utf8_unicode_ci   NOT NULL,
  `purpose`                    varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remote_bank_code`           varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remote_account_number`      varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `remote_account_holder_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `local_bank_code`            varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `local_account_number`       varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `local_account_holder_name`  varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

ALTER TABLE `mbank_transactions`
  ADD PRIMARY KEY (`id`);
