import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Tooltip from "@/common/Tooltip";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";

const WooCommerce = () => {
  const controls = [
    {
      id: 'woo_bypass',
      type: 'checkbox-group',
      label: __('Don\'t cache the following WooCommerce page types', 'wp-cloudflare-page-cache'),
      controls: [{
        id: 'cf_bypass_woo_cart_page',
        type: 'checkbox',
        label: <div className="flex items-center">{__('Cart', 'wp-cloudflare-page-cache')} <Tooltip><code>is_cart</code></Tooltip> </div>,
        recommended: true,
      },
      {
        id: 'cf_bypass_woo_checkout_page',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('Checkout', 'wp-cloudflare-page-cache')} <Tooltip><code>is_checkout</code></Tooltip> </div>,
        recommended: true,
      },
      {
        id: 'cf_bypass_woo_checkout_pay_page',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('Checkout\'s pay page', 'wp-cloudflare-page-cache')} <Tooltip><code>is_checkout_pay_page</code></Tooltip> </div>,
        recommended: true,
      },
      {
        id: 'cf_bypass_woo_product_page',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('Product', 'wp-cloudflare-page-cache')} <Tooltip><code>is_product</code></Tooltip> </div>,
      },
      {
        id: 'cf_bypass_woo_shop_page',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('Shop', 'wp-cloudflare-page-cache')} <Tooltip><code>is_shop</code></Tooltip> </div>,
      },
      {
        id: 'cf_bypass_woo_product_tax_page',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('Product taxonomy', 'wp-cloudflare-page-cache')} <Tooltip><code>is_product_taxonomy</code></Tooltip> </div>,
      },
      {
        id: 'cf_bypass_woo_product_tag_page',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('Product tag', 'wp-cloudflare-page-cache')} <Tooltip><code>is_product_tag</code></Tooltip> </div>,
      },
      {
        id: 'cf_bypass_woo_product_cat_page',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('Product category', 'wp-cloudflare-page-cache')} <Tooltip><code>is_product_category</code></Tooltip> </div>,
      },
      {
        id: 'cf_bypass_woo_pages',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('WooCommerce page', 'wp-cloudflare-page-cache')} <Tooltip><code>is_woocommerce</code></Tooltip> </div>,
      },
      {
        id: 'cf_bypass_woo_account_page',
        type: 'checkbox',
        label: <div className="inline-flex items-center">{__('My Account page', 'wp-cloudflare-page-cache')} <Tooltip><code>is_account</code></Tooltip> </div>,
        recommended: true,
      }]
    },
    {
      id: 'cf_auto_purge_woo_product_page',
      type: 'toggle',
      label: __('Automatically purge cache for product page and related categories when stock quantity changes', 'wp-cloudflare-page-cache'),
    },
    {
      id: 'cf_auto_purge_woo_scheduled_sales',
      type: 'toggle',
      label: __('Automatically purge cache for scheduled sales', 'wp-cloudflare-page-cache'),
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('WooCommerce settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default WooCommerce;