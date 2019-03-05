# Custom cron job for Nosto PrestaShop module

This PrestaShop extension createa a custom "cron controller" for updating product data to Nosto. Please note that this extension should NOT be used as is. This is more of an example how to fetch products with the given limit and offset and send them to Nosto. You MUST implement the logic to only update products that have been changed. This can be achieved for example by checking when the product was last updated and only update the changed ones. This controller does not have any authentication either. The controller is only accessible from localhost.

# Installing
Clone this repository to your Prestashop's "modules/mynostocron" directory and install the module (mynostocron) from Prestashop store admin.  

# Calling the controller
After you have cloned the repository and installed the module you can access the controller from command line by calling curl -v http:/your.store.com/modules/mynostocron/front/updateProducts.php?fc=module&module=mynostocron&controller=updateProducts&limit=50&offset=0. Please note that this controller is accessible only from localhost. If you need to enable remote access comment our remote address check from MyNostoCronUpdateProductsModuleFrontController's constructor.

## Dependencies

[PrestaShop version > 1.5.0](https://github.com/PrestaShop/PrestaShop)

[Nosto PrestaShop module > 3.0.0](https://github.com/Nosto/nosto-prestashop/)
