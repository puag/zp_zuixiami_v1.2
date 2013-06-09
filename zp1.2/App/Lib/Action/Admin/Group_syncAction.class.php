<?php
/**
 * QQ群成员同步
 */
class Group_syncAction extends CommonAction {
	/**
	 * 显示首页列表
	 */
	public function index() {
        import('@.Admin.cls_netdo');
        $m = M('t_members');
        $data = $m->select();
        $this->assign('list', $data);
        $this->display();
    }
}
?>