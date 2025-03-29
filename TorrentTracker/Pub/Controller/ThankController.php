public function actionThank(ParameterBag $params)
{
    $torrentId = $params->torrent_id;
    $userId = \XF::visitor()->user_id;

    $db = \XF::db();
    $db->query("INSERT IGNORE INTO xf_torrent_thanks (user_id, torrent_id) VALUES (?, ?)", [$userId, $torrentId]);

    return $this->message('✅ تم تسجيل شكرك!');
}
