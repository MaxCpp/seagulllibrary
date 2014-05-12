<?
/*	Class CCountry 0.0.1
	Date update 0.0.1: 2012-10-02
*/

class CCountry {

	function __construct() { //---------------------------------------------
		$this->table = '`seagull_countries`';
		$this->tablename = 'seagull_countries';
	}

	function get_name($id, $lang='ru') { //---------------------------------------------
		
		$name = retr_sql('SELECT `name_'.$lang.'` FROM '.$this->table.' WHERE `id`='.$id);

		if ($name)
			return ($name);
		else
			return 0;
	}

	function getCountriesHTML($lang='ru', $select=NULL) { //---------------------------------------------

		$arr = sql2table('SELECT * FROM '.$this->table.' ORDER BY `name_'.$lang.'`');
		
		$html = '<option value="0">------</option>';
		foreach ($arr as $country) {
			if ($country['id'] == $select)
				$html .= '<option value="'.$country['id'].'" selected="selected">'.$country['name_'.$lang].'</option>';
			else
				$html .= '<option value="'.$country['id'].'">'.$country['name_'.$lang].'</option>';
		}

		if ($html)
			return $html;
		else
			return 0;
	}

	function getArrName($lang='ru', $select=NULL) { //---------------------------------------------

		$arr = sql2array('SELECT `id`, `name_'.$lang.'` FROM '.$this->table.' ORDER BY `name_'.$lang.'`', 'id', 'name_'.$lang);
		array_unshift($arr, '------');

		if ($arr)
			return $arr;
		else
			return 0;
	}
	
