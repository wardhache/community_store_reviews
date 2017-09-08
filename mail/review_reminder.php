<?php
defined('C5_EXECUTE') or die("Access Denied.");

$dh = Core::make('helper/date');
$encryptor = Core::make("helper/encryption");

$subject = t("%s: Submit a review for order #%s", $siteName, $order->getOrderID());

/**
 * HTML BODY START
 */
ob_start();
$mailData = array();
$mailData['rHash'] = $mailHash;
$mailData['oID'] = $order->getOrderID();
$mailData['cID'] = $order->getCustomerID();
?>
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
    <?= $mailHeaderHtml; ?>
    <?= $mailHeaderContent; ?>
    <table>
        <tbody>
            <?php $orderItems = $order->getOrderItems(); ?>
            <?php if(!empty($orderItems)) { ?>

            <?php foreach($orderItems as $item) { ?>
                <?php $mailData['pID'] = $item->getProductID(); ?>
                <tr style="padding:15px; text-align:center;">
                    <td>
                        <h2><?= $item->getProductName(); ?></h2>
                        <?php for($i = 1; $i <= 5; $i++) { ?>
                            <?php $mailData['rRating'] = $i; ?>
                            <?php $mailEncrypt = implode('|', array_map(function ($v, $k) { return sprintf("%s=%s", $k, $v); }, $mailData, array_keys($mailData)));?>
                            <a href="<?= $url; ?>?rm=<?= rawurlencode($encryptor->encrypt($mailEncrypt)); ?>">
                                <img src="<?= BASE_URL ; ?>/packages/community_store_reviews/css/images/star.png" alt="&#9733;" />
                            </a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
      </tbody>
    </table>
    <?= $mailFooterContent; ?>
    <?= $mailFooterHtml; ?>

<?php
$bodyHTML = ob_get_clean();
/**
 * HTML BODY END
 *
 * ======================
 *
 * PLAIN TEXT BODY START
 */
ob_start();

?>

<?= t("Review Reminder for Order #:") ?> <?= $order->getOrderID() ?>
<?php

$body = ob_get_clean(); ?>
