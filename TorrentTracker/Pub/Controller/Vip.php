namespace TorrentTracker\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Mvc\Controller;

class Vip extends Controller
{
    public function actionSubscribe()
    {
        $visitor = \XF::visitor();
        $duration = $this->filter('duration', 'uint');

        if ($visitor->vip_status) {
            return $this->error('✅ لديك اشتراك VIP بالفعل.');
        }

        $visitor->vip_status = 1;
        $visitor->vip_expiry = time() + ($duration * 30 * 24 * 60 * 60);

        $vipGroupId = \XF::options()->vipUserGroupId;
        if ($vipGroupId) {
            $visitor->addUserGroup($vipGroupId);
        }

        $visitor->save();

        return $this->message('🎉 تم تفعيل اشتراك VIP بنجاح!');
    }
}
