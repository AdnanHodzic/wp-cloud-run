import { getUpsellURL } from "@/lib/utils";
import TextareaControl from "@/pages/settings/controls/TextareaControl";
import ToggleControl from "@/pages/settings/controls/ToggleControl";
import { __ } from "@wordpress/i18n";
import { Crown } from "lucide-react";

const ProBadge = ({ utmCampaign = 'pro-tag' }: { utmCampaign?: string }) => {
  return (
    <a href={getUpsellURL(utmCampaign)} className="ml-2 px-2 py-0.5 flex items-center gap-1 text-xs font-semibold bg-gray-800 dark:bg-white text-white dark:text-gray-800 hover:opacity-80 transition-opacity duration-200 rounded flex items-center uppercase">
      <Crown className="size-3" />
      {__('Pro', 'wp-cloudflare-page-cache')}
    </a>
  );
}

type DummyControlProps = {
  type: 'textarea' | 'toggle';
  id: string;
  label?: string | React.ReactNode;
  description?: string | React.ReactNode;
  utmCampaign?: string;
  placeholder?: string;
}

const DummyControl = ({ type, id, label = "", description = "", utmCampaign = '', placeholder = '' }: DummyControlProps) => {

  const handleChange = (nextValue: boolean | string, id?: string) => { }

  if (type === 'textarea') {
    return (
      <TextareaControl
        id={`dummy-${id}`}
        placeholder={placeholder}
        label={
          <span className="inline-flex items-center gap-3">
            {label}
            <ProBadge utmCampaign={utmCampaign} />
          </span>
        }
        value=""
        disabled
        description={description}
        onChange={handleChange}
        locked
      />
    );
  }

  if (type === 'toggle') {
    return (
      <ToggleControl
        id={`dummy-${id}`}
        disabled
        label={
          <span className="inline-flex items-center gap-3">
            {label}
            <ProBadge utmCampaign={utmCampaign} />
          </span>
        }
        description={description}
        value={false}
        onChange={handleChange}
        locked
      />
    );
  }

  return null;

}

export default DummyControl;