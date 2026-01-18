import { useSettingsStore } from "@/store/optionsStore";
import SelectControl from "@/pages/settings/controls/SelectControl";
import { useAppStore } from "@/store/store";

type SelectProps = {
  id: string;
  label: string | React.ReactNode;
  description?: string | React.ReactNode;
  children?: React.ReactNode;
  options: { value: string; label: string }[];
  className?: string;
  disabled?: boolean;
}

const Select = ({ disabled = false, ...props }: SelectProps) => {
  const { updateSetting, settings } = useSettingsStore();
  const { asyncLocked } = useAppStore();
  const { id } = props;

  const handleChange = (nextValue: string) => {
    updateSetting(id, nextValue);
  }

  return (
    <SelectControl
      onChange={handleChange}
      value={settings[id] as string}
      disabled={asyncLocked || disabled}
      {...props}
    />
  );
}

export default Select; 