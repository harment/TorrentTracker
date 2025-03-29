<?php

namespace TorrentTracker;

use XF\Db\Schema\Create;
use XF\Db\Schema\Alter;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\AddOn\StepRunnerUninstallTrait;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    /**
     * تثبيت الإضافة
     */
    public function install(array $stepParams = [])
    {
        $this->createTorrentTable();
        $this->createVipTable();
        $this->alterUserTable();
        $this->insertDefaultOptions();
        $this->insertDefaultPermissions();
        $this->insertNavigationEntries();
        $this->createVipUserGroup();
    }

    /**
     * حذف الإضافة
     */
    public function uninstall(array $stepParams = [])
    {
        $this->dropTableIfExists('xf_torrent');
        $this->dropTableIfExists('xf_vip');
        $this->revertUserTable();
        $this->deleteOptions();
        $this->deletePermissions();
        $this->deleteNavigationEntries();
    }

    // ✅ إنشاء جدول التورنت
    protected function createTorrentTable()
    {
        $this->schemaManager()->createTable('xf_torrent', function (Create $table) {
            $table->addColumn('torrent_id', 'int')->autoIncrement();
            $table->addColumn('user_id', 'int');
            $table->addColumn('title', 'varchar', 255);
            $table->addColumn('file_hash', 'varchar', 40)->unique();
            $table->addColumn('size', 'bigint');
            $table->addColumn('seeders', 'int')->setDefault(0);
            $table->addColumn('leechers', 'int')->setDefault(0);
            $table->addColumn('downloads', 'int')->setDefault(0);
            $table->addColumn('is_freeleech', 'tinyint', 1)->setDefault(0);
            $table->addPrimaryKey('torrent_id');
        });
    }

    // ✅ إنشاء جدول VIP
    protected function createVipTable()
    {
        $this->schemaManager()->createTable('xf_vip', function (Create $table) {
            $table->addColumn('vip_id', 'int')->autoIncrement();
            $table->addColumn('user_id', 'int');
            $table->addColumn('start_date', 'int');
            $table->addColumn('end_date', 'int');
            $table->addColumn('status', 'enum', ['active', 'expired'])->setDefault('active');
            $table->addPrimaryKey('vip_id');
        });
    }

    // ✅ تعديل جدول المستخدم لإضافة بيانات VIP
    protected function alterUserTable()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table) {
            $table->addColumn('vip_status', 'tinyint', 1)->setDefault(0);
            $table->addColumn('vip_expiry', 'int')->nullable();
        });
    }

    // ✅ إعادة جدول المستخدم إلى حالته الأصلية عند الحذف
    protected function revertUserTable()
    {
        $this->schemaManager()->alterTable('xf_user', function (Alter $table) {
            $table->dropColumns(['vip_status', 'vip_expiry']);
        });
    }

    // ✅ إدخال الإعدادات الافتراضية
    protected function insertDefaultOptions()
    {
        $options = [
            ['vip_price', '10', 'float'],
            ['vip_duration', '30', 'integer'],
            ['freeleech_enabled', '0', 'boolean']
        ];

        foreach ($options as [$optionId, $defaultValue, $type]) {
            $this->db()->insert('xf_option', [
                'option_id' => $optionId,
                'option_value' => $defaultValue,
                'data_type' => $type
            ]);
        }
    }

    // ✅ حذف الإعدادات عند إلغاء التثبيت
    protected function deleteOptions()
    {
        $this->db()->delete('xf_option', "option_id IN ('vip_price', 'vip_duration', 'freeleech_enabled')");
    }

    // ✅ إضافة الصلاحيات الافتراضية
    protected function insertDefaultPermissions()
    {
        $permissions = [
            ['torrent', 'view_torrents', 1],
            ['torrent', 'download_torrents', 0],
            ['torrent', 'upload_torrents', 0],
            ['torrent', 'freeleech_access', 0],
            ['vip', 'vip_access', 0],
            ['vip', 'bypass_ratio', 0]
        ];

        foreach ($permissions as [$group, $permissionId, $default]) {
            $this->db()->insert('xf_permission', [
                'permission_group_id' => $group,
                'permission_id' => $permissionId,
                'default_value' => $default
            ]);
        }
    }

    // ✅ حذف الصلاحيات عند إلغاء التثبيت
    protected function deletePermissions()
    {
        $this->db()->delete('xf_permission', "permission_group_id IN ('torrent', 'vip')");
    }

    // ✅ إدخال روابط لوحة التحكم
    protected function insertNavigationEntries()
    {
        $navigation = [
            ['torrentTracker', '', 'إدارة التورنت', 'admin.php?torrent-tracker/', 'fa-magnet', 10],
            ['torrentManager', 'torrentTracker', 'إدارة التورنتات', 'admin.php?torrent-tracker/manage', '', 20],
            ['vipManager', 'torrentTracker', 'إدارة VIP', 'admin.php?torrent-tracker/vip', '', 30]
        ];

        foreach ($navigation as [$id, $parent, $title, $link, $icon, $order]) {
            $this->db()->insert('xf_admin_navigation', [
                'navigation_id' => $id,
                'parent_navigation_id' => $parent,
                'title' => $title,
                'link' => $link,
                'icon' => $icon,
                'display_order' => $order
            ]);
        }
    }

    // ✅ حذف الروابط عند إلغاء التثبيت
    protected function deleteNavigationEntries()
    {
        $this->db()->delete('xf_admin_navigation', "navigation_id IN ('torrentTracker', 'torrentManager', 'vipManager')");
    }

    // ✅ إنشاء مجموعة VIP تلقائيًا
    protected function createVipUserGroup()
    {
        $this->db()->insert('xf_user_group', [
            'title' => 'الأعضاء الذهبيون (VIP)',
            'display_style_priority' => 100,
            'user_title' => 'VIP'
        ]);
    }

    // ✅ حذف مجموعة VIP عند إلغاء التثبيت
    protected function deleteVipUserGroup()
    {
        $this->db()->delete('xf_user_group', "title = 'الأعضاء الذهبيون (VIP)'");
    }
}
