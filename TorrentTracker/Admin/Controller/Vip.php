namespace TorrentTracker\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;
use XF\Mvc\FormAction;

class Vip extends AbstractController
{
    public function actionIndex()
    {
        $users = $this->finder('XF:User')
            ->where('vip_status', 1)
            ->fetch();

        return $this->view('TorrentTracker:Vip\AdminList', 'torrenttracker_vip_list', [
            'users' => $users
        ]);
    }

    public function actionEdit(ParameterBag $params)
    {
        $user = $this->assertUserExists($params->user_id);

        $viewParams = [
            'user' => $user
        ];
        return $this->view('TorrentTracker:Vip\AdminEdit', 'torrenttracker_vip_edit', $viewParams);
    }

    public function actionSave(ParameterBag $params)
    {
        $this->assertPostOnly();
        $user = $this->assertUserExists($params->user_id);

        $input = $this->filter([
            'vip_status' => 'bool',
            'vip_expiry' => 'uint'
        ]);

        if ($input['vip_status'] && !$user->vip_status) {
            // تعيين VIP
            $user->vip_status = 1;
            $user->vip_expiry = time() + ($input['vip_expiry'] * 86400);
            $user->addUserGroup(\XF::options()->vipUserGroupId);
        } elseif (!$input['vip_status']) {
            // إزالة VIP
            $user->vip_status = 0;
            $user->vip_expiry = null;
            $user->removeUserGroup(\XF::options()->vipUserGroupId);
        }

        $user->save();

        return $this->redirect($this->buildLink('vip'));
    }
}
