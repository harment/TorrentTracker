namespace TorrentTracker;

use XF\Container;
use XF\Extend\LoadClass;

class LoadClass
{
    public static function modifyUserPermissions(LoadClass $loadClass, array &$extend)
    {
        if ($loadClass->extendClass === 'XF\Entity\User') {
            $extend[] = 'TorrentTracker:VipUser';
        }
    }

    public static function checkVipPermissions(Container $container, $userId)
    {
        $user = \XF::em()->find('XF:User', $userId);

        if (!$user) {
            return false;
        }

        // السماح بمزايا VIP للمستخدمين الذين لديهم حالة VIP نشطة
        return ($user->vip_status && ($user->vip_expiry === null || $user->vip_expiry > time()));
    }
}
