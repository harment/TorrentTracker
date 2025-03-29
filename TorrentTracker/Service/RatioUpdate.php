namespace TorrentTracker\Service;

use XF\Service\AbstractService;
use XF\Entity\User;

class RatioUpdate extends AbstractService
{
    protected $user;

    public function __construct(\XF\App $app, User $user)
    {
        parent::__construct($app);
        $this->user = $user;
    }

    public function updateRatio($uploaded, $downloaded)
    {
        $db = $this->db();

        // تحديث جدول الريشيو
        $db->query("
            INSERT INTO xf_torrent_ratio (user_id, uploaded_bytes, downloaded_bytes)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                uploaded_bytes = uploaded_bytes + VALUES(uploaded_bytes),
                downloaded_bytes = downloaded_bytes + VALUES(downloaded_bytes)
        ", [$this->user->user_id, $uploaded, $downloaded]);

        // تحديث حالة VIP بناءً على نسبة الرفع
        $this->checkVipEligibility();
    }

    protected function checkVipEligibility()
    {
        $ratio = $this->getUserRatio();
        $requiredRatio = 2.0; // الحد الأدنى للـ VIP

        if ($ratio >= $requiredRatio && !$this->user->vip_status) {
            $this->user->vip_status = 1;
            $this->user->vip_expiry = time() + (30 * 86400); // شهر VIP
            $this->user->addUserGroup(\XF::options()->vipUserGroupId);
        } elseif ($ratio < $requiredRatio && $this->user->vip_status) {
            $this->user->vip_status = 0;
            $this->user->vip_expiry = null;
            $this->user->removeUserGroup(\XF::options()->vipUserGroupId);
        }

        $this->user->save();
    }

    protected function getUserRatio()
    {
        $db = $this->db();
        $data = $db->fetchRow("SELECT uploaded_bytes, downloaded_bytes FROM xf_torrent_ratio WHERE user_id = ?", $this->user->user_id);

        if (!$data || $data['downloaded_bytes'] == 0) {
            return 0;
        }

        return $data['uploaded_bytes'] / max(1, $data['downloaded_bytes']);
    }
}
