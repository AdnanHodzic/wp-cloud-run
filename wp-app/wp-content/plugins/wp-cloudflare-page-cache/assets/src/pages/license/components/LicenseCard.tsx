import TransitionWrapper from "@/common/TransitionWrapper";
import Button from "@/components/Button";
import Card, { CardContent, CardFooter } from "@/components/Card";
import Notice from "@/components/Notice";
import { Input } from "@/components/ui/input";
import { spcApi } from "@/lib/api";
import { LINKS } from "@/lib/constants";
import { cn, formatDate } from "@/lib/utils";
import { useAppStore } from "@/store/store";
import { useMemo, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { CheckCircle, Copy, KeySquare } from "lucide-react";
import { toast } from "sonner";

const LicenseCard = () => {
  const { licenseData } = useAppStore();

  const {
    key = '',
    expires = '',
    license: licenseState,
    customer_email: email,
    item_name: plan,
    activations_left: activationsLeft,
  } = licenseData;

  const valid = licenseState === 'valid';

  const cloakedKey = useMemo(() => {
    if (!key) return '';

    return '•'.repeat(Math.min(key.length - 4, 28)) + key.substring(key.length - 4);
  }, [key]);

  const cloakedEmail = useMemo(() => {
    if (!email) return '';

    const [localPart, domainPart] = email.split('@');
    const [domain, tld] = domainPart.split('.');

    return `${localPart.substring(0, 2)}${'•'.repeat(localPart.length - 2)}@${domain.substring(0, 2)}${'•'.repeat(domain.length - 2)}.${tld}`;

  }, [email]);

  const [notification, setNotification] = useState(null)
  const [loading, setLoading] = useState(false);
  const [inputValue, setInputValue] = useState(key);

  const { asyncLocked, lockAsync, setLicenseData } = useAppStore();

  let timeout = null;

  const toggleLicense = async (e) => {
    e.preventDefault();

    setLoading(true);
    lockAsync(true);
    clearTimeout(timeout);
    setNotification(null);

    const formData = new FormData(e.currentTarget);
    const keyToSend = valid ? key : formData.get('licenseKey') as string;

    const response = await spcApi.toggleLicenseKey({
      action: valid ? 'deactivate' : 'activate',
      key: keyToSend
    })

    if (!response.success) {
      setNotification({ type: 'error', message: response.message });

      timeout = setTimeout(() => {
        setNotification(null);
      }, 5000);

      setLoading(false);
      lockAsync(false);
      return;
    }

    if (valid) {
      setInputValue('');
    }

    setLicenseData(response.data.license);
    setNotification({
      type: 'success',
      message: response.data.message,
    });

    timeout = setTimeout(() => {
      setNotification(null);
    }, 5000);

    setLoading(false);
    lockAsync(false);
  }

  return (
    <Card>
      <CardContent className="text-sm p-6">
        <div className="flex items-center mb-6">
          <div className={cn(
            "size-12 rounded-full flex items-center justify-center mr-4",
            {
              "bg-orange-100 dark:bg-orange-900/20": !valid,
              "bg-green-100 dark:bg-green-900/20": valid
            })}
          >
            {valid ? (
              <CheckCircle className="size-6 text-green-600 dark:text-green-400" />
            ) : (
              <KeySquare className="size-6 text-orange-600 dark:text-orange-400" />
            )}
          </div>
          <div>
            <h2 className="text-xl font-semibold text-foreground">
              {valid ?
                __('License Activated Successfully!', 'wp-cloudflare-page-cache') :
                __('Activate Super Page Cache Pro', 'wp-cloudflare-page-cache')
              }
            </h2>
            <p className="text-muted-foreground text-sm">
              {valid ?
                __('Super Page Cache Pro is now active on your site.', 'wp-cloudflare-page-cache') :
                __('Enter your license key to unlock Pro features', 'wp-cloudflare-page-cache')
              }
            </p>
          </div>
        </div>

        <form onSubmit={toggleLicense} className="space-y-4">


          {!valid && (<>
            <div>
              <label htmlFor="license-key" className="block text-sm font-medium mb-2">{__('License Key', 'wp-cloudflare-page-cache')}</label>
              <Input
                id="license-key"
                className="w-full h-12"
                placeholder={__('Enter your license key here', 'wp-cloudflare-page-cache') + '...'}
                value={valid ? cloakedKey : inputValue}
                onChange={(e) => setInputValue(e.target.value)}
                disabled={valid || loading}
                name="licenseKey"
                required
              />

              <p className="text-xs text-muted-foreground mt-1">
                {__('Your license key should be 32-40 characters long and contain letters and numbers.', 'wp-cloudflare-page-cache')}
              </p>
            </div>

            <div className="flex items-center space-x-3">
              <Button
                variant="orange"
                size="sm"
                className="h-auto"
                type="submit"
                disabled={asyncLocked || !inputValue}
                loader={loading}
                icon={KeySquare}
              >
                {loading ?
                  __('Activating', 'wp-cloudflare-page-cache') + '...' :
                  __('Activate License', 'wp-cloudflare-page-cache')
                }
              </Button>

              <Button
                target="_blank"
                href={LINKS.STORE}
                variant="link"
                className="text-orange-600 dark:text-orange-400 hover:text-orange-700 font-medium px-0"
              >
                {__('Find my license key', 'wp-cloudflare-page-cache')}
              </Button>
            </div>
          </>)}

          {valid && (<>
            <div className="grid grid-cols-2 gap-6 mb-6">
              <Notice
                type="success"
                icon="disabled"
              >
                <div className="grid grid-cols-1 gap-3">
                  <div>
                    <div className="text-sm font-medium">
                      {__('Licensed to', 'wp-cloudflare-page-cache')}
                    </div>
                    <div className="text-green-900 dark:text-green-500">{cloakedEmail}</div>
                  </div>
                  <div>
                    <div className="text-sm font-medium">
                      {__('Plan', 'wp-cloudflare-page-cache')}
                    </div>
                    <div className="text-green-900 dark:text-green-500">{plan}</div>
                  </div>
                </div>
              </Notice>

              <Notice
                type="success"
                icon="disabled"
              >
                <div className="grid grid-cols-1 gap-3">
                  <div>
                    <div className="text-sm font-medium">
                      {__('Expires', 'wp-cloudflare-page-cache')}
                    </div>
                    <div className="text-green-900 dark:text-green-500">
                      {formatDate(expires)}
                    </div>
                  </div>
                  <div>
                    <div className="text-sm font-medium">
                      {__('Activations Left', 'wp-cloudflare-page-cache')}
                    </div>
                    <div className="text-green-900 dark:text-green-500">{activationsLeft}</div>
                  </div>
                </div>
              </Notice>

            </div>

            <div className="bg-muted rounded-lg p-4 mb-6">
              <div className="flex items-center justify-between text-foreground gap-4">
                <div className="text-sm font-medium shrink-0">{__('License Key', 'wp-cloudflare-page-cache')}</div>
                <div className="font-mono text-sm overflow-hidden text-ellipsis whitespace-nowrap text-right">{cloakedKey}</div>
              </div>
            </div>

            <Button variant="outline" type="submit" loader={loading} disabled={asyncLocked}>
              {loading ?
                __('Deactivating', 'wp-cloudflare-page-cache') + '...' :
                __('Deactivate License', 'wp-cloudflare-page-cache')
              }
            </Button>
          </>)}

        </form>


      </CardContent>
      {
        notification && (
          <CardFooter className="bg-muted">
            <TransitionWrapper from="bottom" >
              <Notice
                type={notification.type}
                title={notification.message}
              />
            </TransitionWrapper>
          </CardFooter>
        )
      }
    </Card >
  )
}

export default LicenseCard;