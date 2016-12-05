<?php
/**
 * Projet : AclManager
 * Auteur : Raphaël Gabriel
 * Date: 16.03.2016
 */
namespace AclManager\Controller;

use App\Controller\AppController as BaseController;
use Cake\Event\Event;

class AppController extends BaseController {

    /**
     * beforeFitler
     */
    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        
    }

}
