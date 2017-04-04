<?php
// # RatePAY invoice reserve transaction

// This example displays the usage of reserve method for payment method RatePAY invoice.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/config.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Use the amount object as amount which has to be payed by the consumer.
$amount = new Amount(2400, 'EUR');

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// The order number
$orderNumber = 'A2';

// ### Basket items
// A Basket contains one or more items.

// For each item you have to set some properties as described here.
// Required: name, price, quantity, article number, tax rate.
// Optional: description.
$item1 = new \Wirecard\PaymentSdk\Entity\Item('Item 1', new Amount(400, 'EUR'), 1);
// In contrast to the [basket example](../Features/basket.html),
// RatePAY requires the **tax rate** and the ** article number**.
$item1->setArticleNumber('A1');
$item1->setTaxRate(0.1);

$item2 = new \Wirecard\PaymentSdk\Entity\Item('Item 2', new Amount(1000, 'EUR'), 2);
$item2->setArticleNumber('B2');
$item2->setTaxRate(0.2);

// Create a basket to store the items.
$basket = new \Wirecard\PaymentSdk\Entity\Basket();
$basket->add($item1);
$basket->add($item2);

// #### Account holder with address
$address = new \Wirecard\PaymentSdk\Entity\Address('DE', 'Berlin', 'Berlin');
$address->setPostalCode('13353');

$accountHolder = new \Wirecard\PaymentSdk\Entity\AccountHolder();
$accountHolder->setFirstName('John');
$accountHolder->setLastName('Constantine');
$accountHolder->setEmail('john.doe@test.com');
$accountHolder->setPhone('03018425165');
$accountHolder->setDateOfBirth(new \DateTime('1973-12-07'));
$accountHolder->setAddress($address);


// ## Transaction

// The RatePAY invoice transaction holds all transaction relevant data for the reserve process.
$transaction = new RatepayInvoiceTransaction();
$transaction->setNotificationUrl($notificationUrl);
$transaction->setAmount($amount);
$transaction->setBasket($basket);
$transaction->setOrderNumber($orderNumber);
$transaction->setAccountHolder($accountHolder);

// ### Transaction Service

// The service is used to execute the reserve operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);


// ## Response handling

// The response of the service must be handled depending on it's class.
if ($response instanceof SuccessResponse) {
    echo 'Reservation successfully completed.<br>';
    echo getTransactionLink($baseUrl, $response);
    ?>
    <form action="pay-based-on-reserve.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <input type="submit" value="Capture the reservation">
    </form>
    <br>
    <form action="cancel.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <label for="amount">Amount to cancel:</label>
        <input type="text" name="amount" id="amount" value="2400"/>
        <input type="submit" value="Cancel">
    </form>
    <form action="credit.php" method="post">
        <input type="hidden" name="parentTransactionId" value="<?= $response->getTransactionId() ?>"/>
        <label for="amount">Amount to credit:</label>
        <input type="text" name="amount" id="amount" value="100"/>
        <input type="submit" value="Credit">
    </form>
    <?php
// The failure state is represented by a FailureResponse object.
// In this case the returned errors should be stored in your system.
} elseif ($response instanceof FailureResponse) {
// In our example we iterate over all errors and echo them out. You should display them as
// error, warning or information based on the given severity.
    foreach ($response->getStatusCollection() as $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Entity\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description);
    }
}