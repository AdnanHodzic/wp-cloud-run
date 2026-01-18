import { __ } from "@wordpress/i18n";
import { CopyIcon } from "lucide-react";
import { toast } from "sonner";
import Button from "@/components/Button";
import { Input } from "@/components/ui/input";

type LockedInputCopyProps = {
  content: string;
  className?: string;
}

const LockedInputCopy = ({ content }: LockedInputCopyProps) => {

  const onCopy = () => {
    navigator.clipboard.writeText(content);
    toast.success(__('Copied to clipboard!', 'wp-cloudflare-page-cache'));
  }


  return (
    <div className="flex mt-5">
      <Input
        className="rounded-r-none border-r-0 grow h-auto"
        disabled
        value={content ?? ""}
      />
      <Button icon={CopyIcon} onClick={onCopy} className="rounded-l-none" size="icon">
        <span className="sr-only">
          {__('Copy to clipboard', 'wp-cloudflare-page-cache')}
        </span>
      </Button>
    </div>
  )
}

export default LockedInputCopy;