import ControlsGroup from "@/pages/settings/controls/ControlsGroup";
import Card, { CardContent, CardHeader } from "@/components/Card";
import { __ } from "@wordpress/i18n";

const Edd = () => {
  const controls = [
    {
      id: 'edd_bypass',
      type: 'checkbox-group',
      label: __('Don\'t cache the following EDD page types', 'wp-cloudflare-page-cache'),
      controls: [{
        id: 'cf_bypass_edd_checkout_page',
        type: 'checkbox',
        label: __('Primary checkout page', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_bypass_edd_purchase_history_page',
        type: 'checkbox',
        label: __('Purchase history page', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_bypass_edd_login_redirect_page',
        type: 'checkbox',
        label: __('Login redirect page', 'wp-cloudflare-page-cache'),
        recommended: true,
      },
      {
        id: 'cf_bypass_edd_success_page',
        type: 'checkbox',
        label: __('Success page', 'wp-cloudflare-page-cache'),
      },
      {
        id: 'cf_bypass_edd_failure_page',
        type: 'checkbox',
        label: __('Failure page', 'wp-cloudflare-page-cache'),
      }]
    },
    {
      id: 'cf_auto_purge_edd_payment_add',
      type: 'toggle',
      label: __('Automatically purge cache when a payment is inserted into the database', 'wp-cloudflare-page-cache'),
    }
  ];

  return (
    <Card>
      <CardHeader className="bg-muted">
        <h3 className="font-semibold text-base flex items-center">{__('Easy Digital Downloads settings', 'wp-cloudflare-page-cache')}</h3>
      </CardHeader>

      <CardContent className="p-0">
        <ControlsGroup controls={controls} />
      </CardContent>
    </Card>
  );
};

export default Edd;