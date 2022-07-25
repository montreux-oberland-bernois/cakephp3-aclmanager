<?php

/**
 * CakePHP 3.x - Acl Manager
 *
 * PHP version 5
 *
 * Class AclHelper
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @category CakePHP3
 *
 * @package  AclManager\View\Helper
 *
 * @author Ivan Amat <dev@ivanamat.es>
 * @copyright Copyright 2016, Iván Amat
 * @license MIT http://opensource.org/licenses/MIT
 * @link https://github.com/ivanamat/cakephp3-aclmanager
 */

namespace AclManager\View\Helper;

use Acl\Controller\Component\AclComponent;
use Cake\Controller\Component\AuthComponent;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use Cake\View\View;

class AclManagerHelper extends Helper
{
    /**
     * Helpers used.
     *
     * @var array
     */
    public $helpers = ['Html'];

    /**
     * Acl Instance.
     *
     * @var object
     */
    public $Acl;
    public $Auth;

    /**
     * AclManagerHelper constructor
     *
     * @param \Cake\View\View $View
     * @param array $config
     */
    public function __construct(View $View, $config = [])
    {
        parent::__construct($View, $config);

        $collection = new ComponentRegistry();
        $this->Acl = new AclComponent($collection, Configure::read('Acl'));
    }

    /**
     *  Check if the ARO has access to the aco
     *  Set as private as knowing the ARO is almost useless
     *
     * @param Model|array|string $aro The Aro of the object you want to check
     * @param Model|array|string $aco The path of the Aco like App/Blog/add
     * @param string $action CRUD Actions to check
     * @return bool
     */
    private function _check($aro, $aco, $action = '*')
    {
        if (empty($aro) || empty($aco)) {
            return false;
        }

        return $this->Acl->check($aro, $aco, $action);
    }

    /**
     *  Check if the User ID has access to the aco
     *
     * @param string $aco The path of the Aco like App/Blog/add
     * @param int|null $uid The ID of the User you want to check
     * @param string $action CRUD Actions to check
     * @return bool
     */
    public function checkUser($aco, $uid = null, $action = '*')
    {
        $uid = $this->getView()->getRequest()->getSession()->read('Auth.User.id');

        if (empty($uid)) {
            return false;
        }

        return $this->_check(['model' => 'Users', 'foreign_key' => $uid], $aco, $action);
    }

    /**
     *  Check if the Group ID has access to the aco
     *
     * @param string $aco The path of the Aco like App/Blog/add
     * @param int|null $gid The ID of the User you want to check
     * @param string $action CRUD Actions to check
     * @return bool
     */
    public function checkGroup($aco, $gid = null, $action = '*')
    {
        if (empty($gid)) {
            return false;
        }

        $gid = $this->getView()->getRequest()->getSession()->read('Auth.User.group_id');

        return $this->_check(['model' => 'Groups', 'foreign_key' => $gid], $aco, $action);
    }

    /**
     * @param int $aro
     * @param int $id
     * @return \Cake\Datasource\EntityInterface|mixed
     */
    public function getName($aro, $id)
    {
        return $this->__getName($aro, $id);
    }

    /**
     * Return value from permissions input
     *
     * @param string|null $value
     *
     * @return bool
     */
    public function value(string $value = null)
    {
        if ($value == null) {
            return false;
        }

        $o = explode('.', $value);
        $data = $this->getView()->getRequest()->getData();

        return $data[$o[0]][$o[1]][$o[2]];
    }

    /**
     * Find ARO/ACO id
     *
     * @param string $aro
     * @param int $id
     *
     * @return \Cake\Datasource\EntityInterface
     */
    protected function __getName($aro, $id)
    {
        $model = TableRegistry::getTableLocator()->get($aro);

        return $model->get($id, ['recursive' => -1]);
    }
}