	function install() {
		global $dbase;

		$r = retr_sql('SHOW TABLE STATUS FROM '.$dbase." LIKE '".$this->tablename."'");
		if (!$r) {
			$r = run_sql('CREATE TABLE '.$this->table." (
			  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
			  `name_ru` varchar(255) NOT NULL,
			  `fullname_ru` varchar(255) NOT NULL,
			  `name_en` varchar(255) NOT NULL,
			  `alpha2` varchar(2) NOT NULL,
			  `alpha3` varchar(3) NOT NULL,
			  `iso` varchar(3) NOT NULL,
			  `location` varchar(255) NOT NULL,
			  `location-precise` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
			if ($r)
				$this->msg->setOk('������� "'.$this->table.'" �������');
		} else
			$this->msg->setWarning('������� "'.$this->table.'" ��� �������');


		run_sql("INSERT INTO `seagull_countries`(`id`,`name_ru`,`fullname_ru`,`name_en`,`alpha2`,`alpha3`,`iso`,`location`,`location-precise`) VALUES (1,'���������','','Australia','AU','AUS','036','�������','��������� � ����� ��������\n'),(2,'�������','����������� ����������','Austria','AT','AUT','040','������','�������� ������\n'),(3,'�����������','���������� �����������','Azerbaijan','AZ','AZE','031','����','�������� ����\n'),(4,'�������','���������� �������','Albania','AL','ALB','008','������','����� ������\n'),(5,'�����','��������� �������� ��������������� ����������','Algeria','DZ','DZA','012','������','�������� ������\n'),(6,'������������ �����','','American Samoa','AS','ASM','016','�������','���������\n'),(7,'�������','','Anguilla','AI','AIA','660','�������','��������� �������\n'),(8,'������','���������� ������','Angola','AO','AGO','024','������','����������� ������\n'),(9,'�������','��������� �������','Andorra','AD','AND','020','������','����� ������\n'),(10,'����������','','Antarctica','AQ','ATA','010','����������',' \n'),(11,'������� � �������','','Antigua and Barbuda','AG','ATG','028','�������','��������� �������\n'),(12,'���������','������������ ����������','Argentina','AR','ARG','032','�������','����� �������\n'),(13,'�������','���������� �������','Armenia','AM','ARM','051','����','�������� ����\n'),(14,'�����','','Aruba','AW','ABW','533','�������','��������� �������\n'),(15,'����������','���������� ��������� ����������� ����������','Afghanistan','AF','AFG','004','����','����� ����� ����������� ����\n'),(16,'������','����������� ������','Bahamas, The','BS','BHS','044','�������','��������� �������\n'),(17,'���������','�������� ���������� ���������','Bangladesh','BD','BGD','050','����','����� ����� ����������� ����\n'),(18,'��������','','Barbados','BB','BRB','052','�������','��������� �������\n'),(19,'�������','����������� �������','Bahrain','BH','BHR','048','����','�������� ����\n'),(20,'��������','���������� ��������','Belarus','BY','BLR','112','������','��������� ������\n'),(21,'�����','','Belize','BZ','BLZ','084','�������','��������� �������\n'),(22,'�������','����������� �������','Belgium','BE','BEL','056','������','�������� ������\n'),(23,'�����','���������� �����','Benin','BJ','BEN','204','������','�������� ������\n'),(24,'�������','','Bermuda','BM','BMU','060','�������','�������� �������\n'),(25,'��������','���������� ��������','Bulgaria','BG','BGR','100','������','��������� ������\n'),(26,'�������','���������� �������','Bolivia','BO','BOL','068','�������','����� �������\n'),(27,'������ � �����������','','Bosnia and Herzegovina','BA','BIH','070','������','����� ������\n'),(28,'��������','���������� ��������','Botswana','BW','BWA','072','������','����� ����� ������\n'),(29,'��������','������������ ���������� ��������','Brazil','BR','BRA','076','�������','����� �������\n'),(30,'���������� ���������� � ��������� ������','','British Indian Ocean Territory','IO','IOT','086','�������','��������� �����\n'),(31,'������-����������','','Brunei','BN','BRN','096','����','���-��������� ����\n'),(32,'�������-����','','Burkina Faso','BF','BFA','854','������','�������� ������\n'),(33,'�������','���������� �������','Burundi','BI','BDI','108','������','��������� ������\n'),(34,'�����','����������� �����','Bhutan','BT','BTN','064','����','����� ����� ����������� ����\n'),(35,'�������','���������� �������','Vanuatu','VU','VUT','548','�������','���������\n'),(36,'�������','���������� ����������','Hungary','HU','HUN','348','������','��������� ������\n'),(37,'���������','������������� ���������� ���������','Venezuela','VE','VEN','862','�������','����� �������\n'),(38,'���������� �������, ����������','���������� ���������� �������','British Virgin Islands','VG','VGB','092','�������','��������� �������\n'),(39,'���������� �������, ���','���������� ������� ����������� ������','Virgin Islands','VI','VIR','850','�������','��������� �������\n'),(40,'�������','���������������� ���������� �������','Vietnam','VN','VNM','704','����','���-��������� ����\n'),(41,'�����','��������� ����������','Gabon','GA','GAB','266','������','����������� ������\n'),(42,'�����','���������� �����','Haiti','HT','HTI','332','�������','��������� �������\n'),(43,'������','���������� ������','Guyana','GY','GUY','328','�������','����� �������\n'),(44,'������','���������� ������','Gambia, The','GM','GMB','270','������','�������� ������\n'),(45,'����','���������� ����','Ghana','GH','GHA','288','������','�������� ������\n'),(46,'���������','','Guadeloupe','GP','GLP','312','�������','��������� �������\n'),(47,'���������','���������� ���������','Guatemala','GT','GTM','320','�������','����������� �������\n'),(48,'������','���������� ����������','Guinea','GN','GIN','324','������','�������� ������\n'),(49,'������-�����','���������� ������-�����','Guinea-Bissau','GW','GNB','624','������','�������� ������\n'),(50,'��������','������������ ���������� ��������','Germany','DE','DEU','276','������','�������� ������\n'),(51,'������','','Guernsey','GG','GGY','831','������','�������� ������\n'),(52,'���������','','Gibraltar','GI','GIB','292','������','����� ������\n'),(53,'��������','���������� ��������','Honduras','HN','HND','340','�������','����������� �������\n'),(54,'�������','�����������  ����������������  ������ ����� �������','Hong Kong','HK','HKG','344','����','��������� ����\n'),(55,'�������','','Grenada','GD','GRD','308','�������','��������� �������\n'),(56,'����������','','Greenland','GL','GRL','304','�������','�������� �������\n'),(57,'������','��������� ����������','Greece','GR','GRC','300','������','����� ������\n'),(58,'������','','Georgia','GE','GEO','268','����','�������� ����\n'),(59,'����','','Guam','GU','GUM','316','�������','����������\n'),(60,'�����','����������� �����','Denmark','DK','DNK','208','������','�������� ������\n'),(61,'������','','Jersey','JE','JEY','832','������','�������� ������\n'),(62,'�������','���������� �������','Djibouti','DJ','DJI','262','������','��������� ������\n'),(63,'��������','����������� ��������','Dominica','DM','DMA','212','�������','��������� �������\n'),(64,'������������� ����������','','Dominican Republic','DO','DOM','214','�������','��������� �������\n'),(65,'������','�������� ���������� ������','Egypt','EG','EGY','818','������','�������� ������\n'),(66,'������','���������� ������','Zambia','ZM','ZMB','894','������','��������� ������\n'),(67,'�������� ������','','Western Sahara','EH','ESH','732','������','�������� ������\n'),(68,'��������','���������� ��������','Zimbabwe','ZW','ZWE','716','������','��������� ������\n'),(69,'�������','����������� �������','Israel','IL','ISR','376','����','�������� ����\n'),(70,'�����','���������� �����','India','IN','IND','356','����','����� ����� ����������� ����\n'),(71,'���������','���������� ���������','Indonesia','ID','IDN','360','����','���-��������� ����\n'),(72,'��������','���������� ����������� �����������','Jordan','JO','JOR','400','����','�������� ����\n'),(73,'����','���������� ����','Iraq','IQ','IRQ','368','����','�������� ����\n'),(74,'����, ��������� ����������','��������� ���������� ����','Iran','IR','IRN','364','����','����� ����� ����������� ����\n'),(75,'��������','','Ireland','IE','IRL','372','������','�������� ������\n'),(76,'��������','���������� ��������','Iceland','IS','ISL','352','������','�������� ������\n'),(77,'�������','����������� �������','Spain','ES','ESP','724','������','����� ������\n'),(78,'������','����������� ����������','Italy','IT','ITA','380','������','����� ������\n'),(79,'�����','��������� ����������','Yemen','YE','YEM','887','����','�������� ����\n'),(80,'����-�����','���������� ����-�����','Cape Verde','CV','CPV','132','������','�������� ������\n'),(81,'���������','���������� ���������','Kazakhstan','KZ','KAZ','398','����','����� ����� ����������� ����\n'),(82,'��������','����������� ��������','Cambodia','KH','KHM','116','����','���-��������� ����\n'),(83,'�������','���������� �������','Cameroon','CM','CMR','120','������','����������� ������\n'),(84,'������','','Canada','CA','CAN','124','�������','�������� �������\n'),(85,'�����','����������� �����','Qatar','QA','QAT','634','����','�������� ����\n'),(86,'�����','���������� �����','Kenya','KE','KEN','404','������','��������� ������\n'),(87,'����','���������� ����','Cyprus','CY','CYP','196','����','�������� ����\n'),(88,'��������','���������� ����������','Kyrgyzstan','KG','KGZ','417','����','����� ����� ����������� ����\n'),(89,'��������','���������� ��������','Kiribati','KI','KIR','296','�������','����������\n'),(90,'�����','��������� �������� ����������','China','CN','CHN','156','����','��������� ����\n'),(91,'��������� (������) �������','','Cocos (Keeling) Islands','CC','CCK','166','�������','��������� �����\n'),(92,'��������','���������� ��������','Colombia','CO','COL','170','�������','����� �������\n'),(93,'������','���� ������','Comoros','KM','COM','174','������','��������� ������\n'),(94,'�����','���������� �����','Congo, Republic of the','CG','COG','178','������','����������� ������\n'),(95,'�����, ��������������� ����������','��������������� ���������� �����','Congo, Democratic Republic of the','CD','COD','180','������','����������� ������\n'),(96,'������','���������� ������','Kosovo','',' ','','������','����� ������\n'),(97,'�����-����','���������� �����-����','Costa Rica','CR','CRI','188','�������','����������� �������\n'),(98,'��� �\'�����','���������� ��� �\'�����','Cote d\'Ivoire','CI','CIV','384','������','�������� ������\n'),(99,'����','���������� ����','Cuba','CU','CUB','192','�������','��������� �������\n'),(100,'������','����������� ������','Kuwait','KW','KWT','414','����','�������� ����\n'),(101,'����','�������� �������-��������������� ����������','Laos','LA','LAO','418','����','���-��������� ����\n'),(102,'������','���������� ����������','Latvia','LV','LVA','428','������','�������� ������\n'),(103,'������','����������� ������','Lesotho','LS','LSO','426','������','����� ����� ������\n'),(104,'�����','��������� ����������','Lebanon','LB','LBN','422','����','�������� ����\n'),(105,'��������� �������� ����������','���������������� �������� ��������� �������� ����������','Libya','LY','LBY','434','������','�������� ������\n'),(106,'�������','���������� �������','Liberia','LR','LBR','430','������','�������� ������\n'),(107,'�����������','��������� �����������','Liechtenstein','LI','LIE','438','������','�������� ������\n'),(108,'�����','��������� ����������','Lithuania','LT','LTU','440','������','�������� ������\n'),(109,'����������','������� ���������� ����������','Luxembourg','LU','LUX','442','������','�������� ������\n'),(110,'��������','���������� ��������','Mauritius','MU','MUS','480','������','��������� ������\n'),(111,'����������','��������� ���������� ����������','Mauritania','MR','MRT','478','������','�������� ������\n'),(112,'����������','���������� ����������','Madagascar','MG','MDG','450','������','��������� ������\n'),(113,'�������','','Mayotte','YT','MYT','175','������','����� ����� ������\n'),(114,'�����','����������� ���������������� ������ ����� �����','Macau','MO','MAC','446','����','��������� ����\n'),(115,'������','���������� ������','Malawi','MW','MWI','454','������','��������� ������\n'),(116,'��������','','Malaysia','MY','MYS','458','����','���-��������� ����\n'),(117,'����','���������� ����','Mali','ML','MLI','466','������','�������� ������\n'),(118,'����� ������������� ���������� ������� ����������� ������','','United States Pacific Island Wildlife Refuges','UM','UMI','581','�������','��������� �����\n'),(119,'��������','����������� ����������','Maldives','MV','MDV','462','����','����� ����� ����������� ����\n'),(120,'������','���������� ������','Malta','MT','MLT','470','������','����� ������\n'),(121,'�������','����������� �������','Morocco','MA','MAR','504','������','�������� ������\n'),(122,'���������','','Martinique','MQ','MTQ','474','�������','��������� �������\n'),(123,'���������� �������','���������� ���������� �������','Marshall Islands','MH','MHL','584','�������','����������\n'),(124,'�������','������������ ����������� �����','Mexico','MX','MEX','484','�������','����������� �������\n'),(125,'����������, ������������ �����','������������ ����� ����������','Micronesia, Federated States of','FM','FSM','583','�������','����������\n'),(126,'��������','���������� ��������','Mozambique','MZ','MOZ','508','������','��������� ������\n'),(127,'�������, ����������','���������� �������','Moldova','MD','MDA','498','������','��������� ������\n'),(128,'������','��������� ������','Monaco','MC','MCO','492','������','�������� ������\n'),(129,'��������','','Mongolia','MN','MNG','496','����','��������� ����\n'),(130,'����������','','Montserrat','MS','MSR','500','�������','��������� �������\n'),(131,'������','���� ������','Burma','MM','MMR','104','����','���-��������� ����\n'),(132,'�������','���������� �������','Namibia','NA','NAM','516','������','����� ����� ������\n'),(133,'�����','���������� �����','Nauru','NR','NRU','520','�������','����������\n'),(134,'�����','����������� �����','Nepal','NP','NPL','524','����','����� ����� ����������� ����\n'),(135,'�����','���������� �����','Niger','NE','NER','562','������','�������� ������\n'),(136,'�������','������������ ���������� �������','Nigeria','NG','NGA','566','������','�������� ������\n'),(137,'������������� ������','','Netherlands Antilles','AN','ANT','530','�������','��������� �������\n'),(138,'����������','����������� �����������','Netherlands','NL','NLD','528','������','�������� ������\n'),(139,'���������','���������� ���������','Nicaragua','NI','NIC','558','�������','����������� �������\n'),(140,'����','���������� ����','Niue','NU','NIU','570','�������','���������\n'),(141,'����� ��������','','New Zealand','NZ','NZL','554','�������','��������� � ����� ��������\n'),(142,'����� ���������','','New Caledonia','NC','NCL','540','�������','���������\n'),(143,'��������','����������� ��������','Norway','NO','NOR','578','������','�������� ������\n'),(144,'������������ �������� �������','','United Arab Emirates','AE','ARE','784','����','�������� ����\n'),(145,'����','�������� ����','Oman','OM','OMN','512','����','�������� ����\n'),(146,'������ ����','','Bouvet Island','BV','BVT','074','','����� �����\n'),(147,'������ ����������','','Clipperton Island','CP','',' ','�������','����� �����\n'),(148,'������ ���','','Isle of Man','IM','IMN','833','������','�������� ������\n'),(149,'������ �������','','Norfolk Island','NF','NFK','574','�������','��������� � ����� ��������\n'),(150,'������ ���������','','Christmas Island','CX','CXR','162','����','��������� �����\n'),(151,'������ ������� �������','','Saint Martin','MF','MAF','663','�������','��������� �������\n'),(152,'������ ���� � ������� ����������','','Heard Island and McDonald Islands','HM','HMD','334','','��������� �����\n'),(153,'������� ������','','Cayman Islands','KY','CYM','136','�������','��������� �������\n'),(154,'������� ����','','Cook Islands','CK','COK','184','�������','���������\n'),(155,'������� ����� � ������','','Turks and Caicos Islands','TC','TCA','796','�������','��������� �������\n'),(156,'��������','��������� ���������� ��������','Pakistan','PK','PAK','586','����','����� ����� ����������� ����\n'),(157,'�����','���������� �����','Palau','PW','PLW','585','�������','����������\n'),(158,'������������ ����������, ��������������','�������������� ������������ ����������','Palestinian Territory, Occupied','PS','PSE','275','����','�������� ����\n'),(159,'������','���������� ������','Panama','PA','PAN','591','�������','����������� �������\n'),(160,'������� ������� (����������� &mdash; ����� �������)','','Holy See (Vatican City)','VA','VAT','336','������','����� ������\n'),(161,'�����-����� ������','','Papua New Guinea','PG','PNG','598','�������','���������\n'),(162,'��������','���������� ��������','Paraguay','PY','PRY','600','�������','����� �������\n'),(163,'����','���������� ����','Peru','PE','PER','604','�������','����� �������\n'),(164,'�������','','Pitcairn','PN','PCN','612','�������','���������\n'),(165,'������','���������� ������','Poland','PL','POL','616','������','��������� ������\n'),(166,'����������','������������� ����������','Portugal','PT','PRT','620','������','����� ������\n'),(167,'������-����','','Puerto Rico','PR','PRI','630','�������','��������� �������\n'),(168,'���������� ���������','','Macedonia','MK','MKD','807','������','����� ������\n'),(169,'�������','','Reunion','RE','REU','638','������','��������� ������\n'),(170,'������','���������� ���������','Russia','RU','RUS','643','������','��������� ������\n'),(171,'������','����������� ����������','Rwanda','RW','RWA','646','������','��������� ������\n'),(172,'�������','','Romania','RO','ROU','642','������','��������� ������\n'),(173,'�����','����������� ����������� �����','Samoa','WS','WSM','882','�������','���������\n'),(174,'���-������','���������� ���-������','San Marino','SM','SMR','674','������','����� ������\n'),(175,'���-���� � ��������','��������������� ���������� ���-���� � ��������','Sao Tome and Principe','ST','STP','678','������','����������� ������\n'),(176,'���������� ������','����������� ���������� ������','Saudi Arabia','SA','SAU','682','����','�������� ����\n'),(177,'���������','����������� ���������','Swaziland','SZ','SWZ','748','������','����� ����� ������\n'),(178,'������ �����','','Saint Helena','SH','SHN','654','������','�������� ������\n'),(179,'�������� �����','��������� �������-��������������� ����������','Korea, North','KP','PRK','408','����','��������� ����\n'),(180,'�������� ���������� �������','����������� �������� ���������� ��������','Northern Mariana Islands','MP','MNP','580','�������','����������\n'),(181,'���-���������','','Saint Barthelemy','BL','BLM','652','�������','��������� �������\n'),(182,'���-���� � �������','','Saint Pierre and Miquelon','PM','SPM','666','�������','�������� �������\n'),(183,'�������','���������� �������','Senegal','SN','SEN','686','������','�������� ������\n'),(184,'����-������� � ���������','','Saint Vincent and the Grenadines','VC','VCT','670','�������','��������� �������\n'),(185,'����-�����','','Saint Lucia','LC','LCA','662','�������','��������� �������\n'),(186,'����-���� � �����','','Saint Kitts and Nevis','KN','KNA','659','�������','��������� �������\n'),(187,'������','���������� ������','Serbia','RS','SRB','688','������','����� ������\n'),(188,'�������','���������� �������','Seychelles','SC','SYC','690','������','��������� ������\n'),(189,'��������','���������� ��������','Singapore','SG','SGP','702','����','���-��������� ����\n'),(190,'��������� �������� ����������','','Syria','SY','SYR','760','����','�������� ����\n'),(191,'��������','��������� ����������','Slovakia','SK','SVK','703','������','��������� ������\n'),(192,'��������','���������� ��������','Slovenia','SI','SVN','705','������','����� ������\n'),(193,'����������� �����������','����������� ����������� �������������� � �������� ��������','United Kingdom','GB','GBR','826','������','�������� ������\n'),(194,'����������� �����','����������� ����� �������','United States','US','USA','840','�������','�������� �������\n'),(195,'���������� �������','','Solomon Islands','SB','SLB','090','�������','���������\n'),(196,'������','����������� ����������','Somalia','SO','SOM','706','������','��������� ������\n'),(197,'�����','���������� �����','Sudan','SD','SDN','736','������','�������� ������\n'),(198,'�������','���������� �������','Suriname','SR','SUR','740','�������','����� �������\n'),(199,'������-�����','���������� ������-�����','Sierra Leone','SL','SLE','694','������','�������� ������\n'),(200,'�����������','���������� �����������','Tajikistan','TJ','TJK','762','����','����� ����� ����������� ����\n'),(201,'�������','����������� �������','Thailand','TH','THA','764','����','���-��������� ����\n'),(202,'��������, ������������ ����������','������������ ���������� ��������','Tanzania','TZ','TZA','834','������','��������� ������\n'),(203,'������� (�����)','','Taiwan','TW','TWN','158','����','��������� ����\n'),(204,'�����-�����','��������������� ���������� �����-�����','Timor-Leste','TL','TLS','626','����','���-��������� ����\n'),(205,'����','����������� ����������','Togo','TG','TGO','768','������','�������� ������\n'),(206,'�������','','Tokelau','TK','TKL','772','�������','���������\n'),(207,'�����','����������� �����','Tonga','TO','TON','776','�������','���������\n'),(208,'�������� � ������','���������� �������� � ������','Trinidad and Tobago','TT','TTO','780','�������','��������� �������\n'),(209,'������','','Tuvalu','TV','TUV','798','�������','���������\n'),(210,'�����','��������� ����������','Tunisia','TN','TUN','788','������','�������� ������\n'),(211,'���������','������������','Turkmenistan','TM','TKM','795','����','����� ����� ����������� ����\n'),(212,'������','�������� ����������','Turkey','TR','TUR','792','����','�������� ����\n'),(213,'������','���������� ������','Uganda','UG','UGA','800','������','��������� ������\n'),(214,'����������','���������� ����������','Uzbekistan','UZ','UZB','860','����','����� ����� ����������� ����\n'),(215,'�������','','Ukraine','UA','UKR','804','������','��������� ������\n'),(216,'������ � ������','','Wallis and Futuna','WF','WLF','876','�������','���������\n'),(217,'�������','��������� ���������� �������','Uruguay','UY','URY','858','�������','����� �������\n'),(218,'��������� �������','','Faroe Islands','FO','FRO','234','������','�������� ������\n'),(219,'�����','���������� �������� �����','Fiji','FJ','FJI','242','�������','���������\n'),(220,'���������','���������� ���������','Philippines','PH','PHL','608','����','���-��������� ����\n'),(221,'���������','����������� ����������','Finland','FI','FIN','246','������','�������� ������\n'),(222,'������������ ������� (�����������)','','Falkland Islands (Islas Malvinas)','FK','FLK','238','�������','����� �������\n'),(223,'�������','����������� ����������','France','FR','FRA','250','������','�������� ������\n'),(224,'����������� ������','','French Guiana','GF','GUF','254','�������','����� �������\n'),(225,'����������� ���������','','French Polynesia','PF','PYF','258','�������','���������\n'),(226,'����������� ����� ����������','','French Southern Lands','TF','ATF','260','','��������� �����\n'),(227,'��������','���������� ��������','Croatia','HR','HRV','191','������','����� ������\n'),(228,'����������-����������� ����������','','Central African Republic','CF','CAF','140','������','����������� ������\n'),(229,'���','���������� ���','Chad','TD','TCD','148','������','����������� ������\n'),(230,'����������','���������� ����������','Montenegro','ME','MNE','499','������','����� ������\n'),(231,'������� ����������','','Czech Republic','CZ','CZE','203','������','��������� ������\n'),(232,'����','���������� ����','Chile','CL','CHL','152','�������','����� �������\n'),(233,'���������','����������� ������������','Switzerland','CH','CHE','756','������','�������� ������\n'),(234,'������','����������� ������','Sweden','SE','SWE','752','������','�������� ������\n'),(235,'���������� � �� �����','','Svalbard and Jan Mayen','SJ','SJM','744','������','�������� ������\n'),(236,'���-�����','��������������� ���������������� ���������� ���-�����','Sri Lanka','LK','LKA','144','����','����� ����� ����������� ����\n'),(237,'�������','','Ecuador','EC','ECU','218','�������','����� �������\n'),(238,'�������������� ������','���������� �������������� ������','Equatorial Guinea','GQ','GNQ','226','������','����������� ������\n'),(239,'��������� �������','','Aland Islands','AX','ALA','248','������','�������� ������\n'),(240,'���-���������','���������� ���-���������','El Salvador','SV','SLV','222','�������','����������� �������\n'),(241,'�������','','Eritrea','ER','ERI','232','������','��������� ������\n'),(242,'�������','��������� ����������','Estonia','EE','EST','233','������','�������� ������\n'),(243,'�������','������������ ��������������� ���������� �������','Ethiopia','ET','ETH','231','������','��������� ������\n'),(244,'����� ������','����-����������� ����������','South Africa','ZA','ZAF','710','������','����� ����� ������\n'),(245,'����� �������� � ����� ���������� �������','','South Georgia and the South Sandwich Islands','GS','SGS','239','','����� �����\n'),(246,'����� �����','���������� �����','Korea, South','KR','KOR','410','����','��������� ����\n'),(247,'������','','Jamaica','JM','JAM','388','�������','��������� �������\n'),(248,'������','','Japan','JP','JPN','392','����','��������� ����\n')");

	}

}
?>