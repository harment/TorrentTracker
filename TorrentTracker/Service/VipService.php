namespace TorrentTracker\Service;

use XF\Service\AbstractService;
use XF\Mvc\Entity\Entity;

class VipService extends AbstractService
{
    protected $user;

    public function __construct(\XF\App $app, Entity $user = null)
    {
        parent::__construct($app);
        $this->user = $user;
    }

    /**
     * ترقية العضو إلى VIP لمدة محددة
     */
    public function upgradeToVip($userId, $durationDays)
    {
        $user = $this->em()->find('XF:User', $userId);
        if (!$user) {
            throw new \XF\PrintableException(\XF::phrase('user_not_found'));
        }

        $expireTime = \XF::$time + ($durationDays * 86400);

        $user->vip_status = 1;
        $user->vip_expiry = $expireTime;
        $user->user_group_id = 5; // مثال: تحويله إلى مجموعة VIP
        $user->save();
    }

    /**
     * التحقق مما إذا كان المستخدم لديه اشتراك VIP نشط
     */
    public function isUserVip($userId)
    {
        $user = $this->em()->find('XF:User', $userId);
        return $user && $user->vip_status == 1 && ($user->vip_expiry == 0 || $user->vip_expiry > \XF::$time);
    }

    /**
     * إلغاء اشتراك VIP عند انتهاء المدة
     */
    public function removeExpiredVipUsers()
    {
        $expiredUsers = $this->finder('XF:User')
            ->where('vip_status', 1)
            ->where('vip_expiry', '>', 0)
            ->where('vip_expiry', '<', \XF::$time)
            ->fetch();

        foreach ($expiredUsers as $user) {
            $user->vip_status = 0;
            $user->vip_expiry = 0;
            $user->user_group_id = 2; // إعادته إلى المجموعة العادية
            $user->save();
        }
    }
}
