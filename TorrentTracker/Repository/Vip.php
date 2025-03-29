namespace TorrentTracker\Repository;

use XF\Mvc\Entity\Repository;

class Vip extends Repository
{
    /**
     * جلب جميع أعضاء VIP
     */
    public function getVipUsers()
    {
        return $this->finder('XF:User')
            ->where('vip_status', 1)
            ->fetch();
    }

    /**
     * ترقية عضو إلى VIP
     */
    public function promoteToVip($userId, $durationDays)
    {
        $expireTime = \XF::$time + ($durationDays * 86400);

        $this->db()->query("
            UPDATE xf_user 
            SET vip_status = 1, vip_expiry = ? 
            WHERE user_id = ?
        ", [$expireTime, $userId]);
    }

    /**
     * إلغاء اشتراك VIP عند انتهاء المدة
     */
    public function revokeExpiredVip()
    {
        $this->db()->query("
            UPDATE xf_user 
            SET vip_status = 0 
            WHERE vip_status = 1 AND vip_expiry > 0 AND vip_expiry < ?
        ", [\XF::$time]);
    }

    /**
     * التحقق مما إذا كان المستخدم VIP
     */
    public function isUserVip($userId)
    {
        return (bool) $this->db()->fetchOne("
            SELECT COUNT(*) FROM xf_user 
            WHERE user_id = ? AND vip_status = 1 AND (vip_expiry = 0 OR vip_expiry > ?)
        ", [$userId, \XF::$time]);
    }
}
