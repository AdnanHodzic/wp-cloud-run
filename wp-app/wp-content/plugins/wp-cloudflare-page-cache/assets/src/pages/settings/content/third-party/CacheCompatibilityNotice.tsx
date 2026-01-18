import Notice from "@/components/Notice";
import { __ } from "@wordpress/i18n";

type noticeProps = {
  className?: string,
}

const CacheCompatibilityNotice = ({ className = "" }: noticeProps) => (
  <div className={className}>
    <Notice type="warning">
      {__('It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache')}
    </Notice>
  </div>
);

export default CacheCompatibilityNotice;