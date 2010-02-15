<?

class SystemNotification extends Object {

	const SN_TYPE_CORE_UPDATE = 10;
	const SN_TYPE_CORE_MESSAGE_HELP = 11;
	const SN_TYPE_CORE_MESSAGE_NEWS = 12;
	const SN_TYPE_CORE_MESSAGE_OTHER = 19;
	const SN_TYPE_ADDON_UPDATE = 20;
	const SN_TYPE_ADDON_MESSAGE = 22;
	
	public function getSystemNotificationURL() {return $this->snURL;}
	public function getSystemNotificationTitle() {return $this->snTitle;}
	public function getSystemNotificationDescription() {return $this->snDescription;}
	public function getSystemNotificationBody() {return $this->snBody;}
	public function getSystemNotificationDateTime() {return $this->snDateTime;}
	public function isSystemNotificationNew() {return $this->snIsNew;}
	public function isSystemNotificationArchived() {return $this->snIsArchived;}
	
	public static function add($typeID, $title, $description, $body, $url) {
		$db = Loader::db();
		$date = Loader::helper('date')->getLocalDateTime();
		$db->Execute('insert into SystemNotifications (snTypeID, snTitle, snDescription, snBody, snURL, snDateTime, snIsNew) values (?, ?, ?, ?, ?, ?, ?)', array(
			$typeID, $title, $description, $body, $url, $date, 1
		));	
	}


	public static function addFromFeed($post, $type) {
		$db = Loader::db();
		$cnt = $db->GetOne('select count(snID) from SystemNotifications where snURL = ?', array($post->get_permalink()));
		if ($cnt == 0) {
			// otherwise we already have this
			$db->Execute('insert into SystemNotifications (snTypeID, snTitle, snDescription, snBody, snURL, snDateTime, snIsNew) values (?, ?, ?, ?, ?, ?, ?)', array(
				$type, $post->get_title(), $post->get_description(), $post->get_content(), $post->get_permalink(), $post->get_date('Y-m-d H:i:s'), 1
			));
		}	
	}
	
	public static function getByID($snID) {
		$db = Loader::db();
		$row = $db->GetRow('select * from SystemNotifications where snID = ?', array($snID));
		if (is_array($row) && $row['snID']) {
			$sn = new SystemNotification();
			$sn->setPropertiesFromArray($row);
			return $sn;
		}
	}

}

class SystemNotificationList extends DatabaseItemList {
	
	public function filterByType($type) {
		$db = Loader::db();
		$this->filter('sn.snTypeID', $type);
	}
	
	function __construct() {
		$this->setQuery("select sn.snID from SystemNotifications sn");
		$this->sortBy('snDateTime', 'desc');
	}

	public function get($itemsToGet = 0, $offset = 0) {
		$r = parent::get($itemsToGet, $offset);
		$posts = array();
		foreach($r as $snID) {
			$sn = SystemNotification::getByID($snID);
			if (is_object($sn)) {
				$posts[] = $sn;
			}
		}
		return $posts;
	}
	
}