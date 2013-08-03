<?php
/**
 * QQ群成员同步
 */
class Group_syncAction extends CommonAction {
	/**
	 * 显示首页列表
	 */
	public function index() {
//        dump(dirname(__FILE__));
        import('ORG.Util.cls_qqapi');
//        $qq = new cls_qqapi();
//        var_dump(new cls_qqapi());

        $m = M('t_members');
        $data = $m->select();
        $this->assign('list', $data);
        $this->display();
    }
}
?>