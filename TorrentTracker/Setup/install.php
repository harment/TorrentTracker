namespace TorrentTracker\Setup;

use XF\Db\Schema\Create;
use XF\Db\Schema\Alter;
use XF\Setup\AbstractSetup;

class Install extends AbstractSetup
{
    /**
     * تنفيذ عملية التثبيت الكاملة
     */
    public function install(array $stepParams = [])
    {
        $this->createTorrentTable();
        $this->createVipFields();
        $this->createBonusPointsTable();
        $this->createTrackerSettings();
        $this->createTorrentCategoriesTable();
        $this->createTorrentReportsTable();
    }

    /**
     * إنشاء جدول التورنتات
     */
    protected function createTorrentTable()
    {
        $this->schemaManager()->createTable('xf_torrents', function (Create $table) {
            $table->addColumn('torrent_id', 'int')->autoIncrement();
            $table->addColumn('title', 'varchar', 255);
            $table->addColumn('hash', 'char', 40)->unique();
            $table->addColumn('uploader_id', 'int');
            $table->addColumn('upload_date', 'int');
            $table->addColumn('category_id', 'int')->setDefault(0);
            $table->addColumn('seeders', 'int')->setDefault(0);
            $table->addColumn('leechers', 'int')->setDefault(0);
            $table->addColumn('snatches', 'int')->setDefault(0);
            $table->addColumn('freeleech', 'tinyint', 1)->setDefault(0);
            $table->addColumn('freeleech_until', 'int')->setDefault(0);
            $table->addPrimaryKey('torrent_id');
        });
    }

    /**
     * إنشاء الحقول الخاصة بـ VIP في جدول المستخدمين
     */
    protected function createVipFields()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table) {
            $table->addColumn('vip_status', 'tinyint', 1)->setDefault(0);
            $table->addColumn('vip_expiry', 'int')->setDefault(0);
            $table->addColumn('bonus_points', 'int')->setDefault(0);
        });

        // إنشاء مجموعة VIP تلقائيًا
        $this->db()->insert('xf_user_group', [
            'title' => 'VIP Members',
            'display_style_priority' => 100,
            'user_title' => 'VIP'
        ]);
    }

    /**
     * إنشاء جدول نقاط المكافآت Bonus Points
     */
    protected function createBonusPointsTable()
    {
        $this->schemaManager()->createTable('xf_bonus_points', function (Create $table) {
            $table->addColumn('user_id', 'int');
            $table->addColumn('points', 'int')->setDefault(0);
            $table->addPrimaryKey('user_id');
        });
    }

    /**
     * إنشاء جدول إعدادات التراكر
     */
    protected function createTrackerSettings()
    {
        $this->schemaManager()->createTable('xf_tracker_settings', function (Create $table) {
            $table->addColumn('tracker_url', 'varchar', 255);
            $table->addColumn('tracker_port', 'int');
            $table->addColumn('announce_url', 'varchar', 255);
        });
    }

    /**
     * إنشاء جدول تصنيفات التورنتات
     */
    protected function createTorrentCategoriesTable()
    {
        $this->schemaManager()->createTable('xf_torrent_categories', function (Create $table) {
            $table->addColumn('category_id', 'int')->autoIncrement();
            $table->addColumn('category_name', 'varchar', 255);
            $table->addPrimaryKey('category_id');
        });
    }

    /**
     * إنشاء جدول تقارير التورنتات
     */
    protected function createTorrentReportsTable()
    {
        $this->schemaManager()->createTable('xf_torrent_reports', function (Create $table) {
            $table->addColumn('report_id', 'int')->autoIncrement();
            $table->addColumn('torrent_id', 'int');
            $table->addColumn('user_id', 'int');
            $table->addColumn('reason', 'text');
            $table->addColumn('report_date', 'int');
            $table->addPrimaryKey('report_id');
        });
    }
}
