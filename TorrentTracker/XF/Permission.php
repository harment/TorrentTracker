namespace TorrentTracker\XF;

use XF\Entity\User;
use XF\Permission\PermissionSet;
use XF\Db\AbstractAdapter;

class Permission
{
    protected $db;

    public function __construct(AbstractAdapter $db)
    {
        $this->db = $db;
    }

    /**
     * قائمة الصلاحيات الخاصة بالتورينت و الـ VIP
     */
    public static function getPermissions()
    {
        return [
            'torrent' => [
                'view_torrents'    => ['type' => 'bool', 'default' => true, 'title' => 'إمكانية عرض التورنتات'],
                'download_torrents'=> ['type' => 'bool', 'default' => false, 'title' => 'إمكانية تحميل التورنتات'],
                'upload_torrents'  => ['type' => 'bool', 'default' => false, 'title' => 'إمكانية رفع التورنتات'],
                'manage_torrents'  => ['type' => 'bool', 'default' => false, 'title' => 'إدارة التورنتات (تعديل، حذف)'],
                'freeleech_access' => ['type' => 'bool', 'default' => false, 'title' => 'الوصول إلى التورنتات المجانية (FreeLeech)'],
                'edit_own_torrent' => ['type' => 'bool', 'default' => false, 'title' => 'إمكانية تعديل التورنت الخاص بالمستخدم'],
                'delete_own_torrent' => ['type' => 'bool', 'default' => false, 'title' => 'إمكانية حذف التورنت الخاص بالمستخدم']
            ],
            'vip' => [
                'vip_access'   => ['type' => 'bool', 'default' => false, 'title' => 'الوصول إلى ميزات VIP'],
                'vip_bonus'    => ['type' => 'bool', 'default' => false, 'title' => 'إمكانية استخدام نقاط المكافآت'],
                'bypass_ratio' => ['type' => 'bool', 'default' => false, 'title' => 'تجاوز نسبة التحميل/الرفع المطلوبة'],
                'vip_freeleech' => ['type' => 'bool', 'default' => false, 'title' => 'إمكانية تحميل جميع التورنتات بدون حساب النسبة']
            ]
        ];
    }

    /**
     * التحقق مما إذا كان المستخدم لديه صلاحية معينة
     */
    public static function hasPermission(User $user, $category, $permission)
    {
        return $user->hasPermission($category, $permission);
    }

    /**
     * تحديث صلاحيات جميع المجموعات تلقائيًا عند تثبيت الإضافة
     */
    public function applyDefaultPermissions()
    {
        $permissions = [
            'Registered' => [
                'torrent' => ['view_torrents' => 1, 'download_torrents' => 0, 'upload_torrents' => 0],
                'vip' => ['vip_access' => 0]
            ],
            'VIP Members' => [
                'torrent' => ['view_torrents' => 1, 'download_torrents' => 1, 'upload_torrents' => 1, 'freeleech_access' => 1],
                'vip' => ['vip_access' => 1, 'vip_freeleech' => 1]
            ],
            'Moderators' => [
                'torrent' => ['view_torrents' => 1, 'download_torrents' => 1, 'upload_torrents' => 1, 'manage_torrents' => 1],
                'vip' => ['vip_access' => 1, 'vip_bonus' => 1]
            ]
        ];

        foreach ($permissions as $group => $categories) {
            $groupId = $this->getUserGroupIdByName($group);
            if ($groupId) {
                foreach ($categories as $category => $perms) {
                    foreach ($perms as $perm => $value) {
                        $this->setPermission($groupId, $category, $perm, $value);
                    }
                }
            }
        }
    }

    /**
     * البحث عن معرف مجموعة المستخدم بناءً على الاسم
     */
    protected function getUserGroupIdByName($groupName)
    {
        return $this->db->fetchOne("SELECT user_group_id FROM xf_user_group WHERE title = ?", $groupName);
    }

    /**
     * تعيين صلاحية لمجموعة مستخدم معينة
     */
    protected function setPermission($groupId, $category, $permission, $value)
    {
        $this->db->query("
            INSERT INTO xf_permission_entry 
            (user_group_id, user_id, permission_group_id, permission_id, permission_value)
            VALUES (?, 0, ?, ?, ?)
            ON DUPLICATE KEY UPDATE permission_value = ?
        ", [$groupId, $category, $permission, $value, $value]);
    }
}
