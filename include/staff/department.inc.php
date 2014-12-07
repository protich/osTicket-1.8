<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($dept && $_REQUEST['a']!='add') {
    //Editing Department.
    $title=__('Update Department');
    $action='update';
    $submit_text=__('Save Changes');
    $info = $dept->getInfo();
    $info['id'] = $dept->getId();
    $info['groups'] = $dept->getAllowedGroups();
    $qstr.='&id='.$dept->getId();
} else {
    $title=__('Add New Department');
    $action='create';
    $submit_text=__('Create Dept');
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $info['ticket_auto_response']=isset($info['ticket_auto_response'])?$info['ticket_auto_response']:1;
    $info['message_auto_response']=isset($info['message_auto_response'])?$info['message_auto_response']:1;
    if (!isset($info['group_membership']))
        $info['group_membership'] = 1;

    $qstr.='&a='.$_REQUEST['a'];

}

$info = Format::htmlchars(($errors && $_POST) ? $_POST : $info);
?>
<form action="departments.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo __('Department');?></h2>
<br>
<ul class="tabs">
    <li class="active"><a href="#settings">
        <i class="icon-file"></i> <?php echo __('Settings'); ?></a></li>
    <li><a href="#access">
        <i class="icon-lock"></i> <?php echo __('Access'); ?></a></li>
</ul>
<div id="settings" class="tab_content">
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><?php echo __('Department Information');?></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                <?php echo __('Name');?>:
            </td>
            <td>
                <input data-translate-tag="<?php echo $dept ? $dept->getTranslateTag() : '';
                ?>" type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                <?php echo __('Type');?>:
            </td>
            <td>
                <input type="radio" name="ispublic" value="1" <?php echo $info['ispublic']?'checked="checked"':''; ?>><strong><?php echo __('Public');?></strong>
                &nbsp;
                <input type="radio" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>><strong><?php echo __('Private');?></strong> <?php echo mb_convert_case(__('(internal)'), MB_CASE_TITLE);?>
                &nbsp;<i class="help-tip icon-question-sign" href="#type"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('SLA'); ?>:
            </td>
            <td>
                <select name="sla_id">
                    <option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['sla_id']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error"><?php echo $errors['sla_id']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#sla"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Manager'); ?>:
            </td>
            <td>
                <span>
                <select name="manager_id">
                    <option value="0">&mdash; <?php echo __('None'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT staff_id,CONCAT_WS(", ",lastname, firstname) as name '
                        .' FROM '.STAFF_TABLE.' staff '
                        .' ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['manager_id'] && $id==$info['manager_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error"><?php echo $errors['manager_id']; ?></span>
                <i class="help-tip icon-question-sign" href="#manager"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Ticket Assignment'); ?>:</td>
            <td>
                <span>
                <input type="checkbox" name="assign_members_only" <?php echo
                $info['assign_members_only']?'checked="checked"':''; ?>>
                <?php echo __('Restrict ticket assignment to department members'); ?>
                <i class="help-tip icon-question-sign" href="#sandboxing"></i>
                </span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Outgoing Email Settings'); ?></strong>:</em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Outgoing Email'); ?>:
            </td>
            <td>
                <select name="email_id">
                    <option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' email ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$email,$name)=db_fetch_row($res)){
                            $selected=($info['email_id'] && $id==$info['email_id'])?'selected="selected"':'';
                            if($name)
                                $email=Format::htmlchars("$name <$email>");
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['email_id']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#email"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Template Set'); ?>:
            </td>
            <td>
                <select name="tpl_id">
                    <option value="0">&mdash; <?php echo __('System Default'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_GRP_TABLE.' tpl WHERE isactive=1 ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $selected=($info['tpl_id'] && $id==$info['tpl_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['tpl_id']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#template"></i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Autoresponder Settings'); ?></strong>:
                <i class="help-tip icon-question-sign" href="#auto_response_settings"></i></em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('New Ticket');?>:
            </td>
            <td>
                <span>
                <input type="checkbox" name="ticket_auto_response" value="0" <?php echo !$info['ticket_auto_response']?'checked="checked"':''; ?> >

                <?php echo __('<strong>Disable</strong> for this Department'); ?>
                <i class="help-tip icon-question-sign" href="#new_ticket"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('New Message');?>:
            </td>
            <td>
                <span>
                <input type="checkbox" name="message_auto_response" value="0" <?php echo !$info['message_auto_response']?'checked="checked"':''; ?> >
                <?php echo __('<strong>Disable</strong> for this Department'); ?>
                <i class="help-tip icon-question-sign" href="#new_message"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Auto-Response Email'); ?>:
            </td>
            <td>
                <span>
                <select name="autoresp_email_id">
                    <option value="0" selected="selected">&mdash; <?php echo __('Department Email'); ?> &mdash;</option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' email ORDER by name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$email,$name)=db_fetch_row($res)){
                            $selected = (isset($info['autoresp_email_id'])
                                    && $id == $info['autoresp_email_id'])
                                ? 'selected="selected"' : '';
                            if($name)
                                $email=Format::htmlchars("$name <$email>");
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$email);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error"><?php echo $errors['autoresp_email_id']; ?></span>
                <i class="help-tip icon-question-sign" href="#auto_response_email"></i>
                </span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Alerts and Notices'); ?>:</strong>
                <i class="help-tip icon-question-sign" href="#group_membership"></i></em>
            </th>
        </tr>
        <tr>
            <td width="180">
                <?php echo __('Recipients'); ?>:
            </td>
            <td>
                <span>
                <select name="group_membership">
