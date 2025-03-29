namespace TorrentTracker\Cron;

use XF;

class ExpireVip
{
    public static function run()
    {
        $db = XF::db();
        $vipGroupId = XF::options()->vipUserGroupId;

        $expiredUsers = $db->fetchAll("SELECT user_id FROM xf_user WHERE vip_expiry < UNIX_TIMESTAMP() AND vip_status = 1");

        foreach ($expiredUsers as $user) {
            $userEntity = XF::app()->em()->find('XF:User', $user['user_id']);
            if ($userEntity) {
                $userEntity->vip_status = 0;
                $userEntity->vip_expiry = null;

                if ($vipGroupId) {
                    $userEntity->removeUserGroup($vipGroupId);
                }

                $userEntity->save();
            }
        }
    }
}
