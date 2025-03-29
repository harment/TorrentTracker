namespace TorrentTracker\Service;

use XF\Service\AbstractService;
use XF\Entity\User;
use XF\Mvc\Entity\Entity;

class TorrentUploader extends AbstractService
{
    protected $user;
    protected $torrentData;

    public function __construct(\XF\App $app, User $user, array $torrentData)
    {
        parent::__construct($app);
        $this->user = $user;
        $this->torrentData = $torrentData;
    }

    public function upload()
    {
        $this->validateData();
        $torrent = $this->insertTorrent();

        return $torrent;
    }

    protected function validateData()
    {
        if (empty($this->torrentData['title']) || empty($this->torrentData['file'])) {
            throw new \InvalidArgumentException("العنوان أو ملف التورنت مفقود!");
        }

        if (!$this->user->user_id) {
            throw new \LogicException("يجب أن تكون مسجلًا لرفع التورنت!");
        }
    }

    protected function insertTorrent()
    {
        /** @var Entity $torrent */
        $torrent = $this->em()->create('XF:Torrent');
        $torrent->title = $this->torrentData['title'];
        $torrent->description = $this->torrentData['description'] ?? '';
        $torrent->size = $this->torrentData['size'] ?? 0;
        $torrent->seeders = 0;
        $torrent->leechers = 0;
        $torrent->uploader_user_id = $this->user->user_id;
        $torrent->upload_date = time();

        $torrent->save();

        return $torrent;
    }
}
