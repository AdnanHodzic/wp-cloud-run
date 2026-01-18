import BaseControl from "@/pages/settings/controls/BaseControl";
import Select from "@/common/Select";

type SelectControlProps = {
  id: string;
  value: string;
  onChange: (nextValue: string, id?: string) => void;
  label?: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  disabled?: boolean;
  locked?: boolean;
  options: { value: string; label: string }[];
  className?: string;
}

const SelectControl = ({
  id,
  label,
  description,
  value,
  onChange,
  children,
  options,
  className = "",
  disabled = false,
  locked = false
}: SelectControlProps) => {

  const handleChange = (nextValue: string) => {
    onChange(nextValue, id);
  }

  return (
    <div className="grid gap-3">
      <BaseControl
        label={label}
        description={description}
        id={id}
        stack={false}
        locked={locked}
        stackMobile={true}
      >
        <div className="flex items-center items-end text-right relative">
          <Select
            id={id}
            value={value ?? ""}
            onChange={handleChange}
            disabled={disabled}
            options={options}
            className={className}
          />
        </div>
      </BaseControl>
      {children}
    </div>
  );
}

export default SelectControl; 