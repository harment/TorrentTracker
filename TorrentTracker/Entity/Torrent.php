namespace TorrentTracker\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class Torrent extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_torrents';
        $structure->shortName = 'TorrentTracker:Torrent';
        $structure->primaryKey = 'torrent_id';
        $structure->columns = [
            'torrent_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'title' => ['type' => self::STR, 'maxLength' => 255, 'required' => true],
            'description' => ['type' => self::STR, 'nullable' => true, 'default' => ''],
            'size' => ['type' => self::UINT, 'default' => 0],
            'seeders' => ['type' => self::UINT, 'default' => 0],
            'leechers' => ['type' => self::UINT, 'default' => 0],
            'uploader_user_id' => ['type' => self::UINT, 'required' => true],
            'upload_date' => ['type' => self::UINT, 'default' => \XF::$time]
             'freeleech' => ['type' => self::BOOL, 'default' => 0] // 🚀 جديد: دعم التورنت المجاني
             'freeleech_until' => ['type' => self::UINT, 'default' => 0] // ⏳ وقت انتهاء FreeLeech
        ];

        return $structure;
    }

    public function incrementSeeders()
    {
        $this->seeders++;
        $this->save();
    }

    public function decrementSeeders()
    {
        if ($this->seeders > 0) {
            $this->seeders--;
            $this->save();
        }
    }

    public function incrementLeechers()
    {
        $this->leechers++;
        $this->save();
    }

    public function decrementLeechers()
    {
        if ($this->leechers > 0) {
            $this->leechers--;
            $this->save();
        }
    }

public function isFreeLeech()
    {
        if ($this->freeleech_until > 0 && $this->freeleech_until < \XF::$time) {
            return false; // انتهت الفترة
        }
        return $this->freeleech;
    }
}
}
