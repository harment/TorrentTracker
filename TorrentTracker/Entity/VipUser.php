namespace TorrentTracker\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

class VipUser extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_user';
        $structure->shortName = 'TorrentTracker:VipUser';
        $structure->primaryKey = 'user_id';
        $structure->columns = [
            'user_id' => ['type' => self::UINT, 'required' => true],
            'vip_status' => ['type' => self::BOOL, 'default' => 0],
            'vip_expiry' => ['type' => self::UINT, 'nullable' => true, 'default' => null]
        ];

        $structure->getters = [
            'isVip' => true
        ];

        return $structure;
    }

    public function isVip()
    {
        return ($this->vip_status && ($this->vip_expiry === null || $this->vip_expiry > time()));
    }

    public function activateVip($days = 30)
    {
        $this->vip_status = 1;
        $this->vip_expiry = time() + ($days * 86400);
        $this->save();

        // إضافة العضو إلى مجموعة VIP
        $this->addUserGroup(\XF::options()->vipUserGroupId);
    }

    public function deactivateVip()
    {
        $this->vip_status = 0;
        $this->vip_expiry = null;
        $this->save();

        // إزالة العضو من مجموعة VIP
        $this->removeUserGroup(\XF::options()->vipUserGroupId);
    }
}
