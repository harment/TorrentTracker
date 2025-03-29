namespace TorrentTracker\Cron;

class FreeLeechCleanup
{
    public static function runCleanup()
    {
        $db = \XF::db();
        $db->query("
            UPDATE xf_torrents 
            SET freeleech = 0 
            WHERE freeleech = 1 AND freeleech_until > 0 AND freeleech_until < ?
        ", [\XF::$time]);
    }
}
