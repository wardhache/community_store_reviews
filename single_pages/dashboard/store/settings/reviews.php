<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<form method="post" action="<?= $view->action('save')?>">
    <div class="row">
        <div class="col-sm-3">
            <ul class="nav nav-pills nav-stacked">
                <li class="active"><a href="#settings-reviews-ratings" data-pane-toggle ><?= t('Ratings')?></a></li>
                <li><a href="#settings-reviews-statuses" data-pane-toggle><?= t('Review Statuses')?></a></li>
                <li><a href="#settings-reviews-autoapprove" data-pane-toggle><?= t('Auto Approval')?></a></li>
                <li><a href="#settings-reviews-form" data-pane-toggle><?= t('Review Form')?></a></li>
                <li><a href="#settings-reviews-reminder" data-pane-toggle><?= t('Reminder')?></a></li>
            </ul>
        </div>

        <div class="col-sm-9 store-pane active" id="settings-reviews-ratings">
            <h3><?= t('Ratings');?></h3>

            <div class="form-group">
                <?= $form->label('raName[]',t('Standard rating %sThis rating will automatically be used for calculations and the review reminder landings page%s', '<small class="text-muted">','</small>')); ?>
                <?= $form->text('raName[]',$generalRating->getName(), array('placeholder'=>t('General')));?>
                <?= $form->hidden('raID[]',$generalRating->getID());?>
                <?= $form->hidden('raHandle[]',$generalRating->getHandle());?>
                <?= $form->hidden('raStandard[]',$generalRating->getStandard());?>
            </div>

            <h4><?= t('Custom Ratings');?></h4>
            <div class="panel panel-default">
                <table class="table" id="reviewRatingTable">
                    <thead>
                    <tr>
                        <th rowspan="1">&nbsp;</th>
                        <th rowspan="1"><?= t('Handle'); ?></th>
                        <th rowspan="1"><?= t('Display Name'); ?></th>
                        <th rowspan="1">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($customRatings)) { ?>
                            <?php foreach($customRatings as $rating){?>
                                <tr>
                                    <td class="sorthandle">
                                        <input type="hidden" name="raID[]" value="<?= $rating->getID(); ?>">
                                        <input type="hidden" name="raStandard[]" value="0">
                                        <i class="fa fa-arrows-v"></i>
                                    </td>
                                    <td><input type="text" name="raHandle[]" value="<?= t($rating->getHandle()); ?>" class="form-control ccm-input-text"></td>
                                    <td><input type="text" name="raName[]" value="<?= t($rating->getName()); ?>" class="form-control ccm-input-text"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm deleteCustomRating"><i class="fa fa-times"></i></button></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4" id="customRatingNone">
                                    <?= t('No custom ratings found...'); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <script>
                $(function(){
                    $('#reviewRatingTable TBODY').sortable({
                        cursor: 'move',
                        opacity: 0.5,
                        handle: '.sorthandle'
                    });

                    $('#customRatingAdd').on('click', function() {
                        $('#customRatingNone').hide();
                        $('#reviewRatingTable TBODY').append('<tr>' +
                          '<td class="sorthandle"><input type="hidden" name="raID[]" value=""><input type="hidden" name="raStandard[]" value="0"><i class="fa fa-arrows-v"></i></td>' +
                          '<td><input type="text" name="raHandle[]" value="" class="form-control ccm-input-text"></td>' +
                          '<td><input type="text" name="raName[]" value="" class="form-control ccm-input-text"></td>' +
                          '<td><button type="button" class="btn btn-danger btn-sm deleteCustomRating"><i class="fa fa-times"></i></button></td>' +
                          '</tr>');
                    });

                    $(document).on('click', '.deleteCustomRating', function() {
                        $(this).closest('tr').remove();
                        if($('#reviewRatingTable TBODY').children().size() <= 1) {
                            $('#customRatingNone').show();
                        }
                    });
                });
            </script>

            <button class="btn btn-success" id="customRatingAdd" type="button"><?= t('Add Rating'); ?></button>
        </div>

        <div class="col-sm-9 store-pane" id="settings-reviews-statuses">
            <h3><?= t('Review Statuses');?></h3>

            <?php if(count($reviewStatuses)>0){ ?>
                <div class="panel panel-default">
                    <table class="table" id="reviewStatusTable">
                        <thead>
                        <tr>
                            <th rowspan="1">&nbsp;</th>
                            <th rowspan="1"><?= t('Display Name'); ?></th>
                            <th rowspan="1"><?= t('Show Frontend'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($reviewStatuses as $reviewStatus){?>
                            <tr>
                                <td class="sorthandle"><input type="hidden" name="rsID[]" value="<?= $reviewStatus->getID(); ?>"><i class="fa fa-arrows-v"></i></td>
                                <td><input type="text" name="rsName[]" value="<?= t($reviewStatus->getName()); ?>" class="form-control ccm-input-text"></td>
                                <td><input type="radio" name="rsShowFrontend" value="<?= $reviewStatus->getID(); ?>" <?= $reviewStatus->getShowFrontend() ? 'checked':''; ?>></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <script>
                        $(function(){
                            $('#reviewStatusTable TBODY').sortable({
                                cursor: 'move',
                                opacity: 0.5,
                                handle: '.sorthandle'
                            });
                        });
                    </script>
                </div>
            <?php } else { ?>
                <?= t("No Review Statuses are available"); ?>
            <?php } ?>
        </div>

        <div class="col-sm-9 store-pane" id="settings-reviews-autoapprove">
            <h3><?= t('Auto Approval');?></h3>

            <div class="form-group">
                <label for="autoApprove"><?= t("Enable Auto Approve?")?></label>
                <?= $form->select('autoApprove',array(0=>t("No, I will manually approve or decline every review."),1=>t("Yes, every review equal or higher than the minimum average rating should be approved automatically.")),Config::get('community_store_review.autoApprove')); ?>
            </div>

            <div class="form-group">
                <?= $form->label('autoApproveRating',t('Minimum Average Rating %sBetween 1 &amp; 100&#37; %s', '<small class="text-muted">','</small>')); ?>
                <?= $form->number('autoApproveRating',Config::get('community_store_review.autoApproveRating'), array("min" => 0, "max" => 100, "step" => 1)); ?>
            </div>
        </div>

        <div class="col-sm-9 store-pane" id="settings-reviews-form">
            <h3><?= t('Review Form Settings');?></h3>

            <h4><?= t('Groups'); ?> <small><?= t('May submit a review'); ?></small></h4>

            <?php foreach($userGroups as $key => $group) { ?>
                <?php $configGroup = Config::get('community_store_review.formGroups');?>
                <div>
                    <input type="checkbox" name="formGroups[]" id="formGroup<?= $key; ?>" value="<?= $group; ?>" <?= (!empty($configGroup) ? (in_array($group, $configGroup) ? 'checked' : '') : ''); ?>>
                    <label for="formGroup<?= $key; ?>"><?= $group; ?></label>
                </div>
            <?php } ?>

            <h4><?= t('Captcha'); ?> <small><?= t('Show on the following forms'); ?></small></h4>

            <div>
                <?= $form->checkbox('captchaMail','1',Config::get('community_store_review.captchaMail') ? '1' : '0') ?>
                <label for="captchaMail"><?= t("Captcha after reminder mail")?></label>
            </div>

            <div>
                <?= $form->checkbox('captchaFormLoggedOut','1',Config::get('community_store_review.captchaFormLoggedOut') ? '1' : '0') ?>
                <label for="captchaFormLoggedOut"><?= t("Captcha in form when user NOT logged in")?></label>
            </div>

            <div>
                <?= $form->checkbox('captchaFormLoggedIn','1',Config::get('community_store_review.captchaFormLoggedIn') ? '1' : '0') ?>
                <label for="captchaFormLoggedIn"><?= t("Captcha in form when user IS logged in")?></label>
            </div>

            <h3><?= t('Form Details'); ?></h3>

            <div class="form-group">
                <?= $form->label('formTitle', t('Title')); ?>
                <?= $form->text('formTitle', Config::get('community_store_review.formTitle')); ?>
            </div>

            <div class="form-group">
                <?= $form->label('formSubmitText', t('Submit Text')); ?>
                <?= $form->text('formSubmitText', Config::get('community_store_review.formSubmitText')); ?>
            </div>

            <div class="form-group">
                <?= $form->label('formRecipientEmail', t('Notify me by email when people submit a review')); ?>
                <div class="input-group">
                    <?php $formNotifyMeOnSubmission = Config::get('community_store_review.formNotifyMeOnSubmission'); ?>
                    <span class="input-group-addon" style="z-index: 2000">
                        <?= $form->checkbox('formNotifyMeOnSubmission','1', ($formNotifyMeOnSubmission ? '1' : '0')); ?>
                    </span>
                    <?php
                        $formRecipientEmailAttr = array();
                        if($formNotifyMeOnSubmission != 1) {
                            $formRecipientEmailAttr["disabled"] = "disabled";
                        }
                    ?>
                    <?= $form->text('formRecipientEmail', Config::get('community_store_review.formRecipientEmail'), $formRecipientEmailAttr); ?>
                </div>
                <small><?= t('(Seperate multiple emails with a comma)'); ?></small>
            </div>

            <div class="form-group">
                <?= $form->label('formThankyouMsg', t('Message to display when review completed')); ?>
                <?= $form->textarea('formThankyouMsg', Config::get('community_store_review.formThankyouMsg')); ?>
            </div>

            <div class="form-group">
                <?php $formRedirectCID = Config::get('community_store_review.formRedirectCID'); ?>
                <?php $ps = Core::make('helper/form/page_selector'); ?>
                <?= $form->label('formRedirectCID', t('Redirect to another page after review submission?')); ?>
                <?= $ps->selectPage('formRedirectCID', ($formRedirectCID > 0 ? $formRedirectCID : false)); ?>
            </div>

            <script>
                $(function(){
                    $('#formNotifyMeOnSubmission').on('click', function() {
                        if ($(this).is(':checked')) {
                            $("#formRecipientEmail").prop('disabled', false);
                            $("#formRecipientEmail").focus();
                        } else {
                            $("#formRecipientEmail").prop('disabled', true);
                            $("#formRecipientEmail").val('');
                        }
                    });
                });
            </script>
        </div>

        <div class="col-sm-9 store-pane" id="settings-reviews-reminder">
            <h3><?= t('Review Reminder');?></h3>

            <div class="form-group">
                <label for="reminder"><?= t("Enable Review Reminder?")?></label>
                <?= $form->select('reminder',array(0=>t("No, the customer has to use the form on the website."),1=>t("Yes, the customer gets an e-mail to give a review about its last order.")),Config::get('community_store_review.reminder')); ?>
            </div>

            <h4><?= t('Settings'); ?></h4>

            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <?= $form->label('reminderOrderAfterDays',t('Days after creating order?')); ?>
                        <?= $form->number('reminderOrderAfterDays',Config::get('community_store_review.reminderOrderAfterDays'), array('min' => 1, 'step' => 1));?>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group">
                        <?= $form->label('reminderOrderStatus',t('Order must have the status')); ?>
                        <?= $form->select('reminderOrderStatus',$orderStatuses,Config::get('community_store_review.reminderOrderStatus'));?>
                    </div>
                </div>
            </div>

            <h3><?= t('E-mail Details'); ?></h3>

            <div class="form-group">
                <?= $form->label('reminderCc',t('Send reminder copy to email %sseparate multiple emails with commas%s', '<small class="text-muted">','</small>')); ?>
                <?= $form->text('reminderCc',Config::get('community_store_review.reminderCc'), array('placeholder'=>t('Email Address')));?>
            </div>

            <h4><?= t('Emails Sent From');?></h4>

            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <?= $form->label('reminderFrom',t('From Email'));?>
                        <?= $form->text('reminderFrom',Config::get('community_store_review.reminderFrom'),array('placeholder'=>t('From Email Address'))); ?>
                    </div>
                </div>

                <div class="col-xs-6">
                    <div class="form-group">
                        <?= $form->label('reminderFromName',t('From Name'));?>
                        <?= $form->text('reminderFromName',Config::get('community_store_review.reminderFromName'),array('placeholder'=>t('From Name'))); ?>
                    </div>
                </div>
            </div>

            <h4><?= t('Reminder Emails');?></h4>

            <div class="form-group">
                <label><?= t("Reminder Email Header Html")?></label>
                <?= $form->textarea('reminderMailHeaderHtml', Config::get('community_store_review.reminderMailHeaderHtml')); ?>
            </div>

            <div class="form-group">
                <label><?= t("Reminder Email Header Content")?></label>
                <?php $editor = \Core::make('editor');
                echo $editor->outputStandardEditor('reminderMailHeaderContent', Config::get('community_store_review.reminderMailHeaderContent'));?>
            </div>

            <div class="form-group">
                <label><?= t("Reminder Email Footer Content")?></label>
                <?php $editor = \Core::make('editor');
                echo $editor->outputStandardEditor('reminderMailFooterContent', Config::get('community_store_review.reminderMailFooterContent'));?>
            </div>

            <div class="form-group">
                <label><?= t("Reminder Email Footer Html")?></label>
                <?= $form->textarea('reminderMailFooterHtml', Config::get('community_store_review.reminderMailFooterHtml')); ?>
            </div>
        </div>
    </div>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button class="pull-right btn btn-success" type="submit" ><?= t('Save Settings')?></button>
        </div>
    </div>
</form>
