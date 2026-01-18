import BaseControl from "@/pages/settings/controls/BaseControl";
import ToggleButtons from "@/components/ToggleButtons";

type ToggleControlProps = {
  id: string;
  value: boolean | number;
  onChange: (nextValue: boolean, id?: string) => void;
  label: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  disabled?: boolean;
  locked?: boolean;
}

const ToggleControl = ({ id, label, description, value, onChange, children, disabled = false, locked = false }: ToggleControlProps) => {

  const handleChange = (nextValue) => {
    onChange(nextValue, id);
  }

  return (
    <div className="grid gap-3">
      <BaseControl
        label={label}
        description={description}
        id={id}
        stack={false}
        afterControl={children}
        locked={locked}
      >
        <div className="flex flex-col gap-2 items-end text-right">
          <ToggleButtons
            value={Boolean(value)}
            onChange={handleChange}
            id={id}
            disabled={disabled}
          />
        </div>
      </BaseControl>
    </div>
  );
}

export default ToggleControl;