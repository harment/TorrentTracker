namespace TorrentTracker\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Torrent extends AbstractController
{
    public function actionIndex()
    {
        $torrents = $this->finder('TorrentTracker:Torrent')
            ->order('upload_date', 'DESC')
            ->fetch();

        return $this->view('TorrentTracker:Torrent\ListAdmin', 'torrent_list_admin', [
            'torrents' => $torrents
        ]);
    }

    public function actionEdit(ParameterBag $params)
    {
        $torrent = $this->assertTorrentExists($params->torrent_id);

        if ($this->isPost()) {
            $input = $this->filter([
                'title' => 'str',
                'description' => 'str',
                'freeleech' => 'bool'
                'freeleech_until' => 'datetime'
            ]);

            $input['freeleech_until'] = strtotime($input['freeleech_until']); // تحويل التاريخ إلى timestamp
            $torrent->bulkSet($input);
            $torrent->save();

            return $this->redirect($this->buildLink('torrents'));
        }

        return $this->view('TorrentTracker:Torrent\Edit', 'torrent_edit', [
            'torrent' => $torrent
        ]);
    }

    protected function assertTorrentExists($torrentId)
    {
        return $this->assertRecordExists('TorrentTracker:Torrent', $torrentId, 'التورنت غير موجود.');
    }
}
