import BaseControl from "@/pages/settings/controls/BaseControl";
import { Textarea as TextareaUI } from "@/components/ui/textarea";

type TextareaControlProps = {
  id: string;
  value: string;
  onChange: (nextValue: string, id?: string) => void;
  label?: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  disabled?: boolean;
  placeholder?: string;
  locked?: boolean;
}

const TextareaControl = ({ id, label, description, value, onChange, children, placeholder = '', disabled = false, locked = false }: TextareaControlProps) => {

  const handleChange = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
    const nextValue = e.target.value;

    onChange(nextValue, id);
  }

  return (
    <div className="grid gap-3">
      <BaseControl
        label={label}
        description={description}
        id={id}
        locked={locked}
      >
        <div className="flex flex-col gap-2 items-end text-right">
          <TextareaUI
            id={id}
            placeholder={placeholder}
            value={value ?? ""}
            className="w-full h-24 font-mono"
            onChange={handleChange}
            disabled={disabled}
          />
        </div>
      </BaseControl>
      {children}
    </div>
  );
}

export default TextareaControl;