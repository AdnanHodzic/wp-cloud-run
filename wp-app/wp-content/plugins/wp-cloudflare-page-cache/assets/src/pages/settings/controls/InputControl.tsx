import BaseControl from "@/pages/settings/controls/BaseControl";
import { Input as InputUI } from "@/components/ui/input";
import Button from "@/components/Button";
import { EyeIcon, EyeOffIcon } from "lucide-react";
import { useState } from "@wordpress/element";
import { cn } from "@/lib/utils";
import { __ } from "@wordpress/i18n";

type InputControlProps = {
  id: string;
  value: string;
  onChange: (nextValue: string, id?: string) => void;
  label?: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  disabled?: boolean;
  placeholder?: string;
  type?: 'text' | 'number' | 'password' | 'email' | 'url';
  min?: number;
  max?: number;
  step?: number;
}

const InputControl = ({
  id,
  label,
  description,
  value,
  onChange,
  children,
  type = 'text',
  min = null,
  max = null,
  step = 1,
  placeholder = '',
  disabled = false
}: InputControlProps) => {

  const [showPassword, setShowPassword] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const nextValue = e.target.value;

    if (type === 'number') {
      let valueToSet = parseInt(nextValue);

      if (min && valueToSet < min) {
        valueToSet = min;
      }

      if (max && valueToSet > max) {
        valueToSet = max;
      }



      onChange(valueToSet.toString(), id);

      return;
    }

    onChange(nextValue, id);
  }

  const props = {};

  if (type === 'number') {
    if (min !== null) {
      props['min'] = min;
    }
    if (max !== null) {
      props['max'] = max;
    }

    props['onBlur'] = (e: React.FocusEvent<HTMLInputElement>) => {
      if (e.target.value === '') {
        e.target.value = props['min']?.toString() ?? 0;
      }
    }

    props['step'] = step;
  }

  if (type === 'password') {
    props['autoComplete'] = 'off';
  }

  return (
    <div className="grid gap-3">
      <BaseControl
        label={label}
        description={description}
        id={id}
        stack={false}
        stackMobile={true}
      >
        <div className="flex items-center items-end text-right relative">
          <InputUI
            id={id}
            className={cn(type === 'password' && "rounded-r-none", "w-auto")}
            type={type === 'password' ? (showPassword ? 'text' : 'password') : type}
            placeholder={placeholder}
            value={value ?? ""}
            onChange={handleChange}
            disabled={disabled}

            {...props}
          />

          {type === 'password' && (
            <Button
              type="button"
              variant="default"
              size="icon"
              className="rounded-l-none"
              disabled={disabled}
              icon={showPassword ? EyeIcon : EyeOffIcon}
              onClick={() => {
                setShowPassword(!showPassword);
              }}
            >
              <span className="sr-only">
                {showPassword ? __('Hide password', 'wp-cloudflare-page-cache') : __('Show password', 'wp-cloudflare-page-cache')}
              </span>
            </Button>
          )}
        </div>
      </BaseControl>
      {children}
    </div>
  );
}

export default InputControl;