namespace TorrentTracker\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Peer extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_torrent_peers';
        $structure->shortName = 'YourAddon:Peer';
        $structure->primaryKey = ['peer_id', 'torrent_id'];
        $structure->columns = [
            'peer_id' => ['type' => self::STR, 'required' => true, 'maxLength' => 40],
            'torrent_id' => ['type' => self::UINT, 'required' => true],
            'user_id' => ['type' => self::UINT, 'nullable' => true],
            'ip' => ['type' => self::STR, 'required' => true, 'maxLength' => 45],
            'port' => ['type' => self::UINT, 'required' => true],
            'uploaded' => ['type' => self::UINT, 'default' => 0],
            'downloaded' => ['type' => self::UINT, 'default' => 0],
            'left' => ['type' => self::UINT, 'default' => 0],
            'seeder' => ['type' => self::UINT, 'default' => 0],
            'last_announce' => ['type' => self::UINT, 'required' => true]
        ];
        return $structure;
    }
}
