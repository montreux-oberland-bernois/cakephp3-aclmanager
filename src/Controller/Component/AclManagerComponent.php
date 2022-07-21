<?php

/**
 * Projet : AclManager
 * Auteur : Raphaël Gabriel
 * Date: 16.03.2016
 */
namespace AclManager\Controller\Component;

use Acl\Controller\Component\AclComponent;
use Acl\Model\Entity\Aro;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\App;
use Cake\Core\Plugin;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\ORM\TableRegistry;
use ReflectionClass;
use ReflectionMethod;

class AclManagerComponent extends Component
{
    /**
     * Base for acos
     *
     * @var string
     */
    protected $_base = 'controllers';

    /**
     * Basic Api actions
     *
     * @var array
     */
    protected $config = [];

    /**
     * Initialize all properties we need
     *
     * @param array $config initialize cake method need $config
     *
     * @return null
     */
    public function initialize(array $config): void
    {
        $registry = new ComponentRegistry();
        $this->Acl = new AclComponent($registry, Configure::read('Acl'));
        $this->Aco = $this->Acl->Aco;
        $this->Aro = $this->Acl->Aro;
        $this->config = $config;
    }

    /**
     * Acos Builder, find all public actions from controllers and stored them
     * with Acl tree behavior to the acos table.
     * Alias first letter of Controller will
     * be capitalized and actions will be lowercase
     *
     * @return bool return true if acos saved
     */
    public function acosBuilder()
    {
        $srcControllers = $this->__getResources();
        $pluginsControllers = $this->__getPluginsResources();
        //$this->checkNodeOrSave("", "", null);
        $this->__setAcos($srcControllers);
        $this->__setPluginsAcos($pluginsControllers);
    }

