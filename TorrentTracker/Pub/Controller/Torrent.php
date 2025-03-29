namespace TorrentTracker\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class Torrent extends AbstractController
{
    public function actionIndex()
    {
        $torrents = $this->finder('TorrentTracker:Torrent')
            ->order('upload_date', 'DESC')
            ->fetch();

        return $this->view('TorrentTracker:Torrent\List', 'torrent_list', [
            'torrents' => $torrents
        ]);
    }

    public function actionView(ParameterBag $params)
    {
        $torrent = $this->assertTorrentExists($params->torrent_id);

        return $this->view('TorrentTracker:Torrent\View', 'torrent_view', [
            'torrent' => $torrent
        ]);
    }

    public function actionDownload(ParameterBag $params)
    {
        $torrent = $this->assertTorrentExists($params->torrent_id);

        // التحقق من صلاحية المستخدم لتنزيل التورنت
        if (!$this->canDownloadTorrent($torrent)) {
            return $this->noPermission();
        }

        $filePath = \XF::getRootDirectory() . '/data/torrents/' . $torrent->torrent_id . '.torrent';

        if (!file_exists($filePath)) {
            return $this->error('ملف التورنت غير موجود!');
        }

// ⚡ إذا كان التورنت FreeLeech، فلا يُحتسب في معدل التحميل
    if (!$torrent->isFreeLeech()) {
        $this->updateUserDownloadStats(\XF::visitor(), $torrent->size);
    }

        return $this->plugin('XF:File')->getFileResponse($filePath, $torrent->title . '.torrent');
    }

    protected function assertTorrentExists($torrentId)
    {
        return $this->assertRecordExists('TorrentTracker:Torrent', $torrentId, 'التورنت غير موجود.');
    }

    protected function canDownloadTorrent($torrent)
    {
        $visitor = \XF::visitor();

        // السماح لأعضاء VIP أو للأعضاء الذين لديهم ريشيو معين
        if ($visitor->vip_status || $visitor->uploaded_bytes >= ($visitor->downloaded_bytes * 0.5)) {
            return true;
        }

        return false;
    }
}
