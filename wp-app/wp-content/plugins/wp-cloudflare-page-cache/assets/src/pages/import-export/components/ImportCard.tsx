import Card, { CardContent, CardFooter, CardHeader } from "@/components/Card";
import FileControl from "@/pages/settings/controls/FileControl";
import { __ } from "@wordpress/i18n";
import { Upload } from "lucide-react";
import { useEffect, useState } from "@wordpress/element";
import { useAppStore } from "@/store/store";
import { spcApi } from "@/lib/api";
import { toast } from "sonner";
import Notice from "@/components/Notice";
import Button from "@/components/Button";

const ImportCard = () => {
  const [importContent, setImportContent] = useState('');
  const [valid, setValid] = useState(true);
  const [isImporting, setIsImporting] = useState(false);
  const { lockAsync, asyncLocked } = useAppStore();

  const handleFileChange = (content: string | null) => {
    setImportContent(content || '');
  }

  const handleImport = async () => {
    lockAsync(true)
    setIsImporting(true);

    const response = await spcApi.importConfig(JSON.parse(importContent));

    if (!response.success) {
      setIsImporting(false);
      toast.error(response.message);

      return;
    }

    toast.success(response.message, {
      description: __('Please wait, the page will reload soon.', 'wp-cloudflare-page-cache'),
    });

    setTimeout(() => {
      window.location.href = window.SPCDash.mainPageURL;
    }, 3000);
  }

  useEffect(() => {
    if (!importContent.trim()) {
      setValid(true);
      return;
    }

    try {
      JSON.parse(importContent);
      setValid(true);
    } catch {
      setValid(false);
    }
  }, [importContent])

  return <Card>
    <CardHeader className="p-6 flex items-center border-b bg-orange-50 border-orange-200 dark:bg-orange-700/10 dark:border-orange-800/50">
      <div className="size-10 rounded-lg flex items-center justify-center mr-4 bg-orange-100 dark:bg-orange-900/50 border border-orange-200 dark:border-orange-800/50">
        <Upload className="size-5 text-orange-600" />
      </div>

      <div>
        <h3 className="text-base font-semibold text-foreground">
          {__('Import Settings', 'wp-cloudflare-page-cache')}
        </h3>
        <p className="text-sm text-muted-foreground">
          {__('Restore configuration from backup', 'wp-cloudflare-page-cache')}
        </p>
      </div>
    </CardHeader>

    <CardContent className="space-y-4">
      <p className="text-sm">
        {__('Import your settings from a previously exported file to quickly restore your configuration.', 'wp-cloudflare-page-cache')}
      </p>

      <FileControl
        disabled={asyncLocked}
        id="import-config"
        onChange={handleFileChange}
        label={__('Import config file', 'wp-cloudflare-page-cache')}
        accept=".json"
        description={
          <>
            {__('Import the options of the previously exported configuration file.')}
            {' '}
            {__('After importing, you\'ll need to reconnect to Cloudflare by entering your login details again and turning the cache back on.', 'wp-cloudflare-page-cache')}
          </>
        }
      >
        {!valid && (
          <Notice type="error">
            {__('Invalid JSON format', 'wp-cloudflare-page-cache')}
          </Notice>
        )}
      </FileControl>

      <Notice
        className="mt-auto"
        type="warning"
      >
        <strong>{__('Important', 'wp-cloudflare-page-cache')}:</strong>
        {' '}
        {__('Importing will overwrite your current settings. Consider exporting your current configuration first as a backup.', 'wp-cloudflare-page-cache')}
      </Notice>


    </CardContent>
    <CardFooter className="mt-auto">
      <Button
        variant="orange"
        className="w-full mt-auto"
        disabled={!importContent || !valid || asyncLocked}
        loader={isImporting}
        onClick={handleImport}
        icon={Upload}
      >
        {__('Import Settings File', 'wp-cloudflare-page-cache')}
      </Button>
    </CardFooter>
  </Card>;
};

export default ImportCard;