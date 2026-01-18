import { Checkbox } from "@/components/ui/checkbox";

type CheckboxControlProps = {
  id: string;
  value: boolean | number;
  onChange: (nextValue: boolean, id?: string) => void;
  label: string | React.ReactNode;
  disabled?: boolean;
}

const CheckboxControl = ({ id, label, value, onChange, disabled = false }: CheckboxControlProps) => {

  const handleChange = (nextValue) => {
    onChange(nextValue, id);
  }

  return (
    <div className="grid gap-3">
      <div className="flex items-center gap-2 items-center text-left">
        <Checkbox
          className="cursor-pointer"
          id={id}
          checked={Boolean(value)}
          onCheckedChange={handleChange}
          disabled={disabled}
        />
        <label htmlFor={id} className="cursor-pointer flex items-center text-sm">
          {label}
        </label>
      </div>
    </div>
  );
}

export default CheckboxControl;