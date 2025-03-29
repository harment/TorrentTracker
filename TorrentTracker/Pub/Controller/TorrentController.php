namespace TorrentTracker\Controller;

use XF\Mvc\Controller;

class TorrentController extends Controller
{
    public function actionIndex()
    {
        $torrentRepo = $this->repository('YourAddon:Torrent');
        $torrents = $torrentRepo->findTorrents()->fetch();
        
        return $this->view('YourAddon:Torrents', 'torrent_list', ['torrents' => $torrents]);
    }

    public function actionAdd()
    {
        if ($this->isPost())
        {
            $input = $this->filter([
                'file_name' => 'str',
                'file_size' => 'uint',
                'info_hash' => 'str'
            ]);

            $torrent = $this->em()->create('YourAddon:Torrent');
            $torrent->user_id = \XF::visitor()->user_id;
            $torrent->file_name = $input['file_name'];
            $torrent->file_size = $input['file_size'];
            $torrent->info_hash = hex2bin($input['info_hash']);
            $torrent->save();

            return $this->redirect($this->buildLink('torrents'));
        }

        return $this->view('YourAddon:AddTorrent', 'torrent_add');
    }
}

public function actionView(ParameterBag $params)
{
    $torrentId = $params->torrent_id;
    $torrent = $this->em()->find('YourAddon:Torrent', $torrentId);
    
    if (!$torrent) {
        return $this->error('التورنت غير موجود', 404);
    }

    $peers = $this->finder('YourAddon:Peer')->where('torrent_id', $torrentId)->fetch();
    $stats = $this->em()->find('YourAddon:Stats', $torrentId);

    return $this->view('YourAddon:TorrentView', 'torrent_view', [
        'torrent' => $torrent,
        'peers' => $peers,
        'stats' => $stats
    ]);
}
