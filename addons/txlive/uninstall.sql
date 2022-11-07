DROP TABLE IF EXISTS `wst_tx_lives`;
DROP TABLE IF EXISTS `wst_tx_live_goods`;
DROP TABLE IF EXISTS `wst_tx_live_replays`;
DROP TABLE IF EXISTS `wst_tx_live_user_login_logs`;
delete from `wst_crons` where croncode='getTxLiveReplays';
