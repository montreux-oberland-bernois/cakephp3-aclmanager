<?php
/**
 * Projet : tdb3
 * Auteur : Raphaël Gabriel
 * Date: 16.03.2016
 */
$this->assign('icon', 'lock');
$this->assign('title', 'Gestion des droits');
$this->assign('description', 'version 0.1');
echo $this->Html->css('AclManager.default',['inline' => false]);
?>
<h2>Gestion</h2>
<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-users"></i></span>
            <a href="<?php echo $this->Url->build(['controller' => 'Acl', 'action' => 'Permissions', 'Groups']); ?>" style="color: #FFF">
                <div class="info-box-content">
                    <span class="info-box-text">Gestion des droits des groupes</span>
                </div>
            </a>
            <!-- /.info-box-content -->
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-user"></i></span>
            <a href="<?php echo $this->Url->build(['controller' => 'Acl', 'action' => 'Permissions', 'Users']); ?>" style="color: #FFF">
                <div class="info-box-content">
                    <span class="info-box-text">Gestion des droits des utilisateurs</span>
                </div>
            </a>
            <!-- /.info-box-content -->
        </div>
    </div>
</div>
<h2>Mettre à jour</h2>
<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-refresh"></i></span>
            <a href="<?php echo $this->Url->build(['controller' => 'Acl', 'action' => 'UpdateAcos']); ?>" style="color: #FFF">
                <div class="info-box-content">
                    <span class="info-box-text">Mettra à jour les ACOs</span>
                </div>
            </a>
            <!-- /.info-box-content -->
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-retweet"></i></span>
            <a href="<?php echo $this->Url->build(['controller' => 'Acl', 'action' => 'UpdateAros']); ?>" style="color: #FFF">
                <div class="info-box-content">
                    <span class="info-box-text">Mettre à jour les AROs</span>
                </div>
            </a>
            <!-- /.info-box-content -->
        </div>
    </div>
</div>
<h2>Supprimer et restaurer</h2>
<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-chain-broken"></i></span>
            <a href="<?php echo $this->Url->build(['controller' => 'Acl', 'action' => 'RevokePerms']); ?>" style="color: #FFF">
                <div class="info-box-content">
                    <span class="info-box-text">Réinitialiser les permissions</span>
                </div>
            </a>
            <!-- /.info-box-content -->
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-trash"></i></span>
            <a href="<?php echo $this->Url->build(['controller' => 'Acl', 'action' => 'drop']); ?>" style="color: #FFF">
                <div class="info-box-content">
                    <span class="info-box-text">Supprimer les ACOs/AROs</span>
                </div>
            </a>
            <!-- /.info-box-content -->
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-green">
            <span class="info-box-icon"><i class="fa fa-history"></i></span>
            <a href="<?php echo $this->Url->build(['controller' => 'Acl', 'action' => 'defaults']); ?>" style="color: #FFF">
                <div class="info-box-content">
                    <span class="info-box-text">Restaurer les paramètres par défaut</span>
                </div>
            </a>
            <!-- /.info-box-content -->
        </div>
    </div>
</div>

