<?php
defined('C5_EXECUTE') or die("Access Denied.");
$form = Loader::helper('form');
$encryptor = Core::make("helper/encryption");

?>
<div class="row">
    <div class="col-sm-12">
        <?php if($showForm) { ?>
            <h1><?= t('Review for'); ?> <?= $product->getName(); ?></h1>
        <?php } else { ?>
            <h1><?= t('Review'); ?></h1>
        <?php } ?>
    </div>
</div>

<?php if($showForm) { ?>

<div class="row">
    <div class="col-sm-8 col-xs-12">

        <?php if($rSuccess) { ?>
            <div class="alert alert-success">
                <?= $rSuccess; ?>
            </div>

            <?php if($moreProducts) { ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?= t('Give another review on one of your purchased products of order'); ?> #<?= $order->getOrderID(); ?>
                    </div>
                    <div class="panel-body">
                    <?php
                        $reviewData = array();
                        $reviewData['rHash'] = Config::get('community_store_review.reminderHash');;
                        $reviewData['oID'] = $order->getOrderID();
                        $reviewData['cID'] = $order->getCustomerID();
                    ?>
                    <?php foreach($moreProducts as $item) { ?>
                        <?php $reviewData['pID'] = $item->getProductID(); ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <?= $item->getProductName(); ?>
                            </div>
                            <div class="col-sm-6">
                                <?php for($i = 1; $i <= 5; $i++) { ?>
                                    <?php $reviewData['rRating'] = $i; ?>
                                    <?php $reviewEncrypt = implode('|', array_map(function ($v, $k) { return sprintf("%s=%s", $k, $v); }, $reviewData, array_keys($reviewData)));?>
                                    <a href="<?= URL::to('/reviews') ?>?rm=<?= rawurlencode($encryptor->encrypt($reviewEncrypt)); ?>">
                                        <img src="<?= BASE_URL ; ?>/packages/community_store_reviews/css/images/star-small.png" alt="&#9733;" />
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    </div>
                </div>
            <?php } ?>

        <?php } else { ?>

            <?php if($error) { ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach($rError as $e) { ?>
                            <li><?= $e; ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <?= $formTitle; ?>
                </div>
                <div class="panel-body">
                    <form method="post" action="<?= $this->action('add_review'); ?>">
                        <?= $form->hidden('pID', $product->getID()); ?>
                        <?= $form->hidden('cID', $customerID); ?>
                        <?= $form->hidden('oID', $order->getOrderID()); ?>
                        <div class="form-group">
                            <div class="form-group">
                                <?= $form->label('rating[]', t($standardRating->getName())); ?>
                                <?= $standardRating->getFormHtml($rating, "big"); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <?= $form->label('rNickname', t('Nickname')); ?>
                            <?= $form->text('rNickname', $rNickname); ?>
                        </div>
                        <div class="form-group">
                            <?= $form->label('rTitle', t('Title of Review')); ?>
                            <?= $form->text('rTitle', $rTitle); ?>
                        </div>
                        <?php if($customRatings) { ?>
                            <?php foreach($customRatings as $cRating) { ?>
                                <div class="form-group">
                                    <?= $form->label('rating[]', t($cRating->getName())); ?>
                                    <?= $cRating->getFormHtml(); ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <div class="form-group">
                            <?= $form->label('rComment', t('Review')); ?>
                            <?= $form->textarea('rComment', $rComment, array('rows' => 10)); ?>
                        </div>
                        <?php if($captcha) { ?>
                            <div class="form-group">
                                <?= $form->label('catpcha', t('Please type the letters an numers shown in the image. Click the image to see another Captha.')); ?>
                                <?php $captcha->showInput(); ?>
                                <?php $captcha->display(); ?>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <?= $form->submit('rSubmit', $formSubmitText, array('class' => 'btn btn-primary')); ?>
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="col-sm-4 col-xs-12">
        <ul class="store-review-reminder-order-items list-group">
            <?php $orderItems = $order->getOrderItems(); ?>
            <li class="store-review-reminder-order-title list-group-item">
                <strong><?= t('Order'); ?> #<?= $order->getOrderID(); ?></strong>
            </li>
            <?php foreach($orderItems as $item) { ?>
                <li class="store-review-reminder-order-item list-group-item">
                    <?= $item->getProductName(); ?>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>

<?php } else { ?>

    <div class="alert alert-danger">
        <ul>
            <li>
                <?= t('The order is not found...'); ?>
            </li>
        </ul>
    </div>

<?php } ?>
