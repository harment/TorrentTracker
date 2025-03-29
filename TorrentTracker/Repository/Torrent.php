namespace TorrentTracker\Repository;

use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;

class Torrent extends Repository
{
    /**
     * إحضار قائمة التورنتات مع دعم التصفية والفرز
     */
    public function findTorrentsForList()
    {
        return $this->finder('TorrentTracker:Torrent')
            ->order('upload_date', 'DESC');
    }

    /**
     * إحضار التورنت حسب الـ ID
     */
    public function getTorrentById($torrentId)
    {
        return $this->finder('TorrentTracker:Torrent')
            ->where('torrent_id', $torrentId)
            ->fetchOne();
    }

    /**
     * تحديث إحصائيات التورنت (Seeder, Leecher, Snatches)
     */
    public function updateTorrentStats($torrentId, $seeders, $leechers, $snatches)
    {
        $this->db()->query("
            UPDATE xf_torrents 
            SET seeders = ?, leechers = ?, snatches = ? 
            WHERE torrent_id = ?
        ", [$seeders, $leechers, $snatches, $torrentId]);
    }

    /**
     * تعطيل التورنت المجاني (FreeLeech) بعد انتهاء المدة
     */
    public function disableExpiredFreeLeech()
    {
        $this->db()->query("
            UPDATE xf_torrents 
            SET freeleech = 0 
            WHERE freeleech = 1 AND freeleech_until > 0 AND freeleech_until < ?
        ", [\XF::$time]);
    }

    /**
     * حذف تورنت معين
     */
    public function deleteTorrent($torrentId)
    {
        $torrent = $this->getTorrentById($torrentId);
        if ($torrent) {
            $torrent->delete();
            return true;
        }
        return false;
    }
}
