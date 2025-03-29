namespace TorrentTracker\Controller;

use XF\Mvc\Controller;
use XF\Http\Response;

class TrackerController extends Controller
{
    public function actionAnnounce()
    {
        $input = $this->filter([
            'info_hash' => 'str',
            'peer_id' => 'str',
            'port' => 'uint',
            'uploaded' => 'uint',
            'downloaded' => 'uint',
            'left' => 'uint',
            'event' => 'str'
        ]);

        $torrent = $this->em()->findOne('YourAddon:Torrent', ['info_hash' => hex2bin($input['info_hash'])]);
        if (!$torrent) {
            return $this->responseError('Torrent not found', 404);
        }

        $peer = $this->em()->findOne('YourAddon:Peer', [
            'peer_id' => $input['peer_id'],
            'torrent_id' => $torrent->torrent_id
        ]);

        if (!$peer) {
            $peer = $this->em()->create('YourAddon:Peer');
            $peer->peer_id = $input['peer_id'];
            $peer->torrent_id = $torrent->torrent_id;
            $peer->ip = $this->request->getIp();
            $peer->port = $input['port'];
        }

        $peer->uploaded = $input['uploaded'];
        $peer->downloaded = $input['downloaded'];
        $peer->left = $input['left'];
        $peer->seeder = $input['left'] == 0 ? 1 : 0;
        $peer->last_announce = time();
        $peer->save();

        return $this->responseView('YourAddon:TrackerAnnounce', 'tracker_announce', [
            'interval' => 1800,
            'peers' => $this->getPeers($torrent->torrent_id)
        ]);
    }

    protected function getPeers($torrentId)
    {
        $peers = $this->finder('YourAddon:Peer')
            ->where('torrent_id', $torrentId)
            ->fetch();

        $peerList = [];
        foreach ($peers as $peer) {
            $peerList[] = [
                'ip' => $peer->ip,
                'port' => $peer->port
            ];
        }

        return $peerList;
    }
}
