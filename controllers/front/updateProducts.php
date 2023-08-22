<?php
require_once(_PS_MODULE_DIR_ . '/nostotagging/nostotagging.php');

use Nosto\Model\Signup\Account as NostoSDKAccount;

/**
 * Cron controller for updating products in batches to Nosto.
 */
class MyNostoCronUpdateProductsModuleFrontController extends ModuleFrontController
{
    /**
     * Default sorting parameter
     */
    const DEFAULT_ORDER_BY = 'date_upd';

    /**
     * Default sorting direction
     */
    const DEFAULT_ORDER_DIRECTION = 'DESC';

    /**
     * Default limit for how many products to update
     *
     * @var int
     */
    private $limit = 50;

    /**
     * Default limit for how many products to update
     *
     * @var int
     */
    private $offset = 0;

    /**
     * Constructor for the controller
     */
    public function __construct()
    {
        // Remove this line if you want to call this controller remotely
        if ($_SERVER['REMOTE_ADDR'] !== "127.0.0.1") {
            die();
        }
        parent::__construct();
        if (Tools::getValue('limit')) {
            $this->limit =  Tools::getValue('limit');
        }
        if (Tools::getValue('offset')) {
            $this->offset =  Tools::getValue('offset');
        }
    }

    /**
     * @inheritdoc
     */
    public function initContent()
    {
        $updatedProductsCount = 0;
        $controller = $this;
        $messages = [];
        $errors = [];

        NostoHelperContext::runInContextForEachLanguageEachShop(function () use (
            &$updatedProductsCount,
            $controller,
            &$messages,
            &$errors
        ) {
            $shopGroup = Shop::getContextShopGroup();
            if ($shopGroup !== null
                && (bool)$shopGroup->active
            ) {
                try {
                    $nostoAccount = NostoHelperAccount::getAccount();
                    if ($nostoAccount instanceof NostoSDKAccount) {
                        $products = self::getProductsFromStore(
                            NostoHelperContext::getShopId(),
                            self::DEFAULT_ORDER_BY,
                            self::DEFAULT_ORDER_DIRECTION,
                            false,
                            false
                        );

                        if (count($products) == 0) {
                            return;
                        }

                        $nostoProductOperation = new Nosto\Operation\UpsertProduct($nostoAccount);
                        /* @var Product $product */
                        foreach ($products as $product) {
                            $prestaProduct = new Product(
                                $product['id_product'],
                                true,
                                NostoHelperContext::getLanguageId(),
                                NostoHelperContext::getShopId()
                            );

                            $nostoProduct = NostoProduct::loadData($prestaProduct);
                            if ($nostoProduct instanceof NostoProduct) {
                                $nostoProductOperation->addProduct($nostoProduct);
                                ++$updatedProductsCount;
                            }
                        }
                        $messages[] = sprintf(
                            'Updating %d products for store #%d and language #%d',
                            $updatedProductsCount,
                            NostoHelperContext::getShopId(),
                            NostoHelperContext::getLanguageId()
                        );

                        try {
                            $nostoProductOperation->upsert();
                        } catch (NostoException $e) {
                            $errors[] = sprintf('Failed to update products. Reason: %s', $e->getMessage()) ;
                        }
                    }
                } catch (\Exception $e) {
                    NostoHelperLogger::error($e);
                }
            }
        });

        $this->printMessages($messages, $errors);
        die;
    }

    /**
     * Fetches the products by given argurments
     *
     * @param $storeId
     * @param null $order_by
     * @param null $order_direction
     * @param bool $id_category
     * @param bool $only_active
     * @param null $context
     * @return array
     */
    private function getProductsFromStore(
        $storeId,
        $order_by = null,
        $order_direction = null,
        $id_category = false,
        $only_active = false,
        $context = null
    )
    {
        if (empty($order_by)) {
            $order_by = self::DEFAULT_ORDER_BY;
        }
        if (empty($order_direction)) {
            $order_direction = self::DEFAULT_ORDER_DIRECTION;
        }
        return ProductCore::getProducts(
            $storeId,
            $this->offset,
            $this->limit,
            $order_by,
            $order_direction,
            $id_category,
            $only_active,
            $context
        );
    }

    /**
     * Prints out the messages
     *
     * @param array $customMessages
     * @param array $customErrors
     */
    private function printMessages(array $customMessages, array $customErrors)
    {
        echo "\n\n--- Nosto update info ---\n\n";
        if (count($customMessages) > 0) {
            echo "\nMessages:\n";
            foreach ($customMessages as $customMessage) {
                echo " > $customMessage\n";
            }
        }
        if (count($customErrors)) {
            echo "\n\nErrors: \n";
            foreach ($customErrors as $customError) {
                echo " > $customError \n";
            }
        } else {
            echo "\n\nNo errors\n";
        }
       echo "\n\n--- End of Nosto update info ---\n\n";
    }
}