<?php foreach (array(
    Dept::ALERTS_DISABLED =>        __("No one (disable Alerts and Notices)"),
    Dept::ALERTS_DEPT_ONLY =>       __("Department members only"),
    Dept::ALERTS_DEPT_AND_GROUPS => __("Department and Group members"),
) as $mode=>$desc) { ?>
    <option value="<?php echo $mode; ?>" <?php
        if ($info['group_membership'] == $mode) echo 'selected="selected"';
    ?>><?php echo $desc; ?></option><?php
} ?>
                </select>
                <i class="help-tip icon-question-sign" href="#group_membership"></i>
                </span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Department Signature'); ?></strong>:
                <span class="error">&nbsp;<?php echo $errors['signature']; ?></span>
                <i class="help-tip icon-question-sign" href="#department_signature"></i></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext no-bar" name="signature" cols="21"
                    rows="5" style="width: 60%;"><?php echo $info['signature']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
</div>
<div id="access" class="tab_content" style="display:none">
   <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan=2>
                <em><?php echo __('Primary department members have access to this department by default'); ?></em>
            </th>
        </tr>
        <tr>
            <th width="40%"><?php echo __('Group'); ?></th>
            <th><?php echo __('Role'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $deptId = $dept ? $dept->getId() : 0;
        $roles = Role::getRoles();
        $groups = Group::objects()
            ->annotate(array(
                'isenabled'=>new SqlExpr(array(
                        'flags__hasbit' => Group::FLAG_ENABLED))
                ))
            ->order_by('name');
        foreach ($groups as $group) {
            $DeptAccess = $group->getDepartmentsAccess();
            ?>
         <tr>
            <td>
             &nbsp;
             <label>
              <?php
              $ck = ($info['groups'] && in_array($group->getId(), $info['groups'])) ? 'checked="checked"' : '';
              echo sprintf('%s&nbsp;&nbsp;%s',
                        sprintf('<input type="checkbox" class="grp-ckb"
                            name="groups[]" value="%s" %s />',
                            $group->getId(), $ck),
                        Format::htmlchars($group->getName()));
              ?>
             </label>
            </td>
            <td>
                <?php
                $_name = 'group'.$group->getId().'_role_id';
                ?>
                <select name="<?php echo $_name; ?>">
                    <option value="0">&mdash; <?php
                        echo sprintf('%s (%s)',
                                __('Group Default'),
                                $group->getRole());
                        ?>
                        &mdash;</option>
                    <?php
                    foreach ($roles as $rid => $role) {
                        $sel = '';
                        if (isset($info[$_name]))
                            $sel = ($info[$_name] == $rid) ? 'selected="selected"' : '';
                        elseif ($deptId && isset($DeptAccess[$deptId]))
                            $sel = ($DeptAccess[$deptId] == $rid) ?  'selected="selected"' : '';

                        echo sprintf('<option value="%d" %s>%s</option>',
                                $rid, $sel, $role);
                    } ?>
                </select>
                <i class="help-tip icon-question-sign" href="#dept-role"></i>
            </td>
         </tr>
         <?php
        } ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="2">
            <?php echo __('Select');?>:&nbsp;
            <a id="selectAll" href="#grp-ckb"><?php echo __('All');?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#grp-ckb"><?php echo __('None');?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#grp-ckb"><?php echo __('Toggle');?></a>&nbsp;&nbsp;
        </td>
     </tr>
    </tfoot>
   </table>
</div>
<p style="text-align:center">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" value="<?php echo __('Cancel');?>"
        onclick='window.location.href="?"'>
</p>
</form>
