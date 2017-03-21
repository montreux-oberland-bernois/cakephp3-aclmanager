<?php

/**
 * Projet : tdb3
 * Auteur : Raphaël Gabriel
 * Date: 16.03.2016
 */

use Cake\Core\Configure;
use Cake\Utility\Inflector;

$this->assign('icon', 'lock');
$this->assign('title', 'Gestion des droits');
$this->assign('description', 'Gérer les autorisations');

echo $this->Html->css('AclManager.default',['inline' => false]);
echo $this->Html->script('AclManager.acl/manage.js', array('block' => 'script'));
echo $this->Html->script('Accounting.drilldown-table.js', ['block' =>'script']);

$btn_ico = [
        'allow' => '<i class="fa fa-check text-success"></i>',
        'deny' => '<i class="fa fa-remove text-danger"></i>',
        'inherit' => '<i class="fa fa-level-down text-primary"></i>'
];
?>

<div class="row">
    <div class="col-md-9">
        <div class="box">
            <div class="box-header with-border">
                <h4 style="margin-top: 0"><b><?php echo sprintf(__("Managing %s"), strtolower($model)); ?></b></h4>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <?php echo $this->Form->create('Perms'); ?>
                <table class="table table-hovered">
                    <thead>
                    <tr>
                        <th>Action</th>
                        <?php foreach ($aros as $aro){ ?>
                            <?php $aro = array_shift($aro); ?>
                            <th>
                                <?php
                                if ($model == 'Users') {
                                    $gData = $this->AclManager->getName('Groups', $aro['group_id']);
                                    echo h($aro[$aroDisplayField]) . ' (' . $gData['name'] . ')';
                                } else {
                                    echo h($aro[$aroDisplayField]);
                                }
                                ?>
                            </th>
                        <?php } ?>
                    </tr>
                    </thead>

                    <tbody>


                    <?php
                    $uglyIdent = Configure::read('AclManager.uglyIdent');
                    $lastIdent = null;
                    foreach ($acos as $id => $aco) {
                        $action = $aco['Action'];
                        $alias = $aco['Aco']['alias'];
                        $ident = substr_count($action, '/');
                        $nextAction = next($acos)['Action'];
                        $nextIdent = substr_count($nextAction, '/');;
                        if ($ident <= $lastIdent && !is_null($lastIdent)) {
                            for ($i = 0; $i <= ($lastIdent - $ident); $i++) {
                                echo "</tr>";
                            }
                        }
                        if ($ident > 1) {
                            echo "<tr class='active' style='display: none' data-parent='".$aco['Aco']['parent_id']."''>";
                        }elseif ($ident != $lastIdent) {
                            echo "<tr class='aclmanager-ident-" . $ident . "'>";
                        }

                            echo "<td>";
                            echo ($uglyIdent ? str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $ident) : "") . ($ident == 1 || $nextIdent > $ident ? "<i data-trigger='expand'  data-id='".$aco['Aco']['id']."' class='fa fa-plus-square click'></i> <strong>" : "" ) . h($alias) . ($ident == 1 ? "</strong>" : "" );
                            echo "</td>";
                            foreach ($aros as $aro):
                                $inherit = $this->AclManager->value("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}-inherit");
                                $allowed = $this->AclManager->value("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}");
                                $mAro = $model;
                                $mAllowed = $this->AclManager->Acl->check($aro, $action);
                                $mAllowedText = ($mAllowed) ? 'Allow' : 'Deny';
                                // Originally based on 'allowed' above 'mAllowed'
                                $icon = ($mAllowed) ? $this->Html->image('AclManager.allow_32.png') : $this->Html->image('AclManager.deny_32.png');
                                if ($inherit) {
                                    $icon = $this->Html->image('AclManager.inherit_32.png');
                                    $btns = ['allow', 'deny'];
                                }
                                if ($mAllowed && !$inherit) {
                                    $icon = $this->Html->image('AclManager.allow_32.png');
                                    $mAllowedText = 'Allow';
                                    $btns = ['deny', 'inherit'];
                                }
                                if ($mAllowed && $inherit) {
                                    $icon = $this->Html->image('AclManager.allow_inherited_32.png');
                                    $mAllowedText = 'Inherit';
                                    $btns = ['deny'];
                                }
                                if (!$mAllowed && $inherit) {
                                    $icon = $this->Html->image('AclManager.deny_inherited_32.png');
                                    $mAllowedText = 'Inherit';
                                    $btns = ['allow'];
                                }
                                if (!$mAllowed && !$inherit) {
                                    $icon = $this->Html->image('AclManager.deny_32.png');
                                    $mAllowedText = 'Deny';
                                    $btns = ['allow', 'inherit'];
                                }
                                echo "<td class=\"select-perm\">";
                                echo '<span data-toggle="tooltip" data-placement="top" title="" data-original-title="'.$mAllowedText.'">'. $icon . '</span> ' ;
                                foreach ($btns as $btn) {
                                    echo ' <a class="click-acl-'.$btn.' click" data-aco="'. str_replace("/", ":", $action) .'" data-aro="'.$aroAlias.':'.$aro[$aroAlias]['id'].'">'.$btn_ico[$btn].'</a>';
                                }
                              //  echo $this->Form->select("Perms." . str_replace("/", ":", $action) . ".{$aroAlias}:{$aro[$aroAlias]['id']}", array('inherit' => __('Inherit'), 'allow' => __('Allow'), 'deny' => __('Deny')), array('empty' => true, 'class' => 'form-control'));
                                echo "</td>";
                            endforeach;
                            $lastIdent = $ident;

                    }
                    for ($i = 0; $i <= $lastIdent; $i++) {
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="paginator">
                            <ul class="pagination">
                                <?php echo $this->Paginator->prev('< ' . __('previous')) ?>
                                <?php echo $this->Paginator->numbers() ?>
                                <?php echo $this->Paginator->next(__('next') . ' >') ?>
                            </ul>
                            <p><?php echo $this->Paginator->counter() ?></p>
                        </div>
                    </div>
                </div>
                <?php echo $this->Form->end(); ?>

            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-question-circle"></i> Aide</h3>
                <!-- /.box-tools -->
                <!-- /.box-header -->
                <div class="box-body">
                    <p><i class="fa fa-check text-success"></i> Autoriser | <i class="fa fa-remove text-danger"></i> Refuser | <i class="fa fa-level-down text-primary"></i> Hériter du parent</p>
                    <div class="row">
                        <div class="col-md-6">
                            <p><?php echo $this->Html->image('AclManager.deny_32.png' , ['width' => '16px']) . ' ' . __('Refusé') ?></p>
                            <p><?php echo $this->Html->image('AclManager.deny_inherited_32.png', ['width' => '16px']) . ' ' . __('Refusé par héritage') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><?php echo $this->Html->image('AclManager.allow_32.png', ['width' => '16px']) . ' ' . __('Autorisé') ?></p>
                            <p><?php echo $this->Html->image('AclManager.allow_inherited_32.png', ['width' => '16px']) . ' ' . __('Autorisé par héritage') ?></p>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
        </div>
    </div>
</div>

