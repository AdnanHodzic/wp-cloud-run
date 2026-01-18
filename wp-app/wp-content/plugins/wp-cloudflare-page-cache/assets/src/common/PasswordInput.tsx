import Button from "@/components/Button";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { Eye, EyeOff } from "lucide-react";

type PasswordInputProps = {
  type?: 'password' | 'text';
  wrapperClassName?: string;
} & React.ComponentProps<"input">;

const PasswordInput = ({ wrapperClassName, ...props }: PasswordInputProps) => {
  const [isVisible, setIsVisible] = useState(false);

  return (
    <div className={cn("relative", wrapperClassName)}>
      <Input
        {...props}
        type={isVisible ? 'text' : 'password'}
      />
      {!!props.value && (
        <Button
          variant="link"
          size="icon"
          type="button"
          onClick={() => setIsVisible(!isVisible)}
          icon={isVisible ? EyeOff : Eye}
          className="absolute right-0 top-1/2 -translate-y-1/2 p-2 right-1"
        >
          <span className="sr-only">
            {isVisible ? __('Hide password', 'wp-cloudflare-page-cache') : __('Show password', 'wp-cloudflare-page-cache')}
          </span>
        </Button>
      )}
    </div>
  )
}

export default PasswordInput;