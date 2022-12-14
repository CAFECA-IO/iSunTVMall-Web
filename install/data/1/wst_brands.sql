SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `wst_brands`;
CREATE TABLE `wst_brands` (
  `brandId` int(11) NOT NULL AUTO_INCREMENT,
  `brandName` varchar(100) NOT NULL,
  `brandImg` varchar(150) NOT NULL,
  `brandDesc` text,
  `createTime` datetime NOT NULL,
  `dataFlag` tinyint(4) NOT NULL DEFAULT '1',
  `sortNo` int(11) DEFAULT '0',
  PRIMARY KEY (`brandId`),
  KEY `brandFlag` (`dataFlag`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;


INSERT INTO `wst_brands` VALUES ('1', '小米', 'upload/brands/2019-02/5c6fd56465c77.jpg', '小米公司正式成立于2010年4月，是一家专注于高端智能手机、互联网电视自主研发的创新型科技企业。主要由前谷歌、微软、摩托、金山等知名公司的顶尖人才组建。<br />\n<br />\n小米手机、MIUI、米聊、小米网、小米盒子、小米电视和小米路由器是小米公司旗下七大核心业务。“为发烧而生”是小米的产品理念。小米公司首创了用互联网模式开发手机操作系统的模式，将小米手机打造成全球首个互联网手机品牌。并通过互联网开发、营销和销售小米的产品。<br />\n<br />\n小米公司在机顶盒、互联网电视和路由器等领域也颠覆了传统市场。同时，小米公司也在积极打造小米生态链体系，力争全行业、全产业链都能达到共赢。<br />\n<br />\n小米团队<br />\n小米公司由著名天使投资人雷军带领创建。小米公司共计七名创始人，分别为创始人、董事长兼CEO雷军，联合创始人兼总裁林斌，联合创始人及副总裁黎万强、周光平、黄江吉、刘德、洪锋。<br />\n<br />\n小米人主要由来自微软、谷歌、金山、MOTO等国内外IT公司的资深员工所组成，小米人都喜欢创新、快速的互联网文化。小米拒绝平庸，小米人任何时 候都能让你感受到他们的创意。在小米团队中，没有冗长无聊的会议和流程，每一位小米人都在平等、轻松的伙伴式工作氛围中，享受与技术、产品、设计等各领域 顶尖人才共同创业成长的快意。<br />\n<br />\n小米名字由来<br />\n小米的LOGO是一个“MI”形，是Mobile Internet的缩写，代表小米是一家移动互联网公司。另外，小米的LOGO倒过来是一个心字，少一个点，意味着小米要让我们的用户省一点心。<br />', '2019-02-22 18:58:04', '1', '0'),
('2', '同仁堂', 'upload/brands/2019-02/5c6fd5d79a69d.png', '<p>\n	中国北京同仁堂（集团）有限责任公司，是中华老字号“同仁堂”所在公司的全称。同仁堂是中药行业著名的老字号，创建于清康熙八年（ 1669年），在三百多年的历史长河中，历代同仁堂人恪守“炮制虽繁必不敢省人工 品味虽贵必不敢减物力”的传统古训，树立“修合无人见 存心有天知”的自律意识，确保了同仁堂金字招牌的长盛不衰。自雍正元年（1721年）同仁堂正式供奉清皇宫御药房用药，历经八代皇帝，长达188年，造就了同仁堂人在制药过程中兢兢小心、精益求精的严细精神，其产品以“配方独特、选料上乘、工艺精湛、疗效显著”而享誉海内外。\n</p>\n同仁堂品牌誉满海内外，其优势得天独厚。同仁堂商标已参加了马德里协约国和巴黎公约国的注册，受到国际组织的保护，同时，在世界 50多个国家和地区办理了注册登记手续，并在台湾进行了第一个大陆商标的注册。同仁堂的著名商标和优秀品牌已成为同仁堂集团不断发展的特有优势。<br />', '2019-02-22 18:59:10', '1', '0'),
('3', '三只松鼠', 'upload/brands/2019-02/5c6fd65a654ed.jpg', '“三只松鼠”是由它的创始人兼CEO@松鼠老爹_章三疯（章燎原）先生带领一批由其来自全国的粉丝组成的创业团队创始的互联网食品品牌。章燎原先生在其任职业经理人期间曾10年打造出安徽最知名的农产品品牌，一年时间打造出网络知名坚果品牌。其较强的品牌营销理念以及草根出身的背景，使他能够迅速的掌握消费心理，在电商业界素有“电商品牌倡导者”的称号。三只松鼠便是其组建的一个全新的创业团队，这个团队正在逐渐扩大，从最初的5名创始成员发展到700人的规模，平均年龄在23岁，是一支极具生命力和挑战力的年轻团队 。', '2019-02-22 19:00:54', '1', '0'),
('4', 'Apple', 'upload/brands/2019-02/5c6fd6786cceb.png', '苹果公司（Apple Inc. ）是美国一家高科技公司。由史蒂夫·乔布斯、斯蒂夫·沃兹尼亚克和罗·韦恩(Ron Wayne)等人于1976年4月1日创立，并命名为美国苹果电脑公司（Apple Computer Inc. ），2007年1月9日更名为苹果公司，总部位于加利福尼亚州的库比蒂诺。<br />\n苹果公司1980年12月12日公开招股上市，2012年创下6235亿美元的市值记录，截至2014年6月，苹果公司已经连续三年成为全球市值最大公司。苹果公司在2016年世界500强排行榜中排名第9名。 [1]&nbsp; 2013年9月30日，在宏盟集团的“全球最佳品牌”报告中，苹果公司超过可口可乐成为世界最有价值品牌。2014年，苹果品牌超越谷歌（Google），成为世界最具价值品牌。<br />\n2016年9月8日凌晨1点，2016苹果秋季新品发布会在美国旧金山的比尔·格雷厄姆市政礼堂举行 [2]&nbsp; 。10月，苹果公司成为2016年全球100大最有价值品牌第一名。2017年1月6日早晨8点整，“红色星期五”促销活动在苹果官网正式上线，瞬间大量用户涌入官网进行抢购，仅两分钟所有参与活动的耳机便被抢光；2月，Brand Finance发布2017年度全球500强品牌榜单，苹果公司排名第二； [3]&nbsp; 6月7日，2017年《财富》美国500强排行榜发布，苹果公司排名第3位； [4]&nbsp; 7月20日，2017年世界500强排名第9位。 [5]&nbsp; 2018年12月18日，世界品牌实验室编制的《2018世界品牌500强》揭晓，苹果公司排名第3位。 [6]&nbsp;<br />\n2018年8月2日晚间，苹果盘中市值首次超过1万亿美元，股价刷新历史最高位至203.57美元，当前涨幅超过1%。<br />', '2019-02-22 19:01:56', '1', '0'),
('5', '华为', 'upload/brands/2019-02/5c6fd6b8d24af.png', '华为手机 隶属于华为消费者业务，作为华为三大核心业务之一， 华为消费者业务始于2003年底，经过十余年的发展，在中国、俄罗斯、德国、瑞典、印度及美国等地设立了16个研发中心。<br />\n2015年华为入选Brand Z全球最具价值品牌榜百强，位列科技领域品牌排名第16位。<br />\n2018年7月31日，国市场分析机构国际数据公司发布的初步数据显示，2018年第二季度，华为的出货量超过苹果手机，跃居全球第二位。<br />', '2019-02-22 19:03:28', '1', '0'),
('6', '维达', 'upload/brands/2019-02/5c6fd79537a7b.png', '维达集团为亚洲具规模的卫生用品企业。集团于1985年创建，多年来始终秉承「健康生活从维达开始」的生活理念，竭诚为每个家庭提供优质卫生护理用品和服务。维达集团于中国内地建有十大先进生产基地，于马来西亚有两大生产基地，台湾有一间生产基地，以及于澳洲一间后期加工工厂，以维达、得宝、多康、添宁、包大人、轻曲线、薇尔、 丽贝乐、Drypers等主要品牌发展生活用纸、失禁护理、女性护理及婴儿护理四大业务。<br />\n<br />\n维达集团于2014年整合爱生雅*中国内地、香港及澳门之纸巾及个人护理业务，为企业发展创下里程碑。2016年4月1日，维达集团整合爱生雅*马来西亚、台湾、南韩业务，进一步扩展业务至马来西亚、台湾、南韩、新加坡、泰国等亚洲地区，矢志发展成为亚洲领先的卫生用品公司。<br />\n<br />\n维达国际于2007年在香港联交所主板上市，股份代号3331。2017年，维达国际获纳入恒生可持续发展企业基准指数成份股。全球领先的卫生用品公司爱适瑞集团为其控股股东。<br />\n<br />\n*爱生雅集团（其卫生用品业务分拆，成为爱适瑞集团）<br />', '2019-02-22 19:05:58', '1', '0'),
('7', '鲜丰水果', 'upload/brands/2019-02/5c6fd8563ad6b.png', '鲜丰水果', '2019-02-22 19:09:54', '1', '0'),
('8', '优品水果', 'upload/brands/2019-02/5c6fd88b0ea78.png', '优品水果', '2019-02-22 19:10:12', '1', '0'),
('9', '新鲜鲜果', 'upload/brands/2019-02/5c6fd8a0b76a1.jpg', '新鲜鲜果', '2019-02-22 19:10:45', '1', '0'),
('10', '鲜果食光', 'upload/brands/2019-02/5c6fd8ef8638c.png', '鲜果食光', '2019-02-22 19:11:47', '1', '0'),
('11', '猫果', 'upload/brands/2019-02/5c6fd935d7c53.png', '猫果', '2019-02-22 19:13:04', '1', '0'),
('13', '花果山', 'upload/brands/2019-02/5c6fd9aad38b6.png', '花果山', '2019-02-22 19:15:00', '1', '0');
