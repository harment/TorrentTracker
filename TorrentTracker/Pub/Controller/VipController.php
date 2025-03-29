public function actionSubscribe()
{
    if ($this->isPost()) {
        $user = \XF::visitor();
        $duration = $this->filter('duration', 'uint');

        if (!$user->vip_status) {
            $user->vip_status = 1;
            $user->vip_expiry = time() + ($duration * 30 * 24 * 60 * 60);
            $user->save();

            return $this->message('🎉 تم تفعيل اشتراك VIP بنجاح!');
        } else {
            return $this->error('✅ لديك اشتراك VIP بالفعل.');
        }
    }

    return $this->view('YourAddon:VipSubscribe', 'vip_subscribe');
}