    /**
     * Build ARO list
     * @return int
     */
    public function arosBuilder()
    {
        $this->Groups = TableRegistry::getTableLocator()->get('Groups');
        $this->Users = TableRegistry::getTableLocator()->get('Users');

        $newAros = [];
        $counter = 0;

        // Build the groups.
        $groups = $this->Groups->find('all')->toArray();
        foreach ($groups as $group) {
            $aro = new Aro([
                'alias' => $group->name,
                'foreign_key' => $group->id,
                'model' => 'Groups',
                'parent_id' => null
            ]);

            if ($this->__findAro($aro) == 0 && $this->Acl->Aro->save($aro)) {
                $counter++;
            }
        }

    // Build the users.
        $users = $this->Users->find('all');
        foreach ($users as $user) {
            $parent = $this->Aro->find(
                'all',
                ['conditions' => [
                        'model' => 'Groups',
                        'foreign_key' => $user->role_id
                ]]
            )->first();
                $aro = new Aro([
                'alias' => $user->email,
                'foreign_key' => $user->id,
                'model' => 'Users',
                'parent_id' => $parent->id
                ]);
            if ($this->__findAro($aro) == 0 && $this->Acl->Aro->save($aro)) {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * Gets the data for the current tag
     *
     */
    public function value($options = [])
    {
        $o = explode('.', $options);
        $data = $this->request->data;

        return $data[$o[0]][$o[1]][$o[2]];
    }

    /**
     * Get all controllers with actions
     *
     * @return array like Controller => actions
     */
    private function __getResources()
    {
        $controllers = $this->__getControllers();
        $resources = [];
        foreach ($controllers as $controller) {
            $actions = $this->__getActions($controller);
            array_push($resources, $actions);
        }

        return $resources;
    }

    /**
     * Get all controllers with actions in Plugins
     *
     */
    private function __getPluginsResources()
    {
        $controllers = $this->__getPluginsControllers();
        $resources = [];
        foreach ($controllers as $key => $plugin) {
            foreach ($plugin as $plugin => $controllers) {
                $resourcesPlugin = [$plugin => []];
                foreach ($controllers as $controller) {
                    $actions = $this->__getPluginActions($plugin, $controller);
                    foreach ($actions as $action) {
                        $resourcesPlugin[$plugin . '/' . $controller][] = $action;
                    }
                }
                array_push($resources, $resourcesPlugin);
            }
        }

        return $resources;
    }

    /**
     * Get all controllers only from "Controller path only"
     *
     * @return array return a list of all controllers
     */
    private function __getControllers()
    {
        $path = App::path('Controller');
        $dir = new Folder($path[0]);
        $files = $dir->findRecursive('.*Controller\.php');
        $results = [];
        foreach ($files as $file) {
            $controller = str_replace(App::path('Controller'), '', $file);
            $controller = explode('.', $controller)[0];
            $controller = str_replace('Controller', '', $controller);
            array_push($results, $controller);
        }

        return $results;
    }

    /**
     * Return all actions from the controller
     *
     * @param string $controllerName the controller to be check
     *
     * @return array
     */
    private function __getActions($controllerName)
    {
        $className = 'App\\Controller\\' . $controllerName . 'Controller';
        $class = new ReflectionClass($className);
        $actions = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $controllerName = str_replace("\\", "/", $controllerName);
        $results = [$controllerName => []];
        $ignoreList = ['beforeFilter', 'afterFilter', 'initialize', 'beforeRender'];
        foreach ($actions as $action) {
            if (
                $action->class == $className && !in_array($action->name, $ignoreList)
            ) {
                array_push($results[$controllerName], $action->name);
            }
        }

        return $results;
    }

    /**
     * Get all controllers from active Plugins
     *
     */
    private function __getPluginsControllers()
    {
        $results = [];
        $ignoreList = [
            '.',
            '..',
            'Component',
            'AppController.php',
        ];
        $plugins = Plugin::loaded();

        foreach ($plugins as $plugin) {
            $result = [$plugin => []];
            $path = Plugin::path($plugin);
            $path = $path . 'src/Controller/';
            if (is_dir($path)) {
                $files = scandir($path);
                foreach ($files as $file) {
                    if (!in_array($file, $ignoreList)) {
                        $controller = explode('.', $file)[0];
                        $controllerName = str_replace('Controller', '', $controller);
                        array_push($result[$plugin], $controllerName);
                    }
                }
                if (!empty($result[$plugin])) {
                    array_push($results, $result);
                }
            }
        }

        return $results;
    }

    /**
     * Get all actions in plugin controllers
     *
     */
    private function __getPluginActions($plugin, $controllerName)
    {
        $className = $plugin . '\\Controller\\' . $controllerName . 'Controller';
        $class = new ReflectionClass($className);
        $actions = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $results = [$controllerName => []];
        $ignoreList = ['beforeFilter', 'afterFilter', 'initialize'];
        foreach ($actions as $action) {
            if ($action->class == $className && !in_array($action->name, $ignoreList)) {
                array_push($results[$controllerName], $action->name);
            }
        }

        return $results;
    }

    /**
     * Acos Builder, find all public actions from controllers and stored them
     * with Acl tree behavior to the acos table.
     * Alias first letter of Controller will
     * be capitalized and actions will be lowercase
     *
     * @return bool return true if acos saved
     */
    private function __setAcos($ressources)
    {
        $root = $this->checkNodeOrSave($this->_base, $this->_base, null);
        unset($ressources[0]);
        foreach ($ressources as $controllers) {
            foreach ($controllers as $controller => $actions) {
                $tmp = explode('/', $controller);
                if (!empty($tmp) && isset($tmp[1])) {
                    $path = [0 => $this->_base];
                    $slash = '/';
                    $parent = [1 => $root->id];
                    $countTmp = count($tmp);
                    for ($i = 1; $i <= $countTmp; $i++) {
                        $path[$i] = $path[$i - 1];
                        if ($i >= 1 && isset($tmp[$i - 1])) {
                            $path[$i] = $path[$i] . $slash;
                            $path[$i] = $path[$i] . $tmp[$i - 1];
                            $this->checkNodeOrSave(
                                $path[$i],
                                $tmp[$i - 1],
                                $parent[$i]
                            );
                            $new = $this->Aco
                                    ->find()
                                    ->where(
                                        [
                                                'alias' => $tmp[$i - 1],
                                                'parent_id' => $parent[$i]
                                            ]
                                    )
                                    ->first();
                            $parent[$i + 1] = $new['id'];
                        }
                    }
                    foreach ($actions as $action) {
                        if (!empty($action)) {
                            $this->checkNodeOrSave(
                                $controller . $action,
                                $action,
                                end($parent)
                            );
                        }
                    }
                } else {
                    $controllerName = array_pop($tmp);
                    $path = $this->_base . '/' . $controller;
                    $controllerNode = $this->checkNodeOrSave(
                        $path,
                        $controllerName,
                        $root->id
                    );
                    foreach ($actions as $action) {
                        if (!empty($action)) {
                            $this->checkNodeOrSave(
                                $controller . '/' . $action,
                                $action,
                                $controllerNode['id']
                            );
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Plugins Acos Builder, find all public actions from plugin's controllers and stored them
     * with Acl tree behavior to the acos table.
     * Alias first letter of Controller will
     * be capitalized and actions will be lowercase
     *
     * @return bool return true if acos saved
     */
    private function __setPluginsAcos($ressources)
    {
        foreach ($ressources as $controllers) {
            foreach ($controllers as $controller => $actions) {
                $parent = [];
                $path = [];
                $tmp = [];
                $pluginName = '';
                $root = '';

                $tmp = explode('/', $controller);
                $pluginName = $tmp[0];
                $root = $this->checkNodeOrSave($pluginName, $pluginName, 1);
                $slash = '/';
                $parent = [1 => $root->id];
                $path = [0 => $pluginName];
                $countTmp = count($tmp);

                if (!empty($tmp) && isset($tmp[1])) {
                    for ($i = 1; $i <= $countTmp; $i++) {
                        if ($path[$i - 1] != $tmp[$i - 1]) {
                            $path[$i] = $path[$i - 1];
                        } else {
                            $path[$i] = '';
                        }
                        if ($i >= 1 && isset($tmp[$i - 1])) {
                            if ($path[$i]) {
                                $path[$i] = $path[$i] . $slash;
                            }
                            $path[$i] = $path[$i] . $tmp[$i - 1];
                            if ($tmp[$i - 1] == '') {
                                $tmp[$i - 1] = "Controller";
                            }
                            $new = $this->checkNodeOrSave(
                                $this->_base . '/' . $path[$i],
                                $tmp[$i - 1],
                                $parent[$i]
                            );
                            $parent[$i + 1] = $new->id;
                        }
                    }

                    $actions = array_shift($actions);
                    foreach ($actions as $action) {
                        if (!empty($action)) {
                            $this->checkNodeOrSave(
                                $controller . '/' . $action,
                                $action,
                                end($parent)
                            );
                        }
                    }
                } else {
                    $controllerName = array_pop($tmp);
                    $path = $this->_base . '/' . $controller;
                    $controllerNode = $this->checkNodeOrSave(
                        $path,
                        $controllerName,
                        $root->id
                    );
                    foreach ($actions as $action) {
                        if (!empty($action)) {
                            $this->checkNodeOrSave(
                                $controller . '/' . $action,
                                $action,
                                $controllerNode['id']
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if the aco exist and store it if empty
     *
     * @param string $path     the path like App/Admin/Admin/home
     * @param string $alias    the name of the alias like home
     * @param null   $parentId the parent id
     *
     * @return object
     */
    public function checkNodeOrSave($path, $alias, $parentId = null)
    {
        $node = $this->Aco->node($path);
        if ($node === false) {
            $data = [
                'parent_id' => $parentId,
                'model' => null,
                'alias' => $alias,
            ];
            $entity = $this->Aco->newEntity($data);
            $node = $this->Aco->save($entity);

            return $node;
        }

        return $node->first();
    }

    /**
     * Find aro and returns the number of matches
     *
     **/
    private function __findAro($aro)
    {
        $conditions = [
            'alias' => $aro->alias,
            'foreign_key' => $aro->foreign_key,
            'model' => $aro->model
        ];

        if ($aro->parent_id == null) {
            $conditions[] = 'parent_id IS NULL';
        } else {
            $conditions['parent_id'] = $aro->parent_id;
        }

        return $this->Acl->Aro->find('all', [
            'conditions' => $conditions,
            'recursive' => -1
        ])->count();
    }
}
