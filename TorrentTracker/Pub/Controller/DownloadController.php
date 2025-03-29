public function actionDownload(ParameterBag $params)
{
    $torrentId = $params->torrent_id;
    $torrent = $this->em()->find('YourAddon:Torrent', $torrentId);

    if (!$torrent) {
        return $this->error('⚠️ التورنت غير موجود.', 404);
    }

    if ($torrent->is_vip && !$this->visitor->vip_status) {
        return $this->error('🚫 هذا التورنت متاح فقط للأعضاء VIP.');
    }

    $filePath = \XF::app()->config('torrentsPath') . '/' . $torrent->file_name;
    if (!file_exists($filePath)) {
        return $this->error('❌ الملف غير متوفر.');
    }

    return $this->plugin('XF:File')->downloadFile($filePath, $torrent->file_name);
}
