namespace TorrentTracker\Setup;

use XF\Db\Schema\Drop;
use XF\Db\Schema\Alter;
use XF\Setup\AbstractSetup;

class Uninstall extends AbstractSetup
{
    /**
     * تنفيذ عملية إلغاء التثبيت الكاملة
     */
    public function uninstall(array $stepParams = [])
    {
        $this->dropTorrentTable();
        $this->removeVipFields();
        $this->dropBonusPointsTable();
        $this->dropTrackerSettingsTable();
        $this->dropTorrentCategoriesTable();
        $this->dropTorrentReportsTable();
    }

    /**
     * حذف جدول التورنتات
     */
    protected function dropTorrentTable()
    {
        $this->schemaManager()->dropTable('xf_torrents');
    }

    /**
     * إزالة حقول VIP من جدول المستخدمين
     */
    protected function removeVipFields()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table) {
            $table->dropColumns(['vip_status', 'vip_expiry', 'bonus_points']);
        });

        // حذف مجموعة VIP
        $this->db()->delete('xf_user_group', 'title = ?', 'VIP Members');
    }

    /**
     * حذف جدول نقاط المكافآت
     */
    protected function dropBonusPointsTable()
    {
        $this->schemaManager()->dropTable('xf_bonus_points');
    }

    /**
     * حذف جدول إعدادات التراكر
     */
    protected function dropTrackerSettingsTable()
    {
        $this->schemaManager()->dropTable('xf_tracker_settings');
    }

    /**
     * حذف جدول تصنيفات التورنت
     */
    protected function dropTorrentCategoriesTable()
    {
        $this->schemaManager()->dropTable('xf_torrent_categories');
    }

    /**
     * حذف جدول تقارير التورنتات
     */
    protected function dropTorrentReportsTable()
    {
        $this->schemaManager()->dropTable('xf_torrent_reports');
    }
}
