namespace TorrentTracker\Service;

use XF\Service\AbstractService;
use XF\Mvc\Entity\Entity;

class TorrentService extends AbstractService
{
    protected $torrent;

    public function __construct(\XF\App $app, Entity $torrent = null)
    {
        parent::__construct($app);
        $this->torrent = $torrent;
    }

    /**
     * رفع ملف التورنت وحفظه في قاعدة البيانات
     */
    public function uploadTorrent($file, $uploaderId)
    {
        if (!$file || !$file->isValid()) {
            throw new \XF\PrintableException(\XF::phrase('torrent_invalid_file'));
        }

        $fileName = $file->getFilename();
        $torrentData = file_get_contents($file->getTempFile());
        $torrentHash = sha1($torrentData);

        // التحقق من عدم وجود نفس التورنت مسبقًا
        $existingTorrent = $this->finder('TorrentTracker:Torrent')->where('hash', $torrentHash)->fetchOne();
        if ($existingTorrent) {
            throw new \XF\PrintableException(\XF::phrase('torrent_already_exists'));
        }

        $torrent = $this->em()->create('TorrentTracker:Torrent');
        $torrent->title = $fileName;
        $torrent->hash = $torrentHash;
        $torrent->uploader_id = $uploaderId;
        $torrent->upload_date = \XF::$time;
        $torrent->save();

        return $torrent;
    }

    /**
     * تحديث إحصائيات التورنت
     */
    public function updateTorrentStats($torrentId, $seeders, $leechers, $snatches)
    {
        $torrent = $this->em()->find('TorrentTracker:Torrent', $torrentId);
        if ($torrent) {
            $torrent->seeders = $seeders;
            $torrent->leechers = $leechers;
            $torrent->snatches = $snatches;
            $torrent->save();
        }
    }

    /**
     * ضبط التورنت ليكون FreeLeech لفترة محددة
     */
    public function setFreeLeech($torrentId, $durationHours)
    {
        $torrent = $this->em()->find('TorrentTracker:Torrent', $torrentId);
        if ($torrent) {
            $torrent->freeleech = 1;
            $torrent->freeleech_until = \XF::$time + ($durationHours * 3600);
            $torrent->save();
        }
    }
}
